# Corbit SMS gateway for Moodle

Sends Moodle's text messages through a [Corbit SMS](https://corbit.sa) account:

* one-time sign-in codes of the **SMS factor** of multi-factor authentication (Moodle 4.5 and later);
* **notifications by SMS** through the SMS message output (Moodle 5.0 and later).

Corbit is an SMS provider in Saudi Arabia. Messages are sent with a sender name approved in your Corbit account.

## Requirements

* Moodle 4.5 or later (tested on Moodle 4.5 and 5.3 with MariaDB, and on 5.3 with PostgreSQL).
* A Corbit SMS account with an API token, an approved sender name and SMS balance.

## External service

This plugin is an integration with the **Corbit SMS** service (https://corbit.sa), an SMS provider in Saudi Arabia.

* **What depends on it:** all sending. Without a Corbit account the gateway sends nothing; Moodle reports the message as "gateway not available", and sign-in codes and SMS notifications that use this gateway are not delivered.
* **Getting the credentials:** sign in to your Corbit account at https://sms.corbit.sa, open Settings and copy the API token, and use one of the sender names that Corbit has approved for your account (the plugin's test page lists them).
* **Costs:** SMS are charged by Corbit according to your Corbit account; the plugin itself is free.
* **What is sent:** for each SMS, the recipient's mobile number, the message text and the sender name, with your API token. Nothing else.

## Installation

1. Install the plugin into `sms/gateway/corbit` (Site administration > Plugins > Install plugins, or unzip it there) and complete the upgrade.
2. Go to **Site administration > Plugins > SMS > Manage SMS gateways** and create a gateway: choose **Corbit SMS**, give it a name, set the country code (966 for Saudi Arabia) and enter your API token and sender name.
3. Open the **Corbit SMS test page** from the link in the gateway form (or at `/sms/gateway/corbit/test.php`). Press **Check accounts** to see, for each Corbit gateway, whether the account is connected, its SMS balance and its approved sender names (the Corbit API is called only when you press it). The page also sends a test SMS and lists the recent messages.
4. Use the gateway:
   * Sign-in codes: Site administration > Plugins > Admin tools > Multi-factor authentication > SMS mobile phone, and choose the Corbit gateway.
   * Notifications (Moodle 5.0 and later): enable the SMS message output (Site administration > Messaging > Notification settings). Users need a mobile phone number in their profile.

## Phone numbers

Numbers may contain spaces, dashes and parentheses, and Arabic-Indic or Persian digits.

* International numbers start with `+` or `00` and the country code, such as +9665XXXXXXXX or +201XXXXXXXXX.
* A local number that starts with 0, such as 05XXXXXXXX, gets the gateway's country code (Saudi Arabia when it is empty).
* On a Saudi gateway, a mobile typed without its 0 (5XXXXXXXX) and 9665XXXXXXXX without `+` are also accepted. On other gateways, a number without `+`, `00` or a leading 0 is rejected.
* Anything else is rejected without calling the API, so no country is guessed: for example a number with no `+`, `00` or leading 0, a number with a decimal point, or `+0…`. A number that starts with `+` or `00` and another country code is never changed into a Saudi number.
* Saudi landlines cannot receive SMS and are rejected.

## Message status

| Corbit answer | Moodle status |
|---|---|
| Accepted | Sent |
| Refused (token, sender name, number, balance) | Rejected |
| No answer, server error or rate limit | Failed |
| Gateway without token or sender name | Gateway not available |

The reason of a refusal is shown when you send a test SMS from the test page, and in the debugging output.

## API address

The plugin calls `https://api.corbit.sa/api/v1`. To use another address (a test server, for example), add this line to `config.php`:

```php
$CFG->forced_plugin_settings['smsgateway_corbit']['apiurl'] = 'https://example.test/api/v1';
```

The address must start with `https://`, because your API token is sent to it; any other address is ignored and the default is used (plain `http://` is allowed only for a server on the same computer, localhost or 127.0.0.1, while developer debugging is on). Redirects are not followed. Moodle's curl security settings also apply: an address on a private network or on a port other than 80 or 443 must be allowed in Site administration > General > Security > HTTP security.

## Privacy

The plugin stores no personal data. To deliver an SMS it sends the recipient's mobile number and the message text to Corbit (api.corbit.sa); this is declared through Moodle's Privacy API. Moodle's SMS subsystem keeps its own record of sent messages. The API token is stored in the gateway configuration, like the credentials of Moodle's other SMS gateways. Uninstalling the plugin deletes its gateways, including their API tokens.

## Database

The plugin has no database tables of its own and only reads Moodle's SMS records through Moodle's data API, so it works with every database Moodle supports.

## Changes

* **1.0.1:** numbers are read without guessing (a number with no country code and no leading 0 is rejected, and decimals are rejected); the API address must be HTTPS and redirects are not followed; uninstalling removes the gateways and their API tokens; the test page calls the Corbit API only when you press Check accounts; clearer error messages; Privacy API userlist support.
* **1.0.0:** first release.

## License

GNU GPL v3 or later. © 2026 Corbit Company.
