<?php
defined('BASEPATH') or exit('No direct script access allowed');
init_head();
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body external-api-settings-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">
                                    <i class="fa fa-exchange text-primary"></i>
                                    <?php echo _l('External API Settings'); ?>
                                </h4>
                                <p class="text-muted mb-0 mt-1"><?php echo _l('Manage external API calls with cron scheduling'); ?></p>
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
                    <div class="panel-body external-apis-panel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <div class="panel-icon mr-3">
                                    <i class="fa fa-exchange text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo _l('External APIs'); ?>
                                        <?php if (($external_apis_pages ?? 1) > 1) { ?>
                                            <small class="text-muted"> <?php echo sprintf(_l('ticket_logs_pagination_label'), (int)($external_apis_start ?? 0), (int)($external_apis_end ?? 0), (int)($external_apis_total ?? 0)); ?></small>
                                        <?php } ?>
                                    </h5>
                                    <small class="text-muted"><?php echo _l('Configure and manage external API endpoints with cron scheduling'); ?></small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm add-external-api-btn" data-toggle="modal" data-target="#addExternalApiModal">
                                <i class="fa fa-plus mr-1"></i><?php echo _l('Add External API'); ?>
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped external-apis-table">
                                <thead>
                                    <tr>
                                        <th><i class="fa fa-tag mr-1"></i><?php echo _l('Name'); ?></th>
                                        <th><i class="fa fa-link mr-1"></i><?php echo _l('API URL'); ?></th>
                                        <th><i class="fa fa-clock-o mr-1"></i><?php echo _l('Cron Schedule'); ?></th>
                                        <th><i class="fa fa-toggle-on mr-1"></i><?php echo _l('Status'); ?></th>
                                        <th><i class="fa fa-calendar mr-1"></i><?php echo _l('Last Run'); ?></th>
                                        <th><i class="fa fa-calendar mr-1"></i><?php echo _l('Next Run'); ?></th>
                                        <th><i class="fa fa-cogs mr-1"></i><?php echo _l('Actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="external-apis-table">
                                    <!-- External APIs will be loaded here -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination will be rendered by JavaScript -->
                        <div id="external-apis-pagination-container" class="tw-mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- External API Logs Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body external-api-logs-panel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <div class="panel-icon mr-3">
                                    <i class="fa fa-list-alt text-warning"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo _l('External API Logs'); ?>
                                        <?php if (($external_apis_pages ?? 1) > 1) { ?>
                                            <small class="text-muted"> <?php echo sprintf(_l('ticket_logs_pagination_label'), (int)($external_apis_start ?? 0), (int)($external_apis_end ?? 0), (int)($external_apis_total ?? 0)); ?></small>
                                        <?php } ?>
                                    </h5>
                                    <small class="text-muted"><?php echo _l('Monitor API execution logs and responses'); ?></small>
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
                                            <button type="button" class="btn btn-info btn-sm mr-2" onclick="performExternalApiSearch()">
                                                <i class="fa fa-search mr-1"></i><?php echo _l('search'); ?>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearExternalApiSearch()">
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
                                        <th><?php echo _l('Error'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="external-api-logs-table">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fa fa-spinner fa-spin"></i> <?php echo _l('Loading external API logs'); ?>...
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                        <!-- Pagination will be rendered by JavaScript -->
                        <div id="external-api-logs-pagination-container" class="tw-mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add External API Modal -->
<div class="modal fade" id="addExternalApiModal" tabindex="-1" role="dialog" aria-labelledby="addExternalApiModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addExternalApiModalLabel"><?php echo _l('Add External API'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modal_api_name"><?php echo _l('API Name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_api_name" name="name" placeholder="e.g. External Data Sync" required>
                            <small class="form-text text-muted"><?php echo _l('Unique identifier for the API'); ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="modal_api_url"><?php echo _l('API URL'); ?> <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="modal_api_url" name="api_url" placeholder="https://api.example.com/endpoint" required>
                            <small class="form-text text-muted"><?php echo _l('Full URL of the external API endpoint'); ?></small>
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
                            <small class="form-text text-muted"><?php echo _l('Enable/disable the external API execution'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('Cancel'); ?></button>
                <button type="button" class="btn btn-success" onclick="addExternalApi()"><?php echo _l('Create External API'); ?> <i class="fa fa-plus" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- Edit External API Modal -->
<div class="modal fade" id="editExternalApiModal" tabindex="-1" role="dialog" aria-labelledby="editExternalApiModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editExternalApiModalLabel"><?php echo _l('Edit External API'); ?></h4>
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
                <button type="button" class="btn btn-success" onclick="updateExternalApi()"><?php echo _l('Update External API'); ?> <i class="fa fa-save" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- API Response Modal -->
<div class="modal fade" id="externalApiResponseModal" tabindex="-1" role="dialog" aria-labelledby="externalApiResponseModalLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary" style="border-radius: 6px 6px 0 0;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true" style="font-size: 28px; line-height: 1;">&times;</span>
                </button>
                <h4 class="modal-title" id="externalApiResponseModalLabel" style="color: white; margin: 0;">
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
                            <button type="button" class="btn btn-success btn-sm" onclick="copyExternalApiResponseToClipboard()" title="Copy full response to clipboard" style="font-weight: 500; padding: 6px 12px;">
                                <i class="fa fa-copy" style="margin-right: 6px;"></i>
                                Copy Response
                            </button>
                        </div>
                        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px; position: relative;">
                            <pre id="externalApiResponseContent" style="margin: 0; padding: 10px; background: white; border: 1px solid #dee2e6; border-radius: 4px; white-space: pre-wrap; word-wrap: break-word; font-family: 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'Liberation Mono', 'Courier New', monospace; font-size: 13px; line-height: 1.4; max-height: 500px; overflow-y: auto; color: #212529;"></pre>
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
// External API Management Functions
function addExternalApi() {
    var name = $('#modal_api_name').val();
    var api_url = $('#modal_api_url').val();
    var request_method = $('#modal_request_method').val();
    var request_body = $('#modal_request_body').val();
    var headers = $('#modal_headers').val();
    var cron_schedule = $('#modal_cron_schedule').val();
    var is_active = $('#modal_is_active').is(':checked');

    $.ajax({
        url: admin_url + 'external_api_settings/add_external_api',
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
                // Reload the external APIs table
                loadExternalApis();

                // Close modal and reset
                $('#addExternalApiModal').modal('hide');
                $('#modal_api_name').val('');
                $('#modal_api_url').val('');
                $('#modal_request_method').val('GET');
                $('#modal_request_body').val('');
                $('#modal_headers').val('');
                $('#modal_cron_schedule').val('0 */6 * * *');
                $('#modal_is_active').prop('checked', true);

                alert_float('success', data.message);
            } else {
                alert_float('danger', data.message || 'Error adding external API');
            }
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Error adding external API: ' + error);
        }
    });
}

function loadExternalApis() {
    $.ajax({
        url: admin_url + 'external_api_settings/get_external_apis',
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
                        '<td>' + escapeHtml(api.name) + '</td>' +
                        '<td><span title="' + escapeHtml(api.api_url) + '">' + escapeHtml(api.api_url.length > 50 ? api.api_url.substring(0, 50) + '...' : api.api_url) + '</span></td>' +
                        '<td><code>' + escapeHtml(api.cron_schedule) + '</code></td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td>' + lastRun + '</td>' +
                        '<td>' + nextRun + '</td>' +
                        '<td>' +
                            '<button class="btn btn-sm btn-info mr-1" onclick="editExternalApi(' + api.id + ')" title="Edit"><i class="fa fa-edit"></i></button>' +
                            '<button class="btn btn-sm btn-warning mr-1" onclick="executeExternalApi(' + api.id + ')" title="Execute Now"><i class="fa fa-play"></i></button>' +
                            '<button class="btn btn-sm btn-danger" onclick="deleteExternalApi(' + api.id + ')" title="Delete"><i class="fa fa-trash"></i></button>' +
                        '</td>' +
                        '</tr>';
                });
            } else {
                html = '<tr><td colspan="7" class="text-center text-muted"><?php echo _l('No external APIs found'); ?></td></tr>';
            }
            $('#external-apis-table').html(html);
        },
        error: function(xhr, status, error) {
            $('#external-apis-table').html('<tr><td colspan="7" class="text-danger">Error loading external APIs</td></tr>');
        }
    });
}

