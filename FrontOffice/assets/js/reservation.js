document.addEventListener("DOMContentLoaded", () => {
    
    // Ambil semua elemen penting
    const dayButtons = document.querySelectorAll(".days .day-btn");
    const hiddenDay = document.getElementById("selectedDay");
    
    const hourBtn = document.getElementById("hourBtn");
    const minuteBtn = document.getElementById("minuteBtn");
    const hiddenTime = document.getElementById("selectedTime");

    const hourListItems = document.querySelectorAll(".hour-list li");
    const minuteListItems = document.querySelectorAll(".minute-list li");

    const form = document.getElementById("reservationForm");
    const confirmBtn = document.getElementById("confirmBtn");

    // === FUNGSI UNTUK DROPDOWN ===
    document.querySelectorAll(".custom-dropdown").forEach(drop => {
        const btn = drop.querySelector(".dropdown-btn");
        const items = drop.querySelectorAll(".dropdown-list li");
        if (!btn) return;

        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            if (btn.disabled) return; 

            document.querySelectorAll(".custom-dropdown.active").forEach(d => {
                if (d !== drop) d.classList.remove("active");
            });
            drop.classList.toggle("active");
        });

        items.forEach(item => {
            item.addEventListener("click", () => {
                if (item.classList.contains('disabled')) return;
                
                btn.textContent = item.textContent;
                drop.classList.remove("active");
                
                updateSelectedTime(); 
                
                if (drop.querySelector('.hour-list')) {
                    checkSlotAvailability();
                }
            });
        });
    });

    document.addEventListener("click", () => {
        document.querySelectorAll(".custom-dropdown.active").forEach(d => d.classList.remove("active"));
    });

    // === FUNGSI UNTUK TOMBOL HARI ===
    dayButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            if (btn.disabled) return; 

            dayButtons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            
            hiddenDay.value = btn.dataset.value;
            
            checkSlotAvailability();
        });
    });

    // === FUNGSI UPDATE WAKTU ===
    function updateSelectedTime() {
        const hour = hourBtn.textContent.trim();
        const minute = minuteBtn.textContent.trim();
        hiddenTime.value = `${hour}:${minute}:00`;
    }

    // ==========================================================
    // === FUNGSI CEK SLOT (LOGIKA UTAMA) - (FIXED) ===
    // ==========================================================
    function checkSlotAvailability() {
        
        // Cek apakah variabel dari PHP sudah dimuat
        if (typeof bookedHours === 'undefined' || typeof serverTime === 'undefined') {
            console.error("bookedHours atau serverTime tidak ditemukan. (Cek file index.php)");
            return;
        }

        // --- Gunakan variabel 'serverTime' dari PHP (Anti-Timezone Bug) ---
        const todayDateStr = serverTime.todayDateStr;     // "2025-11-05"
        const currentHour = serverTime.currentHour;     // Misal: 13 (dari 13:01)
        const currentMinute = serverTime.currentMinute;   // Misal: 01 (dari 13:01)

        const selectedDate = hiddenDay.value; 
        
        // Cek apakah tanggal yang dipilih adalah HARI INI
        const isToday = (selectedDate === todayDateStr);
        
        const hoursBookedOnThisDate = bookedHours[selectedDate] || {};

        // --- 1. Cek Ketersediaan JAM ---
        hourListItems.forEach(hourItem => {
            const hour = parseInt(hourItem.dataset.value, 10); // "10", "11", "12"
            
            const isBooked = !!hoursBookedOnThisDate[hour];
            
            // Cek jam lewat
            // (isToday=true) AND (jam tombol '11' < jam server '13') -> true (disable)
            const isPast = (isToday && hour < currentHour); 
            
            if (isBooked || isPast) {
                hourItem.classList.add('disabled');
                hourItem.title = isBooked ? 'Jam ini sudah penuh' : 'Jam ini sudah lewat';
            } else {
                hourItem.classList.remove('disabled');
                hourItem.title = '';
            }
        });

        // --- 2. Cek apakah JAM yang DIPILIH SEKARANG valid ---
        const selectedHour = hourBtn.textContent.trim();
        const selectedHourEl = Array.from(hourListItems).find(li => li.dataset.value === selectedHour);
        
        if (selectedHourEl && selectedHourEl.classList.contains('disabled')) {
            let foundNewHour = false;
            for (const hourItem of hourListItems) {
                if (!hourItem.classList.contains('disabled')) {
                    hourBtn.textContent = hourItem.dataset.value; // Pindah ke jam pertama yg valid
                    foundNewHour = true;
                    break;
                }
            }
            if (!foundNewHour) {
                console.warn("Semua jam penuh di tanggal ini.");
            }
        }
        
        // --- 3. Cek Ketersediaan MENIT ---
        const finalSelectedHour = parseInt(hourBtn.textContent.trim(), 10);
        
        const isHourBooked = !!hoursBookedOnThisDate[finalSelectedHour];
        
        // Cek apakah jam yang dipilih adalah JAM SEKARANG
        // (isToday=true) AND (jam tombol '13' === jam server '13')
        const isCurrentHour = (isToday && finalSelectedHour === currentHour);

        let firstAvailableMinuteFound = false;
        minuteListItems.forEach(minItem => {
            const minute = parseInt(minItem.dataset.value, 10); // 0, 10, 20

            if (isHourBooked) {
                minItem.classList.add('disabled');
                minItem.title = 'Jam ini sudah penuh';
                return;
            }
            
            // Cek menit lewat (HANYA jika ini adalah jam sekarang)
            // (isCurrentHour=true) AND (menit tombol '0' < menit server '1') -> true (disable)
            const isMinutePast = (isCurrentHour && minute < currentMinute);
            
            if (isMinutePast) {
                 minItem.classList.add('disabled');
                 minItem.title = 'Waktu sudah lewat';
            } else {
                minItem.classList.remove('disabled');
                minItem.title = '';
                if (!firstAvailableMinuteFound) {
                    minuteBtn.textContent = minItem.dataset.value.padStart(2, '0'); 
                    firstAvailableMinuteFound = true;
                }
            }
        });
        
        // --- 4. Atur status tombol Menit & Confirm ---
        const finalSelectedHourEl = Array.from(hourListItems).find(li => li.dataset.value === String(finalSelectedHour).padStart(2,'0'));
        const isFinalHourDisabled = (finalSelectedHourEl && finalSelectedHourEl.classList.contains('disabled'));

        if (isHourBooked || isFinalHourDisabled || !firstAvailableMinuteFound) {
            if (!firstAvailableMinuteFound) {
                minuteBtn.textContent = "00";
            }
            minuteBtn.disabled = true;
            minuteBtn.title = 'Jam ini tidak tersedia';
            confirmBtn.disabled = true;
            confirmBtn.textContent = "Pilih Waktu Lain";
        } else {
            minuteBtn.disabled = false;
            minuteBtn.title = 'Pilih Menit';
            confirmBtn.disabled = false;
            confirmBtn.textContent = "Confirm";
        }
        
        // --- 5. Cek Hari ---
        dayButtons.forEach(btn => {
            if (btn.dataset.value === selectedDate && btn.disabled) {
                 confirmBtn.disabled = true;
                 if(btn.title.includes("Penuh")) {
                    confirmBtn.textContent = "Slot Penuh";
                 } else {
                    confirmBtn.textContent = "Tanggal Sudah Lewat";
                 }
            }
        });

        updateSelectedTime();
    }
    // ==========================================================
    // === AKHIR FUNGSI CHECK SLOT ===
    // ==========================================================


    // === FUNGSI SUBMIT FORM (AJAX/FETCH) ===
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault(); 
            confirmBtn.disabled = true;
            confirmBtn.textContent = "Processing...";

            const formData = new FormData(form);

            try {
                const response = await fetch("action/store.php", {
                    method: "POST",
                    body: formData
                });

                const result = await response.json(); 

                if (result.success) {
                    showPopup("✅ Reservasi berhasil! Silakan tunggu konfirmasi admin.", 'success');
                    form.reset();
                    
                    let firstAvailableDayBtn = null;
                    dayButtons.forEach(btn => {
                        if (!btn.disabled && !firstAvailableDayBtn) {
                            firstAvailableDayBtn = btn;
                        }
                    });

                    dayButtons.forEach(btn => btn.classList.remove('active'));
                    if(firstAvailableDayBtn) {
                        firstAvailableDayBtn.classList.add('active');
                        hiddenDay.value = firstAvailableDayBtn.dataset.value;
                    }

                    hourBtn.textContent = "10";
                    minuteBtn.textContent = "00";
                    updateSelectedTime();
                    
                    setTimeout(() => {
                        location.reload(); // Reload halaman untuk data slot baru
                    }, 2000); 

                } else {
                    showPopup(`🚫 ${result.message}`, 'error');
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = "Confirm";
                }

            } catch (error) {
                showPopup("🚫 Terjadi kesalahan. Coba beberapa saat lagi.", 'error');
                console.error("Error submitting form:", error);
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Confirm";
            }
        });
    }

    // === FUNGSI POPUP ===
    function showPopup(message, type = 'success') {
        const popup = document.createElement("div");
        popup.className = "reservation-popup";
        popup.textContent = message;
        
        if (type === 'error') {
            popup.style.background = "#dc3545"; // Merah
        }
        
        document.body.appendChild(popup);
        setTimeout(() => popup.classList.add("show"), 50);
        setTimeout(() => {
            popup.classList.remove("show");
            setTimeout(() => popup.remove(), 400);
        }, 3000);
    }
    
    // === Inisialisasi ===
    if (dayButtons.length > 0) {
        checkSlotAvailability(); // Cek slot saat halaman dimuat
    }
    
    // Efek focus/blur dari CSS asli kamu
    document.querySelectorAll(".input-group input").forEach(input => {
        input.addEventListener("focus", () => input.classList.add("focused"));
        input.addEventListener("blur", () => input.classList.remove("focused"));
    });
});