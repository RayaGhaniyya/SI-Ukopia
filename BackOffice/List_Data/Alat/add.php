<?php
include("../../../Koneksi/koneksi.php");
include("../../Component/session.php");
include("../../Component/head.php");
$kategori = mysqli_query($conn, "SELECT * FROM kategori_alat ORDER BY nama_kategori_alat ASC");
$current_host = $_SERVER['HTTP_HOST'];
$BASE_IMAGE_URL = "http://{$current_host}/si-ukopia/BackOffice/List_Data/Uploads/Alat/";
?>
<div class="container">
    <?php include("../../Component/sidebar.php"); ?>
    <div class="dashboard-container">
        <div class="form-container">
            <h1><i class="fas fa-plus-circle"></i> Tambah Alat</h1>
            <form id="alatAddForm" enctype="multipart/form-data">
                <label>Nama Alat <span style="color:red">*</span></label>
                <input type="text" name="nama_alat" required placeholder="Nama alat...">
                <label>Kategori <span style="color:red">*</span></label>
                <select name="id_kategori" id="kategoriSelect" required onchange="loadExistingImages(this.value)">
                    <option value="">-- Pilih Kategori --</option>
                    <?php 
                    mysqli_data_seek($kategori, 0); // Reset pointer
                    while ($k = mysqli_fetch_assoc($kategori)): 
                    ?>
                        <option value="<?= $k['id_kategori_alat'] ?>"><?= $k['nama_kategori_alat'] ?></option>
                    <?php endwhile; ?>
                </select>
                <div class="form-row">
                    <!-- Pilihan: Gunakan gambar existing atau upload baru -->
                    <div style="width: 100%;">
                        <label>Pilih Gambar <span style="color:red">*</span></label>
                        <div style="margin-bottom: 15px;">
                            <label style="display: inline-flex; align-items: center; margin-right: 20px; cursor: pointer;">
                                <input type="radio" name="image_option" value="existing" id="useExisting" 
                                       style="margin-right: 8px;" onchange="toggleImageOption('existing')">
                                <span>Gunakan Gambar yang Sudah Ada</span>
                            </label>
                            <label style="display: inline-flex; align-items: center; cursor: pointer;">
                                <input type="radio" name="image_option" value="new" id="useNew" checked
                                       style="margin-right: 8px;" onchange="toggleImageOption('new')">
                                <span>Upload Gambar Baru</span>
                            </label>
                        </div>
                        <!-- SECTION: Pilih Gambar Existing -->
                        <div id="existingImageSection" style="display: none;">
                            <div id="existingImagesContainer" style="margin-bottom: 15px;">
                                <p style="color: #999; text-align: center; padding: 20px;">
                                    <i class="fas fa-info-circle"></i> Pilih kategori terlebih dahulu
                                </p>
                            </div>
                            <input type="hidden" name="existing_image" id="selectedExistingImage">
                        </div>
                        <!-- SECTION: Upload Gambar Baru -->
                        <div id="newImageSection">
                            <input type="file" id="fileInput" name="gambar" accept="image/*" 
                                   style="display:none;" onchange="handleImagePreviewAlat(this, 'previewBox', 'btnUpload')">
                            <div id="previewBox" onclick="document.getElementById('fileInput').click()" 
                                 style="border:2px dashed #ddd; padding:20px; text-align:center; cursor:pointer; border-radius:8px;">
                                <i class="fas fa-image" style="font-size:24px; color:#ccc;"></i><br>
                                <span style="color: #999;">Klik untuk pilih gambar</span>
                            </div>
                            <button type="button" id="btnUpload" class="btn btn-info btn-sm" 
                                    style="margin-top:10px; width:100%; display:none;" 
                                    onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-sync-alt"></i> Ganti Gambar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="index.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
.image-library-item {
    display: inline-block;
    width: 120px;
    margin: 10px;
    text-align: center;
    cursor: pointer;
    border: 3px solid transparent;
    border-radius: 8px;
    padding: 8px;
    transition: all 0.3s ease;
}
.image-library-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.3);
}
.image-library-item.selected {
    border-color: #28a745;
    background-color: #f0f9ff;
}
.image-library-item img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 6px;
    margin-bottom: 5px;
}
.image-library-item .image-info {
    font-size: 11px;
    color: #666;
    margin-top: 5px;
}
.image-library-item .usage-badge {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    margin-top: 3px;
}
</style>
<script>
const BASE_IMAGE_URL = "<?= $BASE_IMAGE_URL ?>";
function toggleImageOption(option) {
    const existingSection = document.getElementById('existingImageSection');
    const newSection = document.getElementById('newImageSection');
    const fileInput = document.getElementById('fileInput');
    if (option === 'existing') {
        existingSection.style.display = 'block';
        newSection.style.display = 'none';
        fileInput.removeAttribute('required');
        const kategoriId = document.getElementById('kategoriSelect').value;
        if (kategoriId) {
            loadExistingImages(kategoriId);
        }
    } else {
        existingSection.style.display = 'none';
        newSection.style.display = 'block';
        fileInput.setAttribute('required', 'required');
        document.getElementById('selectedExistingImage').value = '';
    }
}
async function loadExistingImages(kategoriId) {
    const container = document.getElementById('existingImagesContainer');
    if (!kategoriId) {
        container.innerHTML = '<p style="color: #999; text-align: center; padding: 20px;"><i class="fas fa-info-circle"></i> Pilih kategori terlebih dahulu</p>';
        return;
    }
    container.innerHTML = '<p style="text-align: center; color: #999;"><i class="fas fa-spinner fa-spin"></i> Memuat gambar...</p>';
    try {
        const response = await fetch(`action/get_images.php?kategori_id=${kategoriId}`);
        const data = await response.json();
        if (data.success && data.images.length > 0) {
            let html = '<div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 8px;">';
            data.images.forEach(img => {
                html += `
                    <div class="image-library-item" onclick="selectExistingImage('${img.filename}', this)">
                        <img src="${BASE_IMAGE_URL}${img.filename}" alt="${img.filename}">
                        <div class="image-info">
                            <div style="font-weight: 500; color: #333; margin-bottom: 3px;">${img.sample_alat}</div>
                            <div class="usage-badge">
                                <i class="fas fa-tools"></i> ${img.usage_count} alat
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = `
                <div style="text-align: center; padding: 30px; background: #f8f9fa; border-radius: 8px; border: 1px dashed #ddd;">
                    <i class="fas fa-image" style="font-size: 48px; color: #ccc; margin-bottom: 10px;"></i>
                    <p style="color: #666; margin: 0;">Belum ada gambar untuk kategori ini.</p>
                    <p style="color: #999; font-size: 13px; margin-top: 5px;">Silakan upload gambar baru.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading images:', error);
        container.innerHTML = '<p style="color: red; text-align: center;">Gagal memuat gambar</p>';
    }
}
function selectExistingImage(filename, element) {
    document.querySelectorAll('.image-library-item').forEach(item => {
        item.classList.remove('selected');
    });
    element.classList.add('selected');
    document.getElementById('selectedExistingImage').value = filename;
}
function handleImagePreviewAlat(input, previewBoxId, btnUploadId) {
    const previewBox = document.getElementById(previewBoxId);
    const btnUpload = document.getElementById(btnUploadId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewBox.innerHTML = `<img src="${e.target.result}" style="max-width:100%; max-height:200px; border-radius:6px;">`;
            btnUpload.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<script src="../../assets/js/alat.js"></script>
<?php include("../../Component/bottom.php"); ?>

