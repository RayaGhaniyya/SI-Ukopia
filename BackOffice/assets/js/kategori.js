


document.addEventListener("DOMContentLoaded", () => {
  
  const addForm = document.getElementById("kategoriAddForm");
  if (addForm) {
    addForm.addEventListener("submit", (e) =>
      
      handleKategoriSubmit(e, "action/store.php", "Kategori berhasil ditambahkan!")
    );
    initFormAutoSave(addForm); 
  }

  
  const updateForm = document.getElementById("kategoriUpdateForm");
  if (updateForm) {
    updateForm.addEventListener("submit", (e) =>
      
      handleKategoriSubmit(e, "action/update.php", "Kategori berhasil diperbarui!")
    );
    initFormAutoSave(updateForm); 
  }

  
  loadSavedFormData(); 
});



async function handleKategoriSubmit(e, url, successMessage) {
  e.preventDefault();
  
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnHTML = submitBtn?.innerHTML;
  
  
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
  }

  
  showLoading("Menyimpan data kategori..."); 

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



async function confirmDelete(id) {
  if (!id) {
    showNotification("ID kategori tidak valid", "error"); 
    return;
  }

  
  if (!confirm("⚠️ Yakin ingin menghapus kategori ini?\n\nData yang dihapus tidak dapat dikembalikan!")) {
    return;
  }

  
  showLoading("Menghapus kategori..."); 

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
      
      showNotification("Kategori berhasil dihapus!", "success"); 
      setTimeout(() => window.location.reload(), 1000);
    } else {
      showNotification(result.message || "Gagal menghapus kategori", "error"); 
    }
  } catch (error) {
    hideLoading(); 
    console.error("Error:", error);
    showNotification("Terjadi kesalahan saat menghapus data", "error"); 
  }
}