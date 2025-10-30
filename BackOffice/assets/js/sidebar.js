const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggleBtn");
const productBtn = document.getElementById("productBtn");
const productDropdown = document.getElementById("productDropdown");
const mobileBtn = document.getElementById("mobileBtn");
const mobileDropdown = document.getElementById("mobileDropdown");

// Tombol garis tiga (toggle sidebar)
toggleBtn.addEventListener("click", () => {
  sidebar.classList.toggle("collapsed");
  document.body.classList.toggle("sidebar-collapsed"); // TAMBAH INI - toggle class di body
});

// Dropdown Product tetap bisa dibuka meski sidebar ditutup
productBtn.addEventListener("click", (e) => {
  e.preventDefault();
  productDropdown.classList.toggle("show");
});
// Dropdown Mobile tetap bisa dibuka meski sidebar ditutup
mobileBtn.addEventListener("click", (e) => {
  e.preventDefault();
  mobileDropdown.classList.toggle("show");
});