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
 * Provides the information to backup essay questions
 *
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_qtype_splitset_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to question element
     */
    protected function define_question_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill
        $plugin = $this->get_plugin_element(null, '../../qtype', 'splitset');

        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // connect the visible container ASAP
        $plugin->add_child($pluginwrapper);

        // Now create the qtype own structures
        $splitset = new backup_nested_element('splitset', array('id'), array(
                'sets', 'numbering', 'shuffleanswers',
                'set1name', 'set2name', 'set3name', 'set4name', 'set5name', 
                'correctfeedback', 'correctfeedbackformat', 'partiallycorrectfeedback', 'partiallycorrectfeedbackformat', 
                'incorrectfeedback', 'incorrectfeedbackformat', 'shownumcorrect'));

        $splitsetsubs = new backup_nested_element('splitsetsubs');

        $splitsetsub = new backup_nested_element('splitsetsub', array('id'), array(
                'code', 'answer', 'item', 'itemformat'));

        // Now the own qtype tree
        $splitsetsubs->add_child($splitsetsub);
        $splitset->add_child($splitsetsubs);
        $pluginwrapper->add_child($splitset);

        // set source to populate the data
        $splitset->set_source_table('question_splitset',
                array('questionid' => backup::VAR_PARENTID));

        $splitsetsub->set_source_table('question_splitset_sub',
                array('questionid' => backup::VAR_PARENTID));

        // don't need to annotate ids nor files

        return $plugin;
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype
     *
     * Used by {@link get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas() {
        return array(
        );
    }
}
