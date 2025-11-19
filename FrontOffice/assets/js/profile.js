/* =================================
   Logika Halaman Profil
   ================================= */

// Fungsi Toggle Password
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

document.addEventListener("DOMContentLoaded", () => {
    
    initTogglePassword('oldPassword', 'toggleOldPassword');
    initTogglePassword('newPassword', 'toggleNewPassword');
    initTogglePassword('confirmNewPassword', 'toggleConfirmNewPassword');

    // --- 1. Edit Data Diri ---
    const editBtn = document.getElementById("editProfileBtn");
    const namaInput = document.getElementById("namaLengkap");
    const usernameInput = document.getElementById("username");

    if (editBtn) {
        editBtn.addEventListener("click", () => {
            const isEditing = editBtn.textContent.trim() === "Edit";
            if (isEditing) {
                namaInput.disabled = false;
                usernameInput.disabled = false;
                editBtn.textContent = "Simpan";
                editBtn.classList.remove("btn-dark");
                editBtn.classList.add("btn-success"); 
            } else {
                submitProfileForm();
            }
        });
    }

    async function submitProfileForm() {
        const formData = new FormData();
        formData.append('nama', namaInput.value);
        formData.append('username', usernameInput.value);

        editBtn.disabled = true;
        editBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const response = await fetch('action/update_profile.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                showToast(result.message, 'success');
                namaInput.disabled = true;
                usernameInput.disabled = true;
                editBtn.textContent = "Edit";
                editBtn.classList.remove("btn-success");
                editBtn.classList.add("btn-dark");
                document.querySelector('.profile-name-modern').textContent = namaInput.value;
                document.querySelector('.profile-username-modern').textContent = '@' + usernameInput.value;
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            showToast('Terjadi kesalahan jaringan.', 'error');
        } finally {
            editBtn.disabled = false;
            if (editBtn.textContent.includes('spinner')) editBtn.textContent = "Simpan";
        }
    }

    // --- 2. Ganti Email (2 Langkah) ---
    const step1Form = document.getElementById("emailStep1Form");
    const step2Form = document.getElementById("emailStep2Form");
    const btnBatalEmail = document.getElementById("btnBatalEmail");

    if (step1Form) {
        step1Form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const btn = step1Form.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Mengirim Kode...';

            const formData = new FormData(step1Form);

            try {
                const response = await fetch('action/request_email_code.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showToast('Kode terkirim! Cek inbox email baru Anda.', 'success');
                    document.getElementById('targetEmailDisplay').textContent = formData.get('new_email');
                    step1Form.style.display = 'none';
                    step2Form.style.display = 'block';
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Gagal menghubungi server.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }

    if (step2Form) {
        step2Form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const btn = step2Form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const formData = new FormData(step2Form);

            try {
                const response = await fetch('action/verify_email_change.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('emailModal'));
                    modal.hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan verifikasi.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Verifikasi & Simpan';
            }
        });
    }

    if (btnBatalEmail) {
        btnBatalEmail.addEventListener('click', () => {
            step2Form.style.display = 'none';
            step1Form.style.display = 'block';
        });
    }

    // --- 3. Ganti Password ---
    const passwordForm = document.getElementById("passwordChangeForm");
    if (passwordForm) {
        passwordForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmNewPassword').value;
            
            if (newPass !== confirmPass) {
                showToast('Password baru dan konfirmasi tidak cocok.', 'error');
                return;
            }

            const btn = passwordForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            const formData = new FormData(passwordForm);
            try {
                const response = await fetch('action/update_password.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
                    modal.hide();
                    passwordForm.reset();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan jaringan.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Simpan Password Baru';
            }
        });
    }
    
    // --- 4. Alamat Baru ---
    const alamatForm = document.getElementById("alamatChangeForm");
    if (alamatForm) {
       alamatForm.addEventListener("submit", async (e) => { 
            e.preventDefault();
            const btn = alamatForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            const formData = new FormData(alamatForm);
            try {
                const response = await fetch('action/manage_alamat.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    alamatForm.reset();
                    loadAlamatList(); 
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan jaringan.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Simpan Alamat';
            }
       });
    }

    // --- 5. Load Alamat ---
    const alamatContainer = document.getElementById('daftarAlamatContainer');
    
    async function loadAlamatList() {
        if (!alamatContainer) return;
        alamatContainer.innerHTML = '<p class="text-muted">Memuat alamat...</p>';

        try {
            const response = await fetch('action/get_alamat.php');
            const result = await response.json();

            if (result.success && result.alamat.length > 0) {
                alamatContainer.innerHTML = ''; 
                result.alamat.forEach(item => {
                    const isUtama = item.is_utama == 1 ? '<span class="badge bg-dark">Utama</span>' : '';
                    alamatContainer.innerHTML += `
                        <div class="address-item">
                            <div class="address-info">
                                <h6>${item.label_alamat} ${isUtama}</h6>
                                <p>${item.nama_penerima} | ${item.no_telepon}</p>
                                <p>${item.alamat_lengkap}, ${item.kota}, ${item.provinsi}, ${item.kode_pos}</p>
                            </div>
                            <div class="address-actions">
                                <button class="btn btn-outline-dark btn-sm" onclick="editAlamat(${item.id_alamat})">Ubah</button>
                                <button class="btn btn-outline-danger btn-sm" onclick="hapusAlamat(${item.id_alamat})">Hapus</button>
                            </div>
                        </div>
                    `;
                });
            } else if (result.success && result.alamat.length === 0) {
                alamatContainer.innerHTML = '<p class="text-muted text-center">Masih belum memiliki alamat.</p>';
            } else {
                alamatContainer.innerHTML = `<p class="text-danger">${result.message}</p>`;
            }
        } catch (error) {
            alamatContainer.innerHTML = '<p class="text-danger">Gagal memuat alamat.</p>';
        }
    }

    const collapseAlamat = document.getElementById('collapseAlamat');
    if (collapseAlamat) {
        collapseAlamat.addEventListener('shown.bs.collapse', function () {
            loadAlamatList();
        });
    }

    // --- 6. LOGOUT (CUSTOM CONFIRM) ---
    const logoutBtn = document.getElementById('logoutBtn');
    if(logoutBtn){
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Gunakan parameter untuk mengubah teks popup
            showConfirm(
                'Yakin ingin keluar dari akun?', 
                () => { window.location.href = 'action/logout.php'; },
                "Konfirmasi Logout", 
                "Keluar"
            );
        });
    }
});

// --- Fungsi Global ---

function hapusAlamat(id) {
    // Gunakan default (Konfirmasi Hapus, Hapus)
    showConfirm('Yakin ingin menghapus alamat ini?', async () => {
        const formData = new FormData();
        formData.append('id_alamat', id);
        
        try {
            const response = await fetch('action/delete_alamat.php', { method: 'POST', body: formData });
            const result = await response.json();
    
            if (result.success) {
                showToast('Alamat berhasil dihapus.', 'success');
                document.getElementById('daftarAlamatContainer').innerHTML = '<p class="text-muted">Memuat ulang...</p>';
                document.querySelector('[href="#collapseAlamat"]').click(); 
                setTimeout(() => document.querySelector('[href="#collapseAlamat"]').click(), 300);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            showToast('Gagal menghapus alamat.', 'error');
        }
    });
}

function editAlamat(id) {
    showToast('Fitur "Ubah Alamat" belum diimplementasikan.', 'error');
}