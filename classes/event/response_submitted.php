<?php
// Evento: response_submitted para mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

namespace mod_alpysurvey\event;

defined('MOODLE_INTERNAL') || die();

class response_submitted extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'alpysurvey_responses';
    }
    public static function get_name() {
        return get_string('eventresponse_submitted', 'mod_alpysurvey');
    }
    public function get_description() {
        return "El usuario con id {$this->userid} envió una respuesta en la actividad alpysurvey con id {$this->contextinstanceid}.";
    }
    public function get_url() {
        return new \moodle_url('/mod/alpysurvey/view.php', ['id' => $this->contextinstanceid]);
    }
}
