<?php
// Lógica y formulario de respuestas para mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

require_once($CFG->libdir.'/formslib.php');

class mod_alpysurvey_response_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;
        $cm = $this->_customdata['cm'];
        $alpysurvey = $this->_customdata['alpysurvey'];

        // Hidden fields
        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);

        // Dimensiones y preguntas
        $likertlabels = [
            1 => get_string('likert_1', 'mod_alpysurvey'),
            2 => get_string('likert_2', 'mod_alpysurvey'),
            3 => get_string('likert_3', 'mod_alpysurvey'),
            4 => get_string('likert_4', 'mod_alpysurvey'),
            5 => get_string('likert_5', 'mod_alpysurvey'),
        ];
        // D1: Q1-Q7
        $mform->addElement('header', 'd1', get_string('dimension_d1', 'mod_alpysurvey'));
        $this->add_likert_table($mform, range(1,7), $likertlabels);
        // Q8: Ranking
        $mform->addElement('header', 'q8', get_string('question8', 'mod_alpysurvey'));
        $this->add_ranking_matrix($mform);
        // D2: Q9-Q11
        $mform->addElement('header', 'd2', get_string('dimension_d2', 'mod_alpysurvey'));
        $this->add_likert_table($mform, range(9,11), $likertlabels);
        // D3: Q12-Q16
        $mform->addElement('header', 'd3', get_string('dimension_d3', 'mod_alpysurvey'));
        $this->add_likert_table($mform, range(12,16), $likertlabels);
        $this->add_action_buttons(false, get_string('submit', 'mod_alpysurvey'));
    }

        private function add_likert_table($mform, $qnums, $labels) {
            $html = '<table class="alpysurvey-likert-table"><thead><tr><th></th>';
            foreach ($labels as $val => $label) {
                $html .= '<th>' . $label . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($qnums as $qnum) {
                $name = 'q'.$qnum;
                $question = get_string('question'.$qnum, 'mod_alpysurvey');
                $html .= '<tr><td class="alpysurvey-likert-label">' . $question . '</td>';
                foreach ($labels as $val => $label) {
                    $id = $name.'_'.$val;
                    $html .= '<td><input type="radio" name="'.$name.'" id="'.$id.'" value="'.$val.'" /></td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            $mform->addElement('html', $html);
        }

    private function add_likert_header($mform, $labels) {
        $html = '<div class="alpysurvey-likert-header" style="display:flex;justify-content:flex-end;gap:18px;margin-bottom:4px;">';
        foreach ($labels as $val => $label) {
            $html .= '<span style="min-width:70px;text-align:center;font-weight:600;color:#1976d2;">' . $label . '</span>';
        }
        $html .= '</div>';
        $mform->addElement('html', $html);
    }
    private function add_likert_row($mform, $qnum, $labels, $showlabels = false) {
        $name = 'q'.$qnum;
        $question = get_string('question'.$qnum, 'mod_alpysurvey');
        $html = '<div class="alpysurvey-likert-item">';
        $html .= '<div class="alpysurvey-likert-label">' . $question . '</div>';
        $html .= '<div class="alpysurvey-likert-options">';
        foreach ($labels as $val => $label) {
            $id = $name.'_'.$val;
            $html .= '<label for="'.$id.'" style="display:inline-block;">';
            $html .= '<input type="radio" name="'.$name.'" id="'.$id.'" value="'.$val.'" /> ';
            if ($showlabels) {
                $html .= $label;
            }
            $html .= '</label>';
        }
        $html .= '</div></div>';
        $mform->addElement('html', $html);
    }
    private function add_ranking_matrix($mform) {
        $mform->addElement('header', 'rankinghdr', get_string('dimension_d1', 'mod_alpysurvey'));
        $mform->addElement('static', 'ranking_instr', '', get_string('ranking_instructions', 'mod_alpysurvey'));
        
        // El usuario pidió un campo de texto simple en lugar de arrastrar y soltar
        $mform->addElement('textarea', 'q8_text', get_string('question8', 'mod_alpysurvey'), 'wrap="virtual" rows="5" cols="50"');
        $mform->addRule('q8_text', get_string('required'), 'required', null, 'client');
        $mform->setType('q8_text', PARAM_CLEANHTML);
    }
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // La validación de Q8 ahora se maneja con la regla 'required' en definition()
        return $errors;
    }
}
