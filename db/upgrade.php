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
 * competgrade module upgrade
 *
 * @package mod_competgrade
 * @copyright  2006 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_competgrade_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024011900) {

        // Define table competgrade_comment to be created.
        $table = new xmldb_table('competgrade_comment');

        // Adding fields to table competgrade_comment.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('competgrade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('authorid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('comment', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table competgrade_comment.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for competgrade_comment.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Competgrade savepoint reached.
        upgrade_mod_savepoint(true, 2024011900, 'competgrade');
    }

    if ($oldversion < 2024011902) {

        // Define field commenttitle to be added to competgrade_comment.
        $table = new xmldb_table('competgrade_comment');
        $field = new xmldb_field('commenttitle', XMLDB_TYPE_TEXT, null, null, null, null, null, 'authorid');

        // Conditionally launch add field commenttitle.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Rename field commenttext on table competgrade_comment to NEWNAMEGOESHERE.
        $table = new xmldb_table('competgrade_comment');
        $field = new xmldb_field('comment', XMLDB_TYPE_TEXT, null, null, null, null, null, 'commenttitle');

        // Launch rename field commenttext.
        $dbman->rename_field($table, $field, 'commenttext');

        // Competgrade savepoint reached.
        upgrade_mod_savepoint(true, 2024011902, 'competgrade');
    }

    if ($oldversion < 2024011903) {

        // Define field type to be added to competgrade_comment.
        $table = new xmldb_table('competgrade_comment');
        $field = new xmldb_field('type', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'commenttext');

        // Conditionally launch add field type.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Competgrade savepoint reached.
        upgrade_mod_savepoint(true, 2024011903, 'competgrade');
    }

    return true;
}