function editExternalApi(id) {
    $.ajax({
        url: admin_url + 'external_api_settings/get_external_apis',
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

                $('#editExternalApiModal').modal('show');
            }
        }
    });
}

function updateExternalApi() {
    var id = $('#edit_api_id').val();
    var name = $('#edit_api_name').val();
    var api_url = $('#edit_api_url').val();
    var request_method = $('#edit_request_method').val();
    var request_body = $('#edit_request_body').val();
    var headers = $('#edit_headers').val();
    var cron_schedule = $('#edit_cron_schedule').val();
    var is_active = $('#edit_is_active').is(':checked');

    $.ajax({
        url: admin_url + 'external_api_settings/update_external_api',
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
                loadExternalApis();
                $('#editExternalApiModal').modal('hide');
                alert_float('success', data.message);
            } else {
                alert_float('danger', data.message || 'Error updating external API');
            }
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Error updating external API: ' + error);
        }
    });
}

function deleteExternalApi(id) {
    if (confirm('<?php echo _l('Are you sure you want to delete this external API?'); ?>')) {
        $.ajax({
            url: admin_url + 'external_api_settings/delete_external_api',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    loadExternalApis();
                    alert_float('success', data.message);
                } else {
                    alert_float('danger', data.message || 'Error deleting external API');
                }
            },
            error: function(xhr, status, error) {
                alert_float('danger', 'Error deleting external API: ' + error);
            }
        });
    }
}

function executeExternalApi(id) {
    $.ajax({
        url: admin_url + 'external_api_settings/execute_external_api',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                loadExternalApiLogs();
                showExternalApiResponseModal(data.response, data.http_code);
                alert_float('success', data.message);
            } else {
                alert_float('danger', data.message || 'Error executing external API');
            }
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Error executing external API: ' + error);
        }
    });
}

