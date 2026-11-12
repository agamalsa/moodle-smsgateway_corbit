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
 * Uninstall: removes the Corbit gateways, so their API tokens do not stay in the database.
 *
 * @package    smsgateway_corbit
 * @copyright  2026 Corbit Company
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Deletes every SMS gateway that uses this plugin (Moodle keeps them in sms_gateways with the API token).
 *
 * @return bool
 */
function xmldb_smsgateway_corbit_uninstall() {
    global $DB;
    $ids = $DB->get_fieldset_select('sms_gateways', 'id', 'gateway = ?', [\smsgateway_corbit\gateway::class]);
    $DB->delete_records('sms_gateways', ['gateway' => \smsgateway_corbit\gateway::class]);
    // The SMS factor of multi-factor authentication must not keep pointing at a deleted gateway.
    if ($ids && in_array((string) get_config('factor_sms', 'smsgateway'), array_map('strval', $ids), true)) {
        unset_config('smsgateway', 'factor_sms');
    }
    return true;
}
