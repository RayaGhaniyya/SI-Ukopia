/* ============================================
   KATEGORI_ALAT.JS - UKOPIA BACKOFFICE (FIXED)
   
   Dependencies dari global.js:
   - showNotification()
   - showLoading() / hideLoading()
   - initFormAutoSave()
   - loadSavedFormData()
   - initTableSearch()
   ============================================ */

document.addEventListener("DOMContentLoaded", () => {
  const addForm = document.getElementById("kategoriAlatAddForm");
  if (addForm) {
    addForm.addEventListener("submit", (e) =>
      handleKategoriAlatSubmit(e, "action/store.php", "Kategori berhasil ditambahkan!")
    );
    
    if (typeof initFormAutoSave === 'function') {
      initFormAutoSave(addForm);
    }
  }

  const updateForm = document.getElementById("kategoriAlatUpdateForm");
  if (updateForm) {
    updateForm.addEventListener("submit", (e) =>
      handleKategoriAlatSubmit(e, "action/update.php", "Kategori berhasil diperbarui!")
    );
    
    if (typeof initFormAutoSave === 'function') {
      initFormAutoSave(updateForm);
    }
  }

  if (typeof loadSavedFormData === 'function') {
    loadSavedFormData();
  }
});

async function handleKategoriAlatSubmit(e, url, successMessage) {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnHTML = submitBtn?.innerHTML;
  
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
  }

  if (typeof showLoading === 'function') {
    showLoading("Menyimpan data kategori alat...");
  }

  try {
    const res = await fetch(url, { method: "POST", body: formData });

    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const result = await res.json();
    
    if (typeof hideLoading === 'function') {
      hideLoading();
    }

    if (result.success) {
      if (typeof showNotification === 'function') {
        showNotification(successMessage, "success");
      } else {
        alert(successMessage);
      }
      
      const inputs = form.querySelectorAll('input[type="text"], textarea, select');
      inputs.forEach(input => {
        localStorage.removeItem(`${form.id}_${input.name}`);
      });
      
      setTimeout(() => window.location.href = "index.php", 1200);
    } else {
      if (typeof showNotification === 'function') {
        showNotification(result.message || "Gagal menyimpan data", "error");
      } else {
        alert(result.message || "Gagal menyimpan data");
      }
      
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHTML;
      }
    }
  } catch (error) {
    if (typeof hideLoading === 'function') {
      hideLoading();
    }
    
    console.error("Error:", error);
    
    if (typeof showNotification === 'function') {
      showNotification("Terjadi kesalahan! " + error.message, "error");
    } else {
      alert("Terjadi kesalahan! " + error.message);
    }
    
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnHTML;
    }
  }
}

async function confirmDelete(id) {
  if (!id) {
    if (typeof showNotification === 'function') {
      showNotification("ID kategori tidak valid", "error");
    } else {
      alert("ID kategori tidak valid");
    }
    return;
  }

  if (!confirm("⚠️ Yakin ingin menghapus kategori ini?\n\nData yang dihapus tidak dapat dikembalikan!")) {
    return;
  }

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
    
    if (typeof hideLoading === 'function') {
      hideLoading();
    }

    if (result.success) {
      if (typeof showNotification === 'function') {
        showNotification("Kategori berhasil dihapus!", "success");
      } else {
        alert("Kategori berhasil dihapus!");
      }
      setTimeout(() => window.location.reload(), 1000);
    } else {
      if (typeof showNotification === 'function') {
        showNotification(result.message || "Gagal menghapus kategori", "error");
      } else {
        alert(result.message || "Gagal menghapus kategori");
      }
    }
  } catch (error) {
    if (typeof hideLoading === 'function') {
      hideLoading();
    }
    
    console.error("Error:", error);
    
    if (typeof showNotification === 'function') {
      showNotification("Terjadi kesalahan saat menghapus data", "error");
    } else {
      alert("Terjadi kesalahan saat menghapus data");
    }
  }
}
