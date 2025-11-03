// ============================================
// GLOBAL.JS - UKOPIA BACKOFFICE (UPDATED)
// ============================================

// === NOTIFICATION SYSTEM ===
function showNotification(message, type = 'info') {
  const oldNotif = document.querySelector('.notification');
  if (oldNotif) oldNotif.remove();

  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  
  let icon = 'ℹ';
  if (type === 'success') icon = '✓';
  if (type === 'error') icon = '✕';
  if (type === 'warning') icon = '⚠';
  
  notification.innerHTML = `
    <div class="notification-content">
      <span class="notification-icon">${icon}</span>
      <span class="notification-message">${message}</span>
      <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
    </div>
  `;
  
  document.body.appendChild(notification);
  setTimeout(() => notification.classList.add('show'), 10);
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => notification.remove(), 300);
  }, 4000);
}

// === LOADING STATE ===
function showLoading(message = 'Loading...') {
  const oldLoader = document.getElementById('global-loader');
  if (oldLoader) oldLoader.remove();

  const loader = document.createElement('div');
  loader.id = 'global-loader';
  loader.className = 'loader-overlay';
  loader.innerHTML = `
    <div class="loader-content">
      <div class="loader-spinner"></div>
      <p class="loader-text">${message}</p>
    </div>
  `;
  document.body.appendChild(loader);
}

function hideLoading() {
  const loader = document.getElementById('global-loader');
  if (loader) loader.remove();
}

// ============================================
// FORM AUTO-SAVE - Universal
// ============================================
function initFormAutoSave(form) {
  if (!form) return;
  
  const formId = form.id;
  const inputs = form.querySelectorAll('input[type="text"], input[type="date"], input[type="email"], input[type="number"], textarea, select');
  
  inputs.forEach(input => {
    // Load saved value
    const savedValue = localStorage.getItem(`${formId}_${input.name}`);
    if (savedValue && !input.value) {
      input.value = savedValue;
    }
    
    // Save on input
    input.addEventListener('input', () => {
      localStorage.setItem(`${formId}_${input.name}`, input.value);
    });
  });
  
  // Clear saved data on successful submit
  form.addEventListener('submit', () => {
    setTimeout(() => {
      inputs.forEach(input => {
        localStorage.removeItem(`${formId}_${input.name}`);
      });
    }, 1000);
  });
}

function loadSavedFormData() {
  const forms = document.querySelectorAll('form[id]');
  forms.forEach(form => {
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], textarea, select');
    let hasSavedData = false;
    
    inputs.forEach(input => {
      const savedValue = localStorage.getItem(`${form.id}_${input.name}`);
      if (savedValue) {
        hasSavedData = true;
      }
    });
    
    if (hasSavedData) {
      showNotification('Data form sebelumnya berhasil dipulihkan', 'info');
    }
  });
}

// ============================================
// IMAGE PREVIEW - Universal
// ============================================

/**
 * Single Image Preview (untuk menu, product, dll)
 * @param {HTMLInputElement} input - File input element
 * @param {string} previewContainerId - ID container preview
 * @param {string} uploadButtonId - ID button upload (optional, akan disembunyikan)
 */
function handleImagePreview(input, previewContainerId = 'imagePreview', uploadButtonId = 'uploadButton') {
  const file = input.files[0];
  if (!file) return;

  // Validasi tipe file
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
  if (!validTypes.includes(file.type)) {
    showNotification('Format file tidak didukung! Gunakan JPG, PNG, atau WEBP.', 'error');
    input.value = '';
    return;
  }

  // Validasi ukuran (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    showNotification('Ukuran file terlalu besar! Maksimal 5MB.', 'warning');
    input.value = '';
    return;
  }

  const previewContainer = document.getElementById(previewContainerId);
  const uploadButton = document.getElementById(uploadButtonId);

  if (!previewContainer) {
    console.error(`Preview container #${previewContainerId} tidak ditemukan`);
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    previewContainer.innerHTML = '';
    const img = document.createElement('img');
    img.src = e.target.result;
    img.alt = 'Preview Image';
    previewContainer.appendChild(img);
    previewContainer.style.display = 'flex';
    
    if (uploadButton) {
      uploadButton.style.display = 'none';
    }
  };
  
  reader.onerror = function() {
    showNotification('Gagal membaca file gambar', 'error');
  };
  
  reader.readAsDataURL(file);
}

