<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2">
                    <a href="#" onclick="new_division(); return false;" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        New Division
                    </a>
                    <a href="<?php echo admin_url('divisions/bulk_upload'); ?>" class="btn btn-info">
                        <i class="fa fa-upload tw-mr-1"></i>
                        <?php echo _l('bulk_upload_divisions'); ?>
                    </a>
                </div>

                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php render_datatable([
                            _l('id'),
                            'Name',
                            _l('options'),
                        ], 'divisions'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="division" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?= form_open(admin_url('divisions/division'), ['id' => 'division-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title">Edit Division</span>
                    <span class="add-title">New Division</span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?= render_input('name', 'Division Name'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?= _l('submit'); ?></button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-divisions', window.location.href, [2], [2], undefined, [1, 'asc']);
        appValidateForm($('#division-form'), {
            name: 'required',
        }, manage_divisions);

        $('#division').on('hidden.bs.modal', function() {
            $('#additional').html('');
            $('#division input[type="text"]').val('');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });
    });

    function manage_divisions(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
            }
            $('.table-divisions').DataTable().ajax.reload();
            $('#division').modal('hide');
        }).fail(function(data) {
            var error = JSON.parse(data.responseText);
            alert_float('danger', error.message || 'Something went wrong');
        });
        return false;
    }

    function new_division() {
        $('#division').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_division(invoker, id) {
        $('#additional').html('');
        $('#additional').append(hidden_input('id', id));
        $('#division input[name="name"]').val($(invoker).data('name'));
        $('#division').modal('show');
        $('.add-title').addClass('hide');
    }
</script>
</body>

</html>
