<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
  <div class="container-fluid">
    <div class="pull-right">
    <button type="submit" form="form-chip" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
    <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
    <h1><?php echo $heading_title; ?></h1>
    <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
    </ul>
  </div>
  </div>
  <div class="container-fluid">
  <?php if (!empty($error)) { ?>
    <?php foreach ($error as $key => $value) {
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $value; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
  <?php } ?>

  <div class="panel panel-default">
    <div class="panel-heading">
    <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
    </div>
    <div class="panel-body">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-chip" class="form-horizontal">
      <ul class="nav nav-tabs">
      <li class="active"><a href="#tab-api" data-toggle="tab"><?php echo $tab_api_details; ?></a></li>
      <li><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
      <li><a href="#tab-order-status" data-toggle="tab"><?php echo $tab_order_status; ?></a></li>
      <li><a href="#tab-checkout" data-toggle="tab"><?php echo $tab_checkout; ?></a></li>
      <li><a href="#tab-troubleshoot" data-toggle="tab"><?php echo $tab_troubleshooting; ?></a></li>
      </ul>
      <div class="tab-content">
      <div class="tab-pane active" id="tab-api">
        <?php foreach ($languages as $language) { ?>
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-payment-name-<?php echo $language['language_id']; ?>"><span data-toggle="tooltip" title="<?php echo $help_payment_name; ?>"><?php echo $entry_payment_name; ?></span></label>
            <div class="col-sm-10">
              <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                <input type="text" name="chip_payment_name_<?php echo $language['language_id']; ?>" placeholder="<?php echo $entry_payment_name; ?>" id="input-payment-name-<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset(${'chip_payment_name_' . $language['language_id']}) ? ${'chip_payment_name_' . $language['language_id']} : $text_title; ?>
              </div>
              <?php if (${'error_payment_name_' . $language['language_id']}) { ?>
              <div class="text-danger"><?php echo ${'error_payment_name_' . $language['language_id']}; ?></div>
              <?php } ?>
            </div>
          </div>
        <?php } ?>
        <div class="form-group required">
        <label class="col-sm-2 control-label" for="input-secret-key"><span data-toggle="tooltip" title="<?php echo $help_secret_key; ?>"><?php echo $entry_secret_key; ?></span></label>
        <div class="col-sm-10">
          <input type="text" name="chip_secret_key" value="<?php echo $chip_secret_key; ?>" placeholder="<?php echo $entry_secret_key; ?>" id="input-secret-key" class="form-control" />
          <?php if ($error_secret_key) { ?>
            <div class="text-danger"><?php echo $error_secret_key; ?></div>
          <?php } ?>
        </div>
        </div>
        <div class="form-group required">
        <label class="col-sm-2 control-label" for="input-brand-id"><span data-toggle="tooltip" title="<?php echo $help_brand_id; ?>"><?php echo $entry_brand_id; ?></span></label>
        <div class="col-sm-10">
          <input type="text" name="chip_brand_id" value="<?php echo $chip_brand_id; ?>" placeholder="<?php echo $entry_brand_id; ?>" id="input-brand-id" class="form-control" />
          <?php if ($error_brand_id) { ?>
            <div class="text-danger"><?php echo $error_brand_id; ?></div>
          <?php } ?>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $help_webhook_url; ?>"><?php echo $entry_webhook_url; ?></span></label>
        <div class="col-sm-10">
          <div class="input-group"> <span class="input-group-addon"><i class="fa fa-link"></i></span>
          <input type="text" value="<?php echo $webhook; ?>" class="form-control" />
          </div>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $help_public_key; ?>"><?php echo $entry_public_key; ?></span></label>
        <div class="col-sm-10">
          <textarea name="chip_public_key" cols="80" rows="10" placeholder="<?php echo $entry_public_key; ?>" id="input-public-key" class="form-control"><?php echo $chip_public_key; ?></textarea>
          <?php if ($error_public_key) { ?>
            <div class="text-danger"><?php echo $error_public_key; ?></div>
          <?php } ?>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $help_general_public_key; ?>"><?php echo $entry_general_public_key; ?></span></label>
        <div class="col-sm-10">
          <textarea name="chip_general_public_key" cols="80" rows="10" placeholder="<?php echo $entry_general_public_key; ?>" id="input-general-public-key" class="form-control" readonly><?php echo $chip_general_public_key; ?></textarea>
        </div>
        </div>
      </div>
      <div class="tab-pane" id="tab-general">
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-convert-to-processing"><span data-toggle="tooltip" title="<?php echo $help_convert_to_processing; ?>"><?php echo $entry_convert_to_processing; ?></span></label>
        <div class="col-sm-10">
          <select name="chip_convert_to_processing" id="input-convert-to-processing" class="form-control">
          <?php if ($chip_convert_to_processing) { ?>
            <option value="1" selected="selected"><?php echo $text_yes; ?></option>
            <option value="0"><?php echo $text_no; ?></option>
          <?php } else { ?>
            <option value="1"><?php echo $text_yes; ?></option>
            <option value="0" selected="selected"><?php echo $text_no; ?></option>
          <?php } ?>
          </select>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-purchase-send-receipt"><?php echo $entry_purchase_send_receipt; ?></label>
        <div class="col-sm-10">
          <select name="chip_purchase_send_receipt" id="input-purchase-send-receipt" class="form-control">
          <?php if ($chip_purchase_send_receipt) { ?>
            <option value="1" selected="selected"><?php echo $text_yes; ?></option>
            <option value="0"><?php echo $text_no; ?></option>
          <?php } else { ?>
            <option value="1"><?php echo $text_yes; ?></option>
            <option value="0" selected="selected"><?php echo $text_no; ?></option>
          <?php } ?>
          </select>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-due-strict"><span data-toggle="tooltip" title="<?php echo $help_due_strict; ?>"><?php echo $entry_due_strict; ?></span></label>
        <div class="col-sm-10">
          <select name="chip_due_strict" id="input-due-strict" class="form-control">
          <?php if ($chip_due_strict) { ?>
            <option value="1" selected="selected"><?php echo $text_yes; ?></option>
            <option value="0"><?php echo $text_no; ?></option>
          <?php } else { ?>
            <option value="1"><?php echo $text_yes; ?></option>
            <option value="0" selected="selected"><?php echo $text_no; ?></option>
          <?php } ?>
          </select>
        </div>
        </div>
        <div class="form-group required">
        <label class="col-sm-2 control-label" for="input-due-strict-timing"><span data-toggle="tooltip" title="<?php echo $help_due_strict_timing; ?>"><?php echo $entry_due_strict_timing; ?></span></label>
        <div class="col-sm-10">
          <input type="number" name="chip_due_strict_timing" value="<?php echo $chip_due_strict_timing; ?>" placeholder="<?php echo $entry_due_strict_timing; ?>" id="input-due-strict-timing" class="form-control" />
          <?php if ($error_due_strict_timing) { ?>
            <div class="text-danger"><?php echo $error_due_strict_timing; ?></div>
          <?php } ?>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-total"><span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span></label>
        <div class="col-sm-10">
          <input type="text" name="chip_total" value="<?php echo $chip_total; ?>" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control" />
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-geo-zone"><?php echo $entry_geo_zone; ?></label>
        <div class="col-sm-10">
          <select name="chip_geo_zone_id" id="input-geo-zone" class="form-control">
          <option value="0"><?php echo $text_all_zones; ?></option>
          <?php foreach ($geo_zones as $geo_zone) { ?>
            <?php if ($geo_zone['geo_zone_id'] == $chip_geo_zone_id) { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
            <?php } else { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
            <?php } ?>
          <?php } ?>
          </select>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-time-zone"><span data-toggle="tooltip" title="<?php echo $help_time_zone; ?>"><?php echo $entry_time_zone; ?></span></label>
        <div class="col-sm-10">
          <select name="chip_time_zone" id="input-time-zone" class="form-control">
          <?php foreach ($time_zones as $time_zone) { ?>
            <?php if ($time_zone == $chip_time_zone) { ?>
              <option value="<?php echo $time_zone; ?>" selected="selected"><?php echo $time_zone; ?></option>
            <?php } else { ?>
              <option value="<?php echo $time_zone; ?>"><?php echo $time_zone; ?></option>
            <?php } ?>
          <?php } ?>
          </select>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-status"><span data-toggle="tooltip" title="<?php echo $help_status; ?>"><?php echo $entry_status; ?></span></label>
        <div class="col-sm-10">
          <select name="chip_status" id="input-status" class="form-control">
          <?php if ($chip_status) { ?>
            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
            <option value="0"><?php echo $text_disabled; ?></option>
          <?php } else { ?>
            <option value="1"><?php echo $text_enabled; ?></option>
            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
          <?php } ?>
          </select>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-sort-order"><span data-toggle="tooltip" title="<?php echo $help_sort_order; ?>"><?php echo $entry_sort_order; ?></span></label>
        <div class="col-sm-10">
          <input type="text" name="chip_sort_order" value="<?php echo $chip_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
        </div>
        </div>
      </div>
      <div class="tab-pane" id="tab-order-status">
        <div class="form-group">
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $help_paid_order_status; ?>"><?php echo $entry_paid_order_status; ?></span></label>
        <div class="col-sm-10">
          <select name="chip_paid_status_id" class="form-control">
          <?php foreach ($order_statuses as $order_status) { ?>
            <?php if ($order_status['order_status_id'] == $chip_paid_status_id) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
            <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
            <?php } ?>
          <?php } ?>
          </select>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="<?php echo $help_refunded_order_status; ?>"><?php echo $entry_refunded_order_status; ?></span></label>
        <div class="col-sm-10">
          <select name="chip_refunded_order_status_id" class="form-control">
          <?php foreach ($order_statuses as $order_status) { ?>
            <?php if ($order_status['order_status_id'] == $chip_refunded_order_status_id) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
            <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
            <?php } ?>
          <?php } ?>
          </select>
        </div>
        </div>
      </div>
      <div class="tab-pane" id="tab-checkout">
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-allow-instruction"><?php echo $entry_allow_instruction; ?></label>
        <div class="col-sm-10">
          <select name="chip_allow_instruction" id="input-allow-instruction" class="form-control">
          <?php if ($chip_allow_instruction) { ?>
            <option value="1" selected="selected"><?php echo $text_yes; ?></option>
            <option value="0"><?php echo $text_no; ?></option>
          <?php } else { ?>
            <option value="1"><?php echo $text_yes; ?></option>
            <option value="0" selected="selected"><?php echo $text_no; ?></option>
          <?php } ?>
          </select>
        </div>
        </div>
        <?php foreach ($languages as $language) { ?>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-instruction-<?php echo $language['language_id']; ?>"><span data-toggle="tooltip" title="<?php echo $help_instruction; ?>"><?php echo $entry_instruction; ?></span></label>
            <div class="col-sm-10">
              <div class="input-group"><span class="input-group-addon"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /></span>
                <input type="text" name="chip_instruction_<?php echo $language['language_id']; ?>" placeholder="<?php echo $entry_instruction; ?>" id="input-instruction-<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset(${'chip_instruction_' . $language['language_id']}) ? ${'chip_instruction_' . $language['language_id']} : $text_title; ?>
              </div>
              <?php if (${'error_instruction_' . $language['language_id']}) { ?>
              <div class="text-danger"><?php echo ${'error_instruction_' . $language['language_id']}; ?></div>
              <?php } ?>
            </div>
          </div>
        <?php } ?>
      </div>
      <div class="tab-pane" id="tab-troubleshooting">
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-debug"><?php echo $entry_debug; ?></label>
        <div class="col-sm-10">
          <select name="chip_debug" id="input-debug" class="form-control">
          <?php if ($chip_debug) { ?>
            <option value="1" selected="selected"><?php echo $text_yes; ?></option>
            <option value="0"><?php echo $text_no; ?></option>
          <?php } else { ?>
            <option value="1"><?php echo $text_yes; ?></option>
            <option value="0" selected="selected"><?php echo $text_no; ?></option>
          <?php } ?>
          </select>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-disable-success-redirect"><span data-toggle="tooltip" title="<?php echo $help_disable_success_redirect; ?>"><?php echo $entry_disable_success_redirect; ?></span></label>
        <div class="col-sm-10">
          <select name="chip_disable_success_redirect" id="input-disable-success-redirect" class="form-control">
          <?php if ($chip_disable_success_redirect) { ?>
            <option value="1" selected="selected"><?php echo $text_yes; ?></option>
            <option value="0"><?php echo $text_no; ?></option>
          <?php } else { ?>
            <option value="1"><?php echo $text_yes; ?></option>
            <option value="0" selected="selected"><?php echo $text_no; ?></option>
          <?php } ?>
          </select>
        </div>
        </div>
        <div class="form-group">
        <label class="col-sm-2 control-label" for="input-disable-success-callback"><span data-toggle="tooltip" title="<?php echo $help_disable_success_callback; ?>"><?php echo $entry_disable_success_callback; ?></span></label>
        <div class="col-sm-10">
          <select name="chip_disable_success_callback" id="input-disable-success-callback" class="form-control">
          <?php if ($chip_disable_success_callback) { ?>
            <option value="1" selected="selected"><?php echo $text_yes; ?></option>
            <option value="0"><?php echo $text_no; ?></option>
          <?php } else { ?>
            <option value="1"><?php echo $text_yes; ?></option>
            <option value="0" selected="selected"><?php echo $text_no; ?></option>
          <?php } ?>
          </select>
        </div>
        </div>
      </div>
      </div>
    </form>
    </div>
  </div>
  </div>
</div>
<?php echo $footer; ?>