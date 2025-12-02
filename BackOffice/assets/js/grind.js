document.addEventListener("DOMContentLoaded", () => {
  const addForm = document.getElementById("GrindSizeAddForm");
  if (addForm) {
    addForm.addEventListener("submit", (e) =>
      handleGrindSizeSubmit(e, "action/store.php", "Grind Size berhasil ditambahkan!")
    );
    initFormAutoSave(addForm); // ✅ Dari global.js
  }
  const updateForm = document.getElementById("GrindSizeUpdateForm");
  if (updateForm) {
    updateForm.addEventListener("submit", (e) =>
      handleGrindSizeSubmit(e, "action/update.php", "Grind Size berhasil diperbarui!")
    );
    initFormAutoSave(updateForm); // ✅ Dari global.js
  }
  loadSavedFormData(); // ✅ Dari global.js
});
async function handleGrindSizeSubmit(e, url, successMessage) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnHTML = submitBtn?.innerHTML;
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
  }
  showLoading("Menyimpan data Grind Size..."); // ✅ Dari global.js
  try {
    const res = await fetch(url, { method: "POST", body: formData });
    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
    const result = await res.json();
    hideLoading(); // ✅ Dari global.js
    if (result.success) {
      showNotification(successMessage, "success"); // ✅ Dari global.js
      const inputs = form.querySelectorAll('input[type="text"], textarea, select');
      inputs.forEach(input => {
        localStorage.removeItem(`${form.id}_${input.name}`);
      });
      setTimeout(() => window.location.href = "index.php", 1200);
    } else {
      showNotification(result.message || "Gagal menyimpan data", "error"); // ✅ Dari global.js
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnHTML;
      }
    }
  } catch (error) {
    hideLoading(); // ✅ Dari global.js
    console.error("Error:", error);
    showNotification("Terjadi kesalahan! " + error.message, "error"); // ✅ Dari global.js
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnHTML;
    }
  }
}
async function confirmDelete(id) {
  if (!id) {
    showNotification("ID Grind Size tidak valid", "error"); // ✅ Dari global.js
    return;
  }
  if (!confirm("⚠️ Yakin ingin menghapus Grind Size ini?\n\nData yang dihapus tidak dapat dikembalikan!")) {
    return;
  }
  showLoading("Menghapus Grind Size..."); // ✅ Dari global.js
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
      showNotification("Grind Size berhasil dihapus!", "success"); // ✅ Dari global.js
      setTimeout(() => window.location.reload(), 1000);
    } else {
      showNotification(result.message || "Gagal menghapus Grind Size", "error"); // ✅ Dari global.js
    }
  } catch (error) {
    hideLoading(); // ✅ Dari global.js
    console.error("Error:", error);
    showNotification("Terjadi kesalahan saat menghapus data", "error"); // ✅ Dari global.js
  }
}

