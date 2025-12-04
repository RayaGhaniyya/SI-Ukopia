<?php
include("../../../Koneksi/koneksi.php");

$id = $_GET['id'] ?? 0;


$query = mysqli_query($conn, "
    SELECT t.*, 
    a.nama_penerima, a.no_telepon, a.alamat_lengkap, a.kota, a.provinsi, a.kode_pos, 
    c.nama as nama_user, c.email
    FROM transaksi t
    JOIN akun_customer c ON t.uid_customer = c.uid
    JOIN alamat_customer a ON t.id_alamat_kirim = a.id_alamat
    WHERE t.id_transaksi = '$id'
");
$trx = mysqli_fetch_assoc($query);

if (!$trx) {
    echo "<div class='p-4 text-center text-danger'>Data tidak ditemukan</div>";
    exit;
}


$items = mysqli_query($conn, "
    SELECT dt.*, p.nama_produk, s.ukuran, g.nama_grind, p.gambar_url
    FROM detail_transaksi dt
    JOIN detail_produk dp ON dt.id_detail_produk = dp.id_detail_produk
    JOIN produk p ON dp.id_produk = p.id_produk
    JOIN size s ON dp.id_size = s.id_size
    LEFT JOIN grind_size g ON dp.id_grind = g.id_grind
    WHERE dt.id_transaksi = '$id'
");
?>

<div class="detail-header-grid">

    <div class="info-group">
        <h6><i class="fas fa-receipt me-2"></i> Info Pesanan</h6>
        <div class="info-item"><span class="info-label">Invoice</span><span>#<?= $trx['id_transaksi'] ?></span></div>
        <div class="info-item"><span class="info-label">Tanggal</span><span><?= date('d M Y, H:i', strtotime($trx['tanggal_pesan'])) ?></span></div>
        <div class="info-item"><span class="info-label">Status</span><span class="badge bg-info text-dark"><?= $trx['status_pesanan'] ?></span></div>
        <div class="mt-3">
            <span class="d-block text-muted small mb-1">Customer:</span>
            <strong><?= htmlspecialchars($trx['nama_user']) ?></strong> <br>
            <small class="text-muted"><?= htmlspecialchars($trx['email']) ?></small>
        </div>
    </div>

    <div class="info-group">
        <h6><i class="fas fa-shipping-fast me-2"></i> Pengiriman</h6>
        <div class="info-item"><span class="info-label">Penerima</span><span><strong><?= htmlspecialchars($trx['nama_penerima']) ?></strong></span></div>
        <div class="info-item"><span class="info-label">Telepon</span><span><?= htmlspecialchars($trx['no_telepon']) ?></span></div>
        <div class="info-item">
            <span class="info-label">Alamat</span>
            <span>
                <?= htmlspecialchars($trx['alamat_lengkap']) ?><br>
                <?= htmlspecialchars($trx['kota']) ?>, <?= htmlspecialchars($trx['provinsi']) ?> <?= htmlspecialchars($trx['kode_pos']) ?>
            </span>
        </div>
        <?php if (!empty($trx['catatan_pesanan'])): ?>
            <div class="alert alert-warning mt-2 mb-0 py-2 px-3 small">Catatan: "<?= htmlspecialchars($trx['catatan_pesanan']) ?>"</div>
        <?php endif; ?>
    </div>
</div>

<h6 class="fw-bold mb-3">Rincian Barang</h6>
<div class="table-responsive">
    <table class="trx-product-table">
        <thead>
            <tr>
                <th width="50%">Produk</th>
                <th width="15%" class="text-center">Qty</th>
                <th width="15%" class="text-end">Harga</th>
                <th width="20%" class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = mysqli_fetch_assoc($items)):
                $img = str_replace("localhost", $_SERVER['HTTP_HOST'], $item['gambar_url']);
            ?>
                <tr>
                    <td>
                        <img src="<?= $img ?>" alt="Img" class="trx-product-img">
                        <div style="display: inline-block; vertical-align: middle;">
                            <span class="d-block fw-bold text-dark"><?= htmlspecialchars($item['nama_produk']) ?></span>
                            <span class="d-block text-muted small"><?= htmlspecialchars($item['ukuran']) ?> <?= $item['nama_grind'] ? '(' . $item['nama_grind'] . ')' : '' ?></span>
                        </div>
                    </td>
                    <td class="text-center"><?= $item['jumlah'] ?></td>
                    <td class="text-end">Rp <?= number_format($item['harga_saat_beli'], 0, ',', '.') ?></td>
                    <td class="text-end fw-bold">Rp <?= number_format($item['harga_saat_beli'] * $item['jumlah'], 0, ',', '.') ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="detail-footer">
    <div class="summary-box">
        <div class="summary-row"><span>Total Harga Barang</span><span>Rp <?= number_format($trx['total_harga_barang'], 0, ',', '.') ?></span></div>
        <div class="summary-row"><span>Ongkos Kirim</span><span>Rp <?= number_format($trx['ongkir'], 0, ',', '.') ?></span></div>

        <?php
        $layanan = $trx['total_pembayaran'] - ($trx['total_harga_barang'] + $trx['ongkir']);
        if ($layanan > 0):
        ?>
            <div class="summary-row"><span>Biaya Layanan</span><span>Rp <?= number_format($layanan, 0, ',', '.') ?></span></div>
        <?php endif; ?>

        <div class="summary-row total">
            <span>TOTAL</span>
            <span class="text-highlight">Rp <?= number_format($trx['total_pembayaran'], 0, ',', '.') ?></span>
        </div>
    </div>
</div>

<div class="form-actions">
    <?php if ($trx['status_pesanan'] == 'Sudah Dibayar'): ?>
        <button class="btn btn-primary" onclick="updateStatus(<?= $id ?>, 'Diproses')"><i class="fas fa-box"></i> Proses Pesanan</button>
    <?php elseif ($trx['status_pesanan'] == 'Diproses'): ?>
        <button class="btn btn-info text-white" onclick="updateStatus(<?= $id ?>, 'Dikirim')"><i class="fas fa-truck"></i> Kirim Barang</button>
    <?php elseif ($trx['status_pesanan'] == 'Pengajuan Batal'): ?>
        <button class="btn btn-danger" onclick="updateStatus(<?= $id ?>, 'Batal')"><i class="fas fa-check"></i> Setujui Batal</button>
    <?php endif; ?>

    <button class="btn btn-secondary" onclick="closeDetailModal()">Tutup</button>
</div>