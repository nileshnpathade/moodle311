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

namespace block_course_modules;

/**
 * The course_modules test class.
 *
 * @package     block_course_modules
 * @category    test
 * @copyright   2022 Nilesh Pathade <nileshnpathade@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sample_test extends advanced_testcase {
    /**
     * Creates an HTML block on a course.
     *
     * @param \stdClass $course Course object
     * @return \block_html Block instance object
     */
    protected function create_block($course) {
        $page = self::construct_page($course);
        $page->blocks->add_block_at_end_of_default_region('html');

        // Load the block.
        $page = self::construct_page($course);
        $page->blocks->load_blocks();
        $blocks = $page->blocks->get_blocks_for_region($page->blocks->get_default_region());
        $block = end($blocks);
        return $block;
    }
}
