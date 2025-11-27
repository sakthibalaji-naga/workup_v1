<div role="tabpanel" class="tab-pane" id="sla">
    <!-- CSRF Token for AJAX requests -->
    <input type="hidden" id="csrf_token" name="csrf_token_name" value="<?php echo $this->security->get_csrf_hash(); ?>">
    <div class="panel_s">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-5 tw-h-5 tw-mr-1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V12zm0 3h.008v.008H6.75V12z" />
                        </svg>
                        <span>SLA Information</span>
                    </h4>
                    <div class="clearfix"></div>
                    <hr class="tw-mt-2 sm:tw-mt-0 tw-mb-4"/>
                </div>
            </div>

            <div id="sla-container">
                <!-- Dynamic SLA entries will be added here -->
                <div class="sla-entry" data-entry-id="1">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="sla_text_1">SLA Text <span class="text-danger">*</span></label>
                                <textarea id="sla_text_1" name="sla_text[]" class="form-control" rows="4" placeholder="Enter SLA information..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>SLA Attachments</label>
                                <div class="attachments-container">
                                    <input type="file" class="form-control sla-attachment" name="sla_attachments[1][]" multiple accept=".pdf,.doc,.docx,.txt,.jpeg,.jpg,.png">
                                    <div class="attachment-list" id="attachment-list-1">
                                        <!-- Attachments will appear here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="button" class="btn btn-danger btn-sm remove-sla-entry" style="display: none;" onclick="removeSLAEntry(1)">
                                <i class="fa fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-default" id="add-sla-entry">
                        <i class="fa fa-plus"></i> Add Another SLA Entry
                    </button>
                    <button type="button" class="btn btn-success" id="save-sla-entries">
                        <i class="fa fa-save"></i> Save SLA Information
                    </button>
                </div>
            </div>

            <!-- Existing SLA Entries Display -->
            <div id="existing-sla-entries">
                <!-- Existing SLA entries will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
var slaEntryCounter = 1;

// Ensure jQuery is available before running
window.addEventListener('load', function() {
    function waitForjQuery(cb){ if (window.jQuery && typeof $ === 'function') { cb(); } else { setTimeout(function(){ waitForjQuery(cb); }, 50); } }
    waitForjQuery(function(){
        // Initialize SLA functionality after jQuery is ready
        initSLAFunctionality();
    });
});

// Separate function to initialize all SLA functionality
function initSLAFunctionality() {
    // Add new SLA entry
    $('#add-sla-entry').on('click', function() {
        addNewSLAEntry();
    });

    // Save SLA entries
    $('#save-sla-entries').on('click', function() {
        saveSLAEntries();
    });

    // Handle file uploads
    $(document).on('change', '.sla-attachment', function() {
        handleFileUpload(this);
    });

    // Load existing SLA entries
    loadExistingSLAEntries();

    // Show remove button when we have more than one entry
    updateRemoveButtons();
}

function addNewSLAEntry() {
    slaEntryCounter++;
    var entryId = slaEntryCounter;

    var newEntryHtml = `
        <div class="sla-entry" data-entry-id="${entryId}">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="sla_text_${entryId}">SLA Text <span class="text-danger">*</span></label>
                        <textarea id="sla_text_${entryId}" name="sla_text[]" class="form-control" rows="4" placeholder="Enter SLA information..."></textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>SLA Attachments</label>
                        <div class="attachments-container">
                            <input type="file" class="form-control sla-attachment" name="sla_attachments[${entryId}][]" multiple accept=".pdf,.doc,.docx,.txt,.jpeg,.jpg,.png">
                            <div class="attachment-list" id="attachment-list-${entryId}">
                                <!-- Attachments will appear here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-danger btn-sm remove-sla-entry" style="display: none;" onclick="removeSLAEntry(${entryId})">
                        <i class="fa fa-trash"></i> Remove
                    </button>
                </div>
            </div>
            <hr>
        </div>
    `;

    $('#sla-container').append(newEntryHtml);
    updateRemoveButtons();

    // Initialize tinymce for the new textarea if needed
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: `#sla_text_${entryId}`,
            height: 150,
            plugins: 'lists link image paste',
            toolbar: 'bold italic underline | bullist numlist | link image | paste',
            menubar: false,
            statusbar: false
        });
    }
}

function removeSLAEntry(entryId) {
    $(`.sla-entry[data-entry-id="${entryId}"]`).remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    var entries = $('.sla-entry');
    if (entries.length > 1) {
        $('.remove-sla-entry').show();
    } else {
        $('.remove-sla-entry').hide();
    }
}

