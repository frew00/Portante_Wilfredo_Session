// Authentication related JavaScript functionality
const Auth = {
    init() {
        this.bindLoginForm();
        this.bindSignupForm();
        this.bindPasswordToggles();
        this.bindFormSwitching();
    },

    bindLoginForm() {
        const loginForm = document.getElementById('loginForm');
        if (!loginForm) return;

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Reset error messages
            document.querySelectorAll('.error-msg').forEach(el => el.textContent = '');
            const messageDiv = document.getElementById('loginMessage');
            messageDiv.className = 'alert d-none';

            // Basic validation
            let valid = true;
            const email = document.getElementById('loginEmail').value.trim();
            const password = document.getElementById('loginPassword').value;

            if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
                document.getElementById('loginEmailError').textContent = 'Please enter a valid email';
                valid = false;
            }

            if (!password) {
                document.getElementById('loginPasswordError').textContent = 'Password is required';
                valid = false;
            }

            if (!valid) return;

            try {
                const formData = new FormData(loginForm);
                const response = await fetch('misc/login_function.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });

                const result = await response.json();

                // Clear previous field errors
                const emailErr = document.getElementById('loginEmailError');
                const passErr = document.getElementById('loginPasswordError');
                if (emailErr) emailErr.textContent = '';
                if (passErr) passErr.textContent = '';

                if (result.success) {
                    // show inline success and reload
                    messageDiv.className = 'alert alert-success mt-3';
                    messageDiv.textContent = result.message || 'Login successful';
                    setTimeout(() => { window.location.reload(); }, 800);
                } else {
                    const msg = result.message || 'Invalid credentials';
                    // Map message to fields
                    if (/email/i.test(msg)) {
                        if (emailErr) emailErr.textContent = msg;
                    }
                    if (/password|credential|invalid/i.test(msg)) {
                        if (passErr) passErr.textContent = msg;
                    }
                    if (!(/email/i.test(msg) || /password|credential|invalid/i.test(msg))) {
                        // fallback to showing under both fields
                        if (emailErr) emailErr.textContent = msg;
                        if (passErr) passErr.textContent = msg;
                    }
                    messageDiv.className = 'alert alert-danger mt-3';
                    messageDiv.textContent = msg;
                }
            } catch (error) {
                messageDiv.className = 'alert alert-danger mt-3';
                messageDiv.textContent = 'An error occurred. Please try again.';
            }
        });
    },

    bindSignupForm() {
        const signupForm = document.getElementById('signupForm');
        if (!signupForm) return;

        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Reset error messages
            document.querySelectorAll('.error-msg').forEach(el => el.textContent = '');
            
            // Validation will be handled by the backend
            try {
                const formData = new FormData(signupForm);
                const response = await fetch('misc/signup_function.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    document.getElementById('openLogin').click();
                    signupForm.reset();
                } else {
                    // Display specific error messages
                    Object.keys(result.errors || {}).forEach(field => {
                        const errorDiv = document.getElementById(`${field}Error`);
                        if (errorDiv) {
                            errorDiv.textContent = result.errors[field];
                        }
                    });
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            }
        });
    },

    bindPasswordToggles() {
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', () => {
                const target = document.querySelector(button.dataset.target);
                if (!target) return;
                
                const type = target.type === 'password' ? 'text' : 'password';
                target.type = type;
                
                const icon = button.querySelector('i');
                if (icon) {
                    icon.className = type === 'password' ? 'fa fa-eye' : 'fa fa-eye-slash';
                }
            });
        });
    },

    bindFormSwitching() {
        const openSignup = document.getElementById('openSignup');
        const openLogin = document.getElementById('openLogin');
        const loginFormContainer = document.getElementById('loginFormContainer');
        const signupFormContainer = document.getElementById('signupFormContainer');
        const modalTitle = document.getElementById('modalTitle');

        if (openSignup) {
            openSignup.addEventListener('click', (e) => {
                e.preventDefault();
                loginFormContainer.classList.add('d-none');
                signupFormContainer.classList.remove('d-none');
                modalTitle.textContent = 'Sign Up';
            });
        }

        if (openLogin) {
            openLogin.addEventListener('click', (e) => {
                e.preventDefault();
                signupFormContainer.classList.add('d-none');
                loginFormContainer.classList.remove('d-none');
                modalTitle.textContent = 'Login';
            });
        }
    }
};

// Initialize authentication functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    Auth.init();
});