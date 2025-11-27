<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="screen-options-area"></div>
    <div class="screen-options-btn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="tw-w-5 tw-h-5 ltr:tw-mr-1 rtl:tw-ml-1">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>

        <?= _l('dashboard_options'); ?>
    </div>
    <div class="content">
        <div class="row">
            <?php $this->load->view('admin/includes/alerts'); ?>

            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="text-center mb-4">
                            <div class="force-password-header">
                                <i class="fa fa-lock fa-4x text-primary mb-3" aria-hidden="true"></i>
                                <h3 class="text-primary">Set Your Password</h3>
                                <p class="text-muted">For security reasons, you must set a new password to continue using Workup.</p>
                            </div>
                        </div>

                        <?php echo form_open($this->uri->uri_string()); ?>
                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <div class="form-group">
                                    <label for="password" class="control-label"><?php echo _l('staff_password'); ?> <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password" id="password" required>
                                        <span class="input-group-addon password-toggle" data-target="password">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <!-- Password Strength Indicator -->
                                    <div class="password-strength-container mt-2">
                                        <div class="password-strength-meter">
                                            <div class="password-strength-bar" id="password-strength-bar"></div>
                                        </div>
                                        <div class="password-strength-text" id="password-strength-text">
                                            <small class="text-muted">Password strength: <span id="strength-label">Very Weak</span></small>
                                        </div>
                                        <div class="password-requirements mt-2" id="password-requirements">
                                            <small class="text-muted">
                                                <div class="requirement-item" data-requirement="length">
                                                    <i class="fa fa-times text-danger" aria-hidden="true"></i> At least 8 characters
                                                </div>
                                                <div class="requirement-item" data-requirement="uppercase">
                                                    <i class="fa fa-times text-danger" aria-hidden="true"></i> One uppercase letter
                                                </div>
                                                <div class="requirement-item" data-requirement="lowercase">
                                                    <i class="fa fa-times text-danger" aria-hidden="true"></i> One lowercase letter
                                                </div>
                                                <div class="requirement-item" data-requirement="number">
                                                    <i class="fa fa-times text-danger" aria-hidden="true"></i> One number
                                                </div>
                                                <div class="requirement-item" data-requirement="special">
                                                    <i class="fa fa-times text-danger" aria-hidden="true"></i> One special character
                                                </div>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="passwordr" class="control-label"><?php echo _l('staff_password_repeat'); ?> <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="passwordr" id="passwordr" required>
                                        <span class="input-group-addon password-toggle" data-target="passwordr">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <div class="password-match-indicator mt-1" id="password-match-indicator" style="display: none;">
                                        <small id="password-match-text"></small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="submit-btn">
                                        <i class="fa fa-save" aria-hidden="true"></i> Set Password & Continue
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close(); ?>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                Password must be at least 8 characters long and contain a mix of letters, numbers, and special characters.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.force-password-header {
    padding: 30px 0;
}

.force-password-header .fa-lock {
    color: #007bff;
    margin-bottom: 20px;
}

.force-password-header h3 {
    margin-bottom: 15px;
    font-weight: 600;
}

.panel-body {
    padding: 40px;
}

/* Password Strength Indicator Styles */
.password-strength-container {
    margin-top: 10px;
}

.password-strength-meter {
    width: 100%;
    height: 8px;
    background-color: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}

.password-strength-bar {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 4px;
}

.password-strength-very-weak {
    background-color: #dc3545;
    width: 20%;
}

.password-strength-weak {
    background-color: #fd7e14;
    width: 40%;
}

.password-strength-fair {
    background-color: #ffc107;
    width: 60%;
}

.password-strength-good {
    background-color: #20c997;
    width: 80%;
}

.password-strength-strong {
    background-color: #28a745;
    width: 100%;
}

.password-strength-text {
    text-align: left;
}

.requirement-item {
    display: flex;
    align-items: center;
    margin-bottom: 4px;
    font-size: 12px;
}

.requirement-item i {
    margin-right: 8px;
    width: 12px;
}

.requirement-item.met i {
    color: #28a745 !important;
}

.requirement-item.met i:before {
    content: "\f00c"; /* check icon */
}

.password-match-indicator {
    text-align: left;
}

.password-match-indicator .text-success {
    color: #28a745 !important;
}

.password-match-indicator .text-danger {
    color: #dc3545 !important;
}

/* Password Toggle Styles */
.input-group-addon.password-toggle {
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-left: none;
    padding: 6px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #6c757d;
}

.input-group-addon.password-toggle:hover {
    background-color: #e9ecef;
    color: #495057;
}

.input-group-addon.password-toggle:active {
    background-color: #dee2e6;
}

.input-group .form-control:focus + .input-group-addon.password-toggle {
    border-color: #80bdff;
}

@media (max-width: 768px) {
    .panel-body {
        padding: 20px;
    }

    .force-password-header {
        padding: 20px 0;
    }

    .requirement-item {
        font-size: 11px;
    }
}
</style>

