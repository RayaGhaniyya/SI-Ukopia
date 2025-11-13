/* ============================================
    KATEGORI.JS - UKOPIA BACKOFFICE (OPTIMIZED)
    [UBAH] Ganti nama file dan fungsi sesuai modul
    
    Dependencies dari global.js:
    - showNotification()
    - showLoading() / hideLoading()
    - initFormAutoSave()
    - loadSavedFormData()
    // initTableSearch() SUDAH DIHAPUS
    ============================================ */

// ===== INITIALIZATION =====
document.addEventListener("DOMContentLoaded", () => {
  // [UBAH] Form Add - ID form sesuai HTML
  const addForm = document.getElementById("kategoriAddForm");
  if (addForm) {
    addForm.addEventListener("submit", (e) =>
      // [UBAH] URL action dan success message
      handleKategoriSubmit(e, "action/store.php", "Kategori berhasil ditambahkan!")
    );
    initFormAutoSave(addForm); // ✅ Dari global.js
  }

  // [UBAH] Form Update - ID form sesuai HTML
  const updateForm = document.getElementById("kategoriUpdateForm");
  if (updateForm) {
    updateForm.addEventListener("submit", (e) =>
      // [UBAH] URL action dan success message
      handleKategoriSubmit(e, "action/update.php", "Kategori berhasil diperbarui!")
    );
    initFormAutoSave(updateForm); // ✅ Dari global.js
  }

  // Load saved form data
  loadSavedFormData(); // ✅ Dari global.js
});

// ===== FORM SUBMIT HANDLER (Simple Version) =====
// [UBAH] Nama fungsi bisa disesuaikan (contoh: handleKategoriSubmit, handleProductSubmit)
async function handleKategoriSubmit(e, url, successMessage) {
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

  // [UBAH] Loading message
  showLoading("Menyimpan data kategori..."); // ✅ Dari global.js

  try {
    const res = await fetch(url, { method: "POST", body: formData });

    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const result = await res.json();
    hideLoading(); // ✅ Dari global.js

    if (result.success) {
      showNotification(successMessage, "success"); // ✅ Dari global.js
      
      // Clear localStorage untuk form ini
      const inputs = form.querySelectorAll('input[type="text"], textarea, select');
      inputs.forEach(input => {
        localStorage.removeItem(`${form.id}_${input.name}`);
      });
      
      // [UBAH] Redirect ke halaman index
      setTimeout(() => window.location.href = "index.php", 1200);
    } else {
      showNotification(result.message || "Gagal menyimpan data", "error"); // ✅ Dari global.js
      
      // Re-enable button
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHTML;
      }
    }
  } catch (error) {
    hideLoading(); // ✅ Dari global.js
    console.error("Error:", error);
    showNotification("Terjadi kesalahan! " + error.message, "error"); // ✅ Dari global.js
    
    // Re-enable button
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnHTML;
    }
  }
}

// ===== DELETE HANDLER =====
// [UBAH] Confirmation message dan loading message sesuai modul
async function confirmDelete(id) {
  if (!id) {
    showNotification("ID kategori tidak valid", "error"); // ✅ Dari global.js
    return;
  }

  // [UBAH] Confirmation text sesuai modul
  if (!confirm("⚠️ Yakin ingin menghapus kategori ini?\n\nData yang dihapus tidak dapat dikembalikan!")) {
    return;
  }

  // [UBAH] Loading message
  showLoading("Menghapus kategori..."); // ✅ Dari global.js

  try {
    const res = await fetch("action/delete.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ id: id })
    });

    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const result = await res.json();
    hideLoading(); // ✅ Dari global.js

    if (result.success) {
      // [UBAH] Success message
      showNotification("Kategori berhasil dihapus!", "success"); // ✅ Dari global.js
      setTimeout(() => window.location.reload(), 1000);
    } else {
      showNotification(result.message || "Gagal menghapus kategori", "error"); // ✅ Dari global.js
    }
  } catch (error) {
    hideLoading(); // ✅ Dari global.js
    console.error("Error:", error);
    showNotification("Terjadi kesalahan saat menghapus data", "error"); // ✅ Dari global.js
  }
}