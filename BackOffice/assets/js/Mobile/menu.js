/* ============================================
   MENU.JS - UKOPIA BACKOFFICE
   Hanya fungsi spesifik menu
   Semua fungsi universal dari global.js
   ============================================ */

const BASE_IMAGE_URL = "../../Uploads/Menu/";

document.addEventListener("DOMContentLoaded", () => {
  console.log("Menu.js initialized");

  initFormHandlers();
});

function initFormHandlers() {
  const addForm = document.getElementById("menuAddForm");
  if (addForm) {
    addForm.addEventListener("submit", handleMenuAdd);
  }

  const updateForm = document.getElementById("menuUpdateForm");
  if (updateForm) {
    updateForm.addEventListener("submit", handleMenuUpdate);
  }
}

async function handleMenuAdd(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  const nama = formData.get('nama_menu');
  const deskripsi = formData.get('deskripsi');
  const kategori = formData.get('id_kategori');
  const gambar = formData.get('gambar');

  if (!nama || !deskripsi || !kategori) {
    showNotification('Semua field wajib diisi!', 'error');
    return;
  }
  if (!gambar || gambar.size === 0) {
    showNotification('Gambar wajib dipilih!', 'error');
    return;
  }

  showLoading('Menyimpan menu...');
  try {
    const response = await fetch('action/store.php', {
      method: 'POST',
      body: formData
    });
    const result = await response.json();
    hideLoading();
    if (result.success) {
      showNotification(result.message, 'success');
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 1500);
    } else {
      showNotification(result.message, 'error');
    }
  } catch (error) {
    hideLoading();
    console.error('Error:', error);
    showNotification('Terjadi kesalahan saat menyimpan data', 'error');
  }
}

async function handleMenuUpdate(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  const nama = formData.get('nama_menu');
  const deskripsi = formData.get('deskripsi');
  const kategori = formData.get('id_kategori');

  if (!nama || !deskripsi || !kategori) {
    showNotification('Semua field wajib diisi!', 'error');
    return;
  }

  showLoading('Menyimpan perubahan...');
  try {
    const response = await fetch('action/update.php', {
      method: 'POST',
      body: formData
    });
    const result = await response.json();
    hideLoading();
    if (result.success) {
      showNotification(result.message, 'success');
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 1500);
    } else {
      showNotification(result.message, 'error');
    }
  } catch (error) {
    hideLoading();
    console.error('Error:', error);
    showNotification('Terjadi kesalahan saat menyimpan data', 'error');
  }
}

async function confirmDelete(id) { // Mengganti nama fungsi agar sesuai panggilan di index.php
  if (!id) {
    showNotification("ID menu tidak valid", "error");
    return;
  }

  if (confirm('⚠️ Yakin ingin menghapus menu ini?\n\nData yang dihapus tidak dapat dikembalikan!')) {
    showLoading('Menghapus menu...');

    try {
      const formData = new FormData();
      formData.append('id', id);

      const response = await fetch('action/delete.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();
      hideLoading();

      if (result.success) {
        showNotification(result.message, 'success');
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        showNotification(result.message, 'error');
      }

    } catch (error) {
      hideLoading();
      console.error('Error:', error);
      showNotification('Terjadi kesalahan saat menghapus data', 'error');
    }
  }
}


async function showDetailMenu(id) {
  if (!id) {
    showNotification("ID menu tidak valid", "error");
    return;
  }

  showLoading("Memuat detail menu...");

  try {
    const res = await fetch(`action/view_detail.php?id=${id}`);
    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

    const result = await res.json();
    hideLoading();

    if (!result.success) {
      showNotification(result.message || "Gagal memuat detail menu", "error");
      return;
    }

    const popup = document.getElementById("detailPopup");
    if (!popup) {
      showNotification("Element popup tidak ditemukan", "error");
      return;
    }

    const gambarNama = escapeHtml(result.data.gambar);
    const gambarUrlLengkap = gambarNama ? (BASE_IMAGE_URL + gambarNama) : '';

    const popupContent = popup.querySelector(".popup-content");
    if (popupContent) {
      popupContent.innerHTML = `
        <div class="detail-info">
          <div class="detail-info-row">
            <div class="detail-label">Nama Menu:</div>
            <div class="detail-value"><strong>${escapeHtml(result.data.nama)}</strong></div>
          </div>
          <div class="detail-info-row">
            <div class="detail-label">Kategori:</div>
            <div class="detail-value"><span class="badge badge-info">${escapeHtml(result.data.kategori)}</span></div>
          </div>
          <div class="detail-info-row">
            <div class="detail-label">Deskripsi:</div>
            <div class="detail-value description">${escapeHtml(result.data.deskripsi)}</div>
          </div>
        </div>
        ${result.data.gambar ? `
          <div style="margin-top: 20px;">
            <h4 style="margin-bottom: 10px; color: #111;">Gambar Menu:</h4>
            <img src="${gambarUrlLengkap}" alt="${escapeHtml(result.data.nama)}" 
                 style="width: 100%; max-width: 500px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);"
                 onerror="this.src='../../assets/img/no-image.png'">
          </div>
        ` : ''}
      `;
    }

    popup.style.display = "flex";
    setTimeout(() => popup.classList.add("show"), 10);

  } catch (error) {
    hideLoading();
    console.error("Error:", error);
    showNotification("Terjadi kesalahan saat memuat detail", "error");
  }
}

function closeMenuPopup() {
  const popup = document.getElementById("detailPopup");
  if (!popup) return;

  popup.classList.remove("show");
  setTimeout(() => popup.style.display = "none", 300);
}

