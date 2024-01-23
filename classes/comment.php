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

require_once("$CFG->dirroot/completion/data_object.php");

defined('MOODLE_INTERNAL') || die();

/**
 * Class grade
 *
 * @package    mod_competgrade
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comment extends \data_object {

    /* @var string Table that the class maps to in the database */
    public $table = 'competgrade_comment';

    /* @var array Array of required table fields, must start with 'id'. */
    public $required_fields = [
        'id', 'competgrade', 'authorid', 'userid', 'commenttitle', 'type', 'commenttext'
    ];

    public $optional_fields = [
        'course' => 0,
        'timemodified' => 0
    ];

    /* @var int The primary key */
    public $id;

    /**
    * @param $params
    * @return object compet grade comments
    */
    public static function fetch($params) {
        return self::fetch_helper('competgrade_comment', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @param bool $sort
     * @return array marker criteria
     */
    public static function fetch_all($params, $sort = false) {
        $ret = self::fetch_all_helper('competgrade_comment', __CLASS__, $params);
        if (!$ret) {
            $ret = [];
        }
        if (count($ret)) {
            usort($ret, function($a, $b) {return $a->userid <=> $b->userid;});
        }
        return $ret;
    }

    public function save() {
        if ($this->id) {
            $this->update();
        } else {
            $this->id = $this->insert();
        }
        return $this->id;
    }
};
