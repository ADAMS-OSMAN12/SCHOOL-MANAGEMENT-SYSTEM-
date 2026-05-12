/**
 * Sakam M/A JHS School Management System
 * JavaScript for form validation and interactivity
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initSidebar();
    initAlerts();
    initModals();
    initFormValidation();
    initSearch();
    initTableActions();
    initDatePickers();
    initTabs();
});

/**
 * Sidebar Toggle Functionality
 */
function initSidebar() {
    const toggleBtn = document.querySelector('.toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
        });
        
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
    }
}

/**
 * Auto-dismiss alerts
 */
function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
        
        const closeBtn = alert.querySelector('.close-alert');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                alert.remove();
            });
        }
    });
}

/**
 * Modal functionality
 */
function initModals() {
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modalCloseBtns = document.querySelectorAll('.modal-close, .modal-cancel');
    
    modalTriggers.forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    modalCloseBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(function(modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    });
}

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Real-time validation on blur
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function(input) {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
    });
}

/**
 * Validate entire form
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(function(input) {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate single field
 */
function validateField(input) {
    const value = input.value.trim();
    const name = input.name;
    let isValid = true;
    let errorMessage = '';
    
    // Remove existing error
    const existingError = input.parentElement.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    input.classList.remove('error');
    
    // Required validation
    if (input.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Email validation
    if (input.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }
    }
    
    // Phone validation
    if (input.type === 'tel' && value) {
        const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
        if (!phoneRegex.test(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid phone number';
        }
    }
    
    // Number validation
    if (input.type === 'number' && value) {
        const min = parseFloat(input.getAttribute('min'));
        const max = parseFloat(input.getAttribute('max'));
        
        if (!isNaN(min) && parseFloat(value) < min) {
            isValid = false;
            errorMessage = 'Value must be at least ' + min;
        }
        
        if (!isNaN(max) && parseFloat(value) > max) {
            isValid = false;
            errorMessage = 'Value must not exceed ' + max;
        }
    }
    
    // Score validation (0-100)
    if (name === 'ca_score' || name === 'exam_score' || name === 'score') {
        if (value && (parseFloat(value) < 0 || parseFloat(value) > 100)) {
            isValid = false;
            errorMessage = 'Score must be between 0 and 100';
        }
    }
    
    // Date validation
    if (input.type === 'date' && value) {
        const inputDate = new Date(value);
        const today = new Date();
        
        if (inputDate > today && name !== 'due_date') {
            isValid = false;
            errorMessage = 'Date cannot be in the future';
        }
    }
    
    // Password validation
    if (input.type === 'password' && name === 'password') {
        if (value.length < 6) {
            isValid = false;
            errorMessage = 'Password must be at least 6 characters';
        }
    }
    
    // Confirm password
    if (name === 'confirm_password') {
        const password = form.querySelector('input[name="password"]');
        if (password && value !== password.value) {
            isValid = false;
            errorMessage = 'Passwords do not match';
        }
    }
    
    // Show error
    if (!isValid) {
        input.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = errorMessage;
        input.parentElement.appendChild(errorDiv);
    }
    
    return isValid;
}

/**
 * Search functionality
 */
function initSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(function(input) {
        input.addEventListener('input', debounce(function() {
            const searchTerm = this.value.toLowerCase();
            const targetTable = this.getAttribute('data-table');
            const table = targetTable ? document.querySelector(targetTable) : document.querySelector('table');
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(function(row) {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
        }, 300));
    });
}

/**
 * Table actions (edit, delete, view)
 */
function initTableActions() {
    // Delete confirmation
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Select all checkbox
    const selectAll = document.querySelector('.select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAll.checked;
            });
        });
    }
}

/**
 * Date picker initialization
 */
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateInputs.forEach(function(input) {
        // Set max date to today for birth dates and admission dates
        if (input.name === 'date_of_birth' || input.name === 'admission_date' || input.name === 'hire_date') {
            input.max = today;
        }
        
        // Set min date for due dates
        if (input.name === 'due_date') {
            input.min = today;
        }
    });
}

