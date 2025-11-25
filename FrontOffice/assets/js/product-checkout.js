document.addEventListener("DOMContentLoaded", () => {
    const payBtn = document.getElementById("pay-button");

    if (payBtn) {
        payBtn.addEventListener("click", async (e) => {
            e.preventDefault();

            // 1. Cek apakah alamat sudah dipilih
            const selectedAddress = document.querySelector('input[name="id_alamat"]:checked');
            
            if (!selectedAddress) {
                if (typeof showToast === 'function') {
                    showToast("Silakan pilih alamat pengiriman terlebih dahulu!", "error");
                } else {
                    alert("Pilih alamat dulu!");
                }
                return;
            }

            // 2. Proses Pembayaran (Placeholder untuk Midtrans nanti)
            const originalText = payBtn.innerText;
            payBtn.innerText = "Memproses...";
            payBtn.disabled = true;

            // Simulasi Loading
            setTimeout(() => {
                if (typeof showToast === 'function') {
                    showToast("Fitur pembayaran Midtrans belum diaktifkan.", "warning");
                } else {
                    alert("Fitur pembayaran belum aktif.");
                }
                
                payBtn.innerText = originalText;
                payBtn.disabled = false;
            }, 2000);
        });
    }
});