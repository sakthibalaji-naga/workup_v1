<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="no-margin">Bulk Upload Divisions</h4>
                                <hr class="hr-panel-heading" />
                            </div>
                        </div>

                        <!-- File Upload Section -->
                        <div id="upload-section">
                            <?php echo form_open_multipart(admin_url('divisions/bulk_upload'), ['id' => 'bulk-upload-form']); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="file" class="control-label">Select File <span class="text-danger">*</span></label>
                                        <input type="file" name="file" id="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                                        <small class="form-text text-muted">
                                            Supported file types: CSV, Excel (.xlsx, .xls)
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" id="preview-btn" class="btn btn-info">
                                        <i class="fa fa-eye"></i>
                                        Preview Data
                                    </button>
                                    <a href="<?php echo site_url('sample_divisions_upload.csv'); ?>" class="btn btn-success" download>
                                        <i class="fa fa-download"></i>
                                        Download Sample CSV
                                    </a>
                                    <a href="<?php echo admin_url('divisions'); ?>" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i>
                                        Back to Divisions List
                                    </a>
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>

                        <!-- Preview Section (Hidden initially) -->
                        <div id="preview-section" style="display: none;">
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h4><?php echo _l('data_preview'); ?></h4>
                                    <div class="alert alert-warning">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        <?php echo _l('preview_data_warning'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="preview-table" class="table table-striped table-bordered">
                                            <thead id="preview-table-head">
                                                <!-- Headers will be populated by JavaScript -->
                                            </thead>
                                            <tbody id="preview-table-body">
                                                <!-- Data rows will be populated by JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div id="validation-errors" class="alert alert-danger" style="display: none;">
                                        <h5><?php echo _l('validation_errors_found'); ?>:</h5>
                                        <ul id="error-list"></ul>
                                    </div>

                                    <div id="validation-success" class="alert alert-success" style="display: none;">
                                        <i class="fa fa-check-circle"></i>
                                        <?php echo _l('data_validation_passed'); ?>
                                        <span id="valid-rows-count"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <?php echo form_open(admin_url('divisions/bulk_upload'), ['id' => 'import-form']); ?>
                                    <input type="hidden" name="confirmed_data" id="confirmed-data">
                                    <button type="submit" id="import-btn" class="btn btn-success" disabled>
                                        <i class="fa fa-upload"></i>
                                        <?php echo _l('confirm_and_import'); ?>
                                        (<span id="import-count">0</span> <?php echo _l('divisions'); ?>)
                                    </button>
                                    <button type="button" id="back-to-upload" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i>
                                        <?php echo _l('back_to_upload'); ?>
                                    </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h5><?php echo _l('csv_format_instructions'); ?></h5>
                                    <p><?php echo _l('bulk_upload_csv_format_desc'); ?></p>
                                    <strong><?php echo _l('required_columns'); ?>:</strong>
                                    <ul>
                                        <li><code>name</code> - <?php echo _l('division_name'); ?></li>
                                    </ul>
                                    <strong><?php echo _l('optional_columns'); ?>:</strong>
                                    <ul>
                                        <li><code>division_name</code> - <?php echo _l('division_name'); ?> (<?php echo _l('alternative_column_name'); ?>)</li>
                                    </ul>
                                    <p><strong><?php echo _l('sample_csv_format'); ?>:</strong></p>
                                    <pre>name
IT Division
HR Division
Finance Division
Operations Division</pre>
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
    let csvData = [];
    let csvHeaders = [];

    // Preview button click handler
    $('#preview-btn').on('click', function() {
        var fileInput = $('#file')[0];
        if (!fileInput.files[0]) {
            alert('Please select a file to upload.');
            return;
        }

        var file = fileInput.files[0];
        var fileName = file.name;
        var fileExtension = fileName.split('.').pop().toLowerCase();

        if (!['csv', 'xlsx', 'xls'].includes(fileExtension)) {
            alert('Invalid file type. Please select a CSV or Excel file.');
            return;
        }

        // Show loading
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        var reader = new FileReader();
        reader.onload = function(e) {
            try {
                if (fileExtension === 'csv') {
                    processCSV(e.target.result);
                } else {
                    // For Excel files, we'd need additional library
                    alert('Excel file processing is not currently supported. Please use CSV format.');
                    $('#preview-btn').prop('disabled', false).html('<i class="fa fa-eye"></i> Preview Data');
                    return;
                }
            } catch (error) {
                alert('Error processing file: ' + error.message);
                $('#preview-btn').prop('disabled', false).html('<i class="fa fa-eye"></i> Preview Data');
            }
        };

        reader.readAsText(file);
    });

    // Back to upload button
    $('#back-to-upload').on('click', function() {
        $('#preview-section').hide();
        $('#upload-section').show();
        $('#preview-btn').prop('disabled', false).html('<i class="fa fa-eye"></i> Preview Data');
        csvData = [];
        csvHeaders = [];
    });

    function processCSV(csvText) {
        var lines = csvText.split('\n').filter(function(line) {
            return line.trim().length > 0;
        });

        if (lines.length < 2) {
            alert('File must contain at least a header row and one data row.');
            $('#preview-btn').prop('disabled', false).html('<i class="fa fa-eye"></i> Preview Data');
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
        var requiredColumns = ['name'];
        var foundRequired = 0;
        for (var i = 0; i < csvHeaders.length; i++) {
            var header = csvHeaders[i].toLowerCase().trim();
            if (requiredColumns.includes(header) || header === 'division_name') {
                foundRequired++;
            }
        }

        if (foundRequired < 1) {
            alert('File must contain a name or division_name column.');
            $('#preview-btn').prop('disabled', false).html('<i class="fa fa-eye"></i> Preview Data');
            return;
        }

        // Display preview
        displayPreview();
        validateData();

        // Show preview section
        $('#upload-section').hide();
        $('#preview-section').show();
        $('#preview-btn').prop('disabled', false).html('<i class="fa fa-eye"></i> Preview Data');
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
        // Clear previous content
        $('#preview-table-head').empty();
        $('#preview-table-body').empty();

        // Add headers
        var headerRow = '<tr>';
        for (var i = 0; i < csvHeaders.length; i++) {
            headerRow += '<th>' + escapeHtml(csvHeaders[i]) + '</th>';
        }
        headerRow += '<th><?php echo _l('status'); ?></th></tr>';
        $('#preview-table-head').html(headerRow);

        // Add data rows (limit to first 10 for preview)
        var displayRows = csvData.slice(0, 10);
        var tbody = '';

        for (var i = 0; i < displayRows.length; i++) {
            tbody += '<tr>';
            for (var j = 0; j < displayRows[i].length; j++) {
                tbody += '<td>' + escapeHtml(displayRows[i][j]) + '</td>';
            }
            tbody += '<td><span class="label label-default"><?php echo _l('pending_validation'); ?></span></td></tr>';
        }

        $('#preview-table-body').html(tbody);

        // Show message if there are more rows
        if (csvData.length > 10) {
            $('#preview-table-body').append('<tr><td colspan="' + (csvHeaders.length + 1) + '" class="text-center text-muted"><?php echo _l('showing_first_10_rows'); ?> (' + csvData.length + ' <?php echo _l('total_rows'); ?>)</td></tr>');
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

        // Display validation results
        if (errors.length > 0) {
            $('#validation-errors').show();
            $('#validation-success').hide();

            var errorHtml = '';
            var displayErrors = errors.slice(0, 10); // Show first 10 errors
            for (var i = 0; i < displayErrors.length; i++) {
                errorHtml += '<li>' + displayErrors[i] + '</li>';
            }

            if (errors.length > 10) {
                errorHtml += '<li><?php echo _l('and_more_errors'); ?>'.replace('%s', (errors.length - 10)) + '</li>';
            }

            $('#error-list').html(errorHtml);
            $('#import-btn').prop('disabled', true);
        } else {
            $('#validation-errors').hide();
            $('#validation-success').show();
            $('#valid-rows-count').text('(' + validCount + ' <?php echo _l('valid_rows'); ?>)');
            $('#import-count').text(validCount);
            $('#import-btn').prop('disabled', false);

            // Prepare data for import
            $('#confirmed-data').val(JSON.stringify({
                headers: csvHeaders,
                data: csvData
            }));
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
        var name = getColumnValue(row, columnMap, 'name');
        if (!name && getColumnValue(row, columnMap, 'division_name')) {
            name = getColumnValue(row, columnMap, 'division_name');
        }

        if (!name) {
            errors.push('Row ' + rowNumber + ': Division name is required.');
        }

        // Check field lengths
        if (name && name.length > 191) {
            errors.push('Row ' + rowNumber + ': Division name is too long (maximum 191 characters).');
        }

        return errors;
    }

    function getColumnValue(row, columnMap, columnName) {
        var index = columnMap[columnName];
        return (index !== undefined && row[index]) ? row[index].trim() : '';
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // Import form submission
    $('#import-form').on('submit', function(e) {
        if (!$('#confirmed-data').val()) {
            alert('No data to import.');
            e.preventDefault();
            return false;
        }

        if (!confirm('Are you sure you want to import these divisions? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }

        // Show loading
        $('#import-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Importing...');
    });
});
</script>
</body>
</html>
