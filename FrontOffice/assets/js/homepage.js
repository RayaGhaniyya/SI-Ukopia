// homepage.js — safe & defensive: cek dulu element sebelum dipakai
document.addEventListener("DOMContentLoaded", () => {
  /* ===== HERO: fade-in + typing effect (safe checks) ===== */
  try {
    const captionEl = document.querySelector(".hero-caption h3");
    if (captionEl) {
      const fullText = captionEl.textContent.trim();
      // kosongkan dulu untuk typing
      captionEl.textContent = "";

      const captionWrapper = captionEl.closest(".hero-caption");
      if (captionWrapper) {
        captionWrapper.style.opacity = "0";
        captionWrapper.style.transition = "opacity 1s ease";
      }

      // tampilkan wrapper dulu lalu mulai typing
      setTimeout(() => {
        if (captionWrapper) captionWrapper.style.opacity = "1";

        let i = 0;
        const speed = 150; 
        (function typeChar() {
          if (i < fullText.length) {
            captionEl.textContent += fullText.charAt(i);
            i++;
            setTimeout(typeChar, speed);
          }
        })();
      }, 450);
    }
  } catch (err) {
    // jangan crash; log untuk debugging di dev console
    console.error("Hero typing error:", err);
  }

  /* ===== GALLERY: scroll reveal (IntersectionObserver) ===== */
  try {
    const galleryItems = document.querySelectorAll(".gallery-item");
    if (galleryItems && galleryItems.length) {
      // Fallback: if IO not supported, reveal all
      if (!("IntersectionObserver" in window)) {
        galleryItems.forEach(it => it.classList.add("show"));
      } else {
        const io = new IntersectionObserver((entries, obs) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add("show");
              obs.unobserve(entry.target);
            }
          });
        }, { threshold: 0.15 });

        galleryItems.forEach(it => io.observe(it));
      }
    }
  } catch (err) {
    console.error("Gallery reveal error:", err);
  }

  /* ===== PRODUCTS: add/remove class on hover (no inline styles) ===== */
  try {
    const productCards = document.querySelectorAll(".product-card");
    productCards.forEach(card => {
      card.addEventListener("mouseenter", () => card.classList.add("is-hover"));
      card.addEventListener("mouseleave", () => card.classList.remove("is-hover"));
    });
  } catch (err) {
    console.error("Product hover error:", err);
  }

  /* ===== RESERVATION: button hover class (safe) ===== */
  try {
    const reserveBtn = document.querySelector(".btn-reserve");
    if (reserveBtn) {
      reserveBtn.addEventListener("mouseenter", () => reserveBtn.classList.add("is-hover"));
      reserveBtn.addEventListener("mouseleave", () => reserveBtn.classList.remove("is-hover"));
    }
  } catch (err) {
    console.error("Reserve button error:", err);
  }
});
