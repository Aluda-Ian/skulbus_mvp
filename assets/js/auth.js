// Authentication JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeAuth();
});

function initializeAuth() {
    initializeLoginForm();
    initializeRegisterForm();
    initializePasswordToggle();
    initializePasswordStrength();
    initializeRoleSelection();
    initializeDemoLogin();
}

// Login form functionality
function initializeLoginForm() {
    const loginForm = document.getElementById('loginForm');
    if (!loginForm) return;

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!SkulBus.validateForm(this)) {
            return;
        }

        const formData = new FormData(this);
        const email = formData.get('email');
        const password = formData.get('password');
        const rememberMe = formData.get('rememberMe');

        handleLogin(email, password, rememberMe);
    });
}

// Register form functionality
function initializeRegisterForm() {
    const registerForm = document.getElementById('registerForm');
    if (!registerForm) return;

    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!SkulBus.validateForm(this)) {
            return;
        }

        const formData = new FormData(this);
        const userData = {
            fullName: formData.get('fullName'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            role: formData.get('role'),
            school: formData.get('school'),
            region: formData.get('region'),
            password: formData.get('password'),
            confirmPassword: formData.get('confirmPassword')
        };

        // Validate password confirmation
        if (userData.password !== userData.confirmPassword) {
            SkulBus.showNotification('Passwords do not match', 'danger');
            return;
        }

        handleRegistration(userData);
    });
}

// Password toggle functionality
function initializePasswordToggle() {
    const toggleButtons = document.querySelectorAll('#togglePassword');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.parentNode.querySelector('input[type="password"], input[type="text"]');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

// Password strength indicator
function initializePasswordStrength() {
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordHelp');
    
    if (!passwordInput || !strengthBar || !strengthText) return;

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        updatePasswordStrength(strength, strengthBar, strengthText);
    });
}

// Calculate password strength
function calculatePasswordStrength(password) {
    let score = 0;
    let feedback = [];

    if (password.length >= 8) {
        score += 25;
    } else {
        feedback.push('At least 8 characters');
    }

    if (/[a-z]/.test(password)) {
        score += 25;
    } else {
        feedback.push('Lowercase letter');
    }

    if (/[A-Z]/.test(password)) {
        score += 25;
    } else {
        feedback.push('Uppercase letter');
    }

    if (/[0-9]/.test(password)) {
        score += 25;
    } else {
        feedback.push('Number');
    }

    return { score, feedback };
}

// Update password strength display
function updatePasswordStrength(strength, strengthBar, strengthText) {
    const { score, feedback } = strength;
    
    strengthBar.style.width = `${score}%`;
    
    if (score < 50) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Weak password';
        strengthText.className = 'text-danger';
    } else if (score < 75) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Medium password';
        strengthText.className = 'text-warning';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Strong password';
        strengthText.className = 'text-success';
    }
}

// Role selection functionality
function initializeRoleSelection() {
    const roleInputs = document.querySelectorAll('input[name="role"]');
    const schoolField = document.getElementById('schoolField');
    const regionField = document.getElementById('regionField');
    
    if (!roleInputs.length) return;

    roleInputs.forEach(input => {
        input.addEventListener('change', function() {
            const selectedRole = this.value;
            
            // Hide all conditional fields
            if (schoolField) schoolField.style.display = 'none';
            if (regionField) regionField.style.display = 'none';
            
            // Show relevant fields based on role
            if (selectedRole === 'parent' || selectedRole === 'admin') {
                if (schoolField) {
                    schoolField.style.display = 'block';
                    schoolField.querySelector('select').required = true;
                }
            } else if (selectedRole === 'sacco') {
                if (regionField) {
                    regionField.style.display = 'block';
                    regionField.querySelector('select').required = true;
                }
            }
        });
    });
}

// Demo login functionality
function initializeDemoLogin() {
    window.demoLogin = function(role) {
        const credentials = {
            parent: { email: 'parent@demo.com', password: 'demo123' },
            sacco: { email: 'sacco@demo.com', password: 'demo123' },
            admin: { email: 'admin@demo.com', password: 'demo123' }
        };

        const cred = credentials[role];
        if (cred) {
            // Fill login form
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            if (emailInput && passwordInput) {
                emailInput.value = cred.email;
                passwordInput.value = cred.password;
            }
            
            // Simulate login
            handleLogin(cred.email, cred.password, false, role);
        }
    };
}

// Handle login process
async function handleLogin(email, password, rememberMe, demoRole = null) {
    const submitButton = document.querySelector('#loginForm button[type="submit"]');
    
    try {
        SkulBus.showLoading(submitButton);
        
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Store user session
        const userData = {
            email: email,
            role: demoRole || getUserRoleFromEmail(email),
            loginTime: new Date().toISOString(),
            rememberMe: rememberMe
        };
        
        SkulBus.Storage.set('currentUser', userData);
        
        SkulBus.showNotification('Login successful! Redirecting...', 'success');
        
        // Redirect based on role
        setTimeout(() => {
            redirectToDashboard(userData.role);
        }, 1000);
        
    } catch (error) {
        SkulBus.showNotification('Login failed. Please try again.', 'danger');
    } finally {
        SkulBus.hideLoading(submitButton);
    }
}

// Handle registration process
async function handleRegistration(userData) {
    const submitButton = document.querySelector('#registerForm button[type="submit"]');
    
    try {
        SkulBus.showLoading(submitButton);
        
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        SkulBus.showNotification('Registration successful! Please login to continue.', 'success');
        
        // Redirect to login page
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 2000);
        
    } catch (error) {
        SkulBus.showNotification('Registration failed. Please try again.', 'danger');
    } finally {
        SkulBus.hideLoading(submitButton);
    }
}

// Get user role from email (for demo purposes)
function getUserRoleFromEmail(email) {
    if (email.includes('parent')) return 'parent';
    if (email.includes('sacco')) return 'sacco';
    if (email.includes('admin')) return 'admin';
    return 'parent'; // default
}

// Redirect to appropriate dashboard
function redirectToDashboard(role) {
    const dashboards = {
        parent: 'dashboards/parent-dashboard.html',
        sacco: 'dashboards/sacco-dashboard.html',
        admin: 'dashboards/admin-dashboard.html'
    };
    
    window.location.href = dashboards[role] || 'dashboards/parent-dashboard.html';
}

// Check if user is logged in
function checkAuthStatus() {
    const currentUser = SkulBus.Storage.get('currentUser');
    
    if (currentUser) {
        const loginTime = new Date(currentUser.loginTime);
        const now = new Date();
        const hoursDiff = (now - loginTime) / (1000 * 60 * 60);
        
        // Session expires after 24 hours (or 1 hour if not remembered)
        const maxHours = currentUser.rememberMe ? 24 : 1;
        
        if (hoursDiff > maxHours) {
            logout();
            return false;
        }
        
        return currentUser;
    }
    
    return false;
}

// Logout function
function logout() {
    SkulBus.Storage.remove('currentUser');
    SkulBus.showNotification('You have been logged out.', 'info');
    
    setTimeout(() => {
        window.location.href = 'login.html';
    }, 1000);
}

// Phone number formatting
document.addEventListener('input', function(e) {
    if (e.target.type === 'tel') {
        let value = e.target.value.replace(/\D/g, '');
        
        // Kenyan phone number formatting
        if (value.startsWith('254')) {
            value = '+' + value;
        } else if (value.startsWith('0')) {
            // Keep as is
        } else if (value.length > 0) {
            value = '0' + value;
        }
        
        e.target.value = value;
    }
});

// Export auth functions
window.AuthManager = {
    checkAuthStatus,
    logout,
    redirectToDashboard
};