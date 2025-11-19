<?php
// Pastikan koneksi dan ID Produk tersedia dari parent
if (!isset($db_connection) || !isset($id_produk)) {
    return;
}

// Inisialisasi Variabel User Review (Default Kosong)
$userReview = null;
$isEditing = false;
$ratingValue = 0;
$komentarValue = "";

// Cek apakah user sedang login?
if (isset($_SESSION['customer_uid'])) {
    $uid_akun = $_SESSION['customer_uid'];

    // Cek di database apakah user ini sudah pernah review produk ini?
    $checkReview = mysqli_query($db_connection, "SELECT * FROM ulasan_produk WHERE id_produk = '$id_produk' AND uid_akun = '$uid_akun'");

    if (mysqli_num_rows($checkReview) > 0) {
        $userReview = mysqli_fetch_assoc($checkReview);
        $isEditing = true; // Tandai bahwa ini mode EDIT
        $ratingValue = $userReview['rating'];
        $komentarValue = $userReview['komentar'];
    }
}

/* ==========================================================
   LOGIKA 1: PROSES INSERT (ULASAN BARU)
   ========================================================== */
if (isset($_POST['kirim_ulasan'])) {
    if (!isset($_SESSION['customer_uid'])) {
        echo "<script>window.location.href='../auth/login.php';</script>";
        exit;
    }

    $uid_akun = $_SESSION['customer_uid'];
    $rating = intval($_POST['rating']);
    $komentar = mysqli_real_escape_string($db_connection, $_POST['komentar']);
    $tanggal = date('Y-m-d H:i:s');

    if ($rating == 0 || empty($komentar)) {
        // Error Validation (Tampil langsung karena page reload)
        echo "<script>document.addEventListener('DOMContentLoaded', () => showToast('Mohon isi rating bintang dan komentar!', 'error'));</script>";
    } else {
        $queryInsert = "INSERT INTO ulasan_produk (id_produk, uid_akun, rating, komentar, tanggal) 
                        VALUES ('$id_produk', '$uid_akun', '$rating', '$komentar', '$tanggal')";

        if (mysqli_query($db_connection, $queryInsert)) {
            // Sukses (Simpan pesan ke Storage lalu Redirect)
            echo "<script>
                localStorage.setItem('toast_msg', 'Terima kasih! Ulasan berhasil dikirim.');
                localStorage.setItem('toast_type', 'success');
                window.location.href='" . $_SERVER['REQUEST_URI'] . "';
            </script>";
            exit;
        } else {
            echo "<script>document.addEventListener('DOMContentLoaded', () => showToast('Gagal mengirim: " . mysqli_error($db_connection) . "', 'error'));</script>";
        }
    }
}

/* ==========================================================
   LOGIKA 2: PROSES UPDATE (EDIT ULASAN)
   ========================================================== */
if (isset($_POST['update_ulasan'])) {
    if (!isset($_SESSION['customer_uid'])) {
        echo "<script>window.location.href='../auth/login.php';</script>";
        exit;
    }

    $uid_akun = $_SESSION['customer_uid'];
    $id_ulasan = intval($_POST['id_ulasan']); // Ambil ID ulasan yang mau diedit
    $rating = intval($_POST['rating']);
    $komentar = mysqli_real_escape_string($db_connection, $_POST['komentar']);
    $tanggal = date('Y-m-d H:i:s');

    if ($rating == 0 || empty($komentar)) {
        echo "<script>document.addEventListener('DOMContentLoaded', () => showToast('Mohon isi rating bintang dan komentar!', 'error'));</script>";
    } else {
        // Query UPDATE
        $queryUpdate = "UPDATE ulasan_produk SET rating = '$rating', komentar = '$komentar', tanggal = '$tanggal' 
                        WHERE id_ulasan = '$id_ulasan' AND uid_akun = '$uid_akun'";

        if (mysqli_query($db_connection, $queryUpdate)) {
            // Sukses Update
            echo "<script>
                localStorage.setItem('toast_msg', 'Ulasan berhasil diperbarui!');
                localStorage.setItem('toast_type', 'success');
                window.location.href='" . $_SERVER['REQUEST_URI'] . "';
            </script>";
            exit;
        } else {
            echo "<script>document.addEventListener('DOMContentLoaded', () => showToast('Gagal update: " . mysqli_error($db_connection) . "', 'error'));</script>";
        }
    }
}

/* ==========================================================
   LOGIKA 3: AMBIL DATA GLOBAL (LIST REVIEW & RATA-RATA)
   ========================================================== */
