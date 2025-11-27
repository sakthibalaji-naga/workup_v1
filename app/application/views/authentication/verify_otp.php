<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php');
$otp_resend_cooldown = isset($otp_resend_cooldown) ? $otp_resend_cooldown : (get_option('otp_resend_cooldown') ?: 60);
$otp_expiry_minutes = isset($otp_expiry_minutes) ? $otp_expiry_minutes : 10;
$otp_time = isset($otp_time) ? $otp_time : time();
?>

<body class="tw-bg-neutral-100 authentication forgot-password">

<div class="tw-max-w-md tw-mx-auto tw-pt-24 authentication-form-wrappe tw-relative tw-z-20">
    <div class="company-logo text-center">
        <?= get_dark_company_logo(); ?>
    </div>

    <h1 class="tw-text-2xl tw-text-neutral-800 text-center tw-font-semibold tw-mb-5">
        Verify OTP Code
    </h1>

    <div class="tw-bg-white tw-mx-2 sm:tw-mx-6 tw-py-8 tw-px-6 sm:tw-px-8 tw-shadow-sm tw-rounded-lg tw-border tw-border-solid tw-border-neutral-600/20">
        <?php echo form_open($this->uri->uri_string()); ?>

        <?php echo validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>

        <?php $this->load->view('authentication/includes/alerts'); ?>

        <div class="form-group" style="text-align: center;">
            <label for="otp">OTP Code</label>
            <div style="display: inline-flex; flex-direction: row; align-items: center; gap: 0;">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <input type="text" class="form-control text-center" name="otp[]" maxlength="1" pattern="[0-9]" required
                           inputmode="numeric"
                           style="width: 50px; height: 50px; font-size: 20px; border-radius: 8px;"
                           oninput="moveToNext(this, <?php echo $i; ?>)"
                           onkeydown="handleKey(this, event, <?php echo $i; ?>)">
                    <?php if ($i < 4): ?>
                        <span style="font-size: 20px; color: #666; margin: 0 5px;">-</span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block tw-font-semibold tw-py-2">
            Verify OTP
        </button>

        <!-- OTP Expiry Countdown Timer -->
        <div class="text-center mt-3">
            <div id="otpExpiryTimer" class="alert alert-info" style="padding: 8px; margin-bottom: 10px;">
                <small>
                    <i class="fa fa-clock-o"></i>
                    OTP expires in: <span id="otpCountdown">00:00</span>
                </small>
            </div>
        </div>

        <div class="text-center mt-3">
            <p class="text-muted small">Didn't receive OTP?</p>
            <a id="resendOtp" href="javascript:void(0);" onclick="resendOtp()"
               class="btn btn-secondary disabled">
                Resend OTP (<?= $otp_resend_cooldown ?>)
            </a>
        </div>

        <?php echo form_close(); ?>
    </div>

</div>

<script>
let countdown = parseInt('<?= $otp_resend_cooldown ?>') || 60;
let resendBtn = document.getElementById('resendOtp');

// OTP Expiry Timer
let otpExpiryTime = parseInt('<?= $otp_expiry_minutes ?>') * 60; // Convert minutes to seconds
let otpSentTime = parseInt('<?= $otp_time ?>');
let currentTime = Math.floor(Date.now() / 1000);
let timeElapsed = currentTime - otpSentTime;
let remainingTime = Math.max(0, otpExpiryTime - timeElapsed);

let otpCountdownElement = document.getElementById('otpCountdown');
let otpExpiryTimerElement = document.getElementById('otpExpiryTimer');

function updateOtpExpiryTimer() {
    if (remainingTime <= 0) {
        // OTP has expired
        otpCountdownElement.textContent = 'EXPIRED';
        otpExpiryTimerElement.className = 'alert alert-danger';
        otpExpiryTimerElement.innerHTML = '<small><i class="fa fa-exclamation-triangle"></i> OTP has expired. Please request a new one.</small>';

        // Disable the verify button
        let verifyBtn = document.querySelector('button[type="submit"]');
        if (verifyBtn) {
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'OTP Expired';
        }

        return;
    }

    let minutes = Math.floor(remainingTime / 60);
    let seconds = remainingTime % 60;

    otpCountdownElement.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

    // Change color when less than 2 minutes remaining
    if (remainingTime <= 120) {
        otpExpiryTimerElement.className = 'alert alert-warning';
    }

    remainingTime--;
    setTimeout(updateOtpExpiryTimer, 1000);
}

function updateResendBtn() {
    if (countdown > 0) {
        resendBtn.classList.add('disabled');
        resendBtn.textContent = `Resend OTP (${countdown})`;
        countdown--;
        setTimeout(updateResendBtn, 1000);
    } else {
        resendBtn.classList.remove('disabled');
        resendBtn.textContent = 'Resend OTP';
    }
}

function resendOtp() {
    if (countdown > 0) return;

    // Reset the OTP expiry timer immediately for better UX
    otpExpiryTime = parseInt('<?= $otp_expiry_minutes ?>') * 60;
    remainingTime = otpExpiryTime;
    otpExpiryTimerElement.className = 'alert alert-info';
    otpExpiryTimerElement.innerHTML = '<small><i class="fa fa-clock-o"></i> OTP expires in: <span id="otpCountdown">00:00</span></small>';
    otpCountdownElement = document.getElementById('otpCountdown');

    // Re-enable the verify button if it was disabled
    let verifyBtn = document.querySelector('button[type="submit"]');
    if (verifyBtn) {
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify OTP';
    }

    // Restart the expiry timer
    updateOtpExpiryTimer();

    window.location.href = '<?= admin_url('authentication/resend_otp'); ?>';
}

// Start the timers on page load
updateResendBtn();
updateOtpExpiryTimer();

function moveToNext(input, index) {
    let otpInputs = document.querySelectorAll('input[name="otp[]"]');
    let value = input.value;

    // Allow only numbers
    if (!/^[0-9]$/.test(value) && value !== '') {
        input.value = '';
        return;
    }

    if (value.length === 1) {
        if (index < 4) {
            otpInputs[index + 1].focus();
        }
    }
}

function handleKey(input, event, index) {
    let otpInputs = document.querySelectorAll('input[name="otp[]"]');

    if (event.key === 'Backspace') {
        if (input.value === '' && index > 0) {
            otpInputs[index - 1].focus();
        }
    } else if (event.key === 'ArrowLeft') {
        if (index > 0) {
            otpInputs[index - 1].select();
        }
    } else if (event.key === 'ArrowRight') {
        if (index < 4) {
            otpInputs[index + 1].select();
        }
    }
}

// Paste handler for better UX
document.addEventListener('paste', function(e) {
    if (e.target.name === 'otp[]') {
        e.preventDefault();
        let pasteData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 5);
        let otpInputs = document.querySelectorAll('input[name="otp[]"]');

        for (let i = 0; i < pasteData.length && i < 5; i++) {
            otpInputs[i].value = pasteData[i];
        }

        // Focus the next empty field or last field
        let focusIndex = Math.min(pasteData.length, 4);
        otpInputs[focusIndex].focus();
    }
});
</script>

</body>

</html>
