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
 * Unit tests for this question type.
 *
 * @package     qtype_splitset
 * @category    qtype
 * @copyright   (C) 2011 Valery Fremaux
 * @author      valery.fremaux@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../../../../config.php');

global $CFG;
require_once($CFG->libdir . '/simpletestlib.php');
require_once($CFG->dirroot . '/question/type/splitset/questiontype.php');

class splitset_qtype_test extends UnitTestCase {
    var $qtype;

    function setUp() {
        $this->qtype = new splitset_qtype();
    }

    function tearDown() {
        $this->qtype = null;
    }

    function test_name() {
        $this->assertEqual($this->qtype->name(), 'splitset');
    }
    
    // TODO write unit tests for the other methods of the question type class.
}
