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

namespace mod_competgrade;

/**
 * Class competgrade
 *
 * @package    mod_competgrade
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class competgrade {
    /**
     * @var int $id
     */
    public $id;

    /**
     * @var int $course
     */
    public $course;

    /**
     * @var int $cmid
     */
    public $cmid;

    /**
     * @var object $context
     */
    public $context;

    /**
     * @var int $instance
     */
    public $instance;

    public function __construct($cm) {
        global $DB;

        if ($cm) {
            $this->id = $cm->instance;
            $record = $DB->get_record('competgrade', ['id' => $cm->instance], '*', MUST_EXIST);
            $this->course = $DB->get_record('course', ['id' => $record->course], '*', MUST_EXIST);
            $this->context = \context_module::instance($cm->id);
        }
    }

    /**
     * Gets the list of students.
     * @return array list of students
     */
    public function student_list($groupid = 0) {
        $userfieldsapi = \core_user\fields::for_userpic()->including('idnumber');
        $namefields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $students = get_enrolled_users($this->context, 'mod/competgrade:grade',
            $groupid, $namefields);

        return array_values($students);
    }
}