<script>
// Wait for jQuery to be available
function initPasswordStrength() {
    if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
        // jQuery not loaded yet, try again in 100ms
        setTimeout(initPasswordStrength, 100);
        return;
    }

    $(document).ready(function() {
        // Prevent back navigation
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };

        // Password strength checking function
        function checkPasswordStrength(password) {
            var strength = 0;

            // Length check
            if (password.length >= 8) {
                strength += 1;
                $('#password-requirements .requirement-item[data-requirement="length"]').addClass('met');
            } else {
                $('#password-requirements .requirement-item[data-requirement="length"]').removeClass('met');
            }

            // Uppercase check
            if (/[A-Z]/.test(password)) {
                strength += 1;
                $('#password-requirements .requirement-item[data-requirement="uppercase"]').addClass('met');
            } else {
                $('#password-requirements .requirement-item[data-requirement="uppercase"]').removeClass('met');
            }

            // Lowercase check
            if (/[a-z]/.test(password)) {
                strength += 1;
                $('#password-requirements .requirement-item[data-requirement="lowercase"]').addClass('met');
            } else {
                $('#password-requirements .requirement-item[data-requirement="lowercase"]').removeClass('met');
            }

            // Number check
            if (/\d/.test(password)) {
                strength += 1;
                $('#password-requirements .requirement-item[data-requirement="number"]').addClass('met');
            } else {
                $('#password-requirements .requirement-item[data-requirement="number"]').removeClass('met');
            }

            // Special character check
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                strength += 1;
                $('#password-requirements .requirement-item[data-requirement="special"]').addClass('met');
            } else {
                $('#password-requirements .requirement-item[data-requirement="special"]').removeClass('met');
            }

            return strength;
        }

        // Update password strength indicator
        function updatePasswordStrength(password) {
            var strength = checkPasswordStrength(password);
            var strengthBar = $('#password-strength-bar');
            var strengthLabel = $('#strength-label');

            // Remove all strength classes
            strengthBar.removeClass('password-strength-very-weak password-strength-weak password-strength-fair password-strength-good password-strength-strong');

            // Update based on strength
            if (strength === 0) {
                strengthLabel.text('Very Weak');
                strengthBar.addClass('password-strength-very-weak');
            } else if (strength === 1) {
                strengthLabel.text('Very Weak');
                strengthBar.addClass('password-strength-very-weak');
            } else if (strength === 2) {
                strengthLabel.text('Weak');
                strengthBar.addClass('password-strength-weak');
            } else if (strength === 3) {
                strengthLabel.text('Fair');
                strengthBar.addClass('password-strength-fair');
            } else if (strength === 4) {
                strengthLabel.text('Good');
                strengthBar.addClass('password-strength-good');
            } else if (strength === 5) {
                strengthLabel.text('Strong');
                strengthBar.addClass('password-strength-strong');
            }
        }

        // Check password match
        function checkPasswordMatch() {
            var password = $('#password').val();
            var confirmPassword = $('#passwordr').val();
            var matchIndicator = $('#password-match-indicator');
            var matchText = $('#password-match-text');

            if (confirmPassword.length > 0) {
                matchIndicator.show();
                if (password === confirmPassword) {
                    matchText.removeClass('text-danger').addClass('text-success');
                    matchText.html('<i class="fa fa-check" aria-hidden="true"></i> Passwords match');
                    $('#passwordr').closest('.form-group').removeClass('has-error');
                } else {
                    matchText.removeClass('text-success').addClass('text-danger');
                    matchText.html('<i class="fa fa-times" aria-hidden="true"></i> Passwords do not match');
                    $('#passwordr').closest('.form-group').addClass('has-error');
                }
            } else {
                matchIndicator.hide();
                $('#passwordr').closest('.form-group').removeClass('has-error');
            }
        }

        // Password input event handlers
        $('#password').on('input', function() {
            var password = $(this).val();
            updatePasswordStrength(password);

            // Check if password meets minimum requirements
            var strength = checkPasswordStrength(password);
            if (strength >= 3) { // At least fair strength
                $(this).closest('.form-group').removeClass('has-error');
            } else {
                $(this).closest('.form-group').addClass('has-error');
            }

            // Check password match when password changes
            checkPasswordMatch();
        });

        $('#passwordr').on('input', function() {
            checkPasswordMatch();
        });

        // Form validation
        $('form').on('submit', function(e) {
            var password = $('#password').val();
            var confirmPassword = $('#passwordr').val();
            var strength = checkPasswordStrength(password);

            // Check password strength
            if (strength < 3) {
                e.preventDefault();
                alert('Password is too weak. Please ensure your password meets at least the basic requirements.');
                return false;
            }

            // Check password match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                return false;
            }

            // All validations passed
            $('#submit-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Setting Password...');
        });

        // Password visibility toggle functionality
        $('.password-toggle').on('click', function() {
            var targetId = $(this).data('target');
            var targetInput = $('#' + targetId);
            var icon = $(this).find('i');

            if (targetInput.attr('type') === 'password') {
                targetInput.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                targetInput.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Initialize on page load
        updatePasswordStrength($('#password').val());
        checkPasswordMatch();
    });
}

// Start initialization
initPasswordStrength();
</script>

<?php init_tail(); ?>
</body>
</html>
