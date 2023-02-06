<?php echo $header; ?>
<div id="content">

    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <?php if (!empty($error)) { ?>
        <?php foreach ($error as $key => $value) { ?>
            <div class="warning"><?php echo $value; ?></div>
        <?php } ?>
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
                <a href="#tab-troubleshooting"><?php echo $tab_troubleshooting; ?></a>
            </div>

            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">

                <div id="tab-api-details">
                    <table class="form">
                        <?php foreach ($languages as $language) { ?>
                        <tr>
                            <td><span class="required">*</span> <?php echo $entry_payment_name; ?></td>
                            <td>
                                <input size="50" name="chip_payment_name_<?php echo $language['language_id']; ?>" value="<?php echo isset(${'chip_payment_name_' . $language['language_id']}) ? ${'chip_payment_name_' . $language['language_id']} : $text_title; ?>">
                                <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" style="vertical-align: top;" /><br />
                                <?php if (isset(${'error_payment_name_' . $language['language_id']})) { ?>
                                <span class="error"><?php echo ${'error_payment_name_' . $language['language_id']}; ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td><span class="required">*</span> <?php echo $entry_secret_key; ?></td>
                            <td><input size="50" type="text" name="chip_secret_key" value="<?php echo $chip_secret_key; ?>"/>
                                <?php if (isset($error['secret_key'])) { ?>
                                    <span class="error"><?php echo $error['secret_key']; ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><span class="required">*</span> <?php echo $entry_brand_id; ?></td>
                            <td><input size="50" type="text" name="chip_brand_id" value="<?php echo $chip_brand_id; ?>"/>
                                <?php if (isset($error['brand_id'])) { ?>
                                    <span class="error"><?php echo $error['brand_id']; ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_webhook_url; ?></td>
                            <td><b><?php echo $webhook; ?></b></td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_public_key; ?></td>
                            <td><textarea cols="50" rows="10" name="chip_public_key"><?php echo $chip_public_key; ?></textarea>
                                <?php if (isset($error['public_key'])) { ?>
                                    <span class="error"><?php echo $error['public_key']; ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                        <td><?php echo $entry_general_public_key; ?></td>
                        <td><textarea cols="50" rows="10" name="chip_general_public_key" readonly><?php echo $chip_general_public_key; ?></textarea>
                        </td>
                    </tr>
                    </table>
                </div>

                <div id="tab-general">
                    <table class="form">
                        <tr>
                            <td><?php echo $entry_convert_to_processing; ?></td>
                            <td>
                                <?php if ($chip_convert_to_processing) { ?>
                                    <input type="radio" name="chip_convert_to_processing" value="1" checked="checked"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_convert_to_processing" value="0"/>
                                    <?php echo $text_no; ?>
                                <?php } else { ?>
                                    <input type="radio" name="chip_convert_to_processing" value="1"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_convert_to_processing" value="0" checked="checked"/>
                                    <?php echo $text_no; ?>
                                <?php } ?>
                            </td>
                        </tr>
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
                            <td><span class="required">*</span> <?php echo $entry_due_strict_timing; ?></td>
                            <td>
                            <?php if ($chip_due_strict_timing) { ?>
                                <input type="number" name="chip_due_strict_timing" value="<?php echo $chip_due_strict_timing; ?>"/>
                            <?php } else { ?>
                                <input type="number" name="chip_due_strict_timing" value="60"/>
                            <?php } ?>
                            <?php if (isset($error['due_strict_timing'])) { ?>
                                <span class="error"><?php echo $error['due_strict_timing']; ?></span>
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
                            <td><?php echo $entry_time_zone; ?></td>
                            <td>
                                <select name="chip_time_zone">
                                    <?php foreach ($time_zones as $time_zone) { ?>
                                    <?php if ($time_zone == $chip_time_zone) { ?>
                                    <option value="<?php echo $time_zone; ?>" selected="selected"><?php echo $time_zone; ?></option>
                                    <?php } else { ?>
                                    <option value="<?php echo $time_zone; ?>"><?php echo $time_zone; ?></option>
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
                            <td><?php echo sprintf($entry_paid_order_status, $config_complete_status_name); ?></td>
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
                            <td><?php echo $entry_allow_instruction; ?></td>
                            <td>
                                <?php if ($chip_allow_instruction) { ?>
                                    <input type="radio" name="chip_allow_instruction" value="1" checked="checked"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_allow_instruction" value="0"/>
                                    <?php echo $text_no; ?>
                                <?php } else { ?>
                                    <input type="radio" name="chip_allow_instruction" value="1"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_allow_instruction" value="0" checked="checked"/>
                                    <?php echo $text_no; ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php foreach ($languages as $language) { ?>
                        <tr>
                          <td><?php echo $entry_instruction; ?></td>
                          <td><textarea name="chip_instruction_<?php echo $language['language_id']; ?>" cols="80" rows="10"><?php echo isset(${'chip_instruction_' . $language['language_id']}) ? ${'chip_instruction_' . $language['language_id']} : ''; ?></textarea>
                            <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" style="vertical-align: top;" /><br />
                            <?php if (isset(${'error_instruction_' . $language['language_id']})) { ?>
                            <span class="error"><?php echo ${'error_instruction_' . $language['language_id']}; ?></span>
                            <?php } ?></td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>

                <div id="tab-troubleshooting">
                    <table class="form">
                        <tr>
                            <td><?php echo $entry_debug; ?></td>
                            <td>
                                <?php if ($chip_debug) { ?>
                                    <input type="radio" name="chip_debug" value="1" checked="checked"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_debug" value="0"/>
                                    <?php echo $text_no; ?>
                                <?php } else { ?>
                                    <input type="radio" name="chip_debug" value="1"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_debug" value="0" checked="checked"/>
                                    <?php echo $text_no; ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_disable_success_redirect; ?></td>
                            <td>
                                <?php if ($chip_disable_success_redirect) { ?>
                                    <input type="radio" name="chip_disable_success_redirect" value="1" checked="checked"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_disable_success_redirect" value="0"/>
                                    <?php echo $text_no; ?>
                                <?php } else { ?>
                                    <input type="radio" name="chip_disable_success_redirect" value="1"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_disable_success_redirect" value="0" checked="checked"/>
                                    <?php echo $text_no; ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_disable_success_callback; ?></td>
                            <td>
                                <?php if ($chip_disable_success_callback) { ?>
                                    <input type="radio" name="chip_disable_success_callback" value="1" checked="checked"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_disable_success_callback" value="0"/>
                                    <?php echo $text_no; ?>
                                <?php } else { ?>
                                    <input type="radio" name="chip_disable_success_callback" value="1"/>
                                    <?php echo $text_yes; ?>
                                    <input type="radio" name="chip_disable_success_callback" value="0" checked="checked"/>
                                    <?php echo $text_no; ?>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript"><!--
    $('#htabs a').tabs();
//--></script>
<?php echo $footer; ?>