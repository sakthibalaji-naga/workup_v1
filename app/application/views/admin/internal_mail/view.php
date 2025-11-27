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
                                <!-- Header Actions -->
                                <div class="tw-mb-4">
                                    <a href="<?= admin_url('internal_mail/inbox'); ?>" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i> <?= _l('back_to_inbox'); ?>
                                    </a>
                                    <a href="<?= admin_url('internal_mail/delete/' . $mail->id); ?>" class="btn btn-danger pull-right" onclick="return confirm('<?= _l('confirm_action_prompt'); ?>');">
                                        <i class="fa fa-trash"></i> <?= _l('delete'); ?>
                                    </a>
                                </div>
                                
                                <!-- Mail Content -->
                                <div class="mail-content">
                                    <!-- Subject -->
                                    <h3 class="tw-mb-3">
                                        <?= e($mail->subject); ?>
                                        <?php if ($mail->priority == 'high'): ?>
                                            <span class="label label-danger"><?= _l('high_priority'); ?></span>
                                        <?php endif; ?>
                                    </h3>
                                    
                                    <!-- From -->
                                    <div class="mail-meta tw-mb-3">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <strong><?= _l('from'); ?>:</strong>
                                                <?php
                                                    $sender = $this->staff_model->get($mail->from_staff_id);
                                                    if ($sender) {
                                                        echo e($sender->firstname . ' ' . $sender->lastname);
                                                        echo ' <small class="text-muted">&lt;' . e($sender->email) . '&gt;</small>';
                                                    }
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <!-- To -->
                                        <div class="row tw-mt-2">
                                            <div class="col-md-12">
                                                <strong><?= _l('internal_mail_to'); ?>:</strong>
                                                <?php
                                                    $to_recipients = array_filter($mail->recipients, function($r) { return $r['recipient_type'] == 'to'; });
                                                    $to_names = array_map(function($r) { 
                                                        return e($r['firstname'] . ' ' . $r['lastname']); 
                                                    }, $to_recipients);
                                                    echo implode(', ', $to_names);
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <!-- CC -->
                                        <?php
                                            $cc_recipients = array_filter($mail->recipients, function($r) { return $r['recipient_type'] == 'cc'; });
                                            if (count($cc_recipients) > 0):
                                        ?>
                                        <div class="row tw-mt-2">
                                            <div class="col-md-12">
                                                <strong><?= _l('internal_mail_cc'); ?>:</strong>
                                                <?php
                                                    $cc_names = array_map(function($r) { 
                                                        return e($r['firstname'] . ' ' . $r['lastname']); 
                                                    }, $cc_recipients);
                                                    echo implode(', ', $cc_names);
                                                ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Date -->
                                        <div class="row tw-mt-2">
                                            <div class="col-md-12">
                                                <strong><?= _l('date'); ?>:</strong>
                                                <span class="text-muted">
                                                    <?= _dt($mail->date_sent); ?>
                                                    (<?= time_ago($mail->date_sent); ?>)
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <!-- Message Body -->
                                    <div class="mail-body tw-mb-4">
                                        <?= $mail->message; ?>
                                    </div>
                                    
                                    <!-- Attachments -->
                                    <?php if ($mail->has_attachments && count($mail->attachments) > 0): ?>
                                        <div class="mail-attachments">
                                            <hr>
                                            <h4><?= _l('internal_mail_attachments'); ?>:</h4>
                                            <div class="row">
                                                <?php foreach ($mail->attachments as $attachment): ?>
                                                    <div class="col-md-3 col-sm-6 tw-mb-3">
                                                        <div class="panel panel-default">
                                                            <div class="panel-body text-center">
                                                                <i class="fa fa-file fa-3x text-muted"></i>
                                                                <p class="tw-mt-2">
                                                                    <strong><?= e($attachment['original_file_name']); ?></strong>
                                                                </p>
                                                                <small class="text-muted">
                                                                    <?= bytesToSize($attachment['file_size']); ?>
                                                                </small>
                                                                <div class="tw-mt-2">
                                                                    <a href="<?= admin_url('internal_mail/download_attachment/' . $attachment['id']); ?>" class="btn btn-primary btn-sm">
                                                                        <i class="fa fa-download"></i> <?= _l('download'); ?>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mail-meta {
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 4px;
}
.mail-body {
    padding: 20px 0;
    line-height: 1.6;
}
</style>

<?php init_tail(); ?>
