<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2">
                    <div class="_buttons">
                        <?php if (staff_can('create', 'approval_flow')) { ?>
                        <a href="<?php echo admin_url('approval_flow/create'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?= _l('new_approval_flow'); ?>
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Bootstrap Tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#all_approval_flows" aria-controls="all_approval_flows" role="tab" data-toggle="tab">
                                    <?= _l('all_approval_flows'); ?>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#my_influence" aria-controls="my_influence" role="tab" data-toggle="tab">
                                    <?= _l('my_influence'); ?>
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- All Approval Flows Tab -->
                            <div role="tabpanel" class="tab-pane active" id="all_approval_flows">
                                <div class="panel-table-full">
                                    <?php $this->load->view('admin/approval_flow/_table', [
                                        'filter' => 'all',
                                        'table_identifier' => 'approval_flow_all',
                                    ]); ?>
                                </div>
                            </div>

                            <!-- My Influence Tab -->
                            <div role="tabpanel" class="tab-pane" id="my_influence">
                                <div class="panel-table-full">
                                    <?php $this->load->view('admin/approval_flow/_table', [
                                        'filter' => 'my_influence',
                                        'table_identifier' => 'approval_flow_my_influence',
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        // Initialize DataTables for each tab with unique identifiers
        var approvalFlowTables = {
            all: initDataTable('#approval_flow_all', admin_url + 'approval_flow/table?filter=all'),
            myInfluence: initDataTable('#approval_flow_my_influence', admin_url + 'approval_flow/table?filter=my_influence'),
        };

        // Handle tab switching to ensure proper DataTable initialization
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var targetTab = $(e.target).attr('href');
            if (targetTab === '#all_approval_flows' && approvalFlowTables.all) {
                approvalFlowTables.all.columns.adjust().draw(false);
            }
            if (targetTab === '#my_influence' && approvalFlowTables.myInfluence) {
                approvalFlowTables.myInfluence.columns.adjust().draw(false);
            }
        });

        // Handle status toggle clicks
        $(document).on('click', '.status-toggle', function() {
            var $toggle = $(this);
            var url = $toggle.data('switch-url');
            var currentStatus = parseInt($toggle.data('status'));

            // Show loading state
            $toggle.html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        // Toggle the status
                        var newStatus = currentStatus === 1 ? 0 : 1;
                        var newStatusClass = newStatus === 1 ? 'success' : 'danger';
                        var newStatusText = newStatus === 1 ? 'Active' : 'Inactive';
                        var newStatusIcon = newStatus === 1 ? 'fa-toggle-on' : 'fa-toggle-off';

                        // Update the toggle appearance
                        $toggle.removeClass('label-success label-danger')
                               .addClass('label-' + newStatusClass)
                               .html('<i class="fa ' + newStatusIcon + '"></i> ' + newStatusText)
                               .data('status', newStatus);

                        // Show success message using the standard CRM notification
                        var statusMessage = newStatus === 1 ?
                            '<?= _l('approval_flow_activated_successfully'); ?>' :
                            '<?= _l('approval_flow_deactivated_successfully'); ?>' ;

                        alert_float('success', statusMessage);
                    } else {
                        // Revert on error
                        var originalStatusClass = currentStatus === 1 ? 'success' : 'danger';
                        var originalStatusText = currentStatus === 1 ? 'Active' : 'Inactive';
                        var originalStatusIcon = currentStatus === 1 ? 'fa-toggle-on' : 'fa-toggle-off';

                        $toggle.removeClass('label-success label-danger')
                               .addClass('label-' + originalStatusClass)
                               .html('<i class="fa ' + originalStatusIcon + '"></i> ' + originalStatusText);

                        var errorMsg = (response && response.message) ? response.message : 'Error updating status. Please try again.';
                        alert_float('danger', errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    // Revert on error
                    var originalStatusClass = currentStatus === 1 ? 'success' : 'danger';
                    var originalStatusText = currentStatus === 1 ? 'Active' : 'Inactive';
                    var originalStatusIcon = currentStatus === 1 ? 'fa-toggle-on' : 'fa-toggle-off';

                    $toggle.removeClass('label-success label-danger')
                           .addClass('label-' + originalStatusClass)
                           .html('<i class="fa ' + originalStatusIcon + '"></i> ' + originalStatusText);

                    console.log('AJAX Error:', xhr.responseText, status, error);
                    alert_float('danger', 'Error updating status. Please check console for details.');
                }
            });
        });
    });
</script>
</body>
</html>
