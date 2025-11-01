/* ============================================
   GALLERY.JS - UKOPIA BACKOFFICE
   Hanya fungsi spesifik gallery
   Fungsi universal sudah di global.js
   ============================================ */

// Global variable untuk menyimpan file yang dipilih (gallery specific)
let selectedFiles = [];

// ===== INITIALIZATION =====
document.addEventListener("DOMContentLoaded", () => {
  // Set tanggal hari ini secara default
  const dateInput = document.getElementById("tanggal");
  if (dateInput && !dateInput.value) {
    const today = new Date();
    dateInput.value = today.toISOString().split("T")[0];
  }

  // Form Add Gallery
  const addForm = document.getElementById("galleryAddForm");
  if (addForm) {
    addForm.addEventListener("submit", (e) =>
      handleGallerySubmit(e, "action/store.php", "Galeri berhasil ditambahkan!")
    );
    initFormAutoSave(addForm); // Dari global.js
  }

  // Form Update Gallery
  const updateForm = document.getElementById("galleryUpdateForm");
  if (updateForm) {
    updateForm.addEventListener("submit", (e) =>
      handleGallerySubmit(e, "action/update.php", "Galeri berhasil diperbarui!")
    );
    initFormAutoSave(updateForm); // Dari global.js
  }

  // Search functionality - Gunakan fungsi universal dari global.js
  // Kolom: [1]=Judul, [2]=Deskripsi, [3]=Tanggal, [4]=Jumlah Foto
  initTableSearch('searchGallery', '.gallery-table tbody', [1, 2, 3]);

  // Close popup dengan ESC key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeGalleryPopup();
  });

  // Click outside popup to close
  document.addEventListener("click", (e) => {
    const popup = document.getElementById("detailPopup");
    if (popup && e.target === popup) closeGalleryPopup();
  });
  
  // Load saved form data (dari global.js)
  loadSavedFormData();
});

// ===== ADD MORE IMAGES (GALLERY SPECIFIC) =====
function addMoreImages(input) {
  if (!input.files || input.files.length === 0) return;

  const preview = document.getElementById("previewContainer");
  if (!preview) return;

  const newFiles = Array.from(input.files);
  
  newFiles.forEach(file => {
    const isDuplicate = selectedFiles.some(
      existingFile => existingFile.name === file.name && existingFile.size === file.size
    );
    
    if (!isDuplicate && selectedFiles.length < 4) {
      selectedFiles.push(file);
    }
  });

  if (selectedFiles.length > 4) {
    showNotification("Maksimal 4 gambar! Hanya 4 gambar pertama yang digunakan.", "warning");
    selectedFiles = selectedFiles.slice(0, 4);
  }

  renderPreview();
  input.value = '';
}

// ===== RENDER PREVIEW (GALLERY SPECIFIC) =====
function renderPreview() {
  const preview = document.getElementById("previewContainer");
  if (!preview) return;

  preview.innerHTML = "";

  if (selectedFiles.length === 0) return;

  selectedFiles.forEach((file, index) => {
    if (!file.type.startsWith('image/')) return;
    if (file.size > 5 * 1024 * 1024) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      const wrapper = document.createElement("div");
      wrapper.style.cssText = "position: relative; display: inline-block;";

      const img = document.createElement("img");
      img.src = e.target.result;
      img.alt = `Preview ${index + 1}`;
      img.loading = "lazy";
      
      const removeBtn = document.createElement("button");
      removeBtn.type = "button";
      removeBtn.innerHTML = "×";
      removeBtn.style.cssText = `
        position: absolute; top: -8px; right: -8px;
        width: 24px; height: 24px; border-radius: 50%;
        border: none; background: #ef4444; color: white;
        font-size: 18px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: all 0.2s;
      `;
      
      removeBtn.onmouseover = () => {
        removeBtn.style.background = '#dc2626';
        removeBtn.style.transform = 'scale(1.1)';
      };
      
      removeBtn.onmouseout = () => {
        removeBtn.style.background = '#ef4444';
        removeBtn.style.transform = 'scale(1)';
      };
      
      removeBtn.onclick = () => removeFile(index);
      
      wrapper.appendChild(img);
      wrapper.appendChild(removeBtn);
      preview.appendChild(wrapper);
    };
    
    reader.onerror = () => {
      showNotification(`Gagal membaca file ${file.name}`, "error");
    };
    
    reader.readAsDataURL(file);
  });

  const infoDiv = document.createElement("div");
  infoDiv.style.cssText = `
    width: 100%; padding: 10px;
    background: #eff6ff; border-left: 4px solid #3b82f6;
    border-radius: 6px; margin-top: 10px;
  `;
  infoDiv.innerHTML = `
    <small style="color: #1e40af; display: flex; align-items: center; gap: 6px;">
      <i class="fas fa-info-circle"></i> 
      <strong>${selectedFiles.length} gambar dipilih</strong> ${selectedFiles.length < 4 ? `(Bisa tambah ${4 - selectedFiles.length} lagi)` : '(Maksimal tercapai)'}
    </small>
  `;
  preview.appendChild(infoDiv);
}

