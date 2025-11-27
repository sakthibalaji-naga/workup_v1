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
                                    <i class="fa fa-list-alt text-primary"></i>
                                    <?php echo _l('flow_execution_logs'); ?>
                                </h4>
                                <p class="text-muted mb-0 mt-1"><?php echo _l('monitor_flow_execution_logs_and_responses'); ?></p>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-info" onclick="refreshLogs()">
                                    <i class="fa fa-refresh"></i> <?php echo _l('refresh'); ?>
                                </button>
                                <button type="button" class="btn btn-success" onclick="exportLogs()">
                                    <i class="fa fa-download"></i> <?php echo _l('export'); ?>
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
                        <!-- Search and Filter -->
                        <div class="search-container mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-tag mr-1"></i><?php echo _l('flow_name'); ?>
                                                </label>
                                                <select class="form-control form-control-sm" id="search_flow">
                                                    <option value=""><?php echo _l('all_flows'); ?></option>
                                                    <?php if (!empty($flows)): ?>
                                                        <?php foreach ($flows as $flow): ?>
                                                            <option value="<?php echo $flow['id']; ?>"><?php echo htmlspecialchars($flow['name']); ?></option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-toggle-on mr-1"></i><?php echo _l('execution_status'); ?>
                                                </label>
                                                <select class="form-control form-control-sm" id="search_status">
                                                    <option value=""><?php echo _l('all_statuses'); ?></option>
                                                    <option value="success"><?php echo _l('success'); ?></option>
                                                    <option value="error"><?php echo _l('failed'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-user mr-1"></i><?php echo _l('executed_by'); ?>
                                                </label>
                                                <select class="form-control form-control-sm" id="search_executed_by">
                                                    <option value=""><?php echo _l('all_users'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-calendar mr-1"></i><?php echo _l('date_range'); ?>
                                                </label>
                                                <input type="date" class="form-control form-control-sm" id="search_date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <button type="button" class="btn btn-info btn-sm mr-2" onclick="performSearch()">
                                                <i class="fa fa-search mr-1"></i><?php echo _l('search'); ?>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSearch()">
                                                <i class="fa fa-times mr-1"></i><?php echo _l('clear'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Logs Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fa fa-calendar mr-1"></i><?php echo _l('executed_at'); ?></th>
                                        <th><i class="fa fa-tag mr-1"></i><?php echo _l('flow_name'); ?></th>
                                        <th><i class="fa fa-user mr-1"></i><?php echo _l('executed_by'); ?></th>
                                        <th><i class="fa fa-clock-o mr-1"></i><?php echo _l('execution_time'); ?></th>
                                        <th><i class="fa fa-toggle-on mr-1"></i><?php echo _l('execution_status'); ?></th>
                                        <th><i class="fa fa-comment mr-1"></i>Log Message</th>
                                        <th><i class="fa fa-info-circle mr-1"></i><?php echo _l('result'); ?></th>
                                        <th><i class="fa fa-cogs mr-1"></i><?php echo _l('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="logs-table-body">
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                            <?php
                                                $flow_name = 'Unknown Flow';
                                                foreach ($flows as $flow) {
                                                    if ($flow['id'] == $log['flow_id']) {
                                                        $flow_name = $flow['name'];
                                                        break;
                                                    }
                                                }
                                                $executed_by_name = get_staff_full_name($log['executed_by']);
                                                $logMessage = isset($log['log_message']) ? trim($log['log_message']) : '';
                                                $logPayload = $log;
                                                $logPayload['flow_name_display'] = $flow_name;
                                                $logPayload['executed_by_name'] = $executed_by_name;
                                                $logPayload['log_message'] = $logMessage;
                                            ?>
                                            <tr>
                                                <td><?php echo _dt($log['executed_at']); ?></td>
                                                <td><?php echo htmlspecialchars($flow_name); ?></td>
                                                <td><?php echo htmlspecialchars($executed_by_name); ?></td>
                                                <td><?php echo number_format($log['execution_time'], 4); ?>s</td>
                                                <td>
                                                    <span class="badge badge-<?php echo $log['status'] === 'success' ? 'success' : 'danger'; ?>">
                                                        <?php echo $log['status'] === 'success' ? _l('success') : _l('failed'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($logMessage !== ''): ?>
                                                        <span class="d-inline-block text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($logMessage); ?>">
                                                            <?php echo htmlspecialchars($logMessage); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">--</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="text-primary" style="cursor: pointer;" onclick="showResultModal(<?php echo htmlspecialchars(json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>)">
                                                        <?php echo _l('view_details'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteLog(<?php echo $log['id']; ?>)"
                                                            title="<?php echo _l('delete'); ?>">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                <i class="fa fa-inbox fa-3x mb-3"></i>
                                                <p><?php echo _l('no_logs_found'); ?></p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div id="logs-pagination" class="mt-3">
                            <?php if (!empty($logs) && count($logs) >= 50): ?>
                                <nav aria-label="Logs pagination">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item disabled">
                                            <span class="page-link"><?php echo _l('showing'); ?> 1-<?php echo min(count($logs), 50); ?> <?php echo _l('of'); ?> <?php echo count($logs); ?> <?php echo _l('logs'); ?></span>
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

<!-- Result Details Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="resultModalLabel"><?php echo _l('execution_details'); ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><?php echo _l('execution_info'); ?></h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong><?php echo _l('executed_at'); ?>:</strong></td>
                                <td id="modal-executed-at">-</td>
                            </tr>
                            <tr>
                                <td><strong><?php echo _l('flow_name'); ?>:</strong></td>
                                <td id="modal-flow-name">-</td>
                            </tr>
                            <tr>
                                <td><strong><?php echo _l('executed_by'); ?>:</strong></td>
                                <td id="modal-executed-by">-</td>
                            </tr>
                            <tr>
                                <td><strong><?php echo _l('execution_time'); ?>:</strong></td>
                                <td id="modal-execution-time">-</td>
                            </tr>
                            <tr>
                                <td><strong><?php echo _l('execution_status'); ?>:</strong></td>
                                <td id="modal-status">-</td>
                            </tr>
                            <tr>
                                <td><strong>Log Message:</strong></td>
                                <td id="modal-log-message">--</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><?php echo _l('result_data'); ?></h6>
                        <pre id="modal-result" style="max-height: 300px; overflow-y: auto; font-size: 12px;"></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary" onclick="copyResultToClipboard()"><?php echo _l('copy_to_clipboard'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
// Log management functions
function refreshLogs() {
    location.reload();
}

function exportLogs() {
    const searchData = {
        flow_id: document.getElementById('search_flow').value,
        status: document.getElementById('search_status').value,
        executed_by: document.getElementById('search_executed_by').value,
        date: document.getElementById('search_date').value
    };

    $.ajax({
        url: admin_url + 'flow_builder/export_logs',
        type: 'POST',
        data: searchData,
        success: function(response) {
            if (response.success) {
                alert_float('success', '<?php echo _l('export_initiated_successfully'); ?>');
            } else {
                alert_float('danger', response.message || '<?php echo _l('export_failed'); ?>');
            }
        },
        error: function() {
            alert_float('danger', '<?php echo _l('export_failed'); ?>');
        }
    });
}

function deleteLog(logId) {
    if (confirm('<?php echo _l('are_you_sure_you_want_to_delete_this_log'); ?>')) {
        $.ajax({
            url: admin_url + 'flow_builder/delete_log/' + logId,
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    alert_float('success', '<?php echo _l('log_deleted_successfully'); ?>');
                    location.reload();
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function() {
                alert_float('danger', '<?php echo _l('error_deleting_log'); ?>');
            }
        });
    }
}

function showResultModal(log) {
    const executedAtRaw = log.executed_at || '';
    let executedAtDisplay = executedAtRaw || '-';
    if (executedAtRaw) {
        const parsedDate = new Date(executedAtRaw);
        if (!Number.isNaN(parsedDate.getTime())) {
            executedAtDisplay = parsedDate.toLocaleString();
        }
    }

    document.getElementById('modal-executed-at').textContent = executedAtDisplay;
    document.getElementById('modal-flow-name').textContent = log.flow_name_display || log.flow_name || 'Unknown Flow';
    document.getElementById('modal-executed-by').textContent = log.executed_by_name || 'Unknown User';
    const executionTimeValue = log.execution_time ? parseFloat(log.execution_time) : 0;
    document.getElementById('modal-execution-time').textContent = executionTimeValue.toFixed(4) + 's';
    document.getElementById('modal-status').innerHTML = '<span class="badge badge-' + (log.status === 'success' ? 'success' : 'danger') + '">' + (log.status === 'success' ? '<?php echo _l('success'); ?>' : '<?php echo _l('failed'); ?>') + '</span>';
    document.getElementById('modal-log-message').textContent = log.log_message && log.log_message.trim() !== '' ? log.log_message : '--';

    const resultElement = document.getElementById('modal-result');
    if (log.result) {
        try {
            const result = JSON.parse(log.result);
            resultElement.textContent = JSON.stringify(result, null, 2);
        } catch (e) {
            resultElement.textContent = log.result;
        }
    } else {
        resultElement.textContent = '';
    }

    $('#resultModal').modal('show');
}

function copyResultToClipboard() {
    const resultText = document.getElementById('modal-result').textContent;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(resultText).then(function() {
            alert_float('success', '<?php echo _l('copied_to_clipboard'); ?>');
        }).catch(function(err) {
            fallbackCopyTextToClipboard(resultText);
        });
    } else {
        fallbackCopyTextToClipboard(resultText);
    }
}

function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        var successful = document.execCommand('copy');
        if (successful) {
            alert_float('success', '<?php echo _l('copied_to_clipboard'); ?>');
        } else {
            alert_float('danger', '<?php echo _l('failed_to_copy'); ?>');
        }
    } catch (err) {
        alert_float('danger', '<?php echo _l('failed_to_copy'); ?>');
    }

    textArea.remove();
}

function performSearch() {
    const searchData = {
        flow_id: document.getElementById('search_flow').value,
        status: document.getElementById('search_status').value,
        executed_by: document.getElementById('search_executed_by').value,
        date: document.getElementById('search_date').value
    };

    // Filter table rows based on search criteria
    filterLogsTable(searchData);
}

function clearSearch() {
    document.getElementById('search_flow').value = '';
    document.getElementById('search_status').value = '';
    document.getElementById('search_executed_by').value = '';
    document.getElementById('search_date').value = '';

    // Show all rows
    document.querySelectorAll('#logs-table-body tr').forEach(row => {
        if (row.cells.length > 1) { // Skip "no data" row
            row.style.display = '';
        }
    });
}

function filterLogsTable(searchData) {
    const tableRows = document.querySelectorAll('#logs-table-body tr');

    tableRows.forEach(row => {
        if (row.cells.length === 1) return; // Skip "no data" row

        let showRow = true;

        // Filter by flow
        if (searchData.flow_id) {
            const flowName = row.cells[1].textContent;
            const flowOption = document.querySelector(`#search_flow option[value="${searchData.flow_id}"]`);
            if (flowOption && flowName !== flowOption.textContent) {
                showRow = false;
            }
        }

        // Filter by status
        if (searchData.status) {
            const statusBadge = row.cells[4].querySelector('.badge');
            const statusText = statusBadge ? statusBadge.textContent : '';
            if (searchData.status === 'success' && statusText !== '<?php echo _l('success'); ?>') {
                showRow = false;
            } else if (searchData.status === 'error' && statusText !== '<?php echo _l('failed'); ?>') {
                showRow = false;
            }
        }

        // Filter by executed by
        if (searchData.executed_by) {
            const executedBy = row.cells[2].textContent;
            // This would need to be implemented with actual user data
        }

        // Filter by date
        if (searchData.date) {
            const executedAt = row.cells[0].textContent;
            const logDate = new Date(executedAt).toISOString().split('T')[0];
            if (logDate !== searchData.date) {
                showRow = false;
            }
        }

        row.style.display = showRow ? '' : 'none';
    });
}

// Auto-refresh every 30 seconds
setInterval(function() {
    // Optionally refresh the logs
    // refreshLogs();
}, 30000);
</script>

<?php init_tail(); ?>
</body>
</html>
