<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.bulk-upload-progress {
    margin-bottom: 30px;
}

.step-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step-title {
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
}

.step-connector {
    width: 80px;
    height: 2px;
    background-color: #e0e0e0;
    margin: 0 10px;
    position: relative;
    z-index: 0;
}

.step.active .step-number {
    background-color: #007bff;
    color: white;
}

.step.completed .step-number {
    background-color: #28a745;
    color: white;
}

.step.pending .step-number {
    background-color: #6c757d;
    color: white;
}

.step.active ~ .step-connector {
    background-color: #007bff;
}

.step.completed ~ .step-connector {
    background-color: #28a745;
}

/* Step content sections */
.step-content {
    display: none;
}

.step-content.active {
    display: block;
}

/* Enhanced styling for better UX */
.alert-step-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
    border-left: 4px solid #17a2b8;
}

.alert-step-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-step-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
    border-left: 4px solid #ffc107;
}

.btn-step-primary {
    background-color: #007bff;
    border-color: #007bff;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: 500;
}

.btn-step-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.btn-step-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    padding: 10px 20px;
    font-size: 16px;
}

.btn-step-secondary:hover {
    background-color: #545b62;
    border-color: #545b62;
}

.table-step-preview {
    margin-top: 20px;
}

.table-step-preview th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-top: 2px solid #dee2e6;
}

.table-step-preview td {
    vertical-align: middle;
}

