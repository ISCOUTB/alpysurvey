<?php
// Restore steps for mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/alpysurvey/backup/moodle2/restore_stepslib.php');

class restore_alpysurvey_activity_structure_step extends restore_activity_structure_step {
    protected function define_structure() {
        $paths = array();
        $paths[] = new restore_path_element('alpysurvey', '/activity/alpysurvey');
        $paths[] = new restore_path_element('alpysurvey_response', '/activity/alpysurvey/responses/response');
        $paths[] = new restore_path_element('alpysurvey_answer', '/activity/alpysurvey/responses/response/answers/answer');
        return $paths;
    }
    protected function process_alpysurvey($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record('alpysurvey', $data);
        $this->set_activityid($newitemid);
    }
    protected function process_alpysurvey_response($data) {
        global $DB;
        $data = (object)$data;
        $data->alpysurveyid = $this->get_new_parentid('alpysurvey');
        $DB->insert_record('alpysurvey_responses', $data);
    }
    protected function process_alpysurvey_answer($data) {
        global $DB;
        $data = (object)$data;
        $data->responseid = $this->get_new_parentid('alpysurvey_response');
        $DB->insert_record('alpysurvey_answers', $data);
    }
    protected function after_execute() {
        // Nada especial.
    }
}
