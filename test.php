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
 * Corbit SMS: account status of each Corbit gateway, a test SMS and the recent messages.
 *
 * @package    smsgateway_corbit
 * @copyright  2026 Corbit Company
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use smsgateway_corbit\local\api;
use smsgateway_corbit\local\phone;

// Same access as Moodle's own SMS gateway pages (sms/configure.php).
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$pageurl = new moodle_url('/sms/gateway/corbit/test.php');
$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('testpage', 'smsgateway_corbit'));
$PAGE->set_heading(get_string('testpage', 'smsgateway_corbit'));
navigation_node::override_active_url(new moodle_url('/sms/sms_gateways.php'));
$PAGE->navbar->add(get_string('testpage', 'smsgateway_corbit'), $pageurl);

$manager = \core\di::get(\core_sms\manager::class);
$records = $manager->get_gateway_records(['gateway' => \smsgateway_corbit\gateway::class]);

$gateways = [];
$configs = [];
foreach ($records as $record) {
    $gateways[$record->id] = format_string($record->name);
    $configs[$record->id] = (object) json_decode($record->config, true);
}

$form = new \smsgateway_corbit\form\test_form($pageurl, ['gateways' => $gateways]);
$notice = null;
if ($gateways && ($data = $form->get_data())) {
    $config = $configs[$data->gatewayid] ?? null;
    $number = $config ? phone::normalise($data->number, (string) ($config->countrycode ?? '')) : '';
    if (!$config) {
        $notice = [get_string('nogateway', 'smsgateway_corbit'), \core\output\notification::NOTIFY_ERROR];
    } else if ($number === '') {
        $notice = [get_string('invalidnumber', 'smsgateway_corbit'), \core\output\notification::NOTIFY_ERROR];
    } else {
        $result = (new api((string) ($config->api_token ?? '')))->send($number, (string) ($config->sender_name ?? ''), $data->text);
        if ($result['result'] === api::RESULT_SENT) {
            $notice = [get_string('testsent', 'smsgateway_corbit', ['number' => phone::mask($number), 'id' => $result['id']]),
                \core\output\notification::NOTIFY_SUCCESS];
        } else {
            $notice = [get_string('testfailed', 'smsgateway_corbit', $result['error']), \core\output\notification::NOTIFY_ERROR];
        }
    }
}

echo $OUTPUT->header();

if ($notice) {
    echo $OUTPUT->notification($notice[0], $notice[1]);
}

if (!$gateways) {
    $createurl = new moodle_url('/sms/configure.php', ['smsgateway' => 'smsgateway_corbit']);
    echo $OUTPUT->notification(
        get_string('nogateways', 'smsgateway_corbit', $createurl->out(false)),
        \core\output\notification::NOTIFY_INFO
    );
    echo $OUTPUT->footer();
    die();
}

// Account status of each Corbit gateway: the API is asked once per gateway each time this page opens.
$table = new html_table();
$table->head = [
    get_string('gateway', 'smsgateway_corbit'),
    get_string('sender_name', 'smsgateway_corbit'),
    get_string('account', 'smsgateway_corbit'),
    get_string('balance', 'smsgateway_corbit'),
    get_string('approvedsenders', 'smsgateway_corbit'),
];
foreach ($records as $record) {
    $config = $configs[$record->id];
    $sender = trim((string) ($config->sender_name ?? ''));
    $client = new api(trim((string) ($config->api_token ?? '')));
    $status = $balance = $senders = '';
    try {
        $balance = s($client->balance());
        $names = $client->approved_sender_names();
        $senders = $names ? s(implode(', ', $names)) : get_string('none');
        if ($sender !== '' && !in_array($sender, $names, true)) {
            $status = html_writer::span(
                get_string('sendernotapproved', 'smsgateway_corbit', s($sender)),
                'badge badge-warning bg-warning text-dark'
            );
        } else {
            $status = html_writer::span(get_string('connected', 'smsgateway_corbit'), 'badge badge-success bg-success');
        }
    } catch (moodle_exception $e) {
        $status = html_writer::span(get_string('notconnected', 'smsgateway_corbit'), 'badge badge-danger bg-danger')
            . ' ' . s($e->getMessage());
    }
    if (!$record->enabled) {
        $status .= ' ' . html_writer::span(get_string('disabled', 'smsgateway_corbit'), 'badge badge-secondary bg-secondary');
    }
    $editurl = new moodle_url('/sms/configure.php', ['id' => $record->id, 'returnurl' => $pageurl->out_as_local_url(false)]);
    $table->data[] = [
        html_writer::link($editurl, format_string($record->name)),
        s($sender),
        $status,
        $balance,
        $senders,
    ];
}
echo html_writer::table($table);

$form->display();

// Recent messages sent through Corbit gateways, as recorded by Moodle's SMS subsystem.
echo $OUTPUT->heading(get_string('recentmessages', 'smsgateway_corbit'), 3, 'mt-5');
[$insql, $params] = $DB->get_in_or_equal(array_keys($gateways), SQL_PARAMS_NAMED);
$messages = $DB->get_records_select(
    'sms_messages',
    "gatewayid $insql",
    $params,
    'timecreated DESC, id DESC',
    'id, recipientnumber, component, messagetype, gatewayid, status, timecreated',
    0,
    20
);
if (!$messages) {
    echo html_writer::tag('p', get_string('nomessages', 'smsgateway_corbit'));
} else {
    $table = new html_table();
    $table->head = [
        get_string('date'),
        get_string('gateway', 'smsgateway_corbit'),
        get_string('messagetype', 'smsgateway_corbit'),
        get_string('recipient', 'smsgateway_corbit'),
        get_string('status'),
    ];
    foreach ($messages as $m) {
        $status = \core_sms\message_status::tryFrom((string) $m->status);
        $description = $status ? $status->description() : null;
        // The name of the plugin that sent the SMS (Assignment, SMS mobile phone...), and its message type.
        $component = (string) $m->component;
        $source = get_string_manager()->string_exists('pluginname', $component) ? get_string('pluginname', $component) : $component;
        $table->data[] = [
            userdate($m->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
            $gateways[$m->gatewayid] ?? '',
            s($source) . ' ' . html_writer::span(s('(' . $m->messagetype . ')'), 'text-muted small'),
            html_writer::span(s(phone::mask((string) $m->recipientnumber)), '', ['dir' => 'ltr']),
            $description ? (string) $description : s((string) $m->status),
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
