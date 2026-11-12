<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings for component smsgateway_corbit, language 'en'.
 *
 * @package    smsgateway_corbit
 * @copyright  2026 Corbit Company
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['account'] = 'Account';
$string['api_token'] = 'API token';
$string['api_token_help'] = 'The API token of your Corbit SMS account: sign in at sms.corbit.sa, open Settings, and copy the API token.';
$string['apierror'] = 'Corbit SMS: {$a}';
$string['approvedsenders'] = 'Approved sender names';
$string['badtoken'] = 'The API token contains a space or a hidden character. Copy it again from your Corbit account and paste it alone.';
$string['balance'] = 'SMS balance';
$string['checkaccounts'] = 'Check accounts';
$string['checkaccountshint'] = 'Shows the balance and the approved sender names of each Corbit gateway. The Corbit API is asked only when you press the button.';
$string['connected'] = 'Connected';
$string['connectionerror'] = 'Could not reach the Corbit API: {$a}';
$string['error401'] = 'The API token was rejected. Copy it again from your Corbit account.';
$string['errorhttp'] = 'The Corbit API answered with HTTP {$a}.';
$string['forminformation'] = 'Enter the API token of your Corbit SMS account and one of its approved sender names. For Saudi numbers, set the country code to 966. After saving, check the account and send a test SMS on the <a href="{$a}">Corbit SMS test page</a>.';
$string['gateway'] = 'Gateway';
$string['hint402'] = 'Check the SMS balance of your Corbit account.';
$string['hint422'] = 'Check the mobile number, the sender name and the message.';
$string['hint429'] = 'Too many requests: wait a minute and try again.';
$string['hint500'] = 'The Corbit service is not available right now: try again later.';
$string['invalidnumber'] = 'This is not a mobile number that can receive SMS. Use the international form, such as +9665XXXXXXXX, or a local number that starts with 0, such as 05XXXXXXXX.';
$string['messagetype'] = 'Sent by';
$string['nogateway'] = 'Choose a Corbit gateway.';
$string['nogateways'] = 'There is no Corbit gateway yet. <a href="{$a}">Create one</a> with your API token and sender name.';
$string['nomessages'] = 'No SMS has been sent through a Corbit gateway yet.';
$string['notconnected'] = 'Not connected';
$string['notoken'] = 'The gateway has no API token.';
$string['pluginname'] = 'Corbit SMS';
$string['privacy:metadata:corbit'] = 'To deliver an SMS, the recipient\'s mobile number and the message text are sent to Corbit SMS (api.corbit.sa).';
$string['privacy:metadata:corbit:message'] = 'The text of the SMS.';
$string['privacy:metadata:corbit:phonenumber'] = 'The mobile number of the recipient.';
$string['recentmessages'] = 'Recent messages';
$string['recipient'] = 'Recipient';
$string['sender_name'] = 'Sender name';
$string['sender_name_help'] = 'A sender name approved in your Corbit account, written exactly as it appears there. The test page lists the approved names.';
$string['sendernotapproved'] = 'Sender name {$a} is not approved in this account';
$string['sendtest'] = 'Send a test SMS';
$string['sendtestbutton'] = 'Send test SMS';
$string['testfailed'] = 'The test SMS was not sent: {$a}';
$string['testmessage'] = 'Message';
$string['testmessagedefault'] = 'Test message from {$a} through Corbit SMS.';
$string['testnumber'] = 'Mobile number';
$string['testnumber_help'] = 'For example 05XXXXXXXX or +9665XXXXXXXX.';
$string['testpage'] = 'Corbit SMS test';
$string['testsent'] = 'Corbit accepted the test SMS to {$a->number}. Message ID: {$a->id}';
$string['testsentnoid'] = 'Corbit accepted the test SMS to {$a}.';
