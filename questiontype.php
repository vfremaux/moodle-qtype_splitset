<?php
/**
 * The question type class for the splitset question type.
 *
 * @copyright &copy; 2006 Valery Fremaux
 * @author valery.fremaux@club-internet.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package splitset
 *//** */

define('NUMERIC_NUMBERING', 0);
define('ALPHA_NUMBERING', 1);
define('ALPHASUP_NUMBERING', 2);

/**
 * The splitset question class
 *
 * TODO give an overview of how the class works here.
 */
class splitset_qtype extends default_questiontype {

    function name() {
        return 'splitset';
    }
    
    // TODO think about whether you need to override the is_manual_graded or
    // is_usable_by_random methods form the base class. Most the the time you
    // Won't need to.

    /**
     * @return boolean to indicate success of failure.
     */
    function get_question_options(&$question) {
        // TODO code to retrieve the extra data you stored in the database into
        // $question->options.
        
        $question->options = get_record('question_splitset', 'questionid', $question->id);
        $question->options->items = get_records('question_splitset_sub', 'questionid', $question->id, 'id');
        
        return true;
    }

    /**
     * Save the units and the answers associated with this question.
     * @return boolean to indicate success of failure.
     */
    function save_question_options($question) {
        // TODO code to save the extra data to your database tables from the
        // $question object, which has all the post data from editquestion.html
        
        $alphanumbering = 'abcdefghijklmnopqrstuvwxyz';
        $alphasupnumbering = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        
        $options->questionid = $question->id;
        $options->sets = $question->sets;
        $options->numbering = $question->numbering;
        $options->shuffleanswers = 0 + @$question->shuffleanswers;
        $options->set1name = $question->set1name;
        $options->set2name = $question->set2name;
        $options->set3name = $question->set3name;
        $options->set4name = $question->set4name;
        $options->feedbackok = $question->feedbackok;
        $options->feedbackmissed = $question->feedbackmissed;
        
        if ($oldrec = get_record('question_splitset', 'questionid', $question->id)){
        	$options->id = $oldrec->id;
        	update_record('question_splitset', $options);
        } else {
        	insert_record('question_splitset', $options);
        }
        
        // todo : how to get and store items
        
		if ($question->item){
			delete_records('question_splitset_sub', 'questionid', $question->id);
			$i = 0;
			foreach($question->item as $item){
				if (empty($item)){
					continue;
					$i++;
				}
				
				switch($options->numbering){
					case 0: $code = ''; break;
					case 1: $code = $i + 1; break;
					case 2: $code = substr($alphanumbering, $i, 1); break;
					case 3: $code = substr($alphasupnumbering, $i, 1); break;
				}
				
				$itemrec = new StdClass;
				$itemrec->questionid = $question->id;
				$itemrec->code = $code ;
				$itemrec->answer = $question->set[$i];
				$itemrec->item = preg_replace('/^<p>(.*)<\/p>$/', "$1", $item);
        		insert_record('question_splitset_sub', $itemrec);
        		$i++;
			}
		}        
        
        return true;
    }

    /**
     * Deletes question from the question-type specific tables
     *
     * @param integer $questionid The question being deleted
     * @return boolean to indicate success of failure.
     */
    function delete_question($questionid) {
        // TODO delete any    
        
        delete_records('question_splitset', 'questionid', "$questionid");
        delete_records('question_splitset_sub', 'questionid', "$questionid");
        
        return true;
    }

    function create_session_and_responses(&$question, &$state, $cmoptions, $attempt) {

		// echo "CREATING STATE ";
		// print_object($state);

        if (!$subquestions = get_records('question_splitset_sub', 'questionid', $question->id, 'id ASC')) {
            notify('Error: Missing items!');
            return false;
        }

        // Place the subquestions into the state options keyed by id
        foreach ($subquestions as $subquestion) {
            $state->options->subquestions[$subquestion->id] = $subquestion;
        }

		$state->responses = array();
        foreach ($state->options->subquestions as $key => $subquestion) {
            // This seems rather over complicated, but it is useful for the
            // randomsamatch questiontype, which can then inherit the print
            // and grading functions. This way it is possible to define multiple
            // answers per question, each with different marks and feedback.
            /*
            $answer = new stdClass();
            $answer->id       = $key;
            $answer->answer   = $subquestion->answer;
            $answer->fraction = 1.0;
            $state->options->subquestions[$key]->options->answers[$key] = clone($answer);
            */
        	$state->responses[$key] = '';
        }

        // Add default defaultresponse value
        $state->options->defaultresponse = '';
        
        // set response array to empty

		// echo "CREATING STATE : Output state ";
		// print_object($state);

        return true;
    }