// ============================================
// TABLE SEARCH - Universal & Reusable
// ============================================

/**
 * Universal Table Search Function
 * @param {string} searchInputId - ID input search
 * @param {string} tableBodySelector - Selector tbody (e.g., '.gallery-table tbody')
 * @param {Array} searchColumns - Array index kolom yang mau disearch (e.g., [1, 2, 3])
 */
function initTableSearch(searchInputId, tableBodySelector, searchColumns = []) {
  const searchInput = document.getElementById(searchInputId);
  const tbody = document.querySelector(tableBodySelector);

  if (!searchInput || !tbody) {
    console.warn(`Search init failed: ${searchInputId} or ${tableBodySelector} not found`);
    return;
  }

  searchInput.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = tbody.getElementsByTagName('tr');
    let visibleCount = 0;

    for (let i = 0; i < rows.length; i++) {
      const cells = rows[i].getElementsByTagName('td');
      let found = false;

      // Jika searchColumns kosong, search semua kolom kecuali kolom aksi (terakhir)
      const colsToSearch = searchColumns.length > 0 
        ? searchColumns 
        : Array.from({length: cells.length - 1}, (_, i) => i + 1); // Skip kolom 0 (No) dan terakhir (Aksi)

      for (let j of colsToSearch) {
        if (cells[j]) {
          const cellText = cells[j].textContent || cells[j].innerText;
          if (cellText.toLowerCase().indexOf(filter) > -1) {
            found = true;
            break;
          }
        }
      }

      if (found) {
        rows[i].style.display = '';
        visibleCount++;
      } else {
        rows[i].style.display = 'none';
      }
    }

    // Show empty state if no results
    const colCount = tbody.querySelector('tr')?.cells.length || 5;
    let emptyState = tbody.querySelector('.empty-search-row');
    
    if (visibleCount === 0 && filter !== '') {
      if (!emptyState) {
        emptyState = document.createElement('tr');
        emptyState.className = 'empty-search-row';
        emptyState.innerHTML = `
          <td colspan="${colCount}" style="text-align: center; padding: 40px;">
            <div class="empty-state">
              <i class="fas fa-search" style="font-size: 2.5rem; color: #d1d5db; margin-bottom: 10px;"></i>
              <p style="color: #6b7280; margin: 0;">Tidak ada hasil untuk "<strong>${escapeHtml(filter)}</strong>"</p>
            </div>
          </td>
        `;
        tbody.appendChild(emptyState);
      }
    } else {
      if (emptyState) {
        emptyState.remove();
      }
    }
  });
}

