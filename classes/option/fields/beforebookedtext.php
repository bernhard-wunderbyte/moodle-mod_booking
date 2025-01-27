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
use mod_booking\option\fields_info;
use mod_booking\option\field_base;
use MoodleQuickForm;
use stdClass;

/**
 * Class to handle one property of the booking_option_settings class.
 *
 * @copyright Wunderbyte GmbH <info@wunderbyte.at>
 * @author Georg Maißer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class beforebookedtext extends field_base {

    /**
     * This ID is used for sorting execution.
     * @var int
     */
    public static $id = MOD_BOOKING_OPTION_FIELD_BEFOREBOOKEDTEXT;

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
    public static $header = MOD_BOOKING_HEADER_BOOKINGOPTIONTEXT;

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

        $key = fields_info::get_class_name(static::class);
        $value = $formdata->{$key} ?? null;

        if (!empty($value)) {
            // The form comes in the form of an array.
            if (gettype($value) === 'array') {
                $newoption->beforebookedtext = $value['text'];
            } else {
                $newoption->{$key} = $value;
            }
        } else {
            $newoption->{$key} = '';
        }

        // We can return an warning message here.
        return '';
    }

    /**
     * Instance form definition
     * @param MoodleQuickForm $mform
     * @param array $formdata
     * @param array $optionformconfig
     * @return void
     */
    public static function instance_form_definition(MoodleQuickForm &$mform, array &$formdata, array $optionformconfig) {

        // Standardfunctionality to add a header to the mform (only if its not yet there).
        fields_info::add_header_to_mform($mform, self::$header);

        // Workaround: Only show, if it is not turned off in the option form config.
        // We currently need this, because hideIf does not work with editors.
        // In expert mode, we do not hide anything.
        if ($optionformconfig['formmode'] == 'expert' ||
            !isset($optionformconfig['beforebookedtext']) || $optionformconfig['beforebookedtext'] == 1) {
            $mform->addElement('editor', 'beforebookedtext', get_string("beforebookedtext", "booking"),
                    null, null);
            $mform->setType('beforebookedtext', PARAM_CLEANHTML);
            $mform->addHelpButton('beforebookedtext', 'beforebookedtext', 'mod_booking');
        }
    }

    /**
     * Standard function to transfer stored value to form.
     * @param stdClass $data
     * @param booking_option_settings $settings
     * @return void
     * @throws dml_exception
     */
    public static function set_data(stdClass &$data, booking_option_settings $settings) {

        $key = fields_info::get_class_name(static::class);
        // Normally, we don't call set data after the first time loading.
        if (isset($data->{$key})) {
            return;
        }

        $value = $settings->{$key} ?? null;

        $data->{$key} = ['text' => $value];
    }
}
