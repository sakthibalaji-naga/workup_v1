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
                        
                        <?= form_open_multipart(admin_url('internal_mail/compose'), ['id' => 'internal-mail-form']); ?>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <!-- To -->
                                <div class="form-group">
                                    <label for="to"><?= _l('internal_mail_to'); ?> <span class="text-danger">*</span></label>
                                    <select name="to[]" id="to" class="selectpicker" data-live-search="true" multiple required data-width="100%">
                                        <?php foreach ($staff_members as $staff): ?>
                                            <?php if ($staff['staffid'] != get_staff_user_id()): ?>
                                                <option value="<?= $staff['staffid']; ?>">
                                                    <?= e($staff['firstname'] . ' ' . $staff['lastname']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- CC -->
                                <div class="form-group">
                                    <label for="cc"><?= _l('internal_mail_cc'); ?></label>
                                    <select name="cc[]" id="cc" class="selectpicker" data-live-search="true" multiple data-width="100%">
                                        <?php foreach ($staff_members as $staff): ?>
                                            <?php if ($staff['staffid'] != get_staff_user_id()): ?>
                                                <option value="<?= $staff['staffid']; ?>">
                                                    <?= e($staff['firstname'] . ' ' . $staff['lastname']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- BCC -->
                                <div class="form-group">
                                    <label for="bcc"><?= _l('internal_mail_bcc'); ?></label>
                                    <select name="bcc[]" id="bcc" class="selectpicker" data-live-search="true" multiple data-width="100%">
                                        <?php foreach ($staff_members as $staff): ?>
                                            <?php if ($staff['staffid'] != get_staff_user_id()): ?>
                                                <option value="<?= $staff['staffid']; ?>">
                                                    <?= e($staff['firstname'] . ' ' . $staff['lastname']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Subject -->
                                <div class="form-group">
                                    <label for="subject"><?= _l('internal_mail_subject'); ?> <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" id="subject" class="form-control" required value="<?= isset($mail) ? e($mail->subject) : ''; ?>">
                                </div>
                                
                                <!-- Priority -->
                                <div class="form-group">
                                    <label for="priority"><?= _l('internal_mail_priority'); ?></label>
                                    <select name="priority" id="priority" class="selectpicker" data-width="100%">
                                        <option value="low"><?= _l('low'); ?></option>
                                        <option value="normal" selected><?= _l('normal'); ?></option>
                                        <option value="high"><?= _l('high'); ?></option>
                                    </select>
                                </div>
                                
                                <!-- Message -->
                                <div class="form-group">
                                    <label for="message"><?= _l('internal_mail_message'); ?> <span class="text-danger">*</span></label>
                                    <textarea name="message" id="message" class="form-control" rows="10" required><?= isset($mail) ? $mail->message : ''; ?></textarea>
                                </div>
                                
                                <!-- Attachments -->
                                <div class="form-group">
                                    <label for="attachments"><?= _l('internal_mail_attachments'); ?></label>
                                    <input type="file" name="attachments[]" id="attachments" multiple class="form-control">
                                    <small class="text-muted"><?= _l('internal_mail_max_file_size'); ?></small>
                                </div>
                                
                                <!-- Buttons -->
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-paper-plane"></i> <?= _l('internal_mail_send'); ?>
                                    </button>
                                    <button type="submit" name="save_draft" value="1" class="btn btn-default">
                                        <i class="fa fa-save"></i> <?= _l('internal_mail_save_draft'); ?>
                                    </button>
                                    <a href="<?= admin_url('internal_mail/inbox'); ?>" class="btn btn-default">
                                        <i class="fa fa-times"></i> <?= _l('cancel'); ?>
                                    </a>
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

<script>
$(document).ready(function() {
    // Initialize TinyMCE or similar editor
    <?php if (get_option('tinymce_textarea') == 1): ?>
    tinymce.init({
        selector: '#message',
        height: 300,
        menubar: false,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic backcolor | \
                  alignleft aligncenter alignright alignjustify | \
                  bullist numlist outdent indent | removeformat | help'
    });
    <?php endif; ?>
    
    // Form validation
    $('#internal-mail-form').submit(function(e) {
        var to = $('#to').val();
        if (!to || to.length === 0) {
            alert('<?= _l('internal_mail_to_required'); ?>');
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php init_tail(); ?>
