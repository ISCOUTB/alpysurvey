<?php
// Backup task for mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/alpysurvey/backup/moodle2/backup_alpysurvey_stepslib.php');

class backup_alpysurvey_activity_task extends backup_activity_task {
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
        $this->add_step(new backup_alpysurvey_activity_structure_step('alpysurvey_structure', 'alpysurvey.xml'));
    }

    /**
     * Code the transformations to perform in the activity in order to get tidy 8.3-style URLs
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of alpysurveys.
        $search = "/(" . $base . "\/mod\/alpysurvey\/index.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@ALPY_SURVEY_INDEX*$2@$', $content);

        // Link to alpysurvey view by moduleid.
        $search = "/(" . $base . "\/mod\/alpysurvey\/view.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@ALPYSURVEYVIEWBYID*$2@$', $content);

        return $content;
    }
}