.validation-summary {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.validation-summary .summary-item {
    display: inline-block;
    margin-right: 20px;
    font-weight: 500;
}

.validation-summary .summary-item.success {
    color: #28a745;
}

.validation-summary .summary-item.error {
    color: #dc3545;
}

.validation-summary .summary-item.warning {
    color: #ffc107;
}
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Progress Steps -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="bulk-upload-progress">
                                    <div class="step-indicator">
                                        <div class="step step-1 active" id="step-1-indicator">
                                            <span class="step-number">1</span>
                                            <span class="step-title">Upload & Validate</span>
                                        </div>
                                        <div class="step-connector"></div>
                                        <div class="step step-2" id="step-2-indicator">
                                            <span class="step-number">2</span>
                                            <span class="step-title">Create Staff</span>
                                        </div>
                                        <div class="step-connector"></div>
                                        <div class="step step-3" id="step-3-indicator">
                                            <span class="step-number">3</span>
                                            <span class="step-title">Update Managers</span>
                                        </div>
                                        <div class="step-connector"></div>
                                        <div class="step step-4" id="step-4-indicator">
                                            <span class="step-number">4</span>
                                            <span class="step-title">Complete</span>
                                        </div>
                                    </div>
                                </div>
                                <hr class="hr-panel-heading" />
                            </div>
                        </div>

                        <!-- Step 1: Upload & Validate -->
                        <div id="step-1-content" class="step-content active">
                            <div class="alert alert-step-info">
                                <h4><i class="fa fa-upload"></i> Step 1: Upload & Validate CSV File</h4>
                                <p>Upload your CSV file containing staff data. The system will validate the data format and check for any errors before proceeding.</p>
                            </div>

                            <?php echo form_open_multipart(admin_url('staff/bulk_upload'), ['id' => 'bulk-upload-form']); ?>
                            <input type="hidden" name="confirmed_data" id="confirmed-data" value="">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="file" class="control-label">Select CSV File <span class="text-danger">*</span></label>
                                        <input type="file" name="file" id="file" class="form-control" accept=".csv" required>
                                        <small class="form-text text-muted">
                                            Only CSV files are supported. Maximum file size: 10MB
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label><br>
                                        <a href="<?php echo site_url('sample_staff_upload.csv'); ?>" class="btn btn-success btn-block" download>
                                            <i class="fa fa-download"></i>
                                            Download Sample CSV
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" id="preview-btn" class="btn btn-step-primary">
                                        <i class="fa fa-eye"></i>
                                        Preview & Validate Data
                                    </button>
                                    <a href="<?php echo admin_url('staff'); ?>" class="btn btn-step-secondary">
                                        <i class="fa fa-arrow-left"></i>
                                        Back to Staff List
                                    </a>
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>

                        <!-- Step 2: Create Staff -->
                        <div id="step-2-content" class="step-content">
                            <div class="alert alert-step-success">
                                <h4><i class="fa fa-user-plus"></i> Step 2: Create Staff Members</h4>
                                <p>Data validation completed successfully. Now create staff accounts with departments and basic information.</p>
                            </div>

                            <div class="validation-summary">
                                <div class="summary-item success">
                                    <i class="fa fa-check-circle"></i>
                                    <strong id="valid-rows-count">0</strong> Valid Rows
                                </div>
                                <div class="summary-item error" id="error-count" style="display: none;">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    <strong id="error-rows-count">0</strong> Errors Found
                                </div>
                            </div>

                            <div class="table-step-preview">
                                <table id="preview-table" class="table table-striped table-bordered">
                                    <thead id="preview-table-head">
                                        <!-- Headers will be populated by JavaScript -->
                                    </thead>
                                    <tbody id="preview-table-body">
                                        <!-- Data rows will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                            <div id="validation-errors" class="alert alert-danger" style="display: none;">
                                <h5><i class="fa fa-exclamation-triangle"></i> Validation Errors Found:</h5>
                                <ul id="error-list"></ul>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" id="create-staff-btn" class="btn btn-step-primary" disabled>
                                        <i class="fa fa-user-plus"></i>
                                        Create Staff Members
                                        (<span id="create-count">0</span> staff)
                                    </button>
                                    <button type="button" id="back-to-step1" class="btn btn-step-secondary">
                                        <i class="fa fa-arrow-left"></i>
                                        Back to Upload
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Update Managers -->
                        <div id="step-3-content" class="step-content">
                            <div class="alert alert-step-warning">
                                <h4><i class="fa fa-users"></i> Step 3: Update Reporting Managers</h4>
                                <p>Staff members have been created successfully. Now update their reporting manager relationships.</p>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th><i class="fa fa-user"></i> Staff Name</th>
                                                    <th><i class="fa fa-id-badge"></i> Employee Code</th>
                                                    <th><i class="fa fa-hashtag"></i> Staff ID</th>
                                                    <th><i class="fa fa-user-tie"></i> Reporting Manager</th>
                                                    <th><i class="fa fa-check-circle"></i> Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="created-staff-table">
                                                <!-- Created staff will be populated here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" id="update-managers-btn" class="btn btn-step-primary">
                                        <i class="fa fa-users"></i>
                                        Update Reporting Managers
                                    </button>
                                    <a href="<?php echo admin_url('staff'); ?>" class="btn btn-step-secondary">
                                        <i class="fa fa-arrow-left"></i>
                                        Skip & Go to Staff List
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Complete -->
                        <div id="step-4-content" class="step-content">
                            <div class="alert alert-step-success">
                                <h4><i class="fa fa-check-circle"></i> Step 4: Bulk Upload Complete!</h4>
                                <p>All staff members have been successfully created and configured.</p>
                            </div>

                            <div class="text-center">
                                <div class="completion-summary">
                                    <h3><i class="fa fa-trophy text-success"></i></h3>
                                    <h4 class="text-success">Bulk Upload Completed Successfully!</h4>
                                    <p id="completion-message">All staff members have been created with their departments and reporting managers properly configured.</p>
                                    <br>
                                    <a href="<?php echo admin_url('staff'); ?>" class="btn btn-step-primary btn-lg">
                                        <i class="fa fa-users"></i>
                                        View Staff List
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Error Modal -->
<div class="modal fade" id="detailedErrorModal" tabindex="-1" role="dialog" aria-labelledby="detailedErrorModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="detailedErrorModalLabel">
                    <i class="fa fa-exclamation-triangle text-danger"></i>
                    Detailed Error Information
                </h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>Staff creation failed due to the following errors:</strong>
                </div>
                <div id="detailed-error-content">
                    <!-- Error details will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="fix-retry-btn">Fix & Retry</button>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    let csvData = [];
    let csvHeaders = [];
    let currentStep = 1;

    // Initialize step display
    showStep(1);

    function showStep(stepNumber) {
        currentStep = stepNumber;

        // Hide all steps
        $('.step-content').removeClass('active');

        // Show current step
        $('#step-' + stepNumber + '-content').addClass('active');

        // Update step indicators
        $('.step').removeClass('active completed pending');
        for (let i = 1; i <= 4; i++) {
            if (i < stepNumber) {
                $('#step-' + i + '-indicator').addClass('completed');
            } else if (i === stepNumber) {
                $('#step-' + i + '-indicator').addClass('active');
            } else {
                $('#step-' + i + '-indicator').addClass('pending');
            }
        }
    }

    // Step 1: Preview & Validate button
    $('#preview-btn').on('click', function() {
        var fileInput = $('#file')[0];
        if (!fileInput.files[0]) {
            alert('Please select a CSV file to upload.');
            return;
        }

        var file = fileInput.files[0];
        var fileName = file.name;
        var fileExtension = fileName.split('.').pop().toLowerCase();

        if (fileExtension !== 'csv') {
            alert('Invalid file type. Please select a CSV file.');
            return;
        }

        // Show loading
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        var reader = new FileReader();
        reader.onload = function(e) {
            try {
                processCSV(e.target.result);
            } catch (error) {
                alert('Error processing file: ' + error.message);
                $('#preview-btn').prop('disabled', false).html('<i class="fa fa-eye"></i> Preview & Validate Data');
            }
        };

        reader.readAsText(file);
    });

    function processCSV(csvText) {
        var lines = csvText.split('\n').filter(function(line) {
            return line.trim().length > 0;
        });

        if (lines.length < 2) {
            alert('File must contain at least a header row and one data row.');
            $('#preview-btn').prop('disabled', false).html('<i class="fa fa-eye"></i> Preview & Validate Data');
            return;
        }

        // Parse CSV
        csvData = [];
        for (var i = 0; i < lines.length; i++) {
            var row = parseCSVLine(lines[i]);
            csvData.push(row);
        }

        csvHeaders = csvData.shift(); // Remove header row

        // Validate headers
        var requiredColumns = ['firstname', 'emp_code'];
        var foundRequired = 0;
        for (var i = 0; i < csvHeaders.length; i++) {
            var header = csvHeaders[i].toLowerCase().trim();
            if (requiredColumns.includes(header)) {
                foundRequired++;
            }
        }

        if (foundRequired < 2) {
            alert('File must contain both "firstname" and "emp_code" columns.');
            $('#preview-btn').prop('disabled', false).html('<i class="fa fa-eye"></i> Preview & Validate Data');
            return;
        }

        // Move to Step 2 and validate data
        showStep(2);
        displayPreview();
        validateData();
        $('#preview-btn').prop('disabled', false).html('<i class="fa fa-eye"></i> Preview & Validate Data');
    }

    function parseCSVLine(line) {
        var result = [];
        var current = '';
        var inQuotes = false;

        for (var i = 0; i < line.length; i++) {
            var char = line[i];

            if (char === '"') {
                if (inQuotes && line[i + 1] === '"') {
                    current += '"';
                    i++; // Skip next quote
                } else {
                    inQuotes = !inQuotes;
                }
            } else if (char === ',' && !inQuotes) {
                result.push(current.trim());
                current = '';
            } else {
                current += char;
            }
        }

        result.push(current.trim());
        return result;
    }

    function displayPreview() {
        $('#preview-table-head').empty();
        $('#preview-table-body').empty();

        // Add headers
        var headerRow = '<tr>';
        for (var i = 0; i < csvHeaders.length; i++) {
            headerRow += '<th>' + escapeHtml(csvHeaders[i]) + '</th>';
        }
        headerRow += '<th>Status</th></tr>';
        $('#preview-table-head').html(headerRow);

        // Add data rows (limit to first 10 for preview)
        var displayRows = csvData.slice(0, 10);
        var tbody = '';

        for (var i = 0; i < displayRows.length; i++) {
            tbody += '<tr>';
            for (var j = 0; j < displayRows[i].length; j++) {
                tbody += '<td>' + escapeHtml(displayRows[i][j]) + '</td>';
            }
            tbody += '<td><span class="label label-default">Pending Validation</span></td></tr>';
        }

        $('#preview-table-body').html(tbody);

        // Show message if there are more rows
        if (csvData.length > 10) {
            $('#preview-table-body').append('<tr><td colspan="' + (csvHeaders.length + 1) + '" class="text-center text-muted">Showing first 10 rows (' + csvData.length + ' total rows)</td></tr>');
        }
    }

    function validateData() {
        var errors = [];
        var validCount = 0;

        // Validate each row
        for (var i = 0; i < csvData.length; i++) {
            var row = csvData[i];
            var rowErrors = validateRow(row, i + 2);

            if (rowErrors.length === 0) {
                validCount++;
            } else {
                errors = errors.concat(rowErrors);
            }
        }

        // If no basic validation errors, check database references
        if (errors.length === 0) {
            validateDatabaseReferences(function(dbErrors) {
                errors = errors.concat(dbErrors);
                displayValidationResults(errors, validCount);
            });
        } else {
            displayValidationResults(errors, validCount);
        }
    }

    function validateDatabaseReferences(callback) {
        var dbErrors = [];

        // Prepare data for server validation
        var validationData = {
            headers: csvHeaders,
            data: csvData
        };

        // Make AJAX call to validate references
        $.ajax({
            url: '<?php echo admin_url('staff/validate_bulk_upload_references'); ?>',
            type: 'POST',
            data: {
                validation_data: JSON.stringify(validationData),
                [csrfData.token_name]: csrfData.hash
            },
            dataType: 'json',
            success: function(response) {
                if (response.errors && response.errors.length > 0) {
                    dbErrors = response.errors;
                }
                callback(dbErrors);
            },
            error: function() {
                dbErrors.push('Failed to validate database references. Please try again.');
                callback(dbErrors);
            }
        });
    }

    function displayValidationResults(errors, validCount) {
        $('#valid-rows-count').text(validCount);
        $('#create-count').text(validCount);

        if (errors.length > 0) {
            $('#error-count').show();
            $('#error-rows-count').text(errors.length);

            var errorHtml = '';
            var displayErrors = errors.slice(0, 10);
            for (var i = 0; i < displayErrors.length; i++) {
                errorHtml += '<li>' + displayErrors[i] + '</li>';
            }

            if (errors.length > 10) {
                errorHtml += '<li>And ' + (errors.length - 10) + ' more errors...</li>';
            }

            $('#error-list').html(errorHtml);
            $('#create-staff-btn').prop('disabled', true);
        } else {
            $('#error-count').hide();
            $('#validation-errors').hide();
            $('#create-staff-btn').prop('disabled', false);
        }
    }

    function validateRow(row, rowNumber) {
        var errors = [];

        // Map columns
        var columnMap = {};
        for (var i = 0; i < csvHeaders.length; i++) {
            columnMap[csvHeaders[i].toLowerCase().trim()] = i;
        }

        // Check required fields
        var firstname = getColumnValue(row, columnMap, 'firstname');
        var emp_code = getColumnValue(row, columnMap, 'emp_code');

        if (!firstname) {
            errors.push('Row ' + rowNumber + ': First name is required');
        }

        if (!emp_code) {
            errors.push('Row ' + rowNumber + ': Employee code is required');
        }

        // Validate email format if provided
        var email = getColumnValue(row, columnMap, 'email');
        if (email && !isValidEmail(email)) {
            errors.push('Row ' + rowNumber + ': Invalid email format');
        }

        // Check field lengths
        if (firstname && firstname.length > 50) {
            errors.push('Row ' + rowNumber + ': First name is too long (maximum 50 characters)');
        }

        var lastname = getColumnValue(row, columnMap, 'lastname');
        if (lastname && lastname.length > 50) {
            errors.push('Row ' + rowNumber + ': Last name is too long (maximum 50 characters)');
        }

        var phone = getColumnValue(row, columnMap, 'phone');
        if (phone && phone.length > 20) {
            errors.push('Row ' + rowNumber + ': Phone number is too long (maximum 20 characters)');
        }

        return errors;
    }

    function getColumnValue(row, columnMap, columnName) {
        var index = columnMap[columnName];
        return (index !== undefined && row[index]) ? row[index].trim() : '';
    }

    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // Step 2: Create Staff button
    $('#create-staff-btn').on('click', function() {
        if (csvData.length === 0) {
            alert('No data to import.');
            return;
        }

        if (!confirm('Are you sure you want to create ' + csvData.length + ' staff members?')) {
            return;
        }

        // Move to Step 3 and start creation process
        showStep(3);
        startStaffCreation();
    });

    function startStaffCreation() {
        // Make AJAX call to create staff
        $.ajax({
            url: '<?php echo admin_url('staff/complete_bulk_import_step1'); ?>',
            type: 'POST',
            data: {
                confirmed_data: JSON.stringify({
                    headers: csvHeaders,
                    data: csvData
                }),
                [csrfData.token_name]: csrfData.hash
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Populate created staff table
                    populateCreatedStaffTable(response.created_staff || []);
                    showStep(3);
                } else {
                    // Show detailed error modal
                    showDetailedErrorModal(response);
                    showStep(2);
                }
            },
            error: function(xhr, status, error) {
                // Show generic error modal for network/AJAX errors
                var errorMessage = 'A network error occurred while processing your request. Please try again.';
                if (xhr.responseText) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            errorMessage = response.error;
                        }
                    } catch (e) {
                        // If JSON parsing fails, show generic message
                    }
                }

                $('#detailed-error-content').html('<p>' + escapeHtml(errorMessage) + '</p>');
                $('#detailedErrorModal').modal('show');
                showStep(2);
            }
        });
    }

    function showDetailedErrorModal(response) {
        var errorContent = '';

        if (response.detailed_errors && response.detailed_errors.length > 0) {
            errorContent += '<div class="table-responsive">';
            errorContent += '<table class="table table-striped table-bordered">';
            errorContent += '<thead>';
            errorContent += '<tr>';
            errorContent += '<th><i class="fa fa-exclamation-triangle"></i> Error Details</th>';
            errorContent += '</tr>';
            errorContent += '</thead>';
            errorContent += '<tbody>';

            for (var i = 0; i < response.detailed_errors.length; i++) {
                errorContent += '<tr>';
                errorContent += '<td>' + escapeHtml(response.detailed_errors[i]) + '</td>';
                errorContent += '</tr>';
            }

            errorContent += '</tbody>';
            errorContent += '</table>';
            errorContent += '</div>';

            if (response.error_count > response.detailed_errors.length) {
                errorContent += '<div class="alert alert-warning">';
                errorContent += '<i class="fa fa-info-circle"></i> ';
                errorContent += 'Showing ' + response.detailed_errors.length + ' of ' + response.error_count + ' total errors.';
                errorContent += '</div>';
            }
        } else {
            errorContent += '<p>' + escapeHtml(response.error || 'Unknown error occurred') + '</p>';
        }

        $('#detailed-error-content').html(errorContent);
        $('#detailedErrorModal').modal('show');
    }

    function populateCreatedStaffTable(createdStaff) {
        var tbody = '';
        for (var i = 0; i < createdStaff.length; i++) {
            var staff = createdStaff[i];
            tbody += '<tr>';
            tbody += '<td>' + escapeHtml(staff.firstname + ' ' + staff.lastname) + '</td>';
            tbody += '<td>' + escapeHtml(staff.emp_code) + '</td>';
            tbody += '<td>' + escapeHtml(staff.staff_id) + '</td>';

            // Display manager information
            var managerDisplay = 'Not specified';
            if (staff.manager_info) {
                managerDisplay = '<div class="manager-info">';
                managerDisplay += '<strong>' + escapeHtml(staff.manager_info.name) + '</strong><br>';
                managerDisplay += '<small class="text-muted">';
                managerDisplay += 'ID: ' + escapeHtml(staff.manager_info.staffid) + ' | ';
                managerDisplay += 'Emp Code: ' + escapeHtml(staff.manager_info.emp_code);
                managerDisplay += '</small>';
                managerDisplay += '</div>';
            } else if (staff.reporting_manager) {
                managerDisplay = '<span class="text-warning">' + escapeHtml(staff.reporting_manager) + ' (Not found)</span>';
            }

            tbody += '<td>' + managerDisplay + '</td>';
            tbody += '<td><span class="label label-warning">Pending Manager Update</span></td>';
            tbody += '</tr>';
        }
        $('#created-staff-table').html(tbody);
    }

    // Step 3: Update Managers button
    $('#update-managers-btn').on('click', function() {
        // Make AJAX call to update reporting managers
        $.ajax({
            url: '<?php echo admin_url('staff/bulk_upload'); ?>',
            type: 'POST',
            data: {
                update_managers: 1,
                [csrfData.token_name]: csrfData.hash
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update status in table
                    $('#created-staff-table .label').removeClass('label-warning').addClass('label-success')
                        .html('<i class="fa fa-check"></i> Manager Updated');

                    // Show completion step
                    $('#completion-message').text(response.message || 'All staff members have been created with their departments and reporting managers properly configured.');
                    showStep(4);
                } else {
                    alert('Error updating managers: ' + response.error);
                }
            },
            error: function(xhr, status, error) {
                alert('Error updating managers: ' + error);
            }
        });
    });

    // Navigation buttons
    $('#back-to-step1').on('click', function() {
        showStep(1);
    });

    // Fix & Retry button in error modal
    $('#fix-retry-btn').on('click', function() {
        $('#detailedErrorModal').modal('hide');
        showStep(1);
    });

    // Check for bulk upload completion and show popup
    <?php if ($this->session->userdata('bulk_upload_complete')): ?>
    $(document).ready(function() {
        var completionData = <?php echo json_encode($this->session->userdata('bulk_upload_complete')); ?>;
        if (completionData && completionData.success) {
            setTimeout(function() {
                alert('🎉 Bulk Upload Completed Successfully!\n\n' + completionData.message + '\n\nYou will now be redirected to the staff list.');
                window.location.href = '<?php echo admin_url('staff'); ?>';
            }, 1000);
        }
        // Clear the session data via AJAX to prevent re-showing popup
        $.ajax({
            url: '<?php echo admin_url('staff/clear_bulk_upload_session'); ?>',
            type: 'POST',
            data: {[csrfData.token_name]: csrfData.hash},
            dataType: 'json'
        });
    });
    <?php endif; ?>
});
</script>
</body>
</html>
