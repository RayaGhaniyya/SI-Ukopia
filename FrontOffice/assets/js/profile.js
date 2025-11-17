/* =================================
   Fungsi Notifikasi (Wajib Ada)
   ================================= */
function showNotification(message, type = 'info') {
  const oldNotif = document.querySelector('.notification');
  if (oldNotif) oldNotif.remove();

  const notification = document.createElement('div');
  // [PERBAIKAN] Class disesuaikan (danger/alert)
  notification.className = `notification notification-${type}`;
  
  let icon = 'ℹ';
  if (type === 'success') icon = '✓';
  if (type === 'danger') icon = '✕';
  if (type === 'alert') icon = '⚠';
  
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

/* =================================
   Logika Halaman Profil
   ================================= */

// [BARU] Fungsi init toggle password (dipindah ke sini)
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
    
    // [BARU] Aktifkan semua tombol mata
    initTogglePassword('oldPassword', 'toggleOldPassword');
    initTogglePassword('newPassword', 'toggleNewPassword');
    initTogglePassword('confirmNewPassword', 'toggleConfirmNewPassword');

    // --- 1. Tombol Edit/Simpan Data Diri ---
    const editBtn = document.getElementById("editProfileBtn");
    const namaInput = document.getElementById("namaLengkap");
    const usernameInput = document.getElementById("username");
    const profileForm = document.getElementById("profileForm");

    if (editBtn) {
        editBtn.addEventListener("click", () => {
            const isEditing = editBtn.textContent.trim() === "Edit";

            if (isEditing) {
                // Ubah ke mode Edit
                namaInput.disabled = false;
                usernameInput.disabled = false;
                editBtn.textContent = "Simpan";
                editBtn.classList.remove("btn-dark");
                editBtn.classList.add("btn-success"); 
            } else {
                // Ubah ke mode Simpan (kirim data)
                submitProfileForm();
            }
        });
    }

    // Fungsi untuk kirim data profil (Nama & Username)
    async function submitProfileForm() {
        // Ambil data hanya dari input yang relevan (bukan semua form)
        const formData = new FormData();
        formData.append('nama', namaInput.value);
        formData.append('username', usernameInput.value);

        editBtn.disabled = true;
        editBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            // [PATH] Pastikan path 'action/update_profile.php' ini benar
            const response = await fetch('action/update_profile.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');
                // Kembalikan ke mode read-only
                namaInput.disabled = true;
                usernameInput.disabled = true;
                editBtn.textContent = "Edit";
                editBtn.classList.remove("btn-success");
                editBtn.classList.add("btn-dark");
                // [UPDATE NAMA] Perbarui nama di header jika berhasil
                document.querySelector('.profile-name-modern').textContent = namaInput.value;
                document.querySelector('.profile-username-modern').textContent = '@' + usernameInput.value;
            } else {
                showNotification(result.message, 'danger');
            }
        } catch (error) {
            showNotification('Terjadi kesalahan jaringan.', 'danger');
        } finally {
            editBtn.disabled = false;
            // Jika error, kembalikan teks tombol ke "Simpan"
            if (editBtn.textContent.includes('spinner')) {
                editBtn.textContent = "Simpan";
            }
        }
    }


    // --- 2. Submit Form Ganti Email (di Modal) ---
    const emailForm = document.getElementById("emailChangeForm");
    if (emailForm) {
        emailForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const btn = emailForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            const formData = new FormData(emailForm);

            try {
                // [PATH] Pastikan path 'action/update_email.php' ini benar
                const response = await fetch('action/update_email.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    // Tutup modal (jika Bootstrap 5 JS dimuat)
                    const modal = bootstrap.Modal.getInstance(document.getElementById('emailModal'));
                    modal.hide();
                    // Reload halaman untuk update email
                    setTimeout(() => location.reload(), 1500); 
                } else {
                    showNotification(result.message, 'danger');
                }
            } catch (error) {
                showNotification('Terjadi kesalahan jaringan.', 'danger');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Simpan Email Baru';
            }
        });
    }


    // --- 3. Submit Form Ganti Password (di Modal) ---
    const passwordForm = document.getElementById("passwordChangeForm");
    if (passwordForm) {
        passwordForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            
            // Validasi JS Sederhana
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = document.getElementById('confirmNewPassword').value;
            if (newPass !== confirmPass) {
                showNotification('Password baru dan konfirmasi tidak cocok.', 'danger');
                return;
            }

            const btn = passwordForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            const formData = new FormData(passwordForm);

            try {
                // [PATH] Pastikan path 'action/update_password.php' ini benar
                const response = await fetch('action/update_password.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
                    modal.hide();
                    passwordForm.reset();
                } else {
                    showNotification(result.message, 'danger');
                }
            } catch (error) {
                showNotification('Terjadi kesalahan jaringan.', 'danger');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Simpan Password Baru';
            }
        });
    }

    
    // --- 4. Submit Form Alamat Baru (di Accordion) ---
    const alamatForm = document.getElementById("alamatChangeForm");
    if (alamatForm) {
       alamatForm.addEventListener("submit", async (e) => { 
            e.preventDefault();
            const btn = alamatForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            const formData = new FormData(alamatForm);

            try {
                // [PATH] Pastikan path 'action/manage_alamat.php' ini benar
                const response = await fetch('action/manage_alamat.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    alamatForm.reset();
                    // Panggil fungsi untuk reload daftar alamat
                    loadAlamatList(); 
                } else {
                    showNotification(result.message, 'danger');
                }
            } catch (error) {
                showNotification('Terjadi kesalahan jaringan.', 'danger');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Simpan Alamat';
            }
       });
    }

    // --- 5. Fungsi untuk memuat daftar alamat ---
    const alamatContainer = document.getElementById('daftarAlamatContainer');
    
    async function loadAlamatList() {
        if (!alamatContainer) return;

        alamatContainer.innerHTML = '<p class="text-muted">Memuat alamat...</p>';

        try {
            // [PATH] Pastikan path 'action/get_alamat.php' ini benar
            const response = await fetch('action/get_alamat.php');
            const result = await response.json();

            if (result.success && result.alamat.length > 0) {
                alamatContainer.innerHTML = ''; // Kosongkan
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
                                <!-- (Fungsi ubah/hapus bisa ditambahkan nanti) -->
                                <button class="btn btn-outline-dark btn-sm" onclick="editAlamat(${item.id_alamat})">Ubah</button>
                                <button class="btn btn-outline-danger btn-sm" onclick="hapusAlamat(${item.id_alamat})">Hapus</button>
                            </div>
                        </div>
                    `;
                });
            } else if (result.success && result.alamat.length === 0) {
                // [PERMINTAAN] Tampilkan pesan jika belum ada alamat
                alamatContainer.innerHTML = '<p class="text-muted text-center">Masih belum memiliki alamat.</p>';
            } else {
                alamatContainer.innerHTML = `<p class="text-danger">${result.message}</p>`;
            }
        } catch (error) {
            alamatContainer.innerHTML = '<p class="text-danger">Gagal memuat alamat.</p>';
        }
    }

    // Panggil fungsi saat accordion alamat dibuka
    const collapseAlamat = document.getElementById('collapseAlamat');
    if (collapseAlamat) {
        collapseAlamat.addEventListener('shown.bs.collapse', function () {
            loadAlamatList();
        });
    }
});


/* =================================
   Fungsi Global (Hapus/Edit Alamat)
   (Diletakkan di luar 'DOMContentLoaded' agar bisa dipanggil 'onclick')
   ================================= */

async function hapusAlamat(id) {
    if (!confirm('Yakin ingin menghapus alamat ini?')) {
        return;
    }

    showNotification('Menghapus alamat...', 'alert');
    const formData = new FormData();
    formData.append('id_alamat', id);
    
    try {
        // [PATH] Pastikan path 'action/delete_alamat.php' ini benar
        const response = await fetch('action/delete_alamat.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showNotification('Alamat berhasil dihapus.', 'success');
            // Reload daftar alamat (memanggil fungsi di dalam DOMContentLoaded)
            // Trik: kita tidak bisa panggil 'loadAlamatList' dari sini, jadi kita refresh via DOM
            document.getElementById('daftarAlamatContainer').innerHTML = '<p class="text-muted">Memuat ulang...</p>';
            document.querySelector('[href="#collapseAlamat"]').click(); // Tutup
            document.querySelector('[href="#collapseAlamat"]').click(); // Buka lagi untuk refresh
        } else {
            showNotification(result.message, 'danger');
        }
    } catch (error) {
        showNotification('Gagal menghapus alamat.', 'danger');
    }
}

// (Fungsi editAlamat bisa dibuat nanti, biasanya memuat data ke form tambah)
function editAlamat(id) {
    showNotification('Fitur "Ubah Alamat" belum diimplementasikan.', 'alert');
    // Nanti: Ambil data by ID, isi ke form #alamatChangeForm, ubah tombol 'Simpan' jadi 'Update'
}