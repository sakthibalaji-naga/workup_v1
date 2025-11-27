<div role="tabpanel" class="tab-pane" id="logs">
    <div class="panel_s">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-5 tw-h-5 tw-mr-1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <span><?php echo _l('ticket_logs'); ?></span>
                    </h4>
                    <div class="clearfix"></div>
                    <hr class="tw-mt-2 sm:tw-mt-0 tw-mb-4"/>
                    <div class="table-responsive">
                    <?php
                        $ticketLogStatusLookup = isset($ticket_status_lookup) && is_array($ticket_status_lookup) ? $ticket_status_lookup : [];
                        $ticketLogStatusKeys   = ['previous_status','new_status','target_status','from_status','status','old_status'];
                        $ticketLogStaffKeys    = ['requested_by','assignee_id','responded_by','from_assigned','to_assigned','assigned','created_by','approver_id','user_id','staff_id','requested_by_id'];
                        if (!isset($ticketLogStaffCache)) { $ticketLogStaffCache = []; }
                        if (!function_exists('ticket_logs_human_label')) {
                            function ticket_logs_human_label($key) {
                                $langKey = 'ticket_log_' . strtolower($key);
                                $translated = _l($langKey);
                                if ($translated !== $langKey) {
                                    return $translated;
                                }
                                $human = str_replace('_', ' ', strtolower($key));
                                return ucwords($human);
                            }
                        }

                        if (!function_exists('ticket_logs_format_value')) {
                            function ticket_logs_format_value($key, $value, $statusKeys, $statusLookup, $staffKeys, &$staffCache) {
                                $lowerKey = strtolower($key);
                                if (is_numeric($value)) {
                                    $id = (int) $value;
                                    if (in_array($lowerKey, $statusKeys, true) && isset($statusLookup[$id])) {
                                        return $statusLookup[$id];
                                    }
                                    if (in_array($lowerKey, $staffKeys, true)) {
                                        if (!isset($staffCache[$id])) {
                                            $staff = get_staff($id);
                                            $name  = $staff ? trim(($staff->firstname ?? '') . ' ' . ($staff->lastname ?? '')) : '';
                                            $staffCache[$id] = $name !== '' ? $name : ('#' . $id);
                                        }
                                        return $staffCache[$id];
                                    }
                                }
                                if (is_scalar($value)) {
                                    return (string) $value;
                                }
                                return json_encode($value);
                            }
                        }
                    ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><?php echo _l('timestamp'); ?></th>
                                    <th><?php echo _l('user'); ?></th>
                                    <th><?php echo _l('action'); ?></th>
                                    <th><?php echo _l('details'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ticket_logs)) {
                                foreach($ticket_logs as $log) {
                                    $user_name = '';
                                    if ($log['user_type'] == 'staff' && $log['user_id']) {
                                        $staff = get_staff($log['user_id']);
                                        $user_name = $staff ? ($staff->firstname . ' ' . $staff->lastname) : 'Unknown Staff';
                                    } else {
                                        $user_name = 'System';
                                    }

                                    $details = json_decode($log['log_details'], true);

                                    if ($log['log_type'] === 'reopen_request_created' && is_array($details)) {
                                        $requestedByRaw = $details['requested_by'] ?? null;
                                        $previousStatusRaw = $details['previous_status'] ?? null;
                                        $targetStatusRaw   = $details['target_status'] ?? null;

                                        $requestedBy = ticket_logs_format_value('requested_by', $requestedByRaw, $ticketLogStatusKeys, $ticketLogStatusLookup, $ticketLogStaffKeys, $ticketLogStaffCache);
                                        $previousStatus = ticket_logs_format_value('previous_status', $previousStatusRaw, $ticketLogStatusKeys, $ticketLogStatusLookup, $ticketLogStaffKeys, $ticketLogStaffCache);
                                        $targetStatus   = ticket_logs_format_value('target_status', $targetStatusRaw, $ticketLogStatusKeys, $ticketLogStatusLookup, $ticketLogStaffKeys, $ticketLogStaffCache);

                                        echo '<div class="tw-mb-2 tw-font-medium">' . sprintf(_l('ticket_log_reopen_request_created_summary'), htmlspecialchars($requestedBy, ENT_QUOTES, 'UTF-8'), htmlspecialchars($previousStatus, ENT_QUOTES, 'UTF-8'), htmlspecialchars($targetStatus, ENT_QUOTES, 'UTF-8')) . '</div>';

                                        unset($details['requested_by'], $details['previous_status'], $details['target_status']);
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo _dt($log['timestamp']); ?></td>
                                        <td><?php echo $user_name; ?></td>
                                        <td><?php echo _l($log['log_type']); ?></td>
                                        <td>
                                            <?php
                                            if (is_array($details)) {

                                                foreach ($details as $key => $value) {

                                                    $lowerKey = strtolower($key);

                                                    $isIdKey  = ($lowerKey === 'id' || substr($lowerKey, -3) === '_id');

                                                    if (is_array($value)) {

                                                        if (!$isIdKey) {

                                                            echo '<strong>' . ticket_logs_human_label($key) . ':</strong><br />';

                                                        }

                                                        foreach ($value as $k => $v) {

                                                            $displayRaw = ticket_logs_format_value($k, $v, $ticketLogStatusKeys, $ticketLogStatusLookup, $ticketLogStaffKeys, $ticketLogStaffCache);

                                                            $displayEsc = htmlspecialchars($displayRaw, ENT_QUOTES, 'UTF-8');

                                                            $subLower   = strtolower($k);

                                                            $subIsId    = ($subLower === 'id' || substr($subLower, -3) === '_id');

                                                            if ($subIsId) {

                                                                echo '&nbsp;&nbsp;' . $displayEsc . '<br />';

                                                            } else {

                                                                echo '&nbsp;&nbsp;<strong>' . _l($k) . '</strong>: ' . $displayEsc . '<br />';

                                                            }

                                                        }

                                                    } else {

                                                        $displayRaw = ticket_logs_format_value($key, $value, $ticketLogStatusKeys, $ticketLogStatusLookup, $ticketLogStaffKeys, $ticketLogStaffCache);

                                                        $displayEsc = htmlspecialchars($displayRaw, ENT_QUOTES, 'UTF-8');

                                                        if ($isIdKey) {

                                                            echo $displayEsc . '<br />';

                                                        } else {

                                                            echo '<strong>' . ticket_logs_human_label($key) . ':</strong> ' . $displayEsc . '<br />';

                                                        }

                                                    }

                                                }

                                            } else {

                                                $fallbackDisplay = ticket_logs_format_value('value', $log['log_details'], $ticketLogStatusKeys, $ticketLogStatusLookup, $ticketLogStaffKeys, $ticketLogStaffCache);

                                                echo htmlspecialchars($fallbackDisplay, ENT_QUOTES, 'UTF-8');

                                            }

                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?><?php } else { ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            <?php echo _l('ticket_logs_empty'); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
</tbody>
                        </table>
                    </div>
                    <?php if (($ticket_logs_pages ?? 1) > 1) { ?>
                    <div class="tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-2 tw-mt-3">
                        <div class="tw-text-sm tw-text-neutral-600">
                            <?= sprintf(_l('ticket_logs_pagination_label'), (int)($ticket_logs_start ?? 0), (int)($ticket_logs_end ?? 0), (int)($ticket_logs_total ?? 0)); ?>
                        </div>
                        <ul class="pagination tw-m-0">
                            <?php $logsBaseUrl = admin_url('tickets/ticket/' . $ticket->ticketid); ?>
                            <?php if (($ticket_logs_page ?? 1) > 1) { ?>
                                <li><a href="<?= $logsBaseUrl . '?logs_page=' . (($ticket_logs_page ?? 1) - 1) . '#logs'; ?>">&laquo;</a></li>
                            <?php } ?>
                            <?php for ($p = 1; $p <= ($ticket_logs_pages ?? 1); $p++) { ?>
                                <li class="<?= $p == ($ticket_logs_page ?? 1) ? 'active' : ''; ?>"><a href="<?= $logsBaseUrl . '?logs_page=' . $p . '#logs'; ?>"><?= $p; ?></a></li>
                            <?php } ?>
                            <?php if (($ticket_logs_page ?? 1) < ($ticket_logs_pages ?? 1)) { ?>
                                <li><a href="<?= $logsBaseUrl . '?logs_page=' . (($ticket_logs_page ?? 1) + 1) . '#logs'; ?>">&raquo;</a></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
</div>
