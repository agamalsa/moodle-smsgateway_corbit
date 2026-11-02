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

namespace smsgateway_corbit;

use core_sms\hook\after_sms_gateway_form_hook;

/**
 * Adds the Corbit fields to the SMS gateway form (Site administration > Plugins > SMS > Manage SMS gateways).
 *
 * @package    smsgateway_corbit
 * @copyright  2026 Corbit Company
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_listener {
    /**
     * Form fields for a Corbit gateway.
     *
     * @param after_sms_gateway_form_hook $hook
     */
    public static function set_form_definition_for_corbit_sms_gateway(after_sms_gateway_form_hook $hook): void {
        if ($hook->plugin !== 'smsgateway_corbit') {
            return;
        }
        $mform = $hook->mform;

        $testurl = new \moodle_url('/sms/gateway/corbit/test.php');
        $mform->addElement(
            'static',
            'corbit_information',
            '',
            get_string('forminformation', 'smsgateway_corbit', $testurl->out(false))
        );

        $mform->addElement(
            'passwordunmask',
            'api_token',
            get_string('api_token', 'smsgateway_corbit'),
            'maxlength="1024" size="40"'
        );
        $mform->setType('api_token', PARAM_RAW_TRIMMED);
        $mform->addRule('api_token', get_string('required'), 'required', null, 'server');
        $mform->addHelpButton('api_token', 'api_token', 'smsgateway_corbit');

        $mform->addElement('text', 'sender_name', get_string('sender_name', 'smsgateway_corbit'), 'maxlength="40" size="20"');
        $mform->setType('sender_name', PARAM_TEXT);
        $mform->addRule('sender_name', get_string('required'), 'required', null, 'server');
        $mform->addRule('sender_name', get_string('maximumchars', '', 40), 'maxlength', 40);
        $mform->addHelpButton('sender_name', 'sender_name', 'smsgateway_corbit');

        // Moodle's own "Country code" field above applies; Corbit numbers default to Saudi Arabia (966) when it is empty.
        $mform->setDefault('countrycode', '966');
    }
}
