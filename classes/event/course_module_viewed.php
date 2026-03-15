<?php
// Evento: course_module_viewed para mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

namespace mod_alpysurvey\event;

defined('MOODLE_INTERNAL') || die();

class course_module_viewed extends \core\event\course_module_viewed {
    /**
     * Inicializa los datos del evento.
     */
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'alpysurvey';
    }
}
