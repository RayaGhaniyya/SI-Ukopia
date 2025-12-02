document.addEventListener("DOMContentLoaded", () => {
    
    function startCountdowns() {
        const timers = document.querySelectorAll('.countdown-timer');
        
        setInterval(() => {
            const now = new Date().getTime();
            
            timers.forEach(timer => {
                const deadlineAttr = timer.getAttribute('data-deadline');
                const deadline = new Date(deadlineAttr.replace(/-/g, '/')).getTime();
                
                const distance = deadline - now;

                if (distance < 0) {
                    timer.innerHTML = "Kadaluarsa";
                    timer.classList.remove('bg-danger');
                    timer.classList.add('bg-secondary');
                } else {
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    timer.innerHTML = `Sisa: ${minutes}m ${seconds}s`;
                }
            });
        }, 1000);
    }

    if (document.querySelectorAll('.countdown-timer').length > 0) {
        startCountdowns();
    }
});

/* =================================
   FUNGSI-FUNGSI AKSI (GLOBAL)
   ================================= */

function payNow(token) {
    if (!token) {
        showToast('Token pembayaran tidak valid.', 'error');
        return;
    }
    window.snap.pay(token, {
        onSuccess: function(result){ showToast("Pembayaran Berhasil!", "success"); setTimeout(() => location.reload(), 2000); },
        onPending: function(result){ showToast("Menunggu Pembayaran...", "warning"); setTimeout(() => location.reload(), 2000); },
        onError: function(result){ showToast("Pembayaran Gagal!", "error"); },
        onClose: function(){ showToast("Pembayaran belum selesai.", "warning"); }
    });
}

function completeOrder(id) {
    
    showConfirm("Apakah barang sudah diterima dengan baik?", async () => {
        const formData = new FormData();
        formData.append('id_transaksi', id);

        try {
            const response = await fetch('action/complete_order.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                showToast(result.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) { showToast("Terjadi kesalahan jaringan.", 'error'); }
    }, "Konfirmasi Penerimaan", "Ya, Sudah Diterima");
}

function cancelOrder(id) {
    const modalEl = document.getElementById('trxDetailModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if(modal) modal.hide();

    showConfirm("Yakin ingin membatalkan pesanan ini?", async () => {
        const formData = new FormData();
        formData.append('id_transaksi', id);
        formData.append('alasan', 'Dibatalkan oleh customer via web');

        try {
            const response = await fetch('action/cancel_order.php', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.status === 'success') {
                showToast(result.message, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) { showToast("Terjadi kesalahan jaringan.", 'error'); }
    }, "Konfirmasi Pembatalan", "Ya, Batalkan");
}

async function showDetail(id) {
    const modalEl = document.getElementById('trxDetailModal');
    const modal = new bootstrap.Modal(modalEl);
    const content = document.getElementById('detailContent');
    
    content.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Memuat detail...</p></div>';
    modal.show();

    try {
        const response = await fetch(`action/get_transaction_detail.php?id=${id}`);
        const result = await response.json();

        if (result.status === 'success') {
            const trx = result.transaksi;
            const items = result.items;
            const fmt = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
            const date = new Date(trx.tanggal_pesan).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute:'2-digit' });

            let badgeClass = 'bg-secondary';
            if (trx.status_pesanan === 'Menunggu Pembayaran') badgeClass = 'bg-warning text-dark';
            else if (trx.status_pesanan === 'Sudah Dibayar' || trx.status_pesanan === 'Diproses') badgeClass = 'bg-info text-dark';
            else if (trx.status_pesanan === 'Dikirim') badgeClass = 'bg-primary';
            else if (trx.status_pesanan === 'Selesai') badgeClass = 'bg-success';
            else if (trx.status_pesanan === 'Batal' || trx.status_pesanan === 'Kadaluarsa') badgeClass = 'bg-danger';

            let itemsHtml = '';
            items.forEach(item => {
                itemsHtml += `
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <img src="${item.gambar_url}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 15px;">
                        <div style="flex:1;">
                            <h6 class="mb-0 fw-bold">${item.nama_produk}</h6>
                            <small class="text-muted">${item.ukuran}${item.nama_grind ? ', ' + item.nama_grind : ''}</small>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-muted">${item.jumlah} x ${fmt(item.harga_saat_beli)}</span>
                                <span class="fw-bold">${fmt(item.jumlah * item.harga_saat_beli)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            const biayaLayanan = parseInt(trx.total_pembayaran) - (parseInt(trx.total_harga_barang) + parseInt(trx.ongkir));

            let actionButton = '';
            if (trx.status_pesanan === 'Menunggu Pembayaran') {
                actionButton = `
                    <hr>
                    <div class="d-flex justify-content-between">
                         <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(${trx.id_transaksi})">Batalkan</button>
                         <button class="btn btn-dark btn-sm" onclick="payNow('${trx.snap_token}')">Bayar Sekarang</button>
                    </div>
                `;
            } else if (trx.status_pesanan === 'Dikirim') {
                actionButton = `
                    <hr>
                    <div class="text-end">
                        <button class="btn btn-success btn-sm" onclick="completeOrder(${trx.id_transaksi})">
                            <i class="fas fa-check-circle"></i> Pesanan Diterima
                        </button>
                    </div>
                `;
            } else if (trx.status_pesanan === 'Sudah Dibayar') {
                 actionButton = `
                    <hr>
                    <div class="text-end">
                        <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(${trx.id_transaksi})">Batalkan Pesanan</button>
                    </div>
                `;
            }

            content.innerHTML = `
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted">No. Order</small>
                        <p class="fw-bold mb-0">#${trx.id_transaksi}</p>
                        <small class="text-muted" style="font-size:0.8rem;">Ref: ${trx.midtrans_order_id || '-'}</small>
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted">Tanggal Order</small>
                        <p class="fw-bold mb-0">${date}</p>
                    </div>
                </div>
                <div class="mb-4"><span class="badge ${badgeClass} px-3 py-2 rounded-pill" style="font-size: 0.9rem;">${trx.status_pesanan}</span></div>
                <div class="card mb-4 bg-light border-0">
                    <div class="card-body">
                        <h6 class="fw-bold"><i class="fas fa-map-marker-alt me-2"></i> Alamat Pengiriman</h6>
                        <p class="mb-1 fw-bold">${trx.nama_penerima} (${trx.no_telepon})</p>
                        <p class="mb-0 text-muted small">${trx.label_alamat}<br>${trx.alamat_lengkap}, ${trx.kota}, ${trx.provinsi} ${trx.kode_pos}</p>
                    </div>
                </div>
                <h6 class="fw-bold mb-3">Rincian Produk</h6>
                ${itemsHtml}
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Total Harga</span><span>${fmt(trx.total_harga_barang)}</span></div>
                    <div class="d-flex justify-content-between mb-1"><span class="text-muted">Ongkir</span><span>${fmt(trx.ongkir)}</span></div>
                    <div class="d-flex justify-content-between mb-3"><span class="text-muted">Layanan</span><span>${fmt(biayaLayanan)}</span></div>
                    <div class="d-flex justify-content-between border-top pt-3">
                        <h5 class="fw-bold">Total Bayar</h5><h5 class="fw-bold">${fmt(trx.total_pembayaran)}</h5>
                    </div>
                </div>
                ${actionButton}
            `;
        } else { content.innerHTML = `<div class="alert alert-danger">${result.message}</div>`; }
    } catch (error) { content.innerHTML = `<div class="alert alert-danger">Gagal memuat data.</div>`; }
}
