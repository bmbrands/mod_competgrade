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
 * External API.
 *
 * @package    mod_competgrade
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_competgrade;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_warnings;
use context_module;
use moodle_exception;
use external_single_structure;
use external_multiple_structure;
use invalid_parameter_exception;
use user_picture;

/**
 * External API class.
 *
 * @package    mod_competgrade
 * @copyright  2021 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function grade_parameters() {
        return new external_function_parameters(
            array(
                'competgrade' => new external_value(PARAM_INT, 'Competgrade ID', VALUE_DEFAULT, 0),
                'criterium' => new external_value(PARAM_INT, 'Criterium ID', VALUE_DEFAULT, 0),
                'gradeid' => new external_value(PARAM_INT, 'Grade ID', VALUE_DEFAULT, 0),
                'userid' => new external_value(PARAM_INT, 'User ID', VALUE_DEFAULT, 0),
                'grade' => new external_value(PARAM_INT, 'grade', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Get courses matching the given timeline classification.
     *
     * @param  int $competgrade Competgrade ID
     * @param  int $criterium Criterium ID
     * @param  int $gradeid Grade ID
     * @param  int $userid User ID
     * @param  int $grade Grade
     * @return bool success
     */
    public static function grade(int $competgrade, int $criterium, int $gradeid, int $userid, int $grade) {
        global $USER;

        $params = self::validate_parameters(self::grade_parameters(),
            [
                'competgrade' => $competgrade,
                'criterium' => $criterium,
                'gradeid' => $gradeid,
                'userid' => $userid,
                'grade' => $grade,
            ]
        );

        $competgrade = $params['competgrade'];
        $criterium = $params['criterium'];
        $grade = $params['grade'];

        $cm  = get_coursemodule_from_instance('competgrade', $competgrade, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/competgrade:viewallgrades', $context);

        $criterium = \mod_competgrade\criterium::fetch(['id' => $criterium]);

        if (!$criterium) {
            $criterium = new \mod_competgrade\criterium([
                'competgrade' => $competgrade->id,
                'name' => 'Temp criterium',
                'script' => '0',
            ]);
        }

        $usercontext = \context_user::instance($USER->id);

        self::validate_context($usercontext);

        $params['type'] = 1;
        $params['id'] = $gradeid;
        $params['timemodified'] = time();

        $gradeobj = new \mod_competgrade\grade($params);
        $gradeid = $gradeobj->save();

        return [
            'gradeid' => $gradeid,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function grade_returns() {
        return new external_single_structure(
            array(
                'gradeid' => new external_value(PARAM_INT, 'Grade ID'),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.6
     */
    public static function deletegrade_parameters() {
        return new external_function_parameters([
            'competgrade' => new external_value(PARAM_INT, 'competgrade ID', VALUE_DEFAULT, 0),
            'criterium' => new external_value(PARAM_INT, 'Criterium ID', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Delete a competgrade grade.
     *
     * @param int $competgrade competgrade ID
     * @param int $criterium Question ID
     *
     * @return  array of warnings
     */
    public static function deletegrade(int $competgrade, int $criterium) {
        global $DB, $USER;

        $params = self::validate_parameters(self::deletegrade_parameters(), [
            'competgrade' => $competgrade,
            'criterium' => $criterium,
        ]);

        $competgrade = $params['competgrade'];
        $criterium = $params['criterium'];

        if (!$DB->record_exists('competgrade', array('id' => $competgrade))) {
            throw new moodle_exception('Bad competgrade number ' . $competgrade);
        }

        $cm  = get_coursemodule_from_instance('competgrade', $competgrade, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/competgrade:grade', $context);

        $warnings = [];

        $params['userid'] = $USER->id;

        $gradeobj = new \mod_competgrade\grade($params);
        $gradeobj->delete();

        $warnings = [];

        return ['warnings' => $warnings];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.6
     */
    public static function deletegrade_returns() {
        return new external_single_structure(
            array(
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function userlist_parameters() {
        return new external_function_parameters(
            array(
                'competgrade' => new external_value(PARAM_INT, 'competgrade ID', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Get courses matching the given timeline classification.
     *
     * @param int $competgrade Competgrade ID
     * @return array Competgrade users and grades.
     * @throws  invalid_parameter_exception
     */
    public static function userlist(int $competgrade) {
        global $USER, $PAGE;

        $params = self::validate_parameters(self::grade_parameters(),
            ['competgrade' => $competgrade]
        );

        $competgrade = $params['competgrade'];

        $usercontext = \context_user::instance($USER->id);

        self::validate_context($usercontext);

        $cm = get_coursemodule_from_instance('competgrade', $competgrade, 0, false, MUST_EXIST);

        $competgrade = new \mod_competgrade\competgrade($cm);

        $userlist = $competgrade->student_list();
        $grades = \mod_competgrade\grade::fetch_all(['competgrade' => $competgrade->id]);
        // Get the user picture for each user.
        foreach ($userlist as $key => $user) {
            $userpicture = new user_picture($user);
            $userlist[$key]->picture = $userpicture->get_url($PAGE)->out(false);
            $userpicture->size = 200;
            $userlist[$key]->picturelarge = $userpicture->get_url($PAGE)->out(false);
            $userlist[$key]->fullname = fullname($user);
            // Find the grade for this user.
            foreach ($grades as $grade) {
                if ($grade->userid == $user->id) {
                    $userlist[$key]->gradeid = $grade->id;
                    $userlist[$key]->grade = $grade->grade;
                }
            }
            if (!isset($userlist[$key]->gradeid)) {
                $userlist[$key]->gradeid = 0;
                $userlist[$key]->grade = 0;
            }
        }

        $success = 1;

        return [
            'success' => $success,
            'userlist' => $userlist,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function userlist_returns() {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_INT, '1 for success'),
                'userlist' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'User ID'),
                            'picture' => new external_value(PARAM_RAW, 'User picture'),
                            'picturelarge' => new external_value(PARAM_RAW, 'User picture large'),
                            'firstname' => new external_value(PARAM_TEXT, 'User first name'),
                            'lastname' => new external_value(PARAM_TEXT, 'User last name'),
                            'firstnamephonetic' => new external_value(PARAM_TEXT, 'User first name phonetic'),
                            'lastnamephonetic' => new external_value(PARAM_TEXT, 'User last name phonetic'),
                            'middlename' => new external_value(PARAM_TEXT, 'User middle name'),
                            'alternatename' => new external_value(PARAM_TEXT, 'User alternate name'),
                            'imagealt' => new external_value(PARAM_TEXT, 'User image alt'),
                            'email' => new external_value(PARAM_TEXT, 'User email'),
                            'idnumber' => new external_value(PARAM_TEXT, 'User idnumber'),
                            'fullname' => new external_value(PARAM_TEXT, 'User fullname'),
                            'gradeid' => new external_value(PARAM_INT, 'Grade ID'),
                            'grade' => new external_value(PARAM_INT, 'User grade'),
                        )
                    )
                ),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function usercomments_parameters() {
        return new external_function_parameters(
            array(
                'competgrade' => new external_value(PARAM_INT, 'competgrade ID', VALUE_DEFAULT, 0),
                'userid' => new external_value(PARAM_INT, 'User ID', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Get courses matching the given timeline classification.
     *
     * @param int $competgrade Competgrade ID
     * @param int $userid User ID
     * @return array Competgrade usercomments and appraisercomments.
     * @throws  invalid_parameter_exception
     */
    public static function usercomments(int $competgrade, int $userid) {
        global $USER, $PAGE;

        $params = self::validate_parameters(self::grade_parameters(),
            [
                'competgrade' => $competgrade,
                'userid' => $userid,
            ]
        );

        $competgrade = $params['competgrade'];
        $userid = $params['userid'];

        $usercontext = \context_user::instance($USER->id);

        self::validate_context($usercontext);

        $cm = get_coursemodule_from_instance('competgrade', $competgrade, 0, false, MUST_EXIST);

        $competgrade = new \mod_competgrade\competgrade($cm);

        $commentsrecords = \mod_competgrade\comment::fetch_all(['competgrade' => $competgrade->id, 'userid' => $userid, 'type' => 1]);
        $appraisercomments = [];
        $usercomments = [];

        foreach ($commentsrecords as $comment) {
            if (intval($comment->authorid) === $userid) {
                if (array_key_exists($comment->authorid, $usercomments)) {
                    $usercomments[$comment->authorid]->comments[] = $comment;
                } else {
                    $usercomments[$comment->authorid] = new \stdClass();
                    $usercomments[$comment->authorid]->fullname = fullname(\core_user::get_user($comment->authorid));
                    $picture = new user_picture(\core_user::get_user($comment->authorid));
                    $usercomments[$comment->authorid]->picture = $picture->get_url($PAGE)->out(false);
                    $usercomments[$comment->authorid]->comments = [];
                    $usercomments[$comment->authorid]->comments[] = $comment;
                }
            } else {
                if (array_key_exists($comment->authorid, $appraisercomments)) {
                    $appraisercomments[$comment->authorid]->comments[] = $comment;
                } else {
                    $appraisercomments[$comment->authorid] = new \stdClass();
                    $appraisercomments[$comment->authorid]->fullname = fullname(\core_user::get_user($comment->authorid));
                    $picture = new user_picture(\core_user::get_user($comment->authorid));
                    $appraisercomments[$comment->authorid]->picture = $picture->get_url($PAGE)->out(false);
                    $appraisercomments[$comment->authorid]->comments = [];
                    $appraisercomments[$comment->authorid]->comments[] = $comment;
                }
            }
        }

        $comments = new \stdClass();
        $comments->usercomments = array_values($usercomments);
        $comments->appraisercomments = array_values($appraisercomments);

        return [
            'comments' => $comments,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function usercomments_returns() {
        return new external_single_structure(
            array(
                'comments' => new external_single_structure(
                    array(
                        'usercomments' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'fullname' => new external_value(PARAM_TEXT, 'Fullname'),
                                    'picture' => new external_value(PARAM_RAW, 'User picture'),
                                    'comments' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'id' => new external_value(PARAM_INT, 'Comment ID'),
                                                'competgrade' => new external_value(PARAM_INT, 'Competgrade ID'),
                                                'authorid' => new external_value(PARAM_INT, 'Author ID'),
                                                'userid' => new external_value(PARAM_INT, 'User ID'),
                                                'commenttitle' => new external_value(PARAM_TEXT, 'Comment title'),
                                                'commenttext' => new external_value(PARAM_TEXT, 'Comment'),
                                                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                                            )
                                        )
                                    ),
                                )
                            )
                        ),
                        'appraisercomments' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'fullname' => new external_value(PARAM_TEXT, 'Fullname'),
                                    'picture' => new external_value(PARAM_RAW, 'User picture'),
                                    'comments' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'id' => new external_value(PARAM_INT, 'Comment ID'),
                                                'competgrade' => new external_value(PARAM_INT, 'Competgrade ID'),
                                                'authorid' => new external_value(PARAM_INT, 'Author ID'),
                                                'userid' => new external_value(PARAM_INT, 'User ID'),
                                                'commenttitle' => new external_value(PARAM_TEXT, 'Comment title'),
                                                'commenttext' => new external_value(PARAM_TEXT, 'Comment'),
                                                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                                            )
                                        )
                                    ),
                                )
                            )
                        ),
                    )
                ),
            )
        );
    }

    /**
     * Returns description of method parameters
     */
    public static function comment_parameters() {
        return new external_function_parameters(
            array(
                'commentid' => new external_value(PARAM_INT, 'Comment ID', VALUE_DEFAULT, 0),
                'competgrade' => new external_value(PARAM_INT, 'competgrade ID', VALUE_DEFAULT, 0),
                'userid' => new external_value(PARAM_INT, 'User ID', VALUE_DEFAULT, 0),
                'type' => new external_value(PARAM_INT, 'Type', VALUE_DEFAULT, 0),
                'commenttitle' => new external_value(PARAM_TEXT, 'Comment title', VALUE_DEFAULT, ''),
                'commenttext' => new external_value(PARAM_TEXT, 'Comment', VALUE_DEFAULT, ''),
            )
        );
    }

    /**
     * Comment on a competgrade grade.
     *
     * @param int $commentid Comment ID
     * @param int $competgrade competgrade ID
     * @param int $userid User ID
     * @param int $type Type
     * @param string $commenttitle Comment title
     * @param string $commenttext Comment
     */
    public static function comment(int $commentid, int $competgrade, int $userid, int $type, string $commenttitle, string $commenttext) {
        global $USER;

        $params = self::validate_parameters(self::comment_parameters(),
            [
                'commentid' => $commentid,
                'competgrade' => $competgrade,
                'userid' => $userid,
                'type' => $type,
                'commenttitle' => $commenttitle,
                'commenttext' => $commenttext,
            ]
        );

        $competgrade = $params['competgrade'];
        $userid = $params['userid'];
        $type = $params['type'];
        $commenttitle = $params['commenttitle'];
        $commenttext = $params['commenttext'];

        $cm  = get_coursemodule_from_instance('competgrade', $competgrade, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/competgrade:grade', $context);

        $usercontext = \context_user::instance($USER->id);

        self::validate_context($usercontext);

        $params['authorid'] = $USER->id;
        $params['timemodified'] = time();

        $commentsrecord = null;

        if ($commentid) {
            $commentsrecord = \mod_competgrade\comment::fetch(['id' => $commentid]);
            $commentsrecord->commenttitle = $commenttitle;
            $commentsrecord->commenttext = $commenttext;
        } else {
            $commentsrecord = new \mod_competgrade\comment([
                'competgrade' => $competgrade,
                'userid' => $userid,
                'type' => $type,
                'commenttitle' => $commenttitle,
                'commenttext' => $commenttext,
                'authorid' => $USER->id,
            ]);

        }

        $commentsrecord->save();

        return [
            'commentid' => $commentsrecord->id,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function comment_returns() {
        return new external_single_structure(
            array(
                'commentid' => new external_value(PARAM_INT, 'Comment ID'),
            )
        );
    }

    /**
     * Returns description of method parameters
     */
    public static function delete_comment_parameters() {
        return new external_function_parameters(
            array(
                'commentid' => new external_value(PARAM_INT, 'Comment ID', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Delete a competgrade comment.
     *
     * @param int $commentid Comment ID
     */
    public static function delete_comment(int $commentid) {
        global $USER;

        $params = self::validate_parameters(self::delete_comment_parameters(),
            [
                'commentid' => $commentid,
            ]
        );

        $commentid = $params['commentid'];

        $commentsrecord = \mod_competgrade\comment::fetch(['id' => $commentid]);

        if (!$commentsrecord) {
            throw new moodle_exception('Bad comment number ' . $commentid);
        }

        $cm  = get_coursemodule_from_instance('competgrade', $commentsrecord->competgrade, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/competgrade:grade', $context);

        $usercontext = \context_user::instance($USER->id);

        self::validate_context($usercontext);

        $commentsrecord->delete();

        return [
            'commentid' => $commentid,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function delete_comment_returns() {
        return new external_single_structure(
            array(
                'commentid' => new external_value(PARAM_INT, 'Comment ID'),
            )
        );
    }

    /**
     * Returns description of method parameters
     */
    public static function get_comment_parameters() {
        return new external_function_parameters(
            array(
                'competgrade' => new external_value(PARAM_INT, 'competgrade ID', VALUE_DEFAULT, 0),
                'userid' => new external_value(PARAM_INT, 'User ID', VALUE_DEFAULT, 0),
                'type' => new external_value(PARAM_INT, 'Type', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Get a single competgrade comment.
     *
     * @param int $competgrade Competgrade ID
     * @param int $userid User ID
     * @param int $type Type
     */
    public static function get_comment(int $competgrade, int $userid, int $type) {
        global $USER;

        $params = self::validate_parameters(self::get_comment_parameters(),
            [
                'competgrade' => $competgrade,
                'userid' => $userid,
                'type' => $type,
            ]
        );

        $competgrade = $params['competgrade'];
        $userid = $params['userid'];
        $type = $params['type'];

        $cm  = get_coursemodule_from_instance('competgrade', $competgrade, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        require_capability('mod/competgrade:grade', $context);

        $usercontext = \context_user::instance($USER->id);

        self::validate_context($usercontext);

        $commentsrecord = \mod_competgrade\comment::fetch(['competgrade' => $competgrade, 'userid' => $userid, 'type' => $type]);

        return [
            'commentid' => $commentsrecord->id,
            'commenttitle' => $commentsrecord->commenttitle,
            'commenttext' => $commentsrecord->commenttext,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_comment_returns() {
        return new external_single_structure(
            array(
                'commentid' => new external_value(PARAM_INT, 'Comment ID'),
                'commenttitle' => new external_value(PARAM_TEXT, 'Comment title'),
                'commenttext' => new external_value(PARAM_TEXT, 'Comment'),
            )
        );
    }

}