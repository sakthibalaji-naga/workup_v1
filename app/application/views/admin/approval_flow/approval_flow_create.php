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
                                <h4><?php echo _l('new_approval_flow'); ?></h4>
                                <hr />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <?php $this->load->view('admin/approval_flow/approval_flow'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function waitForjQuery(callback) {
    if (window.jQuery && typeof window.jQuery === 'function') {
        callback(window.jQuery);
        return;
    }

    setTimeout(function() {
        waitForjQuery(callback);
    }, 50);
})(function($) {
    $(function() {
        $('#approval_flow_modal').modal('show');
    });
});
</script>
<?php init_tail(); ?>
</body>
</html>
