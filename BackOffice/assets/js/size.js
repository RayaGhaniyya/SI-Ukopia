/* ============================================
   SIZE JS - UKOPIA BACKOFFICE
   
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
  const addForm = document.getElementById("SizeAddForm");
  if (addForm) {
    addForm.addEventListener("submit", (e) =>
      handleSizeSubmit(e, "action/store.php", "Size berhasil ditambahkan!")
    );
    initFormAutoSave(addForm);
  }

  // Form Update
  const updateForm = document.getElementById("SizeUpdateForm");
  if (updateForm) {
    updateForm.addEventListener("submit", (e) =>
      handleSizeSubmit(e, "action/update.php", "Size berhasil diperbarui!")
    );
    initFormAutoSave(updateForm);
  }

  // Load saved form data
  loadSavedFormData();
});

// ===== FORM SUBMIT HANDLER =====
async function handleSizeSubmit(e, url, successMessage) {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnHTML = submitBtn?.innerHTML;
  
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
  }

  showLoading("Menyimpan data Size...");

  try {
    const res = await fetch(url, { method: "POST", body: formData });

    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const result = await res.json();
    hideLoading();

    if (result.success) {
      showNotification(successMessage, "success");
      
      const inputs = form.querySelectorAll('input[type="text"], textarea, select');
      inputs.forEach(input => {
        localStorage.removeItem(`${form.id}_${input.name}`);
      });
      
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

// ===== DELETE HANDLER =====
async function confirmDelete(id) {
  if (!id) {
    showNotification("ID Size tidak valid", "error");
    return;
  }

  if (!confirm("⚠️ Yakin ingin menghapus Size ini?\n\nData yang dihapus tidak dapat dikembalikan!")) {
    return;
  }

  showLoading("Menghapus Size...");

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
      showNotification("Size berhasil dihapus!", "success");
      setTimeout(() => window.location.reload(), 1000);
    } else {
      showNotification(result.message || "Gagal menghapus Size", "error");
    }
  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showNotification("Terjadi kesalahan saat menghapus data", "error");
  }
}