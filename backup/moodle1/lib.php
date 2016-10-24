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
 * @package    qtype
 * @subpackage splitset
 * @version Moodle 2
 * @copyright  2011 Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Short answer question type conversion handler
 */
class moodle1_qtype_splitset_handler extends moodle1_qtype_handler {

    /**
     * @return array
     */
    public function get_question_subpaths() {
        return array(
            'ITEMS/ITEM'
        );
    }

    /**
     * Appends the essay specific information to the question
     */
    public function process_question(array $data, array $raw) {
        global $CFG;

        // populate the list of matches first to get their ids
        // note that the field is re-populated on restore anyway but let us
        // do our best to produce valid backup files
        $itemids = array();
        if (isset($data['items']['item'])) {
            foreach ($data['items']['item'] as $item) {
                $itemids[] = $item['id'];
            }
        }

        // convert match options
        $splitset = $data;
        $splitset['id'] = $this->converter->get_nextid();
        $this->write_xml('splitset', $splitset, array('/splitset/id'));

        // convert subs
        $this->xmlwriter->begin_tag('splitsetsubs');
        if (isset($data['items']['item'])) {
            foreach ($data['items']['item'] as $item) {
                // replay the upgrade step 2009072100
                if ($CFG->texteditors !== 'textarea') {
                    $item['answer'] = text_to_html($item['answer'], false, false, true);
                    $item['answerformat'] = FORMAT_HTML;
                } else {
                    $item['answer'] = $item['answer'];
                }

                $this->write_xml('splitsetsub', $match, array('/splitsetsub/id'));
            }
        }
        $this->xmlwriter->end_tag('splitsetsubs');
    }
}
