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
 * Renderable that initialises the grading "app".
 *
 * @package    mod_compatgrade
 * @copyright  2016 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_competgrade;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use renderable;
use templatable;
use stdClass;

/**
 * Grading app renderable.
 *
 * @package    mod_compatgrade
 * @since      Moodle 3.1
 * @copyright  2016 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grading_app implements templatable, renderable {

    /**
     * @var $userid - The initial user id.
     */
    public $userid = 0;

    /**
     * @var $groupid - The initial group id.
     */
    public $groupid = 0;

    /**
     * @var $competgrade - The competgrade instance.
     */
    public $competgrade = null;

    /**
     * @var $participants - The participants for this competgrade.
     */
    public $participants = [];

    /**
     * Constructor for this renderable.
     *
     * @param int $userid The user we will open the grading app too.
     * @param int $groupid If groups are enabled this is the current course group.
     * @param assign $competgrade The competgrade class
     */
    public function __construct($userid, $groupid, $competgrade) {
        $this->userid = $userid;
        $this->groupid = $groupid;
        $this->competgrade = $competgrade;
        $this->participants = $competgrade->student_list($groupid);
    }

    /**
     * Export this class data as a flat list for rendering in a template.
     *
     * @param renderer_base $output The current page renderer.
     * @return stdClass - Flat list of exported data.
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $USER;

        $template = new stdClass();
        $template->userid = $this->userid;
        $template->competgradeid = $this->competgrade->id;
        $template->participants = [];
        $template->participants[] = $this->participants;
        $template->count = count($template->participants);
        $template->courseurl = new \moodle_url('/course/view.php', ['id' => $this->competgrade->course->id]);
        $template->version = time();

        $template->coursename = $this->competgrade->course->fullname;
        return $template;
    }

}
