/* ============================================
   GALLERY.JS - UKOPIA FRONTOFFICE
   Scroll Animation for Gallery Page
   ============================================ */

document.addEventListener("DOMContentLoaded", () => {
  const images = document.querySelectorAll(".galery-images img");
  const texts = document.querySelectorAll(".galery-text");

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("show");
        }
      });
    },
    { 
      threshold: 0.2,
      rootMargin: "0px 0px -50px 0px" 
    }
  );

  images.forEach((img) => {
    observer.observe(img);
  });

  texts.forEach((txt) => {
    observer.observe(txt);
  });

  const paginationLinks = document.querySelectorAll('.pagination a');
  paginationLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  });
});

window.addEventListener('load', () => {
  const images = document.querySelectorAll('.galery-images img');
  images.forEach(img => {
    if (!img.complete) {
      img.style.opacity = '0';
      img.addEventListener('load', () => {
        img.style.transition = 'opacity 0.5s ease';
        img.style.opacity = '1';
      });
    }
  });
});
