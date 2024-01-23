<?php

// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of functions and constants for module competgrade
 *
 * @package     mod_competgrade
 * @copyright   2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/** competgrade_MAX_NAME_LENGTH = 50 */
define("competgrade_MAX_NAME_LENGTH", 50);

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know or string for the module purpose.
 */
function competgrade_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_MOD_PURPOSE:             return MOD_PURPOSE_CONTENT;

        default: return null;
    }
}

/**
 * @uses competgrade_MAX_NAME_LENGTH
 * @param object $competgrade
 * @return string
 */
function get_competgrade_name($competgrade) {
    $name = strip_tags(format_string($competgrade->intro,true));
    if (core_text::strlen($name) > competgrade_MAX_NAME_LENGTH) {
        $name = core_text::substr($name, 0, competgrade_MAX_NAME_LENGTH)."...";
    }

    if (empty($name)) {
        // arbitrary name
        $name = get_string('modulename','competgrade');
    }

    return $name;
}
/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $competgrade
 * @return bool|int
 */
function competgrade_add_instance($competgrade) {
    global $DB;

    $competgrade->name = get_competgrade_name($competgrade);
    $competgrade->timemodified = time();

    $id = $DB->insert_record("competgrade", $competgrade);

    return $id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $competgrade
 * @return bool
 */
function competgrade_update_instance($competgrade) {
    global $DB;

    $competgrade->name = get_competgrade_name($competgrade);
    $competgrade->timemodified = time();
    $competgrade->id = $competgrade->instance;

    return $DB->update_record("competgrade", $competgrade);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function competgrade_delete_instance($id) {
    global $DB;

    if (! $competgrade = $DB->get_record("competgrade", ['id' => $id])) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("competgrade", ['id' => $competgrade->id])) {
        $result = false;
    }

    return $result;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return cached_cm_info|null
 */
function competgrade_get_coursemodule_info($coursemodule) {
    global $DB;

    if ($competgrade = $DB->get_record('competgrade', ['id'=>$coursemodule->instance], 'id, name, intro, introformat')) {
        if (empty($competgrade->name)) {
            // competgrade name missing, fix it
            $competgrade->name = "competgrade{$competgrade->id}";
            $DB->set_field('competgrade', 'name', $competgrade->name, ['id'=>$competgrade->id]);
        }
        $info = new cached_cm_info();
        // no filtering hre because this info is cached and filtered later
        $info->content = format_module_intro('competgrade', $competgrade, $coursemodule->id, false);
        $info->name  = $competgrade->name;
        return $info;
    } else {
        return null;
    }
}

/**
 * Serves the competgrade starttext attachments.
 *
 * @param stdClass $course course object
 * @param cm_info $cm course module object
 * @param context $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function competgrade_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if ($filearea !== 'starttext') {
        return false;
    }

    // All users may access it.
    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';
    $fs = get_file_storage();
    if (!$file = $fs->get_file($context->id, 'mod_competgrade', 'starttext', 0, $filepath, $filename) or $file->is_directory()) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, null, 0, false, $options);
}