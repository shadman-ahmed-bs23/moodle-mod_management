<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Management module library function.
 *
 * @package    mod_management
 * @copyright  2022 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const MANAGEMENT_TABLE_NAME = 'management';

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@see plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function management_supports(string $feature): ?bool {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_MOD_INTRO:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GRADE_OUTCOMES:
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
            return false;
        default: return null;
    }
}

/**
 * Add html5player instance.
 * @param object $data
 * @param object $mform
 * @return int new url instance id
 */
function management_add_instance($data, $mform) {
    global $CFG, $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    $data->id = $DB->insert_record(MANAGEMENT_TABLE_NAME, $data);

    return $data->id;
}

/**
 * Update html5player instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 * @throws Throwable
 * @throws coding_exception
 * @throws dml_transaction_exception
 */
function management_update_instance($data, $mform) {
    global $CFG, $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    $DB->update_record(MANAGEMENT_TABLE_NAME, $data);

    return true;
}

/**
 * Delete html5player instance.
 * @param int $id
 * @return bool true
 * @throws Throwable
 * @throws coding_exception
 * @throws dml_exception
 * @throws dml_transaction_exception
 */
function management_delete_instance($id) {
    global $DB;

    if (!$html5player = $DB->get_record(MANAGEMENT_TABLE_NAME, array('id'=>$id))) {
        return false;
    }
    // Delete record from html5player instance
    $DB->delete_records(HTML5_TABLE_NAME, array('id'=>$html5player->id));

    return true;
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $management management object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function management_view($management, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $management->id
    );

    $event = \mod_management\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('page', $management);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}
