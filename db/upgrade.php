<?php
// Archivo de actualización de la base de datos para mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

defined('MOODLE_INTERNAL') || die();

function xmldb_alpysurvey_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    // Actualizaciones futuras aquí.
    return true;
}
