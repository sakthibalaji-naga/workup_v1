<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<a href="#" class="dropdown-toggle notifications-icon !tw-px-0 tw-group" data-toggle="dropdown" aria-expanded="false">
    <span class="sm:tw-inline-flex sm:tw-items-center sm:tw-justify-center sm:tw-h-8 sm:tw-w-9 sm:-tw-mt-1.5">
        <i class="fa-regular fa-bell fa-lg tw-shrink-0 tw-text-neutral-400 group-hover:tw-text-neutral-800"></i>
        <?php if ($current_user->total_unread_notifications > 0) { ?>
        <span
            class="tw-leading-none tw-px-1 tw-py-0.5 tw-text-xs bg-info tw-z-10 tw-absolute tw-rounded-full -tw-right-0.5 -tw-top-2 sm:tw-top-2 tw-min-w-[18px] tw-min-h-[18px] tw-inline-flex tw-items-center tw-justify-center icon-notifications tw-mt-px icon-total-indicator"><?= e($current_user->total_unread_notifications); ?></span>
        <?php } ?>
    </span>
</a>
<?php $_notifications = $this->misc_model->get_user_notifications(); ?>
<ul class="dropdown-menu notifications animated fadeIn width400<?= count($_notifications) > 0 ? ' tw-pb-0' : ''; ?>"
    data-total-unread="<?= e($current_user->total_unread_notifications); ?>">
    <div class="tw-py-1 tw-px-3 tw-mb-1.5 tw-text-right">
        <a href="#" class="tw-text-right tw-inline text-muted"
            onclick="event.stopPropagation(); mark_all_notifications_as_read_inline(this); return false;">
            <?= _l('mark_all_as_read'); ?>
        </a>
    </div>
    <li class="divider"></li>
    <?php foreach ($_notifications as $notification) { ?>
    <?php
        $isFromStaff  = ($notification['fromcompany'] == null && (int) $notification['fromuserid'] !== 0);
        $isFromClient = ($notification['fromcompany'] == null && (int) $notification['fromclientid'] !== 0);

        $additional_data = [];
        if (! empty($notification['additional_data'])) {
            $additional_data = unserialize($notification['additional_data']);

            if (! is_array($additional_data)) {
                $additional_data = [];
            }

            foreach ($additional_data as $i => $data) {
                if (strpos($data, '<lang>') !== false) {
                    $lang = get_string_between($data, '<lang>', '</lang>');
                    $temp = _l($lang);
                    if (strpos($temp, 'project_status_') !== false) {
                        $status = get_project_status_by_id(strafter($temp, 'project_status_'));
                        $temp   = $status['name'];
                    }
                    $additional_data[$i] = $temp;
                }
            }
        }

        $descriptionRaw = _l($notification['description'], $additional_data);
        $descriptionKey = $notification['description'];

        $authorName              = '';
        $customerBadge           = '';
        $customerIndicatorLabel  = '';
        $notificationIconUrl     = base_url('assets/images/user-placeholder.jpg');

        if ($isFromStaff) {
            $authorName          = e($notification['from_fullname']);
            $notificationIconUrl = staff_profile_image_url($notification['fromuserid']);
        } elseif ($isFromClient) {
            $authorName              = e($notification['from_fullname']);
            $customerIndicatorLabel  = _l('is_customer_indicator');
            $customerBadge           = '<span class="label inline-block label-info tw-m-0 tw-uppercase tw-text-[10px] tw-font-semibold tw-leading-none">' . e($customerIndicatorLabel) . '</span>';
            $notificationIconUrl     = contact_profile_image_url($notification['fromclientid']);
        }

        $notificationText = trim(strip_tags($descriptionRaw));
        if ($authorName !== '') {
            $notificationText = $authorName . ' - ' . $notificationText;
        }
        if ($customerIndicatorLabel !== '') {
            $notificationText .= ' (' . $customerIndicatorLabel . ')';
        }
        $notificationText = preg_replace('/\s+/', ' ', $notificationText);

        $iconClass = 'fa-solid fa-bell';
        if (strpos($descriptionKey, 'task') !== false) {
            $iconClass = 'fa-solid fa-square-check';
        } elseif (strpos($descriptionKey, 'invoice') !== false) {
            $iconClass = 'fa-solid fa-file-invoice-dollar';
        } elseif (strpos($descriptionKey, 'project') !== false) {
            $iconClass = 'fa-solid fa-diagram-project';
        } elseif (strpos($descriptionKey, 'ticket') !== false) {
            $iconClass = 'fa-solid fa-ticket';
        } elseif (strpos($descriptionKey, 'lead') !== false) {
            $iconClass = 'fa-solid fa-user-tie';
        } elseif (strpos($descriptionKey, 'estimate') !== false || strpos($descriptionKey, 'proposal') !== false) {
            $iconClass = 'fa-solid fa-file-signature';
        } elseif (strpos($descriptionKey, 'contract') !== false) {
            $iconClass = 'fa-solid fa-file-contract';
        } elseif (strpos($descriptionKey, 'expense') !== false) {
            $iconClass = 'fa-solid fa-wallet';
        } elseif (strpos($descriptionKey, 'goal') !== false) {
            $iconClass = 'fa-solid fa-flag-checkered';
        }

        $boxClasses = [
            'tw-p-3',
            'notification-box',
            'tw-relative',
            'tw-flex',
            'tw-gap-3',
            'tw-items-start',
            'tw-rounded-lg',
            'tw-transition',
            'tw-duration-150',
            'tw-border',
        ];

        $isUnread = (int) $notification['isread_inline'] === 0;

        if ($isUnread) {
            $boxClasses[] = 'tw-bg-primary-50';
            $boxClasses[] = 'tw-border-primary-100';
            $boxClasses[] = 'tw-shadow-sm';
            $boxClasses[] = 'unread';
        } else {
            $boxClasses[] = 'tw-bg-white';
            $boxClasses[] = 'hover:tw-bg-neutral-50';
            $boxClasses[] = 'tw-border-transparent';
        }

        $avatarHtml = '';
        if ($isFromStaff) {
            $avatarHtml = staff_profile_image($notification['fromuserid'], [
                'staff-profile-image-small',
                'notification-image',
                'tw-h-10',
                'tw-w-10',
                'tw-rounded-full',
                'tw-object-cover',
                'tw-shadow-sm',
            ]);
        } elseif ($isFromClient) {
            $avatarHtml = '<img src="' . e($notificationIconUrl) . '" alt="' . $authorName . '" class="client-profile-image-small notification-image tw-h-10 tw-w-10 tw-rounded-full tw-object-cover tw-shadow-sm" />';
        } else {
            $avatarHtml = '<span class="tw-inline-flex tw-items-center tw-justify-center tw-h-10 tw-w-10 tw-rounded-full tw-bg-neutral-100 tw-text-neutral-500"><i class="' . e($iconClass) . ' tw-text-lg"></i></span>';
        }
    ?>
    <li class="relative notification-wrapper tw-relative"
        data-notification-id="<?= e($notification['id']); ?>">
        <a href="<?= empty($notification['link']) ? '#' : admin_url($notification['link']); ?>"
            onclick="<?= empty($notification['link']) ? 'event.preventDefault();' : ''; ?>"
            class="notification-handler !tw-p-0<?= $notification['isread_inline'] == 0 ? ' unread-notification' : ''; ?><?= empty($notification['link']) ? ' tw-cursor-text' : ' tw-cursor-pointer notification-top notification-link'; ?> tw-block">
            <div class="<?= implode(' ', $boxClasses); ?>">
                <div class="tw-flex-shrink-0 tw-pt-0.5 notification-avatar" data-notification-icon="<?= e($notificationIconUrl); ?>">
                    <?= $avatarHtml; ?>
                </div>
                <div class="tw-flex-1 tw-min-w-0 tw-space-y-2">
                    <?php if ($authorName !== '') { ?>
                    <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
                        <span class="tw-text-sm tw-font-semibold tw-text-neutral-800"><?= $authorName; ?></span>
                        <?= $customerBadge; ?>
                    </div>
                    <?php } ?>
                    <div class="notification-title tw-text-sm tw-leading-snug tw-text-neutral-700" data-notification-text="<?= e($notificationText); ?>">
                        <?= $descriptionRaw; ?>
                    </div>
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <span class="tw-inline-flex tw-items-center tw-justify-center tw-h-5 tw-w-5 tw-rounded-full tw-bg-neutral-100">
                            <i class="fa-regular fa-clock tw-text-[11px] tw-text-neutral-500"></i>
                        </span>
                        <span class="tw-text-xs tw-font-medium tw-text-neutral-500 notification-date"
                            data-placement="right" data-toggle="tooltip"
                            data-title="<?= e(_dt($notification['date'])); ?>">
                            <?= e(time_ago($notification['date'])); ?>
                        </span>
                    </div>
                </div>
            </div>
        </a>

        <?php if ($notification['isread_inline'] == 0) { ?>
        <a href="#" class="text-muted pull-right not-mark-as-read-inline tw-absolute tw-top-3 tw-right-4 tw-text-neutral-400 hover:tw-text-primary-600"
            onclick="set_notification_read_inline(<?= e($notification['id']); ?>);"
            data-placement="left" data-toggle="tooltip"
            data-title="<?= _l('mark_as_read'); ?>">
            <small>
                <i class="fa-regular fa-circle-check"></i>
            </small>
        </a>
        <?php } ?>
    </li>
    <li class="divider !tw-my-0"></li>
    <?php } ?>
    <div class="tw-text-center tw-p-4 tw-bg-neutral-50">
        <?php if (count($_notifications) > 0) { ?>
        <a class="btn btn-default"
            href="<?= admin_url('profile?notifications=true'); ?>">
            <?= _l('nav_view_all_notifications'); ?>
        </a>
        <?php } else { ?>
        <p class="tw-text-neutral-500 tw-font-medium tw-mb-0 tw-inline-flex tw-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="tw-w-6 tw-h-6 tw-mr-1">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <?= _l('nav_no_notifications'); ?>
        </p>
        <?php } ?>
    </div>

</ul>