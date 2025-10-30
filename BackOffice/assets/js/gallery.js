/* ============================================
   GALLERY.JS - UKOPIA BACKOFFICE (FINAL FIX)
   ============================================ */

// Global variable untuk menyimpan file yang dipilih
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
    
    // Auto-save form data
    initFormAutoSave(addForm);
  }

  // Form Update Gallery
  const updateForm = document.getElementById("galleryUpdateForm");
  if (updateForm) {
    updateForm.addEventListener("submit", (e) =>
      handleGallerySubmit(e, "action/update.php", "Galeri berhasil diperbarui!")
    );
    
    // Auto-save form data
    initFormAutoSave(updateForm);
  }

  // Search functionality
  const searchInput = document.getElementById("searchGallery");
  if (searchInput) {
    searchInput.addEventListener("input", searchGalleryTable);
  }

  // Close popup dengan ESC key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeGalleryPopup();
    }
  });
  
  // Load saved form data on page load
  loadSavedFormData();
});

// ===== AUTO-SAVE FORM DATA =====
function initFormAutoSave(form) {
  const formId = form.id;
  const inputs = form.querySelectorAll('input[type="text"], input[type="date"], textarea');
  
  inputs.forEach(input => {
    // Load saved value
    const savedValue = localStorage.getItem(`${formId}_${input.name}`);
    if (savedValue && !input.value) {
      input.value = savedValue;
    }
    
    // Save on input
    input.addEventListener('input', () => {
      localStorage.setItem(`${formId}_${input.name}`, input.value);
    });
  });
  
  // Clear saved data on successful submit
  form.addEventListener('submit', () => {
    setTimeout(() => {
      inputs.forEach(input => {
        localStorage.removeItem(`${formId}_${input.name}`);
      });
    }, 1000);
  });
}

function loadSavedFormData() {
  const forms = document.querySelectorAll('form[id*="gallery"]');
  forms.forEach(form => {
    const inputs = form.querySelectorAll('input[type="text"], textarea');
    let hasSavedData = false;
    
    inputs.forEach(input => {
      const savedValue = localStorage.getItem(`${form.id}_${input.name}`);
      if (savedValue) {
        hasSavedData = true;
      }
    });
    
    if (hasSavedData) {
      showNotification('Data form sebelumnya berhasil dipulihkan', 'info');
    }
  });
}

// ===== ADD MORE IMAGES (ACCUMULATIVE) =====
function addMoreImages(input) {
  if (!input.files || input.files.length === 0) {
    return;
  }

  const preview = document.getElementById("previewContainer");
  if (!preview) return;

  // Tambahkan file baru ke array (JANGAN REPLACE!)
  const newFiles = Array.from(input.files);
  
  newFiles.forEach(file => {
    // Cek apakah sudah ada file dengan nama dan ukuran yang sama
    const isDuplicate = selectedFiles.some(
      existingFile => existingFile.name === file.name && existingFile.size === file.size
    );
    
    // Tambahkan jika belum ada dan belum mencapai limit
    if (!isDuplicate && selectedFiles.length < 4) {
      selectedFiles.push(file);
    }
  });

  // Validasi max 4 gambar
  if (selectedFiles.length > 4) {
    showNotification("Maksimal 4 gambar! Hanya 4 gambar pertama yang digunakan.", "warning");
    selectedFiles = selectedFiles.slice(0, 4);
  }

  // Render preview
  renderPreview();
  
  // Reset input value agar bisa pilih file yang sama lagi
  input.value = '';
}

