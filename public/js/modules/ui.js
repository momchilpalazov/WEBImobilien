// UI State
let darkMode = false;
let sidebarCollapsed = false;

// Event handlers
const handleDarkModeToggle = () => {
    darkMode = !darkMode;
    updateTheme();
    saveUIPreferences();
};

const handleSidebarToggle = () => {
    sidebarCollapsed = !sidebarCollapsed;
    updateSidebar();
    saveUIPreferences();
};

const handleWindowResize = () => {
    updateResponsiveUI();
};

// UI updates
const updateTheme = () => {
    document.body.classList.toggle('dark-mode', darkMode);
    dispatchUIEvent('themeChange', { darkMode });
};

const updateSidebar = () => {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('collapsed', sidebarCollapsed);
        dispatchUIEvent('sidebarChange', { collapsed: sidebarCollapsed });
    }
};

const updateResponsiveUI = () => {
    const isMobile = window.innerWidth < 768;
    const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
    
    document.body.classList.toggle('is-mobile', isMobile);
    document.body.classList.toggle('is-tablet', isTablet);
    
    // Auto-collapse sidebar on mobile
    if (isMobile && !sidebarCollapsed) {
        sidebarCollapsed = true;
        updateSidebar();
    }
};

// Loading state management
const showLoading = (container = document.body) => {
    const loader = document.createElement('div');
    loader.className = 'loading-overlay';
    loader.innerHTML = `
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Loading...</p>
        </div>
    `;
    container.appendChild(loader);
};

const hideLoading = (container = document.body) => {
    const loader = container.querySelector('.loading-overlay');
    if (loader) {
        loader.remove();
    }
};

// Modal management
const showModal = (modalId) => {
    const modal = document.querySelector(`#${modalId}`);
    if (modal) {
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        dispatchUIEvent('modalShow', { modalId });
    }
};

const hideModal = (modalId) => {
    const modal = document.querySelector(`#${modalId}`);
    if (modal) {
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        dispatchUIEvent('modalHide', { modalId });
    }
};

// Toast notifications
const showToast = (message, type = 'info', duration = 3000) => {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-message">${message}</span>
        </div>
    `;
    
    document.querySelector('.toast-container').appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, duration);
};

// Helper functions
const dispatchUIEvent = (type, data = null) => {
    const event = new CustomEvent(`ui:${type}`, {
        detail: data,
        bubbles: true
    });
    document.dispatchEvent(event);
};

const saveUIPreferences = () => {
    const preferences = {
        darkMode,
        sidebarCollapsed
    };
    localStorage.setItem('uiPreferences', JSON.stringify(preferences));
};

const loadUIPreferences = () => {
    try {
        const preferences = JSON.parse(localStorage.getItem('uiPreferences'));
        if (preferences) {
            darkMode = preferences.darkMode;
            sidebarCollapsed = preferences.sidebarCollapsed;
            updateTheme();
            updateSidebar();
        }
    } catch (error) {
        console.error('Error loading UI preferences:', error);
    }
};

// Initialize UI module
export const initializeUI = () => {
    // Add event listeners
    const darkModeToggle = document.querySelector('#darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', handleDarkModeToggle);
    }

    const sidebarToggle = document.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', handleSidebarToggle);
    }

    window.addEventListener('resize', handleWindowResize);

    // Initialize UI state
    loadUIPreferences();
    updateResponsiveUI();

    // Create toast container if it doesn't exist
    if (!document.querySelector('.toast-container')) {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
};

// Export UI module
export const ui = {
    showLoading,
    hideLoading,
    showModal,
    hideModal,
    showToast,
    toggleDarkMode: handleDarkModeToggle,
    toggleSidebar: handleSidebarToggle,
    isDarkMode: () => darkMode,
    isSidebarCollapsed: () => sidebarCollapsed
}; 