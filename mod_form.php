<?php
// Formulario de configuración de la actividad alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_alpysurvey_mod_form extends moodleform_mod {
    /**
     * Define el formulario de configuración
     */
    public function definition() {
        $mform = $this->_form;
        // Nombre
        $mform->addElement('text', 'name', get_string('modulename', 'mod_alpysurvey'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        // Intro
        $this->standard_intro_elements();
        // Fechas
        $mform->addElement('date_time_selector', 'timeopen', get_string('availablefrom', 'mod_alpysurvey'), ['optional' => true]);
        $mform->addElement('date_time_selector', 'timeclose', get_string('availableto', 'mod_alpysurvey'), ['optional' => true]);
        // Calificación
        $mform->addElement('text', 'grade', get_string('grade'), ['size' => '4']);
        $mform->setType('grade', PARAM_INT);
        $mform->setDefault('grade', 100);
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
