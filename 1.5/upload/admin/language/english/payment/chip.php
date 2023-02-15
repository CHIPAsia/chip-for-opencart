<?php

$_['heading_title'] = 'CHIP  - Better Payment & Business Solutions';

$_['text_title'] = 'Online Banking / Credit Card / Debit Card (CHIP)';
$_['text_success'] = 'Success: You have modified CHIP account details!';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_all_zones'] = 'All Zones';
$_['text_browse'] = 'Browse';
$_['text_clear'] = 'Clear';
$_['text_payment'] = 'Payment';
$_['text_chip'] = '<a href="https://www.chip-in.asia/" taget="_blank"><img src="view/image/payment/chip.png" alt="CHIP" title="CHIP" style="height: 14px;" /></a>';

$_['tab_general'] = 'General';
$_['tab_order_status'] = 'Order status';
$_['tab_api_details'] = 'API details';
$_['tab_customise'] = 'Customize checkout';
$_['tab_troubleshooting'] = 'Troubleshooting';

$_['behavior_missing_order'] = 'Missing Order';
$_['behavior_cancel_order'] = 'Cancel Order';
$_['behavior_fail_order'] = 'Fail Order';

$_['entry_payment_name'] = 'Payment Name:<br /><span class="help">Name that will be displayed on checkout page.</span>';
$_['entry_secret_key'] = 'Secret Key:<br /><span class="help">Secret Key can be retrieved from CHIP Collect Dashboard.</span>';
$_['entry_brand_id'] = 'Brand ID:<br /><span class="help">Secret Key can be retrieved from CHIP Collect Dashboard.</span>';
$_['entry_webhook_url'] = 'Webhook URL:<br /><span class="help">Optional: This can be set in the CHIP Collect Dashboard.</span>';
$_['entry_public_key'] = 'Public Key:<br /><span class="help">Public Key can be retrieved from CHIP Collect Dashboard.</span>';
$_['entry_general_public_key'] = 'General Public Key:<br /><span class="help">This public key will be filled automatically when the secret key is set correctly. It requires no configuration.</span>';
$_['entry_purchase_send_receipt'] = 'Purchase Send Receipt:';
$_['entry_due_strict'] = 'Purchase Due Strict:<br /><span class="help">This will prevent payment after passing due strict timing. Recommended to set to Yes.</span>';
$_['entry_due_strict_timing'] = 'Purchase Due Strict Timing:<br /><span class="help">Set due strict timing in minutes. Setting 60 will make the payment timeout in 1 hour. If leave blank, default to 60 minutes</span>';
$_['entry_total'] = 'Total:<br /><span class="help">The checkout total the order must reach before this payment method becomes active.</span>';
$_['entry_geo_zone'] = 'Geo Zone:';
$_['entry_status'] = 'Status:<br /><span class="help">Whether to enable/disable CHIP payment gateway.</span>';
$_['entry_sort_order'] = 'Sort Order:<br /><span class="help">Payment gateway sorting on checkout page.</span>';
$_['entry_canceled_order_status'] = 'Canceled Order Status:<br /><span class="help">Default to Canceled</span>';
$_['entry_failed_order_status'] = 'Failed Order Status:<br /><span class="help">Default to Failed</span>';
$_['entry_paid_order_status'] = 'Paid Order Status:<br /><span class="help">Default to %s.</span>';
$_['entry_refunded_order_status'] = 'Refunded Order Status:<br /><span class="help">Default to Refunded.</span>';
$_['entry_allow_instruction'] = 'Instruction:<br /><span class="help">To insturction on checkout page.</span>';
$_['entry_instruction'] = 'CHIP Instructions:<br /><span class="help">This will show on checkout page before customer make payment.</span>';
$_['entry_time_zone'] = 'Time Zone:<br /><span class="help">This will dictate the timestamp zone on the invoice and receipt.</span>';
$_['entry_debug'] = 'Debug logging:';
$_['entry_convert_to_processing'] = 'Convert To Processing:<br /><span class="help">This to allow payment if the store currency is set to other than MYR.</span>';
$_['entry_disable_success_redirect'] = 'Disable Success Redirect:<br /><span class="help">Default to No. Only tick yes if you are performing a test.</span>';
$_['entry_disable_success_callback'] = 'Disable Success Callback:<br /><span class="help">Default to No. Only tick yes if you are performing a test.</span>';
$_['entry_canceled_behavior'] = 'Canceled Order Behavior:<br /><span class="help">Missing Order is the default behavior. If you require the order status to be updated to canceled, change to Cancel Order</span>';
$_['entry_failed_behavior'] = 'Failed Order Behavior:<br /><span class="help">Missing Order is the default behavior. If you require the order status to be updated to failed, change to Fail Order</span>';

$_['error_permission'] = 'Warning: You do not have permission to modify CHIP!';
$_['error_secret_key'] = 'Error! You are required to set CHIP Secret Key';
$_['error_secret_key_invalid'] = 'Error! CHIP Secret Key is not valid';
$_['error_brand_id'] = 'Error! You are required to set Brand ID';
$_['error_public_key'] = 'Error! The public key is not valid';
$_['error_due_strict_timing'] = 'Error! You are required to set Due Strict Timing';
$_['error_payment_name'] = 'Error! You are required to set Payment Name';
$_['error_instruction'] = 'Error! You are required to set CHIP Instructions';