<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php'); ?>

<body class="tw-bg-neutral-100 authentication choose-otp-method">

<div class="tw-max-w-md tw-mx-auto tw-pt-24 authentication-form-wrappe tw-relative tw-z-20">
    <div class="company-logo text-center">
        <?= get_dark_company_logo(); ?>
    </div>

    <h1 class="tw-text-2xl tw-text-neutral-800 text-center tw-font-semibold tw-mb-5">
        Choose OTP Method
    </h1>

    <div class="tw-bg-white tw-mx-2 sm:tw-mx-6 tw-py-8 tw-px-6 sm:tw-px-8 tw-shadow-sm tw-rounded-lg tw-border tw-border-solid tw-border-neutral-600/20">
        <p class="tw-text-center tw-text-neutral-600 tw-mb-6">
            Please select how you would like to receive your OTP code:
        </p>

        <?php echo form_open($this->uri->uri_string()); ?>

        <?php echo validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>

        <?php $this->load->view('authentication/includes/alerts'); ?>

        <div class="tw-space-y-4">
            <?php if ($has_phone): ?>
                <label class="tw-flex tw-items-center tw-p-4 tw-border tw-border-gray-200 tw-rounded-lg tw-hover:bg-gray-50 tw-cursor-pointer">
                    <input type="radio" name="otp_method" value="sms" class="tw-mr-3" required>
                    <div class="tw-flex-1">
                        <div class="tw-font-medium tw-text-gray-900">SMS to Phone Number</div>
                        <div class="tw-text-sm tw-text-gray-600">
                            Receive OTP via SMS to your registered mobile number ending with
                            <strong><?php echo $masked_phone; ?></strong>
                        </div>
                    </div>
                </label>
            <?php endif; ?>

            <?php if ($has_email): ?>
                <label class="tw-flex tw-items-center tw-p-4 tw-border tw-border-gray-200 tw-rounded-lg tw-hover:bg-gray-50 tw-cursor-pointer">
                    <input type="radio" name="otp_method" value="email" class="tw-mr-3" required>
                    <div class="tw-flex-1">
                        <div class="tw-font-medium tw-text-gray-900">Email</div>
                        <div class="tw-text-sm tw-text-gray-600">
                            Receive OTP via email to <strong><?php echo $masked_email; ?></strong>
                        </div>
                    </div>
                </label>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary btn-block tw-font-semibold tw-py-2 tw-mt-6">
            Send OTP
        </button>

        <div class="tw-text-center tw-mt-4">
            <a href="<?= admin_url('authentication/forgot_password'); ?>" class="tw-text-sm tw-text-gray-600 hover:tw-text-gray-800">
                ← Back to Enter Employee Code
            </a>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

</body>

</html>
