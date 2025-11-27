<div id="settings">

    <?= render_input('subject', 'ticket_settings_subject', $ticket->subject); ?>

    <!-- Contact field removed as requested; keep hidden placeholders to satisfy validation logic -->
    <input type="hidden" id="contactid" name="contactid" value="" data-no-contact="1">
    <?= form_hidden('userid', $ticket->userid); ?>
    <?php
        $is_handler = isset($ticket->ticketid) ? $this->tickets_model->is_ticket_handler((int)$ticket->ticketid, get_staff_user_id()) : false;
        $is_owner   = isset($ticket->assigned) && (int)$ticket->assigned === (int)get_staff_user_id();
        $is_creator = isset($ticket->admin) && (int)$ticket->admin === (int)get_staff_user_id();
        $can_reassign = is_admin() || $is_owner;
        $can_edit_resolution_time = is_admin() || $is_owner;
    ?>
    <div class="tw-mt-2 tw-mb-3">
        <?php if ($can_reassign) { ?>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#reassignTicketModal">
            <i class="fa fa-exchange"></i> Re Assign
        </button>
        <?php } ?>
        <?php if (is_admin() || $is_owner) { ?>
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#ticketHandlerModal" style="margin-left:8px;">
                <i class="fa fa-users"></i> Ticket Handler
            </button>
        <?php } ?>
    </div>
    <?php
        $department_name = '';
        foreach ($departments as $dept) {
            if ($dept['departmentid'] == $ticket->department) {
                $department_name = $dept['name'];
                break;
            }
        }

        $sub_department_name = '';
        foreach ($departments as $dept) {
            if ($dept['departmentid'] == $ticket->sub_department) {
                $sub_department_name = $dept['name'];
                break;
            }
        }

        $assigned_to_name = '';
        foreach ($staff as $member) {
            if ($member['staffid'] == $ticket->assigned) {
                $assigned_to_name = $member['firstname'] . ' ' . $member['lastname'];
                break;
            }
        }

        $service_name = '';
        foreach ($services as $service) {
            if ($service['serviceid'] == $ticket->service) {
                $service_name = $service['name'];
                break;
            }
        }

        $created_by_name = get_staff_full_name($ticket->admin);

                $approx_response_time = $ticket->approx_response_time;
        $approx_resolution_time = $ticket->approx_resolution_time;

        $division_name = '';
        if (isset($divisions)) {
            foreach ($divisions as $division) {
                if ($division['divisionid'] == $ticket->divisionid) {
                    $division_name = $division['name'];
                    break;
                }
            }
        }
    ?>

<div class="clearfix"></div>
<hr class="hr-panel-heading" />
<div class="row">
    <div class="col-md-12">
        <h4 class="no-margin">Ticket Details</h4>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <table class="table table-striped table-bordered">
            <tbody>
                <tr>
                    <td><strong>Division:</strong></td>
                    <td><?= $division_name ?></td>
                </tr>
                <tr>
                    <td><strong>Department:</strong></td>
                    <td><?= $department_name ?></td>
                </tr>
                <tr>
                    <td><strong>Sub Department:</strong></td>
                    <td><?= $sub_department_name ?></td>
                </tr>
                <tr>
                    <td><strong>Ticket Responsible:</strong></td>
                    <td><?= $assigned_to_name ?></td>
                </tr>
                <tr>
                    <td><strong>Ticket Handlers:</strong></td>
                    <td id="ticket_handlers_cell">Loading...</td>
                </tr>

                <tr>
                    <td><strong>Service:</strong></td>
                    <td><?= $service_name ?></td>
                </tr>
                <tr>
                    <td><strong>Ticket Owner:</strong></td>
                    <td><?= $created_by_name ?></td>
                </tr>
                <tr>
                    <td><strong>Approx. Response Time:</strong></td>
                    <td>
                        <?php
                        // First check if stored approx_response_time exists, use that first
                        $respStr = '';
                        $storedResponseTime = !empty($ticket->approx_response_time) ? trim($ticket->approx_response_time) : '';
                        if ($storedResponseTime !== '') {
                            try {
                                // Parse the stored time and format it
                                $respStr = date('M j, Y h:i A', strtotime($storedResponseTime));
                            } catch (Exception $e) {
                                // If parsing fails, fall back to computing
                            }
                        }
                        // If no stored time, compute from ticket created date + priority duration
                        if ($respStr === '' && !empty($ticket->date)) {
                            // Try to get priority duration values
                            $v = (int) ($ticket->priority_duration_value ?? 0);
                            $u = strtolower((string) ($ticket->priority_duration_unit ?? ''));

                            // If priority values not available, fall back to service values
                            if ($v <= 0 || $u === '') {
                                $v = (int) ($ticket->response_time_value ?? 0);
                                $u = strtolower((string) ($ticket->response_time_unit ?? ''));
                            }

                            if ($v > 0 && $u !== '') {
                                try {
                                    $dt = new DateTime($ticket->date);
                                    switch ($u) {
                                        case 'hour':
                                        case 'hours':
                                        case 'hrs':
                                            $dt->modify('+' . $v . ' hours');
                                            break;
                                        case 'day':
                                        case 'days':
                                            $dt->modify('+' . $v . ' days');
                                            break;
                                        case 'week':
                                        case 'weeks':
                                            $dt->modify('+' . ($v * 7) . ' days');
                                            break;
                                        case 'month':
                                        case 'months':
                                            $dt->modify('+' . $v . ' months');
                                            break;
                                        case 'year':
                                        case 'years':
                                            $dt->modify('+' . $v . ' years');
                                            break;
                                    }
                                    $respStr = date('M j, Y h:i A', strtotime($dt->format('Y-m-d H:i:s')));
                                } catch (Exception $e) { /* ignore */ }
                            }
                        }
                        echo $respStr !== '' ? e($respStr) : '-';
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Approx. Resolution Time:</strong></td>
                    <td class="approx-resolution-time-cell"<?php if ($can_edit_resolution_time): ?> style="cursor: pointer; color: #007bff;" data-toggle="modal" data-target="#editResolutionTimeModal"<?php endif; ?>>
                        <?php
                        // Use approx_resolution_time if set, otherwise compute from ticket created date + resolution_time_value/unit
                        $resoStr = '';
                        $displayValue = !empty($ticket->approx_resolution_time) ?
                            date('M j, Y', strtotime($ticket->approx_resolution_time)) :
                            '';

                        if (empty($displayValue) && !empty($ticket->date)) {
                            $v = (int) ($ticket->resolution_time_value ?? 0);
                            $u = strtolower((string) ($ticket->resolution_time_unit ?? ''));
                            if ($v > 0 && $u !== '') {
                                try {
                                    $dt = new DateTime($ticket->date);
                                    switch ($u) {
                                        case 'hour': case 'hours': $dt->modify('+' . $v . ' hours'); break;
                                        case 'day': case 'days': $dt->modify('+' . $v . ' days'); break;
                                        case 'week': case 'weeks': $dt->modify('+' . ($v * 7) . ' days'); break;
                                        case 'month': case 'months': $dt->modify('+' . $v . ' months'); break;
                                        case 'year': case 'years': $dt->modify('+' . $v . ' years'); break;
                                    }
                                    $resoStr = date('M j, Y', strtotime($dt->format('Y-m-d H:i:s')));
                                } catch (Exception $e) { /* ignore */ }
                            }
                        }

                        echo !empty($displayValue) ? e($displayValue) : ($resoStr !== '' ? e($resoStr) : '-');
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

    <div
        class="form-group select-placeholder projects-wrapper<?= $ticket->userid == 0 ? ' hide' : ''; ?>">
        <label for="project_id">
            <?= _l('project'); ?>
        </label>
        <div id="project_ajax_search_wrapper">
            <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true"
                data-width="100%"
                data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                <?php if ($ticket->project_id) { ?>
                <option value="<?= e($ticket->project_id); ?>">
                    <?= e(get_project_name_by_id($ticket->project_id)); ?>
                </option>
                <?php } ?>
            </select>
        </div>
    </div>
    <?php $can_merge = is_admin() || $is_owner; ?>
    <?php if ($can_merge) { ?>
        <?= render_input('merge_ticket_ids', 'merge_ticket_ids_field_label', '', 'text', $ticket->merged_ticket_id === null ? ['placeholder' => _l('merge_ticket_ids_field_placeholder')] : ['disabled' => true]); ?>
    <?php } ?>

