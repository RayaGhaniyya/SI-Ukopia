



const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggleBtn");
const dropdownToggles = document.querySelectorAll(".dropdown-toggle");


function loadSidebarState() {
  const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  if (isCollapsed) {
    sidebar.classList.add('collapsed');
    document.body.classList.add('sidebar-collapsed');
  }
}


function saveSidebarState(isCollapsed) {
  localStorage.setItem('sidebarCollapsed', isCollapsed);
}


toggleBtn.addEventListener("click", () => {
  const isCollapsed = sidebar.classList.toggle("collapsed");
  document.body.classList.toggle("sidebar-collapsed");
  saveSidebarState(isCollapsed);
  
  
  setTimeout(() => {
    updateDropdownPositions();
  }, 400); 
});


dropdownToggles.forEach(toggle => {
  toggle.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    const dropdown = toggle.nextElementSibling;
    if (!dropdown) return;
    
    const isCollapsed = sidebar.classList.contains('collapsed');
    const isCurrentlyOpen = dropdown.classList.contains('show');
    
    
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
      if (menu !== dropdown) {
        menu.classList.remove('show');
        const otherToggle = menu.previousElementSibling;
        if (otherToggle) {
          otherToggle.classList.remove('active');
        }
      }
    });
    
    
    if (isCurrentlyOpen) {
      dropdown.classList.remove('show');
      toggle.classList.remove('active');
    } else {
      dropdown.classList.add('show');
      toggle.classList.add('active');
      
      
      if (isCollapsed) {
        updateDropdownPosition(dropdown, toggle);
      }
    }
  });
});


function updateDropdownPosition(dropdown, toggle) {
  if (!dropdown || !toggle) return;
  
  const parentRect = toggle.parentElement.getBoundingClientRect();
  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  
  
  dropdown.style.top = `${parentRect.top + scrollTop}px`;
}


function updateDropdownPositions() {
  const isCollapsed = sidebar.classList.contains('collapsed');
  if (!isCollapsed) {
    
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


let scrollTimeout;
let lastScrollTop = 0;

window.addEventListener('scroll', () => {
  const isCollapsed = sidebar.classList.contains('collapsed');
  if (!isCollapsed) return;
  
  const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
  
  
  if (Math.abs(currentScrollTop - lastScrollTop) < 5) return;
  
  lastScrollTop = currentScrollTop;
  
  clearTimeout(scrollTimeout);
  
  
  updateDropdownPositions();
  
  
  scrollTimeout = setTimeout(() => {
    updateDropdownPositions();
  }, 50);
}, { passive: true }); 


function setActiveMenu() {
  const currentPath = window.location.pathname;
  const menuLinks = document.querySelectorAll('.nav-list a');
  
  menuLinks.forEach(link => {
    const href = link.getAttribute('href');
    
    
    if (!href || href === '#' || link.classList.contains('dropdown-toggle')) {
      return;
    }
    
    
    link.classList.remove('active');
    
    
    if (currentPath.includes(href)) {
      link.classList.add('active');
      
      
      const parentDropdown = link.closest('.dropdown-menu');
      if (parentDropdown) {
        parentDropdown.classList.add('show');
        const toggle = parentDropdown.previousElementSibling;
        if (toggle) {
          toggle.classList.add('active');
        }
        
        
        if (sidebar.classList.contains('collapsed')) {
          updateDropdownPosition(parentDropdown, toggle);
        }
      }
    }
  });
}


function smoothScrollToActiveItem() {
  const activeItem = document.querySelector('.nav-list a.active');
  
  
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


let resizeTimeout;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(() => {
    updateDropdownPositions();
  }, 150);
}, { passive: true });


const bodyPaddingObserver = new MutationObserver(() => {
  const isCollapsed = sidebar.classList.contains('collapsed');
  
  document.body.style.paddingLeft = isCollapsed ? '70px' : '240px';
});

bodyPaddingObserver.observe(sidebar, {
  attributes: true,
  attributeFilter: ['class']
});


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


document.querySelectorAll('.dropdown-menu').forEach(menu => {
  menu.addEventListener('click', (e) => {
    e.stopPropagation();
  });
});


document.addEventListener('DOMContentLoaded', () => {
  loadSidebarState();
  setActiveMenu();
  addTooltips();
  
  
  const isCollapsed = sidebar.classList.contains('collapsed');
  document.body.style.paddingLeft = isCollapsed ? '70px' : '240px';
  
  
  setTimeout(smoothScrollToActiveItem, 500);
});


window.addEventListener('beforeunload', () => {
  bodyPaddingObserver.disconnect();
});