// Search and Filter Functions
var performExternalApiSearch = function() {
    var searchData = {
        search_name: $('#search_name').val() || '',
        search_url: $('#search_url').val() || '',
        search_status: $('#search_status').val() || '',
        search_status_code: $('#search_status_code').val() || ''
    };

    loadExternalApiLogsWithFilters(searchData);
};

var clearExternalApiSearch = function() {
    $('#search_name').val('');
    $('#search_url').val('');
    $('#search_status').val('');
    $('#search_status_code').val('');
    loadExternalApiLogs();
};

var loadExternalApiLogsWithFilters = function(searchData) {
    $.ajax({
        url: admin_url + 'external_api_settings/get_external_api_logs',
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

            renderExternalApiLogsTable(filteredLogs);
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Error loading external API logs: ' + error);
        }
    });
};

var renderExternalApiLogsTable = function(logs) {
    console.log('Rendering table with logs:', logs); // Debug log

    // Hide the searching overlay first
    $('#searching-overlay').hide();
    console.log('Searching overlay hidden'); // Debug log

    var html = '';
    if (logs && logs.length > 0) {
        console.log('Processing ' + logs.length + ' log entries'); // Debug log

        logs.forEach(function(log, index) {
            console.log('Processing log ' + index + ':', log); // Debug log

            var statusBadge = log.status_code >= 200 && log.status_code < 300 ?
                '<span class="badge badge-success">' + log.status_code + '</span>' :
                '<span class="badge badge-danger">' + log.status_code + '</span>';

            var responsePreview = log.response_body ?
                log.response_body.replace(/(\r\n|\n|\r)/g, ' ').trim().substring(0, 50) + '...' :
                'No response';

            var errorText = log.error_message || 'No errors';

            // Store the full response body for the modal
            var fullResponseBody = log.response_body || '';

            html += '<tr>' +
                '<td>' + formatDateTime(log.created_at) + '</td>' +
                '<td>' + escapeHtml(log.api_name || 'Unknown') + '</td>' +
                '<td><span title="' + escapeHtml(log.api_url || '') + '">' + escapeHtml((log.api_url || '').length > 30 ? (log.api_url || '').substring(0, 30) + '...' : (log.api_url || '')) + '</span></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td><span class="text-primary show-response" style="cursor: pointer;" data-response="' + btoa(escapeHtml(fullResponseBody)) + '" data-status="' + log.status_code + '" title="Click to view full response">' + escapeHtml(responsePreview) + '</span></td>' +
                '<td><span class="' + (log.error_message ? 'text-danger' : 'text-muted') + '">' + escapeHtml(errorText) + '</span></td>' +
                '</tr>';
        });

        console.log('Generated HTML:', html); // Debug log
    } else {
        console.log('No logs found or logs is empty'); // Debug log
        html = '<tr><td colspan="6" class="text-center text-muted"><?php echo _l('No external API logs found'); ?></td></tr>';
    }

    console.log('Final HTML to insert:', html); // Debug log
    $('#external-api-logs-table').html(html);
    console.log('Table updated'); // Debug log
};

// External API Logs Functions
function loadExternalApiLogs() {
    $.ajax({
        url: admin_url + 'external_api_settings/get_external_api_logs',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('API Response:', response); // Debug log
            if (response.error) {
                $('#external-api-logs-table').html('<tr><td colspan="6" class="text-warning"><i class="fa fa-exclamation-triangle"></i> ' + response.error + '<br><small>Please run the SQL file: <code>' + response.sql_file + '</code></small></td></tr>');
            } else {
                renderExternalApiLogsTable(response);
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', xhr, status, error); // Debug log
            $('#external-api-logs-table').html('<tr><td colspan="6" class="text-danger">Error loading external API logs: ' + error + '</td></tr>');
        }
    });
}

function showExternalApiResponseModal(element, statusCode) {
    // Get the response data from the element's data attribute
    var responseData = $(element).data('response');
    if (responseData) {
        // Decode from base64
        var responseText = decodeURIComponent(escape(atob(responseData)));
        $('#externalApiResponseContent').text(responseText);
    } else {
        $('#externalApiResponseContent').text('No response data available');
    }
    $('#externalApiResponseModal').modal('show');
}

function copyExternalApiResponseToClipboard() {
    var responseText = $('#externalApiResponseContent').text();

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(responseText).then(function() {
            alert_float('success', 'Response copied to clipboard successfully!');
        }).catch(function(err) {
            fallbackCopyTextToClipboard(responseText);
        });
    } else {
        fallbackCopyTextToClipboard(responseText);
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
            alert_float('success', 'Response copied to clipboard successfully!');
        } else {
            alert_float('danger', 'Failed to copy response');
        }
    } catch (err) {
        alert_float('danger', 'Failed to copy response');
    }

    textArea.remove();
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

// Initialize on page load
$(function() {
    loadExternalApis();
    loadExternalApiLogs();
});
</script>
