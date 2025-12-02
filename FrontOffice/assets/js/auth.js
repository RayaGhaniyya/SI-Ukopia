document.addEventListener("DOMContentLoaded", () => {
    
    // --- (1) LOGIKA SLIDESHOW (SAMA) ---
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

    // --- (2) LOGIKA SHOW/HIDE PASSWORD (SAMA) ---
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
    initTogglePassword('password', 'togglePassword');
    initTogglePassword('password_reg', 'togglePasswordReg');
    initTogglePassword('confirm_password', 'toggleConfirmPassword');
    initTogglePassword('password_new', 'togglePasswordNew');
    initTogglePassword('confirm_password_new', 'toggleConfirmNew');


    // --- (3) LOGIKA SLIDING FORMS (SAMA) ---
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

    // --- (4) LOGIKA NOTIFIKASI DARI URL (UPDATE PAKE TOAST) ---
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('status')) {
        const status = urlParams.get('status'); // 'success' atau 'error'
        const message = urlParams.get('message') || 'Terjadi kesalahan.';
        
        // Panggil showToast (dari file toast.js)
        // Pastikan type-nya sesuai dengan css toast ('success' / 'error')
        if (typeof showToast === 'function') {
            showToast(message, status);
        } else {
            alert(message); // Fallback
        }

        // Hapus parameter URL agar bersih
        const view = urlParams.get('view');
        let cleanURL = window.location.pathname + (view ? '?view=' + view : '');
        window.history.pushState(null, '', cleanURL);
    }


    // --- (5) LOGIKA VALIDASI FORM REGISTER (UPDATE PAKE TOAST) ---
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password_reg').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault(); 
                showToast('Password dan Konfirmasi tidak cocok.', 'error');
                return;
            }
            if (password.length < 8) {
                e.preventDefault(); 
                showToast('Password minimal 8 karakter.', 'error');
                return;
            }
            if (!/[A-Z]/.test(password)) {
                e.preventDefault();
                showToast('Password harus ada huruf besar (A-Z).', 'error');
                return;
            }
            // (Opsional: Tambah validasi huruf kecil jika perlu)
        });
    }
    
    // --- (6) VALIDASI FORM RESET PASSWORD (UPDATE PAKE TOAST) ---
    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password_new').value;
            const confirmPassword = document.getElementById('confirm_password_new').value;

            if (password !== confirmPassword) {
                e.preventDefault(); 
                showToast('Password baru tidak cocok.', 'error');
                return;
            }
            if (password.length < 8) {
                e.preventDefault(); 
                showToast('Password minimal 8 karakter.', 'error');
                return;
            }
        });
    }
});