<?php echo $header; ?>
<div id="content">

    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <?php if (isset($error['error_warning'])) { ?>
        <div class="warning"><?php echo $error['error_warning']; ?></div>
    <?php } ?>

    <div class="box">
        <div class="heading">
            <h1><img src="view/image/payment/chip.png" style="height:20px;" alt=""/> <?php echo $heading_title; ?></h1>

            <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
        </div>
        <div class="content">

            <div id="htabs" class="htabs">
                <a href="#tab-api-details"><?php echo $tab_api_details; ?></a>
                <a href="#tab-general"><?php echo $tab_general; ?></a>
                <a href="#tab-status"><?php echo $tab_order_status; ?></a>
                <a href="#tab-customise"><?php echo $tab_customise; ?></a>
            </div>

            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">

                <div id="tab-api-details">
                    <table class="form">
                        <tr>
                            <td><span class="required">*</span> <?php echo $entry_secret_key; ?></td>
                            <td><input type="text" name="chip_secret_key" value="<?php echo $chip_secret_key; ?>"/>
                                <?php if (isset($error['secret_key'])) { ?>
                                    <span class="error"><?php echo $error['secret_key']; ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="required">*</span> <?php echo $entry_brand_id; ?></td>
                            <td><input type="text" name="chip_brand_id" value="<?php echo $chip_brand_id; ?>"/>
                                <?php if (isset($error['brand_id'])) { ?>
                                    <span class="error"><?php echo $error['brand_id']; ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="tab-general">
                    <table class="form">
                        <tr>
                            <td><?php echo $entry_purchase_send_receipt; ?></td>
                            <td>
                                <?php if ($chip_purchase_send_receipt) { ?>
                                    <input type="radio" name="chip_purchase_send_receipt" value="1" checked="checked"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_purchase_send_receipt" value="0"/>
                                    <?php echo $text_no; ?>
                                <?php } else { ?>
                                    <input type="radio" name="chip_purchase_send_receipt" value="1"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_purchase_send_receipt" value="0" checked="checked"/>
                                    <?php echo $text_no; ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_due_strict; ?></td>
                            <td>
                                <?php if ($chip_due_strict) { ?>
                                    <input type="radio" name="chip_due_strict" value="1" checked="checked"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_due_strict" value="0"/>
                                    <?php echo $text_no; ?>
                                <?php } else { ?>
                                    <input type="radio" name="chip_due_strict" value="1"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_due_strict" value="0" checked="checked"/>
                                    <?php echo $text_no; ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_total; ?></td>
                            <td><input type="text" name="chip_total" value="<?php echo $chip_total; ?>"/></td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_geo_zone; ?></td>
                            <td>
                                <select name="chip_geo_zone_id">
                                    <option value="0"><?php echo $text_all_zones; ?></option>
                                    <?php foreach ($geo_zones as $geo_zone) { ?>
                                    <?php if ($geo_zone['geo_zone_id'] == $chip_geo_zone_id) { ?>
                                    <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                    <?php } else { ?>
                                    <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_status; ?></td>
                            <td>
                                <select name="chip_status">
                                    <?php if ($chip_status) { ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_sort_order; ?></td>
                            <td><input type="text" name="chip_sort_order" value="<?php echo $chip_sort_order; ?>" size="1"/></td>
                        </tr>
                    </table>

                </div>
                <div id="tab-status">
                    <table class="form">
                        <tr>
                            <td><?php echo $entry_created_order_status; ?></td>
                            <td>
                                <select name="chip_created_order_status_id">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $chip_created_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_error_order_status; ?></td>
                            <td>
                                <select name="chip_error_order_status_id">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $chip_error_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_cancelled_order_status; ?></td>
                            <td>
                                <select name="chip_cancelled_order_status_id">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $chip_cancelled_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_overdue_order_status; ?></td>
                            <td>
                                <select name="chip_overdue_order_status_id">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $chip_overdue_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_paid_order_status; ?></td>
                            <td>
                                <select name="chip_paid_order_status_id">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $chip_paid_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_refunded_order_status; ?></td>
                            <td>
                                <select name="chip_refunded_order_status_id">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $chip_refunded_order_status_id) { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="tab-customise">
                    <table class="form">
                        <tr>
                            <td><?php echo $entry_allow_notes; ?></td>
                            <td>
                                <?php if ($chip_allow_note) { ?>
                                    <input type="radio" name="chip_allow_note" value="1" checked="checked"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_allow_note" value="0"/>
                                    <?php echo $text_no; ?>
                                <?php } else { ?>
                                    <input type="radio" name="chip_allow_note" value="1"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_allow_note" value="0" checked="checked"/>
                                    <?php echo $text_no; ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_logo; ?></td>
                            <td valign="top">
                                <div class="image"><img src="<?php echo $thumb; ?>" alt="" id="thumb" />
                                    <input type="hidden" name="chip_logo" value="<?php echo $chip_logo; ?>" id="image" />
                                    <br />
                                    <a onclick="image_upload('image', 'thumb');"><?php echo $text_browse; ?></a>
                                    &nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb').attr('src', '<?php echo $no_image; ?>'); $('#image').attr('value', '');"><?php echo $text_clear; ?></a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>

<script type="text/javascript"><!--
    $('#htabs a').tabs();
//--></script>
<script type="text/javascript"><!--
    function image_upload(field, thumb) {
        $('#dialog').remove();

        $('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="index.php?route=common/filemanager&token=<?php echo $token; ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');

        $('#dialog').dialog({
            title: '<?php echo $text_image_manager; ?>',
            close: function (event, ui) {
                if ($('#' + field).attr('value')) {
                    $.ajax({
                        url: 'index.php?route=payment/chip/imageLogo&token=<?php echo $token; ?>&image=' + encodeURIComponent($('#' + field).val()),
                        dataType: 'text',
                        success: function(data) {
                            $('#' + thumb).replaceWith('<img src="' + data + '" alt="" id="' + thumb + '" />');
                        }
                    });
                }
            },
            bgiframe: false,
            width: 800,
            height: 400,
            resizable: false,
            modal: false
        });
    };
    //--></script>
<?php echo $footer; ?> 