// Task Management System - Main JavaScript File

// Global variables
let timers = {};
let countdownIntervals = {};

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Initialize application
function initializeApp() {
    // Initialize timers
    initializeTimers();
    
    // Initialize form validations
    initializeFormValidations();
    
    // Initialize file uploads
    initializeFileUploads();
    
    // Initialize tooltips and interactions
    initializeInteractions();
    
    // Initialize CSRF tokens
    initializeCSRF();
}

// Timer Functions
function initializeTimers() {
    const timerElements = document.querySelectorAll('[data-timer]');
    
    timerElements.forEach(element => {
        const endTime = element.getAttribute('data-timer');
        const timerId = element.getAttribute('data-timer-id') || Math.random().toString(36).substr(2, 9);
        
        if (endTime) {
            startCountdown(element, endTime, timerId);
        }
    });
}

function startCountdown(element, endTime, timerId) {
    const endDate = new Date(endTime).getTime();
    
    countdownIntervals[timerId] = setInterval(function() {
        const now = new Date().getTime();
        const distance = endDate - now;
        
        if (distance < 0) {
            clearInterval(countdownIntervals[timerId]);
            element.innerHTML = '<span class="text-red-600 font-bold">EXPIRED</span>';
            element.classList.add('expired');
            
            // Disable related buttons
            const taskCard = element.closest('.task-card');
            if (taskCard) {
                const buttons = taskCard.querySelectorAll('.btn');
                buttons.forEach(btn => {
                    if (!btn.classList.contains('btn-info')) {
                        btn.disabled = true;
                        btn.classList.add('opacity-50');
                    }
                });
            }
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        let timeString = '';
        if (days > 0) {
            timeString += days + 'd ';
        }
        if (hours > 0 || days > 0) {
            timeString += hours + 'h ';
        }
        timeString += minutes + 'm ' + seconds + 's';
        
        element.innerHTML = timeString;
        
        // Add warning class when time is running low
        if (distance < 300000) { // 5 minutes
            element.classList.add('warning');
        }
    }, 1000);
}

// Form Validation Functions
function initializeFormValidations() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(input);
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    // Password confirmation validation
    const password = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    
    if (password && confirmPassword) {
        if (password.value !== confirmPassword.value) {
            showFieldError(confirmPassword, 'Passwords do not match');
            isValid = false;
        } else {
            clearFieldError(confirmPassword);
        }
    }
    
    // Email validation
    const emailInputs = form.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        if (input.value && !isValidEmail(input.value)) {
            showFieldError(input, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    clearFieldError(field);
    
    if (field.hasAttribute('required') && !field.value.trim()) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    if (field.type === 'email' && field.value && !isValidEmail(field.value)) {
        showFieldError(field, 'Please enter a valid email address');
        return false;
    }
    
    if (field.name === 'mobile' && field.value && !isValidMobile(field.value)) {
        showFieldError(field, 'Please enter a valid mobile number');
        return false;
    }
    
    return true;
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error text-red-600 text-sm mt-1';
    errorDiv.textContent = message;
    
    field.classList.add('border-red-500');
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('border-red-500');
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidMobile(mobile) {
    const mobileRegex = /^[6-9]\d{9}$/;
    return mobileRegex.test(mobile.replace(/\D/g, ''));
}

// File Upload Functions
function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            validateFileUpload(e.target);
        });
    });
}

function validateFileUpload(input) {
    const file = input.files[0];
    if (!file) return true;
    
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    clearFieldError(input);
    
    if (file.size > maxSize) {
        showFieldError(input, 'File size must be less than 5MB');
        input.value = '';
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        showFieldError(input, 'Only PDF, JPG, PNG, DOC, DOCX files are allowed');
        input.value = '';
        return false;
    }
    
    // Show file preview
    showFilePreview(input, file);
    return true;
}

function showFilePreview(input, file) {
    const previewContainer = input.parentNode.querySelector('.file-preview') || 
                           createFilePreviewContainer(input);
    
    previewContainer.innerHTML = `
        <div class="file-info p-3 bg-gray-50 rounded border">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-medium">${file.name}</div>
                    <div class="text-sm text-gray-600">${formatFileSize(file.size)}</div>
                </div>
                <button type="button" onclick="clearFileInput(this)" class="text-red-600 hover:text-red-800">
                    Remove
                </button>
            </div>
        </div>
    `;
}

function createFilePreviewContainer(input) {
    const container = document.createElement('div');
    container.className = 'file-preview mt-2';
    input.parentNode.appendChild(container);
    return container;
}

function clearFileInput(button) {
    const previewContainer = button.closest('.file-preview');
    const fileInput = previewContainer.parentNode.querySelector('input[type="file"]');
    
    fileInput.value = '';
    previewContainer.innerHTML = '';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Interaction Functions
function initializeInteractions() {
    // Confirmation dialogs
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Toggle sections
    const toggleButtons = document.querySelectorAll('[data-toggle]');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-toggle');
            const target = document.getElementById(targetId);
            if (target) {
                target.classList.toggle('hidden');
            }
        });
    });
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

// CSRF Token Functions
function initializeCSRF() {
    // Add CSRF token to all forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        if (!form.querySelector('input[name="csrf_token"]')) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = CSRF_TOKEN;
            form.appendChild(csrfInput);
        }
    });
}

// AJAX Functions
function makeAjaxRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        if (data && typeof data === 'object') {
            data.csrf_token = CSRF_TOKEN;
        }
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    resolve(response);
                } catch (e) {
                    resolve(xhr.responseText);
                }
            } else {
                reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        xhr.send(data ? JSON.stringify(data) : null);
    });
}

// Task Functions
function acceptTask(taskId, button) {
    if (!confirm('Are you sure you want to accept this task?')) {
        return;
    }
    
    button.disabled = true;
    button.textContent = 'Accepting...';
    
    makeAjaxRequest('/api/accept_task.php', 'POST', { task_id: taskId })
        .then(response => {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Failed to accept task');
                button.disabled = false;
                button.textContent = 'Accept';
            }
        })
        .catch(error => {
            alert('Error accepting task: ' + error.message);
            button.disabled = false;
            button.textContent = 'Accept';
        });
}

function rejectTask(taskId, button) {
    if (!confirm('Are you sure you want to reject this task?')) {
        return;
    }
    
    button.disabled = true;
    button.textContent = 'Rejecting...';
    
    makeAjaxRequest('/api/reject_task.php', 'POST', { task_id: taskId })
        .then(response => {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Failed to reject task');
                button.disabled = false;
                button.textContent = 'Reject';
            }
        })
        .catch(error => {
            alert('Error rejecting task: ' + error.message);
            button.disabled = false;
            button.textContent = 'Reject';
        });
}

// Utility Functions
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => {
                alertDiv.remove();
            }, 300);
        }, 5000);
    }
}

function copyToClipboard(text, button = null) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            if (button) {
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            } else {
                showAlert('Copied to clipboard!', 'success');
            }
        }).catch(() => {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showAlert('Copied to clipboard!', 'success');
    } catch (err) {
        showAlert('Failed to copy to clipboard', 'error');
    }
    
    document.body.removeChild(textArea);
}

function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toFixed(2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Cleanup function
window.addEventListener('beforeunload', function() {
    // Clear all intervals
    Object.values(countdownIntervals).forEach(interval => {
        clearInterval(interval);
    });
});

// Export functions for global use
window.TaskManager = {
    acceptTask,
    rejectTask,
    copyToClipboard,
    showAlert,
    formatCurrency,
    formatDate,
    makeAjaxRequest
};