// === TABLE UTILITIES ===
function renderTable(data, renderRow, options = {}) {
  const {
    tableBodyId = 'tableBody',
    emptyMessage = 'Tidak ada data',
    emptyIcon = 'fa-inbox',
    currentPage = 1,
    itemsPerPage = 10,
    showingCountId = 'showingCount',
    totalCountId = 'totalCount',
    paginationId = 'pagination',
    colspan = 7
  } = options;

  const tbody = document.getElementById(tableBodyId);
  if (!tbody) return;

  const totalCount = data.length;
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const paginatedData = data.slice(startIndex, endIndex);
  
  if (paginatedData.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="${colspan}">
          <div class="empty-state">
            <i class="fas ${emptyIcon}"></i>
            <p>${emptyMessage}</p>
          </div>
        </td>
      </tr>
    `;
    const showingEl = document.getElementById(showingCountId);
    const totalEl = document.getElementById(totalCountId);
    if (showingEl) showingEl.textContent = '0';
    if (totalEl) totalEl.textContent = '0';
    return;
  }
  
  tbody.innerHTML = paginatedData.map((item, index) => 
    renderRow(item, startIndex + index + 1)
  ).join('');
  
  const showingEl = document.getElementById(showingCountId);
  const totalEl = document.getElementById(totalCountId);
  if (showingEl) showingEl.textContent = paginatedData.length;
  if (totalEl) totalEl.textContent = totalCount;
  
  renderPagination(totalCount, currentPage, itemsPerPage, paginationId);
}

function renderPagination(totalItems, currentPage, itemsPerPage, paginationId = 'pagination') {
  const totalPages = Math.ceil(totalItems / itemsPerPage);
  const pagination = document.getElementById(paginationId);
  
  if (!pagination) return;
  if (totalPages <= 1) {
    pagination.innerHTML = '';
    return;
  }
  
  let html = `
    <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
      <i class="fas fa-chevron-left"></i>
    </button>
  `;
  
  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, currentPage + 2);
  if (currentPage <= 3) endPage = Math.min(5, totalPages);
  if (currentPage >= totalPages - 2) startPage = Math.max(1, totalPages - 4);
  
  for (let i = startPage; i <= endPage; i++) {
    html += `
      <button onclick="changePage(${i})" class="${i === currentPage ? 'active' : ''}">
        ${i}
      </button>
    `;
  }
  
  html += `
    <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
      <i class="fas fa-chevron-right"></i>
    </button>
  `;
  
  pagination.innerHTML = html;
}

// === DATE UTILITIES ===
function isValidDate(dateString) {
  const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
  if (!dateString.match(regex)) return false;
  
  const [, day, month, year] = dateString.match(regex);
  const d = parseInt(day), m = parseInt(month), y = parseInt(year);
  if (m < 1 || m > 12 || d < 1 || d > 31 || y < 1900 || y > 2100) return false;

  const daysInMonth = [
    31, (y % 4 === 0 && y % 100 !== 0) || y % 400 === 0 ? 29 : 28,
    31, 30, 31, 30, 31, 31, 30, 31, 30, 31
  ];
  return d <= daysInMonth[m - 1];
}

function formatDateInput(input) {
  let value = input.value.replace(/\D/g, '');
  if (value.length >= 2) value = value.slice(0, 2) + '/' + value.slice(2);
  if (value.length >= 5) value = value.slice(0, 5) + '/' + value.slice(5, 9);
  input.value = value;
}

function getTodayDate() {
  const today = new Date();
  const dd = String(today.getDate()).padStart(2, '0');
  const mm = String(today.getMonth() + 1).padStart(2, '0');
  const yyyy = today.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}

function initDateInput(inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  input.addEventListener('input', (e) => formatDateInput(e.target));
  input.addEventListener('blur', (e) => {
    const value = e.target.value;
    if (value && !isValidDate(value)) {
      showNotification('Format tanggal tidak valid! Gunakan dd/mm/yyyy', 'error');
      e.target.focus();
    }
  });
}

// === FORMAT UTILITIES ===
function formatRupiah(number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(number);
}

function toRupiah(number) {
  return new Intl.NumberFormat('id-ID').format(number);
}

function fromRupiah(rupiahString) {
  return parseInt(rupiahString.replace(/[^0-9]/g, '')) || 0;
}

function formatDate(dateString, format = 'full') {
  if (!dateString) return '-';
  const [day, month, year] = dateString.split('/');
  const date = new Date(year, month - 1, day);
  if (format === 'full') {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
  }
  return dateString;
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function truncate(text, maxLength = 100, suffix = '...') {
  if (!text || text.length <= maxLength) return text;
  return text.substring(0, maxLength).trim() + suffix;
}

// ============================================
// GLOBAL POPUP SYSTEM
// ============================================
function openPopup(title = "Popup", contentHTML = "", options = {}) {
  closePopup();

  const { width = "600px", showClose = true } = options;

  const popup = document.createElement("div");
  popup.className = "popup-overlay";
  popup.innerHTML = `
    <div class="popup-card" style="max-width:${width};">
      <div class="popup-header">
        <h3>${escapeHtml(title)}</h3>
        ${showClose ? `<button class="popup-close" onclick="closePopup()">×</button>` : ""}
      </div>
      <div class="popup-body">${contentHTML}</div>
    </div>
  `;
  document.body.appendChild(popup);
  setTimeout(() => popup.classList.add("show"), 10);
}

function closePopup() {
  const popup = document.querySelector(".popup-overlay");
  if (popup) {
    popup.classList.remove("show");
    setTimeout(() => popup.remove(), 200);
  }
}

// ============================================
// DEBOUNCE UTILITY
// ============================================
function debounce(func, wait = 300) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// === CONSOLE MESSAGE ===
console.log('%cUKOPIA BackOffice', 'font-size: 20px; font-weight: bold; color: #667eea;');
console.log('%cGlobal utilities loaded successfully! 🚀', 'color: #10b981; font-weight: bold;');