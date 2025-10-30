 const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('imagePreview');
    const uploadButton = document.getElementById('uploadButton');
    const previewText = document.getElementById('previewText');
    
    function triggerFileInput() { fileInput.click(); }
    function handleImagePreview(input) {
        const file = input.files[0]; 
        if (file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) { alert('Format file tidak didukung.'); return; }
            if (file.size > 5 * 1024 * 1024) { alert('Ukuran file terlalu besar. Maksimal 5MB.'); return; }
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContainer.innerHTML = ''; 
                const img = document.createElement('img');
                img.src = e.target.result;
                previewContainer.appendChild(img);
                previewContainer.style.display = 'flex'; 
                uploadButton.style.display = 'none'; 
            };
            reader.readAsDataURL(file);
        }
    }