    function restore_session_and_responses(&$question, &$state) {

		// echo 'RESTORING ';
		// print_object($state);

        // The serialized format for splitset questions is a comma separated
        // list of item-answer pairs (e.g. 1-1,2-1,3-2), where the id of
        // the item in table question_order_sub is mapped to the set id.
        if (isset($state->answer)){
	        $responses = explode(',', $state->answer);
	    } else {
	    	// we have responses submitted but not recorded
	        $responses = explode(',', array_pop($state->responses));	
	    }
	    
        if (!$state->options->subquestions = get_records('question_splitset_sub', 'questionid', $question->id, 'id ASC')) {
            notify('Error: Missing subquestions!');
            return false;
        }

        // Place the questions into the state options and restore the
        // previous answers
        $state->responses = array();
        if (!empty($responses)){
	        foreach ($responses as $response) {
	        	list($key, $answer) = explode('-', $response);
	            $state->responses[$key] = $answer;
	        }
	    }

		/*
        foreach ($state->options->subquestions as $key => $subquestion) {
            // This seems rather over complicated, but it is useful for the
            // randomsamatch questiontype, which can then inherit the print
            // and grading functions. This way it is possible to define multiple
            // answers per question, each with different marks and feedback.
            $answer = new stdClass();
            $answer->id       = $key;
            $answer->answer   = $subquestion->answer;
            $answer->fraction = 1.0;
            $state->options->subquestions[$key]->options->answers[$key] = clone($answer);
        }
        */

		// echo "RESTORING state out";
		// print_object($state);

        return true;
    }
    
    function save_session_and_responses(&$question, &$state) {
        // TODO package up the students response from the $state->responses
        // array into a string and save it in the question_states.answer field.

		// echo 'SAVING ';
		// print_object($state);
        
        $subquestions = &$state->options->subquestions;
        $responses = &$state->options->responses;

		/*
        if (isset($responses['defaultresponse']) and $responses['defaultresponse'] == 0) {
            $state->options->defaultresponse = 'yes';
        }
        // If it's not set at all, default is no
        else if (isset($responses['defaultresponse']) and $responses['defaultresponse'] != 0) {
            $state->options->defaultresponse = 0;
        } else {
            $state->options->defaultresponse = 0;
        }
        */

        // Serialize responses
        $responses = array();
        foreach ($subquestions as $key => $subquestion) {
            $response = 0;
            if ($subquestion->item) {
                if (isset($state->responses[$key])) {
                    $response = $state->responses[$key];
                }
            }
            $responses[] = $key.'-'.$response;
        }
        $responses = implode(',', $responses);

        // Set the legacy answer field
        if (!set_field('question_states', 'answer', $responses, 'id', $state->id)) {
            return false;
        }
    }
    
    function print_question_formulation_and_controls(&$question, &$state, $cmoptions, $options) {
        global $CFG;

        $readonly = empty($options->readonly) ? '' : 'readonly="readonly"';

        // Print formulation
        $questiontext = $this->format_text($question->questiontext, $question->questiontextformat, $cmoptions);
        $image = get_question_image($question, $cmoptions->course);
    
        // TODO prepare any other data necessary. For instance
        
        $feedback = '';
        if ($options->feedback){
	        if (!empty($question->options->feedbackok) && ($state->raw_grade == $question->maxgrade)) {
	    		$feedback = $question->options->feedbackok;
	    		$feedbackclass = 'feedbackok';
	        }
	        if (!empty($question->options->feedbackmissed) && ($state->raw_grade != $question->maxgrade)) {
	    		$feedback = $question->options->feedbackmissed;
	    		$feedbackclass = 'feedbackmissed';
	        }
	    }
		
		$responses = $state->responses;

		// get previous response to mark radio inputs
		/*
        $state->options->defaultresponse = array_pop($state->responses);

        // split item-answer pais into a table
        $responses = array_map(create_function('$val',
         'return explode("-", $val);'), $responses);
         */
    
        include($CFG->dirroot."/question/type/splitset/display.html");
    }
    
