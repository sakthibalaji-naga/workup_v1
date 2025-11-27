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

            <?php hooks()->do_action('before_start_render_dashboard_content'); ?>

            <div class="clearfix"></div>

            <div class="col-md-12 mtop20" data-container="top-12">
                <?php render_dashboard_widgets('top-12'); ?>
            </div>

            <?php hooks()->do_action('after_dashboard_top_container'); ?>

            <div class="col-md-6" data-container="middle-left-6">
                <?php render_dashboard_widgets('middle-left-6'); ?>
            </div>
            <div class="col-md-6" data-container="middle-right-6">
                <?php render_dashboard_widgets('middle-right-6'); ?>
            </div>

            <?php hooks()->do_action('after_dashboard_half_container'); ?>

            <div class="col-md-8" data-container="left-8">
                <?php render_dashboard_widgets('left-8'); ?>
            </div>
            <div class="col-md-4" data-container="right-4">
                <?php render_dashboard_widgets('right-4'); ?>
            </div>

            <div class="clearfix"></div>

            <div class="col-md-4" data-container="bottom-left-4">
                <?php render_dashboard_widgets('bottom-left-4'); ?>
            </div>
            <div class="col-md-4" data-container="bottom-middle-4">
                <?php render_dashboard_widgets('bottom-middle-4'); ?>
            </div>
            <div class="col-md-4" data-container="bottom-right-4">
                <?php render_dashboard_widgets('bottom-right-4'); ?>
            </div>

            <?php hooks()->do_action('after_dashboard'); ?>
        </div>
    </div>
</div>
<script>
    app.calendarIDs = '<?= json_encode($google_ids_calendars); ?>';
</script>
<?php init_tail(); ?>
<?php $this->load->view('admin/utilities/calendar_template'); ?>
<?php $this->load->view('admin/dashboard/dashboard_js'); ?>

<!-- Welcome Popup Modal -->
<?php
log_message('debug', 'Dashboard view: show_welcome_popup variable check: ' . (isset($show_welcome_popup) ? ($show_welcome_popup ? 'true' : 'false') : 'not set'));
if (isset($show_welcome_popup) && $show_welcome_popup):
?>
<div id="welcome-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="welcome-animation">
                    <div class="welcome-logo">
                        <img src="<?= base_url('assets/images/company-logo.png'); ?>" alt="Naga Limited Logo" class="company-logo-img" onerror="this.style.display='none';">
                        <div class="company-logo-fallback">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                            <div class="company-name">NAGA LIMITED</div>
                        </div>
                    </div>
                    <h2 class="welcome-title">
                        Welcome to Workup!
                        <span class="party-popper">🎉</span>
                    </h2>
                    <p class="welcome-message">
                        Welcome to <strong>Workup</strong>, the internal software platform of <strong>Naga Limited</strong>.<br><br>
                        Workup helps you manage <strong>Ticketing</strong> efficiently. In the future, we'll be adding <strong>Task Management</strong> and <strong>Project Management</strong> features.<br><br>
                        Let's get started with exploring your new workspace!
                    </p>
                </div>
                <button type="button" class="btn btn-primary btn-lg welcome-next-btn" onclick="dismissWelcomePopup()">
                    <i class="fa fa-arrow-right" aria-hidden="true"></i> Get Started
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.welcome-animation {
    padding: 40px 20px;
    animation: welcomeBounce 2s ease-in-out;
}

.welcome-logo {
    margin-bottom: 30px;
    position: relative;
}

.company-logo-img {
    max-width: 150px;
    max-height: 80px;
    object-fit: contain;
}

