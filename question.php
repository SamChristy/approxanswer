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
 * Approximate answer question definition class.
 *
 * @package    qtype
 * @subpackage approxanswer
 * @copyright  2012 Sam Christy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Represents an approximate answer question.
 */
class qtype_approxanswer_question extends question_graded_by_strategy
        implements question_response_answer_comparer {
    /** @var boolean whether answers should be graded case-sensitively. */
    public $usecase;
    /** @var array of question_answer. */
    public $answers = array();

    public function __construct() {
        parent::__construct(new question_first_matching_answer_grading_strategy($this));
    }

    public function get_expected_data() {
        return array('answer' => PARAM_RAW_TRIMMED);
    }

    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            return $response['answer'];
        } else {
            return null;
        }
    }

    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) &&
                ($response['answer'] || $response['answer'] === '0');
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_approxanswer');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer');
    }

    public function get_answers() {
        return $this->answers;
    }

    public function compare_response_with_answer(array $response, question_answer $answer) {
        if (!array_key_exists('answer', $response) || is_null($response['answer'])) {
            return false;
        }
        
        return $this->approximate_match($response['answer'], $answer->answer);
    }
    
    /**
     * Matches the the strings approximately.
     * @param type $response
     * @param type $correct_answer
     * @param type $error_margin
     * @return type
     */
    public function approximate_match($response, $correct_answer, $error_margin = NULL) {
        // Transliterate to ASCII, to work around the fact that the 
        // comparison algorithms only work with the latin character 
        // set.
        $response = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $response);
        $correct_answer = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $correct_answer);
        
        $correct_answer_met = metaphone($correct_answer);
        $response_met = metaphone($response);
        
        if (is_null($error_margin)) {
            $error_margin = round(strlen($correct_answer_met) / 5);
        }
        
        $distance = levenshtein($response_met, $correct_answer_met);
        
        return ($distance <= $error_margin);
    }
    
    /**
     * Checks if the answer gives a mark. This function is required by the 
     * renderer to find the answer that actually matched the user's 
     * response.
     * @param array $answer
     * @return bool
     */
    public function answer_is_correct($answer) {
        return (!is_null($this->gradingstrategy->grade($answer)));
    }

    public function get_correct_response() { 
        $response = parent::get_correct_response();
        
        if ($response) {
            $response['answer'] = $this->clean_response($response['answer']);
        }
        return $response;
    }
    
    public function clean_response($answer) {
        // Break the string on non-escaped asterisks.
        $bits = preg_split('/(?<!\\\\)\*/', $answer);

        // Unescape *s in the bits.
        $cleanbits = array();
        foreach ($bits as $bit) {
            $cleanbits[] = str_replace('\*', '*', $bit);
        }

        // Put it back together with spaces to look nice.
        return trim(implode(' ', $cleanbits));
    }

    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'answerfeedback') {
            $currentanswer = $qa->get_last_qt_var('answer');
            $answer = $qa->get_question()->get_matching_answer(array('answer' => $currentanswer));
            $answerid = reset($args); // itemid is answer id.
            return $options->feedback && $answer && $answerid == $answer->id;

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
}
