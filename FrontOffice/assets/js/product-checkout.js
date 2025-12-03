document.addEventListener("DOMContentLoaded", () => {
    const payBtn = document.getElementById("pay-button");

    if (payBtn) {
        payBtn.addEventListener("click", async (e) => {
            e.preventDefault();

            const selectedAddress = document.querySelector('input[name="id_alamat"]:checked');
            const totalBayar = document.querySelector('input[name="total_bayar"]').value;
            const ongkir = document.querySelector('input[name="ongkir"]').value;

            if (!selectedAddress) {
                showToast("Silakan pilih alamat pengiriman terlebih dahulu!", "error");
                return;
            }

            const originalText = payBtn.innerText;
            payBtn.innerText = "Memproses...";
            payBtn.disabled = true;

            try {
                const response = await fetch('place_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_alamat: selectedAddress.value,
                        ongkir: ongkir,
                        total: totalBayar
                    })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    window.snap.pay(result.token, {
                        onSuccess: function(result){
                            window.location.href = "success.php?order_id=" + result.order_id;
                        },
                        onPending: function(result){
                            window.location.href = "success.php?order_id=" + result.order_id;
                        },
                        onError: function(result){
                            showToast("Pembayaran Gagal!", "error");
                            payBtn.innerText = originalText;
                            payBtn.disabled = false;
                        },
                        onClose: function(){
                            showToast("Kamu menutup popup pembayaran. Silakan bayar nanti di riwayat pesanan.", "warning");
                            payBtn.innerText = originalText;
                            payBtn.disabled = false;
                        }
                    });
                } else {
                    showToast(result.message, "error");
                    payBtn.innerText = originalText;
                    payBtn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                showToast("Terjadi kesalahan sistem.", "error");
                payBtn.innerText = originalText;
                payBtn.disabled = false;
            }
        });
    }
});