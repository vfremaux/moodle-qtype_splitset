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
 * @package    moodlecore
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * restore plugin class that provides the necessary information
 * needed to restore one essay qtype plugin
 *
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_splitset_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        return array(
            new restore_path_element('splitset', $this->get_pathfor('/splitset')),
            new restore_path_element('splitsetsub', $this->get_pathfor('/splitset/splitsetsubs/splitsetsub'))
        );
    }

    /**
     * Process the qtype/splitset element
     */
    public function process_splitset($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped
        $questioncreated = $this->get_mappingid('question_created',
                $this->get_old_parentid('question')) ? true : false;

        // If the question has been created by restore, we need to create its
        // qtype_splitset too
        if ($questioncreated) {
            $data->questionid = $this->get_new_parentid('question');
            $newitemid = $DB->insert_record('question_splitset', $data);
            $this->set_mapping('question_splitset', $oldid, $newitemid);
        }
    }

    /**
     * Process the qtype/splitset/splitsetsub element
     */
    public function process_splitsetsub($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;

        if ($questioncreated) {
            // If the question has been created by restore, we need to create its
            // question_splitset_sub too

            // Adjust some columns
            $data->question = $newquestionid;
            // Insert record
            $newitemid = $DB->insert_record('question_splitset_sub', $data);
            // Create mapping (there are files and states based on this)
            $this->set_mapping('question_splitset_sub', $oldid, $newitemid);

        } else {
            // splitset questions require mapping of question_splitset_sub, because
            // they are used by question_states->answer

            // Look for matching subquestion (by question, questiontext and answertext)
            $sub = $DB->get_record_select('question_splitset_sub', 'questionid = ? AND ' .
                    $DB->sql_compare_text('code') . ' = ' .
                    $DB->sql_compare_text('?').' AND item = ?',
                    array($newquestionid, $data->code, $data->item),
                    'id', IGNORE_MULTIPLE);

            // Found, let's create the mapping
            if ($sub) {
                $this->set_mapping('question_splitset_sub', $oldid, $sub->id);
            } else {
                throw new restore_step_exception('error_question_splitset_sub_missing_in_db', $data);
            }
        }
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder
     */
    public static function define_decode_contents() {

        $contents = array();

        $contents[] = new restore_decode_content('question_splitset_sub', array('item'), 'question_splitset_sub');

        $fields = array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback');
        $contents[] = new restore_decode_content('question_splitset', $fields, 'question_splitset');

        return $contents;
    }

    /**
    * should not have anyhing to do here
     */
    protected function after_execute_question() {
        global $DB;
    }

    /**
     * Given one question_states record, return the answer
     * recoded pointing to all the restored stuff for splitset questions
     *
     * answer is one comma separated list of hypen separated pairs
     * containing question_splitset_sub->id and question_splitset_sub->code
     */
    public function recode_legacy_state_answer($state) {
        $answer = $state->answer;
        $resultarr = array();
        foreach (explode(',', $answer) as $pair) {
            $pairarr = explode('-', $pair);
            $id = $pairarr[0];
            $code = $pairarr[1];
            $newid = $this->get_mappingid('question_splitset_sub', $id);
            $resultarr[] = implode('-', array($newid, $code));
        }
        return implode(',', $resultarr);
    }

    /**
     * Recode the choice order as stored in the response.
     * @param string $order the original order.
     * @return string the recoded order.
     */
    protected function recode_match_sub_order($order) {
        $neworder = array();
        foreach (explode(',', $order) as $id) {
            if ($newid = $this->get_mappingid('question_splitset_sub', $id)) {
                $neworder[] = $newid;
            }
        }
        return implode(',', $neworder);
    }
}
