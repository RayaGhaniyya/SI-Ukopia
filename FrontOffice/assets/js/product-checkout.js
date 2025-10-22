document.addEventListener("DOMContentLoaded", () => {
  const orderBtn = document.querySelector(".order-btn");

  orderBtn.addEventListener("click", (e) => {
    e.preventDefault();
    alert("Pesanan berhasil dibuat! Terima kasih telah berbelanja ❤️");
  });
});
