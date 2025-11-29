<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

        <style>
        /* Gmail-style layout with sidebar */
        .gm-container {
          display: grid;
          grid-template-columns: 260px minmax(0, 1fr);
          gap: 0;
          background: #f6f7f7;
          min-height: calc(100vh - 150px);
        }
        
        /* Left sidebar */
        .gm-sidebar {
          padding: 16px 12px;
          border-right: 1px solid #e0e0e0;
          background: #f1f3f4;
        }

        .gm-compose-btn {
          display: inline-flex;
          align-items: center;
          border-radius: 24px;
          border: none;
          padding: 10px 24px 10px 16px;
          background: #fff;
          color: #3c4043;
          box-shadow: 0 1px 3px rgba(60, 64, 67, 0.3);
          cursor: pointer;
          margin-bottom: 16px;
          font-size: 14px;
          font-weight: 500;
          transition: box-shadow 0.2s, background 0.2s;
          text-decoration: none !important;
        }
        
        .gm-compose-btn:hover {
            box-shadow: 0 4px 6px rgba(60, 64, 67, 0.3);
            background: #f8f9fa;
            color: #3c4043;
        }

        .gm-compose-btn .material-icons {
          margin-right: 12px;
          font-size: 18px;
          color: #ea4335;
        }

        .gm-folder-list {
          display: flex;
          flex-direction: column;
          gap: 2px;
        }

        .gm-folder-item {
          display: flex;
          align-items: center;
          border-radius: 0 16px 16px 0;
          padding: 8px 12px 8px 16px;
          font-size: 14px;
          text-decoration: none !important;
          color: #3c4043;
          font-weight: 500;
        }

        .gm-folder-item:hover {
            background: #e8eaed;
            color: #3c4043;
        }

        .gm-folder-item .material-icons {
          font-size: 18px;
          margin-right: 16px;
          width: 20px;
          text-align: center;
          color: #5f6368;
        }

        .gm-folder-item .badge {
          margin-left: auto;
          font-size: 12px;
          background: transparent;
          color: #1a73e8;
          font-weight: bold;
        }

        .gm-folder-item.active {
          background: #e8f0fe;
          color: #1967d2;
        }
        
        .gm-folder-item.active .material-icons {
            color: #1967d2;
        }
        
        /* Whole message page */
        .gm-page {
          background: #f6f7f7;
          padding: 8px 0 40px;
          font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        /* 1) TOP TOOLBAR */

        .gm-toolbar {
          display: flex;
          justify-content: space-between;
          align-items: center;
          background: #fff;
          border-bottom: 1px solid #dadce0;
          padding: 4px 16px;
        }

        .gm-toolbar-left,
        .gm-toolbar-right {
          display: flex;
          align-items: center;
          gap: 4px;
        }

        .gm-toolbar-range {
          font-size: 12px;
          color: #5f6368;
        }

        .gm-icon-btn {
          border: none;
          background: transparent;
          padding: 6px;
          border-radius: 50%;
          cursor: pointer;
          display: inline-flex;
          align-items: center;
          justify-content: center;
        }
        .gm-icon-btn .material-icons {
          font-size: 20px;
          vertical-align: middle;
          color: #5f6368;
        }
        .gm-icon-btn:hover {
          background: rgba(60, 64, 67, 0.1);
        }
        .gm-icon-btn:hover .material-icons {
          color: #202124;
        }

        /* 2) THREAD CONTAINER */

        .gm-thread-wrapper {
          display: flex;
          justify-content: center;
          padding-top: 12px;
        }
        .gm-thread {
          width: 100%;
          max-width: 900px;
          background: #fff;
          border-radius: 8px;
          border: 1px solid #dadce0;
          box-shadow: 0 1px 2px rgba(60, 64, 67, 0.3);
        }

        /* SUBJECT ROW */

        .gm-subject-row {
          display: flex;
          align-items: center;
          padding: 16px 24px 8px;
        }
        .gm-subject-text {
          flex: 1;
          font-size: 22px;
          font-weight: 400;
          color: #202124;
        }
        .gm-subject-right {
          display: flex;
          align-items: center;
          gap: 8px;
        }
        .gm-label-chip {
          border-radius: 12px;
          background: #e8eaed;
          padding: 2px 8px;
          font-size: 11px;
          color: #5f6368;
        }

        /* HEADER ROW (SENDER, TO ME, TIME, ACTIONS) */
        
        .gm-message-item {
          border-top: 1px solid #dadce0;
        }
        
        .gm-message-item:first-child {
          border-top: none;
        }

        .gm-header {
          display: flex;
          align-items: flex-start;
          padding: 12px 24px 12px;
        }

        .gm-avatar {
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background: #e8f0fe;
          color: #1a73e8;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: 500;
          margin-right: 12px;
          flex-shrink: 0;
          overflow: hidden;
        }
        
        .gm-avatar img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .gm-header-main {
          flex: 1;
        }

        .gm-header-line1 {
          display: flex;
          flex-wrap: wrap;
          align-items: baseline;
          gap: 6px;
        }
        .gm-from-name {
          font-weight: 700;
          color: #202124;
          font-size: 14px;
        }
        .gm-from-email {
          font-size: 12px;
          color: #5f6368;
        }

        .gm-header-line2 {
          margin-top: 2px;
          font-size: 12px;
          color: #5f6368;
        }

        .gm-to-dropdown {
          border: none;
          background: transparent;
          padding: 0 2px;
          font-size: 12px;
          color: #5f6368;
          display: inline-flex;
          align-items: center;
          cursor: pointer;
        }
        .gm-to-dropdown .material-icons {
          font-size: 16px;
        }
        .gm-to-dropdown:hover {
          background: rgba(60, 64, 67, 0.08);
          border-radius: 4px;
        }
        
        /* Email details dropdown */
        .gm-email-details {
          display: none;
          margin-top: 12px;
          padding: 12px 16px;
          background: #f8f9fa;
          border-radius: 8px;
          font-size: 12px;
          color: #5f6368;
        }
        
        .gm-email-details.show {
          display: block;
        }
        
        .gm-email-detail-row {
          display: grid;
          grid-template-columns: 80px 1fr;
          gap: 8px;
          margin-bottom: 6px;
        }
        
        .gm-email-detail-row:last-child {
          margin-bottom: 0;
        }
        
        .gm-email-detail-label {
          font-weight: 600;
          color: #202124;
        }
        
        .gm-email-detail-value {
          color: #5f6368;
          word-break: break-word;
        }

        .gm-header-right {
          display: flex;
          flex-direction: column;
          align-items: flex-end;
          margin-left: 12px;
          font-size: 12px;
          color: #5f6368;
        }
        .gm-mail-time {
          white-space: nowrap;
          margin-bottom: 4px;
        }
        .gm-header-actions {
          margin-top: 4px;
          display: flex;
          align-items: center;
          gap: 2px;
        }

        /* BODY */

        .gm-body {
          padding: 8px 24px 24px 76px;
          font-size: 14px;
          line-height: 1.6;
          color: #202124;
        }
        
        /* ATTACHMENTS */
        .gm-attachments {
          padding: 0 24px 16px 76px;
          display: flex;
          flex-wrap: wrap;
          gap: 8px;
        }

        .gm-attachment-pill {
          display: inline-flex;
          align-items: center;
          padding: 8px 12px;
          border-radius: 4px;
          border: 1px solid #dadce0;
          font-size: 12px;
          color: #202124;
          text-decoration: none;
          background: #fff;
          transition: background 0.2s;
        }
        
        .gm-attachment-pill:hover {
          background: #f1f3f4;
        }

        .gm-attachment-pill .material-icons {
          font-size: 16px;
          margin-right: 6px;
          color: #5f6368;
        }

        .gm-attachment-size {
          margin-left: 6px;
          color: #5f6368;
        }

        /* 3) REPLY AREA */

        .gm-reply-wrapper {
          max-width: 900px;
          margin: 16px auto 0;
          display: flex;
          padding-inline: 24px;
        }
        .gm-reply-avatar {
          width: 32px;
          height: 32px;
          border-radius: 50%;
          background: #e8f0fe;
          color: #1a73e8;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 14px;
          font-weight: 500;
          margin-right: 12px;
          overflow: hidden;
        }
        
        .gm-reply-avatar img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }
        
        .gm-reply-box {
          flex: 1;
        }
        .gm-reply-box textarea {
          width: 100%;
          min-height: 64px;
          border-radius: 18px;
          border: 1px solid #dadce0;
          padding: 10px 14px;
          font-size: 14px;
          font-family: inherit;
          resize: vertical;
        }
        .gm-reply-box textarea:focus {
          outline: none;
          border-color: #1a73e8;
          box-shadow: 0 0 0 1px #1a73e8;
        }
        .gm-reply-actions {
          margin-top: 8px;
          display: flex;
          align-items: center;
          gap: 6px;
        }
        .gm-send-btn {
          border: none;
          border-radius: 999px;
          padding: 8px 24px;
          font-size: 14px;
          font-weight: 500;
          background: #1a73e8;
          color: #fff;
          cursor: pointer;
          transition: background 0.2s;
        }
        .gm-send-btn:hover {
          background: #185abc;
        }

        /* RESPONSIVE */

        @media (max-width: 900px) {
          .gm-container {
            grid-template-columns: 1fr;
          }
          .gm-sidebar {
            display: none;
          }
          .gm-thread {
            border-radius: 0;
          }
          .gm-thread-wrapper {
            padding-top: 0;
          }
        }
        
        /* PRINT STYLES - Only print mail content */
        @media print {
          /* Hide everything except the message thread */
          .gm-sidebar,
          .gm-toolbar,
          .gm-reply-wrapper,
          .gm-subject-right,
          .gm-header-actions,
          #header,
          #aside_menu,
          .tw-hidden,
          .sidebar {
            display: none !important;
          }
          
          /* Reset page styles for print */
          body {
            background: white !important;
            margin: 0;
            padding: 0;
          }
          
          #wrapper,
          .content,
          .gm-container,
          .gm-page {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
            grid-template-columns: 1fr !important;
          }
          
          /* Clean thread presentation */
          .gm-thread-wrapper {
            padding: 0 !important;
          }
          
          .gm-thread {
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            max-width: 100% !important;
          }
          
          /* Subject styling for print */
          .gm-subject-row {
            padding: 20px 0 10px !important;
            border-bottom: 2px solid #000 !important;
          }
          
          .gm-subject-text {
            font-size: 20px !important;
            font-weight: bold !important;
            color: #000 !important;
          }
          
          /* Message header for print */
          .gm-message-item {
            border-top: 1px solid #ddd !important;
            page-break-inside: avoid;
          }
          
          .gm-header {
            padding: 15px 0 !important;
          }
          
          .gm-from-name,
          .gm-from-email,
          .gm-mail-time {
            color: #000 !important;
          }
          
          /* Message body for print */
          .gm-body {
            padding: 10px 0 20px !important;
            color: #000 !important;
            font-size: 12pt !important;
            line-height: 1.5 !important;
          }
          
          /* Attachments for print */
          .gm-attachments {
            padding: 10px 0 !important;
          }
          
          .gm-attachment-pill {
            border: 1px solid #000 !important;
            background: white !important;
            color: #000 !important;
          }
        }
        </style>

        <div class="gm-container">
          
          <!-- LEFT SIDEBAR -->
          <aside class="gm-sidebar">
            <a href="<?= admin_url('internal_mail/compose'); ?>" class="gm-compose-btn">
              <span class="material-icons">edit</span>
              <?= _l('internal_mail_compose'); ?>
            </a>

            <nav class="gm-folder-list">
              <a href="<?= admin_url('internal_mail/inbox'); ?>" class="gm-folder-item active">
                <span class="material-icons">inbox</span>
                Inbox
                <?php if ($unread_count > 0): ?>
                    <span class="badge"><?= $unread_count; ?></span>
                <?php endif; ?>
              </a>

              <a href="<?= admin_url('internal_mail/sent'); ?>" class="gm-folder-item">
                <span class="material-icons">send</span>
                Sent
              </a>

              <a href="<?= admin_url('internal_mail/drafts'); ?>" class="gm-folder-item">
                <span class="material-icons">drafts</span>
                Drafts
              </a>

              <a href="<?= admin_url('internal_mail/trash'); ?>" class="gm-folder-item">
                <span class="material-icons">delete</span>
                Trash
              </a>
            </nav>
            
            <!-- Search -->
            <div class="tw-mt-4 tw-px-3">
                <form action="<?= admin_url('internal_mail/search'); ?>" method="get">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control" placeholder="Search mails" style="border-radius: 4px 0 0 4px; font-size: 13px;">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit" style="border-radius: 0 4px 4px 0;">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                </form>
            </div>
          </aside>

          <!-- RIGHT: MESSAGE VIEW -->
          <div class="gm-page">

            <!-- TOP TOOLBAR -->
            <div class="gm-toolbar">
              <div class="gm-toolbar-left">
                <button class="gm-icon-btn" onclick="goBackToInbox()" title="Back to inbox">
                  <span class="material-icons">arrow_back</span>
                </button>
                <button class="gm-icon-btn" onclick="archiveMessage()" title="Archive">
                  <span class="material-icons">archive</span>
                </button>
                <button class="gm-icon-btn" onclick="deleteMessage()" title="Delete">
                  <span class="material-icons">delete</span>
                </button>
                <button class="gm-icon-btn" onclick="markUnread()" title="Mark as unread">
                  <span class="material-icons">mark_email_unread</span>
                </button>
                <button class="gm-icon-btn" title="Move to folder">
                  <span class="material-icons">drive_file_move</span>
                </button>
                <button class="gm-icon-btn" title="Label">
                  <span class="material-icons">label</span>
                </button>
                <button class="gm-icon-btn" title="More">
                  <span class="material-icons">more_vert</span>
                </button>
              </div>

              <div class="gm-toolbar-right">
                <span class="gm-toolbar-range">1 of 1</span>
                <button class="gm-icon-btn" onclick="prevMail()" <?= empty($prev_token) ? 'disabled' : ''; ?>>
                  <span class="material-icons">chevron_left</span>
                </button>
                <button class="gm-icon-btn" onclick="nextMail()" <?= empty($next_token) ? 'disabled' : ''; ?>>
                  <span class="material-icons">chevron_right</span>
                </button>
              </div>
            </div>

            <!-- THREAD (SUBJECT + MESSAGES) -->
            <div class="gm-thread-wrapper">
              <div class="gm-thread">

                <!-- SUBJECT ROW – BIG TEXT + LABEL + ICONS -->
                <div class="gm-subject-row">
                  <div class="gm-subject-text">
                    <?= e($subject); ?>
                  </div>
                  <div class="gm-subject-right">
                    <span class="gm-label-chip">Inbox</span>
                    <button class="gm-icon-btn" onclick="toggleStar()" title="Star">
                      <span class="material-icons">star_border</span>
                    </button>
                    <button class="gm-icon-btn" onclick="printMessage()" title="Print">
                      <span class="material-icons">print</span>
                    </button>
                    <button class="gm-icon-btn" onclick="openInNew()" title="Open in new window">
                      <span class="material-icons">open_in_full</span>
                    </button>
                  </div>
                </div>

                <!-- MESSAGES LOOP -->
                <?php foreach ($messages as $index => $msg): ?>
                <div class="gm-message-item" id="msg-<?= $msg['id']; ?>">
                    <!-- HEADER ROW – AVATAR + FROM + "to me ▼" + TIME + ACTIONS -->
                    <div class="gm-header">
                      <div class="gm-avatar">
                        <?php 
                            $sender_initials = strtoupper(substr($msg['firstname'], 0, 1));
                            $profile_img = staff_profile_image($msg['from_staff_id'], ['staff-profile-image-small'], 'small');
                            if (strpos($profile_img, '<img') !== false) {
                                echo $profile_img;
                            } else {
                                echo $sender_initials;
                            }
                        ?>
                      </div>

                      <div class="gm-header-main">
                        <div class="gm-header-line1">
                          <span class="gm-from-name"><?= e($msg['firstname'] . ' ' . $msg['lastname']); ?></span>
                        </div>
                        <div class="gm-header-line2">
                          to
                          <button class="gm-to-dropdown" title="Show details" onclick="toggleEmailDetails(<?= $msg['id']; ?>); event.stopPropagation();">
                            me
                            <span class="material-icons" id="arrow-<?= $msg['id']; ?>">arrow_drop_down</span>
                          </button>
                        </div>
                        
                        <!-- Email Details Dropdown -->
                        <div class="gm-email-details" id="details-<?= $msg['id']; ?>">
                          <div class="gm-email-detail-row">
                            <div class="gm-email-detail-label">from:</div>
                            <div class="gm-email-detail-value"><?= e($msg['firstname'] . ' ' . $msg['lastname']); ?></div>
                          </div>
                          <div class="gm-email-detail-row">
                            <div class="gm-email-detail-label">emp code:</div>
                            <div class="gm-email-detail-value"><?= !empty($msg['emp_code']) ? e($msg['emp_code']) : 'N/A'; ?></div>
                          </div>
                          <?php if (!empty($msg['division_name'])): ?>
                          <div class="gm-email-detail-row">
                            <div class="gm-email-detail-label">division:</div>
                            <div class="gm-email-detail-value"><?= e($msg['division_name']); ?></div>
                          </div>
                          <?php endif; ?>
                          <div class="gm-email-detail-row">
                            <div class="gm-email-detail-label">to:</div>
                            <div class="gm-email-detail-value">me</div>
                          </div>
                          <div class="gm-email-detail-row">
                            <div class="gm-email-detail-label">date:</div>
                            <div class="gm-email-detail-value"><?= date('M d, Y, g:i A', strtotime($msg['date_sent'])); ?></div>
                          </div>
                          <div class="gm-email-detail-row">
                            <div class="gm-email-detail-label">subject:</div>
                            <div class="gm-email-detail-value"><?= e($msg['subject']); ?></div>
                          </div>
                        </div>
                      </div>

                      <div class="gm-header-right">
                        <span class="gm-mail-time" title="<?= _dt($msg['date_sent']); ?>">
                          <?= time_ago($msg['date_sent']); ?>
                        </span>
                        <div class="gm-header-actions">
                          <button class="gm-icon-btn" onclick="toggleMessageStar(<?= $msg['id']; ?>)" title="Star">
                            <span class="material-icons">star_border</span>
                          </button>
                          <button class="gm-icon-btn" onclick="startReply(<?= $msg['id']; ?>)" title="Reply">
                            <span class="material-icons">reply</span>
                          </button>
                          <button class="gm-icon-btn" onclick="startForward(<?= $msg['id']; ?>)" title="Forward">
                            <span class="material-icons">forward</span>
                          </button>
                          <button class="gm-icon-btn" title="More">
                            <span class="material-icons">more_vert</span>
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- BODY CONTENT -->
                    <div class="gm-body">
                      <?= $msg['message']; ?>
                    </div>

                    <!-- ATTACHMENTS -->
                    <?php if (count($msg['attachments']) > 0): ?>
                    <div class="gm-attachments">
                      <?php foreach ($msg['attachments'] as $attachment): ?>
                        <a href="<?= admin_url('internal_mail/download_attachment/' . $attachment['id']); ?>" class="gm-attachment-pill">
                          <span class="material-icons">attachment</span>
                          <span><?= e($attachment['original_file_name']); ?></span>
                          <span class="gm-attachment-size">(<?= bytesToSize($attachment['file_size']); ?>)</span>
                        </a>
                      <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

              </div>
            </div>

            <!-- QUICK REPLY -->
            <div class="gm-reply-wrapper">
              <div class="gm-reply-avatar">
                <?php 
                    $my_profile_img = staff_profile_image(get_staff_user_id(), ['staff-profile-image-small'], 'small');
                    if (strpos($my_profile_img, '<img') !== false) {
                        echo $my_profile_img;
                    } else {
                        $my_initials = strtoupper(substr(get_staff_full_name(), 0, 1));
                        echo $my_initials;
                    }
                ?>
              </div>
              <div class="gm-reply-box">
                <form action="<?= admin_url('internal_mail/compose'); ?>" method="post" id="reply-form">
                    <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                    <input type="hidden" name="thread_id" value="<?= $thread_id; ?>">
                    <input type="hidden" name="to[]" value="<?= $messages[count($messages)-1]['from_staff_id']; ?>">
                    <input type="hidden" name="subject" value="Re: <?= e($subject); ?>">
                    
                    <textarea name="message" id="reply-textarea"
                              placeholder="Reply to <?= e($messages[count($messages)-1]['firstname']); ?>..."></textarea>
                    
                    <div class="gm-reply-actions">
                      <button type="submit" class="gm-send-btn">Send</button>
                      <button type="button" class="gm-icon-btn" title="Formatting">
                        <span class="material-icons">format_color_text</span>
                      </button>
                      <button type="button" class="gm-icon-btn" title="Attach files">
                        <span class="material-icons">attach_file</span>
                      </button>
                    </div>
                </form>
              </div>
            </div>

          </div>
        </div>

        <script>
          function goBackToInbox() {
            window.location.href = '<?= admin_url('internal_mail/inbox'); ?>';
          }

          function prevMail() { 
            <?php if (!empty($prev_token)): ?>
              window.location.href = '<?= admin_url('internal_mail/view/'); ?>' + '<?= $prev_token; ?>';
            <?php endif; ?>
          }
          
          function nextMail() { 
            <?php if (!empty($next_token)): ?>
              window.location.href = '<?= admin_url('internal_mail/view/'); ?>' + '<?= $next_token; ?>';
            <?php endif; ?>
          }

          function archiveMessage() { 
            window.location.href='<?= admin_url('internal_mail/move/' . $token . '/archive'); ?>';
          }
          
          function deleteMessage() { 
            if(confirm('<?= _l('confirm_action_prompt'); ?>')){ 
              window.location.href='<?= admin_url('internal_mail/delete/' . $token); ?>'; 
            }
          }
          
          function markUnread() { 
            window.location.href = '<?= admin_url('internal_mail/mark_unread/' . $token); ?>';
          }
          
          function toggleStar() {
            // Toggle star icon class
            var icon = event.currentTarget.querySelector('.material-icons');
            if (icon.textContent === 'star_border') {
              icon.textContent = 'star';
            } else {
              icon.textContent = 'star_border';
            }
            // TODO: Call API to save state
          }
          
          function toggleMessageStar(msgId) {
            // Toggle message star
            var icon = event.currentTarget.querySelector('.material-icons');
            if (icon.textContent === 'star_border') {
              icon.textContent = 'star';
            } else {
              icon.textContent = 'star_border';
            }
            // TODO: Call API to save state
          }
          
          function printMessage() {
            window.print();
          }
          
          function openInNew() {
            window.open(window.location.href, '_blank');
          }
          
          function startReply(msgId) {
            window.location.href = '<?= admin_url('internal_mail/compose?reply_to_message_id='); ?>' + msgId;
          }
          
          function startForward(msgId) {
            window.location.href = '<?= admin_url('internal_mail/compose?forward_message_id='); ?>' + msgId;
          }
          
          function toggleEmailDetails(msgId) {
            var detailsDiv = document.getElementById('details-' + msgId);
            var arrowIcon = document.getElementById('arrow-' + msgId);
            
            if (detailsDiv.classList.contains('show')) {
              detailsDiv.classList.remove('show');
              arrowIcon.textContent = 'arrow_drop_down';
            } else {
              // Hide all other details first
              document.querySelectorAll('.gm-email-details').forEach(function(el) {
                el.classList.remove('show');
              });
              document.querySelectorAll('[id^="arrow-"]').forEach(function(el) {
                el.textContent = 'arrow_drop_down';
              });
              
              // Show this one
              detailsDiv.classList.add('show');
              arrowIcon.textContent = 'arrow_drop_up';
            }
          }
        </script>
    </div>
</div>
<?php init_tail(); ?>
