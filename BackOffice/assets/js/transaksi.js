/* ============================================
   LOGIKA MANAJEMEN TRANSAKSI
   File: BackOffice/assets/js/transaksi.js
   ============================================ */

// 1. Fungsi Lihat Detail (Membuka Modal)
async function lihatDetail(id) {
    // Tampilkan Modal dengan Loading
    const modalEl = document.getElementById('detailModal');
    const modal = new bootstrap.Modal(modalEl);
    
    document.getElementById('modalBodyContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat data transaksi...</p>
        </div>
    `;
    
    modal.show();

    try {
        // Ambil konten dari detail.php
        const response = await fetch(`detail.php?id=${id}`);
        const html = await response.text();
        
        // Masukkan ke dalam modal
        document.getElementById('modalBodyContent').innerHTML = html;
    } catch (error) {
        document.getElementById('modalBodyContent').innerHTML = `
            <div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>
        `;
        if (typeof showNotification === 'function') {
            showNotification("Gagal memuat data", "error");
        }
    }
}

// 2. Fungsi Update Status
async function updateStatus(id, statusBaru) {
    if(!confirm(`Apakah Anda yakin ingin mengubah status pesanan #${id} menjadi "${statusBaru}"?`)) return;

    if (typeof showLoading === 'function') showLoading("Mengupdate status...");

    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', statusBaru);

        // Kirim ke Backend
        const response = await fetch('action/update_status.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (typeof hideLoading === 'function') hideLoading();

        if(result.success) {
            if (typeof showNotification === 'function') {
                showNotification(result.message, "success");
            }
            
            // Tutup modal
            closeDetailModal();

            // Reload halaman otomatis
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof showNotification === 'function') {
                showNotification(result.message, "error");
            } else {
                alert(result.message);
            }
        }
    } catch (error) {
        if (typeof hideLoading === 'function') hideLoading();
        console.error(error);
        alert("Terjadi kesalahan pada server.");
    }
}

// 3. Fungsi Tutup Modal Manual (Helper)
function closeDetailModal() {
    const modalEl = document.getElementById('detailModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if(modal) modal.hide();
}

/* ============================================
   LOGIKA MANAJEMEN TRANSAKSI
   File: BackOffice/assets/js/transaksi.js
   ============================================ */

// 1. Fungsi Lihat Detail (Membuka Modal)
async function lihatDetail(id) {
    const modalEl = document.getElementById('detailModal');
    const modal = new bootstrap.Modal(modalEl);
    
    // Tampilkan Loading di dalam Modal
    document.getElementById('modalBodyContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Memuat data transaksi...</p>
        </div>
    `;
    
    modal.show();

    try {
        // Fetch konten HTML dari detail.php
        const response = await fetch(`detail.php?id=${id}`);
        const html = await response.text();
        
        // Masukkan ke body modal
        document.getElementById('modalBodyContent').innerHTML = html;
    } catch (error) {
        document.getElementById('modalBodyContent').innerHTML = `
            <div class="alert alert-danger text-center">Gagal memuat data. Silakan coba lagi.</div>
        `;
        showNotification("Gagal memuat data", "error");
    }
}

// 2. Fungsi Update Status (Proses Utama)
async function updateStatus(id, statusBaru) {
    // Konfirmasi dulu
    if(!confirm(`Apakah Anda yakin ingin mengubah status pesanan #${id} menjadi "${statusBaru}"?`)) return;

    // Tampilkan Loading Global
    showLoading("Mengupdate status...");

    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', statusBaru);

        const response = await fetch('action/update_status.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        // Sembunyikan Loading Global
        hideLoading();

        if(result.success) {
            // Notifikasi SUKSES (Dari Global.js)
            showNotification(result.message, "success");
            
            // Tutup Modal Detail
            closeDetailModal();

            // Reload halaman agar tabel terupdate
            setTimeout(() => location.reload(), 1000);
        } else {
            // Notifikasi ERROR (Dari Global.js)
            showNotification(result.message, "error");
        }
    } catch (error) {
        hideLoading();
        console.error(error);
        showNotification("Terjadi kesalahan pada server.", "error");
    }
}

// 3. Helper: Tutup Modal Manual
function closeDetailModal() {
    const modalEl = document.getElementById('detailModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if(modal) modal.hide();
}