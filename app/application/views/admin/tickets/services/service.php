<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="ticket-service-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?= form_open(admin_url('tickets/service'), ['id' => 'ticket-service-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span
                        class="edit-title"><?= _l('ticket_service_edit'); ?></span>
                    <span
                        class="add-title"><?= _l('new_service'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?= render_input('name', 'service_add_edit_name'); ?>
                        <?= render_select('applicationid', $applications ?? [], ['id','name'], 'Application', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
                        <?php // Division, Department, Sub Department selectors ?>
                        <?= render_select('divisionid', $divisions ?? [], ['divisionid','name'], 'Division', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
                        <?php
                            $parentDepartments = isset($departments) ? array_values(array_filter($departments, function($d){
                                return empty($d['parent_department']) || (int)$d['parent_department'] === 0;
                            })) : [];
                        ?>
                        <?= render_select('departmentid', $parentDepartments, ['departmentid','name'], _l('department'), '', ['data-none-selected-text' => _l('dropdown_non_selected_tex')]); ?>
                        <?= render_select('sub_department', [], ['departmentid','name'], 'Sub Department', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'disabled' => true]); ?>
                        <div class="form-group">
                            <label for="staff_type">Staff Selection Type</label>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="staff_type_department" name="staff_type" value="department" checked>
                                <label for="staff_type_department">Department Staff</label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="staff_type_all" name="staff_type" value="all">
                                <label for="staff_type_all">Outside Staff</label>
                            </div>
                        </div>
                        <?= render_select('responsible', [], ['staffid','name'], 'Responsible User', '', ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'data-live-search' => true, 'disabled' => true]); ?>
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
<script>
    // Build a map of department -> parent_department for resolving parent from selected sub department
    <?php
        $deptParentMap = [];
        if (isset($departments) && is_array($departments)) {
            foreach ($departments as $d) {
                $deptParentMap[$d['departmentid']] = isset($d['parent_department']) ? (int) $d['parent_department'] : 0;
            }
        }
    ?>
    var deptParentMap = <?= json_encode($deptParentMap); ?>;
    window.addEventListener('load', function() {
        // Ensure jQuery is available before using $
        function ready(fn){ if (window.jQuery && typeof $ === 'function') { fn(); } else { setTimeout(function(){ ready(fn); }, 50); } }
        ready(function(){
            appValidateForm($('#ticket-service-form'), { name: 'required' }, manage_ticket_services);
            $('#ticket-service-modal').on('hidden.bs.modal', function(event) {
                $('#additional').html('');
                $('#ticket-service-modal input[name="name"]').val('');
                $('#ticket-service-modal select[name="divisionid"]').selectpicker('val','');
                $('#ticket-service-modal select[name="departmentid"]').selectpicker('val','');
                var $sub = $('#ticket-service-modal select[name="sub_department"]');
                $sub.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh');
                // Reset radio buttons to default
                $('#ticket-service-modal input[name="staff_type"][value="department"]').prop('checked', true);
                $('.add-title').removeClass('hide');
                $('.edit-title').removeClass('hide');
            });

            // Bind dependent sub-department loader
            $('#ticket-service-modal').on('changed.bs.select', 'select[name="departmentid"]', retrieve_service_sub_departments);
        });
    });

    function manage_ticket_services(form) {
        var data = $(form).serialize();
        var url = form.action;
        var ticketArea = $('body').hasClass('ticket');
        if (ticketArea) {
            data += '&ticket_area=true';
        }
        $.post(url, data).done(function(response) {
            if (ticketArea) {
                response = JSON.parse(response);
                if (response.success == true && typeof(response.id) != 'undefined') {
                    var group = $('select#service');
                    var attrs = '';
                    if (typeof response.divisionid !== 'undefined' && response.divisionid !== null) attrs += ' data-divisionid="'+ response.divisionid +'"';
                    if (typeof response.departmentid !== 'undefined' && response.departmentid !== null) attrs += ' data-departmentid="'+ response.departmentid +'"';
                    if (typeof response.sub_department !== 'undefined' && response.sub_department !== null) attrs += ' data-sub-department="'+ response.sub_department +'" data-sub_department="'+ response.sub_department +'"';
                    var optHtml = '<option value="' + response.id + '"'+ attrs + '>' + response.name + '</option>';
                    group.find('option:first').after(optHtml);
                    // Update master services list in Add Ticket page if available
                    if (window.ALL_SERVICE_OPTIONS && Array.isArray(window.ALL_SERVICE_OPTIONS)) {
                        window.ALL_SERVICE_OPTIONS.push({
                            id: String(response.id),
                            name: response.name,
                            divisionid: response.divisionid ? String(response.divisionid) : '',
                            departmentid: response.departmentid ? String(response.departmentid) : '',
                            sub_department: response.sub_department ? String(response.sub_department) : ''
                        });
                    }
                    group.selectpicker('val', response.id);
                    group.selectpicker('refresh');
                }
                $('#ticket-service-modal').modal('hide');
            } else {
                window.location.reload();
            }
        });
        return false;
    }

    function retrieve_service_sub_departments(selectedId, selectedStaffId) {
        // jQuery event compatibility: if called as event handler, first arg is event
        if (selectedId && typeof selectedId === 'object' && selectedId.preventDefault) {
            selectedId = null;
        }
        var parentId = $('#ticket-service-modal select[name="departmentid"]').selectpicker('val');
        var $sub = $('#ticket-service-modal select[name="sub_department"]');
        if (!parentId) {
            $sub.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh');
            // Reset responsible when no department is selected
            reset_responsible_select();
            return;
        }
        $.post(admin_url + 'tickets/sub_departments', { parent_id: parentId }).done(function(resp){
            var data = [];
            try { data = JSON.parse(resp); } catch(e) { data = []; }
            var options = '<option value=""></option>';
            if (Array.isArray(data) && data.length) {
                data.forEach(function(d){ options += '<option value="'+ (d.departmentid||'') +'">'+ (d.name||'') +'</option>'; });
                $sub.prop('disabled', false);
            } else {
                $sub.prop('disabled', true);
            }
            $sub.html(options).selectpicker('refresh');
            if (selectedId) {
                $sub.selectpicker('val', selectedId);
            }
            // Whenever sub departments are refreshed, also refresh responsible staff
            retrieve_responsible_staff(selectedStaffId);
        }).fail(function(){ $sub.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh'); });
    }

    function reset_responsible_select() {
        var $resp = $('#ticket-service-modal select[name="responsible"]');
        $resp.html('<option value=""></option>').prop('disabled', true).selectpicker('refresh');
    }

    function retrieve_responsible_staff(selectedStaffId) {
        // jQuery event compatibility: if called as event handler, first arg is event
        if (selectedStaffId && typeof selectedStaffId === 'object' && selectedStaffId.preventDefault) {
            selectedStaffId = null;
        }
        var departmentId = $('#ticket-service-modal select[name="departmentid"]').selectpicker('val');
        var subDepartmentId = $('#ticket-service-modal select[name="sub_department"]').selectpicker('val');
        var staffType = $('#ticket-service-modal input[name="staff_type"]:checked').val();
        var $resp = $('#ticket-service-modal select[name="responsible"]');

        // For department staff, require department/sub-department selection
        if (staffType === 'department' && !departmentId && !subDepartmentId) {
            reset_responsible_select();
            return;
        }

        $.post(admin_url + 'tickets/service_responsible_staff', {
            department_id: departmentId || '',
            sub_department_id: subDepartmentId || '',
            staff_type: staffType || 'department'
        }).done(function(resp){
            var data = [];
            try { data = JSON.parse(resp); } catch(e) { data = []; }
            var options = '<option value=""></option>';
            if (Array.isArray(data) && data.length) {
                data.forEach(function(s){ options += '<option value="'+ (s.staffid||'') +'">'+ (s.name||'') +'</option>'; });
                $resp.prop('disabled', false);
            } else {
                $resp.prop('disabled', true);
            }
            $resp.html(options).selectpicker('refresh');
            if (selectedStaffId) {
                $resp.selectpicker('val', selectedStaffId);
            }
        }).fail(function(){ reset_responsible_select(); });
    }

    // Binding moved inside window load to ensure $ is available

    function new_service() {
        $('#ticket-service-modal').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_service(invoker, id) {
        var $inv = $(invoker);
        var name = $inv.data('name');
        var divisionid = $inv.data('divisionid');
        var departmentid = $inv.data('departmentid');
        var sub = $inv.data('sub_department');
        var responsible = $inv.data('responsible');
        var staff_type = $inv.data('staff_type') || 'department'; // Default to department if not set
        // Ensure name fallback from anchor if missing
        if (typeof name === 'undefined' || name === null || name === '') {
            var $nameA = $inv.closest('tr').find('a.tw-font-medium').first();
            if ($nameA.length) {
                name = $nameA.data('name') || $nameA.text().trim();
            }
        }
        // Fallback: get data attrs from the name anchor in the same row
        if (typeof divisionid === 'undefined' || typeof departmentid === 'undefined' || typeof sub === 'undefined') {
            var $nameA = $inv.closest('tr').find('a.tw-font-medium').first();
            if ($nameA.length) {
                if (typeof divisionid === 'undefined') divisionid = $nameA.data('divisionid');
                if (typeof departmentid === 'undefined') departmentid = $nameA.data('departmentid');
                if (typeof sub === 'undefined') sub = $nameA.data('sub_department');
                if (typeof staff_type === 'undefined') staff_type = $nameA.data('staff_type') || 'department';
            }
        }
        $('#additional').append(hidden_input('id', id));
        $('#ticket-service-modal input[name="name"]').val(name);
        $('#ticket-service-modal select[name="divisionid"]').selectpicker('val', divisionid || '');
        // If department is not set but sub department exists, resolve parent from map
        if ((!departmentid || departmentid === '' || departmentid === '0') && sub) {
            var resolvedParent = deptParentMap[String(sub)] || 0;
            if (resolvedParent) {
                departmentid = resolvedParent;
            }
        }
        $('#ticket-service-modal select[name="departmentid"]').selectpicker('val', departmentid || '');
        // Set radio button state
        $('#ticket-service-modal input[name="staff_type"][value="' + staff_type + '"]').prop('checked', true);
        retrieve_service_sub_departments(sub || null, responsible || null);
        $('#ticket-service-modal').modal('show');
        $('.add-title').addClass('hide');
    }

    // Bind change events to refresh responsible staff (ensure jQuery is available)
    window.addEventListener('load', function() {
        if (window.jQuery && typeof $ === 'function') {
            $(document).on('change', '#ticket-service-modal select[name="departmentid"]', function(){
                retrieve_responsible_staff();
            });
            $(document).on('change', '#ticket-service-modal select[name="sub_department"]', function(){
                retrieve_responsible_staff();
            });
            $(document).on('change', '#ticket-service-modal input[name="staff_type"]', function(){
                retrieve_responsible_staff();
            });
        }
    });

    // Service status toggle functionality
    function toggle_service_status(serviceId, serviceName) {
        if (!serviceId) {
            alert('Invalid service ID');
            return;
        }

        // Show loading state
        var $btn = $('button[onclick*="toggle_service_status(' + serviceId + '"]');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: admin_url + 'tickets/toggle_service_status/' + serviceId,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.show_confirmation) {
                    // Show confirmation modal for services with linked tickets
                    show_deactivation_confirmation(serviceId, serviceName, response.linked_tickets);
                } else {
                    // Direct toggle successful
                    if (response.success) {
                        // Reload the table to show updated status
                        $('.table-services').DataTable().ajax.reload();
                        alert(response.message || 'Service status updated successfully');
                    } else {
                        alert(response.message || 'Failed to update service status');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('An error occurred while updating service status');
            },
            complete: function() {
                // Reset button state
                $btn.prop('disabled', false).html($btn.data('original-text') || 'Toggle');
            }
        });
    }

    // Show confirmation modal for service deactivation
    function show_deactivation_confirmation(serviceId, serviceName, linkedTickets) {
        // Create modal HTML
        var modalHtml = '<div class="modal fade" id="service-deactivation-modal" tabindex="-1" role="dialog">' +
            '<div class="modal-dialog" role="document">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<h4 class="modal-title">Confirm Service Deactivation</h4>' +
            '</div>' +
            '<div class="modal-body">' +
            '<p>Are you sure you want to deactivate the service "<strong>' + serviceName + '</strong>"?</p>' +
            '<div class="alert alert-warning">' +
            '<strong>Warning:</strong> This service is currently linked to the following tickets:' +
            '<ul>';

        // Add linked tickets to the list
        linkedTickets.forEach(function(ticket) {
            modalHtml += '<li>Ticket #' + ticket.ticket_number + ' - ' + ticket.subject + '</li>';
        });

        modalHtml += '</ul>' +
            '<p>Deactivating this service will not affect existing tickets, but it will no longer be available for new tickets.</p>' +
            '</div>' +
            '</div>' +
            '<div class="modal-footer">' +
            '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' +
            '<button type="button" class="btn btn-danger" onclick="force_deactivate_service(' + serviceId + ')">Deactivate Anyway</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';

        // Remove existing modal if present
        $('#service-deactivation-modal').remove();

        // Add modal to body and show
        $('body').append(modalHtml);
        $('#service-deactivation-modal').modal('show');

        // Clean up modal when hidden
        $('#service-deactivation-modal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    // Force deactivate service (bypass confirmation)
    function force_deactivate_service(serviceId) {
        $('#service-deactivation-modal').modal('hide');

        $.ajax({
            url: admin_url + 'tickets/force_deactivate_service/' + serviceId,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Reload the table to show updated status
                    $('.table-services').DataTable().ajax.reload();
                    alert(response.message || 'Service deactivated successfully');
                } else {
                    alert(response.message || 'Failed to deactivate service');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('An error occurred while deactivating service');
            }
        });
    }
</script>
