document.addEventListener('DOMContentLoaded', () => {
    
    // ============================================================
    // 0. CEK FLASH MESSAGE (Notifikasi dari Halaman Lain)
    // ============================================================
    // Ini berguna jika user baru saja Login atau Kirim Review dan di-redirect kembali ke sini
    const savedMsg = localStorage.getItem('toast_msg');
    const savedType = localStorage.getItem('toast_type');

    if (savedMsg) {
        // Tampilkan notifikasi jika ada
        if (typeof showToast === 'function') {
            showToast(savedMsg, savedType || 'success');
        } else {
            alert(savedMsg); // Fallback jika toast.js belum termuat
        }
        // Hapus pesan agar tidak muncul lagi saat refresh
        localStorage.removeItem('toast_msg');
        localStorage.removeItem('toast_type');
    }

    // ============================================================
    // 1. SETUP VARIABEL UI
    // ============================================================
    const plusBtn = document.querySelector('.plus');
    const minusBtn = document.querySelector('.minus');
    const countSpan = document.querySelector('.count');
    const stockDisplay = document.getElementById('stock-display');
    const priceDisplay = document.getElementById('display-price');
    const addToCartBtn = document.querySelector('.add'); // Tombol Add to Cart
    const buyNowBtn = document.querySelector('.buy');    // Tombol Buy Now
    
    let qty = 1;
    let currentStock = 0;
    let currentPrice = 0;
    let selectedDetailID = null; // Menyimpan ID varian yang dipilih

    // 2. FUNGSI FORMAT RUPIAH
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    };

    // 3. LOGIKA UPDATE TAMPILAN (HARGA & STOK)
    const updateInfo = () => {
        const activeGrind = document.querySelector('.grind-btn.active');
        const activeSize = document.querySelector('.size-btn.active');

        // Jika ada tombol Size tapi belum dipilih, stop.
        if (document.querySelector('.size-btn') && !activeSize) return;

        const selectedSize = activeSize ? activeSize.getAttribute('data-value').trim() : null;
        const selectedGrind = activeGrind ? activeGrind.getAttribute('data-value').trim() : null;

        // Cari data yang cocok di variabel productData
        const variant = productData.find(item => {
            const dbSize = item.ukuran ? item.ukuran.trim() : null;
            const dbGrind = item.nama_grind ? item.nama_grind.trim() : null;

            const sizeMatch = selectedSize ? dbSize === selectedSize : true;
            const grindMatch = selectedGrind ? dbGrind === selectedGrind : true;
            
            return sizeMatch && grindMatch;
        });

        if (variant) {
            currentStock = parseInt(variant.stok);
            currentPrice = parseInt(variant.harga);
            selectedDetailID = variant.id_detail_produk; 

            // Update UI
            stockDisplay.textContent = currentStock;
            priceDisplay.textContent = formatRupiah(currentPrice);

            // Reset qty jika melebihi stok baru
            if (qty > currentStock) {
                qty = currentStock > 0 ? 1 : 0;
            }
            if (qty === 0 && currentStock > 0) qty = 1;
            countSpan.textContent = qty;
            
            // Handle Stok Habis
            if (currentStock === 0) {
                 stockDisplay.textContent = "Habis";
                 stockDisplay.style.color = "#ff1744"; // Merah
                 
                 if(addToCartBtn) {
                     addToCartBtn.disabled = true;
                     addToCartBtn.style.opacity = "0.5";
                     addToCartBtn.style.cursor = "not-allowed";
                 }
                 if(buyNowBtn) {
                     buyNowBtn.disabled = true;
                     buyNowBtn.style.opacity = "0.5";
                 }
                 qty = 0;
                 countSpan.textContent = qty;
            } else {
                 stockDisplay.style.color = "#666";
                 
                 if(addToCartBtn) {
                     addToCartBtn.disabled = false;
                     addToCartBtn.style.opacity = "1";
                     addToCartBtn.style.cursor = "pointer";
                 }
                 if(buyNowBtn) {
                     buyNowBtn.disabled = false;
                     buyNowBtn.style.opacity = "1";
                 }
            }

        } else {
            // Varian tidak ditemukan
            stockDisplay.textContent = "-";
            priceDisplay.textContent = "Tidak Tersedia";
            currentStock = 0;
            selectedDetailID = null;
            if(addToCartBtn) addToCartBtn.disabled = true;
        }
    };

    // 4. EVENT LISTENER TOMBOL OPSI (SIZE/GRIND)
    const optionButtons = document.querySelectorAll('.option-btn');
    optionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const siblings = this.parentElement.querySelectorAll('.option-btn');
            siblings.forEach(s => s.classList.remove('active'));
            
            this.classList.add('active');
            updateInfo();
        });
    });

    // 5. EVENT LISTENER QUANTITY (+ dan -)
    if(plusBtn) {
        plusBtn.addEventListener('click', () => {
            if (qty < currentStock) {
                qty++;
                countSpan.textContent = qty;
            } else {
                // Ganti alert dengan Toast Error
                showToast('Stok maksimal tercapai (' + currentStock + ')', 'error');
            }
        });
    }

    if(minusBtn) {
        minusBtn.addEventListener('click', () => {
            if (qty > 1) {
                qty--;
                countSpan.textContent = qty;
            }
        });
    }

    // ============================================================
    // 6. LOGIKA ADD TO CART (MENGIRIM DATA KE DATABASE)
    // ============================================================
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', () => {
            // Validasi Input
            if (!selectedDetailID) {
                showToast("Silakan pilih varian produk terlebih dahulu!", "error");
                return;
            }
            if (qty <= 0) {
                showToast("Stok habis atau jumlah tidak valid!", "error");
                return;
            }

            // Efek Loading Tombol
            const originalText = addToCartBtn.textContent;
            addToCartBtn.textContent = "Memproses...";
            addToCartBtn.disabled = true;

            // Kirim Data via AJAX (Fetch)
            fetch('../Product-Cart/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_detail_produk: selectedDetailID,
                    qty: qty
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // SUKSES: Tampilkan Toast Hijau
                    showToast("Berhasil masuk keranjang!", "success");
                    
                    // Opsional: Reset qty atau update navbar cart count disini
                    
                } else {
                    // GAGAL: Tampilkan Toast Merah
                    showToast(data.message, "error");
                    
                    // Jika gagal karena belum login, redirect ke login
                    if (data.message.toLowerCase().includes('login')) {
                        setTimeout(() => {
                            window.location.href = '../auth/login.php';
                        }, 1500);
                    }
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                showToast("Terjadi kesalahan sistem. Coba lagi nanti.", "error");
            })
            .finally(() => {
                // Kembalikan tombol seperti semula
                addToCartBtn.textContent = originalText;
                addToCartBtn.disabled = false;
            });
        });
    }

    // ============================================================
    // 7. LOGIKA BUY IT NOW
    // ============================================================
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', () => {
             // Trik: Klik add to cart dulu, lalu user bisa checkout manual
             addToCartBtn.click(); 
             
             // Atau jika ingin langsung ke checkout (advanced):
             // Bisa dibuat logika khusus redirect setelah add to cart sukses.
        });
    }

    // 8. Jalankan fungsi update saat halaman pertama kali dimuat
    setTimeout(updateInfo, 100);
});