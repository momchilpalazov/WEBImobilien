// Import UI module for notifications
import { ui } from './ui.js';

// Backup state
let backups = [];
let isBackupInProgress = false;

// Event handlers
const handleCreateBackup = async () => {
    if (isBackupInProgress) {
        ui.showToast('A backup is already in progress', 'warning');
        return;
    }

    try {
        isBackupInProgress = true;
        ui.showLoading(document.querySelector('#backupSection'));
        
        const response = await createBackup();
        if (response.success) {
            ui.showToast('Backup created successfully', 'success');
            await fetchBackups(); // Refresh the list
        }
    } catch (error) {
        ui.showToast('Failed to create backup', 'error');
        console.error('Backup creation error:', error);
    } finally {
        isBackupInProgress = false;
        ui.hideLoading(document.querySelector('#backupSection'));
    }
};

const handleRestoreBackup = async (backupId) => {
    if (!confirm('Are you sure you want to restore this backup? Current data will be overwritten.')) {
        return;
    }

    try {
        ui.showLoading(document.querySelector('#backupSection'));
        
        const response = await restoreBackup(backupId);
        if (response.success) {
            ui.showToast('Backup restored successfully', 'success');
            await fetchBackups(); // Refresh the list
        }
    } catch (error) {
        ui.showToast('Failed to restore backup', 'error');
        console.error('Backup restore error:', error);
    } finally {
        ui.hideLoading(document.querySelector('#backupSection'));
    }
};

const handleDeleteBackup = async (backupId) => {
    if (!confirm('Are you sure you want to delete this backup?')) {
        return;
    }

    try {
        ui.showLoading(document.querySelector('#backupSection'));
        
        const response = await deleteBackup(backupId);
        if (response.success) {
            ui.showToast('Backup deleted successfully', 'success');
            await fetchBackups(); // Refresh the list
        }
    } catch (error) {
        ui.showToast('Failed to delete backup', 'error');
        console.error('Backup deletion error:', error);
    } finally {
        ui.hideLoading(document.querySelector('#backupSection'));
    }
};

const handleDownloadBackup = async (backupId) => {
    try {
        const response = await fetch(`/api/backups/${backupId}/download`);
        if (!response.ok) throw new Error('Download failed');

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `backup-${backupId}.zip`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    } catch (error) {
        ui.showToast('Failed to download backup', 'error');
        console.error('Backup download error:', error);
    }
};

// API calls
const createBackup = async () => {
    const response = await fetch('/api/backups', {
        method: 'POST'
    });
    
    if (!response.ok) throw new Error('Failed to create backup');
    return response.json();
};

const restoreBackup = async (backupId) => {
    const response = await fetch(`/api/backups/${backupId}/restore`, {
        method: 'POST'
    });
    
    if (!response.ok) throw new Error('Failed to restore backup');
    return response.json();
};

const deleteBackup = async (backupId) => {
    const response = await fetch(`/api/backups/${backupId}`, {
        method: 'DELETE'
    });
    
    if (!response.ok) throw new Error('Failed to delete backup');
    return response.json();
};

const fetchBackups = async () => {
    try {
        const response = await fetch('/api/backups');
        if (!response.ok) throw new Error('Failed to fetch backups');
        
        const data = await response.json();
        backups = data.backups;
        
        updateBackupsList();
    } catch (error) {
        console.error('Error fetching backups:', error);
        ui.showToast('Failed to load backups', 'error');
    }
};

// UI updates
const updateBackupsList = () => {
    const container = document.querySelector('#backupsList');
    if (!container) return;

    container.innerHTML = backups.length ? backups.map(backup => `
        <div class="backup-item" data-id="${backup.id}">
            <div class="backup-info">
                <div class="backup-name">${backup.name}</div>
                <div class="backup-date">${formatDate(backup.created_at)}</div>
                <div class="backup-size">${formatSize(backup.size)}</div>
            </div>
            <div class="backup-actions">
                <button class="btn btn-sm btn-primary restore-backup" 
                        title="Restore this backup">
                    <i class="icon-restore"></i>
                </button>
                <button class="btn btn-sm btn-secondary download-backup" 
                        title="Download backup">
                    <i class="icon-download"></i>
                </button>
                <button class="btn btn-sm btn-danger delete-backup" 
                        title="Delete backup">
                    <i class="icon-delete"></i>
                </button>
            </div>
        </div>
    `).join('') : '<div class="no-backups">No backups available</div>';

    // Add click handlers
    container.querySelectorAll('.backup-item').forEach(item => {
        const id = item.dataset.id;
        
        item.querySelector('.restore-backup')?.addEventListener('click', () => 
            handleRestoreBackup(id)
        );
        
        item.querySelector('.download-backup')?.addEventListener('click', () => 
            handleDownloadBackup(id)
        );
        
        item.querySelector('.delete-backup')?.addEventListener('click', () => 
            handleDeleteBackup(id)
        );
    });
};

// Helper functions
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
};

const formatSize = (bytes) => {
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unitIndex = 0;
    
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return `${size.toFixed(1)} ${units[unitIndex]}`;
};

// Initialize backup module
export const initializeBackup = () => {
    // Fetch initial backups
    fetchBackups();

    // Add event listeners
    const createBackupButton = document.querySelector('#createBackup');
    if (createBackupButton) {
        createBackupButton.addEventListener('click', handleCreateBackup);
    }

    // Set up automatic refresh
    setInterval(fetchBackups, 300000); // Refresh every 5 minutes
};

// Export backup module
export const backup = {
    getAll: () => backups,
    create: handleCreateBackup,
    restore: handleRestoreBackup,
    delete: handleDeleteBackup,
    download: handleDownloadBackup,
    isInProgress: () => isBackupInProgress
}; 