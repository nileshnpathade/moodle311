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
 * virtual external functions and service definitions.
 *
 * @package    mod_virtual
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_virtual_view_virtual' => array(
        'classname'     => 'mod_virtual_external',
        'methodname'    => 'view_virtual',
        'description'   => 'Simulate the view.php web interface virtual: trigger events, completion, etc...',
        'type'          => 'write',
        'capabilities'  => 'mod/virtual:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_virtual_get_virtuals_by_courses' => array(
        'classname'     => 'mod_virtual_external',
        'methodname'    => 'get_virtuals_by_courses',
        'description'   => 'Returns a list of virtuals in a provided list of courses, if no list is provided all virtuals that the user
                            can view will be returned.',
        'type'          => 'read',
        'capabilities'  => 'mod/virtual:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);
