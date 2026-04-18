<?php
// ========================================
// FOOTER COMPONENT
// ========================================
// This file should be included at the bottom of every page
// It closes HTML tags opened in _head.php
?>
</div>
    <!-- Footer Section -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>📱 ELEX Store</h3>
                <p>Your trusted source for quality electronics since 2024.</p>
                <div class="social-links">
                    <a href="#" class="social-link">📘</a>
                    <a href="#" class="social-link">📷</a>
                    <a href="#" class="social-link">🐦</a>
                    <a href="#" class="social-link">💼</a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">🏠 Home</a></li>
                    <li><a href="products.php">🛍️ Products</a></li>
                    <li><a href="about.php">ℹ️ About Us</a></li>
                    <li><a href="contact.php">📞 Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Account</h3>
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php">📊 Dashboard</a></li>
                        <li><a href="/profile.php">👤 My Profile</a></li>
                        <li><a href="/logout.php" onclick="return confirm('Logout?')">🚪 Logout</a></li>
                    <?php else: ?>
                        <li><a href="/login.php">🔐 Login</a></li>
                        <li><a href="register.php">📝 Register</a></li>
                        <li><a href="forgot_password.php">❓ Forgot Password</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="faq.php">❓ FAQ</a></li>
                    <li><a href="returns.php">🔄 Returns Policy</a></li>
                    <li><a href="shipping.php">🚚 Shipping Info</a></li>
                    <li><a href="privacy.php">🔒 Privacy Policy</a></li>
                    <li><a href="terms.php">📜 Terms & Conditions</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact Info</h3>
                <ul class="contact-info">
                    <li>📍 67, Jalan Malinja 2, Setapak, Persekutuan Wilayah Kuala Lumpur</li>
                    <li>📞 +60 12-411-4008</li>
                    <li>✉️ support@elexstore.com</li>
                    <li>⏰ Mon-Fri: 9AM - 6PM</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> ELEXStore. All rights reserved.</p>
                <div class="payment-methods">
                    <span>💳 Visa</span>
                    <span>💳 Mastercard</span>
                    <span>💳 PayPal</span>
                    <span>💳 FPX</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button onclick="topFunction()" id="backToTop" class="back-to-top" title="Go to top">↑</button>

    <!-- Notification Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Load JavaScript -->
    <script>
        // ========================================
        // BACK TO TOP BUTTON
        // ========================================
        let backToTopButton = document.getElementById("backToTop");
        
        window.onscroll = function() {
            scrollFunction();
        };
        
        function scrollFunction() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                backToTopButton.style.display = "block";
            } else {
                backToTopButton.style.display = "none";
            }
        }
        
        function topFunction() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
        }
        
        // ========================================
        // TOAST NOTIFICATION SYSTEM
        // ========================================
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            const icons = {
                success: '✓',
                error: '✗',
                warning: '⚠',
                info: 'ℹ'
            };
            
            toast.innerHTML = `
                <span class="toast-icon">${icons[type] || '✓'}</span>
                <span class="toast-message">${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        }
        
        // ========================================
        // FORM VALIDATION HELPERS
        // ========================================
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        function validatePassword(password) {
            return {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
            };
        }
        
        // ========================================
        // PASSWORD STRENGTH METER
        // ========================================
        function updatePasswordStrength(passwordField, strengthIndicatorId) {
            const password = passwordField.value;
            const strength = validatePassword(password);
            const indicator = document.getElementById(strengthIndicatorId);
            
            if (!indicator) return;
            
            let score = 0;
            if (strength.length) score++;
            if (strength.uppercase) score++;
            if (strength.lowercase) score++;
            if (strength.number) score++;
            if (strength.special) score++;
            
            indicator.className = 'password-strength';
            if (password.length === 0) {
                indicator.innerHTML = '';
                return;
            }
            
            if (score <= 2) {
                indicator.className += ' weak';
                indicator.innerHTML = 'Weak Password';
            } else if (score <= 4) {
                indicator.className += ' medium';
                indicator.innerHTML = 'Medium Password';
            } else {
                indicator.className += ' strong';
                indicator.innerHTML = 'Strong Password';
            }
        }
        
        // ========================================
        // AJAX FORM SUBMIT
        // ========================================
        async function submitFormAjax(formElement, url, method = 'POST') {
            const formData = new FormData(formElement);
            
            try {
                const response = await fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                    return data;
                } else {
                    showToast(data.message, 'error');
                    return data;
                }
            } catch (error) {
                showToast('An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            }
        }
        
        // ========================================
        // COUNTDOWN TIMER
        // ========================================
        function startCountdown(seconds, elementId, onComplete) {
            let remaining = seconds;
            const element = document.getElementById(elementId);
            
            if (!element) return;
            
            const interval = setInterval(() => {
                if (remaining <= 0) {
                    clearInterval(interval);
                    if (onComplete) onComplete();
                } else {
                    const minutes = Math.floor(remaining / 60);
                    const secs = remaining % 60;
                    element.textContent = `${minutes}:${secs.toString().padStart(2, '0')}`;
                    remaining--;
                }
            }, 1000);
        }
        
        // ========================================
        // SESSION TIMEOUT WARNING
        // ========================================
        let sessionTimeoutWarning = null;
        
        function initSessionTimeout(timeoutSeconds, warningSeconds) {
            if (sessionTimeoutWarning) return;
            
            const warningTime = (timeoutSeconds - warningSeconds) * 1000;
            
            setTimeout(() => {
                showToast('Your session will expire soon. Please save your work.', 'warning');
                
                // Show modal warning
                const modal = document.createElement('div');
                modal.className = 'modal-overlay';
                modal.innerHTML = `
                    <div class="modal-content">
                        <h3>Session Expiring Soon</h3>
                        <p>Your session will expire in ${Math.floor(warningSeconds / 60)} minutes.</p>
                        <button onclick="window.location.href='/security/login.php'" class="btn btn-primary">Login Again</button>
                        <button onclick="this.closest('.modal-overlay').remove()" class="btn btn-secondary">Dismiss</button>
                    </div>
                `;
                document.body.appendChild(modal);
            }, warningTime);
        }
        
        // ========================================
        // AUTO-LOGOUT AFTER INACTIVITY
        // ========================================
        let inactivityTimer;
        
        function resetInactivityTimer(logoutUrl, timeoutMinutes) {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                showToast('You have been logged out due to inactivity.', 'warning');
                window.location.href = logoutUrl;
            }, timeoutMinutes * 60 * 1000);
        }
        
        // ========================================
        // DARK MODE TOGGLE
        // ========================================
        function initDarkMode() {
            const darkMode = localStorage.getItem('darkMode') === 'enabled';
            
            if (darkMode) {
                document.body.classList.add('dark-mode');
            }
            
            const toggleBtn = document.getElementById('darkModeToggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    document.body.classList.toggle('dark-mode');
                    if (document.body.classList.contains('dark-mode')) {
                        localStorage.setItem('darkMode', 'enabled');
                    } else {
                        localStorage.setItem('darkMode', 'disabled');
                    }
                });
            }
        }
        
        // ========================================
        // MOBILE MENU TOGGLE
        // ========================================
        function initMobileMenu() {
            const menuBtn = document.getElementById('mobileMenuBtn');
            const navMenu = document.querySelector('.nav-menu');
            
            if (menuBtn && navMenu) {
                menuBtn.addEventListener('click', () => {
                    navMenu.classList.toggle('show');
                });
            }
        }
        
        // ========================================
        // PASSWORD SHOW/HIDE TOGGLE
        // ========================================
        function togglePasswordVisibility(passwordFieldId, toggleButtonId) {
            const passwordField = document.getElementById(passwordFieldId);
            const toggleBtn = document.getElementById(toggleButtonId);
            
            if (passwordField && toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    const type = passwordField.type === 'password' ? 'text' : 'password';
                    passwordField.type = type;
                    toggleBtn.textContent = type === 'password' ? '👁️' : '🙈';
                });
            }
        }
        
        // ========================================
        // FORM AUTO-SAVE (for profile, etc.)
        // ========================================
        let autoSaveTimer;
        
        function initAutoSave(formId, saveUrl, delay = 2000) {
            const form = document.getElementById(formId);
            if (!form) return;
            
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = setTimeout(() => {
                        const formData = new FormData(form);
                        formData.append('auto_save', '1');
                        
                        fetch(saveUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Auto-saved', 'success');
                            }
                        })
                        .catch(error => console.error('Auto-save error:', error));
                    }, delay);
                });
            });
        }
        
        // ========================================
        // INITIALIZE ALL FEATURES
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize dark mode
            initDarkMode();
            
            // Initialize mobile menu
            initMobileMenu();
            
            // Initialize back to top button
            if (backToTopButton) {
                backToTopButton.style.display = "none";
            }
            
            // Show flash messages from PHP
            <?php if (function_exists('getFlashMessage') && $flash = getFlashMessage()): ?>
                showToast('<?php echo addslashes($flash['message']); ?>', '<?php echo $flash['type']; ?>');
            <?php endif; ?>
            
            // Initialize all password strength meters
            document.querySelectorAll('.password-strength-input').forEach(input => {
                const indicatorId = input.id + '-strength';
                input.addEventListener('input', function() {
                    updatePasswordStrength(this, indicatorId);
                });
            });
            
            // Initialize all password toggle buttons
            document.querySelectorAll('.toggle-password').forEach(btn => {
                const targetId = btn.getAttribute('data-target');
                if (targetId) {
                    togglePasswordVisibility(targetId, btn.id);
                }
            });
        });
        
        // ========================================
        // CONSOLE WARNINGS FOR DEVELOPMENT
        // ========================================
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.log('%c🔧 Development Mode', 'color: #ff6600; font-size: 16px; font-weight: bold;');
            <?php if (session_status() === PHP_SESSION_ACTIVE): ?>
                console.log('%cSession ID: <?php echo session_id(); ?>', 'color: #666;');
            <?php endif; ?>
        }
        
        // ========================================
        // DISABLE RIGHT CLICK ON SENSITIVE PAGES (Optional)
        // ========================================
        const sensitivePages = ['profile.php', 'dashboard.php', 'checkout.php'];
        if (sensitivePages.includes(window.location.pathname.split('/').pop())) {
            // Uncomment to disable right click on sensitive pages
            // document.addEventListener('contextmenu', function(e) {
            //     e.preventDefault();
            //     return false;
            // });
        }
    </script>

    <!-- Simple Footer Credit Line -->
    <div style="text-align: center; padding: 10px; background: #1a202c; color: #cbd5e0; font-size: 12px;">
        Developed by <strong>ELEX Store Team</strong> &middot;
        Copyrighted &copy; <?= date('Y') ?>
    </div>

</body>
</html>

<?php
// Close database connection if needed
if (isset($connection) && $connection) {
    // Uncomment if you want to close connection automatically
    // mysqli_close($connection);
}
?>