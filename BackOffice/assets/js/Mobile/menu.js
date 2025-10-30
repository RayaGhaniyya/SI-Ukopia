const fileInput = document.getElementById("fileInput");
const previewContainer = document.getElementById("imagePreview");
const uploadButton = document.getElementById("uploadButton");
const previewText = document.getElementById("previewText");

function triggerFileInput() {
  fileInput.click();
}
function handleImagePreview(input) {
  const file = input.files[0];
  if (file) {
    const validTypes = ["image/jpeg", "image/jpg", "image/png", "image/webp"];
    if (!validTypes.includes(file.type)) {
      alert("Format file tidak didukung.");
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      alert("Ukuran file terlalu besar. Maksimal 5MB.");
      return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
      previewContainer.innerHTML = "";
      const img = document.createElement("img");
      img.src = e.target.result;
      previewContainer.appendChild(img);
      previewContainer.style.display = "flex";
      uploadButton.style.display = "none";
    };
    reader.readAsDataURL(file);
  }
}
function confirmDeleteMenu(id) {
    // Tampilkan dialog konfirmasi
    if (confirm('Anda yakin ingin menghapus menu ini? Tindakan ini tidak dapat dibatalkan.')) {
        // Jika pengguna klik "OK", arahkan ke skrip delete.php
        window.location.href = 'delete.php?id=' + id;
    }
}

// Fungsi showDetail (jika Anda belum memilikinya)
function showDetailMenu(id) {
    // Anda bisa gunakan ini untuk AJAX atau modal
    alert('Tampilkan detail untuk ID Menu: ' + id);
}