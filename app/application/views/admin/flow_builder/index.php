<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">
                                    <i class="fa fa-project-diagram text-primary"></i>
                                    <?php echo _l('flow_builder'); ?>
                                </h4>
                                <p class="text-muted mb-0 mt-1"><?php echo _l('flow_builder_description'); ?></p>
                            </div>
                            <div class="btn-group">
                                <a href="<?php echo admin_url('flow_builder/build'); ?>" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> <?php echo _l('new_flow'); ?>
                                </a>
                                <button type="button" class="btn btn-info" onclick="refreshFlows()">
                                    <i class="fa fa-refresh"></i> <?php echo _l('refresh'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><?php echo _l('flows'); ?></h5>
                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control" id="flow-search" placeholder="<?php echo _l('search_flows'); ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                </div>
                            </div>
                        </div>

                        <!-- Flows Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fa fa-tag mr-1"></i><?php echo _l('flow_name'); ?></th>
                                        <th><i class="fa fa-align-left mr-1"></i><?php echo _l('flow_description'); ?></th>
                                        <th><i class="fa fa-calendar mr-1"></i><?php echo _l('created_at'); ?></th>
                                        <th><i class="fa fa-user mr-1"></i><?php echo _l('created_by'); ?></th>
                                        <th><i class="fa fa-toggle-on mr-1"></i><?php echo _l('flow_status'); ?></th>
                                        <th><i class="fa fa-cogs mr-1"></i><?php echo _l('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="flows-table-body">
                                    <?php if (!empty($flows)): ?>
                                        <?php foreach ($flows as $flow): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($flow['name']); ?></td>
                                                <td><?php echo htmlspecialchars($flow['description'] ?? ''); ?></td>
                                                <td><?php echo _dt($flow['created_at']); ?></td>
                                                <td><?php echo get_staff_full_name($flow['created_by']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $flow['status'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $flow['status'] ? _l('active') : _l('inactive'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?php echo admin_url('flow_builder/build/' . $flow['id']); ?>"
                                                           class="btn btn-primary" title="<?php echo _l('edit'); ?>">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-success" onclick="executeFlow(<?php echo $flow['id']; ?>)"
                                                                title="<?php echo _l('run_flow'); ?>">
                                                            <i class="fa fa-play"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-warning" onclick="duplicateFlow(<?php echo $flow['id']; ?>)"
                                                                title="<?php echo _l('duplicate_flow'); ?>">
                                                            <i class="fa fa-copy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger" onclick="deleteFlow(<?php echo $flow['id']; ?>)"
                                                                title="<?php echo _l('delete_flow'); ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="fa fa-inbox fa-3x mb-3"></i>
                                                <p><?php echo _l('no_flows_found'); ?></p>
                                                <a href="<?php echo admin_url('flow_builder/build'); ?>" class="btn btn-primary">
                                                    <i class="fa fa-plus"></i> <?php echo _l('create_first_flow'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div id="flows-pagination" class="mt-3">
                            <?php if (!empty($flows) && count($flows) >= 20): ?>
                                <nav aria-label="Flows pagination">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item disabled">
                                            <span class="page-link"><?php echo _l('showing'); ?> 1-<?php echo min(count($flows), 20); ?> <?php echo _l('of'); ?> <?php echo count($flows); ?> <?php echo _l('flows'); ?></span>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var loaders = document.querySelectorAll('.dt-loader');
    for (var i = 0; i < loaders.length; i++) {
        loaders[i].parentNode.removeChild(loaders[i]);
    }
    var wrappers = document.querySelectorAll('.table-loading');
    for (var j = 0; j < wrappers.length; j++) {
        wrappers[j].classList.remove('table-loading');
    }
    var lightbox = document.getElementById('lightbox');
    if (lightbox && !lightbox.style.display) {
        lightbox.style.display = 'none';
    }
});

// Flow management functions
function refreshFlows() {
    location.reload();
}

function executeFlow(flowId) {
    if (confirm('<?php echo _l('are_you_sure_you_want_to_execute_this_flow'); ?>')) {
        $.ajax({
            url: admin_url + 'flow_builder/execute_flow/' + flowId,
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    alert_float('success', '<?php echo _l('flow_executed_successfully'); ?>');
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function() {
                alert_float('danger', '<?php echo _l('error_executing_flow'); ?>');
            }
        });
    }
}

function duplicateFlow(flowId) {
    if (confirm('<?php echo _l('are_you_sure_you_want_to_duplicate_this_flow'); ?>')) {
        $.ajax({
            url: admin_url + 'flow_builder/duplicate_flow/' + flowId,
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    alert_float('success', '<?php echo _l('flow_duplicated_successfully'); ?>');
                    location.reload();
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function() {
                alert_float('danger', '<?php echo _l('error_duplicating_flow'); ?>');
            }
        });
    }
}

function deleteFlow(flowId) {
    if (confirm('<?php echo _l('are_you_sure_you_want_to_delete_this_flow'); ?>')) {
        $.ajax({
            url: admin_url + 'flow_builder/delete_flow/' + flowId,
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    alert_float('success', '<?php echo _l('flow_deleted_successfully'); ?>');
                    location.reload();
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function() {
                alert_float('danger', '<?php echo _l('error_deleting_flow'); ?>');
            }
        });
    }
}

// Search functionality
document.getElementById('flow-search').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('#flows-table-body tr');

    tableRows.forEach(row => {
        if (row.cells.length === 1) return; // Skip "no data" row

        const flowName = row.cells[0].textContent.toLowerCase();
        const flowDescription = row.cells[1].textContent.toLowerCase();

        if (flowName.includes(searchTerm) || flowDescription.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Auto-refresh every 30 seconds
setInterval(function() {
    // Optionally refresh the flows list
    // refreshFlows();
}, 30000);
</script>

<?php init_tail(); ?>
</body>
</html>
