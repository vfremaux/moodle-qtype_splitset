<?php
/**
 * The editing form code for this question type.
 *
 * @copyright &copy; 2006 Valery Fremaux
 * @author valery.fremaux@club-internet.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package splitset
 *//** */

require_once($CFG->dirroot.'/question/type/edit_question_form.php');

/**
 * splitset editing form definition.
 * 
 * See http://docs.moodle.org/en/Development:lib/formslib.php for information
 * about the Moodle forms library, which is based on the HTML Quickform PEAR library.
 */
class question_edit_splitset_form extends question_edit_form {
    function definition_inner(&$mform) {
        // TODO, add any form fields you need.

		$options['2']= '2';
		$options['3']= '3';
		$options['4']= '4';
		$mform->addElement('select', 'sets', get_string('sets', 'qtype_splitset'), $options);
        
		$mform->addElement('text', 'set1name', get_string('name1', 'qtype_splitset'), array('size'=>'50'));
		$mform->setType('name1', PARAM_CLEANHTML);

		$mform->addElement('text', 'set2name', get_string('name2', 'qtype_splitset'), array('size'=>'50'));
		$mform->setType('name2', PARAM_CLEANHTML);

		$mform->addElement('text', 'set3name', get_string('name3', 'qtype_splitset'), array('size'=>'50'));
		$mform->setType('name3', PARAM_CLEANHTML);

		$mform->addElement('text', 'set4name', get_string('name4', 'qtype_splitset'), array('size'=>'50'));
		$mform->setType('name4', PARAM_CLEANHTML);

		$numoptions['0'] = get_string('nonum', 'qtype_splitset');
		$numoptions['1'] = get_string('numericnum', 'qtype_splitset');
		$numoptions['2'] = get_string('alphanum', 'qtype_splitset');;
		$numoptions['3'] = get_string('alphasupnum', 'qtype_splitset');;
		$mform->addElement('select', 'numbering', get_string('numbering', 'qtype_splitset'), $numoptions);

		$mform->addElement('checkbox', 'shuffleanswers', get_string('shuffleanswers', 'qtype_splitset'));
		$mform->setType('shuffleanswers', PARAM_BOOL);
        
        $mform->addElement('htmleditor', 'feedbackok', get_string('feedbackok', 'qtype_splitset'), array('rows' => 10, 'course' => $this->coursefilesid));
        $mform->setType('feedbackok', PARAM_CLEANHTML);
        $mform->setHelpButton('feedbackok', array('feedbackok', get_string('feedbackok', 'qtype_splitset'), 'qtype_splitset'));

        $mform->addElement('htmleditor', 'feedbackmissed', get_string('feedbackmissed', 'qtype_splitset'), array('rows' => 10, 'course' => $this->coursefilesid));
        $mform->setType('feedbackmissed', PARAM_CLEANHTML);
        $mform->setHelpButton('feedbackmissed', array('feedbackmissed', get_string('feedbackmissed', 'qtype_splitset'), 'qtype_splitset'));

        $repeated = array();
        $repeated[] =& $mform->createElement('header', 'answerhdr', get_string('itemno', 'qtype_splitset', '{no}'));
        $repeated[] =& $mform->createElement('htmleditor', 'item', get_string('item', 'qtype_splitset'), array('rows' => 6, 'course' => $this->coursefilesid));

		$defaultsets = (isset($this->question->options->sets)) ? $this->question->options->sets : 2 ;

		for($i = 1; $i <= $defaultsets ; $i++){
			$setoptions[$i]= "$i";
		}
		$repeated[] =& $mform->createElement('select', 'set', get_string('set', 'qtype_splitset'), $setoptions);
        if (isset($this->question->options)){
            $countanswers = count($this->question->options->items);
        } else {
            $countanswers = 0;
        }
        $repeatsatstart = (QUESTION_NUMANS_START > ($countanswers + QUESTION_NUMANS_ADD))? QUESTION_NUMANS_START : ($countanswers + QUESTION_NUMANS_ADD);
        $repeatedoptions = array();
        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions, 'noanswers', 'addanswers', QUESTION_NUMANS_ADD, get_string('addmoreanswers', 'qtype_splitset'));

        // $mform->addElement( ... );
    }

    function set_data($question) {
        // TODO, preprocess the question definition so the data is ready to load into the form.
        // You may not need this method at all, in which case you can delete it.

        if (!empty($question->options)) {
            $question->sets = $question->options->sets;
            $question->numbering = $question->options->numbering;
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->set1name = $question->options->set1name;
            $question->set2name = $question->options->set2name;
            $question->set3name = $question->options->set3name;
            $question->set4name = $question->options->set4name;
            $question->feedbackok = $question->options->feedbackok;
            $question->feedbackmissed = $question->options->feedbackmissed;
             
            if (!empty($question->options->items)){
            	$i = 0;
            	foreach($question->options->items as $item){
            		$question->item[] = $item->item;
            		$question->set[] = $item->answer;
            		$i++;
            	}
			}
			
        }
        parent::set_data($question);
    }

    function validation($data) {
        $errors = array();

        // TODO, do extra validation on the data that came back from the form. E.g.
        // if (/* Some test on $data['customfield']*/) {
        //     $errors['customfield'] = get_string( ... );
        // }

        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }

    function qtype() {
        return 'splitset';
    }
}
?>