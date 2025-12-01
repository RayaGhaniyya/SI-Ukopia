// ============================================
// SIDEBAR.JS - UKOPIA BACKOFFICE (FIXED & OPTIMIZED)
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
  
  // Update dropdown positions after toggle
  setTimeout(() => {
    updateDropdownPositions();
  }, 400); // Match transition duration
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
        updateDropdownPosition(dropdown, toggle);
      }
    }
  });
});

// === UPDATE DROPDOWN POSITION (FIXED) ===
function updateDropdownPosition(dropdown, toggle) {
  if (!dropdown || !toggle) return;
  
  const parentRect = toggle.parentElement.getBoundingClientRect();
  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  
  // FIXED: Calculate position considering window scroll
  dropdown.style.top = `${parentRect.top + scrollTop}px`;
}

// === UPDATE ALL DROPDOWN POSITIONS ===
function updateDropdownPositions() {
  const isCollapsed = sidebar.classList.contains('collapsed');
  if (!isCollapsed) {
    // Reset top style when not collapsed
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
      menu.style.top = '';
    });
    return;
  }
  
  document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
    const toggle = menu.previousElementSibling;
    if (toggle) {
      updateDropdownPosition(menu, toggle);
    }
  });
}

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

// === UPDATE DROPDOWN POSITION ON SCROLL (OPTIMIZED) ===
let scrollTimeout;
let lastScrollTop = 0;

window.addEventListener('scroll', () => {
  const isCollapsed = sidebar.classList.contains('collapsed');
  if (!isCollapsed) return;
  
  const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
  
  // Only update if scroll delta is significant (performance optimization)
  if (Math.abs(currentScrollTop - lastScrollTop) < 5) return;
  
  lastScrollTop = currentScrollTop;
  
  clearTimeout(scrollTimeout);
  
  // Update immediately for smooth tracking
  updateDropdownPositions();
  
  // Also update after scroll stops (cleanup)
  scrollTimeout = setTimeout(() => {
    updateDropdownPositions();
  }, 50);
}, { passive: true }); // FIXED: Add passive flag for better performance

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
          updateDropdownPosition(parentDropdown, toggle);
        }
      }
    }
  });
}

// === SMOOTH SCROLL UNTUK DROPDOWN (WITH NULL CHECK) ===
function smoothScrollToActiveItem() {
  const activeItem = document.querySelector('.nav-list a.active');
  
  // FIXED: Add null check and collapsed check
  if (!activeItem || sidebar.classList.contains('collapsed')) {
    return;
  }
  
  try {
    activeItem.scrollIntoView({
      behavior: 'smooth',
      block: 'center'
    });
  } catch (error) {
    console.warn('Smooth scroll not supported:', error);
  }
}

// === HANDLE WINDOW RESIZE (OPTIMIZED) ===
let resizeTimeout;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(() => {
    updateDropdownPositions();
  }, 150);
}, { passive: true });

// === UPDATE BODY PADDING SAAT SIDEBAR TOGGLE (FIXED) ===
const bodyPaddingObserver = new MutationObserver(() => {
  const isCollapsed = sidebar.classList.contains('collapsed');
  // FIXED: Match actual sidebar width
  document.body.style.paddingLeft = isCollapsed ? '70px' : '240px';
});

bodyPaddingObserver.observe(sidebar, {
  attributes: true,
  attributeFilter: ['class']
});

// === TOOLTIP UNTUK COLLAPSED SIDEBAR ===
function addTooltips() {
  const menuItems = document.querySelectorAll('.nav-list > li > a');
  
  menuItems.forEach(item => {
    const span = item.querySelector('span');
    if (span) {
      item.setAttribute('title', span.textContent);
      item.setAttribute('data-title', span.textContent);
    }
  });
}

// === PREVENT DROPDOWN CLOSE ON INTERNAL CLICK ===
document.querySelectorAll('.dropdown-menu').forEach(menu => {
  menu.addEventListener('click', (e) => {
    e.stopPropagation();
  });
});

// === INIT ON PAGE LOAD ===
document.addEventListener('DOMContentLoaded', () => {
  loadSidebarState();
  setActiveMenu();
  addTooltips();
  
  // Set initial body padding
  const isCollapsed = sidebar.classList.contains('collapsed');
  document.body.style.paddingLeft = isCollapsed ? '70px' : '240px';
  
  // Optional: smooth scroll ke active item setelah 500ms
  setTimeout(smoothScrollToActiveItem, 500);
});

// === CLEANUP ON PAGE UNLOAD (PREVENT MEMORY LEAK) ===
window.addEventListener('beforeunload', () => {
  bodyPaddingObserver.disconnect();
});

// === DEBUG MODE (REMOVE IN PRODUCTION) ===
// console.log('%c✓ Sidebar Enhanced Loaded (Fixed & Optimized)', 'color: #10b981; font-weight: bold;');