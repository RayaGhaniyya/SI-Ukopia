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
    { threshold: 0.2 }  
  );

  images.forEach((img) => observer.observe(img));
  texts.forEach((txt) => observer.observe(txt));
});
