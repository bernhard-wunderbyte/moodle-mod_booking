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
 * E-mail templates for the custom message modals (booking_emailtemplates table).
 *
 * @package mod_booking
 * @copyright 2026 Wunderbyte GmbH <info@wunderbyte.at>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_booking\local;

use stdClass;

/**
 * E-mail templates (subject and message) for the custom message modals.
 *
 * Scope semantics of a record in booking_emailtemplates:
 * - optionid set: the template only applies to that booking option.
 * - optionid = 0 and cmid set: the template applies to that booking instance.
 * - optionid = 0 and cmid = 0: global template, applies everywhere.
 *
 * @package mod_booking
 * @copyright 2026 Wunderbyte GmbH <info@wunderbyte.at>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class emailtemplates {
    /** @var string Template scope: only the given booking option. */
    public const SCOPE_OPTION = 'option';
    /** @var string Template scope: the given booking instance. */
    public const SCOPE_INSTANCE = 'instance';
    /** @var string Template scope: all booking instances. */
    public const SCOPE_GLOBAL = 'global';

    /**
     * All templates applicable in the given context, most specific first
     * (option templates, then instance templates, then global ones).
     *
     * @param int $cmid course module id of the booking instance
     * @param int $optionid booking option id
     * @return array template records indexed by id
     */
    public static function get_templates(int $cmid, int $optionid): array {
        global $DB;

        $sql = "SELECT *
                  FROM {booking_emailtemplates}
                 WHERE (optionid = :optionid AND optionid > 0)
                    OR (optionid = 0 AND cmid = :cmid AND cmid > 0)
                    OR (optionid = 0 AND cmid = 0)
              ORDER BY optionid DESC, cmid DESC, subject ASC";

        return $DB->get_records_sql($sql, [
            'optionid' => $optionid,
            'cmid' => $cmid,
        ]);
    }

    /**
     * The template scopes the current user may save in, in picker order.
     *
     * Option scope needs no extra capability: whoever may open the tracker of
     * the booking option (and thus the modal) may save option templates.
     * Instance scope requires mod/booking:customemailtemplates in the module
     * context, global scope requires it in the system context.
     *
     * @param int $cmid course module id of the booking instance
     * @return string[] allowed SCOPE_* constants
     */
    public static function get_allowed_scopes(int $cmid): array {
        $scopes = [self::SCOPE_OPTION];
        if (!empty($cmid) && has_capability('mod/booking:customemailtemplates', \context_module::instance($cmid))) {
            $scopes[] = self::SCOPE_INSTANCE;
        }
        if (has_capability('mod/booking:customemailtemplates', \context_system::instance())) {
            $scopes[] = self::SCOPE_GLOBAL;
        }
        return $scopes;
    }

    /**
     * Save a new template. The scope constants map to the stored cmid/optionid:
     * SCOPE_OPTION keeps both, SCOPE_INSTANCE drops the optionid, SCOPE_GLOBAL drops both.
     *
     * @param string $subject
     * @param string $message
     * @param string $scope one of the SCOPE_* constants
     * @param int $cmid course module id of the booking instance
     * @param int $optionid booking option id
     * @return int id of the new record
     */
    public static function save(string $subject, string $message, string $scope, int $cmid, int $optionid): int {
        global $DB;

        $record = new stdClass();
        $record->subject = $subject;
        $record->message = $message;
        $record->cmid = $scope === self::SCOPE_GLOBAL ? 0 : $cmid;
        $record->optionid = $scope === self::SCOPE_OPTION ? $optionid : 0;
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        return $DB->insert_record('booking_emailtemplates', $record);
    }

    /**
     * Localized scope label of a template record, e.g. for the template picker.
     *
     * @param stdClass $template
     * @return string
     */
    public static function get_scope_label(stdClass $template): string {
        if (!empty($template->optionid)) {
            return get_string('bookingoption', 'mod_booking');
        }
        if (!empty($template->cmid)) {
            return get_string('bookinginstance', 'mod_booking');
        }
        return get_string('emailtemplateglobal', 'mod_booking');
    }
}
