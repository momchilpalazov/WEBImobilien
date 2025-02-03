// Auth state
let currentUser = null;

// Event handlers
const handleLogin = async (event) => {
    event.preventDefault();
    const form = event.target;
    const email = form.querySelector('#email').value;
    const password = form.querySelector('#password').value;

    try {
        const response = await login(email, password);
        if (response.success) {
            currentUser = response.user;
            dispatchAuthEvent('login', currentUser);
            redirectToDashboard();
        }
    } catch (error) {
        handleAuthError(error);
    }
};

const handleLogout = async () => {
    try {
        await logout();
        currentUser = null;
        dispatchAuthEvent('logout');
        redirectToLogin();
    } catch (error) {
        handleAuthError(error);
    }
};

// API calls
const login = async (email, password) => {
    const response = await fetch('/api/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email, password })
    });
    
    if (!response.ok) {
        throw new Error('Login failed');
    }
    
    return response.json();
};

const logout = async () => {
    const response = await fetch('/api/auth/logout', {
        method: 'POST'
    });
    
    if (!response.ok) {
        throw new Error('Logout failed');
    }
    
    return response.json();
};

// Helper functions
const dispatchAuthEvent = (type, data = null) => {
    const event = new CustomEvent(`auth:${type}`, {
        detail: data,
        bubbles: true
    });
    document.dispatchEvent(event);
};

const handleAuthError = (error) => {
    console.error('Auth error:', error);
    dispatchAuthEvent('error', error.message);
};

const redirectToDashboard = () => {
    window.location.href = '/dashboard';
};

const redirectToLogin = () => {
    window.location.href = '/login';
};

// Initialize auth module
export const initializeAuth = () => {
    // Add event listeners
    const loginForm = document.querySelector('#loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    const logoutButton = document.querySelector('#logoutButton');
    if (logoutButton) {
        logoutButton.addEventListener('click', handleLogout);
    }

    // Check initial auth state
    checkAuthState();
};

// Check current auth state
const checkAuthState = async () => {
    try {
        const response = await fetch('/api/auth/check');
        const data = await response.json();
        
        if (data.authenticated) {
            currentUser = data.user;
            dispatchAuthEvent('stateChange', currentUser);
        } else {
            currentUser = null;
            dispatchAuthEvent('stateChange', null);
        }
    } catch (error) {
        handleAuthError(error);
    }
};

// Export auth module
export const auth = {
    getCurrentUser: () => currentUser,
    isAuthenticated: () => currentUser !== null,
    login,
    logout,
    checkAuthState
}; 