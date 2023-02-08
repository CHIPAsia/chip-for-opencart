<?php

$_['heading_title'] = 'CHIP  - Better Payment & Business Solutions';

$_['text_title'] = 'Online Banking / Credit Card / Debit Card (CHIP)';
$_['text_edit'] = 'Edit CHIP';
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
$_['tab_checkout'] = 'Customize checkout';
$_['tab_troubleshooting'] = 'Troubleshooting';

$_['entry_payment_name'] = 'Payment Name';
$_['entry_secret_key'] = 'Secret Key';
$_['entry_brand_id'] = 'Brand ID';
$_['entry_webhook_url'] = 'Webhook URL';
$_['entry_public_key'] = 'Public Key';
$_['entry_general_public_key'] = 'General Public Key';
$_['entry_purchase_send_receipt'] = 'Purchase Send Receipt:';
$_['entry_due_strict'] = 'Purchase Due Strict';
$_['entry_due_strict_timing'] = 'Purchase Due Strict Timing';
$_['entry_total'] = 'Total';
$_['entry_geo_zone'] = 'Geo Zone:';
$_['entry_status'] = 'Status';
$_['entry_sort_order'] = 'Sort Order';
$_['entry_paid_order_status'] = 'Paid Order Status';
$_['entry_refunded_order_status'] = 'Refunded Order Status';
$_['entry_allow_instruction'] = 'Instruction';
$_['entry_instruction'] = 'CHIP Instructions';
$_['entry_time_zone'] = 'Time Zone';
$_['entry_debug'] = 'Debug logging';
$_['entry_convert_to_processing'] = 'Convert To Processing';
$_['entry_disable_success_redirect'] = 'Disable Success Redirect';
$_['entry_disable_success_callback'] = 'Disable Success Callback';

$_['help_payment_name'] = 'Name that will be displayed on checkout page';
$_['help_secret_key'] = 'Secret Key can be retrieved from CHIP Collect Dashboard';
$_['help_brand_id'] = 'Secret Key can be retrieved from CHIP Collect Dashboard';
$_['help_webhook_url'] = 'Optional: This can be set in the CHIP Collect Dashboard';
$_['help_public_key'] = 'Public Key can be retrieved from CHIP Collect Dashboard';
$_['help_general_public_key'] = 'This public key will be filled automatically when the secret key is set correctly. It requires no configuration';
$_['help_due_strict'] = 'This will prevent payment after passing due strict timing. Recommended to set to Yes';
$_['help_due_strict_timing'] = 'Set due strict timing in minutes. Setting 60 will make the payment timeout in 1 hour. If leave blank, default to 60 minutes';
$_['help_total'] = 'The checkout total the order must reach before this payment method becomes active';
$_['help_status'] = 'Whether to enable/disable CHIP payment gateway';
$_['help_sort_order'] = 'Payment gateway sorting on checkout page';
$_['help_paid_order_status'] = 'Default to %s';
$_['help_refunded_order_status'] = 'Default to Refunded';
$_['help_allow_instruction'] = 'To insturction on checkout page';
$_['help_instruction'] = 'This will show on checkout page before customer make payment';
$_['help_time_zone'] = 'This will dictate the timestamp zone on the invoice and receipt';
$_['help_convert_to_processing'] = 'This to allow payment if the store currency is set to other than MYR';
$_['help_disable_success_redirect'] = 'Default to No. Only tick yes if you are performing a test';
$_['help_disable_success_callback'] = 'Default to No. Only tick yes if you are performing a test';


$_['error_permission'] = 'Warning: You do not have permission to modify CHIP!';
$_['error_secret_key'] = 'Error! You are required to set CHIP Secret Key';
$_['error_secret_key_invalid'] = 'Error! CHIP Secret Key is not valid';
$_['error_brand_id'] = 'Error! You are required to set Brand ID';
$_['error_public_key'] = 'Error! The public key is not valid';
$_['error_due_strict_timing'] = 'Error! You are required to set Due Strict Timing';
$_['error_payment_name'] = 'Error! You are required to set Payment Name';
$_['error_instruction'] = 'Error! You are required to set CHIP Instructions';