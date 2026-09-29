<img src="./assets/logo.svg" alt="drawing" width="50"/>

# CHIP for OpenCart

This module adds CHIP payment method option to your OpenCart.

## Installation

The installation files differ by OpenCart version. Choose your OpenCart version:

* [OpenCart 1.5.x](https://github.com/CHIPAsia/chip-for-opencart/releases/latest/download/chip-opencart-1.5.zip)
* [OpenCart 2.0.x & 2.1.x](https://github.com/CHIPAsia/chip-for-opencart/releases/latest/download/chip-opencart-2.0.ocmod.zip)
* [OpenCart 2.2.x](https://github.com/CHIPAsia/chip-for-opencart/releases/latest/download/chip-opencart-2.2.ocmod.zip)
* [OpenCart 2.3.x](https://github.com/CHIPAsia/chip-for-opencart/releases/latest/download/chip-opencart-2.3.ocmod.zip)
* [OpenCart 3.0.x](https://github.com/CHIPAsia/chip-for-opencart/releases/latest/download/chip-opencart-3.0.ocmod.zip)
* [OpenCart 4.0.x](https://github.com/CHIPAsia/chip-for-opencart/releases/latest/download/chip.ocmod.zip)
* OpenCart 4.1.x — use [`chip-for-opencart-4.1`](https://github.com/CHIPAsia/chip-for-opencart-4.1)

Upload the zip through **Extensions → Installer**, then enable the gateway under
**Extensions → Payments**.

### Keep the `.ocmod.zip` extension

OpenCart's extension installer rejects any upload whose filename does not end in
`.ocmod.zip` (or `.ocmod.xml`). Uploading a renamed `...zip` fails with a generic
upload error rather than installing.

For **OpenCart 4.0.x** the filename matters beyond the extension: OpenCart 4.x
derives the extension *code* from the filename, and this module's routes resolve
under `extension/chip/...`. The 4.0 build is therefore published as
`chip.ocmod.zip` — do not rename it on download. Earlier versions published it as
`chip-opencart-4.0.zip` and asked merchants to rename it by hand; that is no longer
necessary.

On **2.0.x – 3.0.x** the filename is only a label — OpenCart derives the code from
the installed controller — so `chip-opencart-<version>.ocmod.zip` is fine as-is.

**OpenCart 1.5.x** has no extension installer, so its zip is a plain
`upload/` tree to copy into your store by hand.

## Compatibility

Payments work on every version listed above. OpenCart 4.x is served by two
repositories, and this repository keeps its own `4.0` build for stores that
already run it:

| OpenCart version | Repository | Subscription renewals |
| --- | --- | --- |
| **4.1.0.x** | [`chip-for-opencart-4.1`](https://github.com/CHIPAsia/chip-for-opencart-4.1) | ✅ |
| **4.0.2.x** | this repository (`4.0`) or `chip-for-opencart-4.1` | ❌ |
| **4.0.0.0 – 4.0.1.1** | this repository (`4.0`) or `chip-for-opencart-4.1` | ❌ |
| **3.0.x and below** | this repository | ✅ |

`chip-for-opencart-4.1` implements both OpenCart 4.x payment entry points
(`getMethod()` and `getMethods()`), so it now serves the whole 4.0.x – 4.1.x line
and is the recommended build for all of 4.x.

**Subscription renewals need OpenCart 4.1.0.0 or later**, even though payments work
everywhere: renewals are scheduled by OpenCart's own `cron/subscription.php`, which
calls the payment extension back, and only 4.1.0.0 and later do that. On 4.0.x a
customer can pay for a subscription product, but nothing will renew it.

### Recurring payments

How renewals are driven depends on your OpenCart version:

* **OpenCart 3.0.x and below** — the module exposes its own endpoint. Add this to
  your server's cron (the token is generated for you and shown in the gateway
  settings):

  ```
  * * * * * curl -s "https://your-store.example/index.php?route=extension/payment/chip/cron&token=<cron_token>" >/dev/null
  ```

  OpenCart 1.5 and 2.0 – 2.3 use `route=payment/chip/cron` instead.

* **OpenCart 4.0.x and 4.1.x** — renewals are driven by OpenCart's own scheduler:

  ```
  php /path/to/opencart/cron.php
  ```

  This requires OpenCart **4.1.0.0 or later**; earlier 4.x releases never call a
  payment extension's cron controller, so nothing renews a subscription on 4.0.x.

A failed renewal is retried after 1, 3 and 5 days, measured from the original due
date. After the fourth failed attempt the subscription is **suspended**; the stored
card is retained, so a later payment reactivates the subscription with a fresh
schedule. A card the gateway rejects as no longer valid suspends the subscription
immediately rather than retrying it.

## Configuration

Set the **Brand ID** and **Secret Key** in the plugins settings.

### Important Requirement

**Session SameSite Cookie Setting**: For the CHIP payment integration to work properly, merchants need to set the session samesite cookie to **Lax** instead of **Strict**.

To configure this setting:
1. Navigate to **OpenCart Admin >> System >> Settings >> Stores >> Server**
2. Set the **Session SameSite Cookie** to **Lax**

This setting is required for the payment gateway redirects and callbacks to function correctly.

## Other

See [CHANGELOG.md](CHANGELOG.md) for release history.

Facebook: [Merchants & DEV Community](https://www.facebook.com/groups/3210496372558088)
