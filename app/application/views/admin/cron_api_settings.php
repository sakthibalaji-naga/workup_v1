<?php
defined('BASEPATH') or exit('No direct script access allowed');
init_head();
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body cron-api-settings-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">
                                    <i class="fa fa-clock-o text-primary"></i>
                                    <?php echo _l('Cron API Settings'); ?>
                                </h4>
                                <p class="text-muted mb-0 mt-1"><?php echo _l('Manage cron API calls with scheduling'); ?></p>
                            </div>
                            <div class="text-right">
                                <small class="text-muted"><?php echo _l('Last updated'); ?>: <?php echo date('M d, Y H:i'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body cron-apis-panel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <div class="panel-icon mr-3">
                                    <i class="fa fa-clock-o text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo _l('Cron APIs'); ?>
                                        <?php if (($cron_apis_pages ?? 1) > 1) { ?>
                                            <small class="text-muted"> <?php echo sprintf(_l('ticket_logs_pagination_label'), (int)($cron_apis_start ?? 0), (int)($cron_apis_end ?? 0), (int)($cron_apis_total ?? 0)); ?></small>
                                        <?php } ?>
                                    </h5>
                                    <small class="text-muted"><?php echo _l('Configure and manage cron API endpoints with scheduling'); ?></small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm add-cron-api-btn" data-toggle="modal" data-target="#addCronApiModal">
                                <i class="fa fa-plus mr-1"></i><?php echo _l('Add Cron API'); ?>
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped cron-apis-table table-responsive-custom" id="cron-apis-table-responsive">
                                <thead>
                                    <tr>
                                        <th class="min-w-150"><i class="fa fa-tag mr-1"></i><?php echo _l('Name'); ?></th>
                                        <th class="min-w-200"><i class="fa fa-link mr-1"></i><?php echo _l('API URL'); ?></th>
                                        <th class="min-w-120"><i class="fa fa-clock-o mr-1"></i><?php echo _l('Cron Schedule'); ?></th>
                                        <th class="min-w-80"><i class="fa fa-toggle-on mr-1"></i><?php echo _l('Status'); ?></th>
                                        <th class="min-w-150"><i class="fa fa-calendar mr-1"></i><?php echo _l('Last Run'); ?></th>
                                        <th class="min-w-150"><i class="fa fa-calendar mr-1"></i><?php echo _l('Next Run'); ?></th>
                                        <th class="min-w-120 text-center"><i class="fa fa-cogs mr-1"></i><?php echo _l('Actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="cron-apis-table">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fa fa-spinner fa-spin"></i> <?php echo _l('Loading cron APIs'); ?>...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination will be rendered by JavaScript -->
                        <div id="cron-apis-pagination-container" class="tw-mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cron API Logs Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body cron-api-logs-panel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <div class="panel-icon mr-3">
                                    <i class="fa fa-list-alt text-warning"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo _l('Cron API Logs'); ?>
                                        <?php if (($cron_apis_pages ?? 1) > 1) { ?>
                                            <small class="text-muted"> <?php echo sprintf(_l('ticket_logs_pagination_label'), (int)($cron_apis_start ?? 0), (int)($cron_apis_end ?? 0), (int)($cron_apis_total ?? 0)); ?></small>
                                        <?php } ?>
                                    </h5>
                                    <small class="text-muted"><?php echo _l('Monitor cron API execution logs and responses'); ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Search Form -->
                        <div class="search-container mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-tag mr-1"></i><?php echo _l('API Name'); ?>
                                                </label>
                                                <input type="text" class="form-control form-control-sm" id="search_name" value="<?php echo htmlspecialchars($search_filters['search_name'] ?? ''); ?>" placeholder="<?php echo _l('API Name'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-link mr-1"></i><?php echo _l('API URL'); ?>
                                                </label>
                                                <input type="text" class="form-control form-control-sm" id="search_url" value="<?php echo htmlspecialchars($search_filters['search_url'] ?? ''); ?>" placeholder="<?php echo _l('API URL'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-toggle-on mr-1"></i><?php echo _l('Status'); ?>
                                                </label>
                                                <select class="form-control form-control-sm" id="search_status">
                                                    <option value=""><?php echo _l('All Status'); ?></option>
                                                    <option value="active" <?php echo ($search_filters['search_status'] ?? '') === 'active' ? 'selected' : ''; ?>><?php echo _l('Active'); ?></option>
                                                    <option value="inactive" <?php echo ($search_filters['search_status'] ?? '') === 'inactive' ? 'selected' : ''; ?>><?php echo _l('Inactive'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-hashtag mr-1"></i><?php echo _l('Status Code'); ?>
                                                </label>
                                                <input type="number" class="form-control form-control-sm" id="search_status_code" value="<?php echo htmlspecialchars($search_filters['search_status_code'] ?? ''); ?>" placeholder="<?php echo _l('Status Code'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-center">
                                            <button type="button" class="btn btn-info btn-sm mr-2" onclick="performCronApiSearch()">
                                                <i class="fa fa-search mr-1"></i><?php echo _l('search'); ?>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearCronApiSearch()">
                                                <i class="fa fa-times mr-1"></i><?php echo _l('clear'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="position: relative;">
                            <div id="searching-overlay" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 249, 250, 0.95) 100%); backdrop-filter: blur(2px); z-index: 1000; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px solid rgba(0, 123, 255, 0.1); box-shadow: inset 0 0 20px rgba(0, 123, 255, 0.05);">
                                <div style="text-align: center; color: #0056b3; padding: 25px; border-radius: 12px; background: rgba(255, 255, 255, 0.95); box-shadow: 0 8px 25px rgba(0, 123, 255, 0.15); min-width: 220px; border: 1px solid rgba(0, 123, 255, 0.25); position: relative; overflow: hidden;">
                                    <div style="margin-bottom: 18px;">
                                        <i class="fa fa-circle-notch fa-4x fa-spin" style="color: #007bff; filter: drop-shadow(0 4px 8px rgba(0, 123, 255, 0.4)); animation: spin 1.2s linear infinite;"></i>
                                    </div>
                                    <div style="font-size: 18px; font-weight: 600; color: #495057; margin-bottom: 8px; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">
                                        <?php echo _l('Searching'); ?>...
                                    </div>
                                    <div style="font-size: 13px; color: #6c757d; font-weight: 400; line-height: 1.3;">
                                        <?php echo _l('Please wait while we fetch your results'); ?>
                                    </div>
                                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(45deg, transparent 30%, rgba(0, 123, 255, 0.02) 50%, transparent 70%); animation: shimmer 2s ease-in-out infinite;"></div>
                                </div>
                            </div>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('Date'); ?></th>
                                        <th><?php echo _l('API Name'); ?></th>
                                        <th><?php echo _l('API URL'); ?></th>
                                        <th><?php echo _l('Status'); ?></th>
                                        <th><?php echo _l('Response'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="cron-api-logs-table">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <i class="fa fa-spinner fa-spin"></i> <?php echo _l('Loading cron API logs'); ?>...
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                        <!-- Pagination will be rendered by JavaScript -->
                        <div id="cron-api-logs-pagination-container" class="tw-mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Cron API Modal -->
<div class="modal fade" id="addCronApiModal" tabindex="-1" role="dialog" aria-labelledby="addCronApiModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addCronApiModalLabel"><?php echo _l('Add Cron API'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modal_api_name"><?php echo _l('API Name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_api_name" name="name" placeholder="e.g. Data Sync API" required>
                            <small class="form-text text-muted"><?php echo _l('Unique identifier for the API'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modal_api_url"><?php echo _l('API URL'); ?> <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="modal_api_url" name="api_url" placeholder="https://api.example.com/endpoint" required>
                            <small class="form-text text-muted"><?php echo _l('Full URL of the cron API endpoint'); ?></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modal_request_method"><?php echo _l('Request Method'); ?></label>
                            <select class="form-control" id="modal_request_method" name="request_method">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modal_cron_schedule"><?php echo _l('Cron Schedule'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_cron_schedule" name="cron_schedule" placeholder="0 */6 * * *" value="0 */6 * * *">
                            <small class="form-text text-muted"><?php echo _l('Cron expression (e.g., "0 */6 * * *" for every 6 hours)'); ?></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="modal_request_body"><?php echo _l('Request Body/Parameters'); ?></label>
                            <textarea class="form-control" id="modal_request_body" name="request_body" rows="4" placeholder='{"key": "value"}'></textarea>
                            <small class="form-text text-muted"><?php echo _l('JSON body for POST/PUT requests (leave empty for GET)'); ?></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="modal_headers"><?php echo _l('Headers'); ?></label>
                            <textarea class="form-control" id="modal_headers" name="headers" rows="3" placeholder='{"Content-Type": "application/json", "Authorization": "Bearer token"}'></textarea>
                            <small class="form-text text-muted"><?php echo _l('JSON format headers (optional)'); ?></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="modal_is_active" id="modal_is_active" checked>
                                <label for="modal_is_active"><?php echo _l('Active'); ?></label>
                            </div>
                            <small class="form-text text-muted"><?php echo _l('Enable/disable the cron API execution'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('Cancel'); ?></button>
                <button type="button" class="btn btn-success" onclick="addCronApi()"><?php echo _l('Create Cron API'); ?> <i class="fa fa-plus" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Cron API Modal -->
<div class="modal fade" id="editCronApiModal" tabindex="-1" role="dialog" aria-labelledby="editCronApiModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editCronApiModalLabel"><?php echo _l('Edit Cron API'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_api_id">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_api_name"><?php echo _l('API Name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_api_name" name="name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_api_url"><?php echo _l('API URL'); ?> <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="edit_api_url" name="api_url" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_request_method"><?php echo _l('Request Method'); ?></label>
                            <select class="form-control" id="edit_request_method" name="request_method">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_cron_schedule"><?php echo _l('Cron Schedule'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_cron_schedule" name="cron_schedule" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="edit_request_body"><?php echo _l('Request Body/Parameters'); ?></label>
                            <textarea class="form-control" id="edit_request_body" name="request_body" rows="4"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="edit_headers"><?php echo _l('Headers'); ?></label>
                            <textarea class="form-control" id="edit_headers" name="headers" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="edit_is_active" id="edit_is_active">
                                <label for="edit_is_active"><?php echo _l('Active'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('Cancel'); ?></button>
                <button type="button" class="btn btn-success" onclick="updateCronApi()"><?php echo _l('Update Cron API'); ?> <i class="fa fa-save" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- API Response Modal -->
<div class="modal fade" id="cronApiResponseModal" tabindex="-1" role="dialog" aria-labelledby="cronApiResponseModalLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary" style="border-radius: 6px 6px 0 0;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true" style="font-size: 28px; line-height: 1;">&times;</span>
                </button>
                <h4 class="modal-title" id="cronApiResponseModalLabel" style="color: white; margin: 0;">
                    <i class="fa fa-code" style="margin-right: 10px;"></i>
                    API Response Details
                </h4>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div class="row" style="margin: 0;">
                    <div class="col-md-12" style="padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e9ecef;">
                            <h5 style="margin: 0; color: #495057; font-weight: 600;">
                                <i class="fa fa-file-text-o" style="margin-right: 8px; color: #6c757d;"></i>
                                Response Content
                            </h5>
                            <button type="button" class="btn btn-success btn-sm" onclick="copyCronApiResponseToClipboard()" title="Copy full response to clipboard" style="font-weight: 500; padding: 6px 12px;">
                                <i class="fa fa-copy" style="margin-right: 6px;"></i>
                                Copy Response
                            </button>
                        </div>
                        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px; position: relative;">
                            <pre id="cronApiResponseContent" style="margin: 0; padding: 10px; background: white; border: 1px solid #dee2e6; border-radius: 4px; white-space: pre-wrap; word-wrap: break-word; font-family: 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'Liberation Mono', 'Courier New', monospace; font-size: 13px; line-height: 1.4; max-height: 500px; overflow-y: auto; color: #212529;"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: none; padding: 15px 20px;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="font-weight: 500;">
                    <i class="fa fa-times" style="margin-right: 6px;"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
// Cron API Management Functions
function addCronApi() {
    var name = $('#modal_api_name').val();
    var api_url = $('#modal_api_url').val();
    var request_method = $('#modal_request_method').val();
    var request_body = $('#modal_request_body').val();
    var headers = $('#modal_headers').val();
    var cron_schedule = $('#modal_cron_schedule').val();
    var is_active = $('#modal_is_active').is(':checked');

    $.ajax({
        url: admin_url + 'cron_api_settings/add_cron_api',
        type: 'POST',
        data: {
            name: name,
            api_url: api_url,
            request_method: request_method,
            request_body: request_body,
            headers: headers,
            cron_schedule: cron_schedule,
            is_active: is_active
        },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                // Reload the cron APIs table
                loadCronApis();

                // Close modal and reset
                $('#addCronApiModal').modal('hide');
                $('#modal_api_name').val('');
                $('#modal_api_url').val('');
                $('#modal_request_method').val('GET');
                $('#modal_request_body').val('');
                $('#modal_headers').val('');
                $('#modal_cron_schedule').val('0 */6 * * *');
                $('#modal_is_active').prop('checked', true);

                alert_float('success', data.message);
            } else {
                alert_float('danger', data.message || 'Error adding cron API');
            }
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Error adding cron API: ' + error);
        }
    });
}

function loadCronApis() {
    $.ajax({
        url: admin_url + 'cron_api_settings/get_cron_apis',
        type: 'GET',
        dataType: 'json',
        success: function(apis) {
            var html = '';
            if (apis && apis.length > 0) {
                apis.forEach(function(api) {
                    var statusBadge = api.is_active == 1 ?
                        '<span class="badge badge-success">Active</span>' :
                        '<span class="badge badge-secondary">Inactive</span>';

                    var lastRun = api.last_run ? formatDateTime(api.last_run) : 'Never';
                    var nextRun = api.next_run ? formatDateTime(api.next_run) : 'Not scheduled';

                    html += '<tr>' +
                        '<td data-title="Name"><strong>' + escapeHtml(api.name) + '</strong></td>' +
                        '<td data-title="API URL"><span title="' + escapeHtml(api.api_url) + '">' + escapeHtml(api.api_url.length > 30 ? api.api_url.substring(0, 30) + '...' : api.api_url) + '</span></td>' +
                        '<td data-title="Cron Schedule"><code style="font-size: 11px;">' + escapeHtml(api.cron_schedule) + '</code></td>' +
                        '<td data-title="Status">' + statusBadge + '</td>' +
                        '<td data-title="Last Run" class="text-nowrap"><small>' + lastRun + '</small></td>' +
                        '<td data-title="Next Run" class="text-nowrap"><small>' + nextRun + '</small></td>' +
                        '<td data-title="Actions" class="text-center">' +
                            '<div class="btn-group" role="group">' +
                                '<button class="btn btn-sm btn-outline-info" onclick="editCronApi(' + api.id + ')" title="Edit"><i class="fa fa-edit"></i></button>' +
                                '<button class="btn btn-sm btn-outline-warning" onclick="executeCronApi(' + api.id + ')" title="Execute Now"><i class="fa fa-play"></i></button>' +
                                '<button class="btn btn-sm btn-outline-danger" onclick="deleteCronApi(' + api.id + ')" title="Delete"><i class="fa fa-trash"></i></button>' +
                            '</div>' +
                        '</td>' +
                        '</tr>';
                });
            } else {
                html = '<tr><td colspan="7" class="text-center text-muted"><?php echo _l('No cron APIs found'); ?></td></tr>';
            }
            $('#cron-apis-table').html(html);
        },
        error: function(xhr, status, error) {
            $('#cron-apis-table').html('<tr><td colspan="7" class="text-danger">Error loading cron APIs</td></tr>');
        }
    });
}

function editCronApi(id) {
    $.ajax({
        url: admin_url + 'cron_api_settings/get_cron_apis',
        type: 'GET',
        dataType: 'json',
        success: function(apis) {
            var api = apis.find(function(a) { return a.id == id; });
            if (api) {
                $('#edit_api_id').val(api.id);
                $('#edit_api_name').val(api.name);
                $('#edit_api_url').val(api.api_url);
                $('#edit_request_method').val(api.request_method);
                $('#edit_request_body').val(api.request_body);
                $('#edit_headers').val(api.headers);
                $('#edit_cron_schedule').val(api.cron_schedule);
                $('#edit_is_active').prop('checked', api.is_active == 1);

                $('#editCronApiModal').modal('show');
            }
        }
    });
}

function updateCronApi() {
    var id = $('#edit_api_id').val();
    var name = $('#edit_api_name').val();
    var api_url = $('#edit_api_url').val();
    var request_method = $('#edit_request_method').val();
    var request_body = $('#edit_request_body').val();
    var headers = $('#edit_headers').val();
    var cron_schedule = $('#edit_cron_schedule').val();
    var is_active = $('#edit_is_active').is(':checked');

    $.ajax({
        url: admin_url + 'cron_api_settings/update_cron_api',
        type: 'POST',
        data: {
            id: id,
            name: name,
            api_url: api_url,
            request_method: request_method,
            request_body: request_body,
            headers: headers,
            cron_schedule: cron_schedule,
            is_active: is_active
        },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                loadCronApis();
                $('#editCronApiModal').modal('hide');
                alert_float('success', data.message);
            } else {
                alert_float('danger', data.message || 'Error updating cron API');
            }
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Error updating cron API: ' + error);
        }
    });
}

function deleteCronApi(id) {
    if (confirm('<?php echo _l('Are you sure you want to delete this cron API?'); ?>')) {
        $.ajax({
            url: admin_url + 'cron_api_settings/delete_cron_api',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    loadCronApis();
                    alert_float('success', data.message);
                } else {
                    alert_float('danger', data.message || 'Error deleting cron API');
                }
            },
            error: function(xhr, status, error) {
                alert_float('danger', 'Error deleting cron API: ' + error);
            }
        });
    }
}

function executeCronApi(id) {
    // Show loading indicator on the execute button
    var executeButton = $('button[onclick="executeCronApi(' + id + ')"]');
    var originalContent = executeButton.html();
    var startTime = new Date().getTime();
    var timerInterval;

    // Start timer with better spacing
    timerInterval = setInterval(function() {
        var elapsed = Math.floor((new Date().getTime() - startTime) / 1000);
        executeButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin" style="margin-right: 6px;"></i> Executing... (' + elapsed + ' sec)');
    }, 1000);

    $.ajax({
        url: admin_url + 'cron_api_settings/execute_cron_api',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                loadCronApis();
                showCronApiResponseModal(data.response, data.http_code);
                alert_float('success', data.message);
            } else {
                alert_float('danger', data.message || 'Error executing cron API');
            }
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Error executing cron API: ' + error);
        },
        complete: function() {
            // Stop timer and restore button state after API call completes
            clearInterval(timerInterval);
            executeButton.prop('disabled', false).html(originalContent);
        }
    });
}

// Utility functions
function formatDateTime(dateStr) {
    return new Date(dateStr).toLocaleString();
}

function escapeHtml(text) {
    var textElement = document.createElement('div');
    textElement.innerText = text;
    return textElement.innerHTML;
}

// Function to display the API response modal
function showCronApiResponseModal(response, statusCode) {
    // Format JSON response for better readability
    let formattedResponse = response;
    try {
        if (typeof response === 'string') {
            formattedResponse = JSON.stringify(JSON.parse(response), null, 2);
        } else {
            formattedResponse = JSON.stringify(response, null, 2);
        }
    } catch (e) {
        formattedResponse = response; // Keep original if not JSON
    }

    let modalHtml = `
        <div class="modal fade" id="cronApiResponseModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-check-circle mr-2"></i>
                            API Response (Status: ${statusCode})
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="row m-0">
                            <div class="col-md-12">
                                <div class="response-header p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="fa fa-file-code-o mr-2"></i>
                                            Response Content
                                        </h6>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary mr-2" onclick="formatJsonResponse()">
                                                <i class="fa fa-magic mr-1"></i>Format JSON
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary mr-2" onclick="toggleRawResponse()">
                                                <i class="fa fa-code mr-1"></i>Raw
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success" onclick="copyCronApiResponse()">
                                                <i class="fa fa-copy mr-1"></i>Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="response-content p-3">
                                    <pre id="cronApiResponseContent" style="max-height: 500px; overflow-y: auto; background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #e9ecef; font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 14px; line-height: 1.4; margin: 0; white-space: pre-wrap; word-wrap: break-word;">${formattedResponse}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <small class="text-muted mr-auto">Response received at: ${new Date().toLocaleString()}</small>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if present
    $('#cronApiResponseModal').remove();

    // Append modal to body and show it
    $('body').append(modalHtml);
    $('#cronApiResponseModal').modal('show');
}

// Function to format JSON response
function formatJsonResponse() {
    const responseText = $('#cronApiResponseContent').text();
    try {
        const jsonObj = JSON.parse(responseText);
        const formatted = JSON.stringify(jsonObj, null, 2);
        $('#cronApiResponseContent').text(formatted);
    } catch (e) {
        alert_float('warning', 'Response is not valid JSON');
    }
}

// Function to toggle between formatted and raw response
function toggleRawResponse() {
    const currentText = $('#cronApiResponseContent').text();
    const originalResponse = $('#cronApiResponseModal').data('original-response');

    if ($('#cronApiResponseContent').hasClass('raw-view')) {
        // Switch back to formatted
        try {
            const jsonObj = JSON.parse(originalResponse || currentText);
            $('#cronApiResponseContent').text(JSON.stringify(jsonObj, null, 2)).removeClass('raw-view');
        } catch (e) {
            $('#cronApiResponseContent').text(originalResponse || currentText).removeClass('raw-view');
        }
    } else {
        // Switch to raw
        $('#cronApiResponseContent').text(originalResponse || currentText).addClass('raw-view');
    }
}

// Function to copy response content to clipboard
function copyCronApiResponse() {
    const responseText = $('#cronApiResponseContent').text();
    navigator.clipboard.writeText(responseText).then(function() {
        // Show success feedback
        const copyBtn = $('#cronApiResponseModal .btn-primary');
        const originalText = copyBtn.text();
        copyBtn.text('Copied!');
        setTimeout(() => copyBtn.text(originalText), 2000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
    });
}

// Cron API Logs Functions
function loadCronApiLogs() {
    $.ajax({
        url: admin_url + 'cron_api_settings/get_cron_api_logs',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            // Hide the searching overlay first
            $('#searching-overlay').hide();

            if (response.error) {
                $('#cron-api-logs-table').html('<tr><td colspan="5" class="text-warning"><i class="fa fa-exclamation-triangle"></i> ' + response.error + '<br><small>Please run the SQL file: <code>' + response.sql_file + '</code></small></td></tr>');
            } else {
                renderCronApiLogsTable(response);
            }
        },
        error: function(xhr, status, error) {
            // Hide the searching overlay
            $('#searching-overlay').hide();

            $('#cron-api-logs-table').html('<tr><td colspan="5" class="text-danger">Error loading cron API logs: ' + error + '</td></tr>');
        }
    });
}

function performCronApiSearch() {
    var searchData = {
        search_name: $('#search_name').val() || '',
        search_url: $('#search_url').val() || '',
        search_status: $('#search_status').val() || '',
        search_status_code: $('#search_status_code').val() || ''
    };

    loadCronApiLogsWithFilters(searchData);
}

function clearCronApiSearch() {
    $('#search_name').val('');
    $('#search_url').val('');
    $('#search_status').val('');
    $('#search_status_code').val('');
    loadCronApiLogs();
}

function loadCronApiLogsWithFilters(searchData) {
    $.ajax({
        url: admin_url + 'cron_api_settings/get_cron_api_logs',
        type: 'GET',
        data: searchData,
        dataType: 'json',
        success: function(logs) {
            // Filter the results on client side
            var filteredLogs = logs;

            if (searchData.search_name) {
                filteredLogs = filteredLogs.filter(function(log) {
                    return log.api_name && log.api_name.toLowerCase().indexOf(searchData.search_name.toLowerCase()) !== -1;
                });
            }

            if (searchData.search_url) {
                filteredLogs = filteredLogs.filter(function(log) {
                    return log.api_url && log.api_url.toLowerCase().indexOf(searchData.search_url.toLowerCase()) !== -1;
                });
            }

            if (searchData.search_status) {
                var isActive = searchData.search_status === 'active';
                filteredLogs = filteredLogs.filter(function(log) {
                    return log.is_active == (isActive ? 1 : 0);
                });
            }

            if (searchData.search_status_code) {
                filteredLogs = filteredLogs.filter(function(log) {
                    return log.status_code && log.status_code.toString().indexOf(searchData.search_status_code) !== -1;
                });
            }

            renderCronApiLogsTable(filteredLogs);
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Error loading cron API logs: ' + error);
        }
    });
}

function renderCronApiLogsTable(logs) {
    var html = '';
    if (logs && logs.length > 0) {
        logs.forEach(function(log, index) {

            var statusBadge = '';
            if (log.status_code) {
                statusBadge = log.status_code >= 200 && log.status_code < 300 ?
                    '<span class="badge badge-success">' + log.status_code + '</span>' :
                    '<span class="badge badge-danger">' + log.status_code + '</span>';
            } else {
                statusBadge = '<span class="badge badge-secondary">N/A</span>';
            }

            var responsePreview = 'No response';
            if (log.response_body) {
                try {
                    var jsonResponse = JSON.parse(log.response_body);
                    responsePreview = JSON.stringify(jsonResponse).replace(/(\r\n|\n|\r)/g, ' ').trim().substring(0, 50) + '...';
                } catch (e) {
                    responsePreview = log.response_body.replace(/(\r\n|\n|\r)/g, ' ').trim().substring(0, 50) + '...';
                }
            }

            var errorText = log.error_message || 'No errors';

            // Store the full response body for the modal
            var fullResponseBody = log.response_body || '';

            html += '<tr>' +
                '<td>' + (log.created_at ? formatDateTime(log.created_at) : 'N/A') + '</td>' +
                '<td>' + escapeHtml(log.api_name || 'Unknown') + '</td>' +
                '<td><span title="' + escapeHtml(log.api_url || '') + '">' + escapeHtml((log.api_url || '').length > 30 ? (log.api_url || '').substring(0, 30) + '...' : (log.api_url || '')) + '</span></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td><span class="text-primary show-response" style="cursor: pointer;" data-response="' + btoa(unescape(encodeURIComponent(fullResponseBody))) + '" data-status="' + (log.status_code || 'N/A') + '" title="Click to view full response">' + escapeHtml(responsePreview) + '</span></td>' +
                '</tr>';
        });

        } else {
            html = '<tr><td colspan="5" class="text-center text-muted"><?php echo _l('No cron API logs found'); ?></td></tr>';
        }
    $('#cron-api-logs-table').html(html);
}

// Initialize on page load
$(function() {
    loadCronApis();
    loadCronApiLogs();

    // Attach click handler for response links in logs table
    $(document).on('click', '.show-response', function() {
        const encodedResponse = $(this).data('response');
        const statusCode = $(this).data('status');
        const originalResponse = decodeURIComponent(escape(atob(encodedResponse)));
        showCronApiResponseModal(originalResponse, statusCode);
    });

    // Comment out DataTable initialization to avoid errors
    // if ($.fn.DataTable && !$.fn.dataTable.isDataTable('#cron-apis-table-responsive')) {
    //     $('#cron-apis-table-responsive').DataTable({
    //         responsive: true,
    //         paging: false,
    //         searching: false,
    //         ordering: true,
    //         info: false,
    //         autoWidth: false,
    //         columnDefs: [
    //             { width: '150px', targets: 0 },
    //             { width: '200px', targets: 1 },
    //             { width: '120px', targets: 2 },
    //             { width: '80px', targets: 3 },
    //             { width: '150px', targets: 4 },
    //             { width: '150px', targets: 5},
    //             { width: '120px', targets: 6 }
    //         ]
    //     });
    // }
});
</script>
