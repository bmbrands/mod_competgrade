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
 * Prints an instance of mod_competgrade.
 *
 * @package     mod_competgrade
 * @copyright   2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$t = optional_param('t', 0, PARAM_INT);

$groupid  = optional_param('groupid', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('competgrade', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('competgrade', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('competgrade', array('id' => $t), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('competgrade', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_competgrade\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));

$competgrade = new \mod_competgrade\competgrade($cm);
$grading_app = new \mod_competgrade\grading_app($USER->id, $groupid, $competgrade);

$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('competgrade', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/competgrade/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_pagelayout('embedded');
$PAGE->activityheader->disable();

$competgrade = $DB->get_record('competgrade', ['id' => $cm->instance], '*', MUST_EXIST);

$renderer = $PAGE->get_renderer('mod_competgrade');

echo $OUTPUT->header();
echo $renderer->render($grading_app);
echo $OUTPUT->footer();
