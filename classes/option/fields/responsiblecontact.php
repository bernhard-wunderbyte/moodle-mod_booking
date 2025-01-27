<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Control and manage booking dates.
 *
 * @package mod_booking
 * @copyright 2023 Wunderbyte GmbH <info@wunderbyte.at>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_booking\option\fields;

use mod_booking\booking_option_settings;
use mod_booking\option\field_base;
use mod_booking\singleton_service;
use mod_booking\teachers_handler;
use MoodleQuickForm;
use stdClass;

/**
 * Class to handle one property of the booking_option_settings class.
 *
 * @copyright Wunderbyte GmbH <info@wunderbyte.at>
 * @author Georg Maißer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class responsiblecontact extends field_base {

    /**
     * This ID is used for sorting execution.
     * @var int
     */
    public static $id = MOD_BOOKING_OPTION_FIELD_RESPONSIBLECONTACT;

    /**
     * Some fields are saved with the booking option...
     * This is normal behaviour.
     * Some can be saved only post save (when they need the option id).
     * @var int
     */
    public static $save = MOD_BOOKING_EXECUTION_NORMAL;

    /**
     * This identifies the header under which this particular field should be displayed.
     * @var string
     */
    public static $header = MOD_BOOKING_HEADER_RESPONSIBLECONTACT;

    /**
     * This function interprets the value from the form and, if useful...
     * ... relays it to the new option class for saving or updating.
     * @param stdClass $formdata
     * @param stdClass $newoption
     * @param int $updateparam
     * @param mixed $returnvalue
     * @return string // If no warning, empty string.
     */
    public static function prepare_save_field(
        stdClass &$formdata,
        stdClass &$newoption,
        int $updateparam,
        $returnvalue = null): string {

        return parent::prepare_save_field($formdata, $newoption, $updateparam, 0);
    }

    /**
     * Instance form definition
     * @param MoodleQuickForm $mform
     * @param array $formdata
     * @param array $optionformconfig
     * @return void
     */
    public static function instance_form_definition(MoodleQuickForm &$mform, array &$formdata, array $optionformconfig) {

        // Responsible contact person.
        // Workaround: Only show, if it is not turned off in the option form config.
        // We currently need this, because hideIf does not work with headers.
        // In expert mode, we do not hide anything.
        if ($optionformconfig['formmode'] == 'expert' ||
            !isset($optionformconfig['responsiblecontactheader']) || $optionformconfig['responsiblecontactheader'] == 1) {
            // Advanced options.
            $mform->addElement('header', 'responsiblecontactheader',
            '<i class="fa fa-fw fa-user" aria-hidden="true"></i>&nbsp;' . get_string('responsiblecontact', 'mod_booking'));
        }
        // Responsible contact person - autocomplete.
        $options = [
            'ajax' => 'mod_booking/form_users_selector',
            'multiple' => false,
            'noselectionstring' => get_string('choose...', 'mod_booking'),
            'valuehtmlcallback' => function($value) {
                global $OUTPUT;
                $user = singleton_service::get_instance_of_user((int)$value);
                if (!$user || !user_can_view_profile($user)) {
                    return false;
                }
                $details = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                ];
                return $OUTPUT->render_from_template(
                        'mod_booking/form-user-selector-suggestion', $details);
            },
        ];
        $mform->addElement('autocomplete', 'responsiblecontact',
            get_string('responsiblecontact', 'mod_booking'), [], $options);
        $mform->addHelpButton('responsiblecontact', 'responsiblecontact', 'mod_booking');

    }

    /**
     * Standard function to transfer stored value to form.
     * @param stdClass $data
     * @param booking_option_settings $settings
     * @return void
     * @throws \dml_exception
     */
    public static function set_data(stdClass &$data, booking_option_settings $settings) {

        if (empty($data->importing) && !empty($data->id)) {
            $teacherhandler = new teachers_handler($data->id);
            $teacherhandler->set_data($data);
        } else {
            if (!empty($data->responsiblecontact)) {
                $userids = teachers_handler::get_user_ids_from_string($data->responsiblecontact);
                $data->responsiblecontact = $userids[0] ?? [];
            }
        }
    }
}
