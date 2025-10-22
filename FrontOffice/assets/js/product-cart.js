document.addEventListener("DOMContentLoaded", () => {
  const plusBtns = document.querySelectorAll(".qty-btn:nth-child(3)");
  const minusBtns = document.querySelectorAll(".qty-btn:nth-child(1)");
  const deleteBtns = document.querySelectorAll(".delete-btn");
  const cartTotal = document.getElementById("cart-total");
  const selectAllCheckbox = document.getElementById("select-all");
  const itemCheckboxes = document.querySelectorAll(".custom-checkbox");

  function updateTotal() {
    let total = 0;
    document.querySelectorAll(".cart-item").forEach(item => {
      const checkbox = item.querySelector(".custom-checkbox");
      if (checkbox && checkbox.checked) {
        const qty = parseInt(item.querySelector(".qty-count").textContent);
        const priceText = item.querySelector(".product-details p").textContent;
        const price = parseInt(priceText.replace(/\D/g, ""));
        total += qty * price;
        item.querySelector(".product-total").textContent = `Rp ${(qty * price).toLocaleString("id-ID")},00`;
      }
    });
    cartTotal.textContent = `Rp ${total.toLocaleString("id-ID")},00`;
  }

  selectAllCheckbox.addEventListener("change", () => {
    itemCheckboxes.forEach(cb => {
      if (cb !== selectAllCheckbox) cb.checked = selectAllCheckbox.checked;
    });
    updateTotal();
  });

  plusBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const count = btn.parentElement.querySelector(".qty-count");
      count.textContent = parseInt(count.textContent) + 1;
      updateTotal();
    });
  });

  minusBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const count = btn.parentElement.querySelector(".qty-count");
      let newVal = parseInt(count.textContent) - 1;
      if (newVal >= 1) count.textContent = newVal;
      updateTotal();
    });
  });

  deleteBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      btn.closest(".cart-item").remove();
      updateTotal();
    });
  });

  itemCheckboxes.forEach(cb => cb.addEventListener("change", updateTotal));
  updateTotal();
});
