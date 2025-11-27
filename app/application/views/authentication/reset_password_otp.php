<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php'); ?>

<body class="tw-bg-neutral-100 authentication reset-password">
    <div class="tw-max-w-md tw-mx-auto tw-pt-24 authentication-form-wrapper tw-relative tw-z-20">

        <div class="company-logo text-center">
            <?= get_dark_company_logo(); ?>
        </div>

        <h1 class="tw-text-2xl tw-text-neutral-800 text-center tw-font-semibold tw-mb-5">
            Set New Password
        </h1>

        <div
            class="tw-bg-white tw-mx-2 sm:tw-mx-6 tw-py-8 tw-px-6 sm:tw-px-8 tw-shadow-sm tw-rounded-lg tw-border tw-border-solid tw-border-neutral-600/20">
            <?php echo form_open($this->uri->uri_string()); ?>
            <?php echo validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>
            <?php $this->load->view('authentication/includes/alerts'); ?>

            <div class="alert alert-info">
                <strong>Password Requirements:</strong>
                <ul class="mb-0 mt-2">
                    <li>Minimum 8 characters</li>
                    <li>At least one uppercase letter (A-Z)</li>
                    <li>At least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)</li>
                    <li>At least one number (0-9)</li>
                </ul>
            </div>

            <div class="form-group">
                <label for="password" class="control-label !tw-mb-3">
                    <?= _l('admin_auth_reset_password'); ?>
                </label>
                <div class="tw-relative password-field-wrapper">
                    <input type="password" id="password" name="password" class="form-control" required>
                    <button type="button" class="password-toggle" id="passwordToggleButton" aria-label="Show password" aria-pressed="false" onclick="togglePasswordVisibility('password', 'passwordToggleIcon')">
                        <i class="fa fa-eye" id="passwordToggleIcon"></i>
                    </button>
                </div>
                <div id="password-strength-indicator" class="password-strength mt-1" style="display: none;">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar" id="password-strength-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small id="password-strength-text" class="text-muted"></small>
                </div>
            </div>

            <div class="form-group">
                <label for="passwordr" class="control-label !tw-mb-3">
                    <?= _l('admin_auth_reset_password_repeat'); ?>
                </label>
                <div class="tw-relative password-field-wrapper">
                    <input type="password" id="passwordr" name="passwordr" class="form-control" required>
                    <button type="button" class="password-toggle" id="passwordrToggleButton" aria-label="Show password" aria-pressed="false" onclick="togglePasswordVisibility('passwordr', 'passwordrToggleIcon')">
                        <i class="fa fa-eye" id="passwordrToggleIcon"></i>
                    </button>
                </div>
                <div id="password-match-indicator" class="mt-1" style="display: none;">
                    <small id="password-match-text" class="text-danger"></small>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block tw-font-semibold tw-py-2">
                Set New Password
            </button>
            <?php echo form_close(); ?>
        </div>
    </div>

    <style>
        .shake {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .password-strength .progress-bar {
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .password-strength-weak { background-color: #dc3545; }
        .password-strength-medium { background-color: #ffc107; }
        .password-strength-strong { background-color: #28a745; }

        .password-match-success { color: #28a745 !important; }
        .password-match-error { color: #dc3545 !important; }
    </style>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            if (!passwordInput || !toggleIcon) {
                return;
            }

            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';

            toggleIcon.classList.toggle('fa-eye', !isHidden);
            toggleIcon.classList.toggle('fa-eye-slash', isHidden);
        }

        function checkPasswordStrength(password) {
            let score = 0;
            let feedback = [];

            // Length check
            if (password.length >= 8) {
                score += 25;
            } else {
                feedback.push('At least 8 characters');
            }

            // Uppercase check
            if (/[A-Z]/.test(password)) {
                score += 25;
            } else {
                feedback.push('One uppercase letter');
            }

            // Special character check
            if (/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password)) {
                score += 25;
            } else {
                feedback.push('One special character');
            }

            // Number check
            if (/[0-9]/.test(password)) {
                score += 25;
            } else {
                feedback.push('One number');
            }

            return { score: score, feedback: feedback };
        }

        function updatePasswordStrength() {
            const password = document.getElementById('password').value;
            const indicator = document.getElementById('password-strength-indicator');
            const bar = document.getElementById('password-strength-bar');
            const text = document.getElementById('password-strength-text');

            if (password.length === 0) {
                indicator.style.display = 'none';
                return;
            }

            indicator.style.display = 'block';
            const result = checkPasswordStrength(password);

            bar.style.width = result.score + '%';

            // Remove all strength classes
            bar.classList.remove('password-strength-weak', 'password-strength-medium', 'password-strength-strong');

            if (result.score < 50) {
                bar.classList.add('password-strength-weak');
                text.textContent = 'Weak: ' + result.feedback.join(', ');
                text.className = 'text-danger';
            } else if (result.score < 100) {
                bar.classList.add('password-strength-medium');
                text.textContent = 'Medium: ' + result.feedback.join(', ');
                text.className = 'text-warning';
            } else {
                bar.classList.add('password-strength-strong');
                text.textContent = 'Strong password!';
                text.className = 'text-success';
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('passwordr').value;
            const indicator = document.getElementById('password-match-indicator');
            const text = document.getElementById('password-match-text');

            if (confirmPassword.length === 0) {
                indicator.style.display = 'none';
                return;
            }

            indicator.style.display = 'block';

            if (password === confirmPassword) {
                text.textContent = '✓ Passwords match';
                text.className = 'password-match-success';
            } else {
                text.textContent = '✗ Passwords do not match';
                text.className = 'password-match-error';
            }
        }

        function shakeElement(element) {
            element.classList.add('shake');
            setTimeout(() => {
                element.classList.remove('shake');
            }, 500);
        }

        // Set initial icon states and add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const icons = ['passwordToggleIcon', 'passwordrToggleIcon'];
            icons.forEach(iconId => {
                const icon = document.getElementById(iconId);
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });

            // Add password strength checking
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('passwordr');

            passwordInput.addEventListener('input', updatePasswordStrength);
            passwordInput.addEventListener('input', checkPasswordMatch);
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);

            // Add form validation with animation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                let hasErrors = false;

                // Check password strength
                const strengthResult = checkPasswordStrength(password);
                if (strengthResult.score < 100) {
                    shakeElement(passwordInput.closest('.form-group'));
                    hasErrors = true;
                }

                // Check password match
                if (password !== confirmPassword) {
                    shakeElement(confirmPasswordInput.closest('.form-group'));
                    hasErrors = true;
                }

                if (hasErrors) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>

</html>
