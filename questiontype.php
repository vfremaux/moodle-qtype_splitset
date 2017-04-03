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
 * The question type class for the splitset question type.
 *
 * @copyright &copy; 2006 Valery Fremaux
 * @author valery.fremaux@club-internet.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package splitset
 */
defined('MOODLE_INTERNAL') || die();

define('NUMERIC_NUMBERING', 0);
define('ALPHA_NUMBERING', 1);
define('ALPHASUP_NUMBERING', 2);

/**
 * The splitset question class
 *
 * TODO give an overview of how the class works here.
 */
class qtype_splitset extends question_type {

    /**
     * @return boolean to indicate success of failure.
     */
    public function get_question_options($question) {
        global $DB;

        parent::get_question_options($question);
        $question->options = $DB->get_record('question_splitset', array('questionid' => $question->id));
        $question->options->items = $DB->get_records('question_splitset_sub', array('questionid' => $question->id), 'id');

        return true;
    }

    /**
     * Save the units and the answers associated with this question.
     * @return boolean to indicate success of failure.
     */
    public function save_question_options($question) {
        global $DB;

        $context = $question->context;
        $result = new stdClass();

        $oldsubquestions = $DB->get_records('question_splitset_sub', array('questionid' => $question->id), 'id ASC');

        // Subquestions will be an array with subquestion ids.
        $subquestions = array();

        // Insert all the new question+answer pairs.
        foreach ($question->item as $key => $questiontext) {
            if ($questiontext['text'] == '') {
                continue;
            }

            // Update an existing subquestion if possible.
            $subquestion = array_shift($oldsubquestions);
            if (!$subquestion) {
                $subquestion = new stdClass();

                // Determine a unique random code.
                $subquestion->code = rand(1, 999999);
                $params = array('code' => $subquestion->code, 'questionid' => $question->id);
                while ($DB->record_exists('question_splitset_sub', $params)) {
                    $subquestion->code = rand(1, 999999);
                }
                $subquestion->questionid = $question->id;
                $subquestion->answer = 0;
                $subquestion->item = '';
                $subquestion->itemformat = FORMAT_MOODLE;
                $subquestion->id = $DB->insert_record('question_splitset_sub', $subquestion);
            }

            $subquestion->questiontext = $this->import_or_save_files($questiontext,
                                                                     $context,
                                                                     'qtype_splitset',
                                                                     'subquestion',
                                                                     $subquestion->id);
            $subquestion->item = $questiontext['text'];
            $subquestion->itemformat = $questiontext['format'];
            $subquestion->answer = trim($question->set[$key]);

            $DB->update_record('question_splitset_sub', $subquestion);

            $subquestions[] = $subquestion->id;
        }

        // Delete old subquestions records.
        $fs = get_file_storage();
        foreach ($oldsubquestions as $oldsub) {
            $fs->delete_area_files($context->id, 'qtype_splitset', 'subquestion', $oldsub->id);
            $DB->delete_records('question_splitset_sub', array('id' => $oldsub->id));
        }

        // Save the question options.
        $options = $DB->get_record('question_splitset', array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->correctfeedback = $question->correctfeedback['text'];
            $options->correctfeedbackformat = FORMAT_MOODLE;
            $options->partiallycorrectfeedback = $question->partiallycorrectfeedback['text'];
            $options->partiallycorrectfeedbackformat = FORMAT_MOODLE;
            $options->incorrectfeedback = $question->incorrectfeedback['text'];
            $options->incorrectfeedbackformat = FORMAT_MOODLE;
            $options->id = $DB->insert_record('question_splitset', $options);
        }

        $options->subquestions = implode(',', $subquestions);
        $options->shuffleanswers = 0 + @$question->shuffleanswers;
        $options->sets = $question->sets;
        $options->numbering = $question->numbering;
        $options->set1name = $question->set1name;
        $options->set2name = $question->set2name;
        $options->set3name = $question->set3name;
        $options->set4name = $question->set4name;
        $options->set5name = $question->set5name;
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record('question_splitset', $options);

        $this->save_hints($question, true);

        if (!empty($result->notice)) {
            return $result;
        }

        if (count($subquestions) < 2) {
            $result->notice = get_string('notenoughanswers', 'question', 2);
            return $result;
        }

        return true;
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);

        $question->shuffleitems = $questiondata->options->shuffleanswers;
        $this->initialise_combined_feedback($question, $questiondata, true);

        $question->items = array();
        $question->choices = array();
        $question->sets = array();

        // Transfer set labels into question.
        for ($i = 1; $i <= $questiondata->options->sets; $i++) {
            $var = "set{$i}name";
            $question->sets[$i] = $questiondata->options->{$var};
        }

        // Transfer items and expected answers into question.
        foreach ($questiondata->options->items as $itemid => $item) {
            $question->items[$itemid] = $item->item;
            $question->itemformats[$itemid] = 0 + @$item->itemformat;
            $question->choices[$itemid] = $item->answer;
        }
    }

    protected function make_hint($hint) {
        return question_hint_with_parts::load_from_record($hint);
    }

    /**
     * Deletes question from the question-type specific tables
     *
     * @param integer $questionid The question being deleted
     * @return boolean to indicate success of failure.
     */
    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('question_splitset', array('questionid' => "$questionid"));
        $DB->delete_records('question_splitset_sub', array('questionid' => "$questionid"));

        parent::delete_question($questionid, $contextid);
    }

    public function get_random_guess_score($questiondata) {
        $q = $this->make_question($questiondata);
        return 1 / count($q->choices);
    }

    public function get_possible_responses($questiondata) {
        $subqs = array();

        $q = $this->make_question($questiondata);

        foreach ($q->items as $itemid => $item) {

            $responses = array();
            foreach ($q->choices as $choiceid => $choice) {
                $portion = ($choiceid == $q->sets[$itemid]) / count($q->items);
                $possible = $q->html_to_text($item, $q->itemformats[$itemid]) . ': ' . $choice;
                $responses[$choiceid] = new question_possible_response($possible, $portion);
            }
            $responses[null] = question_possible_response::no_response();

            $subqs[$itemid] = $responses;
        }

        return $subqs;
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        global $DB;

        $fs = get_file_storage();

        parent::move_files($questionid, $oldcontextid, $newcontextid);

        $params = array('question' => $questionid);
        $subquestionids = $DB->get_records_menu('question_splitset_sub', $params, 'id', 'id,1');
        foreach ($subquestionids as $subquestionid => $notused) {
            $fs->move_area_files_to_new_context($oldcontextid,
                    $newcontextid, 'qtype_splitset', 'subquestion', $subquestionid);
        }

        $this->move_files_in_combined_feedback($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        global $DB;
        $fs = get_file_storage();

        parent::delete_files($questionid, $contextid);

        $subquestionids = $DB->get_records_menu('question_splitset_sub', array('questionid' => $questionid), 'id', 'id, 1');
        foreach ($subquestionids as $subquestionid => $notused) {
            $fs->delete_area_files($contextid, 'qtype_splitset', 'subquestion', $subquestionid);
        }

        $this->delete_files_in_combined_feedback($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }
}
