<?php defined('BASEPATH') or exit('No direct script access allowed');
hooks()->do_action('before_sms_gateways_settings');

// Load custom language file for SMS strings
$CI =& get_instance();
$CI->lang->load('custom_lang', 'english');

$gateways       = $this->app_sms->get_gateways();
$triggers       = $this->app_sms->get_available_triggers();
$total_gateways = count($gateways);

if ($total_gateways > 1) { ?>
<div class="alert alert-info">
    <?php echo _l('notice_only_one_active_sms_gateway'); ?>
</div>
<?php } ?>

<div class="panel-group" id="sms_gateways_options" role="tablist" aria-multiselectable="false">
    <?php foreach ($gateways as $gateway) { ?>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="<?php echo 'heading' . $gateway['id']; ?>">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#sms_gateways_options"
                    href="#sms_<?php echo e($gateway['id']); ?>" aria-expanded="true"
                    aria-controls="sms_<?php echo e($gateway['id']); ?>">
                    <?php echo e($gateway['name']); ?> <span class="pull-right"><i class="fa fa-sort-down"></i></span>
                </a>
            </h4>
        </div>
        <div id="sms_<?php echo e($gateway['id']); ?>" class="panel-collapse collapse<?php if ($this->app_sms->get_option($gateway['id'], 'active') == 1 || $total_gateways == 1) {
    echo ' in';
} ?>" role="tabpanel" aria-labelledby="<?php echo 'heading' . $gateway['id']; ?>">
            <div class="panel-body">
        <?php
        if (isset($gateway['info']) && $gateway['info'] != '') {
            echo $gateway['info'];
        }

       if (isset($gateway['deprecated'])) { ?>
            <div class="alert alert-warning">
                This SMS gateway is deprecated and may be removed in future updates.
            </div>
        <?php }

        foreach ($gateway['options'] as $g_option) {
            $type = isset($g_option['field_type']) ? $g_option['field_type'] : 'text';
            if ($type == 'text') {
                echo render_input(
                    'settings[' . $this->app_sms->option_name($gateway['id'], $g_option['name']) . ']',
                    $g_option['label'],
                    $this->app_sms->get_option($gateway['id'], $g_option['name']),
                    'text',
                    [],
                    [],
                    isset($g_option['info']) ? 'mbot5' : 'mbot15'
                );
            } elseif ($type == 'radio') {
                ?>
                <div class="form-group">
                    <p><?php echo e($g_option['label']); ?></p>
                    <?php
                foreach ($g_option['options'] as $option) {
                    ?>
                    <div class="radio radio-info radio-inline">
                        <input type="radio"
                            name="settings[<?php echo $optionName = $this->app_sms->option_name($gateway['id'], $g_option['name']); ?>]"
                            value="<?php echo e($option['value']); ?>"
                            id="<?php echo $option['value'] . '-' . $optionName; ?>" <?php if ($this->app_sms->get_option($gateway['id'], $g_option['name']) == $option['value']) {
                        echo ' checked';
                    } ?>>
                        <label
                            for="<?php echo $option['value'] . '-' . $optionName; ?>"><?php echo e($option['label']); ?></label>
                    </div>
                    <?php
                } ?>
                </div>
                <?php
            }

            if (isset($g_option['info'])) { ?>
                <div class="mbot15">
                    <?php echo $g_option['info']; ?>
                </div>
                <?php }
        }
        echo '<div class="sms_gateway_active">';

        echo render_yes_no_option($this->app_sms->option_name($gateway['id'], 'active'), 'Active');

        echo '</div>';
        ?>
            </div>
        </div>
    </div>
    <?php } ?>
    <hr />
    <?php echo render_input('settings[bitly_access_token]', 'Bitly Access Token', get_option('bitly_access_token')); ?>
    <hr />
    <h4 class="bold mbot15"><?php echo _l('sms_authentication_settings'); ?></h4>
    <?php echo render_input('settings[otp_resend_cooldown]', _l('otp_resend_cooldown_seconds'), get_option('otp_resend_cooldown'), 'number', ['min' => '0', 'step' => '1'], [], 'mbot15'); ?>
    <div class="mbot15">
        <?php echo _l('otp_resend_cooldown_seconds_help'); ?>
    </div>
    <?php echo render_input('settings[otp_expiry_minutes]', _l('otp_expiry_minutes'), get_option('otp_expiry_minutes', 10), 'number', ['min' => '1', 'step' => '1'], [], 'mbot15'); ?>
    <div class="mbot15">
        <?php echo _l('otp_expiry_minutes_help'); ?>
    </div>
    <hr />
    <h4 class="mbot15">
        <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip"
            data-title="<?php echo _l('sms_trigger_disable_tip'); ?>"></i>
        <?php echo _l('triggers'); ?>
    </h4>

    <div class="panel-group" id="sms_trigger_groups" role="tablist" aria-multiselectable="false">
    <?php
    $grouped_triggers = [];
    foreach ($triggers as $trigger_name => $trigger_opts) {
        $group = 'Ticket';
        if (strpos($trigger_name, 'staff') !== false) {
            $group = 'Staff Notifications';
        }
        $grouped_triggers[$group][$trigger_name] = $trigger_opts;
    }

    foreach ($grouped_triggers as $group_name => $group_triggers) {
    ?>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="<?php echo 'heading-trigger-' . md5($group_name); ?>">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#sms_trigger_groups"
                    href="#sms_trigger_<?php echo md5($group_name); ?>" aria-expanded="true"
                    aria-controls="sms_trigger_<?php echo md5($group_name); ?>">
                    <?php echo e($group_name); ?> <span class="pull-right"><i class="fa fa-sort-down"></i></span>
                </a>
            </h4>
        </div>
        <div id="sms_trigger_<?php echo md5($group_name); ?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="<?php echo 'heading-trigger-' . md5($group_name); ?>">
            <div class="panel-body">
                <?php
                foreach ($group_triggers as $trigger_name => $trigger_opts) {
            echo '<a href="#" onclick="slideToggle(\'#sms_merge_fields_' . $trigger_name . '\'); return false;" class="pull-right"><small>' . _l('available_merge_fields') . '</small></a>';

            $label = '<b>' . $trigger_opts['label'] . '</b>';
            if (isset($trigger_opts['info']) && $trigger_opts['info'] != '') {
                $label .= '<p>' . $trigger_opts['info'] . '</p>';
            }

            echo render_textarea('settings[' . $this->app_sms->trigger_option_name($trigger_name) . ']', $label, $trigger_opts['value']);

            echo render_yes_no_option($this->app_sms->trigger_option_name($trigger_name) . '_active', 'Active');

            hooks()->do_action('after_sms_trigger_textarea_content', ['name' => $trigger_name, 'options' => $trigger_opts]);

            $merge_fields = '';

            foreach ($trigger_opts['merge_fields'] as $merge_field) {
                $merge_fields .= $merge_field . ', ';
            }

            if ($merge_fields != '') {
                echo '<div id="sms_merge_fields_' . $trigger_name . '" style="display:none;" class="mbot10">';
                echo substr($merge_fields, 0, -2);
                echo '<hr class="hr-10" />';
                echo '</div>';
            }
            echo '<hr class="hr-10" />';
        }
                ?>
            </div>
        </div>
    </div>
    <?php } ?>
    </div>
</div>
