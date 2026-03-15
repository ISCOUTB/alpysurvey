<?php
// Reporte de respuestas para mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // course_module ID
$cm = get_coursemodule_from_id('alpysurvey', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$alpysurvey = $DB->get_record('alpysurvey', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/alpysurvey:viewreports', $context);

$export = optional_param('export', 0, PARAM_BOOL);

// Obtener respuestas
$responses = $DB->get_records('alpysurvey_responses', ['alpysurveyid'=>$alpysurvey->id]);
$users = $DB->get_records_list('user', 'id', array_map(function($r){return $r->userid;}, $responses));

// Exportar CSV
if ($export) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=alpysurvey_report.csv');
    $out = fopen('php://output', 'w');
    $headers = [
        get_string('report_student', 'mod_alpysurvey'),
        get_string('report_date', 'mod_alpysurvey')
    ];
    for ($i = 1; $i <= 16; $i++) {
        $headers[] = 'P' . $i;
    }
    $headers[] = get_string('report_score_d1', 'mod_alpysurvey');
    $headers[] = get_string('report_score_d2', 'mod_alpysurvey');
    $headers[] = get_string('report_score_d3', 'mod_alpysurvey');
    $headers[] = get_string('report_score_total', 'mod_alpysurvey');
    
    fputcsv($out, $headers);

    foreach ($responses as $r) {
        $user = $users[$r->userid];
        $answers = $DB->get_records('alpysurvey_answers', ['responseid'=>$r->id], 'questionnum ASC');
        
        $row = [fullname($user), userdate($r->timecreated)];
        
        // Mapear respuestas a columnas
        $answermap = [];
        foreach ($answers as $a) {
            $answermap[$a->questionnum] = ($a->questionnum == 8) ? $a->answertext : $a->answervalue;
        }
        
        for ($i = 1; $i <= 16; $i++) {
            $row[] = isset($answermap[$i]) ? $answermap[$i] : '';
        }
        
        $row[] = $r->score_d1;
        $row[] = $r->score_d2;
        $row[] = $r->score_d3;
        $row[] = $r->score_total;
        
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

$PAGE->set_url('/mod/alpysurvey/report.php', ['id'=>$cm->id]);
$PAGE->set_title(get_string('viewreport', 'mod_alpysurvey'));
$PAGE->set_heading($course->fullname);
$PAGE->requires->css('/mod/alpysurvey/styles.css');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('viewreport', 'mod_alpysurvey'));
echo $OUTPUT->single_button(new moodle_url('/mod/alpysurvey/report.php', ['id'=>$cm->id, 'export'=>1]), get_string('export_csv', 'mod_alpysurvey'));

// Tabla de respuestas
$table = new html_table();
$tablehead = [
    get_string('report_student', 'mod_alpysurvey'),
    get_string('report_date', 'mod_alpysurvey')
];
for ($i = 1; $i <= 16; $i++) {
    $tablehead[] = 'P' . $i;
}
$tablehead[] = get_string('report_score_d1', 'mod_alpysurvey');
$tablehead[] = get_string('report_score_d2', 'mod_alpysurvey');
$tablehead[] = get_string('report_score_d3', 'mod_alpysurvey');
$tablehead[] = get_string('report_score_total', 'mod_alpysurvey');
$tablehead[] = get_string('report_actions', 'mod_alpysurvey');

$table->head = $tablehead;

$stats = ['d1'=>[],'d2'=>[],'d3'=>[],'total'=>[]];
foreach ($responses as $r) {
    $user = $users[$r->userid];
    $answers = $DB->get_records('alpysurvey_answers', ['responseid'=>$r->id], 'questionnum ASC');
    
    $answermap = [];
    foreach ($answers as $a) {
        $answermap[$a->questionnum] = ($a->questionnum == 8) ? s($a->answertext) : $a->answervalue;
    }

    $row = [
        fullname($user),
        userdate($r->timecreated)
    ];
    
    for ($i = 1; $i <= 16; $i++) {
        $row[] = isset($answermap[$i]) ? $answermap[$i] : '';
    }

    $row[] = $r->score_d1;
    $row[] = $r->score_d2;
    $row[] = $r->score_d3;
    $row[] = $r->score_total;
    $row[] = html_writer::link(new moodle_url('/mod/alpysurvey/delete_response.php', [
            'id' => $cm->id,
            'responseid' => $r->id
        ]), $OUTPUT->pix_icon('t/delete', get_string('delete_response', 'mod_alpysurvey')));

    $table->data[] = $row;
    
    $stats['d1'][] = $r->score_d1;
    $stats['d2'][] = $r->score_d2;
    $stats['d3'][] = $r->score_d3;
    $stats['total'][] = $r->score_total;
}
echo html_writer::table($table);
// Estadísticas
if (count($responses)) {
    $mean = function($arr){return round(array_sum($arr)/count($arr),2);};
    $stddev = function($arr){$m=array_sum($arr)/count($arr);return round(sqrt(array_sum(array_map(function($v)use($m){return pow($v-$m,2);},$arr))/count($arr)),2);};
    echo html_writer::start_tag('div', ['class'=>'alpysurvey-stats']);
    echo get_string('report_mean', 'mod_alpysurvey').': ';
    echo 'D1: '.$mean($stats['d1']).' | D2: '.$mean($stats['d2']).' | D3: '.$mean($stats['d3']).' | Total: '.$mean($stats['total']);
    echo html_writer::empty_tag('br');
    echo get_string('report_stddev', 'mod_alpysurvey').': ';
    echo 'D1: '.$stddev($stats['d1']).' | D2: '.$stddev($stats['d2']).' | D3: '.$stddev($stats['d3']).' | Total: '.$stddev($stats['total']);
    echo html_writer::end_tag('div');
}
echo $OUTPUT->footer();