// ===== RENDER PREVIEW =====
function renderPreview() {
  const preview = document.getElementById("previewContainer");
  if (!preview) return;

  // Clear preview
  preview.innerHTML = "";

  if (selectedFiles.length === 0) {
    return;
  }

  selectedFiles.forEach((file, index) => {
    // Validasi tipe file
    if (!file.type.startsWith('image/')) {
      return;
    }

    // Validasi ukuran file
    if (file.size > 5 * 1024 * 1024) {
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      const wrapper = document.createElement("div");
      wrapper.style.position = "relative";
      wrapper.style.display = "inline-block";

      const img = document.createElement("img");
      img.src = e.target.result;
      img.alt = `Preview ${index + 1}`;
      img.loading = "lazy";
      
      // Tombol hapus
      const removeBtn = document.createElement("button");
      removeBtn.type = "button";
      removeBtn.innerHTML = "×";
      removeBtn.style.cssText = `
        position: absolute;
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: none;
        background: #ef4444;
        color: white;
        font-size: 18px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
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
      
      removeBtn.onclick = () => {
        removeFile(index);
      };
      
      wrapper.appendChild(img);
      wrapper.appendChild(removeBtn);
      preview.appendChild(wrapper);
    };
    
    reader.onerror = () => {
      showNotification(`Gagal membaca file ${file.name}`, "error");
    };
    
    reader.readAsDataURL(file);
  });

  // Show info
  const infoDiv = document.createElement("div");
  infoDiv.style.cssText = `
    width: 100%;
    padding: 10px;
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    border-radius: 6px;
    margin-top: 10px;
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

// ===== LEGACY FUNCTION (untuk backward compatibility) =====
function previewGalleryImages(input) {
  addMoreImages(input);
}

// ===== FORM SUBMIT HANDLER =====
async function handleGallerySubmit(e, url, successMessage) {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData();
  
  // Tambahkan field text
  const inputs = form.querySelectorAll('input[type="text"], input[type="date"], input[type="hidden"], textarea');
  inputs.forEach(input => {
    formData.append(input.name, input.value);
  });
  
  // Tambahkan file dari array
  if (selectedFiles.length > 0) {
    selectedFiles.forEach((file) => {
      formData.append('gambar[]', file);
    });
  }

  const submitBtn = form.querySelector('button[type="submit"]');

  // Validasi gambar (minimal 1 untuk add, opsional untuk update)
  const isAddForm = url.includes('store.php');
  if (isAddForm && selectedFiles.length === 0) {
    showNotification("Minimal 1 gambar harus diupload!", "warning");
    return;
  }

  // Disable submit button
  const originalBtnHTML = submitBtn.innerHTML;
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
  }

  showLoading("Menyimpan data galeri...");

  try {
    const res = await fetch(url, { 
      method: "POST", 
      body: formData 
    });

    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }

    const result = await res.json();
    hideLoading();

    if (result.success) {
      showNotification(successMessage, "success");
      
      // Clear saved form data
      const textInputs = form.querySelectorAll('input[type="text"], input[type="date"], textarea');
      textInputs.forEach(input => {
        localStorage.removeItem(`${form.id}_${input.name}`);
      });
      
      // Clear selected files
      selectedFiles = [];
      
      setTimeout(() => {
        window.location.href = "index.php";
      }, 1200);
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
    
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }

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
        this.style.display = "flex";
        this.style.alignItems = "center";
        this.style.justifyContent = "center";
        this.alt = "Gagal memuat gambar";
      };
      
      grid.appendChild(img);
    });

    popup.style.display = "flex";
    setTimeout(() => {
      popup.classList.add("show");
    }, 10);

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
  setTimeout(() => {
    popup.style.display = "none";
  }, 300);
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
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({ id: id })
    });

    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }

    const result = await res.json();
    hideLoading();

    if (result.success) {
      showNotification("Galeri berhasil dihapus!", "success");
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      showNotification(result.message || "Gagal menghapus galeri", "error");
    }
  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showNotification("Terjadi kesalahan saat menghapus data", "error");
  }
}

// ===== SEARCH GALLERY TABLE =====
function searchGalleryTable() {
  const input = document.getElementById("searchGallery");
  const filter = input.value.toLowerCase();
  const tbody = document.querySelector(".gallery-table tbody");
  const rows = tbody.getElementsByTagName("tr");

  let visibleCount = 0;

  for (let i = 0; i < rows.length; i++) {
    const cells = rows[i].getElementsByTagName("td");
    let found = false;

    for (let j = 1; j < cells.length - 1; j++) {
      const cellText = cells[j].textContent || cells[j].innerText;
      if (cellText.toLowerCase().indexOf(filter) > -1) {
        found = true;
        break;
      }
    }

    if (found) {
      rows[i].style.display = "";
      visibleCount++;
    } else {
      rows[i].style.display = "none";
    }
  }

  const colCount = tbody.querySelector("tr")?.cells.length || 5;
  let emptyState = tbody.querySelector(".empty-search-row");
  
  if (visibleCount === 0 && filter !== "") {
    if (!emptyState) {
      emptyState = document.createElement("tr");
      emptyState.className = "empty-search-row";
      emptyState.innerHTML = `
        <td colspan="${colCount}" style="text-align: center; padding: 40px;">
          <div class="empty-state">
            <i class="fas fa-search" style="font-size: 2.5rem; color: #d1d5db; margin-bottom: 10px;"></i>
            <p style="color: #6b7280; margin: 0;">Tidak ada hasil untuk "<strong>${filter}</strong>"</p>
          </div>
        </td>
      `;
      tbody.appendChild(emptyState);
    }
  } else {
    if (emptyState) {
      emptyState.remove();
    }
  }
}

// ===== CLICK OUTSIDE POPUP TO CLOSE =====
document.addEventListener("click", (e) => {
  const popup = document.getElementById("detailPopup");
  if (popup && e.target === popup) {
    closeGalleryPopup();
  }
});