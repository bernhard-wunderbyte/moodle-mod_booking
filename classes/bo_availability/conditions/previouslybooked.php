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
 * Base class for a single booking option availability condition.
 *
 * All bo condition types must extend this class.
 *
 * @package mod_booking
 * @copyright 2022 Wunderbyte GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace mod_booking\bo_availability\conditions;

use mod_booking\bo_availability\bo_condition;
use mod_booking\booking_option_settings;
use mod_booking\singleton_service;
use moodle_url;
use MoodleQuickForm;
use stdClass;

/**
 * This class takes the configuration from json in the available column of booking_options table.
 *
 * All bo condition types must extend this class.
 *
 * @package mod_booking
 * @copyright 2022 Wunderbyte GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class previouslybooked implements bo_condition {

    /** @var int $id Id is set via json during construction */
    public $id = null;

    /** @var stdClass $customsettings an stdclass coming from the json which passes custom settings */
    public $customsettings = null;

    /**
     * Constructor.
     *
     * @param integer $id
     * @return void
     */
    public function __construct(int $id = null) {

        if ($id) {
            $this->id = $id;
        }
    }

    /**
     * Needed to see if class can take JSON.
     * @return bool
     */
    public function is_json_compatible(): bool {
        return true; // Customizable condition.
    }

    /**
     * Needed to see if it shows up in mform.
     * @return bool
     */
    public function is_shown_in_mform(): bool {
        return true;
    }

    /**
     * Determines whether a particular item is currently available
     * according to this availability condition.
     * @param booking_option_settings $settings Item we're checking
     * @param int $userid User ID to check availability for
     * @param bool $not Set true if we are inverting the condition
     * @return bool True if available
     */
    public function is_available(booking_option_settings $settings, $userid, $not = false):bool {

        // This is the return value. Not available to begin with.
        $isavailable = false;

        if (!isset($this->customsettings->optionid)) {
            $isavailable = true;
        } else {
            $optionid = $this->customsettings->optionid;
            $optionsettings = singleton_service::get_instance_of_booking_option_settings($optionid);
            $bookinganswer = singleton_service::get_instance_of_booking_answers($optionsettings);
            $bookinginformation = $bookinganswer->return_all_booking_information($userid);

            if (isset($bookinginformation['iambooked'])) {
                $isavailable = true;
            }
        }

        // If it's inversed, we inverse.
        if ($not) {
            $isavailable = !$isavailable;
        }

        return $isavailable;
    }

    /**
     * Obtains a string describing this restriction (whether or not
     * it actually applies). Used to obtain information that is displayed to
     * students if the activity is not available to them, and for staff to see
     * what conditions are.
     *
     * The $full parameter can be used to distinguish between 'staff' cases
     * (when displaying all information about the activity) and 'student' cases
     * (when displaying only conditions they don't meet).
     *
     * @param bool $full Set true if this is the 'full information' view
     * @param booking_option_settings $settings Item we're checking
     * @param int $userid User ID to check availability for
     * @param bool $not Set true if we are inverting the condition
     * @return array availability and Information string (for admin) about all restrictions on
     *   this item
     */
    public function get_description($full = false, booking_option_settings $settings, $userid = null, $not = false):array {

        $description = '';

        $isavailable = $this->is_available($settings, $userid, $not);

        if ($isavailable) {
            $description = $full ? get_string('bo_cond_previouslybooked_full_available', 'mod_booking') :
                get_string('bo_cond_previouslybooked_available', 'mod_booking');
        } else {

            $url = new moodle_url('/mod/booking/optionview.php', [
                'optionid' => $this->customsettings->optionid,
                'cmid' => $settings->cmid
            ]);

            $description = $full ? get_string('bo_cond_previouslybooked_full_not_available',
                'mod_booking',
                $url->out(false)) :
                get_string('bo_cond_previouslybooked_not_available', 'mod_booking');
        }

        return [$isavailable, $description];
    }

    /**
     * Only customizable functions need to return their necessary form elements.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function add_condition_to_mform(MoodleQuickForm &$mform) {

        $mform->addElement('html', '<p class="text-danger text-center">...todo: previously booked...</p>');

        $mform->addElement('html', '<hr class="w-50"/>');

    }

    /**
     * Returns a condition object which is needed to create the condition JSON.
     *
     * @param stdClass &$fromform
     * @return stdClass the object for the JSON
     */
    public function get_condition_object_for_json(stdClass &$fromform): stdClass {
        $conditionobject = new stdClass;

        // TODO.

        return $conditionobject;
    }
}