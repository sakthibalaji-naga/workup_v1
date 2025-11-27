<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if ($email_exist_as_staff) { ?>
                <div class="alert alert-danger">
                    Some of the departments email is used as staff member email, according to the docs, the support
                    department email must be unique email in the system, you must change the staff email or the support
                    department email in order all the features to work properly.
                </div>
                <?php } ?>
                <?php
             if (total_rows('departments', 'password != \'\' AND email LIKE "%gmail.com%"') > 0) { ?>
                <div class="alert alert-warning">
                    <p class="bold">
                        Starting from May 30, 2022, Google will no longer support sign in to your Google Account using
                        your email/username and account password.
                    </p>
                    <p>
                        If you are using your Google Account password to connect to SMTP, it's highly recommended to
                        <span class="bold">update your password with an App Password</span> to avoid any email sending
                        disruptions, find more information on how to generate App Password for your Google Account at
                        the following link: <a href="https://support.google.com/accounts/answer/185833?hl=en"
                            class="alert-link">
                            https://support.google.com/accounts/answer/185833?hl=en
                        </a>
                    </p>
                </div>
                <?php
             }
?>

                <div class="tw-mb-2">
                    <a href="#" onclick="new_department(); return false;" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?= _l('new_department'); ?>
                    </a>
                    <a href="<?php echo admin_url('departments/bulk_upload'); ?>" class="btn btn-info">
                        <i class="fa fa-upload tw-mr-1"></i>
                        <?php echo _l('bulk_upload_departments'); ?>
                    </a>
                </div>

                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('id'),
                            _l('department_list_name'),
                            'Parent Department',
                            'Department HOD Name',
                            'Divisions',
                            _l('options'),
                        ], 'departments'); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="department" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?= form_open(admin_url('departments/department'), ['id' => 'department-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span
                        class="edit-title"><?= _l('edit_department'); ?></span>
                    <span
                        class="add-title"><?= _l('new_department'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                        <input type="text" class="fake-autofill-field" name="fakeusernameremembered" value=''
                            tabindex="-1" />
                        <input type="password" class="fake-autofill-field" name="fakepasswordremembered" value=''
                            tabindex="-1" />
                        <?= render_input('name', 'department_name'); ?>
                        <?php
                            // Show only departments that don't have a parent (top-level) in Parent Department dropdown
                            $parentDeptOptions = array_values(array_filter($departments, function ($d) {
                                return empty($d['parent_department']) || (int) $d['parent_department'] === 0;
                            }));
                        ?>
                        <?= render_select('parent_department', $parentDeptOptions, ['departmentid', 'name'], 'Parent Department', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
                        <?= render_select('divisionid', $divisions ?? [], ['divisionid','name'], 'Division', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
                        <?= render_select('responsible_staff', [], ['staffid','name'], 'HOD Name', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'data-live-search' => true, 'disabled' => true]); ?>
                        
                        <?php if (get_option('google_api_key') != '') { ?>
                        <?= render_input('calendar_id', 'department_calendar_id'); ?>
                        <?php } ?>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="hidefromclient" id="hidefromclient">
                            <label
                                for="hidefromclient"><?= _l('department_hide_from_client'); ?></label>
                        </div>
                        <hr />
                        <?= render_input('email', 'department_email', '', 'email'); ?>
                        <br />
                        <h4><?= _l('email_to_ticket_config'); ?>
                        </h4>
                        <br />
                        <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip"
                            data-title="<?= _l('department_username_help'); ?>"></i>
                        <?= render_input('imap_username', 'department_username'); ?>
                        <?= render_input('host', 'dept_imap_host'); ?>
                        <?= render_input('password', 'dept_email_password', '', 'password'); ?>
                        <div class="form-group">
                            <label
                                for="encryption"><?= _l('dept_encryption'); ?></label><br />
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="encryption" value="tls" id="tls">
                                <label for="tls">TLS</label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="encryption" value="ssl" id="ssl">
                                <label for="ssl">SSL</label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" name="encryption" value="" id="no_enc" checked>
                                <label
                                    for="no_enc"><?= _l('dept_email_no_encryption'); ?></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="folder" class="control-label">
                                <?= _l('imap_folder'); ?>
                                <a href="#" onclick="retrieve_imap_department_folders(); return false;">
                                    <i class="fa fa-refresh hidden" id="folders-loader"></i>
                                    <?= _l('retrieve_folders'); ?>
                                </a>
                            </label>
                            <select name="folder" class="form-control selectpicker" id="folder"></select>
                        </div>
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="delete_after_import" id="delete_after_import">
                                <label
                                    for="delete_after_import"><?= _l('delete_mail_after_import'); ?>
                            </div>
                            <hr />
                            <button onclick="test_dep_imap_connection(); return false;"
                                class="btn btn-default"><?= _l('leads_email_integration_test_connection'); ?></button>
                        </div>
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
<?php init_tail(); ?>
<script>
    $(function() {
        // Make Divisions (4) and Options (5) non-sortable/searchable
        initDataTable('.table-departments', window.location.href, [4,5], [4,5], undefined, [1, 'asc']);
        appValidateForm($('form'), {
            name: 'required',
            email: {
                email: true,
                remote: {
                    url: admin_url + "departments/email_exists",
                    type: 'post',
                    data: {
                        email: function() {
                            return $('input[name="email"]').val();
                        },
                        departmentid: function() {
                            return $('input[name="id"]').val();
                        }
                    }
                }
            }
        }, manage_departments);
        $('#department').on('hidden.bs.modal', function(event) {
            $('#additional').html('');
            $('#department input[type="text"]').val('');
            $('#department input[type="email"]').val('');
            $('#department select[name="parent_department"]').selectpicker('val', '');
            $('#department select[name="divisionid"]').selectpicker('val', '');
            var $resp = $('#department select[name="responsible_staff"]');
            $resp.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh');
            $('input[name="delete_after_import"]').prop('checked', false);
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });

        // Hide Email-to-Ticket (IMAP) configuration section in the New/Edit Department modal
        var $modal = $('#department');
        // Hide Department Email field
        $modal.find('input[name="email"]').closest('.form-group').hide();
        // Hide IMAP username and host
        $modal.find('input[name="imap_username"]').closest('.form-group').hide();
        $modal.find('input[name="host"]').closest('.form-group').hide();
        // Hide IMAP password field
        $modal.find('input[name="password"]').closest('.form-group').hide();
        // Hide encryption radios group
        $modal.find('input[name="encryption"]').first().closest('.form-group').hide();
        // Hide folder selector
        $modal.find('#folder').closest('.form-group').hide();
        // Hide test connection button group
        $modal.find('button[onclick*="test_dep_imap_connection"]').closest('.form-group').hide();
        // Hide any visible headings that mention Email/IMAP (e.g., "Email to ticket configuration")
        $modal.find('h3:contains("Email"), h3:contains("IMAP"), h4:contains("Email"), h4:contains("IMAP"), h5:contains("Email"), h5:contains("IMAP")').hide();
    });

    function manage_departments(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
            }
            if (response.email_exist_as_staff == true) {
                window.location.reload();
            }
            $('.table-departments').DataTable().ajax.reload();
            $('#department').modal('hide');
        }).fail(function(data) {
            var error = JSON.parse(data.responseText);
            alert_float('danger', error.message);
        });
        return false;
    }

    function new_department() {
        $('#department').modal('show');
        $('.edit-title').addClass('hide');
        $('#folder').html('');
        $('#folder').selectpicker('refresh');
        $('#department select[name="parent_department"]').selectpicker('val', '');
        $('#department select[name="divisionid"]').selectpicker('val', '');
    }

    function edit_department(invoker, id) {
        var hide_from_client = $(invoker).data('hide-from-client');
        var delete_after_import = $(invoker).data('delete-after-import');
        var folder = $(invoker).data('folder');
        $('input[name="hidefromclient"]').prop('checked', hide_from_client == 1);
        $('input[name="delete_after_import"]').prop('checked', delete_after_import == 1);

        var encryption = $(invoker).data('encryption');
        var input_enc_selector = encryption == '' ? '#no_enc' : '#' + encryption;
        $(input_enc_selector).prop('checked', true);

        $('#folder').html('<option value="' + folder + '" selected>' + folder + '</option>');
        $('#folder').selectpicker('refresh');

        $('#additional').append(hidden_input('id', id));

        $('#department input[name="name"]').val($(invoker).data('name'));
        $('#department input[name="email"]').val($(invoker).data('email'));
        $('#department input[name="calendar_id"]').val($(invoker).data('calendar-id'));
        $('#department input[name="password"]').val($(invoker).data('password'));
        $('#department input[name="imap_username"]').val($(invoker).data('imap_username'));
        $('#department input[name="host"]').val($(invoker).data('host'));
        $('#department select[name="parent_department"]').selectpicker('val', $(invoker).data('parent-department'));
        var primaryDivisionId = ($(invoker).data('divisionid') || '').toString();
        if (!primaryDivisionId) {
            var divsCsv = ($(invoker).data('divisions') || '').toString();
            if (divsCsv) { primaryDivisionId = divsCsv.split(',')[0]; }
        }
        $('#department select[name="divisionid"]').selectpicker('val', primaryDivisionId || '');
        var responsibleStaffId = ($(invoker).data('responsible-staff') || '').toString();
        // After divisions are set, fetch staff and preselect
        retrieve_department_staff_by_divisions(responsibleStaffId);
        $('#department').modal('show');
        $('.add-title').addClass('hide');
    }

    function retrieve_department_staff_by_divisions(selectedId) {
        var divId = $('#department select[name="divisionid"]').selectpicker('val');
        var $resp = $('#department select[name="responsible_staff"]');
        if (!divId) {
            $resp.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh');
            return;
        }
        $.post(admin_url + 'departments/staff_by_divisions', { divisions: [divId] }).done(function(resp){
            var data = [];
            try { data = JSON.parse(resp) } catch(e) { data = []; }
            var options = '<option value=""></option>';
            if (Array.isArray(data) && data.length) {
                data.forEach(function(s){ options += '<option value="'+ (s.staffid||'') +'">'+ (s.name||'') +'</option>'; });
                $resp.prop('disabled', false);
            } else {
                $resp.prop('disabled', true);
            }
            $resp.html(options).selectpicker('refresh');
            if (selectedId) {
                $resp.selectpicker('val', selectedId);
            }
        }).fail(function(){ $resp.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh'); });
    }

    // Refresh staff on divisions change
    $(document).on('changed.bs.select', '#department select[name="divisionid"]', function(){
        retrieve_department_staff_by_divisions();
    });

    function retrieve_imap_department_folders() {
        retrieve_imap_folders(admin_url + 'departments/folders', {
            email: $('input[name="email"]').val(),
            password: $('input[name="password"]').val(),
            host: $('input[name="host"]').val(),
            username: $('input[name="imap_username"]').val(),
            encryption: $('input[name="encryption"]:checked').val()
        })
    }

    function test_dep_imap_connection() {
        $.post(admin_url + 'departments/test_imap_connection', {
                email: $('input[name="email"]').val(),
                password: $('input[name="password"]').val(),
                host: $('input[name="host"]').val(),
                username: $('input[name="imap_username"]').val(),
                encryption: $('input[name="encryption"]:checked').val(),
                folder: $('#folder').selectpicker('val'),
            })
            .done(function(response) {
                response = JSON.parse(response);
                alert_float(response.alert_type, response.message);
            });
    }

    // (Org chart code removed)
</script>
</body>

</html>
