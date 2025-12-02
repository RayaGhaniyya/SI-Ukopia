document.addEventListener("DOMContentLoaded", () => {
    const cartTable = document.querySelector(".cart-table");
    const cartTotalDisplay = document.getElementById("cart-total");
    const selectAllCheckbox = document.getElementById("select-all");
    const checkoutBtn = document.getElementById("btn-checkout");

    const formatRupiah = (num) => {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0
        }).format(num);
    };

    const updateSummary = () => {
        let total = 0;
        const items = document.querySelectorAll(".cart-item");
        
        items.forEach(item => {
            const checkbox = item.querySelector(".item-check");
            if (checkbox && checkbox.checked) {
                const price = parseInt(item.dataset.price);
                const qty = parseInt(item.querySelector(".qty-count").textContent);
                total += price * qty;
            }
        });
        
        if (cartTotalDisplay) {
            cartTotalDisplay.textContent = formatRupiah(total);
        }
    };

    const updateDB = async (id_keranjang, action, qty = 0) => {
        try {
            const response = await fetch("update_cart.php", { 
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id_keranjang, action, qty })
            });
            
            if (!response.ok) throw new Error("Server Error");
            
            const result = await response.json();
            return result.status === 'success';
        } catch (error) {
            console.error("Error updating cart:", error);
            showToast("Terjadi kesalahan koneksi.", "error");
            return false;
        }
    };

    if (cartTable) {
        cartTable.addEventListener("click", async (e) => {
            const target = e.target;
            const deleteBtn = target.closest(".delete-btn");
            const row = target.closest(".cart-item");
            
            if (!row) return;

            const id = row.dataset.id;
            const price = parseInt(row.dataset.price);
            const maxStock = parseInt(row.dataset.stock); 
            const countSpan = row.querySelector(".qty-count");
            const totalSpan = row.querySelector(".product-total");
            let currentQty = parseInt(countSpan.textContent);

            if (target.classList.contains("plus")) {
                if (currentQty < maxStock) {
                    const newQty = currentQty + 1;
                    countSpan.textContent = newQty;
                    totalSpan.textContent = formatRupiah(price * newQty);
                    updateSummary();
                    updateDB(id, 'update_qty', newQty); 
                } else {
                    showToast(`Stok mentok! Hanya tersisa ${maxStock} item.`, 'error');
                }
            }

            else if (target.classList.contains("minus")) {
                if (currentQty > 1) {
                    const newQty = currentQty - 1;
                    countSpan.textContent = newQty;
                    totalSpan.textContent = formatRupiah(price * newQty);
                    updateSummary();
                    updateDB(id, 'update_qty', newQty);
                }
            }

            else if (deleteBtn) {
                showConfirm("Yakin ingin menghapus item ini?", async () => {
                    const success = await updateDB(id, 'delete');
                    if (success) {
                        showToast("Item berhasil dihapus", "success");
                        
                        row.style.transition = "all 0.5s ease";
                        row.style.opacity = "0";
                        row.style.transform = "translateX(50px)";
                        
                        setTimeout(() => {
                            row.remove();
                            updateSummary();
                            if (document.querySelectorAll(".cart-item").length === 0) {
                                location.reload();
                            }
                        }, 500);
                    } else {
                        showToast("Gagal menghapus item", "error");
                    }
                });
            }
        });

        cartTable.addEventListener("change", (e) => {
            if (e.target.classList.contains("item-check")) {
                updateSummary();
                const allChecked = [...document.querySelectorAll(".item-check")].every(c => c.checked);
                if(selectAllCheckbox) selectAllCheckbox.checked = allChecked;
            }
        });
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("change", (e) => {
            const isChecked = e.target.checked;
            document.querySelectorAll(".item-check").forEach(cb => cb.checked = isChecked);
            updateSummary();
        });
    }
    
    if (checkoutBtn) {
        checkoutBtn.addEventListener("click", () => {
            const totalText = cartTotalDisplay.textContent;
            const totalValue = parseInt(totalText.replace(/[^0-9]/g, "")); 

            if (totalValue === 0) {
                showToast("Pilih minimal satu barang untuk checkout!", "error");
            } else {
                window.location.href = "../Product-Checkout/index.php";
            }
        });
    }

    updateSummary();
});
