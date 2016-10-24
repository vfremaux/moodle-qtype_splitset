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
 * Defines the 'missingtype' question renderer class.
 *
 * @package     qtype_splitset
 * @category    qtype
 * @copyright   2012 Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * This question renderer class is used when the actual question type of this
 * question cannot be found.
 *
 * @copyright  2012 Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_splitset_renderer extends qtype_with_combined_feedback_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

        $question  = $qa->get_question();
        $itemorder = $question->get_item_order();
        $response  = $qa->get_last_qt_data();

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa), array('class' => 'qtext'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::start_tag('table', array('class' => 'answer'));
        $result .= html_writer::start_tag('tbody');

        $result .= html_writer::start_tag('tr', array('class' => 'header'));
        $result .= html_writer::tag('th', '', array('class' => 'header c0'));

        $i = 1;
        foreach ($question->sets as $setid => $set) {
            $classes = 'c'.$i;
            $result .= html_writer::tag('td', $set, array('class' => $classes));
            $i++;
        }

        $parity = 0;
        foreach ($itemorder as $key => $itemid) {

            $result .= html_writer::start_tag('tr', array('class' => 'r' . $parity));
            $fieldname = 'sub'.$key;

            $result .= html_writer::tag('td', $question->format_text(
                    $question->items[$itemid], $question->itemformats[$itemid],
                    $qa, 'qtype_splitset', 'subquestion', $itemid),
                    array('class' => 'text'));

            if (array_key_exists($fieldname, $response)) {
                $selected = $response[$fieldname];
            } else {
                $selected = 0;
            }

            foreach ($question->sets as $setid => $set) {

                $classes = 'control';
                $feedbackimage = '';

                // Mark good response.
                $isgood = (int) ($question->choices[$itemid] == $setid);

                // Mark given response.
                $fraction = (int) ($selected && ($selected == $setid));
                $checked = ($fraction) ? 'checked="checked"' : '' ;

                if ($options->correctness && $selected) {
                    if ($fraction) {
                        $classes = $this->feedback_class($isgood);
                    }
                    $feedbackimage = $this->feedback_image($isgood);
                }

                $optionradio = '<input type="radio"
                                       name="'.$qa->get_qt_field_name($fieldname).'"
                                       value="'.$setid.'"
                                       '.$checked.' />';
                $result .= html_writer::tag('td', $optionradio.' '.$feedbackimage, array('class' => $classes));
            }

            $result .= html_writer::end_tag('tr');
            $parity = 1 - $parity;
        }
        $result .= html_writer::end_tag('tbody');
        $result .= html_writer::end_tag('table');

        $result .= html_writer::end_tag('div'); // ablock

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                    $question->get_validation_error($response),
                    array('class' => 'validationerror'));
        }

        return $result;
    }

    public function correct_response(question_attempt $qa) {

        $question = $qa->get_question();
        $itemorder = $question->get_item_order();

        foreach ($itemorder as $key => $itemid) {
            $correct[] = $question->sets[$question->choices[$itemid]];
        }

        if (!empty($correct)) {
            return get_string('correctansweris', 'qtype_splitset', implode(', ', $correct));
        }
    }
}
