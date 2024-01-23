<?php
// This file is part of Moodle
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
 * Class scripts
 *
 * @package     mod_competgrade
 * @copyright   2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_competgrade;

require_once("$CFG->dirroot/completion/data_object.php");

defined('MOODLE_INTERNAL') || die();

class scripts extends \data_object {

    /* @var string Table that the class maps to in the database */
    public $table = 'competgrade_script';

    /* @var array Array of required table fields, must start with 'id'. */
    public $required_fields = [
            'id', 'competgrade', 'name', 'sortorder'
    ];

    public $optional_fields = [
        'timecreated' => 0
    ];

    /* @var int The primary key */
    public $id;

    /* @var string The sort order field */
    public $sortorder = 1;

    /**
     * @param $params
     * @return object marker scripts
     */
    public static function fetch($params) {
        return self::fetch_helper('competgrade_script', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @param bool $sort
     * @return array marker scripts
     */
    public static function fetch_all($params, $sort = false) {
        $ret = self::fetch_all_helper('competgrade_script', __CLASS__, $params);
        if (!$ret) {
            $ret = [];
        }
        if (count($ret)) {
            usort($ret, function($a, $b) {return $a->sortorder > $b->sortorder;});
        }
        return $ret;
    }

    public function save() {
        $result = false;
        if ($this->id) {
            $result = $this->update();
        } else {
            $result = $this->insert();
        }
        return $result ? true : false;
    }

    public function delete() {
        // Get all criteria for this script and delete them
        $criteria = criterium::fetch_all(['script' => $this->id]);
        foreach ($criteria as $criterion) {
            $criterion->delete();
        }
        return parent::delete();
    }
}
