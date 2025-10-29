/* ============================================
   GALLERY.JS - UKOPIA BACKOFFICE
   Hanya fungsi spesifik gallery (tanpa duplikasi global.js)
   ============================================ */

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
  }

  // Form Update Gallery
  const updateForm = document.getElementById("galleryUpdateForm");
  if (updateForm) {
    addForm.addEventListener("submit", (e) =>
      handleGallerySubmit(e, "action/update.php", "Galeri berhasil diperbarui!")
    );
  }

  // Search functionality (menggunakan fungsi dari global.js jika ada)
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
});

// ===== FORM SUBMIT HANDLER (GALLERY SPECIFIC) =====
async function handleGallerySubmit(e, url, successMessage) {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');

  // Validasi gambar
  const fileInput = form.querySelector('input[type="file"]');
  if (fileInput && fileInput.files.length > 0) {
    // Max 4 gambar
    if (fileInput.files.length > 4) {
      showNotification("Maksimal 4 gambar!", "warning");
      return;
    }

    // Validasi ukuran dan tipe file
    for (let i = 0; i < fileInput.files.length; i++) {
      const file = fileInput.files[i];
      
      // Validasi tipe file
      if (!file.type.startsWith('image/')) {
        showNotification(`File ${file.name} bukan gambar!`, "error");
        return;
      }
      
      // Validasi ukuran (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        showNotification(`File ${file.name} terlalu besar! Max 5MB`, "warning");
        return;
      }
    }
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
      setTimeout(() => {
        window.location.href = "index.php";
      }, 1200);
    } else {
      showNotification(result.message || "Gagal menyimpan data", "error");
      // Re-enable button
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHTML;
      }
    }
  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showNotification("Terjadi kesalahan! " + error.message, "error");
    
    // Re-enable button
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnHTML;
    }
  }
}

// ===== PREVIEW GAMBAR =====
function previewGalleryImages(input) {
  const preview = document.getElementById("previewContainer");
  if (!preview) return;

  preview.innerHTML = "";
  
  if (!input.files || input.files.length === 0) {
    return;
  }

  // Batasi maksimal 4 gambar
  const files = Array.from(input.files).slice(0, 4);
  
  if (input.files.length > 4) {
    showNotification("Maksimal 4 gambar! Hanya 4 gambar pertama yang akan diupload.", "warning");
  }

  files.forEach((file, index) => {
    // Validasi tipe file
    if (!file.type.startsWith('image/')) {
      showNotification(`File ${file.name} bukan gambar!`, "warning");
      return;
    }

    // Validasi ukuran file
    if (file.size > 5 * 1024 * 1024) {
      showNotification(`File ${file.name} terlalu besar! Max 5MB`, "warning");
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      const img = document.createElement("img");
      img.src = e.target.result;
      img.alt = `Preview ${index + 1}`;
      img.loading = "lazy";
      
      preview.appendChild(img);
    };
    
    reader.onerror = () => {
      showNotification(`Gagal membaca file ${file.name}`, "error");
    };
    
    reader.readAsDataURL(file);
  });
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
      
      // Error handling untuk gambar yang gagal load
      img.onerror = function() {
        this.style.background = "#e5e7eb";
        this.style.display = "flex";
        this.style.alignItems = "center";
        this.style.justifyContent = "center";
        this.alt = "Gagal memuat gambar";
      };
      
      grid.appendChild(img);
    });

    // Tampilkan popup
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

// Alias untuk kompatibilitas
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

    // Search in judul, deskripsi, tanggal (skip kolom No dan Aksi)
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

  // Show empty state if no results
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
            <p style="color: #6b7280; margin: 0;">Tidak ada hasil untuk "<strong>${escapeHtml(filter)}</strong>"</p>
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