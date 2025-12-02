function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    let iconClass = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';
    const toast = document.createElement('div');
    toast.classList.add('toast-box', type);
    
    toast.innerHTML = `
        <i class="fa-solid ${iconClass}"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s forwards';
        setTimeout(() => { toast.remove(); }, 300);
    }, 3000);
}

function showConfirm(message, onConfirm, title = "Konfirmasi Hapus", btnText = "Hapus") {
    const existing = document.querySelector('.confirm-overlay');
    if(existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.className = 'confirm-overlay';

    overlay.innerHTML = `
        <div class="confirm-box">
            <div class="confirm-icon">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3>${title}</h3>
            <p>${message}</p>
            <div class="confirm-actions">
                <button class="confirm-btn btn-cancel">Batal</button>
                <button class="confirm-btn btn-confirm">${btnText}</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    const btnCancel = overlay.querySelector('.btn-cancel');
    const btnConfirm = overlay.querySelector('.btn-confirm');

    btnCancel.addEventListener('click', () => {
        overlay.remove();
    });

    btnConfirm.addEventListener('click', () => {
        onConfirm(); // Jalankan aksi
        overlay.remove(); 
    });
    
    overlay.addEventListener('click', (e) => {
        if(e.target === overlay) overlay.remove();
    });
}
