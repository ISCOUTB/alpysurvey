<?php
// Script para borrar una respuesta de mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir.'/gradelib.php');

$id = required_param('id', PARAM_INT); // course_module ID
$responseid = required_param('responseid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('alpysurvey', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$alpysurvey = $DB->get_record('alpysurvey', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/alpysurvey:viewreports', $context);

$response = $DB->get_record('alpysurvey_responses', ['id' => $responseid, 'alpysurveyid' => $alpysurvey->id], '*', MUST_EXIST);

$returnurl = new moodle_url('/mod/alpysurvey/report.php', ['id' => $cm->id]);

if ($confirm && confirm_sesskey()) {
    $transaction = $DB->start_delegated_transaction();
    
    // 1. Borrar respuestas individuales
    $DB->delete_records('alpysurvey_answers', ['responseid' => $response->id]);
    
    // 2. Borrar el registro de respuesta
    $DB->delete_records('alpysurvey_responses', ['id' => $response->id]);
    
    // 3. Actualizar el libro de calificaciones para este usuario (asigna null/borra)
    $grade_item = grade_item::fetch([
        'itemtype' => 'mod',
        'itemmodule' => 'alpysurvey',
        'iteminstance' => $alpysurvey->id,
        'courseid' => $course->id
    ]);
    if ($grade_item) {
        $grade_item->delete_all_grades('mod/alpysurvey');
        // Alternativamente, forzar actualización para el usuario específico
        alpysurvey_update_grades($alpysurvey, $response->userid);
    }

    $transaction->allow_commit();
    redirect($returnurl);
}

$PAGE->set_url('/mod/alpysurvey/delete_response.php', ['id' => $cm->id, 'responseid' => $responseid]);
$PAGE->set_title(get_string('delete_response', 'mod_alpysurvey'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->confirm(
    get_string('delete_confirm', 'mod_alpysurvey'),
    new moodle_url('/mod/alpysurvey/delete_response.php', [
        'id' => $cm->id,
        'responseid' => $responseid,
        'confirm' => 1,
        'sesskey' => sesskey()
    ]),
    $returnurl
);
echo $OUTPUT->footer();
