<?php
// Entry point: lista de instancias de alpysurvey en el curso
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // course id
$course = get_course($id);
require_login($course);
$PAGE->set_url('/mod/alpysurvey/index.php', ['id' => $id]);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('modulenameplural', 'mod_alpysurvey'));
$PAGE->set_heading($course->fullname);

// Listar todas las instancias en el curso
$alpysurveys = get_all_instances_in_course('alpysurvey', $course);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_alpysurvey'));
if (empty($alpysurveys)) {
    echo get_string('none');
} else {
    $table = new html_table();
    $table->head = [get_string('name'), get_string('intro', 'mod_alpysurvey')];
    foreach ($alpysurveys as $s) {
        $table->data[] = [
            html_writer::link(new moodle_url('/mod/alpysurvey/view.php', ['id' => $s->coursemodule]), format_string($s->name)),
            format_module_intro('alpysurvey', $s, $s->coursemodule)
        ];
    }
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
