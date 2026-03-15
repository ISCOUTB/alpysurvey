<?php
// Privacy API provider for mod_alpysurvey
// @package   mod_alpysurvey
// @copyright 2026
// @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

namespace mod_alpysurvey\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\deletion_criteria;

/**
 * Privacy provider for mod_alpysurvey
 */
class provider implements \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Describe the types of personal data stored by this plugin.
     * @param collection $items
     * @return collection
     */
    public static function get_metadata(collection $items) : collection {
        $items->add_database_table('alpysurvey_responses', [
            'userid' => 'privacy:metadata:userid',
            'score_d1' => 'privacy:metadata:score_d1',
            'score_d2' => 'privacy:metadata:score_d2',
            'score_d3' => 'privacy:metadata:score_d3',
            'score_total' => 'privacy:metadata:score_total',
        ], 'privacy:metadata:alpysurvey_responses');
        $items->add_database_table('alpysurvey_answers', [
            'questionnum' => 'privacy:metadata:questionnum',
            'answervalue' => 'privacy:metadata:answervalue',
            'answertext' => 'privacy:metadata:answertext',
        ], 'privacy:metadata:alpysurvey_answers');
        return $items;
    }

    /**
     * Get list of contexts containing user information for the user.
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextmodule
                  JOIN {modules} m ON m.id = cm.module AND m.name = 'alpysurvey'
                  JOIN {alpysurvey} a ON a.id = cm.instance
                  JOIN {alpysurvey_responses} r ON r.alpysurveyid = a.id
                 WHERE r.userid = :userid";
        $params = [
            'contextmodule' => CONTEXT_MODULE,
            'userid' => $userid
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Export all user data for the contexts.
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }
            $cm = get_coursemodule_from_id('alpysurvey', $context->instanceid);
            if (!$cm) continue;
            $responses = $DB->get_records('alpysurvey_responses', [
                'alpysurveyid' => $cm->instance,
                'userid' => $contextlist->get_user()->id
            ]);
            foreach ($responses as $response) {
                $answers = $DB->get_records('alpysurvey_answers', ['responseid' => $response->id]);
                $data = [
                    'scores' => [
                        'score_d1' => $response->score_d1,
                        'score_d2' => $response->score_d2,
                        'score_d3' => $response->score_d3,
                        'score_total' => $response->score_total
                    ],
                    'answers' => $answers
                ];
                writer::with_context($context)->export_data(['alpysurvey'], (object)$data);
            }
        }
    }

    /**
     * Delete all user data for the contexts.
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }
        $cm = get_coursemodule_from_id('alpysurvey', $context->instanceid);
        if (!$cm) return;
        $responses = $DB->get_records('alpysurvey_responses', ['alpysurveyid' => $cm->instance]);
        foreach ($responses as $response) {
            $DB->delete_records('alpysurvey_answers', ['responseid' => $response->id]);
        }
        $DB->delete_records('alpysurvey_responses', ['alpysurveyid' => $cm->instance]);
    }

    /**
     * Delete all user data for a specific user in a context.
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }
            $cm = get_coursemodule_from_id('alpysurvey', $context->instanceid);
            if (!$cm) continue;
            $responses = $DB->get_records('alpysurvey_responses', [
                'alpysurveyid' => $cm->instance,
                'userid' => $contextlist->get_user()->id
            ]);
            foreach ($responses as $response) {
                $DB->delete_records('alpysurvey_answers', ['responseid' => $response->id]);
            }
            $DB->delete_records('alpysurvey_responses', [
                'alpysurveyid' => $cm->instance,
                'userid' => $contextlist->get_user()->id
            ]);
        }
    }
}
