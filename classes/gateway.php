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

use core_sms\message;
use core_sms\message_status;
use smsgateway_corbit\local\api;
use smsgateway_corbit\local\phone;

/**
 * Corbit SMS gateway: sends Moodle's SMS (sign-in codes, notifications) through a Corbit account.
 *
 * @package    smsgateway_corbit
 * @copyright  2026 Corbit Company
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_sms\gateway {
    #[\Override]
    public function send(message $message): message {
        $token = trim((string) ($this->config->api_token ?? ''));
        $sender = trim((string) ($this->config->sender_name ?? ''));
        if (!$this->enabled || $token === '' || $sender === '') {
            return $message->with(status: message_status::GATEWAY_NOT_AVAILABLE);
        }

        $number = phone::normalise($message->recipientnumber, (string) ($this->config->countrycode ?? ''));
        if ($number === '') {
            debugging('Corbit SMS: ' . get_string('invalidnumber', 'smsgateway_corbit'), DEBUG_DEVELOPER);
            return $message->with(status: message_status::GATEWAY_REJECTED);
        }

        $result = (new api($token))->send($number, $sender, (string) $message->content);
        if ($result['result'] === api::RESULT_SENT) {
            return $message->with(status: message_status::GATEWAY_SENT);
        }
        // The reason is not stored with the message; the plugin's test page shows it.
        debugging('Corbit SMS: ' . $result['error'], DEBUG_DEVELOPER);
        $status = $result['result'] === api::RESULT_REJECTED ? message_status::GATEWAY_REJECTED : message_status::GATEWAY_FAILED;
        return $message->with(status: $status);
    }

    #[\Override]
    public function get_send_priority(message $message): int {
        // Only a gateway with a token and a sender name can send.
        $configured = !empty($this->config->api_token) && !empty($this->config->sender_name);
        return $configured ? 50 : 0;
    }
}
