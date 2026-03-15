<?php
// Restore task for mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/alpysurvey/backup/moodle2/restore_alpysurvey_stepslib.php');

class restore_alpysurvey_activity_task extends restore_activity_task {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        $this->add_step(new restore_alpysurvey_activity_structure_step('alpysurvey_structure', 'alpysurvey.xml'));
    }

    /**
     * Define the decoding rules for alpysurvey activity
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('ALPYSURVEYVIEWBYID', '/mod/alpysurvey/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('ALPY_SURVEY_INDEX', '/mod/alpysurvey/index.php?id=$1', 'course');

        return $rules;
    }

    /**
     * Define the decoding rules for alpysurvey activity in content
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('alpysurvey', array('intro'), 'alpysurvey');

        return $contents;
    }

    /**
     * Standard encode_content_links() counterpart
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('alpysurvey', 'add', 'view.php?id={course_module}', '{name}');
        $rules[] = new restore_log_rule('alpysurvey', 'update', 'view.php?id={course_module}', '{name}');
        $rules[] = new restore_log_rule('alpysurvey', 'view', 'view.php?id={course_module}', '{name}');

        return $rules;
    }

    /**
     * Standard encode_content_links() counterpart
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('alpysurvey', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
