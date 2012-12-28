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
 * Approximate answer question type restore implementation.
 *
 * @package    qtype
 * @subpackage approxanswer
 * @copyright  2012 Sam Christy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restore plugin class that provides the necessary information
 * needed to restore one approxanswer qtype plugin.
 */
class restore_qtype_approxanswer_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        $paths = array();

        // This qtype uses question_answers, add them
        $this->add_question_question_answers($paths);

        // Add own qtype stuff
        $elename = 'approxanswer';
        // We used get_recommended_name() so that this works.
        $elepath = $this->get_pathfor('/approxanswer');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths
    }

    /**
     * Process the qtype/approxanswer element
     */
    public function process_approxanswer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;

        // If the question has been created by restore, we need to create its
        // question_approxanswer too, if they are defined (the GUI should ensure this).
        if ($questioncreated && !empty($data->answers)) {
            
            // Adjust some columns
            $data->question = $newquestionid;
            
            // Map sequence of question_answer ids
            $answersarr = explode(',', $data->answers);
            
            foreach ($answersarr as $key => $answer) {
                $answersarr[$key] = $this->get_mappingid('question_answer', $answer);
            }
            
            $data->answers = implode(',', $answersarr);
            // Insert record
            $newitemid = $DB->insert_record('qtype_approxanswer', $data);
            
            // Create mapping
            $this->set_mapping('qtype_approxanswer', $oldid, $newitemid);
        }
    }
}
