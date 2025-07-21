// Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    checkAuthentication();
    initializeCharts();
    initializeDataTables();
    initializeModals();
    initializeNotifications();
    initializeRealTimeUpdates();
    initializeSearchAndFilters();
}

// Check if user is authenticated
function checkAuthentication() {
    const currentUser = AuthManager.checkAuthStatus();
    
    if (!currentUser) {
        window.location.href = '../login.html';
        return;
    }
    
    // Update user info in navbar
    updateUserInfo(currentUser);
}

// Update user information in the interface
function updateUserInfo(user) {
    const userNameElements = document.querySelectorAll('[data-user-name]');
    const userRoleElements = document.querySelectorAll('[data-user-role]');
    
    userNameElements.forEach(el => {
        el.textContent = user.name || user.email;
    });
    
    userRoleElements.forEach(el => {
        el.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    });
}

// Initialize charts (placeholder for future implementation)
function initializeCharts() {
    // Performance chart placeholder
    const chartElements = document.querySelectorAll('.chart-placeholder');
    
    chartElements.forEach(element => {
        // This would integrate with Chart.js or similar library
        element.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                <p class="text-muted">Chart visualization would appear here</p>
                <small class="text-muted">Integration with Chart.js pending</small>
            </div>
        `;
    });
}

// Initialize data tables
function initializeDataTables() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        // Add sorting functionality
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            if (header.textContent.trim()) {
                header.style.cursor = 'pointer';
                header.addEventListener('click', () => sortTable(table, index));
            }
        });
    });
}

// Sort table by column
function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const isAscending = table.dataset.sortOrder !== 'asc';
    table.dataset.sortOrder = isAscending ? 'asc' : 'desc';
    
    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].textContent.trim();
        const bText = b.cells[columnIndex].textContent.trim();
        
        // Try to parse as numbers first
        const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        
        // Sort as strings
        return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
    });
    
    // Clear tbody and append sorted rows
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
    
    // Update header indicators
    const headers = table.querySelectorAll('th');
    headers.forEach(header => {
        header.classList.remove('sorted-asc', 'sorted-desc');
    });
    headers[columnIndex].classList.add(isAscending ? 'sorted-asc' : 'sorted-desc');
}

// Initialize modals
function initializeModals() {
    // Auto-focus first input when modal opens
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
        
        // Reset form when modal closes
        modal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                // Remove validation classes
                form.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                form.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.remove();
                });
            }
        });
    });
    
    // Handle form submissions in modals
    document.querySelectorAll('.modal form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleModalFormSubmission(this);
        });
    });
}

// Handle modal form submissions
async function handleModalFormSubmission(form) {
    if (!SkulBus.validateForm(form)) {
        return;
    }
    
    const submitButton = form.querySelector('button[type="submit"]');
    const modal = form.closest('.modal');
    
    try {
        SkulBus.showLoading(submitButton);
        
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        SkulBus.showNotification('Operation completed successfully!', 'success');
        
        // Close modal
        const modalInstance = bootstrap.Modal.getInstance(modal);
        modalInstance.hide();
        
        // Refresh data (in real app, this would update the specific section)
        setTimeout(() => {
            location.reload();
        }, 1000);
        
    } catch (error) {
        SkulBus.showNotification('Operation failed. Please try again.', 'danger');
    } finally {
        SkulBus.hideLoading(submitButton);
    }
}

// Initialize notifications
function initializeNotifications() {
    // Mark notifications as read when clicked
    document.querySelectorAll('.notification-dropdown .dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (!this.classList.contains('dropdown-header') && !this.textContent.includes('View All')) {
                this.style.opacity = '0.6';
                updateNotificationCount();
            }
        });
    });
    
    // Simulate real-time notifications
    setInterval(() => {
        if (Math.random() < 0.1) { // 10% chance every 30 seconds
            showRandomNotification();
        }
    }, 30000);
}

// Update notification count
function updateNotificationCount() {
    const badge = document.querySelector('.navbar .badge');
    if (badge) {
        let count = parseInt(badge.textContent) || 0;
        count = Math.max(0, count - 1);
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    }
}

// Show random notification (demo)
function showRandomNotification() {
    const notifications = [
        { message: 'New booking received', type: 'info' },
        { message: 'Payment confirmed', type: 'success' },
        { message: 'Trip status updated', type: 'warning' },
        { message: 'Document approved', type: 'success' }
    ];
    
    const randomNotification = notifications[Math.floor(Math.random() * notifications.length)];
    SkulBus.showNotification(randomNotification.message, randomNotification.type);
}

// Initialize real-time updates
function initializeRealTimeUpdates() {
    // Simulate real-time data updates
    setInterval(() => {
        updateDashboardStats();
        updateRecentActivity();
    }, 60000); // Update every minute
}

// Update dashboard statistics
function updateDashboardStats() {
    const statElements = document.querySelectorAll('.stats-content h3');
    
    statElements.forEach(element => {
        const currentValue = parseInt(element.textContent) || 0;
        const change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
        const newValue = Math.max(0, currentValue + change);
        
        if (change !== 0) {
            element.textContent = newValue;
            element.parentElement.classList.add('updated');
            setTimeout(() => {
                element.parentElement.classList.remove('updated');
            }, 2000);
        }
    });
}

// Update recent activity
function updateRecentActivity() {
    const activityList = document.querySelector('.activity-list');
    if (!activityList) return;
    
    const activities = [
        { icon: 'fa-user-plus', bg: 'bg-success', text: 'New user registered', time: 'Just now' },
        { icon: 'fa-check-circle', bg: 'bg-primary', text: 'Trip completed', time: '2 minutes ago' },
        { icon: 'fa-credit-card', bg: 'bg-warning', text: 'Payment processed', time: '5 minutes ago' }
    ];
    
    if (Math.random() < 0.3) { // 30% chance
        const randomActivity = activities[Math.floor(Math.random() * activities.length)];
        const activityHTML = `
            <div class="activity-item">
                <div class="activity-icon ${randomActivity.bg}">
                    <i class="fas ${randomActivity.icon}"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-1">${randomActivity.text}</p>
                    <small class="text-muted">${randomActivity.time}</small>
                </div>
            </div>
        `;
        
        activityList.insertAdjacentHTML('afterbegin', activityHTML);
        
        // Remove oldest activity if more than 5
        const activities = activityList.querySelectorAll('.activity-item');
        if (activities.length > 5) {
            activities[activities.length - 1].remove();
        }
    }
}

// Initialize search and filters
function initializeSearchAndFilters() {
    // Search functionality
    const searchInputs = document.querySelectorAll('input[type="search"], .search-input');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', SkulBus.debounce(function() {
            const searchTerm = this.value.toLowerCase();
            const targetTable = document.querySelector(this.dataset.target || '.table');
            
            if (targetTable) {
                filterTable(targetTable, searchTerm);
            }
        }, 300));
    });
    
    // Filter dropdowns
    const filterSelects = document.querySelectorAll('.filter-select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const filterValue = this.value;
            const targetTable = document.querySelector(this.dataset.target || '.table');
            const columnIndex = parseInt(this.dataset.column) || 0;
            
            if (targetTable) {
                filterTableByColumn(targetTable, columnIndex, filterValue);
            }
        });
    });
}

// Filter table by search term
function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const shouldShow = !searchTerm || text.includes(searchTerm);
        row.style.display = shouldShow ? '' : 'none';
    });
}

// Filter table by column value
function filterTableByColumn(table, columnIndex, filterValue) {
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const cellText = row.cells[columnIndex]?.textContent.toLowerCase() || '';
        const shouldShow = !filterValue || cellText.includes(filterValue.toLowerCase());
        row.style.display = shouldShow ? '' : 'none';
    });
}

// Export/Print functionality
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = Array.from(table.querySelectorAll('tr'));
    const csvContent = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => `"${cell.textContent.trim()}"`).join(',');
    }).join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// Print functionality
function printSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>SkulBus Report</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { font-family: Arial, sans-serif; }
                .no-print { display: none !important; }
                @media print {
                    .btn, .navbar, .modal { display: none !important; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>SkulBus Report</h2>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
                ${section.innerHTML}
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Logout functionality
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        AuthManager.logout();
    }
}

// Add logout event listeners
document.addEventListener('click', function(e) {
    if (e.target.closest('a[href*="logout"]')) {
        e.preventDefault();
        handleLogout();
    }
});

// Export dashboard functions
window.Dashboard = {
    exportTableToCSV,
    printSection,
    handleLogout,
    updateDashboardStats,
    updateRecentActivity
};