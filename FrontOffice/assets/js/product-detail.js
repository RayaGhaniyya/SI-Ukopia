const plus = document.querySelector('.plus');
const minus = document.querySelector('.minus');
const count = document.querySelector('.count');
let qty = 1;

plus.addEventListener('click', () => {
  qty++;
  count.textContent = qty;
});

minus.addEventListener('click', () => {
  if (qty > 1) {
    qty--;
    count.textContent = qty;
  }
});

// Menangani tombol opsi produk (aktif/nonaktif)
document.querySelectorAll('.product-options button').forEach(btn => {
  btn.addEventListener('click', () => {
    const group = btn.parentElement.querySelectorAll('button');
    group.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  });
});
