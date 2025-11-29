<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                
                <style>
                /* Overall layout */
                .gmail-layout {
                  display: grid;
                  grid-template-columns: 260px minmax(0, 1fr);
                  gap: 0;
                  background: #f8f9fa;
                  border-radius: 8px;
                  box-shadow: 0 1px 3px rgba(60, 64, 67, 0.3);
                  border: 1px solid #e0e0e0;
                  min-height: 600px;
                }

                /* Left nav */
                .gmail-nav {
                  padding: 16px 12px;
                  border-right: 1px solid #e0e0e0;
                  background: #f1f3f4;
                }

                .gmail-compose-btn {
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
                }
                
                .gmail-compose-btn:hover {
                    box-shadow: 0 4px 6px rgba(60, 64, 67, 0.3);
                    background: #f8f9fa;
                }

                .gmail-compose-btn .fa {
                  margin-right: 12px;
                  font-size: 16px;
                  color: #ea4335; /* Google Red-ish */
                }

                .gmail-folder-list {
                  display: flex;
                  flex-direction: column;
                  gap: 2px;
                }

                .gmail-folder-item {
                  display: flex;
                  align-items: center;
                  border-radius: 0 16px 16px 0;
                  padding: 8px 12px 8px 16px;
                  font-size: 14px;
                  text-decoration: none !important;
                  color: #3c4043;
                  font-weight: 500;
                }

                .gmail-folder-item:hover {
                    background: #e8eaed;
                }

                .gmail-folder-item .fa {
                  font-size: 16px;
                  margin-right: 16px;
                  width: 20px;
                  text-align: center;
                  color: #5f6368;
                }

                .gmail-folder-item .badge {
                  margin-left: auto;
                  font-size: 12px;
                  background: transparent;
                  color: #1a73e8;
                  font-weight: bold;
                }

                .gmail-folder-item.active {
                  background: #e8f0fe;
                  color: #1967d2;
                }
                
                .gmail-folder-item.active .fa {
                    color: #1967d2;
                }

                /* Right side: toolbar and list */
                .gmail-main {
                  display: flex;
                  flex-direction: column;
                  background: #fff;
                  border-radius: 0 8px 8px 0;
                }

                .gmail-inbox-toolbar {
                  display: flex;
                  justify-content: space-between;
                  align-items: center;
                  padding: 8px 16px;
                  border-bottom: 1px solid #e0e0e0;
                  font-size: 13px;
                  color: #5f6368;
                }

                .left-tools,
                .right-tools {
                  display: flex;
                  align-items: center;
                  gap: 12px;
                }

                .range-text {
                  margin-right: 8px;
                }

                /* Checkbox styling */
                .checkbox-wrapper {
                  position: relative;
                  display: inline-flex;
                  align-items: center;
                  cursor: pointer;
                  width: 18px;
                  height: 18px;
                }

                .checkbox-wrapper input {
                  opacity: 0;
                  width: 0;
                  height: 0;
                  position: absolute;
                }

                .checkbox-fake {
                  width: 18px;
                  height: 18px;
                  border-radius: 2px;
                  border: 2px solid #5f6368;
                  display: inline-block;
                  transition: all 0.2s;
                }

                .checkbox-wrapper input:checked + .checkbox-fake {
                  background: #1a73e8;
                  border-color: #1a73e8;
                  position: relative;
                }
                
                .checkbox-wrapper input:checked + .checkbox-fake::after {
                    content: '';
                    position: absolute;
                    left: 5px;
                    top: 1px;
                    width: 5px;
                    height: 10px;
                    border: solid white;
                    border-width: 0 2px 2px 0;
                    transform: rotate(45deg);
                }

                /* Icon button style */
                .icon-btn {
                  border: none;
                  background: transparent;
                  width: 36px;
                  height: 36px;
                  border-radius: 50%;
                  cursor: pointer;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  color: #5f6368;
                  transition: background 0.2s;
                }

                .icon-btn:hover {
                  background: rgba(60, 64, 67, 0.08);
                  color: #202124;
                }

                .icon-btn .fa {
                  font-size: 16px;
                }

                /* Message list rows */
                .gmail-message-list {
                  display: flex;
                  flex-direction: column;
                  overflow-y: auto;
                  max-height: calc(100vh - 200px);
                }

                .gmail-row {
                  display: grid;
                  grid-template-columns: auto 200px minmax(0, 1fr) 100px;
                  column-gap: 16px;
                  padding: 10px 16px;
                  align-items: center;
                  font-size: 14px;
                  text-decoration: none !important;
                  color: #202124;
                  border-bottom: 1px solid #f1f3f4;
                  cursor: pointer;
                  transition: box-shadow 0.2s, background 0.2s;
                }

                .gmail-row:hover {
                  background: #f5f7f7;
                  box-shadow: inset 1px 0 0 #dadce0, inset -1px 0 0 #dadce0, 0 1px 2px 0 rgba(60,64,67,.3), 0 1px 3px 1px rgba(60,64,67,.15);
                  z-index: 1;
                }

                .gmail-row.unread {
                  background: #fff;
                  font-weight: 700;
                }
                
                .gmail-row.unread .sender,
                .gmail-row.unread .subject-text {
                    font-weight: 700;
                }

                .gmail-row .cell.controls {
                  display: flex;
                  align-items: center;
                  gap: 12px;
                }

                .gmail-row .sender {
                  white-space: nowrap;
                  overflow: hidden;
                  text-overflow: ellipsis;
                  color: #202124;
                }

                .gmail-row .subject-snippet {
                  display: flex;
                  min-width: 0;
                  white-space: nowrap;
                  overflow: hidden;
                }

                .subject-text {
                  margin-right: 4px;
                  color: #202124;
                }

                .snippet-text {
                  color: #5f6368;
                  font-weight: 400;
                }

                .gmail-row .time {
                  text-align: right;
                  font-size: 12px;
                  color: #5f6368;
                  font-weight: 500;
                }
                
                .star-btn {
                    color: #dadce0;
                }
                
                .star-btn.active {
                    color: #f4b400;
                }
                
                /* Mobile */
                @media (max-width: 900px) {
                  .gmail-layout {
                    grid-template-columns: 1fr;
                  }
                  .gmail-nav {
                    display: none; 
                  }
                }
                </style>

                <div class="gmail-layout">

                  <!-- LEFT SIDEBAR -->
                  <aside class="gmail-nav">
                    <a href="<?= admin_url('internal_mail/compose'); ?>" class="gmail-compose-btn">
                      <i class="fa fa-pencil"></i>
                      <?= _l('internal_mail_compose'); ?>
                    </a>

                    <nav class="gmail-folder-list">
                      <a href="<?= admin_url('internal_mail/inbox'); ?>"
                         class="gmail-folder-item <?= $mailbox_type == 'inbox' ? 'active' : ''; ?>">
                        <i class="fa fa-inbox"></i>
                        <?= _l('internal_mail_inbox'); ?>
                        <?php if ($unread_count > 0): ?>
                            <span class="badge"><?= $unread_count; ?></span>
                        <?php endif; ?>
                      </a>

                      <a href="<?= admin_url('internal_mail/sent'); ?>" class="gmail-folder-item <?= $mailbox_type == 'sent' ? 'active' : ''; ?>">
                        <i class="fa fa-paper-plane"></i>
                        <?= _l('internal_mail_sent'); ?>
                      </a>

                      <a href="<?= admin_url('internal_mail/drafts'); ?>" class="gmail-folder-item <?= $mailbox_type == 'drafts' ? 'active' : ''; ?>">
                        <i class="fa fa-file-text"></i>
                        <?= _l('internal_mail_drafts'); ?>
                      </a>

                      <a href="<?= admin_url('internal_mail/trash'); ?>" class="gmail-folder-item <?= $mailbox_type == 'trash' ? 'active' : ''; ?>">
                        <i class="fa fa-trash"></i>
                        <?= _l('internal_mail_trash'); ?>
                      </a>
                    </nav>
                    
                    <!-- Search (Optional, keeping it simple) -->
                    <div class="tw-mt-4 tw-px-3">
                        <form action="<?= admin_url('internal_mail/search'); ?>" method="get">
                            <div class="input-group input-group-sm">
                                <input type="text" name="q" class="form-control" placeholder="<?= _l('internal_mail_search'); ?>" value="<?= isset($keyword) ? e($keyword) : ''; ?>" style="border-radius: 4px 0 0 4px;">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit" style="border-radius: 0 4px 4px 0;">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </form>
                    </div>
                  </aside>

                  <!-- RIGHT SIDE: TOOLBAR + MESSAGE LIST -->
                  <section class="gmail-main">

                    <!-- TOOLBAR -->
                    <div class="gmail-inbox-toolbar">
                      <div class="left-tools">
                        <label class="checkbox-wrapper" title="Select All">
                          <input type="checkbox" id="select-all" onchange="toggleAllRows(this)">
                          <span class="checkbox-fake"></span>
                        </label>

                        <button class="icon-btn" onclick="window.location.reload()" title="Refresh">
                          <i class="fa fa-refresh"></i>
                        </button>

                        <button class="icon-btn" title="More">
                          <i class="fa fa-ellipsis-v"></i>
                        </button>
                      </div>

                      <div class="right-tools">
                        <span class="range-text">
                            <?php 
                                $total = count($mails); // This is just current page count, ideally we need total count from DB
                                echo "1-" . $total . " of " . $total; 
                            ?>
                        </span>
                        <button class="icon-btn" disabled>
                          <i class="fa fa-chevron-left"></i>
                        </button>
                        <button class="icon-btn" disabled>
                          <i class="fa fa-chevron-right"></i>
                        </button>
                      </div>
                    </div>

                    <!-- MESSAGE LIST -->
                    <div class="gmail-message-list">
                      <?php if (count($mails) > 0): ?>
                          <?php foreach ($mails as $mail): ?>
                            <?php 
                                $is_unread = isset($mail['unread_messages']) && $mail['unread_messages'] > 0;
                                $row_class = $is_unread ? 'unread' : '';
                                $mail_url = admin_url('internal_mail/view/' . $mail['token']);
                            ?>
                            <div class="gmail-row <?= $row_class; ?>" onclick="window.location.href='<?= $mail_url; ?>'">

                                <!-- checkbox + star -->
                                <div class="cell controls" onclick="event.stopPropagation()">
                                  <label class="checkbox-wrapper">
                                    <input type="checkbox" class="row-checkbox" value="<?= $mail['id']; ?>">
                                    <span class="checkbox-fake"></span>
                                  </label>
                                  <button class="icon-btn star-btn" onclick="toggleStar(event, '<?= $mail['id']; ?>')">
                                    <i class="fa fa-star-o"></i>
                                  </button>
                                </div>

                                <!-- sender -->
                                <div class="sender">
                                  <?php if ($mailbox_type == 'sent'): ?>
                                      To: <?= isset($mail['sender_fullname']) ? e($mail['sender_fullname']) : 'Unknown'; ?>
                                  <?php elseif ($mailbox_type == 'drafts'): ?>
                                      <span class="text-danger">Draft</span>
                                  <?php else: ?>
                                      <?= isset($mail['sender_fullname']) ? e($mail['sender_fullname']) : 'Unknown'; ?>
                                      <?php if (isset($mail['total_messages']) && $mail['total_messages'] > 1): ?>
                                          <span class="text-muted small">(<?= $mail['total_messages']; ?>)</span>
                                      <?php endif; ?>
                                  <?php endif; ?>
                                </div>

                                <!-- subject + snippet -->
                                <div class="subject-snippet">
                                  <span class="subject-text">
                                    <?= empty($mail['latest_subject']) ? '(' . _l('no_subject') . ')' : e($mail['latest_subject']); ?>
                                  </span>
                                  <span class="snippet-text">
                                    - <?= strip_tags(mb_substr($mail['latest_message'], 0, 60)) . '...'; ?>
                                  </span>
                                </div>

                                <!-- time -->
                                <div class="time">
                                  <?= time_ago($mail['last_message_date']); ?>
                                </div>

                            </div>
                          <?php endforeach; ?>
                      <?php else: ?>
                        <div class="text-center p-5 text-muted" style="padding: 40px;">
                            <i class="fa fa-inbox fa-3x tw-mb-3"></i><br>
                            <?= _l('internal_mail_no_messages'); ?>
                        </div>
                      <?php endif; ?>

                    </div>

                  </section>
                </div>

                <script>
                  function toggleAllRows(checkbox) {
                    document.querySelectorAll('.row-checkbox').forEach(cb => {
                      cb.checked = checkbox.checked;
                    });
                  }

                  function toggleStar(event, id) {
                    event.preventDefault();
                    event.stopPropagation();
                    // Toggle star icon class
                    var btn = event.currentTarget;
                    var icon = btn.querySelector('i');
                    if (icon.classList.contains('fa-star-o')) {
                        icon.classList.remove('fa-star-o');
                        icon.classList.add('fa-star');
                        btn.classList.add('active');
                    } else {
                        icon.classList.remove('fa-star');
                        icon.classList.add('fa-star-o');
                        btn.classList.remove('active');
                    }
                    // TODO: Call API to save state
                  }
                </script>

            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
