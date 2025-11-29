<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mb-4">
                            <a href="<?= admin_url('internal_mail/inbox'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> <?= _l('back'); ?>
                            </a>
                            <?= _l('internal_mail_compose'); ?>
                        </h4>
                        
                        <style>
                        /* Gmail/Zoho Style Full Screen Compose */
                        :root {
                            --primary-blue: #0b57d0;
                            --primary-hover: #0842a0;
                            --bg-gray: #f2f6fc;
                            --text-primary: #1f1f1f;
                            --text-secondary: #444746;
                            --border-light: #e1e3e1;
                            --chip-bg: #e8f0fe;
                            --chip-text: #051e49;
                        }

                        .mail-compose-wrapper {
                            width: 100%;
                            min-height: calc(100vh - 140px); /* Fill available height at minimum */
                            margin: 0;
                            font-family: 'Google Sans', Roboto, RobotoDraft, Helvetica, Arial, sans-serif;
                            display: flex;
                            flex-direction: column;
                        }

                        .mail-compose-container {
                            background: #fff;
                            border-radius: 12px;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                            border: 1px solid var(--border-light);
                            /* overflow: hidden; Removed to allow page scroll */
                            display: flex;
                            flex-direction: column;
                            flex: 1; /* Fill the wrapper */
                        }
                        
                        /* Header */
                        .mail-compose-header {
                            padding: 12px 20px;
                            background: #f8fafd;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            border-bottom: 1px solid var(--border-light);
                        }
                        
                        .mail-compose-title {
                            font-size: 16px;
                            font-weight: 500;
                            color: var(--text-primary);
                            margin: 0;
                        }

                        .mail-compose-body {
                            padding: 0 20px;
                            flex: 1;
                            display: flex;
                            flex-direction: column;
                            /* overflow-y: auto; Removed to allow page scroll */
                        }

                        /* Fields */
                        .mail-field-row {
                            display: flex;
                            align-items: center;
                            padding: 8px 0;
                            border-bottom: 1px solid var(--border-light);
                            position: relative;
                            min-height: 48px;
                        }
                        
                        .mail-field-label {
                            color: var(--text-secondary);
                            font-size: 14px;
                            margin-right: 15px;
                            cursor: text;
                            padding-top: 2px;
                            width: 60px; /* Fixed width for alignment */
                        }
                        
                        .mail-field-input-wrapper {
                            flex: 1;
                            position: relative;
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                        }

                        /* Recipient Input */
                        .recipient-container {
                            display: flex;
                            flex-wrap: wrap;
                            align-items: center;
                            border: none;
                            padding: 0;
                            width: 100%;
                            background: transparent;
                        }
                        
                        .recipient-input {
                            border: none;
                            outline: none;
                            flex-grow: 1;
                            min-width: 100px;
                            padding: 4px 0;
                            font-size: 14px;
                            color: var(--text-primary);
                            background: transparent;
                        }
                        
                        /* Chips */
                        .recipient-chip {
                            display: inline-flex;
                            align-items: center;
                            background-color: #fff;
                            border: 1px solid #747775;
                            color: var(--text-primary);
                            border-radius: 18px;
                            padding: 0 8px 0 4px;
                            margin: 2px 4px 2px 0;
                            font-size: 14px;
                            height: 28px;
                            cursor: default;
                            text-decoration: none !important;
                        }
                        .recipient-chip span {
                            text-decoration: none !important;
                        }
                        .recipient-chip:hover {
                            background-color: #f0f4f8;
                        }
                        .recipient-chip img,
                        .recipient-chip .staff-profile-image-small,
                        .recipient-chip .img-circle {
                            width: 20px !important;
                            height: 20px !important;
                            border-radius: 50%;
                            margin-right: 6px !important;
                            margin-left: 0 !important;
                            float: none !important;
                        }
                        .recipient-chip .remove-chip {
                            margin-left: 6px;
                            cursor: pointer;
                            color: var(--text-secondary);
                            font-size: 16px;
                            line-height: 1;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            width: 16px;
                            height: 16px;
                            border-radius: 50%;
                        }
                        .recipient-chip .remove-chip:hover {
                            background-color: rgba(0,0,0,0.1);
                            color: var(--text-primary);
                        }

                        /* Dropdown */
                        .recipient-dropdown {
                            position: absolute;
                            top: 100%;
                            left: 0;
                            right: 0;
                            background: #fff;
                            border: none;
                            border-radius: 4px;
                            box-shadow: 0 2px 6px 2px rgba(60,64,67,0.15), 0 1px 2px 0 rgba(60,64,67,0.3);
                            z-index: 1000;
                            max-height: 300px;
                            overflow-y: auto;
                            display: none;
                            margin-top: 4px;
                            padding: 6px 0;
                        }
                        .recipient-dropdown-item {
                            padding: 8px 16px;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                        }
                        .recipient-dropdown-item:hover, .recipient-dropdown-item.active {
                            background-color: #f2f2f2;
                        }

                        /* Cc/Bcc Toggle */
                        .cc-bcc-toggle {
                            font-size: 12px;
                            color: var(--text-secondary);
                            text-decoration: none;
                            margin-left: 12px;
                            cursor: pointer;
                        }
                        .cc-bcc-toggle:hover {
                            text-decoration: underline;
                            color: var(--text-primary);
                        }
                        
                        /* Subject */
                        .subject-input {
                            width: 100%;
                            border: none;
                            outline: none;
                            font-size: 16px;
                            padding: 12px 0;
                            color: var(--text-primary);
                            background: transparent;
                            font-weight: 500;
                        }
                        .subject-input::placeholder {
                            color: var(--text-secondary);
                            font-weight: 400;
                        }

                        /* Editor Area */
                        .editor-wrapper {
                            flex: 1;
                            display: flex;
                            flex-direction: column;
                            margin-top: 0;
                            position: relative;
                            min-height: 300px; /* Ensure minimum height */
                        }
                        
                        /* Summernote Overrides for Full Screen */
                        .note-editor.note-frame {
                            border: none !important;
                            box-shadow: none !important;
                            flex: 1;
                            display: flex;
                            flex-direction: column;
                        }
                        .note-toolbar {
                            background: #f8fafd !important;
                            border-bottom: 1px solid var(--border-light) !important;
                            padding: 8px 20px !important;
                            z-index: 50; /* Ensure toolbar is above content */
                            position: relative;
                        }
                        .note-toolbar .note-btn {
                            background: transparent !important;
                            border: none !important;
                            box-shadow: none !important;
                            color: var(--text-secondary) !important;
                            padding: 5px 8px !important;
                            font-size: 14px;
                        }
                        .note-toolbar .note-btn:hover, .note-toolbar .note-btn.active {
                            background: rgba(0,0,0,0.05) !important;
                            color: var(--text-primary) !important;
                            border-radius: 4px !important;
                        }
                        .note-toolbar .dropdown-menu {
                            z-index: 1001 !important; /* Ensure dropdowns are on top */
                        }
                        .note-editable {
                            padding: 20px !important;
                            font-family: 'Arial', sans-serif;
                            font-size: 14px;
                            flex: 1;
                            outline: none;
                        }
                        .note-statusbar { display: none !important; }

                        /* Footer */
                        .mail-compose-footer {
                            padding: 16px 20px;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            background: #fff;
                            border-top: 1px solid var(--border-light);
                        }

                        .btn-send {
                            background-color: var(--primary-blue);
                            color: white;
                            border: none;
                            border-radius: 20px;
                            padding: 0 24px;
                            height: 40px;
                            font-weight: 500;
                            font-size: 14px;
                            cursor: pointer;
                            transition: background 0.2s;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .btn-send:hover {
                            background-color: var(--primary-hover);
                            box-shadow: 0 1px 2px rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
                        }

                        .btn-icon {
                            background: transparent;
                            border: none;
                            color: var(--text-secondary);
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            cursor: pointer;
                            margin-right: 8px;
                            transition: background 0.2s;
                        }
                        .btn-icon:hover {
                            background-color: rgba(60,64,67,0.08);
                            color: var(--text-primary);
                        }
                        
                        .custom-file-upload {
                            display: inline-block;
                        }
                        
                        /* Staff Option Styles */
                        .staff-option-item { display: flex; align-items: center; }
                        .staff-option-item img { width: 32px; height: 32px; border-radius: 50%; margin-right: 12px; }
                        .staff-option-details { display: flex; flex-direction: column; }
                        .staff-option-details span { font-weight: 500; color: var(--text-primary); font-size: 14px; }
                        .staff-subtext { font-size: 12px; color: var(--text-secondary); }
                        </style>
                        
                        <?= form_open_multipart(admin_url('internal_mail/compose'), ['id' => 'internal-mail-form']); ?>
                        
                        <div class="mail-compose-wrapper">
                            <div class="mail-compose-container">
                                <div class="mail-compose-header">
                                    <div style="display:flex; align-items:center;">
                                        <h3 class="mail-compose-title"><?= _l('internal_mail_compose'); ?></h3>
                                        <span id="draft-status" style="margin-left: 15px; font-size: 12px; color: var(--text-secondary); font-style: italic;"></span>
                                    </div>
                                    <div>
                                        <button type="button" class="btn-sm btn-default" id="toggle-bcc-btn" style="border:1px solid #e1e3e1; background:white;">Bcc</button>
                                    </div>
                                </div>
                                <input type="hidden" name="id" id="mail_id" value="<?= isset($mail) ? $mail->id : ''; ?>">
                                <input type="hidden" name="thread_id" id="thread_id" value="<?= isset($thread_id) ? $thread_id : ''; ?>">

                                <div class="mail-compose-body">
                                    <!-- To -->
                                    <div class="mail-field-row">
                                        <div class="mail-field-label"><?= _l('internal_mail_to'); ?></div>
                                        <div class="mail-field-input-wrapper">
                                            <div class="recipient-container" id="to-container" data-name="to[]">
                                                <input type="text" class="recipient-input" id="to-input" placeholder="" autocomplete="off">
                                                <div class="recipient-dropdown" id="to-dropdown"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- CC -->
                                    <div class="mail-field-row" id="cc-wrapper">
                                        <div class="mail-field-label"><?= _l('internal_mail_cc'); ?></div>
                                        <div class="mail-field-input-wrapper">
                                            <div class="recipient-container" id="cc-container" data-name="cc[]">
                                                <input type="text" class="recipient-input" id="cc-input" placeholder="" autocomplete="off">
                                                <div class="recipient-dropdown" id="cc-dropdown"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- BCC -->
                                    <div class="mail-field-row" id="bcc-wrapper" style="display:none;">
                                        <div class="mail-field-label"><?= _l('internal_mail_bcc'); ?></div>
                                        <div class="mail-field-input-wrapper">
                                            <div class="recipient-container" id="bcc-container" data-name="bcc[]">
                                                <input type="text" class="recipient-input" id="bcc-input" placeholder="" autocomplete="off">
                                                <div class="recipient-dropdown" id="bcc-dropdown"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Subject -->
                                    <div class="mail-field-row">
                                        <div class="mail-field-input-wrapper">
                                            <input type="text" name="subject" id="subject" class="subject-input" placeholder="<?= _l('internal_mail_subject'); ?>" required value="<?= isset($forward_subject) ? e($forward_subject) : (isset($reply_subject) ? e($reply_subject) : (isset($mail) ? e($mail->subject) : '')); ?>">
                                        </div>
                                    </div>
                                    
                                    <!-- Message -->
                                    <div class="editor-wrapper">
                                        <textarea name="message" id="message" class="form-control" required><?= isset($forward_message) ? $forward_message : (isset($reply_message) ? $reply_message : (isset($mail) ? $mail->message : '')); ?></textarea>
                                    </div>
                                </div>

                                <div class="mail-compose-footer">
                                    <div style="display: flex; align-items: center;">
                                        <button type="submit" class="btn-send">
                                            <?= _l('internal_mail_send'); ?>
                                        </button>
                                        
                                        <div class="custom-file-upload mleft15">
                                            <label for="attachments" class="btn-icon" title="<?= _l('internal_mail_attachments'); ?>" style="margin:0;">
                                                <i class="fa fa-paperclip"></i>
                                            </label>
                                            <input type="file" name="attachments[]" id="attachments" multiple style="display:none;" onchange="$('#file-count').text(this.files.length > 0 ? this.files.length : '')">
                                            <span id="file-count" class="text-secondary" style="font-size:12px; margin-left: 5px;"></span>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <button type="submit" name="save_draft" value="1" class="btn-icon" title="<?= _l('internal_mail_save_draft'); ?>">
                                            <i class="fa fa-save"></i>
                                        </button>
                                        <a href="<?= admin_url('internal_mail/inbox'); ?>" class="btn-icon" title="<?= _l('delete'); ?>">
                                            <i class="fa fa-trash-o"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

<script>
$(document).ready(function() {
    // Auto Draft Logic
    var draftTimer;
    
    function saveDraft() {
        $('#draft-status').text('Saving...');
        
        // Prepare form data
        var formData = new FormData($('#internal-mail-form')[0]);
        formData.append('save_draft', '1');
        
        $.ajax({
            url: admin_url + 'internal_mail/compose',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    var res = JSON.parse(response);
                    if (res.success) {
                        $('#mail_id').val(res.mail_id);
                        var time = new Date().toLocaleString([], {month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit', hour12: true});
                        $('#draft-status').text('Saved at ' + time);
                    }
                } catch(e) {
                    console.error('Draft save failed', e);
                }
            }
        });
    }

    function triggerAutoSave() {
        clearTimeout(draftTimer);
        draftTimer = setTimeout(saveDraft, 2000); // Save after 2 seconds of inactivity
    }

    // Initialize Summernote
    $('#message').summernote({
        minHeight: 300,
        focus: true,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onChange: function(contents, $editable) {
                triggerAutoSave();
            }
        }
    });

    // Hook into Subject
    $('#subject').on('input', function() {
        triggerAutoSave();
    });
    
    // Recipient Input Logic
    function initRecipientInput(containerId, inputId, dropdownId, initialRecipients) {
        var $container = $('#' + containerId);
        var $input = $('#' + inputId);
        var $dropdown = $('#' + dropdownId);
        var inputName = $container.data('name');
        var selectedRecipients = initialRecipients || [];
        var debounceTimer;

        // Initial render if we have recipients
        if (selectedRecipients.length > 0) {
            updateHiddenInput();
            renderChips();
        }

        // Focus input when container clicked
        $container.on('click', function(e) {
            if (e.target === this || e.target === $container.find('.recipient-chips')[0]) {
                $input.focus();
            }
        });

        // Search input handler
        $input.on('input', function() {
            var query = $(this).val();
            clearTimeout(debounceTimer);
            
            if (query.length < 1) {
                $dropdown.hide();
                return;
            }

            debounceTimer = setTimeout(function() {
                $.post(admin_url + 'internal_mail/search_staff', { q: query }, function(data) {
                    var results = JSON.parse(data);
                    renderDropdown(results);
                });
            }, 300);
        });

        function renderDropdown(results) {
            $dropdown.empty();
            if (results.length === 0) {
                $dropdown.hide();
                return;
            }

            results.forEach(function(item) {
                // Check if already selected
                if (selectedRecipients.find(r => r.staffid == item.staffid)) return;

                var $item = $('<div class="recipient-dropdown-item"></div>');
                $item.html(item.content);
                
                $item.on('click', function() {
                    addRecipient(item);
                    $input.val('');
                    $dropdown.hide();
                    $input.focus();
                });
                
                $dropdown.append($item);
            });

            if ($dropdown.children().length > 0) {
                $dropdown.show();
            } else {
                $dropdown.hide();
            }
        }

        function addRecipient(item) {
            selectedRecipients.push(item);
            updateHiddenInput();
            renderChips();
            triggerAutoSave();
        }

        function removeRecipient(staffid) {
            selectedRecipients = selectedRecipients.filter(r => r.staffid != staffid);
            updateHiddenInput();
            renderChips();
            triggerAutoSave();
        }

        function updateHiddenInput() {
            // Remove old hidden inputs
            $container.find('input[type="hidden"]').remove();
            
            // Add new ones
            selectedRecipients.forEach(function(r) {
                var $hidden = $('<input type="hidden">');
                $hidden.attr('name', inputName);
                $hidden.val(r.staffid);
                $container.append($hidden);
            });
        }

        function renderChips() {
            // Remove existing chips (but keep input and dropdown)
            $container.find('.recipient-chip').remove();
            
            selectedRecipients.forEach(function(item) {
                // Parse the content to get the image
                var $content = $(item.content);
                var imgHtml = $content.find('img').prop('outerHTML') || '<div class="recipient-avatar-placeholder">' + item.firstname.charAt(0) + '</div>';
                
                var $chip = $('<div class="recipient-chip"></div>');
                $chip.append(imgHtml);
                $chip.append('<span>' + item.firstname + ' ' + item.lastname + '</span>');
                var $remove = $('<span class="remove-chip">&times;</span>');
                $remove.on('click', function(e) {
                    e.stopPropagation(); // Prevent container click
                    removeRecipient(item.staffid);
                });
                $chip.append($remove);
                
                $input.before($chip);
            });
        }

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$container.is(e.target) && $container.has(e.target).length === 0) {
                $dropdown.hide();
            }
        });
    }

    <?php
    $recipients_to = [];
    $recipients_cc = [];
    $recipients_bcc = [];

    // Handle reply recipients
    if (isset($reply_recipients)) {
        // Load TO recipients for reply
        foreach ($reply_recipients['to'] as $staff_id) {
            $staff = $this->staff_model->get($staff_id);
            if ($staff) {
                $profile_image = staff_profile_image($staff_id, ['staff-profile-image-small', 'img-circle', 'pull-left'], 'small', ['style' => 'width:30px;margin-right:10px;']);
                $content = "<div class='staff-option-item'>" . $profile_image . "<div class='staff-option-details'><span>" . $staff->firstname . ' ' . $staff->lastname . "</span></div></div>";
                
                $recipients_to[] = [
                    'staffid' => $staff_id,
                    'firstname' => $staff->firstname,
                    'lastname' => $staff->lastname,
                    'content' => $content
                ];
            }
        }
        
        // Load CC recipients for reply
        foreach ($reply_recipients['cc'] as $staff_id) {
            $staff = $this->staff_model->get($staff_id);
            if ($staff) {
                $profile_image = staff_profile_image($staff_id, ['staff-profile-image-small', 'img-circle', 'pull-left'], 'small', ['style' => 'width:30px;margin-right:10px;']);
                $content = "<div class='staff-option-item'>" . $profile_image . "<div class='staff-option-details'><span>" . $staff->firstname . ' ' . $staff->lastname . "</span></div></div>";
                
                $recipients_cc[] = [
                    'staffid' => $staff_id,
                    'firstname' => $staff->firstname,
                    'lastname' => $staff->lastname,
                    'content' => $content
                ];
            }
        }
    }
    // Handle draft recipients
    elseif (isset($mail) && isset($mail->recipients)) {
        foreach ($mail->recipients as $recipient) {
            $profile_image = staff_profile_image($recipient['staff_id'], ['staff-profile-image-small', 'img-circle', 'pull-left'], 'small', ['style' => 'width:30px;margin-right:10px;']);
            $content = "<div class='staff-option-item'>" . $profile_image . "<div class='staff-option-details'><span>" . $recipient['firstname'] . ' ' . $recipient['lastname'] . "</span></div></div>";
            
            $item = [
                'staffid' => $recipient['staff_id'],
                'firstname' => $recipient['firstname'],
                'lastname' => $recipient['lastname'],
                'content' => $content
            ];
            
            if ($recipient['recipient_type'] == 'to') {
                $recipients_to[] = $item;
            } elseif ($recipient['recipient_type'] == 'cc') {
                $recipients_cc[] = $item;
            } elseif ($recipient['recipient_type'] == 'bcc') {
                $recipients_bcc[] = $item;
            }
        }
    }
    ?>


    var recipientsTo = <?= json_encode($recipients_to); ?>;
    var recipientsCc = <?= json_encode($recipients_cc); ?>;
    var recipientsBcc = <?= json_encode($recipients_bcc); ?>;

    initRecipientInput('to-container', 'to-input', 'to-dropdown', recipientsTo);
    initRecipientInput('cc-container', 'cc-input', 'cc-dropdown', recipientsCc);
    initRecipientInput('bcc-container', 'bcc-input', 'bcc-dropdown', recipientsBcc);

    // Individual Toggle Handlers
    $('#toggle-bcc-btn').on('click', function() {
        $('#bcc-wrapper').toggle();
    });

    // Form validation
    $('#internal-mail-form').submit(function(e) {
        var toCount = $('#to-container').find('input[type="hidden"]').length;
        if (toCount === 0) {
            alert('<?= _l('internal_mail_to_required'); ?>');
            e.preventDefault();
            return false;
        }
    });
});
</script>
