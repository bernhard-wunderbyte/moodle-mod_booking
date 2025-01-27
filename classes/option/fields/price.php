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
use mod_booking\price as Mod_bookingPrice;
use MoodleQuickForm;
use stdClass;

/**
 * Class to handle one property of the booking_option_settings class.
 *
 * @copyright Wunderbyte GmbH <info@wunderbyte.at>
 * @author Georg Maißer
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class price extends field_base {

    /**
     * This ID is used for sorting execution.
     * @var int
     */
    public static $id = MOD_BOOKING_OPTION_FIELD_PRICE;

    /**
     * Some fields are saved with the booking option...
     * This is normal behaviour.
     * Some can be saved only post save (when they need the option id).
     * @var int
     */
    public static $save = MOD_BOOKING_EXECUTION_POSTSAVE;

    /**
     * This identifies the header under which this particular field should be displayed.
     * @var string
     */
    public static $header = MOD_BOOKING_HEADER_PRICE;

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

        return parent::prepare_save_field($formdata, $newoption, $updateparam, '');
    }

    /**
     * Instance form definition
     * @param MoodleQuickForm $mform
     * @param array $formdata
     * @param array $optionformconfig
     * @return void
     */
    public static function instance_form_definition(MoodleQuickForm &$mform, array &$formdata, array $optionformconfig) {

        // Add price.
        $price = new Mod_bookingPrice('option', $formdata['id']);
        $price->add_price_to_mform($mform);
    }

    /**
     * This function adds error keys for form validation.
     * @param array $data
     * @param array $files
     * @param array $errors
     * @return void
     */
    public static function validation(array $data, array $files, array &$errors) {

        // Save the prices.
        $price = new Mod_bookingPrice('option', $data['id']);
        $price->validation($data, $errors);
    }

    /**
     * The save data function is very specific only for those values that should be saved...
     * ... after saving the option. This is so, when we need an option id for saving (because of other table).
     * @param stdClass $formdata
     * @param stdClass $option
     * @return void
     * @throws dml_exception
     */
    public static function save_data(stdClass &$formdata, stdClass &$option) {

        // Save the prices.
        $price = new Mod_bookingPrice('option', $option->id);
        $price->save_from_form($formdata);
    }

    /**
     * Standard function to transfer stored value to form.
     * @param stdClass $data
     * @param booking_option_settings $settings
     * @return void
     * @throws dml_exception
     */
    public static function set_data(stdClass &$data, booking_option_settings $settings) {

        // While importing, we need to set the imported prices.
        // Therefore, we first get the pricecategories.

        // Right now, the price handler still sets prices via default in the definition, NOT via set data.
        // This has to be fixed.

        $pricehandler = new Mod_bookingPrice('option', $data->id);

        if (!empty($data->importing)) {

            if (!is_array($pricehandler->pricecategories)) {
                return;
            }

            foreach ($pricehandler->pricecategories as $category) {

                // If we have an imported value, we use it here.
                // To do this, we look in data for the price category identifier.
                if (!empty($data->{$category->identifier}) && is_numeric($data->{$category->identifier})) {
                    $price = $data->{$category->identifier};
                    // We don't want this value to be used elsewhere.
                    unset($data->{$category->identifier});
                } else {
                    $price = $category->defaultvalue ?? 0;
                }

                $pricegroup = MOD_BOOKING_FORM_PRICEGROUP . $category->identifier;
                $priceidentifier = MOD_BOOKING_FORM_PRICE . $category->identifier;

                $data->{$pricegroup}[$priceidentifier] = $price;

                // If we have at least one price during import, we set useprice to 1.
                $data->useprice = 1;
            }

        } else {
            $pricehandler->set_data($data);
        }
    }
}
