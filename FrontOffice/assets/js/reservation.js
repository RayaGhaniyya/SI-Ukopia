// reservation.js — robust typing + interactivity (safe checks)
document.addEventListener("DOMContentLoaded", () => {

  /* -------------------------
     Dropdown Time Picker
     ------------------------- */
  document.querySelectorAll(".custom-dropdown").forEach(drop => {
    const btn = drop.querySelector(".dropdown-btn");
    const items = drop.querySelectorAll(".dropdown-list li");
    if (!btn) return;

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      // close other open dropdowns
      document.querySelectorAll(".custom-dropdown.active").forEach(d => { if (d !== drop) d.classList.remove("active"); });
      drop.classList.toggle("active");
    });

    items.forEach(item => {
      item.addEventListener("click", () => {
        btn.textContent = item.textContent;
        drop.classList.remove("active");
      });
    });
  });

  // close dropdowns when clicking outside
  document.addEventListener("click", () => {
    document.querySelectorAll(".custom-dropdown.active").forEach(d => d.classList.remove("active"));
  });


  /* -------------------------
     Input focus visual (no inline-style permanent)
     ------------------------- */
  document.querySelectorAll(".input-group input").forEach(input => {
    input.addEventListener("focus", () => {
      input.classList.add("focused"); // CSS handles drop-shadow
    });
    input.addEventListener("blur", () => {
      input.classList.remove("focused");
    });
  });


  /* -------------------------
     Days buttons (single active)
     ------------------------- */
  const dayButtons = document.querySelectorAll(".days button");
  if (dayButtons.length) {
    dayButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        dayButtons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
      });
    });
  }


  /* -------------------------
     Typing Effect for left-content paragraph
     - robust: uses textContent, preserves wrapping via CSS
     - small speed tweak: faster on spaces to feel natural
     ------------------------- */
  (function initTyping() {
    const para = document.querySelector(".left-content .text-box p");
    if (!para) return;

    const original = para.textContent.replace(/\s+/g, ' ').trim(); // normalize whitespace
    para.textContent = ""; // clear for typing

    let idx = 0;
    const baseSpeed = 28; // ms per char
    function step() {
      if (idx >= original.length) return;
      const ch = original.charAt(idx);
      para.textContent += ch;
      idx++;
      // small acceleration on spaces for natural feel
      const delay = (ch === ' ') ? 8 : baseSpeed;
      setTimeout(step, delay);
    }

    // small delay before starting
    setTimeout(step, 480);
  })();


  /* -------------------------
     Form submit -> custom popup (no default alert)
     ------------------------- */
const form = document.querySelector(".form-box form");
if (form) {
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    // buat popup
    const popup = document.createElement("div");
    popup.className = "reservation-popup";
    popup.textContent = "✅ Reservasi berhasil dibuat! Terima kasih sudah memilih Ukopia ☕";
    document.body.appendChild(popup);

    // trigger animasi masuk
    setTimeout(() => popup.classList.add("show"), 50);

    // animasi keluar setelah 3 detik
    setTimeout(() => {
      popup.classList.remove("show");
      setTimeout(() => popup.remove(), 400); // hapus dari DOM
    }, 3000);

    form.reset();
  });
}


});
