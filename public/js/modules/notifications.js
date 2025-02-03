// Import UI module for toast notifications
import { ui } from './ui.js';

// Notification types
const NOTIFICATION_TYPES = {
    INFO: 'info',
    SUCCESS: 'success',
    WARNING: 'warning',
    ERROR: 'error'
};

// Notification state
let notifications = new Map();
let unreadCount = 0;

// Event handlers
const handleNotificationClick = (id) => {
    markAsRead(id);
    updateNotificationBadge();
};

const handleClearAll = () => {
    clearAllNotifications();
    updateNotificationBadge();
};

// API calls
const fetchNotifications = async () => {
    try {
        const response = await fetch('/api/notifications');
        if (!response.ok) throw new Error('Failed to fetch notifications');
        
        const data = await response.json();
        notifications = new Map(data.notifications.map(n => [n.id, n]));
        unreadCount = data.notifications.filter(n => !n.read).length;
        
        updateNotificationsList();
        updateNotificationBadge();
    } catch (error) {
        console.error('Error fetching notifications:', error);
        ui.showToast('Failed to load notifications', NOTIFICATION_TYPES.ERROR);
    }
};

const markAsRead = async (id) => {
    try {
        const response = await fetch(`/api/notifications/${id}/read`, {
            method: 'POST'
        });
        if (!response.ok) throw new Error('Failed to mark notification as read');

        const notification = notifications.get(id);
        if (notification) {
            notifications.set(id, { ...notification, read: true });
        }
        
        updateNotificationsList();
        updateNotificationBadge();
    } catch (error) {
        console.error('Error marking notification as read:', error);
        ui.showToast('Failed to update notification', NOTIFICATION_TYPES.ERROR);
    }
};

const clearAllNotifications = async () => {
    try {
        const response = await fetch('/api/notifications/clear', {
            method: 'POST'
        });
        if (!response.ok) throw new Error('Failed to clear notifications');

        notifications.clear();
        unreadCount = 0;
        
        updateNotificationsList();
        updateNotificationBadge();
    } catch (error) {
        console.error('Error clearing notifications:', error);
        ui.showToast('Failed to clear notifications', NOTIFICATION_TYPES.ERROR);
    }
};

// UI updates
const updateNotificationsList = () => {
    const container = document.querySelector('#notificationsList');
    if (!container) return;

    container.innerHTML = notifications.size > 0 ? Array.from(notifications.values()).map(notification => `
        <div class="notification-item ${notification.read ? 'read' : 'unread'}" 
             data-id="${notification.id}">
            <div class="notification-icon ${notification.type}">
                <i class="icon-${notification.type}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">
                    ${formatTimestamp(notification.timestamp)}
                </div>
            </div>
        </div>
    `).join('') : '<div class="no-notifications">No notifications</div>';

    // Add click handlers
    container.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', () => handleNotificationClick(item.dataset.id));
    });
};

const updateNotificationBadge = () => {
    const badge = document.querySelector('#notificationBadge');
    if (badge) {
        badge.textContent = unreadCount;
        badge.style.display = unreadCount > 0 ? 'block' : 'none';
    }
};

// Helper functions
const formatTimestamp = (timestamp) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    // Less than 1 minute
    if (diff < 60000) {
        return 'Just now';
    }
    
    // Less than 1 hour
    if (diff < 3600000) {
        const minutes = Math.floor(diff / 60000);
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    }
    
    // Less than 1 day
    if (diff < 86400000) {
        const hours = Math.floor(diff / 3600000);
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    }
    
    // More than 1 day
    return date.toLocaleDateString();
};

// WebSocket connection for real-time notifications
let ws;

const connectWebSocket = () => {
    ws = new WebSocket('ws://your-websocket-url');

    ws.onmessage = (event) => {
        const notification = JSON.parse(event.data);
        addNotification(notification.message, notification.type, notification.duration);
    };

    ws.onclose = () => {
        // Attempt to reconnect after 5 seconds
        setTimeout(connectWebSocket, 5000);
    };
};

const addNotification = (message, type = 'info', duration = 5000) => {
    const id = Date.now();
    const notification = {
        id,
        message,
        type,
        timestamp: new Date(),
        read: false
    };
    
    notifications.set(id, notification);
    unreadCount++;
    
    // Trigger notification event
    document.dispatchEvent(new CustomEvent('notification:added', { detail: notification }));
    
    // Auto remove after duration
    if (duration > 0) {
        setTimeout(() => removeNotification(id), duration);
    }
    
    updateNotificationsList();
    updateNotificationBadge();
    
    // Show toast for new notification
    ui.showToast(message, type);
    
    return id;
};

function removeNotification(id) {
    if (notifications.has(id)) {
        const notification = notifications.get(id);
        if (!notification.read) {
            unreadCount--;
        }
        notifications.delete(id);
        
        // Trigger notification event
        document.dispatchEvent(new CustomEvent('notification:removed', { detail: { id } }));
        
        updateNotificationsList();
        updateNotificationBadge();
    }
}

function clearNotifications() {
    notifications.clear();
    unreadCount = 0;
    
    // Trigger notification event
    document.dispatchEvent(new CustomEvent('notifications:cleared'));
    
    updateNotificationsList();
    updateNotificationBadge();
}

// Initialize notifications module
export const initializeNotifications = () => {
    // Fetch initial notifications
    fetchNotifications();

    // Add event listeners
    const clearAllButton = document.querySelector('#clearAllNotifications');
    if (clearAllButton) {
        clearAllButton.addEventListener('click', handleClearAll);
    }

    // Connect to WebSocket for real-time updates
    connectWebSocket();

    // Set up periodic polling as fallback
    setInterval(fetchNotifications, 60000); // Poll every minute
};

// Export notifications module
export {
    getAll,
    getUnreadCount,
    markAsRead,
    markAllAsRead,
    addNotification as add,
    removeNotification as remove,
    clearNotifications as clear
};

function getAll() {
    return Array.from(notifications.values());
}

function getUnreadCount() {
    return unreadCount;
}

function markAllAsRead() {
    // Implementation needed
}

function remove(id) {
    // Implementation needed
}

function clear() {
    // Implementation needed
} 