// ===== REMOVE FILE =====
function removeFile(index) {
  selectedFiles.splice(index, 1);
  renderPreview();
  showNotification(`Gambar dihapus. Total: ${selectedFiles.length} gambar`, "info");
}

// ===== LEGACY FUNCTION (backward compatibility) =====
function previewGalleryImages(input) {
  addMoreImages(input);
}

// ===== FORM SUBMIT HANDLER (GALLERY SPECIFIC) =====
async function handleGallerySubmit(e, url, successMessage) {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData();
  
  const inputs = form.querySelectorAll('input[type="text"], input[type="date"], input[type="hidden"], textarea');
  inputs.forEach(input => formData.append(input.name, input.value));
  
  if (selectedFiles.length > 0) {
    selectedFiles.forEach(file => formData.append('gambar[]', file));
  }

  const submitBtn = form.querySelector('button[type="submit"]');
  const isAddForm = url.includes('store.php');
  
  if (isAddForm && selectedFiles.length === 0) {
    showNotification("Minimal 1 gambar harus diupload!", "warning");
    return;
  }

  const originalBtnHTML = submitBtn.innerHTML;
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
  }

  showLoading("Menyimpan data galeri...");

  try {
    const res = await fetch(url, { method: "POST", body: formData });

    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const result = await res.json();
    hideLoading();

    if (result.success) {
      showNotification(successMessage, "success");
      
      const textInputs = form.querySelectorAll('input[type="text"], input[type="date"], textarea');
      textInputs.forEach(input => {
        localStorage.removeItem(`${form.id}_${input.name}`);
      });
      
      selectedFiles = [];
      setTimeout(() => window.location.href = "index.php", 1200);
    } else {
      showNotification(result.message || "Gagal menyimpan data", "error");
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHTML;
      }
    }
  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showNotification("Terjadi kesalahan! " + error.message, "error");
    
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnHTML;
    }
  }
}

// ===== SHOW DETAIL GALLERY =====
async function showDetail(id) {
  if (!id) {
    showNotification("ID galeri tidak valid", "error");
    return;
  }

  showLoading("Memuat detail galeri...");

  try {
    const res = await fetch(`action/view_detail.php?id=${id}`);
    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const result = await res.json();
    hideLoading();

    if (!result.success) {
      showNotification("Gagal memuat detail galeri", "error");
      return;
    }

    if (!result.images || result.images.length === 0) {
      showNotification("Tidak ada gambar untuk ditampilkan", "info");
      return;
    }

    const popup = document.getElementById("detailPopup");
    const grid = document.getElementById("detailImages");
    
    if (!popup || !grid) {
      showNotification("Element popup tidak ditemukan", "error");
      return;
    }

    grid.innerHTML = "";

    result.images.forEach((src, index) => {
      const img = document.createElement("img");
      img.src = src;
      img.alt = `Gambar ${index + 1}`;
      img.loading = "lazy";
      
      img.onerror = function() {
        this.style.background = "#e5e7eb";
        this.alt = "Gagal memuat gambar";
      };
      
      grid.appendChild(img);
    });

    popup.style.display = "flex";
    setTimeout(() => popup.classList.add("show"), 10);

  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showNotification("Terjadi kesalahan saat memuat detail", "error");
  }
}

// ===== CLOSE GALLERY POPUP =====
function closeGalleryPopup() {
  const popup = document.getElementById("detailPopup");
  if (!popup) return;

  popup.classList.remove("show");
  setTimeout(() => popup.style.display = "none", 300);
}

function closeDetailPopup() {
  closeGalleryPopup();
}

// ===== DELETE GALLERY =====
async function confirmDelete(id) {
  if (!id) {
    showNotification("ID galeri tidak valid", "error");
    return;
  }

  if (!confirm("⚠️ Yakin ingin menghapus galeri ini?\n\nData yang dihapus tidak dapat dikembalikan!")) {
    return;
  }

  showLoading("Menghapus galeri...");

  try {
    const res = await fetch("action/delete.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ id: id })
    });

    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const result = await res.json();
    hideLoading();

    if (result.success) {
      showNotification("Galeri berhasil dihapus!", "success");
      setTimeout(() => window.location.reload(), 1000);
    } else {
      showNotification(result.message || "Gagal menghapus galeri", "error");
    }
  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showNotification("Terjadi kesalahan saat menghapus data", "error");
  }
}