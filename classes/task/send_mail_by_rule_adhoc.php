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
namespace mod_booking\task;

defined('MOODLE_INTERNAL') || die();

global $CFG;

use mod_booking\message_controller;

require_once($CFG->dirroot . '/mod/booking/lib.php');

/**
 * Adhoc Task to send a mail by a rule at a certain time.
 */
class send_mail_by_rule_adhoc extends \core\task\adhoc_task {

    /**
     * Get task name.
     *
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('task_send_mail_by_rule_adhoc', 'mod_booking');
    }

    /**
     * Execution function.
     *
     * {@inheritdoc}
     * @throws \coding_exception
     * @throws \dml_exception
     * @see \core\task\task_base::execute()
     */
    public function execute() {

        $taskdata = $this->get_custom_data();
        $nextruntime = $this->get_next_run_time();

        echo 'send_mail_by_rule_adhoc task: sending mail for option ' . $taskdata->optionid . ' to user '
            . $taskdata->userid . PHP_EOL;

        if ($taskdata != null) {

            $rulefullpath = "\\mod_booking\\booking_rules\\rules\\" . $taskdata->rulename;
            $rule = new $rulefullpath;
            // Important: Load the rule data from JSON into the rule instance.
            $rule->set_ruledata_from_json($taskdata->rulejson);

            if (!$rule->check_if_rule_still_applies($taskdata->optionid, $taskdata->userid, $nextruntime)) {
                echo 'send_mail_by_rule_adhoc task: Rule does not apply anymore. Mail was NOT SENT for option ' .
                    $taskdata->optionid . ' and user ' . $taskdata->userid . PHP_EOL;
                return;
            }

            // Use message controller to send the message.
            $messagecontroller = new message_controller(
                MSGCONTRPARAM_SEND_NOW, MSGPARAM_CUSTOM_MESSAGE, $taskdata->cmid, null, $taskdata->optionid,
                    $taskdata->userid, null, null, $taskdata->customsubject, $taskdata->custommessage
            );

            if ($messagecontroller->send_or_queue()) {
                echo 'send_mail_by_rule_adhoc task: mail successfully sent for option ' . $taskdata->optionid . ' to user '
                . $taskdata->userid . PHP_EOL;
            } else {
                echo 'send_mail_by_rule_adhoc task: mail could not be sent to for option ' . $taskdata->optionid . ' to user '
                . $taskdata->userid . PHP_EOL;
            }

        } else {
            throw new \coding_exception(
                    'send_mail_by_rule_adhoc task: ERROR - missing taskdata.');
        }
    }
}