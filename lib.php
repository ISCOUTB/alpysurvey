<?php
// Funciones principales para mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

defined('MOODLE_INTERNAL') || die();

/**
 * Agrega una nueva instancia de alpysurvey
 * @param object $data
 * @param object $mform
 * @return int id de la nueva instancia
 */
function alpysurvey_add_instance($data, $mform) {
    global $DB;
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $id = $DB->insert_record('alpysurvey', $data);
    alpysurvey_grade_item_update($data);
    return $id;
}

/**
 * Actualiza una instancia existente de alpysurvey
 * @param object $data
 * @param object $mform
 * @return bool
 */
function alpysurvey_update_instance($data, $mform) {
    global $DB;
    $data->timemodified = time();
    $data->id = $data->instance;
    $DB->update_record('alpysurvey', $data);
    alpysurvey_grade_item_update($data);
    return true;
}

/**
 * Elimina una instancia de alpysurvey
 * @param int $id
 * @return bool
 */
function alpysurvey_delete_instance($id) {
    global $DB;
    if (!$alpysurvey = $DB->get_record('alpysurvey', ['id' => $id])) {
        return false;
    }
    $responses = $DB->get_records('alpysurvey_responses', ['alpysurveyid' => $id]);
    foreach ($responses as $response) {
        $DB->delete_records('alpysurvey_answers', ['responseid' => $response->id]);
    }
    $DB->delete_records('alpysurvey_responses', ['alpysurveyid' => $id]);
    $DB->delete_records('alpysurvey', ['id' => $id]);
    alpysurvey_grade_item_update($alpysurvey, null);
    return true;
}

/**
 * Indica las características soportadas por el módulo
 * @param string $feature
 * @return mixed
 */
function alpysurvey_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_BACKUP_MOODLE2: return true;
        case FEATURE_SHOW_DESCRIPTION: return true;
        case FEATURE_GRADE_HAS_GRADE: return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES: return false;
        default: return null;
    }
}

/**
 * Actualiza el ítem de calificación en el gradebook
 * @param object $alpysurvey
 * @param mixed $grades
 * @return void
 */
function alpysurvey_grade_item_update($alpysurvey, $grades=null) {
    require_once(__DIR__.'/../../lib/gradelib.php');
    $params = [
        'itemname' => clean_param($alpysurvey->name, PARAM_NOTAGS),
        'gradetype' => GRADE_TYPE_VALUE,
        'grademax' => $alpysurvey->grade,
        'grademin' => 0
    ];
    grade_update('mod/alpysurvey', $alpysurvey->course, 'mod', 'alpysurvey', $alpysurvey->id, 0, $grades, $params);
}

/**
 * Envía calificaciones al gradebook
 * @param object $alpysurvey
 * @param int $userid
 * @param bool $nullifnone
 * @return void
 */
function alpysurvey_update_grades($alpysurvey, $userid=0, $nullifnone=true) {
    global $DB;
    require_once(__DIR__.'/../../lib/gradelib.php');
    $where = ['alpysurveyid' => $alpysurvey->id];
    if ($userid) {
        $where['userid'] = $userid;
    }
    $responses = $DB->get_records('alpysurvey_responses', $where);
    $grades = [];
    foreach ($responses as $r) {
        $grades[$r->userid] = [
            'userid' => $r->userid,
            'rawgrade' => ($r->score_total / 5) * $alpysurvey->grade
        ];
    }
    grade_update('mod/alpysurvey', $alpysurvey->course, 'mod', 'alpysurvey', $alpysurvey->id, 0, $grades);
}