function handleFileUpload(input) {
    var files = input.files;
    var entryId = $(input).closest('.sla-entry').data('entry-id');
    var listContainer = $(`#attachment-list-${entryId}`);

    listContainer.empty();

    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        var fileSize = (file.size / 1024 / 1024).toFixed(2); // Size in MB

        var fileItem = `
            <div class="attachment-item" style="padding: 5px; border: 1px solid #ddd; margin-bottom: 5px;">
                <i class="fa fa-file"></i> ${file.name} (${fileSize} MB)
            </div>
        `;
        listContainer.append(fileItem);
    }
}

function saveSLAEntries() {
    var formData = new FormData();
    formData.append('ticketid', <?= $ticket->ticketid; ?>);
    formData.append('csrf_token_name', $('#csrf_token').val());

    // Collect SLA text and attachments
    var validEntriesCount = 0;
    $('.sla-entry').each(function(index, entry) {
        var entryId = $(entry).data('entry-id');
        var slatText = '';

        if (typeof tinymce !== 'undefined') {
            var editor = tinymce.get(`sla_text_${entryId}`);
            if (editor) {
                slatText = editor.getContent();
            } else {
                slatText = $(entry).find('textarea').val();
            }
        } else {
            slatText = $(entry).find('textarea').val();
        }

        if (slatText.trim() === '') {
            return; // Skip empty entries
        }

        formData.append('sla_text[]', slatText);
        formData.append('sla_entry_key[]', entryId);

        // Handle attachments for this entry
        var attachments = $(entry).find('.sla-attachment')[0].files;
        if (attachments && attachments.length > 0) {
            for (var i = 0; i < attachments.length; i++) {
                formData.append('sla_attachments[' + entryId + '][]', attachments[i]);
            }
        }

        validEntriesCount++;
    });

    if (validEntriesCount === 0) {
        alert_float('warning', 'Please add at least one SLA entry with text.');
        return;
    }

    $('#save-sla-entries').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: admin_url + 'tickets/save_sla_entries',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                var resp = JSON.parse(response);
                if (resp.success) {
                    alert_float('success', 'SLA entries saved successfully');
                    loadExistingSLAEntries();
                    // Clear form
                    $('.sla-entry').not(':first').remove();
                    $('.sla-entry textarea').val('');
                    $('.sla-attachment').val('');
                    $('.attachment-list').empty();
                } else {
                    alert_float('danger', resp.message || 'Failed to save SLA entries');
                }
            } catch (e) {
                console.error('Response parsing error:', e, response);
                alert_float('danger', 'Failed to save SLA entries: Invalid response format');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            alert_float('danger', 'Failed to save SLA entries: Network error');
        },
        complete: function() {
            $('#save-sla-entries').prop('disabled', false).html('<i class="fa fa-save"></i> Save SLA Information');
        }
    });
}

function loadExistingSLAEntries() {
    $.getJSON(admin_url + 'tickets/get_sla_entries/' + <?= $ticket->ticketid; ?>, function(data) {
        var container = $('#existing-sla-entries');
        container.empty();

        if (data.length > 0) {
            container.append('<hr><h5>Existing SLA Entries</h5>');

            data.forEach(function(entry, index) {
                var entryHtml = `
                    <div class="sla-existing-entry" style="border: 1px solid #e0e0e0; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                        <div class="row">
                            <div class="col-md-12">
                                <strong>SLA Entry ${index + 1}</strong>
                                <p>${entry.sla_text}</p>
                                ${entry.attachments.length > 0 ? '<strong>Attachments:</strong><br>' : ''}
                                ${entry.attachments.map(function(att) {
                                    return `<a href="<?= site_url('download/file/sla_attachment'); ?>/${att.id}?ticket_id=<?= $ticket->ticketid; ?>" class="btn btn-sm btn-default" target="_blank">
                                        <i class="fa fa-download"></i> ${att.file_name}
                                    </a> `;
                                }).join('')}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteSLAEntry(${entry.id})">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.append(entryHtml);
            });
        }
    });
}

function deleteSLAEntry(entryId) {
    if (!confirm('Are you sure you want to delete this SLA entry?')) {
        return;
    }

    $.post(admin_url + 'tickets/delete_sla_entry', {
        entry_id: entryId,
        csrf_token_name: $('#csrf_token').val()
    }, function(response) {
        try {
            var resp = JSON.parse(response);
            if (resp.success) {
                alert_float('success', 'SLA entry deleted successfully');
                loadExistingSLAEntries();
            } else {
                alert_float('danger', resp.message || 'Failed to delete SLA entry');
            }
        } catch (e) {
            alert_float('danger', 'Failed to delete SLA entry');
        }
    });
}
</script>
