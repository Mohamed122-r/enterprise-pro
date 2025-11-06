// Enterprise Pro - Main JavaScript File

class EnterpriseApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupInterceptors();
        this.initializeComponents();
    }

    setupEventListeners() {
        // Global click handler for dynamic content
        document.addEventListener('click', this.handleGlobalClick.bind(this));
        
        // Form submission handler
        document.addEventListener('submit', this.handleFormSubmit.bind(this));
        
        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyboardShortcuts.bind(this));
    }

    setupInterceptors() {
        // Axios response interceptor for global error handling
        if (typeof axios !== 'undefined') {
            axios.interceptors.response.use(
                response => response,
                error => {
                    this.handleApiError(error);
                    return Promise.reject(error);
                }
            );
        }
    }

    initializeComponents() {
        // Initialize tooltips
        this.initializeTooltips();
        
        // Initialize notifications
        this.initializeNotifications();
        
        // Initialize modals
        this.initializeModals();
    }

    handleGlobalClick(event) {
        const target = event.target;
        
        // Handle dropdown toggles
        if (target.closest('[data-dropdown-toggle]')) {
            this.toggleDropdown(target.closest('[data-dropdown-toggle]'));
        }
        
        // Handle modal triggers
        if (target.closest('[data-modal-toggle]')) {
            this.toggleModal(target.closest('[data-modal-toggle]').dataset.modalToggle);
        }
        
        // Handle tab switches
        if (target.closest('[data-tab]')) {
            this.switchTab(target.closest('[data-tab]'));
        }
    }

    handleFormSubmit(event) {
        const form = event.target;
        
        if (form.classList.contains('ajax-form')) {
            event.preventDefault();
            this.submitAjaxForm(form);
        }
        
        // Add loading state to submit buttons
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            this.setButtonLoading(submitButton, true);
        }
    }

    handleKeyboardShortcuts(event) {
        // Ctrl/Cmd + K for search
        if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
            event.preventDefault();
            this.focusSearch();
        }
        
        // Escape key to close modals
        if (event.key === 'Escape') {
            this.closeAllModals();
        }
    }

    handleApiError(error) {
        let message = 'An error occurred';
        
        if (error.response) {
            switch (error.response.status) {
                case 401:
                    message = 'Please log in again';
                    this.redirectToLogin();
                    break;
                case 403:
                    message = 'You do not have permission to perform this action';
                    break;
                case 404:
                    message = 'The requested resource was not found';
                    break;
                case 422:
                    message = 'Please check your input and try again';
                    this.showValidationErrors(error.response.data.errors);
                    break;
                case 500:
                    message = 'Server error occurred. Please try again later';
                    break;
                default:
                    message = error.response.data?.message || message;
            }
        } else if (error.request) {
            message = 'Network error. Please check your connection';
        }
        
        this.showNotification(message, 'error');
    }

    // Notification System
    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    &times;
                </button>
            </div>
        `;
        
        const container = document.getElementById('notification-container') || this.createNotificationContainer();
        container.appendChild(notification);
        
        if (duration > 0) {
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, duration);
        }
    }

    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'notification-container';
        document.body.appendChild(container);
        return container;
    }

    // Modal System
    initializeModals() {
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }

    toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (modal.classList.contains('active')) {
                this.closeModal(modal);
            } else {
                this.openModal(modal);
            }
        }
    }

    openModal(modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    closeModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    closeAllModals() {
        document.querySelectorAll('.modal.active').forEach(modal => {
            this.closeModal(modal);
        });
    }

    // Form Handling
    submitAjaxForm(form) {
        const formData = new FormData(form);
        const url = form.getAttribute('action') || window.location.href;
        const method = form.getAttribute('method') || 'POST';

        axios({
            method: method,
            url: url,
            data: formData,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(response => {
            this.showNotification('Operation completed successfully', 'success');
            this.resetForm(form);
            
            // Trigger custom event for form success
            form.dispatchEvent(new CustomEvent('form:success', { detail: response.data }));
        })
        .catch(error => {
            // Error handled by interceptor
        })
        .finally(() => {
            this.setButtonLoading(form.querySelector('button[type="submit"]'), false);
        });
    }

    showValidationErrors(errors) {
        // Clear previous errors
        document.querySelectorAll('.form-error').forEach(el => el.remove());
        
        // Show new errors
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'form-error';
                errorDiv.textContent = errors[field][0];
                input.parentNode.appendChild(errorDiv);
                input.classList.add('error');
            }
        });
    }

    resetForm(form) {
        form.reset();
        form.querySelectorAll('.form-error').forEach(el => el.remove());
        form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    }

    // Utility Methods
    setButtonLoading(button, loading) {
        if (!button) return;
        
        if (loading) {
            button.setAttribute('data-original-text', button.textContent);
            button.innerHTML = '<div class="spinner"></div> Loading...';
            button.disabled = true;
        } else {
            const originalText = button.getAttribute('data-original-text');
            if (originalText) {
                button.innerHTML = originalText;
                button.removeAttribute('data-original-text');
            }
            button.disabled = false;
        }
    }

    focusSearch() {
        const searchInput = document.querySelector('#global-search, [data-search-input]');
        if (searchInput) {
            searchInput.focus();
        } else {
            this.showNotification('Search is not available on this page', 'info');
        }
    }

    initializeTooltips() {
        // Simple tooltip implementation
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', this.showTooltip);
            element.addEventListener('mouseleave', this.hideTooltip);
        });
    }

    showTooltip(event) {
        const tooltipText = event.target.getAttribute('data-tooltip');
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = tooltipText;
        
        document.body.appendChild(tooltip);
        
        const rect = event.target.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
        
        event.target._tooltip = tooltip;
    }

    hideTooltip(event) {
        if (event.target._tooltip) {
            event.target._tooltip.remove();
            event.target._tooltip = null;
        }
    }

    initializeNotifications() {
        // Check for new notifications periodically
        setInterval(() => {
            this.checkNewNotifications();
        }, 30000); // Every 30 seconds
    }

    checkNewNotifications() {
        if (!this.isUserAuthenticated()) return;
        
        axios.get('/api/notifications/unread-count')
            .then(response => {
                this.updateNotificationBadge(response.data.count);
            })
            .catch(() => {
                // Silent fail for notifications
            });
    }

    updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    isUserAuthenticated() {
        return document.body.classList.contains('user-authenticated');
    }

    redirectToLogin() {
        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
    }

    // Data Table Helpers
    initializeDataTable(tableElement) {
        if (typeof $.fn.DataTable !== 'undefined') {
            $(tableElement).DataTable({
                pageLength: 25,
                responsive: true,
                order: [[0, 'desc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        }
    }

    // Export functionality
    exportToCsv(data, filename) {
        const csvContent = this.convertToCsv(data);
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.click();
    }

    convertToCsv(data) {
        const headers = Object.keys(data[0]);
        const csvRows = [headers.join(',')];
        
        for (const row of data) {
            const values = headers.map(header => {
                const value = row[header];
                return `"${String(value).replace(/"/g, '""')}"`;
            });
            csvRows.push(values.join(','));
        }
        
        return csvRows.join('\n');
    }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.EnterpriseApp = new EnterpriseApp();
});

// Utility functions available globally
window.formatCurrency = function(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
};

window.formatDate = function(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

window.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};