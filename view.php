<?php
// Entry point: vista principal de la actividad alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // course_module ID
$cm = get_coursemodule_from_id('alpysurvey', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$alpysurvey = $DB->get_record('alpysurvey', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Evento de visualización
\mod_alpysurvey\event\course_module_viewed::create([
    'objectid' => $alpysurvey->id,
    'context' => $context
])->trigger();

$PAGE->set_url('/mod/alpysurvey/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($alpysurvey->name));
$PAGE->set_heading($course->fullname);
$PAGE->requires->css('/mod/alpysurvey/styles.css');

// Comprobar si el usuario ya respondió
$hasresponse = $DB->record_exists('alpysurvey_responses', [
    'alpysurveyid' => $alpysurvey->id,
    'userid' => $USER->id
]);

// Mostrar mensaje si ya respondió
if ($hasresponse && !has_capability('mod/alpysurvey:viewreports', $context)) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('already_submitted', 'mod_alpysurvey'), 'info');
    // Mostrar puntajes
    $response = $DB->get_record('alpysurvey_responses', [
        'alpysurveyid' => $alpysurvey->id,
        'userid' => $USER->id
]);
    echo html_writer::start_tag('ul');
    echo html_writer::tag('li', get_string('dimension_d1', 'mod_alpysurvey') . ': ' . $response->score_d1);
    echo html_writer::tag('li', get_string('dimension_d2', 'mod_alpysurvey') . ': ' . $response->score_d2);
    echo html_writer::tag('li', get_string('dimension_d3', 'mod_alpysurvey') . ': ' . $response->score_d3);
    echo html_writer::tag('li', get_string('report_score_total', 'mod_alpysurvey') . ': ' . $response->score_total);
    echo html_writer::end_tag('ul');
    echo $OUTPUT->footer();
    exit;
}

// Si es profesor, mostrar enlace a reporte
if (has_capability('mod/alpysurvey:viewreports', $context)) {
    echo $OUTPUT->header();
    echo $OUTPUT->single_button(new moodle_url('/mod/alpysurvey/report.php', ['id' => $cm->id]), get_string('viewreport', 'mod_alpysurvey'));
    echo $OUTPUT->footer();
    exit;
}

// Mostrar formulario de respuestas
require_once($CFG->dirroot.'/mod/alpysurvey/locallib.php');
$form = new mod_alpysurvey_response_form(new moodle_url('/mod/alpysurvey/submit.php', ['id' => $cm->id]), [
    'cm' => $cm,
    'alpysurvey' => $alpysurvey
]);

echo $OUTPUT->header();
// Descripción
if (trim($alpysurvey->intro)) {
    echo $OUTPUT->box(format_module_intro('alpysurvey', $alpysurvey, $cm->id), 'generalbox mod_introbox', 'alpysurveyintro');
}
$form->display();
echo $OUTPUT->footer();