$queryUlasan = mysqli_query($db_connection, "
    SELECT u.*, a.nama 
    FROM ulasan_produk u 
    JOIN akun_customer a ON u.uid_akun = a.uid 
    WHERE u.id_produk = '$id_produk' 
    ORDER BY u.tanggal DESC
");

$queryRating = mysqli_query($db_connection, "SELECT AVG(rating) as rata_rata, COUNT(*) as total FROM ulasan_produk WHERE id_produk = '$id_produk'");
$dataRating = mysqli_fetch_assoc($queryRating);
$rataRata = isset($dataRating['rata_rata']) ? round($dataRating['rata_rata'], 1) : 0;
$totalUlasan = $dataRating['total'];
?>

<div class="review-section-wrapper">

    <div class="review-header">
        <h3>Ulasan Pelanggan</h3>
        <div class="rating-summary">
            <span class="big-rating"><?= $rataRata ?></span>
            <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa-solid fa-star <?= $i <= $rataRata ? 'gold' : 'grey' ?>"></i>
                <?php endfor; ?>
            </div>
            <span class="total-review">(<?= $totalUlasan ?> Ulasan)</span>
        </div>
    </div>

    <?php if (isset($_SESSION['customer_uid'])): ?>
        <div class="review-form">
            <h4><?= $isEditing ? 'Edit ulasanmu sebelumnya' : 'Bagikan pengalamanmu tentang produk ini' ?></h4>

            <form action="" method="POST">
                <?php if ($isEditing): ?>
                    <input type="hidden" name="id_ulasan" value="<?= $userReview['id_ulasan'] ?>">
                <?php endif; ?>

                <div class="star-input">
                    <input type="radio" name="rating" id="star5" value="5" <?= $ratingValue == 5 ? 'checked' : '' ?> required /><label for="star5"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" name="rating" id="star4" value="4" <?= $ratingValue == 4 ? 'checked' : '' ?> /><label for="star4"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" name="rating" id="star3" value="3" <?= $ratingValue == 3 ? 'checked' : '' ?> /><label for="star3"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" name="rating" id="star2" value="2" <?= $ratingValue == 2 ? 'checked' : '' ?> /><label for="star2"><i class="fa-solid fa-star"></i></label>
                    <input type="radio" name="rating" id="star1" value="1" <?= $ratingValue == 1 ? 'checked' : '' ?> /><label for="star1"><i class="fa-solid fa-star"></i></label>
                </div>

                <textarea name="komentar" placeholder="Ceritakan rasa, aroma, atau hal yang kamu suka..." required><?= htmlspecialchars($komentarValue) ?></textarea>

                <button type="submit" name="<?= $isEditing ? 'update_ulasan' : 'kirim_ulasan' ?>" class="submit-review">
                    <?= $isEditing ? 'Simpan Perubahan' : 'Kirim Ulasan' ?>
                </button>
            </form>
        </div>

    <?php else: ?>
        <div class="login-alert">
            <p>Ingin menulis ulasan?</p>
            <p>Silakan <a href="../auth/login.php" style="text-decoration: underline; font-weight: bold;">Login</a> terlebih dahulu.</p>
        </div>
    <?php endif; ?>

    <div class="review-list" style="margin-top: 30px;">
        <?php if (mysqli_num_rows($queryUlasan) > 0): ?>
            <?php while ($ulasan = mysqli_fetch_assoc($queryUlasan)): ?>
                <div class="review-item">
                    <div class="reviewer-info">
                        <strong><?= htmlspecialchars($ulasan['nama']) ?></strong>
                        <span class="review-date"><?= date('d M Y', strtotime($ulasan['tanggal'])) ?></span>
                    </div>
                    <div class="user-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fa-solid fa-star <?= $i <= $ulasan['rating'] ? 'gold' : 'grey' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="review-text"><?= nl2br(htmlspecialchars($ulasan['komentar'])) ?></p>

                    <?php if (isset($_SESSION['customer_uid']) && $ulasan['uid_akun'] == $_SESSION['customer_uid']): ?>
                        <small style="color: #aaa; font-style: italic;">(Ulasan Saya)</small>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 20px; color: #888;">
                <i class="fa-regular fa-comment-dots" style="font-size: 2rem; margin-bottom: 10px; display:block;"></i>
                <p>Belum ada ulasan. Jadilah yang pertama mereview!</p>
            </div>
        <?php endif; ?>
    </div>
</div>