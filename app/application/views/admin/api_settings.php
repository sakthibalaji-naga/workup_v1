<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body api-settings-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">
                                    <i class="fa fa-cogs text-primary"></i>
                                    <?php echo _l('API Settings'); ?>
                                </h4>
                                <p class="text-muted mb-0 mt-1"><?php echo _l('Manage your API users, permissions, and monitor API activity'); ?></p>
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
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body api-users-panel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <div class="panel-icon mr-3">
                                    <i class="fa fa-users text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo _l('API Users'); ?></h5>
                                    <small class="text-muted"><?php echo _l('Manage API users and their permissions'); ?></small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm add-user-btn" data-toggle="modal" data-target="#addApiUserModal">
                                <i class="fa fa-plus mr-1"></i><?php echo _l('Add API User'); ?>
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped api-users-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fa fa-user mr-1"></i><?php echo _l('Username'); ?></th>
                                        <th><i class="fa fa-key mr-1"></i><?php echo _l('API Key'); ?></th>
                                        <th><i class="fa fa-shield mr-1"></i><?php echo _l('Permissions'); ?></th>
                                        <th><i class="fa fa-cogs mr-1"></i><?php echo _l('Actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="api-users-table">
                                    <!-- API users will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body api-docs-panel">
                        <div class="d-flex align-items-center mb-4">
                            <div class="panel-icon mr-3">
                                <i class="fa fa-book text-success"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo _l('API Documentation'); ?></h5>
                                <small class="text-muted"><?php echo _l('Interactive API documentation with Swagger UI'); ?></small>
                            </div>
                        </div>

                        <div class="api-docs-container" style="border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; min-height: 500px;">
                            <div id="swagger-ui"></div>
                        </div>
                        <div class="mt-3">
                            <div class="alert alert-info" role="alert">
                                <i class="fa fa-info-circle mr-2"></i>
                                <strong><?php echo _l('Swagger Documentation'); ?>:</strong>
                                <?php echo _l('Use this interactive interface to explore API endpoints, test requests, and view responses directly from your browser.'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body api-logs-panel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <div class="panel-icon mr-3">
                                    <i class="fa fa-list-alt text-warning"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo _l('API Logs'); ?>
                                        <?php if (($api_logs_pages ?? 1) > 1) { ?>
                                            <small class="text-muted"> <?php echo sprintf(_l('ticket_logs_pagination_label'), (int)($api_logs_start ?? 0), (int)($api_logs_end ?? 0), (int)($api_logs_total ?? 0)); ?></small>
                                        <?php } ?>
                                    </h5>
                                    <small class="text-muted"><?php echo _l('Monitor and review API request activity'); ?></small>
                                </div>
                            </div>
                            <div class="export-buttons">
                                <button type="button" class="btn btn-success btn-sm mr-2" onclick="exportApiLogs('csv')" title="<?php echo _l('Export CSV'); ?>">
                                    <i class="fa fa-file-excel-o mr-1"></i><?php echo _l('CSV'); ?>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm mr-2" onclick="exportApiLogs('xls')" title="<?php echo _l('Export Excel'); ?>">
                                    <i class="fa fa-file-excel-o mr-1"></i><?php echo _l('Excel'); ?>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="exportApiLogs('pdf')" title="<?php echo _l('Export PDF'); ?>">
                                    <i class="fa fa-file-pdf-o mr-1"></i><?php echo _l('PDF'); ?>
                                </button>
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
                                                    <i class="fa fa-user mr-1"></i><?php echo _l('Username'); ?>
                                                </label>
                                                <input type="text" class="form-control form-control-sm" id="search_username" value="<?php echo htmlspecialchars($search_filters['search_username'] ?? ''); ?>" placeholder="<?php echo _l('Username'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-link mr-1"></i><?php echo _l('Endpoint'); ?>
                                                </label>
                                                <input type="text" class="form-control form-control-sm" id="search_endpoint" value="<?php echo htmlspecialchars($search_filters['search_endpoint'] ?? ''); ?>" placeholder="<?php echo _l('Endpoint'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-key mr-1"></i><?php echo _l('API Key'); ?>
                                                </label>
                                                <input type="text" class="form-control form-control-sm" id="search_api_key" value="<?php echo htmlspecialchars($search_filters['search_api_key'] ?? ''); ?>" placeholder="<?php echo _l('API Key'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-3">
                                            <div class="form-group mb-0">
                                                <label class="input-label">
                                                    <i class="fa fa-hashtag mr-1"></i><?php echo _l('Status Code'); ?>
                                                </label>
                                                <input type="number" class="form-control form-control-sm" id="search_status" value="<?php echo htmlspecialchars($search_filters['search_status'] ?? ''); ?>" placeholder="<?php echo _l('Status Code'); ?>">
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
                                        <th><?php echo _l('User'); ?></th>
                                        <th><?php echo _l('Endpoint'); ?></th>
                                        <th><?php echo _l('API Key'); ?></th>
                                        <th><?php echo _l('Status'); ?></th>
                                        <th><?php echo _l('Response'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="api-logs-table">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fa fa-spinner fa-spin"></i> <?php echo _l('Loading API logs'); ?>...
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                        <!-- Pagination will be rendered by JavaScript -->
                        <div id="api-logs-pagination-container" class="tw-mt-3"></div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add API User Modal -->
<div class="modal fade" id="addApiUserModal" tabindex="-1" role="dialog" aria-labelledby="addApiUserModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addApiUserModalLabel"><?php echo _l('Add API User'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="modal_username"><?php echo _l('Username'); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="modal_username" name="username" placeholder="e.g. api_user_1" required>
                    <small class="form-text text-muted"><?php echo _l('Unique identifier for the API user'); ?></small>
                </div>
                <div class="form-group">
                    <label><?php echo _l('API Permissions'); ?> <span class="text-danger">*</span></label>
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="modal_perm_get" id="modal_perm_get">
                        <label for="modal_perm_get">GET</label>
                    </div>
                    <div class="checkbox checkbox-primary mt-1">
                        <input type="checkbox" name="modal_perm_post" id="modal_perm_post">
                        <label for="modal_perm_post">POST</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('Cancel'); ?></button>
                <button type="button" class="btn btn-success" onclick="addApiUser()"><?php echo _l('Create API User'); ?> <i class="fa fa-plus" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- API Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary" style="border-radius: 6px 6px 0 0;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true" style="font-size: 28px; line-height: 1;">&times;</span>
                </button>
                <h4 class="modal-title" id="responseModalLabel" style="color: white; margin: 0;">
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
                            <button type="button" class="btn btn-success btn-sm" onclick="copyResponseToClipboard()" title="Copy full response to clipboard" style="font-weight: 500; padding: 6px 12px;">
                                <i class="fa fa-copy" style="margin-right: 6px;"></i>
                                Copy Response
                            </button>
                        </div>
                        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px; position: relative;">
                            <pre id="responseContent" style="margin: 0; padding: 10px; background: white; border: 1px solid #dee2e6; border-radius: 4px; white-space: pre-wrap; word-wrap: break-word; font-family: 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'Liberation Mono', 'Courier New', monospace; font-size: 13px; line-height: 1.4; max-height: 500px; overflow-y: auto; color: #212529;"></pre>
                        </div>
                        <div class="mt-3" style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #e9ecef;">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i>
                                <strong>Tip:</strong> Use the copy button above to copy the entire response, or scroll within the code block to view all content.
                            </small>
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
<link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@3.25.0/swagger-ui.css" />
<script src="https://unpkg.com/swagger-ui-dist@3.25.0/swagger-ui-bundle.js"></script>
<script>
// Function to delete API user (currently just removes from table, could be enhanced later)
function deleteApiUser(button) {
    if (confirm('Are you sure you want to delete this API user?')) {
        $(button).closest('tr').remove();
        // Note: In a full implementation, this would make an AJAX call to delete from database
    }
}

// Toast notification function
function showToast(message, type = 'success', copyBtn = null) {
    // Use the CRM's built-in alert_float function for consistent UI
    var alertType = type === 'success' ? 'success' : 'danger';
    alert_float(alertType, message);

    // Uncomment below if you want to debug the full toast
    /*
    var toastClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var toastIcon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

    var toastHtml = '<div class="toast-notification" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; min-width: 300px; max-width: 400px; background: white; border: 1px solid #ccc; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">' +
        '<div class="alert ' + toastClass + ' alert-dismissible fade show" style="margin-bottom: 0; border: none;">' +
        '<i class="fa ' + toastIcon + '"></i> ' + message +
        '<button type="button" class="close" data-dismiss="alert" style="font-size: 18px;">&times;</button>' +
        '</div></div>';

    // Add toast to body center
    $('body').append(toastHtml);
    $('.toast-notification').hide().fadeIn(300);

    // Auto hide after 3 seconds
    setTimeout(function() {
        $('.toast-notification').fadeOut(300, function() { $(this).remove(); });
    }, 3000);
    */
}

function copyApiKey(apiKey) {
    // Capture the copy button that was clicked
    var copyBtn = event.target;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        // Modern clipboard API
        navigator.clipboard.writeText(apiKey).then(function() {
            // Success - show toast notification
            showToast('API Key copied to clipboard successfully!', 'success', copyBtn);
        }).catch(function(err) {
            fallbackCopyTextToClipboard(apiKey, copyBtn);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyTextToClipboard(apiKey, copyBtn);
    }
}

function fallbackCopyTextToClipboard(text, copyBtn) {
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
            // Success - show toast notification
            showToast('API Key copied to clipboard successfully!', 'success', copyBtn);
        } else {
            showToast('Failed to copy API Key', 'error', copyBtn);
        }
    } catch (err) {
        showToast('Failed to copy API Key', 'error', copyBtn);
    }

    textArea.remove();
}

function showResponseModal(responseBody) {
    // Set the full response content in the modal
    $('#responseContent').text(responseBody);
    // Show the modal
    $('#responseModal').modal('show');
}

function showResponseModalData(element) {
    // Get the base64 encoded response from data attribute
    var safeResponse = $(element).data('response');
    // Decode from base64
    var responseBody = decodeURIComponent(escape(atob(safeResponse)));

    // Pretty format JSON responses
    var formattedResponse = formatJsonResponse(responseBody);

    // Set the full response content in the modal
    $('#responseContent').text(formattedResponse);
    // Show the modal
    $('#responseModal').modal('show');
}

function formatJsonResponse(responseText) {
    if (!responseText || typeof responseText !== 'string') {
        return responseText;
    }

    // Check if the response looks like JSON
    responseText = responseText.trim();

    // Try to parse as JSON
    try {
        var jsonObj = JSON.parse(responseText);
        // If successful, return pretty-printed JSON
        return JSON.stringify(jsonObj, null, 2);
    } catch (e) {
        // If not valid JSON, return as-is
        return responseText;
    }
}

function copyResponseToClipboard() {
    var responseText = $('#responseContent').text();

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(responseText).then(function() {
            showToast('Response copied to clipboard successfully!');
        }).catch(function(err) {
            // Fallback for older browsers
            fallbackCopyTextToClipboard(responseText);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyTextToClipboard(responseText);
    }
}

function addApiUser() {
    var username = $('#modal_username').val();
    var permGet = $('#modal_perm_get').is(':checked');
    var permPost = $('#modal_perm_post').is(':checked');

    $.ajax({
        url: admin_url + 'api_settings/add_api_user',
        type: 'POST',
        data: {
            username: username,
            perm_get: permGet,
            perm_post: permPost
        },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                // Reload the API users table
                loadApiUsers();

                // Close modal and reset
                $('#addApiUserModal').modal('hide');
                $('#modal_username').val('');
                $('#modal_perm_get').prop('checked', false);
                $('#modal_perm_post').prop('checked', false);
            } else {
                alert(data.message || 'Error adding user');
            }
        },
        error: function(xhr, status, error) {
            alert('Error adding user: ' + error);
        }
    });
}

function loadApiUsers() {
    $.ajax({
        url: admin_url + 'api_settings/get_api_users',
        type: 'GET',
        dataType: 'json',
        success: function(users) {
            var html = '';
            users.forEach(function(user) {
                var permText = '';
                if (user.perm_get == 1) permText += 'GET ';
                if (user.perm_post == 1) permText += 'POST ';

            var shortKey = user.api_key.substring(0, 8) + '...';
            html += '<tr>' +
                '<td>' + user.username + '</td>' +
                '<td><code>' + shortKey + '</code> <button class="btn btn-xs btn-info" onclick="copyApiKey(\'' + user.api_key + '\')" title="Copy full API key"><i class="fa fa-copy"></i></button></td>' +
                '<td>' + permText.trim() + '</td>' +
                '<td><button class="btn btn-sm btn-danger" onclick="deleteApiUser(this, ' + user.id + ')">Delete</button></td>' +
                '</tr>';
            });
            $('#api-users-table').html(html);
        },
        error: function(xhr, status, error) {
            $('#api-users-table').html('<tr><td colspan="4" class="text-danger">Error loading API users</td></tr>');
        }
    });
}

// Global functions for search, export and navigation
var performSearch = function(clear = false) {
    var searchData = {
        page: 1,
        search_username: clear ? '' : $('#search_username').val(),
        search_endpoint: clear ? '' : $('#search_endpoint').val(),
        search_api_key: clear ? '' : $('#search_api_key').val(),
        search_status: clear ? '' : $('#search_status').val()
    };

    loadApiLogs(searchData);
};

var clearSearch = function() {
    $('#search_username').val('');
    $('#search_endpoint').val('');
    $('#search_api_key').val('');
    $('#search_status').val('');
    performSearch(true);
};

var loadApiLogs = function(searchData = {}) {
    if (!searchData.search_username) searchData.search_username = $('#search_username').val() || '';
    if (!searchData.search_endpoint) searchData.search_endpoint = $('#search_endpoint').val() || '';
    if (!searchData.search_api_key) searchData.search_api_key = $('#search_api_key').val() || '';
    if (!searchData.search_status) searchData.search_status = $('#search_status').val() || '';
    if (!searchData.page) searchData.page = 1;

    // Show searching overlay
    $('#searching-overlay').show();

    $.ajax({
        url: admin_url + 'api_settings/get_filtered_logs',
        type: 'POST',
        data: searchData,
        dataType: 'json',
        success: function(data) {
            renderTable(data.logs);
            renderPagination(data);
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Error loading API logs: ' + error);
        },
        complete: function() {
            // Hide searching overlay
            $('#searching-overlay').hide();
        }
    });
};

var renderTable = function(logs) {
    var html = '';
    if (logs && logs.length > 0) {
        logs.forEach(function(log) {
            var user_name = log.username ?? 'N/A';
            var shortKey = log.api_key ? log.api_key.substring(0, 8) + '...' : '';
            var responseForDisplay = log.formatted_response ?? log.response_body ?? '';
            var previewText = responseForDisplay.replace(/(\r\n|\n|\r)/g, ' ').trim();
            var truncatedResponse = previewText.substring(0, 50);
            if (previewText.length > 50) {
                truncatedResponse += '...';
            }
            if (!truncatedResponse || truncatedResponse === 'No response data available') {
                truncatedResponse = 'No response data';
            }
            var safeResponse = btoa(unescape(encodeURIComponent(responseForDisplay)));
            html += '<tr>' +
                '<td>' + (log.created_at ? formatDateTime(log.created_at) : '') + '</td>' +
                '<td>' + escapeHtml(user_name) + '</td>' +
                '<td>' + escapeHtml(log.endpoint || '') + '</td>' +
                '<td><code>' + escapeHtml(shortKey) + '</code></td>' +
                '<td>' + escapeHtml(log.status_code || '') + '</td>' +
                '<td><span onclick="showResponseModalData(this)" data-response="' + safeResponse + '" class="text-primary" style="cursor: pointer;" title="Click to view full response">' + escapeHtml(truncatedResponse) + '</span></td>' +
                '</tr>';
        });
    } else {
        html = '<tr><td colspan="6" class="text-center text-muted"><?php echo _l('No API logs found'); ?></td></tr>';
    }
    $('#api-logs-table').html(html);
};

var renderPagination = function(data) {
    if (data.pages > 1) {
        // Update page display - target only the API Logs section (last panel)
        var logTitle = '<?php echo _l('API Logs'); ?>' +
            '<small> <?php echo sprintf(_l('ticket_logs_pagination_label'), "' + data.start + '", "' + data.end + '", "' + data.total + '"); ?></small>';
        $('.panel_s:last .panel-body h5').html(logTitle);

        var paginationHtml = '<div class="tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-2 tw-mt-3">' +
            '<div class="tw-text-sm tw-text-neutral-600"><?php echo sprintf(_l('ticket_logs_pagination_label'), "' + data.start + '", "' + data.end + '", "' + data.total + '"); ?></div>' +
            '<ul class="pagination tw-m-0">';

        if (data.page > 1) {
            paginationHtml += '<li><a href="#" onclick="changePage(' + (data.page - 1) + '); return false;">&laquo;</a></li>';
        }

        var startPage = Math.max(1, data.page - 2);
        var endPage = Math.min(data.pages, data.page + 2);

        for (var p = startPage; p <= endPage; p++) {
            var activeClass = (p == data.page) ? ' class="active"' : '';
            paginationHtml += '<li' + activeClass + '><a href="#" onclick="changePage(' + p + '); return false;">' + p + '</a></li>';
        }

        if (data.page < data.pages) {
            paginationHtml += '<li><a href="#" onclick="changePage(' + (data.page + 1) + '); return false;">&raquo;</a></li>';
        }

        paginationHtml += '</ul></div>';

        // Replace existing pagination or add to container
        $('#api-logs-pagination-container').html(paginationHtml);
    } else {
        // Remove pagination if it exists and reset title
        $('#api-logs-pagination-container').html('');
        $('.panel_s:last .panel-body h5').html('<?php echo _l('API Logs'); ?>');
    }
};

var changePage = function(page) {
    var searchData = {
        page: page,
        search_username: $('#search_username').val() || '',
        search_endpoint: $('#search_endpoint').val() || '',
        search_api_key: $('#search_api_key').val() || '',
        search_status: $('#search_status').val() || ''
    };

    loadApiLogs(searchData);
};

var exportApiLogs = function(format) {
    var searchData = {
        format: format,
        search_username: $('#search_username').val() || '',
        search_endpoint: $('#search_endpoint').val() || '',
        search_api_key: $('#search_api_key').val() || '',
        search_status: $('#search_status').val() || ''
    };

    $.ajax({
        url: admin_url + 'api_settings/export_api_logs',
        type: 'POST',
        data: searchData,
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                alert_float('success', 'Export initiated successfully!');
            } else {
                alert_float('danger', data.message || 'Export failed');
            }
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Export failed: ' + error);
        }
    });
};

var navigatePage = function(page) {
    changePage(page);
};

// Utility functions
function formatDateTime(dateStr) {
    return new Date(dateStr).toLocaleString();
}

function escapeHtml(text) {
    var textElement = document.createElement('div');
    textElement.innerText = text;
    return textElement.innerHTML;
}

// Load Swagger UI
$(function() {
    const ui = SwaggerUIBundle({
        url: '<?php echo base_url(); ?>/api/v1/swagger.json',
        dom_id: '#swagger-ui',
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIBundle.presets.standalone
        ],
        plugins: [
            SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: "BaseLayout"
    });

    // Load existing API users and logs on page load
    loadApiUsers();
    loadApiLogs(); // Load initial API logs via AJAX
});
</script>
