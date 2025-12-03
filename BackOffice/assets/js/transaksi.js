

async function lihatDetail(id) {
    
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
        
        const response = await fetch(`detail.php?id=${id}`);
        const html = await response.text();
        
        
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


async function updateStatus(id, statusBaru) {
    if(!confirm(`Apakah Anda yakin ingin mengubah status pesanan #${id} menjadi "${statusBaru}"?`)) return;

    if (typeof showLoading === 'function') showLoading("Mengupdate status...");

    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', statusBaru);

        
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
            
            
            closeDetailModal();

            
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


function closeDetailModal() {
    const modalEl = document.getElementById('detailModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if(modal) modal.hide();
}

/* ============================================
   LOGIKA MANAJEMEN TRANSAKSI
   File: BackOffice/assets/js/transaksi.js
   ============================================ */


async function lihatDetail(id) {
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
        
        const response = await fetch(`detail.php?id=${id}`);
        const html = await response.text();
        
        
        document.getElementById('modalBodyContent').innerHTML = html;
    } catch (error) {
        document.getElementById('modalBodyContent').innerHTML = `
            <div class="alert alert-danger text-center">Gagal memuat data. Silakan coba lagi.</div>
        `;
        showNotification("Gagal memuat data", "error");
    }
}


async function updateStatus(id, statusBaru) {
    
    if(!confirm(`Apakah Anda yakin ingin mengubah status pesanan #${id} menjadi "${statusBaru}"?`)) return;

    
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

        
        hideLoading();

        if(result.success) {
            
            showNotification(result.message, "success");
            
            
            closeDetailModal();

            
            setTimeout(() => location.reload(), 1000);
        } else {
            
            showNotification(result.message, "error");
        }
    } catch (error) {
        hideLoading();
        console.error(error);
        showNotification("Terjadi kesalahan pada server.", "error");
    }
}


function closeDetailModal() {
    const modalEl = document.getElementById('detailModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if(modal) modal.hide();
}