// ============================================
// SIDEBAR.JS - UKOPIA BACKOFFICE (FIXED)
// ============================================

const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggleBtn");
const dropdownToggles = document.querySelectorAll(".dropdown-toggle");

// === LOAD SIDEBAR STATE FROM LOCALSTORAGE ===
function loadSidebarState() {
  const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  if (isCollapsed) {
    sidebar.classList.add('collapsed');
    document.body.classList.add('sidebar-collapsed');
  }
}

// === SAVE SIDEBAR STATE ===
function saveSidebarState(isCollapsed) {
  localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// === TOGGLE SIDEBAR ===
toggleBtn.addEventListener("click", () => {
  const isCollapsed = sidebar.classList.toggle("collapsed");
  document.body.classList.toggle("sidebar-collapsed");
  saveSidebarState(isCollapsed);
  
  // PERBAIKAN: Jangan close dropdown saat toggle
  // Biarkan dropdown tetap terbuka, hanya ubah positioningnya
});

// === DROPDOWN FUNCTIONALITY ===
dropdownToggles.forEach(toggle => {
  toggle.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    const dropdown = toggle.nextElementSibling;
    if (!dropdown) return;
    
    const isCollapsed = sidebar.classList.contains('collapsed');
    const isCurrentlyOpen = dropdown.classList.contains('show');
    
    // Close other dropdowns first
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
      if (menu !== dropdown) {
        menu.classList.remove('show');
        const otherToggle = menu.previousElementSibling;
        if (otherToggle) {
          otherToggle.classList.remove('active');
        }
      }
    });
    
    // Toggle current dropdown
    if (isCurrentlyOpen) {
      dropdown.classList.remove('show');
      toggle.classList.remove('active');
    } else {
      dropdown.classList.add('show');
      toggle.classList.add('active');
      
      // Adjust position for collapsed sidebar
      if (isCollapsed) {
        const parentRect = toggle.parentElement.getBoundingClientRect();
        dropdown.style.top = `${parentRect.top}px`;
      } else {
        dropdown.style.top = '';
      }
    }
    
    console.log('Dropdown toggled:', dropdown.classList.contains('show'));
  });
});

// === CLOSE DROPDOWN WHEN CLICKING OUTSIDE ===
document.addEventListener('click', (e) => {
  if (!e.target.closest('.dropdown')) {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
      menu.classList.remove('show');
    });
    document.querySelectorAll('.dropdown-toggle.active').forEach(toggle => {
      toggle.classList.remove('active');
    });
  }
});

// === UPDATE DROPDOWN POSITION ON SCROLL (untuk collapsed mode) ===
let scrollTimeout;
window.addEventListener('scroll', () => {
  const isCollapsed = sidebar.classList.contains('collapsed');
  if (!isCollapsed) return;
  
  clearTimeout(scrollTimeout);
  scrollTimeout = setTimeout(() => {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
      const toggle = menu.previousElementSibling;
      if (toggle) {
        const parentRect = toggle.parentElement.getBoundingClientRect();
        menu.style.top = `${parentRect.top}px`;
      }
    });
  }, 10);
});

// === SET ACTIVE MENU BERDASARKAN URL ===
function setActiveMenu() {
  const currentPath = window.location.pathname;
  const menuLinks = document.querySelectorAll('.nav-list a');
  
  menuLinks.forEach(link => {
    const href = link.getAttribute('href');
    
    // Skip jika href adalah # atau dropdown toggle
    if (!href || href === '#' || link.classList.contains('dropdown-toggle')) {
      return;
    }
    
    // Remove active from all links first
    link.classList.remove('active');
    
    // Check if current path matches
    if (currentPath.includes(href)) {
      link.classList.add('active');
      
      // Jika link ada di dalam dropdown, buka dropdownnya
      const parentDropdown = link.closest('.dropdown-menu');
      if (parentDropdown) {
        parentDropdown.classList.add('show');
        const toggle = parentDropdown.previousElementSibling;
        if (toggle) {
          toggle.classList.add('active');
        }
        
        // Adjust position jika sidebar collapsed
        if (sidebar.classList.contains('collapsed')) {
          const parentRect = toggle.parentElement.getBoundingClientRect();
          parentDropdown.style.top = `${parentRect.top}px`;
        }
      }
    }
  });
}

// === SMOOTH SCROLL UNTUK DROPDOWN (OPTIONAL) ===
function smoothScrollToActiveItem() {
  const activeItem = document.querySelector('.nav-list a.active');
  if (activeItem && !sidebar.classList.contains('collapsed')) {
    activeItem.scrollIntoView({
      behavior: 'smooth',
      block: 'center'
    });
  }
}

// === HANDLE WINDOW RESIZE ===
let resizeTimeout;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(() => {
    const isCollapsed = sidebar.classList.contains('collapsed');
    
    // Update dropdown positions if collapsed
    if (isCollapsed) {
      document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        const toggle = menu.previousElementSibling;
        if (toggle) {
          const parentRect = toggle.parentElement.getBoundingClientRect();
          menu.style.top = `${parentRect.top}px`;
        }
      });
    }
  }, 100);
});

// === INIT ON PAGE LOAD ===
document.addEventListener('DOMContentLoaded', () => {
  loadSidebarState();
  setActiveMenu();
  
  // Optional: smooth scroll ke active item setelah 500ms
  setTimeout(smoothScrollToActiveItem, 500);
});

// === UPDATE BODY PADDING SAAT SIDEBAR TOGGLE ===
const bodyPaddingObserver = new MutationObserver(() => {
  const isCollapsed = sidebar.classList.contains('collapsed');
  document.body.style.paddingLeft = isCollapsed ? '90px' : '260px';
});

bodyPaddingObserver.observe(sidebar, {
  attributes: true,
  attributeFilter: ['class']
});

// === TOOLTIP UNTUK COLLAPSED SIDEBAR (OPTIONAL) ===
function addTooltips() {
  const menuItems = document.querySelectorAll('.nav-list > li > a');
  
  menuItems.forEach(item => {
    const span = item.querySelector('span');
    if (span) {
      item.setAttribute('title', span.textContent);
    }
  });
}

addTooltips();

console.log('%c✓ Sidebar Enhanced Loaded (Fixed)', 'color: #10b981; font-weight: bold;');