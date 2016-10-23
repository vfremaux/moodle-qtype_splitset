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
 * Matching question definition class.
 *
 * @package     qtype_splitset
 * @category    qtype
 * @copyright   (C) 2006 Valery Fremaux
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Represents a splitset question.
 *
 * @copyright  2009 Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_splitset_question extends question_graded_automatically_with_countback {

    /** @var boolean Whether the question items should be shuffled. */
    public $shuffleitems;

    public $correctfeedback;
    public $correctfeedbackformat;
    public $partiallycorrectfeedback;
    public $partiallycorrectfeedbackformat;
    public $incorrectfeedback;
    public $incorrectfeedbackformat;

    /** @var original array of question items. */
    public $items;

    /** @var array of choices that can be done to each item. */
    public $choices;

    /** @var array shuffled item indexes. */
    protected $itemorder;

    /**
     * Stores in attempt state specific question scope internal states and question instance setings
     */
    public function start_attempt(question_attempt_step $step, $variant) {

        $this->itemorder = array_keys($this->items);
        if ($this->shuffleitems) {
            shuffle($this->itemorder);
        }
        $step->set_qt_var('_itemorder', implode(',', $this->itemorder));
    }

    /**
     * Resotres form state some internal instance specific settings
     */
    public function apply_attempt_state(question_attempt_step $step) {
        $this->itemorder = explode(',', $step->get_qt_var('_itemorder'));
    }

    /**
     * Computes a printable summary fro the question 
     */
    public function get_question_summary() {
        $question = $this->html_to_text($this->questiontext, $this->questiontextformat);

        $items = array();
        foreach ($this->itemorder as $itemid) {
            $items[] = $this->html_to_text($this->items[$itemid], $this->itemformats[$itemid]);
        }

        return $question . ' {' . implode('; ', $items) . '}';
    }

    public function classify_response(array $response) {

        $selectedchoices = array();

        foreach ($this->itemorder as $key => $itemid) {
            if (array_key_exists('sub'.$key, $response) && $response['sub'.$key]) {
                $selectedchoices[$itemid] = $this->sets[$response['sub'.$key]];
            } else {
                $selectedchoices[$itemid] = 0;
            }
        }

        $parts = array();
        foreach ($this->items as $itemid => $item) {
            if (empty($selectedchoices[$itemid])) {
                $parts[$itemid] = question_classified_response::no_response();
                continue;
            }
            $choice = $this->choices[$selectedchoices[$itemid]];
            $parts[$itemid] = new question_classified_response(
                    $selectedchoices[$itemid], $choice,
                    ($selectedchoices[$itemid] == $this->choices[$itemid]) / count($this->items));
        }
        return $parts;
    }

    /**
     * Computes a printable form of the response
     */
    public function summarise_response(array $response) {

        $matches = array();

        foreach ($this->itemorder as $key => $itemid) {
            if (array_key_exists('sub'.$key, $response) && $response['sub'.$key]) {
                $matches[] = $this->html_to_text($this->items[$itemid], $this->itemformats[$itemid]) . ' -> ' . $this->sets[$response['sub'.$key]];
            }
        }
        if (empty($matches)) {
            return null;
        }
        return implode('; ', $matches);
    }

    /**
     * we use this function to set a true 0 as choice in unanswered or erroneous subresponses.
     */
    public function clear_wrong_from_response(array $response) {

        foreach ($this->itemorder as $key => $itemid) {
            if (!array_key_exists('sub'.$key, $response) || $response['sub'.$key] != $this->choices[$itemid]) {
                $response['sub'.$key] = 0;
            }
        }
        return $response;
    }

    public function get_expected_data() {

        $vars = array();
        foreach ($this->itemorder as $key => $notused) {
            $vars['sub'.$key] = PARAM_INTEGER;
        }
        return $vars;
    }

    /**
     * tells how many subquestions are correct
     */
    public function get_num_parts_right(array $response) {

        $right = 0;

        foreach ($this->itemorder as $key => $itemid) {
            if ($response['sub'.$key] == $this->choices[$itemid]) {
                $right++;
            }
            $total ++;
        }
        return(array($right, $total));
    }

    public function get_correct_response() {

        $response = array();

        foreach ($this->itemorder as $key => $itemid) {
            $response['sub'.$key] = $this->choices[$itemid];
        }
        return $response;
    }

    public function is_complete_response(array $response) {

        $complete = true;

        foreach ($this->itemorder as $key => $itemid) {
            $complete = $complete && !empty($response['sub'.$key]);
        }
        return $complete;
    }

    public function is_gradable_response(array $response) {

        foreach ($this->itemorder as $key => $itemid) {
            if (!empty($response['sub'.$key])) {
                return true;
            }
        }
        return false;
    }

    public function get_validation_error(array $response) {

        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallparts', 'qtype_match');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        foreach ($this->itemorder as $key => $notused) {
            $fieldname = 'sub'.$key;
            if (!question_utils::arrays_same_at_key_integer($prevresponse, $newresponse, $fieldname)) {
                return false;
            }
        }
        return true;
    }

    public function grade_response(array $response) {

        $good = 0;
        $total = 0;

        foreach ($this->itemorder as $key => $itemid) {
            if ($response['sub'.$key] == $this->choices[$itemid]) {
                $good += 1;
            }
            $total += 1;
        }
        $fraction = $good / $total;
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    /*
     * compute grade by counting correct answered, bad answered and unanswered with penalty 
     * @see matches for computation model
     */
    public function compute_final_grade($responses, $totaltries) {
        $totalitemscore = 0;

        foreach ($this->itemorder as $key => $itemid) {
            $fieldname = 'sub'.$key;

            $lastwrongindex = -1;
            $finallyright = false;
            foreach ($responses as $i => $response) {
                if (!array_key_exists($fieldname, $response) || !$response[$fieldname] || $this->choices[$itemid] != $response[$fieldname]) {
                    $lastwrongindex = $i;
                    $finallyright = false;
                } else {
                    $finallyright = true;
                }
            }

            if ($finallyright) {
                $totalitemscore += max(0, 1 - ($lastwrongindex + 1) * $this->penalty);
            }
        }

        return $totalitemscore / count($this->itemorder);
    }

    public function get_item_order() {
        return $this->itemorder;
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {

        if ($component == 'qtype_slitset' && $filearea == 'subquestion') {
            $subqid = reset($args); // Itemid is sub question id.
            return array_key_exists($subqid, $this->items);

        } else if ($component == 'question' && in_array($filearea, array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'))) {
            return $this->check_combined_feedback_file_access($qa, $options, $filearea);

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
        }
    }
}
