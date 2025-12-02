document.addEventListener("DOMContentLoaded", () => {
    const dayButtons = document.querySelectorAll(".days .day-btn");
    const hiddenDay = document.getElementById("selectedDay");
    const hourBtn = document.getElementById("hourBtn");
    const minuteBtn = document.getElementById("minuteBtn");
    const hiddenTime = document.getElementById("selectedTime");
    const hourListItems = document.querySelectorAll(".hour-list li");
    const minuteListItems = document.querySelectorAll(".minute-list li");
    const form = document.getElementById("reservationForm");
    const confirmBtn = document.getElementById("confirmBtn");
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
    dayButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            if (btn.disabled) return; 
            dayButtons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            hiddenDay.value = btn.dataset.value;
            checkSlotAvailability();
        });
    });
    function updateSelectedTime() {
        const hour = hourBtn.textContent.trim();
        const minute = minuteBtn.textContent.trim();
        hiddenTime.value = `${hour}:${minute}:00`;
    }
    function checkSlotAvailability() {
        if (typeof bookedHours === 'undefined' || typeof serverTime === 'undefined') {
            console.error("bookedHours atau serverTime tidak ditemukan.");
            return;
        }
        const todayDateStr = serverTime.todayDateStr;
        const currentHour = serverTime.currentHour;
        const currentMinute = serverTime.currentMinute;
        const selectedDate = hiddenDay.value; 
        const isToday = (selectedDate === todayDateStr);
        const hoursBookedOnThisDate = bookedHours[selectedDate] || {};
        hourListItems.forEach(hourItem => {
            const hour = parseInt(hourItem.dataset.value, 10);
            const isBooked = !!hoursBookedOnThisDate[hour];
            const isPast = (isToday && hour < currentHour); 
            if (isBooked || isPast) {
                hourItem.classList.add('disabled');
                hourItem.title = isBooked ? 'Jam ini sudah penuh' : 'Jam ini sudah lewat';
            } else {
                hourItem.classList.remove('disabled');
                hourItem.title = '';
            }
        });
        const selectedHour = hourBtn.textContent.trim();
        const selectedHourEl = Array.from(hourListItems).find(li => li.dataset.value === selectedHour);
        if (selectedHourEl && selectedHourEl.classList.contains('disabled')) {
            let foundNewHour = false;
            for (const hourItem of hourListItems) {
                if (!hourItem.classList.contains('disabled')) {
                    hourBtn.textContent = hourItem.dataset.value;
                    foundNewHour = true;
                    break;
                }
            }
            if (!foundNewHour) {
                console.warn("Semua jam penuh.");
            }
        }
        const finalSelectedHour = parseInt(hourBtn.textContent.trim(), 10);
        const isHourBooked = !!hoursBookedOnThisDate[finalSelectedHour];
        const isCurrentHour = (isToday && finalSelectedHour === currentHour);
        let firstAvailableMinuteFound = false;
        minuteListItems.forEach(minItem => {
            const minute = parseInt(minItem.dataset.value, 10); 
            if (isHourBooked) {
                minItem.classList.add('disabled');
                minItem.title = 'Jam ini sudah penuh';
                return;
            }
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
        const finalSelectedHourEl = Array.from(hourListItems).find(li => li.dataset.value === String(finalSelectedHour).padStart(2,'0'));
        const isFinalHourDisabled = (finalSelectedHourEl && finalSelectedHourEl.classList.contains('disabled'));
        if (isHourBooked || isFinalHourDisabled || !firstAvailableMinuteFound) {
            if (!firstAvailableMinuteFound) minuteBtn.textContent = "00";
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
                    showToast("Reservasi berhasil! Silakan tunggu konfirmasi admin.", 'success');
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
                        location.reload(); // Reload untuk update slot
                    }, 2000); 
                } else {
                    showToast(result.message, 'error');
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = "Confirm";
                }
            } catch (error) {
                showToast("Terjadi kesalahan sistem. Coba lagi nanti.", 'error');
                console.error("Error submitting form:", error);
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Confirm";
            }
        });
    }
    if (dayButtons.length > 0) {
        checkSlotAvailability();
    }
    document.querySelectorAll(".input-group input").forEach(input => {
        input.addEventListener("focus", () => input.classList.add("focused"));
        input.addEventListener("blur", () => input.classList.remove("focused"));
    });
});

