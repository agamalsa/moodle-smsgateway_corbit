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

namespace smsgateway_corbit\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Test SMS form.
 *
 * @package    smsgateway_corbit
 * @copyright  2026 Corbit Company
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_form extends \moodleform {
    #[\Override]
    protected function definition() {
        global $SITE;
        $mform = $this->_form;
        $gateways = $this->_customdata['gateways'];

        $mform->addElement('header', 'testheader', get_string('sendtest', 'smsgateway_corbit'));
        $mform->addElement('select', 'gatewayid', get_string('gateway', 'smsgateway_corbit'), $gateways);
        $mform->setType('gatewayid', PARAM_INT);

        $mform->addElement('text', 'number', get_string('testnumber', 'smsgateway_corbit'), ['size' => 20, 'dir' => 'ltr']);
        $mform->setType('number', PARAM_TEXT);
        $mform->addRule('number', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('number', 'testnumber', 'smsgateway_corbit');

        $mform->addElement('textarea', 'text', get_string('testmessage', 'smsgateway_corbit'), ['rows' => 3, 'cols' => 50]);
        $mform->setType('text', PARAM_TEXT);
        $mform->addRule('text', get_string('required'), 'required', null, 'client');
        $mform->setDefault('text', get_string('testmessagedefault', 'smsgateway_corbit', format_string($SITE->shortname)));

        $this->add_action_buttons(false, get_string('sendtestbutton', 'smsgateway_corbit'));
    }
}
