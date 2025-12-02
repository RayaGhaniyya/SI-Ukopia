document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Handle ADD Resep
    const addForm = document.getElementById('resepAddForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault(); // <--- INI KUNCINYA (Mencegah Reload)
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

      const updateForm = document.getElementById('resepUpdateForm');
    
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            // 1. CEGAH RELOAD (Wajib!)
            e.preventDefault(); 
            console.log("Tombol Update ditekan, mencegah reload...");

            // 2. Siapkan Data
            const formData = new FormData(this);

            // 3. Kirim ke Server via Fetch
            fetch('action/update.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("HTTP error " + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("Respon Server:", data);
                if (data.success) {
                    alert(data.message); // Muncul popup sukses
                    window.location.href = 'index.php'; // Pindah halaman
                } else {
                    alert("GAGAL: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem! Cek Console.');
            });
        });
    } else {
        console.error("Form dengan ID 'resepUpdateForm' tidak ditemukan!");
    }
});

// 3. Handle DELETE
function confirmDeleteResep(id) {
    if (confirm('Apakah Anda yakin ingin menghapus resep ini?')) {
        const formData = new FormData();
        
        // KUNCI: Key harus 'id_resep' agar terbaca oleh $_POST['id_resep'] di PHP
        formData.append('id_resep', id); 

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