    function grade_responses(&$question, &$state, $cmoptions) {
        // TODO assign a grade to the response in state.

        $state->raw_grade = 0;

		// calculate a raw grade between 0.0 and 1.0
		$fraction = (float) (1 / count($question->options->items));

        foreach ($state->responses as $key => $response) {
            if ($response == $question->options->items[$key]->answer) {
                $state->raw_grade += $fraction;
            }
        }

        // Make sure we don't assign negative or too high marks
        $state->raw_grade = min(max((float) $state->raw_grade,
                            0.0), 1.0) * $question->maxgrade;

        // Apply the penalty for this attempt
        $state->penalty = $question->penalty * $question->maxgrade;

        // mark the state as graded
        $state->event = ($state->event ==  QUESTION_EVENTCLOSE) ? QUESTION_EVENTCLOSEANDGRADE : QUESTION_EVENTGRADE;
        
        return true;

    }
    
    function compare_responses($question, $state, $teststate) {
        // TODO write the code to return two different student responses, and
        // return two if the should be considered the same.

		// echo "Comparing ";
		// print_object($state);

        $return = 2;
        foreach($state->responses as $key => $response){
        	if (empty($key)) continue;
        	if (!array_key_exists($key, $teststate->responses) || $teststate->responses[$key] != $response){
        		$return = false;
        		break;
        	}
        }
        
        return $return;
    }

    /**
     * Checks whether a response matches a given answer, taking the tolerance
     * and units into account. Returns a true for if a response matches the
     * answer, false if it doesn't.
     */
    function test_response(&$question, &$state, $answer) {
        // TODO if your code uses the question_answer table, write a method to
        // determine whether the student's response in $state matches the    
        // answer in $answer.
        return false;
    }

    function check_response(&$question, &$state){
        // TODO
        return false;
    }

    function get_correct_responses(&$question, &$state) {
        // TODO
        $responses = array();
        
        foreach ($question->options->items  as $item) {
            $responses[$item->id] = $item->answer;
        }
        return empty($responses) ? null : $responses;
    }

	/**
	* get all required respons in a readable format
	*
	*/
    function get_all_responses(&$question, &$state) {
        $answers = array();
        if (is_array($question->options->subquestions)) {
            foreach ($question->options->subquestions as $itemid => $answer) {
                if ($answer->questiontext) {
                    $r = new stdClass;
           			$answerkey = 'set'.$answer.'name';
                    $r->answer = $answer->code . ": " . $question->options->$answerkey;
                    $r->credit = 1;
                    $answers[$itemid] = $r;
                }
            }
        }
        $result = new stdClass;
        $result->id = $question->id;
        $result->responses = $answers;
        return $result;
    }

	/**
	* get actual response of the attempt in a readable format
	*/
    function get_actual_response($question, $state) {
       	$subquestions = &$state->options->subquestions;
       	$responses    = &$state->responses;

		// echo "Getting actual response ";       	
       	// print_object($subquestions);
       	// print_object($responses);
       	
       	$results = array();
       	foreach ($subquestions as $key => $sub) {
           	foreach ($responses as $itemid => $value) {
           		$answerkey = 'set'.$value.'name';
               	$results[$itemid] =  $subquestions[$itemid]->code . ": " . $question->options->$answerkey;
           	}
       	}
       	return $results;
    }