<?= render_custom_fields('tickets', $ticket->ticketid); ?>
    <?= render_select('priority', $priorities, ['priorityid', 'name'], 'ticket_settings_priority', $ticket->priority); ?>
</div>

<!-- Re-Assign Modal -->
<div class="modal fade" id="reassignTicketModal" tabindex="-1" role="dialog" aria-labelledby="reassignTicketLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="reassignTicketLabel">Re Assign Ticket</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="reassign_ticket_id" value="<?= (int)$ticket->ticketid; ?>">
        <div class="row">
            <div class="col-sm-12">
                <?= render_select('reassign_divisionid', $divisions ?? [], ['divisionid','name'], 'Division', '', ['data-container' => '#reassignTicketModal']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <?= render_select('reassign_department', [], ['departmentid','name'], 'ticket_settings_departments', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'data-container' => '#reassignTicketModal']); ?>
            </div>
            <div class="col-sm-6">
                <?= render_select('reassign_sub_department', [], ['departmentid','name'], 'Sub Department', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'data-container' => '#reassignTicketModal']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?= render_select('reassign_application_id', [], ['id','name'], 'Application / Module', '', ['data-container' => '#reassignTicketModal', 'disabled' => true, 'data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?= render_select('reassign_service', $services ?? [], ['serviceid','name'], 'ticket_settings_service', '', ['data-container' => '#reassignTicketModal', 'disabled' => true]); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div id="reassign_assignee_preview" class="text-muted">
                    Will assign to: <span class="label label-default" id="reassign_assignee_name">(determine after selection)</span>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" id="reassign_save_btn" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Ticket Handler Modal -->
<div class="modal fade" id="ticketHandlerModal" tabindex="-1" role="dialog" aria-labelledby="ticketHandlerLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="ticketHandlerLabel">Ticket Handler</h4>
      </div>
      <div class="modal-body">
        <div class="form-inline" style="margin-bottom:10px;">
            <input type="text" id="th_search" class="form-control" placeholder="Search staff..." style="width: 60%;" />
            <span id="th_count" class="text-muted" style="margin-left:10px;"></span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped" id="ticket_handler_table">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Name</th>
                        <th>Sub Department</th>
                        <th>Employee Code</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>
                </tbody>
            </table>
        </div>
        <hr>
        <div id="resolution_time_section" style="display:none;">
            <h5>Approx. Resolution Time (Mandatory)</h5>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-8">
                        <label for="th_resolution_date">Date:</label>
                        <input type="date" class="form-control" id="th_resolution_date" value="" min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="th_resolution_priority">Priority:</label>
                        <select id="th_resolution_priority" class="form-control">
                            <option value="">Select Priority</option>
                        </select>
                    </div>
                </div>
                <small class="text-muted">Set the approximate resolution time for this ticket after handler assignment.</small>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" id="th_save_btn" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
  </div>

<script>
    // Ensure jQuery is available before running
    window.addEventListener('load', function() {
        function waitForjQuery(cb){ if (window.jQuery && typeof $ === 'function') { cb(); } else { setTimeout(function(){ waitForjQuery(cb); }, 50); } }
        waitForjQuery(function(){
        // Global initial values for both modals (reassign/handler)
        window.REASSIGN_INITIAL = {
            div: '<?= isset($ticket->divisionid) ? e((string)$ticket->divisionid) : '' ?>',
            dept: '<?= isset($ticket->department) ? e((string)$ticket->department) : '' ?>',
            sub: '<?= isset($ticket->sub_department) ? e((string)$ticket->sub_department) : '' ?>',
            app: '<?= isset($ticket->application_id) ? e((string)$ticket->application_id) : '' ?>',
            svc: '<?= isset($ticket->service) ? e((string)$ticket->service) : '' ?>',
            assigned: '<?= isset($ticket->assigned) ? e((string)$ticket->assigned) : '' ?>'
        };
        var $department = $('select[name="department"]');
        var $sub = $('select[name="sub_department"]');
        var $service = $('#service');
        var $assigned = $('#assigned');

        // Disable editing for Department, Sub Department, Assigned, and Service
        try {
            $department.prop('disabled', true).selectpicker('refresh');
        } catch(e) { $department.prop('disabled', true); }
        try {
            $sub.prop('disabled', true).selectpicker('refresh');
        } catch(e) { $sub.prop('disabled', true); }
        try {
            $service.prop('disabled', true).selectpicker('refresh');
        } catch(e) { $service.prop('disabled', true); }
        try {
            $assigned.prop('disabled', true).selectpicker('refresh');
        } catch(e) { $assigned.prop('disabled', true); }
        // Hide inline "add service" button if present
        $('a[onclick*="new_service"]').closest('.input-group-btn').addClass('hide');

        // Cache departments for client-side filtering (fallback if AJAX not available)
        <?php
            $departmentsForJs = array_map(function($d){
                return [
                    'departmentid' => (int)$d['departmentid'],
                    'name' => $d['name'],
                    'divisionid' => isset($d['divisionid']) ? (string)$d['divisionid'] : '',
                    'parent_department' => isset($d['parent_department']) ? (int)$d['parent_department'] : 0,
                ];
            }, $departments);
            $topDepartmentsForJs = array_values(array_filter($departmentsForJs, function($d){
                return empty($d['parent_department']) || (int)$d['parent_department'] === 0;
            }));
        ?>
        var ALL_DEPARTMENTS = <?= json_encode($departmentsForJs); ?>;
        var ALL_TOP_DEPARTMENTS = <?= json_encode($topDepartmentsForJs); ?>;
        var SUB_DEPARTMENTS_BY_PARENT = (function(){
            var map = {};
            (ALL_DEPARTMENTS || []).forEach(function(d){
                var parent = d.parent_department || 0;
                if (parent) {
                    if (!map[parent]) map[parent] = [];
                    map[parent].push(d);
                }
            });
            return map;
        })();

        // Cache divisions for rebuilding the modal select if needed
        <?php
            $divisionsForJs = array_map(function($d){
                return [ 'divisionid' => (string)$d['divisionid'], 'name' => $d['name'] ];
            }, $divisions ?? []);
        ?>
        var ALL_DIVISIONS = <?= json_encode($divisionsForJs); ?>;

        // Cache priorities for the resolution time modal
        <?php
            $priorityOptionsForJs = [];
            if (isset($priorities) && is_array($priorities)) {
                foreach ($priorities as $p) {
                    $priorityOptionsForJs[] = ['id' => (string)$p['priorityid'], 'name' => $p['name']];
                }
            }
        ?>
        var PRIORITIES = <?= json_encode($priorityOptionsForJs); ?>;

        function rebuildDepartmentOptions(list){
            var opts = '<option value=""></option>';
            list.forEach(function(d){ opts += '<option value="'+ d.departmentid +'">'+ (d.name||'') +'</option>'; });
            $department.html(opts).selectpicker('refresh');
            // clear sub department
            $sub.html('').prop('disabled', true).selectpicker('refresh');
            // clear assigned
            $assigned.find('option').not(':first').remove();
            $assigned.selectpicker('val','').selectpicker('refresh');
        }

        // Build services map from server data to support filtering by application
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
                ];
            }, is_array($services ?? null) ? $services : []);
        ?>
        var ALL_SERVICE_OPTIONS = <?= json_encode($servicesForJs); ?>;

        // Sidebar service list (outside modal): filter only by division
        function updateServicesForDivision(divId){
            var opts = '<option value=""></option>';
            (ALL_SERVICE_OPTIONS || []).forEach(function(s){
                if (!divId || (s.divisionid && String(s.divisionid) === String(divId))){
                    var attrs = '';
                    if (s.divisionid) attrs += ' data-divisionid="'+ s.divisionid +'"';
                    opts += '<option value="'+ s.id +'"'+ attrs +'>'+ s.name +'</option>';
                }
            });
            $service.html(opts).selectpicker('val','').selectpicker('refresh');
        }

        function rebuildReassignServices(filter){
            var opts = '<option value=""></option>';
            var count = 0;
            (ALL_SERVICE_OPTIONS || []).forEach(function(s){
                if (filter.division && String(s.divisionid||'') !== String(filter.division)) return;
                if (filter.department && String(s.departmentid||'') !== String(filter.department)) return;
                if (filter.sub && String(s.sub_department||'') !== String(filter.sub)) return;
                if (filter.application && String(filter.application||'') !== String(s.applicationid||'')) return;
                var attrs = '';
                if (s.divisionid) attrs += ' data-divisionid="'+ s.divisionid +'"';
                if (s.departmentid) attrs += ' data-departmentid="'+ s.departmentid +'"';
                if (s.sub_department) attrs += ' data-sub-department="'+ s.sub_department +'" data-sub_department="'+ s.sub_department +'"';
                if (s.responsible) attrs += ' data-responsible="'+ s.responsible +'"';
                if (s.applicationid) attrs += ' data-applicationid="'+ s.applicationid +'"';
                opts += '<option value="'+ s.id +'"'+ attrs +'>'+ s.name +'</option>';
                count++;
            });
            $rSvc.html(opts).selectpicker('refresh');
            return count;
        }

        function updateDepartmentsForDivision(divId){
            if (!divId){
                rebuildDepartmentOptions(ALL_TOP_DEPARTMENTS);
                updateServicesForDivision(null);
                return;
            }
            // Client-side filter by division
            var list = (ALL_DEPARTMENTS || []).filter(function(d){
                return (!d.parent_department || d.parent_department === 0) && String(d.divisionid||'') === String(divId);
            });
            rebuildDepartmentOptions(list);
            updateServicesForDivision(divId);
        }

        // Populate Assign list by (sub)department
        function updateAssignedByDepartment(depId, includeChildren){
            if (!depId){
                $assigned.find('option').not(':first').remove();
                $assigned.selectpicker('val','').selectpicker('refresh');
                return;
            }
            var current = $assigned.selectpicker('val');
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
                $assigned.append(options).selectpicker('refresh');
                if (current && list.some(function(x){ return String(x.id)===String(current);} )){
                    $assigned.selectpicker('val', current);
                }
            });
        }

        function loadSubDepartments(parentId, selectedId){
            if (!parentId){ $sub.html('').selectpicker('refresh'); return; }
            var data = SUB_DEPARTMENTS_BY_PARENT[parentId] || [];
            var options = '<option value=""></option>';
            if (Array.isArray(data) && data.length){
                data.forEach(function(d){ options += '<option value="'+ (d.departmentid||'') +'">'+ (d.name||'') +'</option>'; });
            }
            $sub.html(options).selectpicker('refresh');
            if (selectedId){ $sub.selectpicker('val', selectedId); }
        }

        // Map service -> department/sub and update assignees
        function applyServiceDepartmentMapping(){
            var $opt = $('#service option:selected');
            if (!$opt.length) return;
            var dep = $opt.data('departmentid');
            var sub = $opt.data('sub-department');
            if (typeof sub === 'undefined') { sub = $opt.data('sub_department'); }
            var sid = $('#service').selectpicker('val');
            function applyMapping(depVal, subVal){
                if (depVal){
                    $department.selectpicker('val', String(depVal));
                    loadSubDepartments(String(depVal), subVal ? String(subVal) : null);
                    if (subVal) { updateAssignedByDepartment(String(subVal), false); }
                    else { updateAssignedByDepartment(String(depVal), true); }
                }
            }
            if (dep){
                applyMapping(dep, sub);
            } else if (sid){
                $.getJSON(admin_url + 'tickets/service_info/' + sid).done(function(r){
                    if (r && r.success){ applyMapping(r.departmentid, r.sub_department); }
                });
            }
        }

        // Bindings
        $department.on('changed.bs.select', function(){ var v=$(this).selectpicker('val'); loadSubDepartments(v, null); updateAssignedByDepartment(v, true); });
        $sub.on('changed.bs.select', function(){ var sv=$(this).selectpicker('val'); if (sv){ updateAssignedByDepartment(sv, false); } else { var dv=$department.selectpicker('val'); if (dv){ updateAssignedByDepartment(dv, true); } }});
        $('#service').on('changed.bs.select', applyServiceDepartmentMapping);

        // --- Re-Assign Modal Logic ---
        var $rDiv   = $('select[name="reassign_divisionid"]');
        var $rDept  = $('select[name="reassign_department"]');
        var $rSub   = $('select[name="reassign_sub_department"]');
        var $rApp   = $('select[name="reassign_application_id"]');
        var $rSvc   = $('select[name="reassign_service"]');
        var $assigneeName = $('#reassign_assignee_name');
        var $saveBtn = $('#reassign_save_btn');

        function setPickerDisabled($el, disabled){
            if (disabled) {
                $el.prop('disabled', true).selectpicker('refresh');
            } else {
                $el.removeAttr('disabled').selectpicker('refresh');
            }
        }

        // Client-side map for instant assignee display without AJAX
        <?php
            $staffMap = [];
            if (isset($staff) && is_array($staff)) {
                foreach ($staff as $member) {
                    $label = $member['firstname'] . ' ' . $member['lastname'];
                    if (isset($member['staff_emp_code']) && !empty($member['staff_emp_code'])) {
                        $label .= ' (' . $member['staff_emp_code'] . ')';
                    }
                    $staffMap[(string)$member['staffid']] = $label;
                }
            }
        ?>
        var STAFF_DISPLAY_MAP = <?= json_encode($staffMap); ?>;

        var ASSIGNEE_READY = false; 
        function setAssigneeReady(ready){ 
            ASSIGNEE_READY = !!ready; 
            // Save button enablement is controlled by change detection, not assignee readiness
        } 
        function resetAssigneePreview(){ 
            $assigneeName.text('(determine after selection)'); 
            setAssigneeReady(false); 
        } 

        function rRebuildDepartments(list, preserve){
            var opts = '<option value=""></option>';
            list.forEach(function(d){ opts += '<option value="'+ d.departmentid +'">'+ (d.name||'') +'</option>'; });
            var prev = preserve ? $rDept.selectpicker('val') : '';
            $rDept.html(opts).selectpicker('refresh');
            if (preserve && prev && $rDept.find('option[value="'+prev+'"]').length){ $rDept.selectpicker('val', prev); }
            $rSub.html('').selectpicker('refresh');
            // Enable/disable Department based on data presence
            if (list && list.length){ setPickerDisabled($rDept, false); }
            else { setPickerDisabled($rDept, true); }
        }

    function rUpdateDepartmentsForDivision(divId, selectedDept, selectedSub, selectedApp, selectedSvc){
            // selectedDept, selectedSub optional - applied after rebuild
            if (!divId){ rRebuildDepartments(ALL_TOP_DEPARTMENTS, false); if (selectedDept){ $rDept.selectpicker('val', selectedDept); } return; }
            var list = (ALL_DEPARTMENTS || []).filter(function(d){ return (!d.parent_department || d.parent_department === 0) && String(d.divisionid||'') === String(divId); });
            if (Array.isArray(list) && list.length) {
                rRebuildDepartments(list,false);
                if (selectedDept){
                    // apply selection and load sub-departments
                    $rDept.selectpicker('val', String(selectedDept));
                    rLoadSubDepartments(String(selectedDept), selectedSub || null);
                    setPickerDisabled($rSub, !selectedDept ? true : false);
                    // populate applications and services now that department/sub are set
                    rUpdateApplications(String(selectedDept), selectedSub || '', selectedApp || '', selectedSvc || '');
                    var svcCnt = rUpdateServices();
                    if (svcCnt > 0 && selectedSvc){ $rSvc.selectpicker('val', String(selectedSvc)); setPickerDisabled($rSvc, false); }
                }
                return;
            }
            // Fallback: request departments from server if client-side list is empty
            $.post(admin_url + 'tickets/departments_by_division', { divisionid: divId }).done(function(resp){
                var serverList = [];
                try { serverList = JSON.parse(resp); } catch(e){ serverList = []; }
                if (Array.isArray(serverList) && serverList.length){
                    rRebuildDepartments(serverList,false);
                    setPickerDisabled($rDept, false);
                    if (selectedDept){
                        $rDept.selectpicker('val', String(selectedDept));
                        rLoadSubDepartments(String(selectedDept), selectedSub || null);
                        setPickerDisabled($rSub, !selectedDept ? true : false);
                        // populate applications and services now that department/sub are set
                        rUpdateApplications(String(selectedDept), selectedSub || '', selectedApp || '', selectedSvc || '');
                        var svcCnt2 = rUpdateServices();
                        if (svcCnt2 > 0 && selectedSvc){ $rSvc.selectpicker('val', String(selectedSvc)); setPickerDisabled($rSvc, false); }
                    }
                } else {
                    // No departments returned; clear and disable
                    rRebuildDepartments([], false);
                }
            }).fail(function(){ rRebuildDepartments([], false); });
        }

        function rLoadSubDepartments(parentId, selectedId){
            if (!parentId){ $rSub.html('<option value=""></option>').selectpicker('refresh'); setPickerDisabled($rSub, true); return; }
            var data = SUB_DEPARTMENTS_BY_PARENT[parentId] || [];
            if (Array.isArray(data) && data.length){
                var options = '<option value=""></option>';
                data.forEach(function(d){ options += '<option value="'+ (d.departmentid||'') +'">'+ (d.name||'') +'</option>'; });
                $rSub.html(options).selectpicker('refresh');
                if (selectedId){ $rSub.selectpicker('val', selectedId); }
                setPickerDisabled($rSub, false);
                return;
            }
            // Fallback to server if client cache has no sub-departments
            $.post(admin_url + 'tickets/sub_departments', { parent_id: parentId }).done(function(resp){
                var children = [];
                try { children = JSON.parse(resp); } catch(e){ children = []; }
                var options = '<option value=""></option>';
                if (Array.isArray(children) && children.length){
                    children.forEach(function(d){ options += '<option value="'+ (d.departmentid||'') +'">'+ (d.name||'') +'</option>'; });
                }
                $rSub.html(options).selectpicker('refresh');
                if (selectedId && $rSub.find('option[value="'+selectedId+'"]').length){ $rSub.selectpicker('val', String(selectedId)); }
                setPickerDisabled($rSub, !(Array.isArray(children) && children.length));
            }).fail(function(){ $rSub.html('<option value=""></option>').selectpicker('refresh'); });
        }

        function rUpdateServices(){
            var divId = $rDiv.selectpicker('val') || '';
            var depId = $rDept.selectpicker('val') || '';
            var subId = $rSub.selectpicker('val') || '';
            var appId = $rApp.selectpicker('val') || '';
            var count = rebuildReassignServices({ division: divId, department: depId, sub: subId, application: appId });
            var enableSvc = (count > 0) && !!depId && !!appId; // require department and application
            setPickerDisabled($rSvc, !enableSvc);
            if (!enableSvc){ $rSvc.selectpicker('val',''); }
            return count;
        }

function rUpdateApplications(depId, subId, selectedApp, selectedSvc){
    if (!depId){ $rApp.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh'); return; }
    $.post(admin_url + 'tickets/applications_by_department', { department_id: depId, sub_department_id: subId || '' })
        .done(function(resp){
            var list = [];
            try { list = JSON.parse(resp); } catch(e){ list = []; }
            var opts = '<option value=""></option>';
            list.forEach(function(a){ opts += '<option value="'+ a.id +'">'+ a.name +'</option>'; });
            $rApp.html(opts);
            if (list.length > 0) { $rApp.prop('disabled', false); } else { $rApp.prop('disabled', true); }
            $rApp.selectpicker('refresh');
            if (selectedApp && $rApp.find('option[value="'+selectedApp+'"]').length){ $rApp.selectpicker('val', String(selectedApp)); }
            var count = rUpdateServices();
            if (selectedSvc && count > 0 && $rSvc.find('option[value="'+selectedSvc+'"]').length){ $rSvc.selectpicker('val', String(selectedSvc)); rPreviewAssignee(); }
        })
        .fail(function(){ $rApp.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh'); rUpdateServices(); });
}

        function rSetAssigneeFromStaffId(staffId){ 
            if (!staffId) { return; }
            try { window.REASSIGN_ASSIGNEE_ID = String(staffId); } catch(e) { window.REASSIGN_ASSIGNEE_ID = staffId; }
            var key = String(staffId);
            if (STAFF_DISPLAY_MAP && STAFF_DISPLAY_MAP[key]) {
                $assigneeName.text(STAFF_DISPLAY_MAP[key]);
                setAssigneeReady(true);
                return;
            }
            $.getJSON(admin_url + 'tickets/staff_info/' + key).done(function(s){
                if (s && s.success){
                    var display = s.name || ('#'+key);
                    if (s.emp_code) { display += ' (' + s.emp_code + ')'; }
                    $assigneeName.text(display); setAssigneeReady(true);
                } else {
                    $assigneeName.text('#'+key); setAssigneeReady(true);
                }
            }).fail(function(){ $assigneeName.text('#'+key); setAssigneeReady(true); });
        }

        function rPreviewAssignee(){ 
            var sid = $rSvc.selectpicker('val'); 
            resetAssigneePreview(); 
            if (!sid){ return; }
            // Resolve the actual option based on value to avoid selectpicker quirks
            var $sel = $('#reassign_service option[value="'+ sid +'"]');
            // 1) If service has direct responsible mapping in option data, use it
            var optResp = $sel.data('responsible');
            if (optResp) { rSetAssigneeFromStaffId(optResp); return; }
            // 2) Determine HOD based on sub-department first, then department
            var subAttr = $sel.attr('data-sub-department') || $sel.attr('data-sub_department') || '';
            var depAttr = $sel.attr('data-departmentid') || '';
            var subId = subAttr ? String(subAttr) : '';
            var depId = depAttr ? String(depAttr) : '';

            function resolveHOD(sub, dep){
                if (sub){
                    $.getJSON(admin_url + 'tickets/department_info/' + sub).done(function(rr){
                        if (rr && rr.success && rr.responsible_staff){ rSetAssigneeFromStaffId(rr.responsible_staff); }
                        else if (dep){
                            $.getJSON(admin_url + 'tickets/department_info/' + dep).done(function(rr2){
                                if (rr2 && rr2.success && rr2.responsible_staff){ rSetAssigneeFromStaffId(rr2.responsible_staff); }
                                else { $assigneeName.text('(no responsible found)'); setAssigneeReady(false); }
                            }).fail(function(){ $assigneeName.text('(no responsible found)'); setAssigneeReady(false); });
                        } else { $assigneeName.text('(no responsible found)'); setAssigneeReady(false); }
                    }).fail(function(){ if (dep){ $.getJSON(admin_url + 'tickets/department_info/' + dep).done(function(rr2){ if (rr2 && rr2.success && rr2.responsible_staff){ rSetAssigneeFromStaffId(rr2.responsible_staff); } else { $assigneeName.text('(no responsible found)'); setAssigneeReady(false); } }); } else { $assigneeName.text('(no responsible found)'); setAssigneeReady(false); } });
                    return;
                }
                if (dep){
                    $.getJSON(admin_url + 'tickets/department_info/' + dep).done(function(rr){
                        if (rr && rr.success && rr.responsible_staff){ rSetAssigneeFromStaffId(rr.responsible_staff); }
                        else { $assigneeName.text('(no responsible found)'); setAssigneeReady(false); }
                    }).fail(function(){ $assigneeName.text('(no responsible found)'); setAssigneeReady(false); });
                    return;
                }
                // 3) As last resort, try service_info (may be restricted for non-admin)
                $.getJSON(admin_url + 'tickets/service_info/' + sid).done(function(r){
                    if (r && r.success){
                        if (r.responsible){ rSetAssigneeFromStaffId(r.responsible); return; }
                        var hodT = r.sub_department || r.departmentid;
                        if (hodT){
                            $.getJSON(admin_url + 'tickets/department_info/' + String(hodT)).done(function(rr){
                                if (rr && rr.success && rr.responsible_staff){ rSetAssigneeFromStaffId(rr.responsible_staff); } 
                                else { $assigneeName.text('(no responsible found)'); setAssigneeReady(false); }
                            }).fail(function(){ $assigneeName.text('(no responsible found)'); setAssigneeReady(false); });
                        } else { $assigneeName.text('(no responsible found)'); setAssigneeReady(false); }
                    } else { $assigneeName.text('(no responsible found)'); setAssigneeReady(false); }
                }).fail(function(){ $assigneeName.text('(no responsible found)'); setAssigneeReady(false); });
            }
            resolveHOD(subId, depId);
        }

        // Ensure modal is appended to body to avoid clipping/stacking issues
        $('#reassignTicketModal').on('show.bs.modal', function(){
            $(this).appendTo('body');
        });

        $('#reassignTicketModal').on('shown.bs.modal', function(){
            // Re-init selectpicker inside the modal context
            var $modal = $(this);
            // Force re-init to avoid stale state
            $modal.find('.selectpicker').each(function(){ try { $(this).selectpicker('destroy'); } catch(e){} });
            if (typeof init_selectpicker === 'function') { init_selectpicker(); }
            $modal.find('.selectpicker').each(function(){
                var $sp = $(this);
                try { $sp.selectpicker({ container: '#reassignTicketModal' }); } catch(e){}
                try { $sp.selectpicker('refresh'); } catch(e){}
            });
            // If division options are missing for any reason, rebuild from ALL_DIVISIONS
            if ($rDiv.find('option').length <= 1 && Array.isArray(ALL_DIVISIONS) && ALL_DIVISIONS.length){
                var opts = '<option value=""></option>';
                ALL_DIVISIONS.forEach(function(d){ opts += '<option value="'+ d.divisionid +'">'+ (d.name||'') +'</option>'; });
                $rDiv.html(opts).selectpicker('refresh');
            }
            // Fresh form: clear all selects and disable dependent ones
            setPickerDisabled($rDiv, false);
            $rDiv.selectpicker('val','');
            // Reset and disable Department/Sub Department/Application/Service
            $rDept.html('<option value=""></option>').selectpicker('refresh');
            setPickerDisabled($rDept, true);
            $rSub.html('<option value=""></option>').selectpicker('refresh');
            setPickerDisabled($rSub, true);
            $rApp.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh');
            $rSvc.find('option').not(':first').remove();
            setPickerDisabled($rSvc, true);
            // Reset assignee preview
            resetAssigneePreview();
            // Disable Save initially; enable after user selects values
            $saveBtn.prop('disabled', true);
        });
        function updateSaveEnabled(){
            var cur = {
                div: $rDiv.selectpicker('val')||'',
                dept: $rDept.selectpicker('val')||'',
                sub: $rSub.selectpicker('val')||'',
                app: $rApp.selectpicker('val')||'',
                svc: $rSvc.selectpicker('val')||'',
            };
            // For fresh form, compare against empty baseline
            var init = { div:'',dept:'',sub:'',app:'',svc:'' };
            var changed = (cur.div !== (init.div||'')) || (cur.dept !== (init.dept||'')) || (cur.sub !== (init.sub||'')) || (cur.app !== (init.app||'')) || (cur.svc !== (init.svc||''));
            $saveBtn.prop('disabled', !changed);
        }
        function onDivisionChanged(){
            var v = $rDiv.selectpicker('val');
            rUpdateDepartmentsForDivision(v);
            // Enable Department only after division is chosen
            setPickerDisabled($rDept, !v);
            // Reset others
            setPickerDisabled($rSub, true); $rSub.selectpicker('val','');
            $rApp.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh');
            setPickerDisabled($rSvc, true); $rSvc.selectpicker('val','');
            resetAssigneePreview();
            updateSaveEnabled();
        }
        $rDiv.on('changed.bs.select', onDivisionChanged);
        $rDiv.on('change', onDivisionChanged);
        function onDepartmentChanged(){ 
            var v=$(this).selectpicker('val'); 
            // Enable Sub Department when department chosen
            setPickerDisabled($rSub, !v); 
            rLoadSubDepartments(v, null); 
            // Update applications for department change
            rUpdateApplications(v || '', $rSub.selectpicker('val')||'', '', '');
            // Rebuild services (will remain disabled until app selected)
            rUpdateServices();
            resetAssigneePreview();
            updateSaveEnabled();
        }
        $rDept.on('changed.bs.select', onDepartmentChanged);
        $rDept.on('change', onDepartmentChanged);
        function onSubDepartmentChanged(){ rUpdateApplications($rDept.selectpicker('val')||'', $rSub.selectpicker('val')||'', '', ''); rUpdateServices(); resetAssigneePreview(); updateSaveEnabled(); }
        $rSub.on('changed.bs.select', onSubDepartmentChanged);
        $rSub.on('change', onSubDepartmentChanged);
        function onApplicationChanged(){ rUpdateServices(); resetAssigneePreview(); updateSaveEnabled(); }
        $rApp.on('changed.bs.select', onApplicationChanged);
        $rApp.on('change', onApplicationChanged);
        // Ensure service change triggers preview across browsers/plugins
        function onServiceChanged(){ rPreviewAssignee(); updateSaveEnabled(); }
        $rSvc.on('changed.bs.select', onServiceChanged);
        $rSvc.on('change', onServiceChanged);
        $rSvc.on('changed.bs.select', function(){ rPreviewAssignee(); updateSaveEnabled(); });

        $('#reassign_save_btn').on('click', function(){ 
            var targetAssignee = (window.REASSIGN_ASSIGNEE_ID || '').toString();
            var payload = { 
                ticketid: $('#reassign_ticket_id').val(), 
                divisionid: $rDiv.selectpicker('val')||'', 
                department: $rDept.selectpicker('val')||'', 
                sub_department: $rSub.selectpicker('val')||'', 
                application_id: $rApp.selectpicker('val')||'',
                service: $rSvc.selectpicker('val')||'',
                to_assigned: targetAssignee
            };
            if (!payload.ticketid) { alert_float('danger', 'Ticket id missing'); return; }
            if (!payload.to_assigned) { alert_float('danger', 'No assignee could be determined'); return; }
            $(this).prop('disabled', true);
            $.post(admin_url + 'tickets/reassign_request', payload).done(function(resp){
                try { resp = JSON.parse(resp); } catch(e) { resp = {success:false}; }
                if (resp && resp.success){
                    alert_float('success', 'Reassignment request sent');
                    location.reload();
                } else {
                    var msg = (resp && resp.message) ? resp.message : 'Failed to create reassignment request';
                    alert_float('danger', msg);
                }
            }).fail(function(){ alert_float('danger', 'Failed to reassign ticket'); })
              .always(function(){ $('#reassign_save_btn').prop('disabled', false); $('#reassignTicketModal').modal('hide'); });
        });
        // --- End Re-Assign Modal Logic ---

        // --- Ticket Handler Modal Logic ---
        function adjustSaveButton(){
            var sectionVisible = $('#resolution_time_section').is(':visible');
            var dateSelected = $('#th_resolution_date').val().trim() !== '';
            $('#th_save_btn').prop('disabled', !(sectionVisible && dateSelected));
        }

        var $thModal = $('#ticketHandlerModal');
        var $thTable = $('#ticket_handler_table tbody');
        var $thSearch = $('#th_search');
        var $thCount = $('#th_count');
        var TH_DATA = [];
        var TH_SELECTED = [];

        function thRender(list){
            var rows = '';
            (list || []).forEach(function(s){
                var code = s.emp_code ? s.emp_code : '';
                var checked = TH_SELECTED.indexOf(String(s.id||s.staffid||'')) !== -1 ? ' checked' : '';
                var sid = s.id || s.staffid || '';
                rows += '<tr>'+
                        '<td><input type="checkbox" class="th-chk" data-id="'+ sid +'"'+checked+'></td>'+
                        '<td>'+ (s.name||'') +'</td>'+
                        '<td>'+ (s.sub_department||s.dept_name||'') +'</td>'+
                        '<td>'+ code +'</td>'+
                    '</tr>';
            });
            if (!rows){ rows = '<tr><td colspan="4" class="text-center text-muted">No staff found</td></tr>'; }
            $thTable.html(rows);
            $thCount.text(list.length + ' staff');
            // bind checkbox changes
            $thTable.find('.th-chk').off('change').on('change', function(){
                var id = String($(this).data('id')||'');
                if (!id) return;
                if ($(this).is(':checked')) {
                    if (TH_SELECTED.indexOf(id) === -1) TH_SELECTED.push(id);
            } else {
                TH_SELECTED = TH_SELECTED.filter(function(x){ return x !== id; });
            }
            $('#resolution_time_section').toggle(TH_SELECTED.length > 0);
            adjustSaveButton();
        });
        }

        function thFetch(){
            $thTable.html('<tr><td colspan="2" class="text-center text-muted">Loading...</td></tr>');
            $thCount.text('');
            // List subordinates of current user based on reporting_manager
            var currentUserId = '<?= get_staff_user_id(); ?>';
            var currentAssignee = window.REASSIGN_INITIAL.assigned || '';
            $.post(admin_url + 'tickets/staff_by_department', {
                department: '',
                include_children: 0,
                subordinates_only: 1,
                manager_id: currentUserId,
                ticket_id: <?= (int)$ticket->ticketid; ?>
            })
                .done(function(resp){
                    var list = [];
                    try { list = JSON.parse(resp); } catch(e){ list = []; }
                    // Also include current ticket assignee if not already in the list
                    if (currentAssignee) {
                        var assigneeExists = list.some(function(s){ return String(s.id || s.staffid) === String(currentAssignee); });
                        if (!assigneeExists) {
                            // Fetch assignee details
                            $.getJSON(admin_url + 'tickets/staff_info/' + currentAssignee).done(function(assignee){
                                if (assignee && assignee.success) {
                                    list.push({
                                        id: String(currentAssignee),
                                        staffid: String(currentAssignee),
                                        name: assignee.name || 'Unknown',
                                        emp_code: assignee.emp_code || '',
                                        deptid: null,
                                        dept_name: ''
                                    });
                                }
                            }).always(function(){
                                TH_DATA = list;
                                thRender(list);
                            });
                            return;
                        }
                    }
                    TH_DATA = list;
                    thRender(list);
                })
                .fail(function(){ TH_DATA = []; thRender([]); });
        }

        function thFetchSelected(){
            var tid = <?= (int)$ticket->ticketid; ?>;
            TH_SELECTED = [];
            $.getJSON(admin_url + 'tickets/ticket_handlers/' + tid)
                .done(function(list){
                    if (!Array.isArray(list)) list = [];
                    TH_SELECTED = list.map(function(s){ return String(s.staffid || s.id || ''); });
                    thRender(TH_DATA);
                })
                .fail(function(){ TH_SELECTED = []; thRender(TH_DATA); });
        }

        $thModal.on('show.bs.modal', function(){
            // Prevent clipping by ancestors; ensure modal is at body level
            $(this).appendTo('body');
        });
        $thModal.on('shown.bs.modal', function(){
            // initialize selectpicker container for consistency
            thFetch();
            thFetchSelected();
            $thSearch.val('');
            var opts = '<option value="">Select Priority</option>';
            (PRIORITIES || []).forEach(function(p){
                opts += '<option value="' + p.id + '">' + p.name + '</option>';
            });
            $('#th_resolution_priority').html(opts);
            // Populate current values
            var currentValue = '<?= !empty($ticket->approx_resolution_time) ? strtotime($ticket->approx_resolution_time) : '' ?>';
            if (currentValue) {
                var dateObj = new Date(currentValue * 1000);
                var dateStr = dateObj.toISOString().split('T')[0];
                $('#th_resolution_date').val(dateStr);
            } else {
                $('#th_resolution_date').val('');
            }
            $('#th_resolution_priority').val('<?= e((string)$ticket->priority) ?>');
            $('#resolution_time_section').toggle(TH_SELECTED.length > 0);
            adjustSaveButton();
            // Bind date change to enable save button
            $('#th_resolution_date').off('input change').on('input change', function(){
                adjustSaveButton();
            });
        });
        $thSearch.on('input', function(){
            var q = ($(this).val()||'').toLowerCase();
            if (!q){ thRender(TH_DATA); return; }
            var filtered = (TH_DATA||[]).filter(function(s){
                var name = (s.name||'').toLowerCase();
                var code = (s.emp_code||'').toLowerCase();
                return name.indexOf(q) !== -1 || code.indexOf(q) !== -1;
            });
            thRender(filtered);
        });
        $('#th_save_btn').on('click', function(){
            // Validate resolution time date
            var selectedDate = $('#th_resolution_date').val();
            if (selectedDate) {
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                var selected = new Date(selectedDate);
                selected.setHours(0, 0, 0, 0);
                if (selected < today) {
                    alert_float('danger', 'Resolution time date cannot be in the past. Please select today or a future date.');
                    return;
                }
            }

            var tid = <?= (int)$ticket->ticketid; ?>;
            // Build payload in a way CI reliably parses arrays
            var payload = {
                ticket_id: tid,
                approx_resolution_time_date: selectedDate,
                priority: $('#th_resolution_priority').val()
            };
            payload['handlers[]'] = TH_SELECTED;
            var $btn = $(this).prop('disabled', true);
            $.post(admin_url + 'tickets/update_ticket_handlers', payload)
                .done(function(resp){ try{ resp = JSON.parse(resp); }catch(e){ resp = {success:false}; }
    if (resp && resp.success){
        alert_float('success', 'Handlers and resolution time updated');
        var ticketNum = '<?= $ticket->ticket_number ?: $ticket->ticketid ?>';
        window.location.href = admin_url + 'tickets/ticket/' + ticketNum;
    } else {
                alert_float('danger', 'Failed to update handlers and resolution time');
            }
                })
                .fail(function(){ alert_float('danger', 'Failed to update handlers'); })
                .always(function(){ $btn.prop('disabled', false); });
        });
        // --- End Ticket Handler Modal Logic ---

        // Load ticket handlers for display in table
        $.getJSON(admin_url + 'tickets/ticket_handlers/' + <?= (int)$ticket->ticketid; ?>)
            .done(function(list){
                var names = '';
                if (Array.isArray(list) && list.length > 0){
                    names = list.map(function(h){ return h.name || '#'+h.staffid; }).join(', ');
                } else {
                    names = 'None';
                }
                $('#ticket_handlers_cell').text(names);
            })
            .fail(function(xhr, status, error){
                console.log('Ticket handlers AJAX error:', status, error, xhr.responseText);
                $('#ticket_handlers_cell').text('Error loading handlers');
            });

        // --- Edit Resolution Time Modal Logic ---
        var ticketIsClosed = <?= !$can_edit_resolution_time ? 'true' : 'false' ?>;
        function initEditResolutionTimeModal(){
            console.log('Initializing Edit Resolution Time Modal...');
            var $modal = $('#editResolutionTimeModal');
            console.log('Modal found:', $modal.length);
            $modal.remove(); // Ensure any duplicate is removed
            // Re-add the modal to body for proper z-index
            var modalHtml = `
<div class="modal fade" id="editResolutionTimeModal" tabindex="-1" role="dialog" aria-labelledby="editResolutionTimeLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="editResolutionTimeLabel">Edit Approx. Resolution Time</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Select New Resolution Time:</label>
          <div class="row">
            <div class="col-md-12">
              <label for="resolution_date">Date:</label>
              <input type="date" class="form-control" id="resolution_date" value="" min="<?= date('Y-m-d') ?>">
            </div>
          </div>
          <div class="row" style="margin-top:10px;">
            <div class="col-md-12">
              <label for="resolution_priority">Priority:</label>
              <select id="resolution_priority" class="form-control">
                <option value="">Select Priority</option>
              </select>
            </div>
          </div>
          <div class="text-right" style="margin-top: 10px;">
            <button type="button" class="btn btn-sm btn-default" id="clear_resolution_time">Clear All</button>
          </div>
          <small class="text-muted">Leave empty to use auto-calculated time based on service settings</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" id="save_resolution_time_btn" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>
            `;
            $('body').append(modalHtml);
            var $modal = $('#editResolutionTimeModal');
            console.log('Modal re-added to body, now found:', $modal.length);

            if (ticketIsClosed) {
                $('#save_resolution_time_btn').prop('disabled', true);
            }

            // Populate priorities in the select
            var $prioritySelect = $('#resolution_priority');
            var opts = '<option value="">Select Priority</option>';
            (PRIORITIES || []).forEach(function(p){
                opts += '<option value="' + p.id + '">' + p.name + '</option>';
            });
            $prioritySelect.html(opts);

            // Setup modal shown event - populate current value
            $modal.off('shown.bs.modal').on('shown.bs.modal', function(){
                var currentValue = '<?= !empty($ticket->approx_resolution_time) ? strtotime($ticket->approx_resolution_time) : '' ?>';
                if (currentValue) {
                    var dateObj = new Date(currentValue * 1000);
                    var dateStr = dateObj.toISOString().split('T')[0]; // YYYY-MM-DD
                    $('#resolution_date').val(dateStr);
                } else {
                    $('#resolution_date').val('');
                }
                $('#resolution_priority').val('<?= e((string)$ticket->priority) ?>');
            });

            // Clear button functionality
            $('#clear_resolution_time').off('click').on('click', function(){
                $('#resolution_date').val('');
                $('#resolution_time').val('');
                $('#resolution_priority').val('');
            });

            // Save button functionality
            $('#save_resolution_time_btn').off('click').on('click', function(){
                var dateValue = $('#resolution_date').val();

                // Validate mandatory date
                if (!dateValue) {
                    alert_float('danger', 'Date selection is mandatory for Approx. Resolution Time.');
                    return;
                }

                // Set to selected date with end of day time
                var newTime = dateValue + ' 23:59:00';

                var ticketId = <?= (int)$ticket->ticketid; ?>;
                var $btn = $(this).prop('disabled', true);

                // Prepare data
                var data = {
                    ticketid: ticketId,
                    approx_resolution_time: newTime,
                    priority: $('#resolution_priority').val()
                };

                $.post(admin_url + 'tickets/update_single_ticket_settings', data)
                    .done(function(resp){
                        try {
                            resp = JSON.parse(resp);
                        } catch(e) {
                            resp = {success: false};
                        }

                        if (resp && resp.success){
                            alert_float('success', 'Resolution time updated successfully');
                            location.reload(); // Reload page to update display
                            $modal.modal('hide');
                        } else {
                            var msg = (resp && resp.message) ? resp.message : 'Failed to update resolution time';
                            alert_float('danger', msg);
                        }
                    })
                    .fail(function(){
                        alert_float('danger', 'Failed to update resolution time');
                    })
                    .always(function(){
                        $btn.prop('disabled', false);
                    });
            });
        }

        initEditResolutionTimeModal();

        });
    });
</script>
