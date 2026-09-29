== Changelog ==

## [1.3.0] - 2026-09-30

### Fixed
- A recovered subscription was never billed again. `rearmDate()` returned an empty schedule, so a plan that came back `active` after a recovery payment kept `date_next = 0000-00-00 00:00:00` and was never selected by the renewal cron again. The helper now receives the loaded model and computes the next date.
- The renewal cron could charge a customer twice for one billing period. The due list was read before the per-subscription lock was taken, so two overlapping runs both saw the same row as due and both billed it. The row is re-read once the lock is held and the charge proceeds only if it is still due.
- A dead card no longer walks the whole dunning ladder. `failSubscription()` read the gateway error code from a payment-model instance that had already been replaced, so the check for `invalid_recurring_token` was unreachable on 2.2. The charging instance is now threaded through the failure path. Applies to 1.5 - 2.3.
- The webhook public key was never normalised on 2.2 and below. Saving it replaced a newline with a newline, so the backslash-n sequences the gateway returns were stored verbatim and every callback signature check failed silently.
- The cron and callback guards emitted a malformed status line on 2.2 and below (`HTTP/1.1/1.1 ...`), which PHP discards.

### Added
- Renewal cron token endpoint on 4.0: `index.php?route=extension/chip/cron/chip&token=<cron_token>`. OpenCart 4.0.x core never calls a payment extension's cron controller, so without this a 4.0.x subscription never renews. The token is generated when the settings page is first opened and compared with `hash_equals()`; a missing or wrong token is refused before any charging code runs.

### Changed
- On 4.0, direct HTTP calls to `extension/chip/cron/chip` are now refused. Renewals run through the token endpoint (or core's internal call).
- The 4.0 settings page shows the tokenised renewal URL instead of core's `cron/cron`, which does nothing on 4.0.x.

## [1.2.0] - 2026-09-29

### Added
- Recurring product / subscription support for OpenCart 1.5, 2.0, 2.2, 2.3, 3.0 and 4.0, matching the feature set already shipped for 4.1. Subscriptions are tracked in the module's own `chip_subscription` table and renewed by a scheduled job.
- Dunning: a failed renewal is retried after 1, 3 and 5 days, measured from the original due date, and the subscription is suspended after the fourth attempt. A suspended subscription keeps the stored card so it can be recovered.
- Claim-before-charge ordering, so a cron that runs twice in one window cannot charge a customer twice.
- Cron endpoint `index.php?route=extension/payment/chip/cron&token=<cron_token>`. The token is generated at install and compared with `hash_equals()`; a missing, wrong or unconfigured token is refused before any charging code runs.
- Trial-aware billing: while trial cycles remain the charge is the trial price and the cycle steps by the trial schedule.
- `refunded_order_status_id` setting on 4.0 (it already existed on 4.1).
- Stored-card account controller on 4.0 and below.

### Changed
- A dead or revoked card suspends the subscription at once instead of spending the whole retry ladder on a charge that can never succeed. The gateway documents `invalid_recurring_token` as "do not retry, re-prompt the buyer for a new card".
- A suspended subscription can be recovered. Previously the reactivation path only restored `status`, leaving `date_next` at zero, so the subscription read as active on every screen and was never billed again.
- A charge still settling at the acquirer (`pending_charge`) no longer counts as a failure. It is neither retried nor suspended, and the cycle is not consumed.
- Removed the `send_receipt` parameter and the refund webhook, and made the order-history receipt link plain text, matching 4.1.
- Removed dead card-type code that no template rendered.

### Fixed
- The renewal retry ladder compounded its offsets, landing on D+1, D+4 and D+9 instead of D+1, D+3 and D+5.
- A successful recovery permanently shifted the next billing date.
- The failure path re-loaded the payment model, and OpenCart's `Loader::model()` always constructs a new instance — so the gateway error code recorded by the charge was discarded before it was read, making the dead-card check unreachable.
- Undefined whitelist label on the 2.0, 2.2 and 2.3 settings page.
- Fatal error calling `ModelCheckoutOrder::update()` on OpenCart 2.0, which replaced it with `addOrderHistory()`.

## [1.1.0] - 2026-08-24

### Changed
- Clarified the OpenCart 4.0 compatibility range in the README.

## [1.0.0]

### Added
- Initial release: CHIP payment gateway for OpenCart 1.5 through 4.0.
