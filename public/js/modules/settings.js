// Import UI module for notifications
import { ui } from './ui.js';

// Settings state
let settings = {
    general: {},
    notifications: {},
    security: {},
    appearance: {}
};

// Default settings
const DEFAULT_SETTINGS = {
    general: {
        language: 'en',
        timezone: 'UTC',
        dateFormat: 'DD/MM/YYYY',
        timeFormat: '24h'
    },
    notifications: {
        email: true,
        push: true,
        sound: true,
        desktop: true
    },
    security: {
        twoFactorAuth: false,
        sessionTimeout: 30,
        passwordExpiry: 90,
        loginAttempts: 5
    },
    appearance: {
        theme: 'light',
        fontSize: 'medium',
        compactMode: false,
        animationsEnabled: true
    }
};

// Event handlers
const handleSettingChange = async (category, setting, value) => {
    try {
        ui.showLoading(document.querySelector(`#${category}Settings`));
        
        const response = await updateSetting(category, setting, value);
        if (response.success) {
            settings[category][setting] = value;
            ui.showToast('Setting updated successfully', 'success');
            
            // Handle special settings
            handleSpecialSetting(category, setting, value);
        }
    } catch (error) {
        ui.showToast('Failed to update setting', 'error');
        console.error('Settings update error:', error);
        
        // Revert UI to previous state
        revertSettingUI(category, setting);
    } finally {
        ui.hideLoading(document.querySelector(`#${category}Settings`));
    }
};

const handleResetSettings = async (category) => {
    if (!confirm(`Are you sure you want to reset ${category} settings to default?`)) {
        return;
    }

    try {
        ui.showLoading(document.querySelector(`#${category}Settings`));
        
        const response = await resetSettings(category);
        if (response.success) {
            settings[category] = { ...DEFAULT_SETTINGS[category] };
            ui.showToast(`${category} settings reset to default`, 'success');
            updateSettingsUI();
        }
    } catch (error) {
        ui.showToast('Failed to reset settings', 'error');
        console.error('Settings reset error:', error);
    } finally {
        ui.hideLoading(document.querySelector(`#${category}Settings`));
    }
};

// API calls
const fetchSettings = async () => {
    try {
        const response = await fetch('/api/settings');
        if (!response.ok) throw new Error('Failed to fetch settings');
        
        const data = await response.json();
        settings = {
            ...settings,
            ...data.settings
        };
        
        updateSettingsUI();
    } catch (error) {
        console.error('Error fetching settings:', error);
        ui.showToast('Failed to load settings', 'error');
    }
};

const updateSetting = async (category, setting, value) => {
    const response = await fetch('/api/settings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            category,
            setting,
            value
        })
    });
    
    if (!response.ok) throw new Error('Failed to update setting');
    return response.json();
};

const resetSettings = async (category) => {
    const response = await fetch(`/api/settings/${category}/reset`, {
        method: 'POST'
    });
    
    if (!response.ok) throw new Error('Failed to reset settings');
    return response.json();
};

// UI updates
const updateSettingsUI = () => {
    // Update form inputs
    Object.entries(settings).forEach(([category, categorySettings]) => {
        Object.entries(categorySettings).forEach(([setting, value]) => {
            const input = document.querySelector(`#setting_${category}_${setting}`);
            if (!input) return;

            if (input.type === 'checkbox') {
                input.checked = value;
            } else {
                input.value = value;
            }
        });
    });
};

const revertSettingUI = (category, setting) => {
    const input = document.querySelector(`#setting_${category}_${setting}`);
    if (!input) return;

    if (input.type === 'checkbox') {
        input.checked = settings[category][setting];
    } else {
        input.value = settings[category][setting];
    }
};

// Special settings handlers
const handleSpecialSetting = (category, setting, value) => {
    switch (`${category}.${setting}`) {
        case 'appearance.theme':
            document.body.dataset.theme = value;
            break;
        case 'appearance.fontSize':
            document.documentElement.style.fontSize = getFontSizeValue(value);
            break;
        case 'appearance.animationsEnabled':
            document.body.classList.toggle('animations-disabled', !value);
            break;
        case 'security.twoFactorAuth':
            if (value) {
                showTwoFactorSetup();
            }
            break;
    }
};

const getFontSizeValue = (size) => {
    const sizes = {
        small: '14px',
        medium: '16px',
        large: '18px'
    };
    return sizes[size] || sizes.medium;
};

const showTwoFactorSetup = () => {
    ui.showModal('twoFactorSetupModal');
};

// Initialize settings module
export const initializeSettings = () => {
    // Fetch initial settings
    fetchSettings();

    // Add event listeners for settings forms
    document.querySelectorAll('.settings-form').forEach(form => {
        form.addEventListener('change', (event) => {
            const input = event.target;
            const [category, setting] = input.id.replace('setting_', '').split('_');
            const value = input.type === 'checkbox' ? input.checked : input.value;
            
            handleSettingChange(category, setting, value);
        });
    });

    // Add event listeners for reset buttons
    document.querySelectorAll('.reset-settings').forEach(button => {
        button.addEventListener('click', () => {
            const category = button.dataset.category;
            handleResetSettings(category);
        });
    });
};

// Export settings module
export const settingsModule = {
    getAll: () => settings,
    get: (category, setting) => settings[category]?.[setting],
    update: handleSettingChange,
    reset: handleResetSettings,
    getDefaults: () => DEFAULT_SETTINGS
}; 