.company-logo-fallback {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.company-logo-fallback .fa {
    font-size: 60px;
    color: #007bff;
    animation: iconPulse 2s infinite;
}

/* Paper plane flying animation */
.welcome-logo .fa-paper-plane {
    animation: paperPlaneFly 3s ease-in-out infinite;
    font-size: 60px;
    color: #007bff;
}

@keyframes paperPlaneFly {
    0% {
        transform: translateX(-100px) translateY(0px) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    25% {
        transform: translateX(0px) translateY(-20px) rotate(5deg);
    }
    50% {
        transform: translateX(20px) translateY(-10px) rotate(-5deg);
    }
    75% {
        transform: translateX(-10px) translateY(-25px) rotate(3deg);
    }
    100% {
        transform: translateX(0px) translateY(0px) rotate(0deg);
        opacity: 1;
    }
}

.company-name {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    letter-spacing: 2px;
}

.welcome-icon {
    font-size: 80px;
    color: #007bff;
    margin-bottom: 30px;
    animation: iconPulse 2s infinite;
}

.welcome-title {
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.party-popper {
    font-size: 2rem;
    animation: partyPopperBounce 2s ease-in-out infinite;
    display: inline-block;
}

@keyframes partyPopperBounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    10% {
        transform: translateY(-10px) rotate(-5deg);
    }
    30% {
        transform: translateY(-5px) rotate(5deg);
    }
    40% {
        transform: translateY(-15px) rotate(-3deg);
    }
    60% {
        transform: translateY(-8px) rotate(3deg);
    }
    70% {
        transform: translateY(-12px) rotate(-2deg);
    }
    90% {
        transform: translateY(-6px) rotate(2deg);
    }
}

.welcome-message {
    color: #666;
    font-size: 16px;
    margin-bottom: 40px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.welcome-next-btn {
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.welcome-next-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

@keyframes welcomeBounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-20px);
    }
    60% {
        transform: translateY(-10px);
    }
}

@keyframes iconPulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
}

.modal-backdrop {
    background-color: rgba(0, 123, 255, 0.5) !important;
}

/* Ensure modal appears on top of all content */
#welcome-modal {
    z-index: 99999 !important;
}

#welcome-modal .modal-backdrop {
    z-index: 99998 !important;
}

#welcome-modal .modal-dialog {
    z-index: 100000 !important;
}

/* Ensure modal doesn't show loading states */
#welcome-modal .modal-dialog {
    display: block !important;
}

#welcome-modal.show .modal-dialog {
    animation: modalFadeInUp 0.3s ease-out;
}

@keyframes modalFadeInUp {
    from {
        opacity: 0;
        transform: translate(0, -50px);
    }
    to {
        opacity: 1;
        transform: translate(0, 0);
    }
}

/* Hide any loading spinners or indicators */
#welcome-modal .fa-spinner,
#welcome-modal .loading-icon,
#welcome-modal .spinner,
#welcome-modal .table-loading,
#welcome-modal img[src*="table-loading"],
#welcome-modal .loading-overlay {
    display: none !important;
}

/* Hide table loading image specifically */
#welcome-modal img[src*="table-loading.png"] {
    display: none !important;
}

/* Ensure modal content is visible */
#welcome-modal .modal-content {
    opacity: 1 !important;
    visibility: visible !important;
}

/* Prevent any loading states from parent */
#welcome-modal {
    position: fixed !important;
}

/* Ensure no background loading elements interfere */
#welcome-modal::before,
#welcome-modal::after {
    display: none !important;
}
</style>

<script>
$(document).ready(function() {
    // Remove any loading states that might be applied globally
    $('#welcome-modal').removeClass('loading').find('.modal-dialog').removeClass('loading');

    // Show welcome popup with proper initialization
    setTimeout(function() {
        $('#welcome-modal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });

        // Force remove any loading indicators after modal is shown
        setTimeout(function() {
            $('#welcome-modal .fa-spinner, #welcome-modal .loading-icon, #welcome-modal .spinner, #welcome-modal .table-loading').hide();
            $('#welcome-modal img[src*="table-loading"]').hide();
        }, 100);
    }, 500); // Small delay to ensure DOM is fully loaded
});

function dismissWelcomePopup() {
    // Hide modal with animation
    $('#welcome-modal').modal('hide');

    // Redirect to forced password change screen after a short delay
    setTimeout(function() {
        window.location.href = admin_url + 'staff/force_password_change';
    }, 500);
}

// Ensure modal is properly shown when shown event is triggered
$('#welcome-modal').on('shown.bs.modal', function () {
    console.log('Welcome modal shown');
    // Double-check that no loading indicators are present
    $(this).find('.fa-spinner, .loading-icon, .spinner, .table-loading, img[src*="table-loading"]').hide();
});

// Handle modal hide event
$('#welcome-modal').on('hidden.bs.modal', function () {
    console.log('Welcome modal hidden');
});
</script>
<?php endif; ?>
</body>

</html>
