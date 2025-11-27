<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2">
                    <a href="#" onclick="new_service(); return false;" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?= _l('new_service'); ?>
                    </a>
                </div>

                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                                                <?php render_datatable([
                            _l('id'),
                            _l('services_dt_name'),
                            'Application',
                            'Division',
                            _l('department'),
                            'Sub Department',
                            'Responsible User',
                            _l('status'),
                            _l('options'),
                        ], 'services'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/tickets/services/service'); ?>
<?php init_tail(); ?>
<script>
    $(function() {
        // Make only Options column (index 8) non-sortable/searchable
        initDataTable('.table-services', window.location.href, [8], [8], 'undefined', [1, 'asc']);
    });
</script>
</body>

</html>
