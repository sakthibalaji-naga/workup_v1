<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php'); ?>

<body class="tw-bg-neutral-100 login_admin">

    <div class="tw-max-w-md tw-mx-auto tw-pt-24 authentication-form-wrapper tw-relative tw-z-20">
        <div class="company-logo text-center">
            <?php get_dark_company_logo(); ?>
        </div>

        <div class=" text-center tw-mb-5">
            <h1 class="tw-text-neutral-800 tw-text-2xl tw-font-bold tw-mb-1">
                <?= _l('admin_auth_login_heading'); ?>
            </h1>
            <p class="tw-text-neutral-600">
                <?= _l('welcome_back_sign_in'); ?>
            </p>
        </div>

        <div
            class="tw-bg-white tw-mx-2 sm:tw-mx-6 tw-py-8 tw-px-6 sm:tw-px-8 tw-shadow-sm tw-rounded-lg tw-border tw-border-solid tw-border-neutral-600/20">

            <?php $this->load->view('authentication/includes/alerts'); ?>

            <?= form_open($this->uri->uri_string()); ?>

            <?= validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>

            <?php hooks()->do_action('after_admin_login_form_start'); ?>

            <div class="form-group">
                <label for="email" class="control-label !tw-mb-3">
                    <?= _l('admin_auth_login_email'); ?> or Employee Code
                </label>
                <input type="text" id="email" name="email" class="form-control" autofocus="1" placeholder="Enter email or employee code">
            </div>

            <div class="form-group tw-mt-8">
                <span class="tw-inline-flex tw-justify-between tw-items-end tw-w-full tw-mb-3">
                    <label for="password" class="control-label !tw-m-0">
                        <?= _l('admin_auth_login_password'); ?>
                    </label>
                    <a href="<?= admin_url('authentication/forgot_password'); ?>"
                        class="text-muted">
                        <?= _l('admin_auth_login_fp'); ?>
                    </a>
                </span>

                <div class="tw-relative password-field-wrapper">
                    <input type="password" id="password" name="password" class="form-control">
                    <button type="button" class="password-toggle" id="passwordToggleButton" aria-label="Show password" aria-pressed="false" onclick="togglePasswordVisibility()">
                        <i class="fa fa-eye" id="passwordToggleIcon"></i>
                    </button>
                </div>
            </div>

            <?php if (show_recaptcha()) { ?>
            <div class="g-recaptcha tw-mb-4"
                data-sitekey="<?= get_option('recaptcha_site_key'); ?>">
            </div>
            <?php } ?>

            <div class="form-group">
                <div class="checkbox checkbox-inline">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">
                        <?= _l('admin_auth_login_remember_me'); ?></label>
                </div>
            </div>

            <div class="tw-mt-6">
                <button type="submit" class="btn btn-primary btn-block tw-font-semibold tw-py-2">
                    <?= _l('admin_auth_login_button'); ?>
                </button>
            </div>

            <?php hooks()->do_action('before_admin_login_form_close'); ?>

            <?= form_close(); ?>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            const toggleButton = document.getElementById('passwordToggleButton');
            if (!passwordInput || !toggleIcon || !toggleButton) {
                return;
            }

            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';

            toggleIcon.classList.toggle('fa-eye', !isHidden);
            toggleIcon.classList.toggle('fa-eye-slash', isHidden);
            toggleButton.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            toggleButton.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        }

        // Set initial icon state
        document.addEventListener('DOMContentLoaded', function() {
            const toggleIcon = document.getElementById('passwordToggleIcon');
            const toggleButton = document.getElementById('passwordToggleButton');
            if (toggleIcon) {
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
            if (toggleButton) {
                toggleButton.setAttribute('aria-label', 'Show password');
                toggleButton.setAttribute('aria-pressed', 'false');
            }
        });
    </script>

</body>

</html>
