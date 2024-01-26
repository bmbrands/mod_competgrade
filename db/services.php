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
 * competgrade external functions and service definitions.
 *
 * @package     mod_competgrade
 * @copyright   2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [
    'mod_competgrade_grade' => array(
        'classname'   => 'mod_competgrade\external',
        'methodname'  => 'grade',
        'description' => 'Save a grade',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'mod_competgrade_deletegrade' => array(
        'classname'   => 'mod_competgrade\external',
        'methodname'  => 'deletegrade',
        'description' => 'Delete a grade',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'mod_competgrade_userlist' => array(
        'classname'   => 'mod_competgrade\external',
        'methodname'  => 'userlist',
        'description' => 'Get the list of users and their grades',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'mod_competgrade_usercomments' => array(
        'classname'   => 'mod_competgrade\external',
        'methodname'  => 'usercomments',
        'description' => 'Get the list of student and appraiser comments',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'mod_competgrade_getcomment' => array(
        'classname'   => 'mod_competgrade\external',
        'methodname'  => 'get_comment',
        'description' => 'Get the list of student and appraiser comments',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'mod_competgrade_certification' => array(
        'classname'   => 'mod_competgrade\external',
        'methodname'  => 'certification',
        'description' => 'Get the certification data for a user',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'mod_competgrade_comment' => array(
        'classname'   => 'mod_competgrade\external',
        'methodname'  => 'comment',
        'description' => 'Save or update a comment',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'mod_competgrade_deletecomment' => array(
        'classname'   => 'mod_competgrade\external',
        'methodname'  => 'delete_comment',
        'description' => 'Delete a comment',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
];
