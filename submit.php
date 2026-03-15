<?php
// Procesamiento de envío de respuestas para mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

require_once('../../config.php');
require_once('lib.php');
require_once('locallib.php');

$id = required_param('id', PARAM_INT); // course_module ID
$cm = get_coursemodule_from_id('alpysurvey', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$alpysurvey = $DB->get_record('alpysurvey', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Solo un intento por usuario
if ($DB->record_exists('alpysurvey_responses', ['alpysurveyid'=>$alpysurvey->id, 'userid'=>$USER->id])) {
    redirect(new moodle_url('/mod/alpysurvey/view.php', ['id'=>$cm->id]), get_string('already_submitted', 'mod_alpysurvey'));
}

$PAGE->set_url(new moodle_url('/mod/alpysurvey/submit.php', ['id' => $cm->id]));

$form = new mod_alpysurvey_response_form(null, [
    'cm' => $cm,
    'alpysurvey' => $alpysurvey
]);
if (!$form->is_submitted()) {
    redirect(new moodle_url('/mod/alpysurvey/view.php', ['id'=>$cm->id]));
}
// Note: We use optional_param for qX because they are manual HTML in locallib.php and not standard mform elements.
$data = $form->get_data();

// Validar y procesar respuestas
$transaction = $DB->start_delegated_transaction();
$now = time();
// Calcular promedios
$likert_d1 = [];
$likert_d2 = [];
$likert_d3 = [];
for ($i=1; $i<=7; $i++) $likert_d1[] = optional_param('q'.$i, 0, PARAM_INT);
for ($i=9; $i<=11; $i++) $likert_d2[] = optional_param('q'.$i, 0, PARAM_INT);
for ($i=12; $i<=16; $i++) $likert_d3[] = optional_param('q'.$i, 0, PARAM_INT);

$score_d1 = count($likert_d1) ? round(array_sum($likert_d1)/count($likert_d1),2) : 0;
$score_d2 = count($likert_d2) ? round(array_sum($likert_d2)/count($likert_d2),2) : 0;
$score_d3 = count($likert_d3) ? round(array_sum($likert_d3)/count($likert_d3),2) : 0;
$score_total = round(($score_d1+$score_d2+$score_d3)/3,2);
// Insertar en alpysurvey_responses
$response = (object)[
    'alpysurveyid' => $alpysurvey->id,
    'userid' => $USER->id,
    'timecreated' => $now,
    'score_d1' => $score_d1,
    'score_d2' => $score_d2,
    'score_d3' => $score_d3,
    'score_total' => $score_total
];
$responseid = $DB->insert_record('alpysurvey_responses', $response);
// Guardar respuestas individuales
for ($i=1; $i<=7; $i++) {
    $DB->insert_record('alpysurvey_answers', [
        'responseid'=>$responseid,
        'questionnum'=>$i,
        'answervalue'=>optional_param('q'.$i, 0, PARAM_INT),
        'answertext'=>null
    ]);
}
// Q8: text answer
$DB->insert_record('alpysurvey_answers', [
    'responseid'=>$responseid,
    'questionnum'=>8,
    'answervalue'=>null,
    'answertext'=>isset($data->q8_text) ? $data->q8_text : ''
]);
for ($i=9; $i<=11; $i++) {
    $DB->insert_record('alpysurvey_answers', [
        'responseid'=>$responseid,
        'questionnum'=>$i,
        'answervalue'=>optional_param('q'.$i, 0, PARAM_INT),
        'answertext'=>null
    ]);
}
for ($i=12; $i<=16; $i++) {
    $DB->insert_record('alpysurvey_answers', [
        'responseid'=>$responseid,
        'questionnum'=>$i,
        'answervalue'=>optional_param('q'.$i, 0, PARAM_INT),
        'answertext'=>null
    ]);
}
// Actualizar gradebook
alpysurvey_update_grades($alpysurvey, $USER->id);
// Evento
\mod_alpysurvey\event\response_submitted::create([
    'objectid' => $responseid,
    'context' => $context,
    'userid' => $USER->id
])->trigger();
$transaction->allow_commit();
redirect(new moodle_url('/course/view.php', ['id'=>$course->id]), get_string('submit_success', 'mod_alpysurvey'));
