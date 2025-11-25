document.addEventListenerResep('DOMContentLoaded', function() {
    
    // Handle Add Resep
    const addForm = document.getElementById('resepAddForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('action/store.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'index.php';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    // Handle Update Resep
    const updateForm = document.getElementById('resepUpdateForm');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('action/update.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'index.php';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});

function confirmDeleteResep(id) {
    if (confirm('Apakah Anda yakin ingin menghapus resep ini?')) {
        const formData = new FormData();
        formData.append('id', id);

        fetch('action/delete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}