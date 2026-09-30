<img src="../assets/logo.svg" alt="drawing" width="50"/>

# CHIP for OpenCart 4.0.x

This module adds CHIP payment method option to your OpenCart 4.0.x.

## Installation

* [Download zip file of OpenCart plugin](https://github.com/CHIPAsia/chip-for-opencart/releases/latest/download/chip.ocmod.zip)
* Upload to the Extension Installer and Install
* Navigate to : **Extensions** -> **Payments**
* Click **Install**, for CHIP Payment Gateway.

Keep the file named `chip.ocmod.zip`. OpenCart 4.x derives the extension code from
the filename, and this module's routes are resolved under `extension/chip/...`;
renaming the zip installs the files where no route can reach them.

**This build is for OpenCart 4.0.x only.** For OpenCart 4.1.0.x install
[`chip-for-opencart-4.1`](https://github.com/CHIPAsia/chip-for-opencart-4.1) instead.

## Configuration

Set the **Brand ID** and **Secret Key** in the plugins settings.

### Recurring payments

OpenCart 4.0.x never renews a subscription on its own. No 4.0.x release lets
OpenCart's scheduler call the payment extension back:

* **4.0.0.0 - 4.0.1.1** ship no `cron/subscription.php` at all.
* **4.0.2.0 - 4.0.2.3** ship one, but the call into the payment extension is
  commented out (line 319 in 4.0.2.0 / 383 in 4.0.2.3).

This build therefore carries its own cron endpoint. Add it to your server's cron:

```
* * * * * curl -s "https://your-store.example/index.php?route=extension/chip/cron/chip&token=<cron_token>" >/dev/null
```

The token is generated for you and shown on the gateway settings page when you open
it. Without this cron entry a subscription will be created and paid for, but never
renewed.

A failed renewal is retried after 1, 3 and 5 days, measured from the original due
date. After the fourth failed attempt the subscription is **suspended**; the stored
card is retained, so a later payment reactivates the subscription with a fresh
schedule. A card the gateway rejects as no longer valid suspends the subscription
immediately rather than retrying it.

### Important Requirement

**Session SameSite Cookie Setting**: For the CHIP payment integration to work properly, merchants need to set the session samesite cookie to **Lax** instead of **Strict**.

To configure this setting:
1. Navigate to **OpenCart Admin >> System >> Settings >> Stores >> Server**
2. Set the **Session SameSite Cookie** to **Lax**

This setting is required for the payment gateway redirects and callbacks to function correctly.

## Other

See [CHANGELOG.md](CHANGELOG.md) for release history.

Facebook: [Merchants & DEV Community](https://www.facebook.com/groups/3210496372558088)
