document.addEventListener('DOMContentLoaded', () => {
    
    const savedMsg = localStorage.getItem('toast_msg');
    const savedType = localStorage.getItem('toast_type');

    if (savedMsg) {
        if (typeof showToast === 'function') {
            showToast(savedMsg, savedType || 'success');
        } else {
            alert(savedMsg); 
        }
        localStorage.removeItem('toast_msg');
        localStorage.removeItem('toast_type');
    }

    const plusBtn = document.querySelector('.plus');
    const minusBtn = document.querySelector('.minus');
    const countSpan = document.querySelector('.count');
    const stockDisplay = document.getElementById('stock-display');
    const priceDisplay = document.getElementById('display-price');
    const addToCartBtn = document.querySelector('.add');
    const buyNowBtn = document.querySelector('.buy');
    
    let qty = 1;
    let currentStock = 0;
    let currentPrice = 0;
    let selectedDetailID = null;

    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    };

    const updateInfo = () => {
        const activeGrind = document.querySelector('.grind-btn.active');
        const activeSize = document.querySelector('.size-btn.active');

        if (document.querySelector('.size-btn') && !activeSize) return;

        const selectedSize = activeSize ? activeSize.getAttribute('data-value').trim() : null;
        const selectedGrind = activeGrind ? activeGrind.getAttribute('data-value').trim() : null;

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

            stockDisplay.textContent = currentStock;
            priceDisplay.textContent = formatRupiah(currentPrice);

            if (qty > currentStock) qty = currentStock > 0 ? 1 : 0;
            if (qty === 0 && currentStock > 0) qty = 1;
            countSpan.textContent = qty;
            
            if (currentStock === 0) {
                 stockDisplay.textContent = "Habis";
                 stockDisplay.style.color = "#ff1744";
                 if(addToCartBtn) { addToCartBtn.disabled = true; addToCartBtn.style.opacity = "0.5"; addToCartBtn.style.cursor = "not-allowed"; }
                 if(buyNowBtn) { buyNowBtn.disabled = true; buyNowBtn.style.opacity = "0.5"; }
                 qty = 0;
                 countSpan.textContent = qty;
            } else {
                 stockDisplay.style.color = "#666";
                 if(addToCartBtn) { addToCartBtn.disabled = false; addToCartBtn.style.opacity = "1"; addToCartBtn.style.cursor = "pointer"; }
                 if(buyNowBtn) { buyNowBtn.disabled = false; buyNowBtn.style.opacity = "1"; }
            }
        } else {
            stockDisplay.textContent = "-";
            priceDisplay.textContent = "Tidak Tersedia";
            currentStock = 0;
            selectedDetailID = null;
            if(addToCartBtn) addToCartBtn.disabled = true;
        }
    };

    const optionButtons = document.querySelectorAll('.option-btn');
    optionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const siblings = this.parentElement.querySelectorAll('.option-btn');
            siblings.forEach(s => s.classList.remove('active'));
            this.classList.add('active');
            updateInfo();
        });
    });

    if(plusBtn) {
        plusBtn.addEventListener('click', () => {
            if (qty < currentStock) {
                qty++; countSpan.textContent = qty;
            } else {
                showToast('Stok maksimal tercapai (' + currentStock + ')', 'error');
            }
        });
    }

    if(minusBtn) {
        minusBtn.addEventListener('click', () => {
            if (qty > 1) {
                qty--; countSpan.textContent = qty;
            }
        });
    }

    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', () => {
            if (!selectedDetailID) { showToast("Silakan pilih varian produk terlebih dahulu!", "error"); return; }
            if (qty <= 0) { showToast("Stok habis!", "error"); return; }

            const originalText = addToCartBtn.textContent;
            addToCartBtn.textContent = "Memproses...";
            addToCartBtn.disabled = true;

            fetch('../Product-Cart/add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_detail_produk: selectedDetailID, qty: qty }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast("Berhasil masuk keranjang!", "success");
                } else {
                    showToast(data.message, "error");
                    if (data.message.toLowerCase().includes('login')) setTimeout(() => { window.location.href = '../auth/login.php'; }, 1500);
                }
            })
            .catch(() => showToast("Terjadi kesalahan sistem.", "error"))
            .finally(() => {
                addToCartBtn.textContent = originalText;
                addToCartBtn.disabled = false;
            });
        });
    }

    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', () => {
             if (!selectedDetailID) { showToast("Silakan pilih varian produk terlebih dahulu!", "error"); return; }
             if (qty <= 0) { showToast("Stok habis!", "error"); return; }

             const originalText = buyNowBtn.textContent;
             buyNowBtn.textContent = "Memproses...";
             buyNowBtn.disabled = true;

             fetch('../Product-Cart/action_buy_now.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_detail_produk: selectedDetailID, qty: qty }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = '../Product-Checkout/index.php';
                } else {
                    showToast(data.message, "error");
                    if (data.message.toLowerCase().includes('login')) setTimeout(() => { window.location.href = '../auth/login.php'; }, 1500);
                    buyNowBtn.textContent = originalText;
                    buyNowBtn.disabled = false;
                }
            })
            .catch(() => {
                showToast("Terjadi kesalahan sistem.", "error");
                buyNowBtn.textContent = originalText;
                buyNowBtn.disabled = false;
            });
        });
    }

    setTimeout(updateInfo, 100);
});
