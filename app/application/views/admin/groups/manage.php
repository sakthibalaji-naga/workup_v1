<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2">
                    <a href="#" onclick="new_group(); return false;" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?= _l('new_group'); ?>
                    </a>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('id'),
                            _l('group_name'),
                            _l('group_leader'),
                            _l('division'),
                            _l('department'),
                            _l('sub_department'),
                            _l('group_members_count'), // Or list names
                            _l('options'),
                        ], 'groups'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="group" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?= form_open(admin_url('groups/group'), ['id' => 'group-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span
                        class="edit-title"><?= _l('edit_group'); ?></span>
                    <span
                        class="add-title"><?= _l('new_group'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= render_input('name', 'group_name'); ?>
                        <?= render_select('leader_id', [], ['staffid','firstname,lastname'], 'group_leader', '', ['data-live-search' => true]); ?>
                        <?= render_input('division', 'division', '', '', ['readonly' => true, 'placeholder' => 'Division']); ?>
                        <?= render_input('department', 'department', '', '', ['readonly' => true, 'placeholder' => 'Department']); ?>
                        <?= render_input('sub_department', 'sub_department', '', '', ['readonly' => true, 'placeholder' => 'Sub Department']); ?>
                        <?= render_select('members[]', [], ['staffid','firstname,lastname'], 'group_members', '', ['multiple' => true, 'data-live-search' => true]); ?>
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
    var $leaderSelect = $('#group-form select[name="leader_id"]');
    var $membersSelect = $('#group-form select[name="members[]"]');
    var $submitBtn = $('#group-form button[type="submit"]');
    var $nameInput = $('#group-form input[name="name"]');

    initDataTable('.table-groups', window.location.href, [], [], undefined, [1, 'asc']);

    appValidateForm($('#group-form'), {
        name: 'required',
        leader_id: 'required'
    }, manage_groups);

    $('#group').on('hidden.bs.modal', function(event) {
        $('#group input[name="name"]').val('');
        $leaderSelect.selectpicker('val', '');
        $membersSelect.selectpicker('val', '');
        $('.add-title').removeClass('hide');
        $('.edit-title').removeClass('hide');
        $('#group-form input[name="id"]').remove();
    });

    var isEditMode = false;
    var editData = {};

    // Populate leaders on modal open
    $('#group').on('shown.bs.modal', function() {
        if ($leaderSelect.html() === '<option value=""></option>') {
            $.get(admin_url + 'groups/get_staff', function(response) {
                response = JSON.parse(response);
                var options = '<option value=""></option>';
                $.each(response, function(i, staff) {
                    options += '<option value="' + staff.value + '">' + staff.label + '</option>';
                });
                $leaderSelect.html(options).selectpicker('refresh');
                // If editing, set leader
                if (isEditMode && editData.leader_id) {
                    $leaderSelect.selectpicker('val', editData.leader_id);
                    populate_members(editData.leader_id);
                }
            });
        } else {
            if (isEditMode && editData.leader_id) {
                $leaderSelect.selectpicker('val', editData.leader_id);
                populate_members(editData.leader_id);
            }
        }
    });

    // When leader changes, populate members and get leader details
    $leaderSelect.on('changed.bs.select', function() {
        var leader_id = $(this).val();
        if (leader_id) {
            populate_leader_details(leader_id);
            populate_members(leader_id);
        }
        checkFormValidity();
    });

    $nameInput.on('input', checkFormValidity);

    function checkFormValidity() {
        var name = $nameInput.val().trim();
        var leader = $leaderSelect.val();
        if (name !== '' && leader !== '') {
            $submitBtn.prop('disabled', false);
        } else {
            $submitBtn.prop('disabled', true);
        }
    }

    function populate_leader_details(leader_id) {
        $.post(admin_url + 'groups/get_leader_details', {leader_id: leader_id})
            .done(function(response) {
                response = JSON.parse(response);
                $('#group input[name="division"]').val(response.division);
                $('#group input[name="department"]').val(response.department);
                $('#group input[name="sub_department"]').val(response.sub_department);
            });
    }

    function populate_members(leader_id) {
        var group_id = $('#group-form input[name="id"]').val() || 0;
        $.post(admin_url + 'groups/staff_by_leader_department', {leader_id: leader_id, group_id: group_id})
            .done(function(response) {
                $membersSelect.html('<option value=""></option>');
                response = JSON.parse(response);
                $.each(response, function(i, staff) {
                    $membersSelect.append('<option value="' + staff.value + '">' + staff.label + '</option>');
                });
                $membersSelect.selectpicker('refresh');
                if (isEditMode && editData.members) {
                    $membersSelect.selectpicker('val', editData.members);
                    isEditMode = false; // Reset
                }
            });
    }

    function manage_groups(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
                $('.table-groups').DataTable().ajax.reload();
                $('#group').modal('hide');
            } else {
                alert_float('danger', 'Error saving group');
            }
        }).fail(function(data) {
            var error = JSON.parse(data.responseText);
            alert_float('danger', error.message);
        });
        return false;
    }

    function new_group() {
        isEditMode = false;
        editData = {};
        $submitBtn.prop('disabled', true);
        $('#group').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_group(invoker, id) {
        isEditMode = true;
        $.get(admin_url + 'groups/get_group/' + id, function(response) {
            response = JSON.parse(response);
            editData = response;
            editData.members = editData.members || [];
            $('#group input[name="name"]').val(editData.name);
            $('#group input[name="division"]').val(editData.division_name);
            $('#group input[name="department"]').val(editData.department_name);
            $('#group input[name="sub_department"]').val(editData.sub_department_name);
            $('#group').modal('show');
            add_hidden_input('id', id);
            $('.add-title').addClass('hide');
            checkFormValidity(); // Enable save for edit
        });
    }

    function add_hidden_input(name, value) {
        var input = $('<input>').attr({
            type: 'hidden',
            name: name,
            value: value
        });
        $('#group-form').append(input);
    }
</script>
</body>
</html>
