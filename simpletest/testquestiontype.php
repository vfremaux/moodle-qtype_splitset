<?php
/**
 * Unit tests for this question type.
 *
 * @copyright &copy; 2011 Valery Fremaux
 * @author valery.fremaux@club-internet.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package splitset
 *//** */
    
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

?>
