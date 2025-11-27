<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2">
                    <a href="#" onclick="new_application(); return false;" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        New Application
                    </a>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        $headers = [
                            _l('id'),
                            'Application Name',
                            _l('options'),
                        ];

                        // Check if active column exists
                        $CI_temp = &get_instance();
                        $CI_temp->load->database();
                        if ($CI_temp->db->field_exists('active', db_prefix() . 'applications')) {
                            array_splice($headers, 2, 0, _l('status')); // Insert status before options
                        }

                        // Check if position column exists
                        if ($CI_temp->db->field_exists('position', db_prefix() . 'applications')) {
                            array_splice($headers, 2, 0, 'Position'); // Insert position before options
                        }

                        render_datatable($headers, 'application');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="application" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?= form_open(admin_url('application/application'), ['id' => 'application-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title">Edit Application</span>
                    <span class="add-title">New Application</span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?= render_input('name', 'Application Name'); ?>
                        <?php
                        $position_options   = [];
                        $position_options[] = ['id' => 0, 'name' => 'Last (Default)'];
                        for ($i = 1; $i <= 9; $i++) {
                            $position_options[] = ['id' => $i, 'name' => (string) $i];
                        }

                        echo render_select(
                            'position',
                            $position_options,
                            ['id', 'name'],
                            'Position/Order',
                            '0',
                            ['data-none-selected-text' => 'Last (Default)', 'required' => 'required'],
                            [],
                            '',
                            'required',
                            false
                        );
                        ?>
                        <?php // Department and Service columns removed ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                    data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit"
                    class="btn btn-primary"><?= _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?= form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Application Deactivation Confirmation Modal -->
<div class="modal fade" id="deactivate-confirmation-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Confirm Application Deactivation</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Warning!</strong> <span class="deactivation-warning-copy">Deactivating this application will affect the following linked services. Any that are currently active will also be marked as inactive.</span>
                </div>
                <div id="linked-services-list">
                    <!-- Services will be populated here -->
                </div>
                <p>Are you sure you want to deactivate the application "<strong id="application-name"></strong>"?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-deactivate-btn">Deactivate Anyway</button>
            </div>
        </div>
    </div>
</div>


<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-application', window.location.href, undefined, undefined, undefined, [1, 'asc']);
        appValidateForm($('form'), {
            name: 'required',
            position: 'required'
        }, manage_application);
        $('#application').on('hidden.bs.modal', function(event) {
            $('#additional').html('');
            $('#application input[type="text"]').val('');
            var $positionSelect = $('#application select[name="position"]');
            $positionSelect.val('0');
            $positionSelect.selectpicker('refresh');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });
    });

    function manage_application(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
            }
            $('.table-application').DataTable().ajax.reload();
            $('#application').modal('hide');
        }).fail(function(data) {
            var error = JSON.parse(data.responseText);
            alert_float('danger', error.message);
        });
        return false;
    }

    function new_application() {
        $('#additional').html('');
        var $positionSelect = $('#application select[name="position"]');
        $positionSelect.val('0');
        $positionSelect.selectpicker('refresh');
        $('#application').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_application(invoker, id) {
        $('#additional').append(hidden_input('id', id));
        $('#application input[name="name"]').val($(invoker).data('name'));
        var $positionSelect = $('#application select[name="position"]');
        var positionValue = $(invoker).data('position');
        if (typeof positionValue === 'undefined' || positionValue === null || positionValue === '') {
            positionValue = '0';
        }
        $positionSelect.val(positionValue);
        $positionSelect.selectpicker('refresh');
        $('#application').modal('show');
        $('.add-title').addClass('hide');
    }

    function escapeHtml(value) {
        if (value === undefined || value === null) {
            return '';
        }
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(value));
        return div.innerHTML;
    }

    function toggle_application_status(id) {
        $.ajax({
            url: admin_url + 'application/toggle_status/' + id,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.show_confirmation) {
                    // Show confirmation modal with linked services
                    show_deactivation_confirmation(
                        id,
                        response.application_name,
                        response.linked_services,
                        response.active_linked_services
                    );
                } else if (response.success) {
                    alert_float('success', response.message);
                    $('.table-application').DataTable().ajax.reload();
                } else {
                    alert_float('danger', response.message || 'Failed to update application status');
                }
            },
            error: function(xhr, status, error) {
                alert_float('danger', 'An error occurred while updating application status');
            }
        });
    }

    function show_deactivation_confirmation(applicationId, applicationName, linkedServices, activeLinkedCount) {
        $('#application-name').text(applicationName || '');

        var servicesArray = Array.isArray(linkedServices) ? linkedServices : [];
        var computedActiveCount = 0;

        var servicesHtml = '';
        if (servicesArray.length === 0) {
            servicesHtml = '<div class="alert alert-info m0">No linked services were detected for this application.</div>';
        } else {
            servicesHtml = '<ul class="list-group">';
            servicesArray.forEach(function(service) {
                var serviceName = escapeHtml(service.service_name || service.name || '');
                var statusBadge = '';
                var isActiveFlag = null;

                if (Object.prototype.hasOwnProperty.call(service, 'active')) {
                    var normalized = parseInt(service.active, 10);
                    if (!Number.isNaN(normalized)) {
                        isActiveFlag = normalized === 1;
                        if (isActiveFlag) {
                            computedActiveCount += 1;
                        }
                    }

                    var badgeClass = isActiveFlag ? 'label label-success' : 'label label-default';
                    var badgeText = isActiveFlag ? 'Active' : 'Inactive';
                    statusBadge = '<span class="' + badgeClass + '">' + badgeText + '</span>';
                }

                servicesHtml += '<li class="list-group-item tw-flex tw-items-center tw-justify-between">' +
                    '<span>' + serviceName + '</span>' +
                    (statusBadge ? statusBadge : '') +
                    '</li>';
            });
            servicesHtml += '</ul>';
        }

        if (typeof activeLinkedCount === 'number' && activeLinkedCount >= 0) {
            computedActiveCount = activeLinkedCount;
        }

        $('#linked-services-list').html(servicesHtml);

        var warningCopy;
        if (servicesArray.length === 0) {
            warningCopy = 'No linked services were detected. Proceeding will only deactivate the application itself.';
        } else if (computedActiveCount > 0) {
            warningCopy = 'The services marked as Active will be set to Inactive if you proceed.';
        } else {
            warningCopy = 'All linked services are already inactive. Confirming will keep them inactive.';
        }
        $('.deactivation-warning-copy').text(warningCopy);

        // Set up confirmation button
        $('#confirm-deactivate-btn').off('click').on('click', function() {
            force_deactivate_application(applicationId);
        });

        $('#deactivate-confirmation-modal').modal('show');
    }

    function force_deactivate_application(id) {
        $.ajax({
            url: admin_url + 'application/force_deactivate/' + id,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $('#deactivate-confirmation-modal').modal('hide');
                if (response.success) {
                    alert_float('success', response.message);
                    $('.table-application').DataTable().ajax.reload();
                } else {
                    alert_float('danger', response.message || 'Failed to deactivate application');
                }
            },
            error: function() {
                $('#deactivate-confirmation-modal').modal('hide');
                alert_float('danger', 'An error occurred while deactivating the application');
            }
        });
    }
</script>
</body>

</html>