/**
 * Tabs functionality
 */
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(function(b) { b.classList.remove('active'); });
            tabContents.forEach(function(c) { c.classList.remove('active'); });
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            const content = document.getElementById(tabId);
            if (content) {
                content.classList.add('active');
            }
        });
    });
}

/**
 * Debounce function for search
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Show notification/toast message
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${getIcon(type)}"></i>
        <span>${message}</span>
        <button class="close-alert">&times;</button>
    `;
    
    const container = document.querySelector('.page-content') || document.body;
    container.insertBefore(notification, container.firstChild);
    
    // Auto dismiss
    setTimeout(function() {
        notification.style.opacity = '0';
        setTimeout(function() { notification.remove(); }, 300);
    }, 5000);
    
    // Close button
    notification.querySelector('.close-alert').addEventListener('click', function() {
        notification.remove();
    });
}

function getIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-GB', options);
}

/**
 * Calculate age from date of birth
 */
function calculateAge(dateOfBirth) {
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

/**
 * Print specific element
 */
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print - Sakam M/A JHS</title>
                <link rel="stylesheet" href="../css/style.css">
                <style>
                    @media print {
                        body { padding: 20px; }
                        .no-print { display: none !important; }
                    }
                </style>
            </head>
            <body>
                ${element.innerHTML}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
}

/**
 * Export table to CSV
 */
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(function(row) {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(function(col) {
            rowData.push('"' + col.textContent.trim() + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename + '.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

/**
 * AJAX form submission
 */
function ajaxSubmit(formId, successCallback, errorCallback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const formData = new FormData(form);
    const action = form.getAttribute('action');
    
    fetch(action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (successCallback) successCallback(data);
        } else {
            if (errorCallback) errorCallback(data);
            else showNotification(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        if (errorCallback) errorCallback(error);
        else showNotification('Network error occurred', 'error');
    });
}

/**
 * Live search with AJAX
 */
function liveSearch(inputId, endpoint, resultContainerId) {
    const input = document.getElementById(inputId);
    const container = document.getElementById(resultContainerId);
    
    if (!input || !container) return;
    
    input.addEventListener('input', debounce(function() {
        const query = this.value;
        
        if (query.length < 2) {
            container.innerHTML = '';
            return;
        }
        
        fetch(endpoint + '?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                container.innerHTML = data.html || '';
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }, 500));
}

/**
 * Calculate grade based on score
 */
function getGrade(score) {
    score = parseFloat(score);
    if (score >= 90) return 'A+';
    if (score >= 80) return 'A';
    if (score >= 75) return 'B+';
    if (score >= 70) return 'B';
    if (score >= 65) return 'C+';
    if (score >= 60) return 'C';
    if (score >= 55) return 'D+';
    if (score >= 50) return 'D';
    if (score >= 45) return 'E';
    return 'F';
}

/**
 * Auto-calculate total score
 */
function calculateTotal() {
    const caScore = parseFloat(document.querySelector('input[name="ca_score"]')?.value) || 0;
    const examScore = parseFloat(document.querySelector('input[name="exam_score"]')?.value) || 0;
    const totalInput = document.querySelector('input[name="total_score"]');
    
    if (totalInput) {
        totalInput.value = caScore + examScore;
    }
    
    // Auto-calculate grade
    const gradeInput = document.querySelector('select[name="grade"]');
    if (gradeInput) {
        gradeInput.value = getGrade(caScore + examScore);
    }
}

// Add event listeners for auto-calculate
document.addEventListener('DOMContentLoaded', function() {
    const caInput = document.querySelector('input[name="ca_score"]');
    const examInput = document.querySelector('input[name="exam_score"]');
    
    if (caInput) {
        caInput.addEventListener('input', calculateTotal);
    }
    if (examInput) {
        examInput.addEventListener('input', calculateTotal);
    }
});