document.addEventListener("DOMContentLoaded", () => {
  try {
    const captionEl = document.querySelector(".hero-caption h3");
    if (captionEl) {
      const fullText = captionEl.textContent.trim();
      captionEl.textContent = "";
      const captionWrapper = captionEl.closest(".hero-caption");
      if (captionWrapper) {
        captionWrapper.style.opacity = "0";
        captionWrapper.style.transition = "opacity 1s ease";
      }
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
    console.error("Hero typing error:", err);
  }

  try {
    const galleryContainer = document.querySelector(".gallery");
    if (galleryContainer) {
      if (!("IntersectionObserver" in window)) {
        galleryContainer.classList.add("is-visible");
      } else {
        const galleryObserver = new IntersectionObserver((entries, observer) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const items = entry.target.querySelectorAll('.gallery-item');
              items.forEach((item, index) => {
                item.style.transitionDelay = `${index * 50}ms`;
              });
              entry.target.classList.add("is-visible");
              observer.unobserve(entry.target);
            }
          });
        }, { threshold: 0.1 });
        galleryObserver.observe(galleryContainer);
      }
    }
  } catch (err) {
    console.error("Gallery reveal error:", err);
  }

  try {
    const productsContainer = document.querySelector(".products");
    if (productsContainer) {
      if (!("IntersectionObserver" in window)) {
        productsContainer.classList.add("is-visible");
      } else {
        const productsObserver = new IntersectionObserver((entries, observer) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add("is-visible");
              observer.unobserve(entry.target);
            }
          });
        }, { threshold: 0.1 });
        productsObserver.observe(productsContainer);
      }
    }
  } catch (err) {
    console.error("Products reveal error:", err);
  }

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