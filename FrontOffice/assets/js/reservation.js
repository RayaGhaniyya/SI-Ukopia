document.addEventListener("DOMContentLoaded", () => {

  document.querySelectorAll(".custom-dropdown").forEach(drop => {
    const btn = drop.querySelector(".dropdown-btn");
    const items = drop.querySelectorAll(".dropdown-list li");
    if (!btn) return;

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
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

  document.addEventListener("click", () => {
    document.querySelectorAll(".custom-dropdown.active").forEach(d => d.classList.remove("active"));
  });

  document.querySelectorAll(".input-group input").forEach(input => {
    input.addEventListener("focus", () => {
      input.classList.add("focused"); 
    });
    input.addEventListener("blur", () => {
      input.classList.remove("focused");
    });
  });

  const dayButtons = document.querySelectorAll(".days button");
  if (dayButtons.length) {
    dayButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        dayButtons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
      });
    });
  }

  (function initTyping() {
    const para = document.querySelector(".left-content .text-box p");
    if (!para) return;

    const original = para.textContent.replace(/\s+/g, ' ').trim(); 
    para.textContent = ""; 

    let idx = 0;
    const baseSpeed = 28; 
    function step() {
      if (idx >= original.length) return;
      const ch = original.charAt(idx);
      para.textContent += ch;
      idx++;
      const delay = (ch === ' ') ? 8 : baseSpeed;
      setTimeout(step, delay);
    }

    setTimeout(step, 480);
  })();


const form = document.querySelector(".form-box form");
if (form) {
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const popup = document.createElement("div");
    popup.className = "reservation-popup";
    popup.textContent = "✅ Reservasi berhasil dibuat! Terima kasih sudah memilih Ukopia ☕";
    document.body.appendChild(popup);

    setTimeout(() => popup.classList.add("show"), 50);

    setTimeout(() => {
      popup.classList.remove("show");
      setTimeout(() => popup.remove(), 400);
    }, 3000);

    form.reset();
  });
}

});