    /**
     * Backup the data in the question
     *
     * This is used in question/backuplib.php
     */
    function backup($bf, $preferences, $question, $level=6) {
        $status = true;

        $splitset = get_record('question_splitset', 'questionid', $question);
        $status = $status && fwrite($bf, full_tag("SETS", 6, false, $splitset->sets));
        $status = $status && fwrite($bf, full_tag("NUMBERING", 6, false, $splitset->numbering));
        $status = $status && fwrite($bf, full_tag("SHUFFLEANSWERS", 6, false, $splitset->shuffleanswers));
        $status = $status && fwrite($bf, full_tag("SET1NAME", 6, false, $splitset->set1name));
        $status = $status && fwrite($bf, full_tag("SET2NAME", 6, false, $splitset->set2name));
        $status = $status && fwrite($bf, full_tag("SET3NAME", 6, false, $splitset->set3name));
        $status = $status && fwrite($bf, full_tag("SET4NAME", 6, false, $splitset->set4name));
        $status = $status && fwrite($bf, full_tag("FEEDBACKOK", 6, false, $splitset->feedbackok));
        $status = $status && fwrite($bf, full_tag("FEEDBACKMISSED", 6, false, $splitset->feedbackmissed));

        $items = get_records('question_splitset_sub', 'questionid', $question, 'id ASC');
        // If there are items
        if ($items) {
            $status = fwrite($bf, start_tag("ITEMS", 6, true));
            // Iterate over each item
            foreach ($items as $item) {
                $status = $status && fwrite($bf, start_tag("ITEM", 7, true));
                // Print item contents
                $status = $status && fwrite($bf, full_tag("ID", 8, false, $item->id));
                $status = $status && fwrite($bf, full_tag("CODE", 8, false, $item->code));
                $status = $status && fwrite($bf, full_tag("ITEM", 8, false, $item->item));
                $status = $status && fwrite($bf, full_tag("ANSWER", 8, false, $item->answer));
                $status = $status && fwrite($bf, end_tag("ITEM", 7, true));
            }
            $status = $status && fwrite($bf, end_tag("ITEMS", 6, true));
        }
        return $status;
    }

    /**
     * Restores the data in the question
     *
     * This is used in question/restorelib.php
     */
    function restore($old_question_id, $new_question_id, $info, $restore) {
        $status = true;

        // Get the items array
        $items = $info['#']['ITEMS']['0']['#']['ITEM'];

        // We have to build the subquestions field (a list of order_sub id)
        $subquestionsfield = "";
        $infirst = true;

        // Iterate over items
        for($i = 0; $i < sizeof($items); $i++) {
            $iteminfo = $items[$i];

            // We'll need this later!!
            $oldid = backup_todb($iteminfo['#']['ID']['0']['#']);

            // Now, build the question_splitset_sub record structure
            $itemsub = new stdClass;
            $itemsub->questionid = $new_question_id;
            $itemsub->code = backup_todb($iteminfo['#']['CODE']['0']['#']);
            $itemsub->item = backup_todb($iteminfo['#']['ITEM']['0']['#']);
            $itemsub->answer = backup_todb($iteminfo['#']['ANSWER']['0']['#']);

            // The structure is equal to the db, so insert the question_splitset_sub
            $newid = insert_record('question_splitset_sub', $itemsub);

            // Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

            if ($newid) {
                // We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'question_splitset_sub', $oldid, $newid);
                // We have a new splitset_sub, append it to subquestions_field
                if ($infirst) {
                    $subquestionsfield .= $newid;
                    $infirst = false;
                } else {
                    $subquestionsfield .= ",".$newid;
                }
            } else {
                $status = false;
            }
        }

        // We have created every spliset_sub, now create the splitset
        $splitset = new stdClass;
        $splitset->questionid = $new_question_id;
        $splitset->sets = $info['#']['SETS']['0']['#'];
        $splitset->numbering = $info['#']['NUMBERING']['0']['#'];
        $splitset->shuffleanswers = $info['#']['SHUFFLEANSWERS']['0']['#'];
        $splitset->set1name = $info['#']['SET1NAME']['0']['#'];
        $splitset->set2name = $info['#']['SET2NAME']['0']['#'];
        $splitset->set3name = $info['#']['SET3NAME']['0']['#'];
        $splitset->set4name = $info['#']['SET4NAME']['0']['#'];
        $splitset->feedbackok = $info['#']['FEEDBACKOK']['0']['#'];
        $splitset->feedbackmissed = $info['#']['FEEDBACKMISSED']['0']['#'];

        // The structure is equal to the db, so insert the question_splitset
        $newid = insert_record('question_splitset', addslashes_object($splitset));

        if (!$newid) {
            $status = false;
        }

        return $status;
    }

}

// Register this question type with the system.
question_register_questiontype(new splitset_qtype());
?>
