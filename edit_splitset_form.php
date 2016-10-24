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
 * The editing form code for this question type.
 *
 * @package     qtype_splitset
 * @category    qtype
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   (C) 2006 onwards Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/question/type/edit_question_form.php');

/**
 * splitset editing form definition.
 *
 * See http://docs.moodle.org/en/Development:lib/formslib.php for information
 * about the Moodle forms library, which is based on the HTML Quickform PEAR library.
 */
class qtype_splitset_edit_form extends question_edit_form {

    public function definition_inner($mform) {

        $options['2'] = '2';
        $options['3'] = '3';
        $options['4'] = '4';
        $options['5'] = '5';
        $mform->addElement('select', 'sets', get_string('sets', 'qtype_splitset'), $options);

        $mform->addElement('text', 'set1name', get_string('name1', 'qtype_splitset'), array('size' => '50'));
        $mform->setType('name1', PARAM_CLEANHTML);

        $mform->addElement('text', 'set2name', get_string('name2', 'qtype_splitset'), array('size' => '50'));
        $mform->setType('name2', PARAM_CLEANHTML);

        $mform->addElement('text', 'set3name', get_string('name3', 'qtype_splitset'), array('size' => '50'));
        $mform->setType('name3', PARAM_CLEANHTML);

        $mform->addElement('text', 'set4name', get_string('name4', 'qtype_splitset'), array('size' => '50'));
        $mform->setType('name4', PARAM_CLEANHTML);

        $mform->addElement('text', 'set5name', get_string('name5', 'qtype_splitset'), array('size' => '50'));
        $mform->setType('name5', PARAM_CLEANHTML);

        $numoptions['0'] = get_string('numericnum', 'qtype_splitset');
        $numoptions['1'] = get_string('alphanum', 'qtype_splitset');;
        $numoptions['2'] = get_string('alphasupnum', 'qtype_splitset');
        $mform->addElement('select', 'numbering', get_string('numbering', 'qtype_splitset'), $numoptions);

        $mform->addElement('checkbox', 'shuffleanswers', get_string('shuffleanswers', 'qtype_splitset'));
        $mform->setType('shuffleanswers', PARAM_BOOL);

        $label = get_string('correctfeedback', 'qtype_splitset');
        $mform->addElement('editor', 'correctfeedback', $label, array('rows' => 5), $this->editoroptions);
        $mform->setType('correctfeedback', PARAM_CLEANHTML);
        $mform->addHelpButton('correctfeedback', 'correctfeedback', 'qtype_splitset');

        $label = get_string('partiallycorrectfeedback', 'qtype_splitset');
        $mform->addElement('editor', 'partiallycorrectfeedback', $label, array('rows' => 5), $this->editoroptions);
        $mform->setType('partiallycorrectfeedback', PARAM_CLEANHTML);
        $mform->addHelpButton('partiallycorrectfeedback', 'partiallycorrectfeedback', 'qtype_splitset');

        $label = get_string('incorrectfeedback', 'qtype_splitset');
        $mform->addElement('editor', 'incorrectfeedback', $label, array('rows' => 5), $this->editoroptions);
        $mform->setType('incorrectfeedback', PARAM_CLEANHTML);
        $mform->addHelpButton('incorrectfeedback', 'incorrectfeedback', 'qtype_splitset');

        $repeated = array();
        $repeated[] =& $mform->createElement('header', 'answerhdr', get_string('itemno', 'qtype_splitset', '{no}'));
        $label = get_string('item', 'qtype_splitset');
        $repeated[] =& $mform->createElement('editor', 'item', $label, array('rows' => 5), $this->editoroptions);

        $defaultsets = (isset($this->question->options->sets)) ? $this->question->options->sets : 2;

        for ($i = 1; $i <= $defaultsets; $i++) {
            $setoptions[$i] = "$i";
        }
        $repeated[] =& $mform->createElement('select', 'set', get_string('set', 'qtype_splitset'), $setoptions);
        if (isset($this->question->options)) {
            $countanswers = count($this->question->options->items);
        } else {
            $countanswers = 0;
        }
        $add = QUESTION_NUMANS_START > ($countanswers + QUESTION_NUMANS_ADD);
        $repeatsatstart = ($add) ? QUESTION_NUMANS_START : ($countanswers + QUESTION_NUMANS_ADD);
        $repeatedoptions = array();
        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions, 'noanswers', 'addanswers', QUESTION_NUMANS_ADD,
                               get_string('addmoreanswers', 'qtype_splitset'));

    }

    public function set_data($question) {

        if (!empty($question->options)) {
            $question->sets = $question->options->sets;
            $question->numbering = $question->options->numbering;
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->set1name = @$question->options->set1name;
            $question->set2name = @$question->options->set2name;
            $question->set3name = @$question->options->set3name;
            $question->set4name = @$question->options->set4name;
            $question->set5name = @$question->options->set5name;
            $question->incorrectfeedback['text'] = $question->options->incorrectfeedback;
            $question->incorrectfeedbackformat['format'] = $question->options->incorrectfeedbackformat;
            $question->correctfeedback['text'] = $question->options->correctfeedback;
            $question->correctfeedback['format'] = $question->options->correctfeedbackformat;
            $question->partiallycorrectfeedback['text'] = $question->options->partiallycorrectfeedback;
            $question->partiallycorrectfeedback['format'] = $question->options->partiallycorrectfeedbackformat;

            if (!empty($question->options->items)) {
                $i = 0;
                foreach ($question->options->items as $item) {
                    $itemeditor = array();
                    $itemeditor['text'] = $item->item;
                    $itemeditor['format'] = 0 + @$item->itemformat;
                    $question->item[] = $itemeditor;
                    $question->set[] = $item->answer;
                    $i++;
                }
            }
        }
        parent::set_data($question);
    }

    public function validation($data, $files = array()) {

        $errors = parent::validation($data, $files);

        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }

    public function qtype() {
        return 'splitset';
    }
}
