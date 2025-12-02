function showNotification(message, type) {
    const notif = document.createElement("div");
    notif.className = `notification ${type}`;
    notif.textContent = message;
    document.body.appendChild(notif);
    setTimeout(() => notif.classList.add("show"), 100);
    setTimeout(() => {
        notif.classList.remove("show");
        setTimeout(() => notif.remove(), 400);
    }, 3000);
}
function togglePassword(id, icon) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "visibility";
    } else {
        input.type = "password";
        icon.textContent = "visibility_off";
    }
}
document.getElementById("registerForm").addEventListener("submit", function(e) {
    const username = document.getElementById("username").value.trim();
    const nama = document.getElementById("nama").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    if (username === "" || nama === "" || email === "" || password === "") {
        e.preventDefault();
        showNotification("Semua kolom wajib diisi!", "error");
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        e.preventDefault();
        showNotification("Format email tidak valid!", "error");
        return;
    }
    if (password.length < 8) {
        e.preventDefault();
        showNotification("Password minimal 8 karakter!", "error");
        return;
    }
});

