/* ============================================
   KATEGORI_ALAT.JS - UKOPIA BACKOFFICE (FIXED)
   
   Dependencies dari global.js:
   - showNotification()
   - showLoading() / hideLoading()
   - initFormAutoSave()
   - loadSavedFormData()
   - initTableSearch()
   ============================================ */

// ===== INITIALIZATION =====
document.addEventListener("DOMContentLoaded", () => {
  // Form Add
  const addForm = document.getElementById("kategoriAlatAddForm");
  if (addForm) {
    addForm.addEventListener("submit", (e) =>
      // ✅ FIXED: Nama fungsi sekarang konsisten
      handleKategoriAlatSubmit(e, "action/store.php", "Kategori berhasil ditambahkan!")
    );
    
    // Gunakan initFormAutoSave jika fungsi tersedia dari global.js
    if (typeof initFormAutoSave === 'function') {
      initFormAutoSave(addForm);
    }
  }

  // Form Update
  const updateForm = document.getElementById("kategoriAlatUpdateForm");
  if (updateForm) {
    updateForm.addEventListener("submit", (e) =>
      // ✅ FIXED: Nama fungsi sekarang konsisten
      handleKategoriAlatSubmit(e, "action/update.php", "Kategori berhasil diperbarui!")
    );
    
    // Gunakan initFormAutoSave jika fungsi tersedia dari global.js
    if (typeof initFormAutoSave === 'function') {
      initFormAutoSave(updateForm);
    }
  }

  // Load saved form data jika fungsi tersedia
  if (typeof loadSavedFormData === 'function') {
    loadSavedFormData();
  }
});

// ===== FORM SUBMIT HANDLER =====
async function handleKategoriAlatSubmit(e, url, successMessage) {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnHTML = submitBtn?.innerHTML;
  
  // Disable button
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
  }

  // Show loading (gunakan fungsi dari global.js jika ada)
  if (typeof showLoading === 'function') {
    showLoading("Menyimpan data kategori alat...");
  }

  try {
    const res = await fetch(url, { method: "POST", body: formData });

    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const result = await res.json();
    
    // Hide loading
    if (typeof hideLoading === 'function') {
      hideLoading();
    }

    if (result.success) {
      // Show success notification
      if (typeof showNotification === 'function') {
        showNotification(successMessage, "success");
      } else {
        alert(successMessage);
      }
      
      // Clear localStorage untuk form ini
      const inputs = form.querySelectorAll('input[type="text"], textarea, select');
      inputs.forEach(input => {
        localStorage.removeItem(`${form.id}_${input.name}`);
      });
      
      // Redirect ke halaman index
      setTimeout(() => window.location.href = "index.php", 1200);
    } else {
      // Show error notification
      if (typeof showNotification === 'function') {
        showNotification(result.message || "Gagal menyimpan data", "error");
      } else {
        alert(result.message || "Gagal menyimpan data");
      }
      
      // Re-enable button
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHTML;
      }
    }
  } catch (error) {
    // Hide loading
    if (typeof hideLoading === 'function') {
      hideLoading();
    }
    
    console.error("Error:", error);
    
    // Show error notification
    if (typeof showNotification === 'function') {
      showNotification("Terjadi kesalahan! " + error.message, "error");
    } else {
      alert("Terjadi kesalahan! " + error.message);
    }
    
    // Re-enable button
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnHTML;
    }
  }
}

// ===== DELETE HANDLER =====
async function confirmDelete(id) {
  if (!id) {
    if (typeof showNotification === 'function') {
      showNotification("ID kategori tidak valid", "error");
    } else {
      alert("ID kategori tidak valid");
    }
    return;
  }

  // Confirmation
  if (!confirm("⚠️ Yakin ingin menghapus kategori ini?\n\nData yang dihapus tidak dapat dikembalikan!")) {
    return;
  }

  // Show loading
  if (typeof showLoading === 'function') {
    showLoading("Menghapus kategori...");
  }

  try {
    const res = await fetch("action/delete.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ id: id })
    });

    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const result = await res.json();
    
    // Hide loading
    if (typeof hideLoading === 'function') {
      hideLoading();
    }

    if (result.success) {
      // Show success notification
      if (typeof showNotification === 'function') {
        showNotification("Kategori berhasil dihapus!", "success");
      } else {
        alert("Kategori berhasil dihapus!");
      }
      setTimeout(() => window.location.reload(), 1000);
    } else {
      // Show error notification
      if (typeof showNotification === 'function') {
        showNotification(result.message || "Gagal menghapus kategori", "error");
      } else {
        alert(result.message || "Gagal menghapus kategori");
      }
    }
  } catch (error) {
    // Hide loading
    if (typeof hideLoading === 'function') {
      hideLoading();
    }
    
    console.error("Error:", error);
    
    // Show error notification
    if (typeof showNotification === 'function') {
      showNotification("Terjadi kesalahan saat menghapus data", "error");
    } else {
      alert("Terjadi kesalahan saat menghapus data");
    }
  }
}