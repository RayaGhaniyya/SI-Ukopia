// File: FrontOffice/assets/js/auth.js
// V14 (Sudah digabung dengan notifikasi + logika lupa password)

// 
// VVVVV--- (A) FUNGSI NOTIFIKASI (DARI GLOBAL.JS) ---VVVVV
// 
function showNotification(message, type = 'info') {
  const oldNotif = document.querySelector('.notification');
  if (oldNotif) oldNotif.remove();

  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  
  let icon = 'ℹ';
  if (type === 'success') icon = '✓';
  if (type === 'error') icon = '✕';
  if (type === 'warning') icon = '⚠';
  
  notification.innerHTML = `
    <div class="notification-content">
      <span class="notification-icon">${icon}</span>
      <span class="notification-message">${message}</span>
      <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
    </div>
  `;
  
  document.body.appendChild(notification);
  setTimeout(() => notification.classList.add('show'), 10);
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => notification.remove(), 300);
  }, 4000);
}
// ^^^^^--- SELESAI BLOK A ---^^^^^


document.addEventListener("DOMContentLoaded", () => {
    
    // --- (1) LOGIKA SLIDESHOW ---
    const slides = document.querySelectorAll(".auth-slideshow .slide");
    if (slides.length > 0) {
        let currentSlide = 0;
        function showSlide(index) {
            slides.forEach((slide) => slide.classList.remove('is-active'));
            slides[index].classList.add('is-active');
        }
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        showSlide(0); 
        setInterval(nextSlide, 6000); 
    }

    // --- (2) LOGIKA SHOW/HIDE PASSWORD (SEMUA FORM) ---
    function initTogglePassword(inputId, toggleId) {
        const input = document.getElementById(inputId);
        const toggle = document.getElementById(toggleId);
        if (input && toggle) {
            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
                this.classList.toggle('fa-eye');
            });
        }
    }
    initTogglePassword('password', 'togglePassword'); // Form Login
    initTogglePassword('password_reg', 'togglePasswordReg'); // Form Register
    initTogglePassword('confirm_password', 'toggleConfirmPassword'); // Form Register
    initTogglePassword('password_new', 'togglePasswordNew'); // Form Lupa Password
    initTogglePassword('confirm_password_new', 'toggleConfirmNew'); // Form Lupa Password


    // --- (3) LOGIKA SLIDING FORMS (LOGIN/REGISTER) ---
    const authWrapper = document.querySelector('.auth-wrapper');
    const toggleToRegisterBtn = document.getElementById('toggleToRegister');
    const toggleToLoginBtn = document.getElementById('toggleToLogin');
    if (authWrapper && toggleToRegisterBtn && toggleToLoginBtn) {
        toggleToRegisterBtn.addEventListener('click', (e) => {
            e.preventDefault(); 
            authWrapper.classList.add('is-register-view');
        });
        toggleToLoginBtn.addEventListener('click', (e) => {
            e.preventDefault(); 
            authWrapper.classList.remove('is-register-view');
        });
    }

    // 
    // VVVVV--- (B) LOGIKA NOTIFIKASI DARI URL ---VVVVV
    // 
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status') && urlParams.get('status') === 'success') {
        const message = urlParams.get('message') || 'Operasi berhasil!';
        showNotification(message, 'success');
        
        // Hapus parameter dari URL
        const view = urlParams.get('view');
        let cleanURL = window.location.pathname + (view ? '?view=' + view : '');
        window.history.pushState(null, '', cleanURL);
    }
    if (urlParams.has('status') && urlParams.get('status') === 'error') {
        const message = urlParams.get('message') || 'Terjadi kesalahan.';
        showNotification(message, 'error');
        
        const view = urlParams.get('view');
        let cleanURL = window.location.pathname + (view ? '?view=' + view : '');
        window.history.pushState(null, '', cleanURL);
    }
    // ^^^^^--- SELESAI BLOK B ---^^^^^


    // 
    // VVVVV--- (C) LOGIKA VALIDASI FORM (CLIENT-SIDE) ---VVVVV
    //
    // (Validasi Form Register)
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password_reg').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault(); 
                showNotification('Password dan Konfirmasi Password tidak cocok.', 'error');
                return;
            }
            if (password.length < 8) {
                e.preventDefault(); 
                showNotification('Password minimal harus 8 karakter.', 'error');
                return;
            }
            if (!/[A-Z]/.test(password)) {
                e.preventDefault();
                showNotification('Password harus memiliki minimal 1 huruf besar (A-Z).', 'error');
                return;
            }
            if (!/[a-z]/.test(password)) {
                e.preventDefault();
                showNotification('Password harus memiliki minimal 1 huruf kecil (a-z).', 'error');
                return;
            }
        });
    }
    
    // (Validasi Form Lupa Password)
    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password_new').value;
            const confirmPassword = document.getElementById('confirm_password_new').value;

            if (password !== confirmPassword) {
                e.preventDefault(); 
                showNotification('Password baru tidak cocok.', 'error');
                return;
            }
            if (password.length < 8) {
                e.preventDefault(); 
                showNotification('Password minimal harus 8 karakter.', 'error');
                return;
            }
            if (!/[A-Z]/.test(password)) {
                e.preventDefault();
                showNotification('Password harus memiliki minimal 1 huruf besar (A-Z).', 'error');
                return;
            }
            if (!/[a-z]/.test(password)) {
                e.preventDefault();
                showNotification('Password harus memiliki minimal 1 huruf kecil (a-z).', 'error');
                return;
            }
        });
    }
    // ^^^^^--- SELESAI BLOK C ---^^^^^
});