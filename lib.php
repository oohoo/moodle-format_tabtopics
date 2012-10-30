<?php

/**
 * *************************************************************************
 * *                 OOHOO Tab topics Course format                       **
 * *************************************************************************
 * @package     format                                                    **
 * @subpackage  tabtopics                                                 **
 * @name        tabtopics                                                 **
 * @copyright   oohoo.biz                                                 **
 * @link        http://oohoo.biz                                          **
 * @author      Nicolas Bretin                                            **
 * @author      Braedan Jongerius                                         **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************ */

/**
 * Indicates this format uses sections.
 *
 * @return bool Returns true
 */
function callback_tabtopics_uses_sections()
{
    return true;
}

/**
 * Used to display the course structure for a course where format=topic
 *
 * This is called automatically by {@link load_course()} if the current course
 * format = weeks.
 *
 * @param array $path An array of keys to the course node in the navigation
 * @param stdClass $modinfo The mod info object for the current course
 * @return bool Returns true
 */
function callback_tabtopics_load_content(&$navigation, $course, $coursenode)
{
    return $navigation->load_generic_course_sections($course, $coursenode, 'tabtopics');
}

/**
 * The string that is used to describe a section of the course
 * e.g. Topic, Week...
 *
 * @return string
 */
function callback_tabtopics_definition()
{
    return get_string('topic');
}

/**
 * The GET argument variable that is used to identify the section being
 * viewed by the user (if there is one)
 *
 * @return string
 */
function callback_tabtopics_request_key()
{
    return 'topic';
}

function callback_tabtopics_get_section_name($course, $section)
{
    // We can't add a node without any text
    if (!empty($section->name))
    {
        return $section->name;
    }
    else if ($section->section == 0)
    {
        return get_string('section0name', 'format_tabtopics');
    }
    else
    {
        return get_string('topic') . ' ' . $section->section;
    }
}

/**
 * Declares support for course AJAX features
 *
 * @see course_format_ajax_support()
 * @return stdClass
 */
function callback_tabtopics_ajax_support()
{
    $ajaxsupport = new stdClass();
    $ajaxsupport->capable = true;
    $ajaxsupport->testedbrowsers = array('MSIE' => 6.0, 'Gecko' => 20061111, 'Safari' => 531, 'Chrome' => 6.0);
    return $ajaxsupport;
}
