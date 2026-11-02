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
$string['api_token_help'] = 'The personal API token of your Corbit SMS account. Sign in at app.mobile.net.sa and create a token.';
$string['apierror'] = 'Corbit SMS: {$a}';
$string['approvedsenders'] = 'Approved sender names';
$string['balance'] = 'SMS balance';
$string['connected'] = 'Connected';
$string['connectionerror'] = 'Could not reach the Corbit API: {$a}';
$string['disabled'] = 'Disabled';
$string['error401'] = 'The API token was rejected. Copy it again from your Corbit account.';
$string['errorhttp'] = 'The Corbit API answered with HTTP {$a}.';
$string['forminformation'] = 'Enter the API token of your Corbit SMS account and one of its approved sender names. For Saudi numbers, set the country code to 966. After saving, check the account and send a test SMS on the <a href="{$a}">Corbit SMS test page</a>.';
$string['gateway'] = 'Gateway';
$string['invalidnumber'] = 'This is not a mobile number that can receive SMS.';
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
