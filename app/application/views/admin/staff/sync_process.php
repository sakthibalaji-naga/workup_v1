<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="mb-1">
                            <i class="fa fa-sync text-success mr-1"></i>
                            <?php echo _l('staff_sync_settings_title'); ?>
                        </h4>
                        <p class="text-muted mb-4"><?php echo _l('staff_sync_dashboard_description'); ?></p>
                        <?php echo form_open(admin_url('staff/sync_dashboard'), ['id' => 'zinghr-sync-form']); ?>
                        <div class="form-group">
                            <label for="subscription_name"><?php echo _l('staff_sync_subscription_name'); ?></label>
                            <input type="text" name="subscription_name" id="subscription_name" class="form-control"
                                   value="<?php echo html_escape($settings['subscription_name']); ?>" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="token"><?php echo _l('staff_sync_token'); ?></label>
                            <textarea name="token" id="token" rows="2" class="form-control" autocomplete="off"><?php echo html_escape($settings['token']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from_date"><?php echo _l('staff_sync_from_date'); ?></label>
                                    <input type="date" name="from_date" id="from_date" class="form-control"
                                           value="<?php echo html_escape($settings['from_date']); ?>">
                                    <small class="text-muted"><?php echo _l('staff_sync_date_help'); ?></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="to_date"><?php echo _l('staff_sync_to_date'); ?></label>
                                    <input type="date" name="to_date" id="to_date" class="form-control"
                                           value="<?php echo html_escape($settings['to_date']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center">
                            <button type="submit" class="btn btn-primary mr-2 mb-2">
                                <i class="fa fa-save mr-1"></i><?php echo _l('staff_sync_save_settings'); ?>
                            </button>
                            <button type="button" class="btn btn-success mb-2" id="run-sync-btn" onclick="runZingHrSync(this);">
                                <i class="fa fa-play mr-1"></i><?php echo _l('staff_sync_run_now'); ?>
                            </button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-body">
                        <h5 class="mb-2"><?php echo _l('staff_sync_summary_title'); ?></h5>
                        <?php if (!empty($settings['last_run'])) { ?>
                            <p class="mb-2">
                                <strong><?php echo _l('staff_sync_last_run'); ?>:</strong>
                                <?php echo _dt($settings['last_run']); ?>
                            </p>
                        <?php } else { ?>
                            <p class="text-muted mb-2"><?php echo _l('staff_sync_never_ran'); ?></p>
                        <?php } ?>
                        <div id="sync-result" class="bg-light border p-3 rounded">
                            <p class="text-muted mb-0"><?php echo _l('staff_sync_result_placeholder'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <h5 class="mb-2"><?php echo _l('staff_sync_api_details'); ?></h5>
                        <p class="mb-1 small text-muted"><?php echo _l('staff_sync_api_endpoint'); ?>:</p>
                        <code class="d-block mb-3"><?php echo html_escape($zinghr_endpoint); ?></code>
                        <p class="text-muted small mb-0"><?php echo _l('staff_sync_postman_hint'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
function runZingHrSync(button) {
    var $btn = $(button);
    var originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i><?php echo _l('staff_sync_processing'); ?>');

    var payload = {
        subscription_name: $('#subscription_name').val(),
        token: $('#token').val(),
        from_date: $('#from_date').val(),
        to_date: $('#to_date').val()
    };

    $.post(admin_url + 'staff/run_zinghr_sync', payload, function(response) {
        if (response && response.success) {
            alert_float('success', response.message || '<?php echo _l('staff_sync_run_completed'); ?>');
            renderSyncResult(response.stats || {});
        } else {
            var message = response && response.message ? response.message : '<?php echo _l('staff_sync_error_generic'); ?>';
            alert_float('danger', message);
        }
    }, 'json').fail(function() {
        alert_float('danger', '<?php echo _l('staff_sync_error_generic'); ?>');
    }).always(function() {
        $btn.prop('disabled', false).html(originalText);
    });
}

function renderSyncResult(stats) {
    if (!stats) {
        $('#sync-result').html('<p class="text-muted mb-0"><?php echo _l('staff_sync_result_placeholder'); ?></p>');
        return;
    }

    var template = `
        <div class="row">
            <div class="col-sm-6">
                <p class="mb-1"><strong><?php echo _l('staff_sync_result_divisions'); ?>:</strong> ${stats.divisions_created || 0}</p>
                <p class="mb-1"><strong><?php echo _l('staff_sync_result_departments_created'); ?>:</strong> ${stats.departments_created || 0}</p>
                <p class="mb-1"><strong><?php echo _l('staff_sync_result_departments_updated'); ?>:</strong> ${stats.departments_updated || 0}</p>
            </div>
            <div class="col-sm-6">
                <p class="mb-1"><strong><?php echo _l('staff_sync_result_staff_created'); ?>:</strong> ${stats.staff_created || 0}</p>
                <p class="mb-1"><strong><?php echo _l('staff_sync_result_staff_updated'); ?>:</strong> ${stats.staff_updated || 0}</p>
                <p class="mb-1"><strong><?php echo _l('staff_sync_result_staff_inactivated'); ?>:</strong> ${stats.staff_inactivated || 0}</p>
                <p class="mb-0"><strong><?php echo _l('staff_sync_result_staff_reactivated'); ?>:</strong> ${stats.staff_reactivated || 0}</p>
            </div>
        </div>
    `;

    if (stats.errors && stats.errors.length) {
        template += '<hr><p class="text-danger mb-1"><strong><?php echo _l('staff_sync_result_errors'); ?>:</strong></p><ul class="text-danger small">';
        stats.errors.forEach(function(error) {
            template += '<li>' + error + '</li>';
        });
        template += '</ul>';
    }

    $('#sync-result').html(template);
}
</script>
</body>
</html>
