<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?= form_open_multipart($this->uri->uri_string(), ['id' => 'new_ticket_form']); ?>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="tw-flex tw-items-center tw-mb-2">
                    <h4 class="tw-my-0 tw-font-bold tw-text-lg tw-text-neutral-700 tw-mr-4">
                        <?= _l('clients_single_ticket_information_heading'); ?>
                    </h4>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-3">
                                <?= render_select('divisionid', $divisions ?? [], ['divisionid','name'], 'Division', '', ['required' => 'true', 'data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_select('department', [], ['departmentid', 'name'], 'ticket_settings_departments', '', ['required' => 'true', 'disabled' => true, 'data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_select('sub_department', [], ['departmentid', 'name'], 'Sub Department', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'disabled' => true, 'required' => true]); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_select('application_id', $applications, ['id', 'name'], 'Application / Module', '', ['required' => 'true', 'disabled' => true, 'data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?php if (get_option('services') == 1) { ?>
                                    <?= render_select('service', [], ['serviceid', 'name'], 'ticket_settings_service', '', ['required' => 'true', 'disabled' => true]); ?>
                                <?php } ?>
                            </div>
                            <div class="col-md-3">
                                <?php $priorities['callback_translate'] = 'ticket_priority_translate';
                                echo render_select('priority', $priorities, ['priorityid', 'name'], 'ticket_settings_priority', hooks()->apply_filters('new_ticket_priority_selected', 2), ['required' => 'true']); ?>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group select-placeholder">
                                    <label for="assigned" class="control-label"><?= _l('ticket_settings_assign_to'); ?></label>
                                    <select name="assigned" id="assigned" class="form-control selectpicker" data-live-search="true" data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>" data-width="100%" disabled required>
                                        <option value=""><?= _l('ticket_settings_none_assigned'); ?></option>
                                        <?php foreach ($staff as $member) { ?>
                                            <option value="<?= e($member['staffid']); ?>"><?= e($member['firstname'] . ' ' . $member['lastname'] . (isset($member['staff_emp_code']) && !empty($member['staff_emp_code']) ? ' (' . $member['staff_emp_code'] . ')' : '')); ?></option>
                                        <?php } ?>
                                    </select>
                                    <input type="hidden" name="assigned" id="assigned_hidden" value="">
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <?= render_input('subject', 'ticket_settings_subject', '', 'text', ['required' => 'true']); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="tw-mt-0 tw-font-bold tw-text-base tw-text-neutral-700">
                                    <?= _l('ticket_add_body'); ?>
                                </h4>
                                <?= render_textarea('message', '', '', ['required' => 'true'], [], '', 'tinymce'); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="attachment" class="control-label"><?= _l('ticket_add_attachments'); ?></label>
                                    <div class="input-group">
                                        <input type="file" multiple="multiple"
                                            extension="<?= str_replace(['.', ' '], '', get_option('ticket_attachments_file_extensions')); ?>"
                                            filesize="<?= file_upload_max_size(); ?>"
                                            class="form-control" name="attachments[0]"
                                            accept="<?= get_ticket_form_accepted_mimes(); ?>">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default add_more_attachments"
                                                data-max="<?= get_option('maximum_allowed_ticket_attachments'); ?>"
                                                type="button"><i class="fa fa-plus"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <?php echo render_input('approx_response_time','Approx. Response Time','','text',['readonly'=>true]); ?>
                            </div>
                            <div class="col-md-3 col-12" style="padding: 19px;">
                                <button type="submit" data-form="#new_ticket_form" autocomplete="off" data-loading-text="<?= _l('wait_text'); ?>" class="btn btn-primary pull-right col-12"><?= _l('open_ticket'); ?></button>
                            </div>
                        </div>
                        </div>

                        
                    </div>
                </div>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
    <div class="tw-py-10"></div>
    <?php $this->load->view('admin/tickets/services/service'); ?>
    <?php init_tail(); ?>
    <?php hooks()->do_action('new_ticket_admin_page_loaded'); ?>
    <script>
        // Departments are loaded per selected division; keep empty until selection
        $(function() {

            init_ajax_search('contact', '#contactid.ajax-search', {
                tickets_contacts: true,
                contact_userid: function() {
                    // when ticket is directly linked to project only search project client id contacts
                    var uid = $('select[data-auto-project="true"]').attr('data-project-userid');
                    if (uid) {
                        return uid;
                    } else {
                        return '';
                    }
                }
            });

            validate_new_ticket_form();

            function clearSubDepartments() {
                var $sub = $('select[name="sub_department"]');
                $sub.html('');
                $sub.prop('disabled', true);
                $sub.selectpicker('refresh');
                // When clearing sub-departments, rebuild services based on department only
                var depVal = $('select[name="department"]').selectpicker('val') || '';
                updateServicesForDepartment(depVal);
            }

            function loadSubDepartments(parentId, selectedId) {
                if (!parentId) { clearSubDepartments(); return; }
                $.post(admin_url + 'tickets/sub_departments', { parent_id: parentId })
                    .done(function(resp) {
                        var data = [];
                        try { data = JSON.parse(resp); } catch(e) { data = []; }
                        var $sub = $('select[name="sub_department"]');
                        var options = '<option value=""></option>';
                        if (Array.isArray(data) && data.length) {
                            data.forEach(function(d){
                                options += '<option value="'+ (d.departmentid || '') +'">'+ (d.name || '') +'</option>';
                            });
                            $sub.prop('disabled', false);
                        } else {
                            $sub.prop('disabled', true);
                        }
                        $sub.html(options);
                        $sub.selectpicker('refresh');
                        if (selectedId) {
                            $sub.selectpicker('val', selectedId);
                            // When sub is auto-selected, update assignee list and services based on sub only
                            updateAssignedByDepartment(selectedId, false);
                            updateServicesForSubDepartment(selectedId, (typeof MAPPING_FROM_SERVICE !== 'undefined' && MAPPING_FROM_SERVICE === true));
                            // Mapping flow complete; allow subsequent changes to reset if needed
                            if (typeof MAPPING_FROM_SERVICE !== 'undefined') { window.MAPPING_FROM_SERVICE = false; }
                        } else {
                            // No sub selected, include children so department + its subs are shown
                            updateAssignedByDepartment(parentId, true);
                            updateServicesForDepartment(parentId, (typeof MAPPING_FROM_SERVICE !== 'undefined' && MAPPING_FROM_SERVICE === true));
                            if (typeof MAPPING_FROM_SERVICE !== 'undefined') { window.MAPPING_FROM_SERVICE = false; }
                        }
                    })
                    .fail(function(){ clearSubDepartments(); });
            }

            var currentStaffId = '<?= get_staff_user_id(); ?>';
            var ASSIGN_LOCKED_TO_SERVICE = true;
            window.MAPPING_FROM_SERVICE = false;

            function setAssignedByService(staffId){
                var $assigned = $('#assigned');
                var $hidden   = $('#assigned_hidden');
                if (staffId) {
                    $assigned.selectpicker('val', String(staffId));
                    $hidden.val(String(staffId));
                } else {
                    $assigned.selectpicker('val', '');
                    $hidden.val('');
                }
                // ensure disabled state in UI
                $assigned.prop('disabled', true).selectpicker('refresh');
            }

            function updateAssignedByDepartment(depId, includeChildren) {
                if (ASSIGN_LOCKED_TO_SERVICE) { return; }
                var $assigned = $('#assigned');
                if (!depId) {
                    // Clear options (keep placeholder)
                    $assigned.find('option').not(':first').remove();
                    $assigned.selectpicker('refresh');
                    return;
                }
                $.post(admin_url + 'tickets/staff_by_department', { department: depId, include_children: includeChildren ? 1 : 0 })
                    .done(function(resp){
                        var list = [];
                        try { list = JSON.parse(resp); } catch(e){ list = []; }
                        var options = '';
                        list.forEach(function(s){
                            var label = (s.name || '');
                            if (s.emp_code) { label += ' (' + s.emp_code + ')'; }
                            options += '<option value="'+ s.id +'">'+ label +'</option>';
                        });
                        $assigned.find('option').not(':first').remove();
                        $assigned.append(options);
                        $assigned.selectpicker('refresh');
                    })
                    .fail(function(){ /* ignore */ });
            }

            // Build service options from server-side data; start empty/disabled until department/sub selected
            <?php
                $servicesForJs = array_map(function($s){
                    return [
                        'id' => (string)$s['serviceid'],
                        'name' => $s['name'],
                        'divisionid' => isset($s['divisionid']) ? (string)$s['divisionid'] : '',
                        'departmentid' => isset($s['departmentid']) ? (string)$s['departmentid'] : '',
                        'sub_department' => isset($s['sub_department']) ? (string)$s['sub_department'] : '',
                        'responsible' => isset($s['responsible']) ? (string)$s['responsible'] : '',
                        'applicationid' => isset($s['applicationid']) ? (string)$s['applicationid'] : '',
                        'response_time_value' => isset($s['response_time_value']) ? (string)$s['response_time_value'] : '',
                        'response_time_unit' => isset($s['response_time_unit']) ? (string)$s['response_time_unit'] : '',
                        'resolution_time_value' => isset($s['resolution_time_value']) ? (string)$s['resolution_time_value'] : '',
                        'resolution_time_unit' => isset($s['resolution_time_unit']) ? (string)$s['resolution_time_unit'] : '',
                    ];
                }, is_array($services) ? $services : []);
            ?>
            var ALL_SERVICE_OPTIONS = <?= json_encode($servicesForJs); ?>;

            function rebuildServiceOptions(filter, preserveSelection){
                var $svc = $('#service');
                // Always remember current selection; higher-level callers decide if they want to clear
                var previous = $svc.selectpicker('val') || '';
                var opts = '<option value=""></option>';
                var count = 0;
                ALL_SERVICE_OPTIONS.forEach(function(s){
                    // When filtering by department/sub, ignore division constraint to avoid excluding valid services
                    if (!filter.department && !filter.sub) {
                        if (filter.division && String(s.divisionid || '') !== String(filter.division)) return;
                    }
                    if (filter.department && String(s.departmentid || '') !== String(filter.department)) return;
                    if (filter.sub && String(s.sub_department || '') !== String(filter.sub)) return;
                    if (filter.application && String(s.applicationid || '') !== String(filter.application)) return;
                    var attrs = '';
                    if (s.divisionid) attrs += ' data-divisionid="'+ s.divisionid +'"';
                    if (s.departmentid) attrs += ' data-departmentid="'+ s.departmentid +'"';
                    if (s.sub_department) attrs += ' data-sub-department="'+ s.sub_department +'" data-sub_department="'+ s.sub_department +'"';
                    if (s.responsible) attrs += ' data-responsible="'+ s.responsible +'"';
                    if (s.applicationid) attrs += ' data-applicationid="'+ s.applicationid +'"';
                    opts += '<option value="'+ s.id +'"'+ attrs +'>'+ s.name +'</option>';
                    count++;
                });
                $svc.html(opts);
                // Enable only when there are matching services after department/sub filter
                $svc.prop('disabled', count === 0);
                $svc.selectpicker('refresh');
                // Preserve selection when still valid; explicit clears happen in callers when needed
                if (previous && $svc.find('option[value="'+previous+'"]').length){
                    window.SUPPRESS_SERVICE_CHANGE = true;
                    $svc.selectpicker('val', previous);
                    window.SUPPRESS_SERVICE_CHANGE = false;
                }
            }

            function updateServicesForDivision(divId, preserve){
                var $svc = $('#service');
                // Keep Service empty and disabled until department/sub selected
                $svc.html('<option value=""></option>');
                $svc.prop('disabled', true).selectpicker('refresh');
                pendingSubId = null;
                if (!preserve) setAssignedByService('');
            }

            function updateServicesForDepartment(depId, preserve){
                var divId = $('select[name="divisionid"]').selectpicker('val') || '';
                var appId = $('select[name="application_id"]').selectpicker('val') || '';
                rebuildServiceOptions({ division: divId, department: depId || '', sub: '', application: appId }, !!preserve);
                // Requirement: Service should be enabled only after BOTH department and sub-department are selected
                var $svc = $('#service');
                $svc.prop('disabled', true).selectpicker('refresh');
                if (!preserve){ $svc.selectpicker('val', ''); setAssignedByService(''); }
            }

            function updateServicesForSubDepartment(subId, preserve){
                var divId = $('select[name="divisionid"]').selectpicker('val') || '';
                var depId = $('select[name="department"]').selectpicker('val') || '';
                var appId = $('select[name="application_id"]').selectpicker('val') || '';
                rebuildServiceOptions({ division: divId, department: depId, sub: subId || '', application: appId }, !!preserve);
                // Enable Service only when both department and sub-department are selected
                var $svc = $('#service');
                var bothSelected = !!depId && !!subId;
                $svc.prop('disabled', !bothSelected).selectpicker('refresh');
                if (!preserve && !bothSelected){ $svc.selectpicker('val', ''); setAssignedByService(''); }
            }

            // Update Department options based on selected Division
            function updateDepartmentsForDivision(divId, preserve) {
                var $dept = $('select[name="department"]');
                var $sub  = $('select[name="sub_department"]');
                function rebuildOptions(list) {
                    var prev = preserve ? ($dept.selectpicker('val') || '') : '';
                    var opts = '<option value=""></option>';
                    list.forEach(function(d){ opts += '<option value="'+ d.departmentid +'">'+ (d.name||'') +'</option>'; });
                    $dept.html(opts);
                    // Enable only when there are departments to show
                    if (Array.isArray(list) && list.length) {
                        $dept.prop('disabled', false);
                    } else {
                        $dept.prop('disabled', true);
                    }
                    $dept.selectpicker('refresh');
                    // Clear selected department/sub and assigned list
                    $dept.selectpicker('val','');
                    $sub.html('').prop('disabled', true).selectpicker('refresh');
                    updateAssignedByDepartment(null, false);
                    // Restore previous if requested and still present
                    if (preserve && prev && $dept.find('option[value="'+prev+'"]').length) {
                        $dept.selectpicker('val', prev);
                    }
                }
                if (!divId) {
                    // No division selected: keep department empty and disabled
                    $dept.html('<option value=""></option>');
                    $dept.prop('disabled', true).selectpicker('refresh');
                    $sub.html('').prop('disabled', true).selectpicker('refresh');
                    updateAssignedByDepartment(null, false);
                    return;
                }
                $.post(admin_url + 'tickets/departments_by_division', { divisionid: divId })
                    .done(function(resp){
                        var list = [];
                        try { list = JSON.parse(resp); } catch(e){ list = []; }
                        rebuildOptions(list);
                    })
                    .fail(function(){ rebuildOptions([]); });
            }

            // bind change on department
            $('select[name="department"]').on('changed.bs.select', function(){
                var val = $(this).selectpicker('val');
                var selSub = pendingSubId; pendingSubId = null;
                loadSubDepartments(val, selSub);
                // If change is triggered by service mapping, preserve current service selection
                var preserve = typeof MAPPING_FROM_SERVICE !== 'undefined' && MAPPING_FROM_SERVICE === true;
                updateServicesForDepartment(val, preserve);
                updateApplicationsForDepartment(val, '');
            });

            // bind change on division to filter departments and services
            $('select[name="divisionid"]').on('changed.bs.select', function(){
                var divId = $(this).selectpicker('val');
                updateDepartmentsForDivision(divId, false);
                updateServicesForDivision(divId, false);
                // keep assignment locked
                setAssignedByService($('#service option:selected').data('responsible'));
                // Application/Module should be enabled only after department/sub selection
                var $app = $('select[name="application_id"]');
                $app.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh');
            });

            // bind change on sub department to filter assignees strictly by sub department
            $('select[name="sub_department"]').on('changed.bs.select', function(){
                var subVal = $(this).selectpicker('val');
                var depVal = $('select[name="department"]').selectpicker('val');
                if (subVal) {
                    updateAssignedByDepartment(subVal, false);
                    var preserveSub = typeof MAPPING_FROM_SERVICE !== 'undefined' && MAPPING_FROM_SERVICE === true;
                    updateServicesForSubDepartment(subVal, preserveSub);
                    updateApplicationsForDepartment(depVal, subVal);
                } else {
                    if (depVal) updateAssignedByDepartment(depVal, true);
                    var preserveDep = typeof MAPPING_FROM_SERVICE !== 'undefined' && MAPPING_FROM_SERVICE === true;
                    updateServicesForDepartment(depVal, preserveDep);
                    // No sub-department selected -> keep Service disabled
                    $('#service').prop('disabled', true).selectpicker('refresh');
                    updateApplicationsForDepartment(depVal, '');
                }
            });

            $('select[name="application_id"]').on('changed.bs.select', function(){
                var appId = $(this).selectpicker('val');
                var divId = $('select[name="divisionid"]').selectpicker('val') || '';
                var depId = $('select[name="department"]').selectpicker('val') || '';
                var subId = $('select[name="sub_department"]').selectpicker('val') || '';
                rebuildServiceOptions({ division: divId, department: depId, sub: subId, application: appId }, false);
            });

            function updateApplicationsForDepartment(depId, subId) {
                var $app = $('select[name="application_id"]');
                var prev = $app.selectpicker('val') || '';
                // Enable Application list as soon as a department is chosen;
                // use sub-department to narrow results if provided
                if (!depId) {
                    $app.html('<option value=""></option>');
                    $app.prop('disabled', true).selectpicker('refresh');
                    return;
                }
                $.post(admin_url + 'tickets/applications_by_department', { department_id: depId, sub_department_id: subId })
                    .done(function(resp){
                        var list = [];
                        try { list = JSON.parse(resp); } catch(e){ list = []; }
                        var opts = '<option value=""></option>';
                        list.forEach(function(a){
                            opts += '<option value="'+ a.id +'">'+ a.name +'</option>';
                        });
                        $app.html(opts);
                        if (list.length > 0) {
                            $app.prop('disabled', false);
                        } else {
                            $app.prop('disabled', true);
                        }
                        $app.selectpicker('refresh');
                        // Preserve previous application if still valid after refresh
                        if (prev && $app.find('option[value="'+prev+'"]').length) {
                            $app.selectpicker('val', prev);
                        }
                    });
            }

            // initial load if preselected values exist
            var initialDep = $('select[name="department"]').selectpicker('val');
            if (initialDep) { loadSubDepartments(initialDep); }
            var initialDiv = $('select[name="divisionid"]').selectpicker('val');
            // Always start with Application disabled until a department/sub is chosen
            (function(){
                var $app = $('select[name="application_id"]');
                $app.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh');
            })();
            if (initialDiv) {
                updateDepartmentsForDivision(initialDiv, true);
                updateServicesForDivision(initialDiv, false);
            } else {
                // Ensure department is disabled on initial load when no division
                $('select[name="department"]').prop('disabled', true).selectpicker('refresh');
            }

            // Auto-selection of department/sub-department based on service is removed
            // Service selection no longer controls department/sub-department fields
            // prevent recursion when we programmatically set the service value during preservation
            window.SUPPRESS_SERVICE_CHANGE = false;
            $('#service').on('changed.bs.select', function(){
                if (window.SUPPRESS_SERVICE_CHANGE) { return; }
                var serviceId = $(this).val();
                // Note: Removed updateResponseResolutionTime for priority-based calculation
                // Response time should be controlled by priority selection only

                // Auto-select assigned based on selected service's responsible
                if (serviceId) {
                    var selectedOption = $('#service option[value="'+serviceId+'"]');
                    var responsible = selectedOption.data('responsible');
                    if (responsible) {
                        setAssignedByService(responsible);
                    }
                }
            });

            // Priority change handler for approx response time calculation
            $('select[name="priority"]').on('changed.bs.select', function(){
                var priorityId = $(this).val();
                updateResponseTimeFromPriority(priorityId);
            });

            // Service to department mapping removed - no auto-population on load

            function updateResponseResolutionTime(serviceId) {
                var responseTime = '';
                var resolutionTime = '';

                if (serviceId) {
                    var service = ALL_SERVICE_OPTIONS.find(function(s) {
                        return s.id === serviceId;
                    });

                    if (service) {
                        var now = new Date();

                        if (service.response_time_value && service.response_time_unit) {
                            var responseDate = new Date(now);
                            var value = parseInt(service.response_time_value, 10);
                            switch (service.response_time_unit.toLowerCase()) {
                                case 'hour':
                                case 'hours':
                                    responseDate.setHours(responseDate.getHours() + value);
                                    break;
                                case 'day':
                                case 'days':
                                    responseDate.setDate(responseDate.getDate() + value);
                                    break;
                                case 'week':
                                case 'weeks':
                                    responseDate.setDate(responseDate.getDate() + value * 7);
                                    break;
                                case 'month':
                                case 'months':
                                    responseDate.setMonth(responseDate.getMonth() + value);
                                    break;
                                case 'year':
                                case 'years':
                                    responseDate.setFullYear(responseDate.getFullYear() + value);
                                    break;
                            }
                            // Format date in Y-m-d H:i:s format that strtotime() can parse
                            responseTime = responseDate.getFullYear() + '-' +
                                          ('0' + (responseDate.getMonth() + 1)).slice(-2) + '-' +
                                          ('0' + responseDate.getDate()).slice(-2) + ' ' +
                                          ('0' + responseDate.getHours()).slice(-2) + ':' +
                                          ('0' + responseDate.getMinutes()).slice(-2) + ':' +
                                          ('0' + responseDate.getSeconds()).slice(-2);
                        }

                        if (service.resolution_time_value && service.resolution_time_unit) {
                            var resolutionDate = new Date(now);
                            var value = parseInt(service.resolution_time_value, 10);
                            switch (service.resolution_time_unit.toLowerCase()) {
                                case 'hour':
                                case 'hours':
                                    resolutionDate.setHours(resolutionDate.getHours() + value);
                                    break;
                                case 'day':
                                case 'days':
                                    resolutionDate.setDate(resolutionDate.getDate() + value);
                                    break;
                                case 'week':
                                case 'weeks':
                                    resolutionDate.setDate(resolutionDate.getDate() + value * 7);
                                    break;
                                case 'month':
                                case 'months':
                                    resolutionDate.setMonth(resolutionDate.getMonth() + value);
                                    break;
                                case 'year':
                                case 'years':
                                    resolutionDate.setFullYear(resolutionDate.getFullYear() + value);
                                    break;
                            }
                            // Format date in Y-m-d H:i:s format that strtotime() can parse
                            resolutionTime = resolutionDate.getFullYear() + '-' +
                                           ('0' + (resolutionDate.getMonth() + 1)).slice(-2) + '-' +
                                           ('0' + resolutionDate.getDate()).slice(-2) + ' ' +
                                           ('0' + resolutionDate.getHours()).slice(-2) + ':' +
                                           ('0' + resolutionDate.getMinutes()).slice(-2) + ':' +
                                           ('0' + resolutionDate.getSeconds()).slice(-2);
                        }
                    }
                }

                $('input[name="approx_response_time"]').val(responseTime);
                $('input[name="approx_resolution_time"]').val(resolutionTime);
            }

            function updateResponseTimeFromPriority(priorityId) {
                var responseTime = '';

                if (priorityId) {
                    var selectedOption = $('select[name="priority"] option[value="'+priorityId+'"]');
                    var durationValue = selectedOption.data('duration-value');
                    var durationUnit = selectedOption.data('duration-unit');

                    if (durationValue && durationUnit) {
                        var now = new Date();
                        var value = parseInt(durationValue, 10);
                        var responseDate = new Date(now);

                        switch (durationUnit.toLowerCase()) {
                            case 'hrs':
                                responseDate.setHours(responseDate.getHours() + value);
                                break;
                            case 'days':
                                responseDate.setDate(responseDate.getDate() + value);
                                break;
                            case 'weeks':
                                responseDate.setDate(responseDate.getDate() + value * 7);
                                break;
                            case 'months':
                            case 'month':
                                responseDate.setMonth(responseDate.getMonth() + value);
                                break;
                            case 'years':
                            case 'year':
                                responseDate.setFullYear(responseDate.getFullYear() + value);
                                break;
                        }

                        // Format date in Y-m-d H:i:s format that strtotime() can parse
                        responseTime = responseDate.getFullYear() + '-' +
                                      ('0' + (responseDate.getMonth() + 1)).slice(-2) + '-' +
                                      ('0' + responseDate.getDate()).slice(-2) + ' ' +
                                      ('0' + responseDate.getHours()).slice(-2) + ':' +
                                      ('0' + responseDate.getMinutes()).slice(-2) + ':' +
                                      ('0' + responseDate.getSeconds()).slice(-2);
                    }
                }

                $('input[name="approx_response_time"]').val(responseTime);
            }

            <?php if (isset($project_id) || isset($contact)) { ?>
            $('body.ticket select[name="contactid"]').change();
            <?php }

            if (isset($current_user)) { ?>
                $('input[name="name"]').val('<?= $current_user->firstname . ' ' . $current_user->lastname; ?>');
                // Email field removed; no default fill needed
            <?php } ?>


            // Trigger initial priority response time calculation after a short delay to ensure selectpicker is fully initialized
            setTimeout(function(){
                var initialPriority = $('select[name="priority"]').selectpicker('val');
                if (initialPriority) {
                    updateResponseTimeFromPriority(initialPriority);
                }
            }, 100);

            <?php if (isset($project_id)) { ?>
            $('body').on('selected.cleared.ajax.bootstrap.select', 'select[data-auto-project="true"]', function(
                e) {
                $('input[name="userid"]').val('');
                $(this).parents('.projects-wrapper').addClass('hide');
                $(this).prop('disabled', false);
                $(this).removeAttr('data-auto-project');
                $('body.ticket select[name="contactid"]').change();
            });
            <?php } ?>
        });
    </script>
    </body>

    </html>
