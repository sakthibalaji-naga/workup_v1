<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <!-- Sidebar -->
                            <div class="col-md-3">
                                <div class="mail-sidebar">
                                    <a href="<?= admin_url('internal_mail/compose'); ?>" class="btn btn-success btn-block tw-mb-3">
                                        <i class="fa fa-pencil"></i> <?= _l('internal_mail_compose'); ?>
                                    </a>
                                    
                                    <ul class="list-group">
                                        <li class="list-group-item <?= $mailbox_type == 'inbox' ? 'active' : ''; ?>">
                                            <a href="<?= admin_url('internal_mail/inbox'); ?>">
                                                <i class="fa fa-inbox"></i> <?= _l('internal_mail_inbox'); ?>
                                                <?php if ($unread_count > 0): ?>
                                                    <span class="badge pull-right"><?= $unread_count; ?></span>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                        <li class="list-group-item <?= $mailbox_type == 'sent' ? 'active' : ''; ?>">
                                            <a href="<?= admin_url('internal_mail/sent'); ?>">
                                                <i class="fa fa-paper-plane"></i> <?= _l('internal_mail_sent'); ?>
                                            </a>
                                        </li>
                                        <li class="list-group-item <?= $mailbox_type == 'drafts' ? 'active' : ''; ?>">
                                            <a href="<?= admin_url('internal_mail/drafts'); ?>">
                                                <i class="fa fa-file-text"></i> <?= _l('internal_mail_drafts'); ?>
                                            </a>
                                        </li>
                                        <li class="list-group-item <?= $mailbox_type == 'trash' ? 'active' : ''; ?>">
                                            <a href="<?= admin_url('internal_mail/trash'); ?>">
                                                <i class="fa fa-trash"></i> <?= _l('internal_mail_trash'); ?>
                                            </a>
                                        </li>
                                    </ul>
                                    
                                    <!-- Search -->
                                    <div class="tw-mt-4">
                                        <form action="<?= admin_url('internal_mail/search'); ?>" method="get">
                                            <div class="input-group">
                                                <input type="text" name="q" class="form-control" placeholder="<?= _l('internal_mail_search'); ?>" value="<?= isset($keyword) ? e($keyword) : ''; ?>">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="submit">
                                                        <i class="fa fa-search"></i>
                                                    </button>
                                                </span>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mail List -->
                            <div class="col-md-9">
                                <h4 class="tw-mb-3">
                                    <?php if ($mailbox_type == 'search'): ?>
                                        <?= _l('internal_mail_search_results'); ?> "<?= e($keyword); ?>"
                                    <?php else: ?>
                                        <?= ucfirst($mailbox_type); ?>
                                    <?php endif; ?>
                                </h4>
                                
                                <?php if (count($mails) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <tbody>
                                                <?php foreach ($mails as $mail): ?>
                                                    <tr class="<?= (isset($mail['is_read']) && $mail['is_read'] == 0) ? 'unread font-weight-bold' : ''; ?>">
                                                        <td width="5%">
                                                            <?php if (isset($mail['is_read']) && $mail['is_read'] == 0): ?>
                                                                <i class="fa fa-circle text-primary" style="font-size: 8px;"></i>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td width="25%">
                                                            <?php if ($mailbox_type == 'sent'): ?>
                                                                <?php 
                                                                    $recipients = $this->internal_mail_model->get_mail_recipients($mail['id']);
                                                                    $to_recipients = array_filter($recipients, function($r) { return $r['recipient_type'] == 'to'; });
                                                                    if (count($to_recipients) > 0) {
                                                                        $first_recipient = reset($to_recipients);
                                                                        echo e($first_recipient['firstname'] . ' ' . $first_recipient['lastname']);
                                                                        if (count($to_recipients) > 1) {
                                                                            echo ' <small>+' . (count($to_recipients) - 1) . '</small>';
                                                                        }
                                                                    }
                                                                ?>
                                                            <?php else: ?>
                                                                <?= e($mail['firstname'] . ' ' . $mail['lastname']); ?>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td width="50%">
                                                            <a href="<?= admin_url('internal_mail/view/' . $mail['id']); ?>" class="text-dark">
                                                                <?= e($mail['subject']); ?>
                                                                <?php if ($mail['has_attachments']): ?>
                                                                    <i class="fa fa-paperclip"></i>
                                                                <?php endif; ?>
                                                                <?php if ($mail['priority'] == 'high'): ?>
                                                                    <span class="label label-danger"><?= _l('high'); ?></span>
                                                                <?php endif; ?>
                                                            </a>
                                                        </td>
                                                        <td width="15%" class="text-right">
                                                            <small class="text-muted">
                                                                <?= time_ago($mail['date_sent']); ?>
                                                            </small>
                                                        </td>
                                                        <td width="5%" class="text-right">
                                                            <?php if ($mailbox_type == 'trash'): ?>
                                                                <a href="<?= admin_url('internal_mail/permanent_delete/' . $mail['id']); ?>" 
                                                                   class="btn btn-danger btn-xs" 
                                                                   onclick="return confirm('<?= _l('confirm_action_prompt'); ?>');">
                                                                    <i class="fa fa-trash"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="<?= admin_url('internal_mail/delete/' . $mail['id']); ?>" 
                                                                   class="btn btn-default btn-xs">
                                                                    <i class="fa fa-trash"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <?= _l('internal_mail_no_messages'); ?>
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

<style>
.mail-sidebar .list-group-item {
    border: none;
    padding: 8px 15px;
}
.mail-sidebar .list-group-item.active {
    background-color: #f4f4f4;
    border-left: 3px solid #28a745;
}
.mail-sidebar .list-group-item a {
    color: #333;
    text-decoration: none;
}
.mail-sidebar .list-group-item.active a {
    color: #28a745;
    font-weight: bold;
}
.unread {
    background-color: #f9f9f9;
    font-weight: bold;
}
</style>

<?php init_tail(); ?>
