/**
 * Main JavaScript File
 * Common utilities and functionality for all pages
 */

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // If alert has dismissible button, set auto-dismiss
        if (alert.classList.contains('alert-dismissible')) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        }
    });

    // Setup dropdown menu toggle for mobile
    setupDropdowns();
});

/**
 * Setup dropdown menus
 */
function setupDropdowns() {
    const dropdownToggles = document.querySelectorAll('.nav-item > a[href="#user-menu"]');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            const dropdown = this.nextElementSibling;
            if (dropdown && dropdown.classList.contains('dropdown-menu')) {
                dropdown.style.opacity = dropdown.style.opacity === '1' ? '0' : '1';
                dropdown.style.visibility = dropdown.style.visibility === 'visible' ? 'hidden' : 'visible';
                dropdown.style.transform = dropdown.style.transform === 'translateY(0px)' ? 'translateY(-10px)' : 'translateY(0px)';
            }
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
                menu.style.transform = 'translateY(-10px)';
            });
        }
    });
}

/**
 * Show toast notification
 * @param {string} message - The message to display
 * @param {string} type - Type: success, error, warning, info
 * @param {number} duration - Duration in milliseconds (default: 5000)
 */
function showToast(message, type = 'info', duration = 5000) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.textContent = message;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duration);
}

/**
 * Validate email format
 * @param {string} email - Email to validate
 * @returns {boolean}
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Format date for display
 * @param {string|Date} date - Date to format
 * @returns {string}
 */
function formatDate(date) {
    if (typeof date === 'string') {
        date = new Date(date);
    }
    
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Format time for display
 * @param {string|Date} date - Date/time to format
 * @returns {string}
 */
function formatTime(date) {
    if (typeof date === 'string') {
        date = new Date(date);
    }
    
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}

/**
 * Confirm action before proceeding
 * @param {string} message - Confirmation message
 * @param {function} callback - Callback function if confirmed
 * @returns {boolean}
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        if (typeof callback === 'function') {
            callback();
        }
        return true;
    }
    return false;
}

/**
 * Copy text to clipboard
 * @param {string} text - Text to copy
 * @param {string} successMessage - Message to show on success
 */
function copyToClipboard(text, successMessage = 'Copied to clipboard!') {
    navigator.clipboard.writeText(text).then(() => {
        showToast(successMessage, 'success', 3000);
    }).catch(() => {
        showToast('Failed to copy to clipboard', 'error', 3000);
    });
}

/**
 * Debounce function for search/input
 * @param {function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {function}
 */
function debounce(func, wait) {
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

/**
 * Check if element is visible in viewport
 * @param {Element} element - Element to check
 * @returns {boolean}
 */
function isElementInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

/**
 * Make AJAX request
 * @param {string} method - HTTP method (GET, POST, etc.)
 * @param {string} url - Request URL
 * @param {object} data - Data to send (optional)
 * @returns {Promise}
 */
function ajaxRequest(method, url, data = null) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data ? JSON.stringify(data) : null
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .catch(error => {
        console.error('AJAX error:', error);
        showToast('Une erreur est survenue. Veuillez réessayer.', 'error');
        throw error;
    });
}
