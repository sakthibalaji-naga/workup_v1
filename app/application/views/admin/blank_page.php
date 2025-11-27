<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body text-center">
                  <div class="error-icon mbot20">
                     <i class="fa fa-exclamation-triangle fa-4x text-warning"></i>
                  </div>
                  <h1 class="error-title"><?php echo _l('access_denied'); ?></h1>
                  <p class="error-description"><?php echo _l('something_went_wrong'); ?><br><?php echo _l('please_contact_administrator'); ?></p>
                  <div class="error-actions">
                     <a href="<?php echo admin_url('dashboard'); ?>" class="btn btn-primary"><?php echo _l('go_back_to_dashboard'); ?></a>
                     <button class="btn btn-default" onclick="window.history.back();"><?php echo _l('try_again'); ?></button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<style>
   .panel_s { border: 1px solid #e3e6ea; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
   .panel-body { padding: 30px; }
   .error-icon { color: #ff9800; }
   .error-title { font-size: 28px; color: #373737; margin: 20px 0; font-weight: 400; }
   .error-description { font-size: 16px; color: #666; line-height: 1.5; }
   .error-actions { margin-top: 30px; }
   .error-actions .btn { margin: 0 10px; padding: 10px 20px; }
   @media (max-width: 768px) {
      .panel-body { padding: 20px; }
      .error-title { font-size: 24px; }
      .error-description { font-size: 14px; }
      .error-actions { display: flex; flex-direction: column; align-items: center; }
      .error-actions .btn { margin: 10px 0; }
   }
</style>
<?php init_tail(); ?>
</body>
</html>
