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
 * Management module main user interface
 *
 * @package    mod_management
 * @copyright  2022 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/management/lib.php");

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
$m = optional_param('n', 0, PARAM_INT); // Management instance ID - it should be named as the first character of the module.

if ($id) {
    $cm = get_coursemodule_from_id('management', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $management = $DB->get_record('management', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($m) {
    $management = $DB->get_record('management', array('id' => $m), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $management->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('management', $management->id, $course->id, false, MUST_EXIST);
} else {
    throw new Exception('You must specify a course_module ID or an instance ID');
}

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/management:view', $context);

management_view($management, $course, $cm, $context);

// Print the page header.
$PAGE->set_url('/mod/management/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($management->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo html_writer::tag('h1', format_string($management->name));
echo html_writer::tag('p', format_string($management->intro));
echo $OUTPUT->footer();
