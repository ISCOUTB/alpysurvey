<?php
// Backup steps for mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

defined('MOODLE_INTERNAL') || die();

class backup_alpysurvey_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
        $alpysurvey = new backup_nested_element('alpysurvey', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'timecreated', 'timemodified', 'timeopen', 'timeclose', 'grade'
        ));
        $responses = new backup_nested_element('responses');
        $response = new backup_nested_element('response', array('id'), array(
            'userid', 'timecreated', 'score_d1', 'score_d2', 'score_d3', 'score_total'
        ));
        $answers = new backup_nested_element('answers');
        $answer = new backup_nested_element('answer', array('id'), array(
            'questionnum', 'answervalue', 'answertext'
        ));
        $alpysurvey->add_child($responses);
        $responses->add_child($response);
        $response->add_child($answers);
        $answers->add_child($answer);
        $response->set_source_table('alpysurvey_responses', array('alpysurveyid' => backup::VAR_ACTIVITYID));
        $answer->set_source_table('alpysurvey_answers', array('responseid' => backup::VAR_PARENTID));
        $alpysurvey->set_source_table('alpysurvey', array('id' => backup::VAR_ACTIVITYID));
        return $this->prepare_activity_structure($alpysurvey);
    }
}
