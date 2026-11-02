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

namespace smsgateway_corbit\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;

/**
 * Privacy provider: the plugin stores no personal data, but sends the number and the text of each SMS to Corbit.
 *
 * Moodle's SMS subsystem (core_sms) keeps its own record of sent messages.
 *
 * @package    smsgateway_corbit
 * @copyright  2026 Corbit Company
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {
    #[\Override]
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('corbit', [
            'phonenumber' => 'privacy:metadata:corbit:phonenumber',
            'message' => 'privacy:metadata:corbit:message',
        ], 'privacy:metadata:corbit');
        return $collection;
    }

    #[\Override]
    public static function get_contexts_for_userid(int $userid): contextlist {
        return new contextlist();
    }

    #[\Override]
    public static function export_user_data(approved_contextlist $contextlist) {
        // Nothing stored by this plugin.
    }

    #[\Override]
    public static function delete_data_for_all_users_in_context(\context $context) {
        // Nothing stored by this plugin.
    }

    #[\Override]
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        // Nothing stored by this plugin.
    }
}
