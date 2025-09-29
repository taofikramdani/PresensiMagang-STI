class LoginHandler {
    constructor() {
        this.form = document.getElementById('loginForm');
        this.submitBtn = document.getElementById('loginBtn');
        this.directLoginBtn = document.getElementById('directLoginBtn');
        this.nameInput = document.getElementById('name'); // Changed from username
        this.passwordInput = document.getElementById('password');
        this.originalButtonText = this.submitBtn.innerHTML;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setupAnimations();
        this.setupAccessibility();
    }
    
    bindEvents() {
        // Temporary: Skip AJAX, use normal form submission
        console.log('Login handler initialized - using normal form submission');
        
        // Input validasi tanpa efek animasi
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', (e) => this.handleInputFocus(e));
            input.addEventListener('input', (e) => this.handleInputChange(e));
        });
    }
    
    async handleSubmit(e) {
        // Untuk sementara, gunakan form submit biasa sampai CSRF teratasi
        console.log('Using normal form submit to avoid CSRF issues');
        return; // Biarkan form submit normal
        
        // Kode AJAX di-comment untuk sementara
        /*
        // Buat mode debug: jika ada parameter mode=debug di URL, gunakan form submit normal
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('mode') === 'debug') {
            console.log('Debug mode: menggunakan form submit normal');
            return; // Biarkan form submit normal
        }

        e.preventDefault();
        
        // Clear previous error states
        this.clearErrors();

        console.log('nameInput element:', this.nameInput);
        console.log('nameInput value:', this.nameInput ? this.nameInput.value : 'null');
        
        // Double check jika element ada
        if (!this.nameInput) {
            console.error('Element dengan id="name" tidak ditemukan!');
            this.showAlert('Terjadi kesalahan: Element input name tidak ditemukan', 'danger');
            return;
        }
        
        // Get form data
        const formData = {
            name: this.nameInput.value.trim(),
            password: this.passwordInput.value,
            remember: document.getElementById('remember')?.checked || false,
            _token: document.querySelector('input[name="_token"]')?.value || 
                   document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        };
        
        // Debug: Log form data
        console.log('Form data being sent:', formData);
        
        // Validate inputs
        if (!this.validateInputs(formData)) {
            return;
        }
        
        try {
            // Call actual Laravel API
            const response = await this.callLoginAPI(formData);
            
            if (response.success) {
                this.showAlert(response.message, 'success');
                
                // Redirect immediately to dashboard
                window.location.href = response.redirect;
            } else {
                this.showAlert(response.message, 'danger');
                // Show direct submit button on error
                this.toggleDirectLoginButton(true);
            }
            
        } catch (error) {
            this.showAlert(`Terjadi kesalahan saat login: ${error.message}`, 'danger');
            console.error('Login error:', error);
            // Show direct submit button and debug helpers on error
            this.toggleDirectLoginButton(true);
            this.toggleDebugHelpers(true);
        }
        */
    }
    
    validateInputs(data) {
        let isValid = true;
        
        // Name validation
        if (!data.name) {
            this.showFieldError('name', 'Nama harus diisi');
            isValid = false;
        }
        
        // Password validation
        if (!data.password) {
            this.showFieldError('password', 'Password harus diisi');
            isValid = false;
        } else if (data.password.length < 3) { // Reduced minimum length
            this.showFieldError('password', 'Password minimal 3 karakter');
            isValid = false;
        }
        
        return isValid;
    }
    
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    showFieldError(fieldName, message) {
        const field = document.getElementById(fieldName);
        const formFloating = field.parentElement;
        
        // Add error class
        field.classList.add('is-invalid');
        
        // Remove existing error message
        const existingError = formFloating.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        formFloating.appendChild(errorDiv);
        
        // Focus on first error field
        field.focus();
    }
    
    clearErrors() {
        document.querySelectorAll('.form-control').forEach(input => {
            input.classList.remove('is-invalid');
        });
        
        document.querySelectorAll('.invalid-feedback').forEach(error => {
            error.remove();
        });
    }
    
    setLoadingState(isLoading) {
        // Loading state disabled - no animation during login process
        return;
    }
    
    toggleDirectLoginButton(show) {
        if (!this.directLoginBtn) return;
        
        if (show) {
            this.directLoginBtn.classList.remove('d-none');
        } else {
            this.directLoginBtn.classList.add('d-none');
        }
    }
    
    toggleDebugHelpers(show) {
        const debugHelpers = document.getElementById('debugHelpers');
        if (!debugHelpers) return;
        
        if (show) {
            debugHelpers.classList.remove('d-none');
        } else {
            debugHelpers.classList.add('d-none');
        }
    }
    
    async callLoginAPI(data) {
        console.log('Nama yang dikirim:', data.name);
        console.log('CSRF token:', data._token);
        
        const response = await fetch('/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': data._token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: data.name,
                password: data.password,
                remember: data.remember,
                _token: data._token
            })
        });

        const result = await response.json();
        console.log('Server response:', result);
        
        if (!response.ok) {
            throw new Error(result.message || 'Login failed');
        }
        
        return result;
    }
    
    async simulateLogin(data) {
        // Keep this method for backward compatibility but not used anymore
        await new Promise(resolve => setTimeout(resolve, 2000));
        return { success: true, message: 'Demo mode - ready for MySQL integration' };
    }
    
    handleInputFocus(e) {
        // Hanya menghapus status error tanpa animasi pergerakan
        e.target.classList.remove('is-invalid');
    }
    
    handleInputBlur(e) {
        // Tidak ada animasi saat melepas fokus
    }
    
    handleInputChange(e) {
        // Real-time validation feedback
        if (e.target.value && e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const errorMsg = e.target.parentElement.querySelector('.invalid-feedback');
            if (errorMsg) errorMsg.remove();
        }
    }
    
    handleLogoHover(e, isHover) {
        // Menghilangkan efek hover pada logo
    }
    
    setupAnimations() {
        // Menampilkan form tanpa animasi bertahap
        const loginCard = document.querySelector('.login-card');
        loginCard.style.opacity = '1';
        loginCard.style.transform = 'translateY(0)';
        
        // Menampilkan semua elemen form sekaligus tanpa animasi bertahap
        const formElements = document.querySelectorAll('.form-floating, .form-check, .btn-login');
        formElements.forEach(element => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        });
    }
    
    setupAccessibility() {
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && document.activeElement.tagName !== 'BUTTON') {
                e.preventDefault();
                const firstInvalidInput = this.form.querySelector('.form-control:invalid');
                if (firstInvalidInput) {
                    firstInvalidInput.focus();
                } else {
                    this.submitBtn.focus();
                    this.submitBtn.click();
                }
            }
        });
        
        // Focus management
        document.addEventListener('focusin', (e) => {
            if (e.target.matches('.form-control')) {
                e.target.setAttribute('aria-describedby', e.target.id + '-help');
            }
        });
    }
    
    showAlert(message, type = 'info', duration = 5000) {
        // Remove existing alerts
        document.querySelectorAll('.custom-alert').forEach(alert => alert.remove());
        
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show custom-alert`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        `;
        
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${this.getAlertIcon(type)} me-2"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove
        if (duration > 0) {
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.classList.remove('show');
                    setTimeout(() => alertDiv.remove(), 150);
                }
            }, duration);
        }
        
        return alertDiv;
    }
    
    getAlertIcon(type) {
        const icons = {
            success: 'check-circle',
            danger: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || icons.info;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new LoginHandler();
});

// Add minimal CSS styles
const style = document.createElement('style');
style.textContent = `
    .custom-alert {
        border: none !important;
        font-weight: 500;
    }
    
    .custom-alert.alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%) !important;
        color: #155724 !important;
    }
    
    .custom-alert.alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%) !important;
        color: #721c24 !important;
    }
`;
document.head.appendChild(style);
