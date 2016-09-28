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
 * Short-answer question type upgrade code.
 *
 * @package    qtype
 * @subpackage splitset
 * @copyright  2014 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the essay question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_splitset_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    // Moodle v2.4.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2014121401) {
        $table = new xmldb_table('question_splitset');

        // add set5name if missing
        $field = new xmldb_field('set5name', XMLDB_TYPE_CHAR, 50, null, XMLDB_NOTNULL, null, null, 'set4name');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('feedbackok');

        // Conditionally rename or add correctfeedback.
        if ($dbman->field_exists($table, $field)) {
            $field->set_attributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, 'set5name');
            $dbman->rename_field($table, $field, 'correctfeedback');
        } else {
            $field = new xmldb_field('correctfeedback', XMLDB_TYPE_TEXT, 'big', null, null, null, null, 'set5name');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $field = new xmldb_field('correctfeedbackformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'correctfeedback');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('partiallycorrectfeedback', XMLDB_TYPE_TEXT, 'big', null, null, null, null, 'correctfeedbackformat');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('partiallycorrectfeedbackformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'partiallycorrectfeedback');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('feedbackmissed');

        // Conditionally rename feedbackmissed or add incorrectfeedback.
        if ($dbman->field_exists($table, $field)) {
            $field->set_attributes(XMLDB_TYPE_TEXT, 'big', null, null, null, null, 'partiallycorrectfeedbackformat');
            $dbman->rename_field($table, $field, 'incorrectfeedback');
        } else {
            $field = new xmldb_field('incorrectfeedback', XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null, 'partiallycorrectfeedbackformat');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $field = new xmldb_field('incorrectfeedbackformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'incorrectfeedback');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('shownumcorrect', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'incorrectfeedbackformat');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Shortanswer savepoint reached.
        upgrade_plugin_savepoint(true, 2014121401, 'qtype', 'splitset');
    }

    return true;
}
