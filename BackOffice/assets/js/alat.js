function handleImagePreviewAlat(input, previewId, buttonId) {
    const previewContainer = document.getElementById(previewId);
    const uploadButton = document.getElementById(buttonId);

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">`;
            if (uploadButton) {
                uploadButton.innerHTML = '<i class="fas fa-sync-alt"></i> Ganti Gambar';
                uploadButton.style.display = 'block';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.getElementById('alatAddForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('action/store.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if(data.success) window.location.href = 'index.php';
            })
            .catch(err => console.error(err));
        });
    }

    const updateForm = document.getElementById('alatUpdateForm');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('action/update.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if(data.success) window.location.href = 'index.php';
            })
            .catch(err => console.error(err));
        });
    }
});

function confirmDeleteAlat(id) {
    if (confirm('Yakin hapus alat ini?')) {
        const formData = new FormData();
        formData.append('id', id);
        fetch('action/delete.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if(data.success) location.reload();
        })
        .catch(err => console.error(err));
    }
}
