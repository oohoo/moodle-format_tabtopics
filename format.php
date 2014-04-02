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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');

$PAGE->requires->js('/course/format/tabtopics/module.js');

// make sure all sections are created
$course = course_get_format($course)->get_course();
course_create_sections_if_missing($course, range(0, $course->numsections));
//Replace get_context_instance by the class for moodle 2.6+
if(class_exists('context_module'))
{
    $context = context_course::instance($course->id);
}
else
{
    $context = get_context_instance(CONTEXT_COURSE, $course->id);
}
$tabtopicsrenderer = $PAGE->get_renderer('format_tabtopics');
$corerenderer = $PAGE->get_renderer('core', 'course');
$isZeroTab = course_get_format($course)->is_section_zero_tab();
$is_remember_last_tab_session = course_get_format($course)->is_remember_last_tab_session();

$topic = optional_param('topic', -1, PARAM_INT);

$jsmodule = array(
    'name' => 'weekstabs',
    'fullpath' => '/course/format/tabtopics/module.js',
    'requires' => array('base', 'node', 'json', 'io', 'cookie')
);

//THIS IS THE CODE FOR GENERATING THE TABVIEW. ITS ONLY USED DURING NON EDITING
if (!$PAGE->user_is_editing())
{
    echo '<script type="text/javascript">
        var is_remember_last_tab_session = ' . ($is_remember_last_tab_session ? 'true' : 'false') . ';
    </script>';

    echo '
<style type="text/css" media="screen">
/* <![CDATA[ */
@import url("' . $CFG->wwwroot . '/course/format/tabtopics/tabtop.css");
/* ]]> */
</style>
';
    echo '
    <!--[if IE]>
    <div id = "maincontainer" style="display:">
    <![endif]-->

    <!--[if !IE]> <-->
    <div id = "maincontainer" style="display:none">
    <!--> <![endif]-->

    ';

    if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey())
    {
        $course->marker = $marker;
        $DB->set_field("course", "marker", $marker, array("id" => $course->id));
    }

    $streditsummary = get_string('editsummary');
    $stradd = get_string('add');
    $stractivities = get_string('activities');
    $strshowalltopics = get_string('showalltopics', 'format_tabtopics');
    $strtopic = get_string('topic');
    $strgroups = get_string('groups');
    $strgroupmy = get_string('groupmy');
    $editing = $PAGE->user_is_editing();

    if ($editing)
    {
        $strtopichide = get_string('hidetopicfromothers');
        $strtopicshow = get_string('showtopicfromothers');
        $strmarkthistopic = get_string('markthistopic');
        $strmarkedthistopic = get_string('markedthistopic');
        $strmoveup = get_string('moveup');
        $strmovedown = get_string('movedown');
    }

    // Print the Your progress icon if the track completion is enabled
    $completioninfo = new completion_info($course);
    echo $completioninfo->display_help_icon();


    /// If currently moving a file then show the current clipboard
    //not too sure what this does
    if (ismoving($course->id))
    {
        // Note, an ordered list would confuse - "1" could be the clipboard or summary.

        echo "<ul class='topicstabs'>\n";


        $stractivityclipboard = strip_tags(get_string('activityclipboard', '', $USER->activitycopyname));
        $strcancel = get_string('cancel');
        echo '<li class="clipboard">';
        echo $stractivityclipboard . '&nbsp;&nbsp;(<a href="mod.php?cancelcopy=true&amp;sesskey=' . sesskey() . '">' . $strcancel . '</a>)';
        echo "</li>\n";

        echo '</ul>';
    }

    //Insert the section 0
    $section = 0;
    $thissection = $sections[$section];

    if ($thissection->summary or $thissection->sequence or $PAGE->user_is_editing())
    {

        echo '<ul class="sectionul"><li id="sectiontd-0" class="section main yui3-dd-drop">';

        echo '<div class="content">';

        if (!empty($thissection->name))
        {
            echo $OUTPUT->heading(format_string($thissection->name, true, array('context' => $context)), 3, 'sectionname');
        }

        echo '<div class="summary">';

        //Replace get_context_instance by the class for moodle 2.6+
        if(class_exists('context_module'))
        {
            $coursecontext = context_course::instance($course->id);
        }
        else
        {
            $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
        }
        $summarytext = file_rewrite_pluginfile_urls($thissection->summary, 'pluginfile.php', $coursecontext->id, 'course', 'section', $thissection->id);
        $summaryformatoptions = new stdClass;
        $summaryformatoptions->noclean = true;
        $summaryformatoptions->overflowdiv = true;
        echo format_text($summarytext, $thissection->summaryformat, $summaryformatoptions);

        echo '</div>';

        //if section is not a tab, display as a header
        if (!$isZeroTab)
        {
            echo $corerenderer->course_section_cm_list($course, $thissection);
        }

        echo '</div>';
        echo "</li>";
    }

    //if section is a tab we want to start sections at 0, otherwise
    //section 0 is a header and we start at section 1
    $start_tab = $isZeroTab ? 0 : 1;

    /// Now all the normal modules by topic
    /// Everything below uses "section" terminology - each "section" is a topic.
    $timenow = time();
    $section = $start_tab;
    $sectionmenu = array();
    $num = $start_tab;
    echo '<div id="sections">'; //this is the first div that yui looks at, top node

    echo '<ul>'; //begining of the unordered list
    while ($section <= $course->numsections)
    {

        if (!empty($sections[$section]))
        {
            $thissection = $sections[$section];
        }
        else
        {
            $thissection = new stdClass;
            $thissection->course = $course->id;   // Create a new section structure
            $thissection->section = $section;
            $thissection->name = null;
            $thissection->summary = '';
            $thissection->summaryformat = FORMAT_HTML;
            $thissection->visible = 1;
        }

        //check if the current section is visible to user
        $unaval_override = course_get_format($course)->is_unavailable_override($thissection);

        //check if override is turned on (informs user section not avaliable)
        $user_access = course_get_format($course)->check_user_access($thissection);

        //if don't have access AND override "not avaliable" message not on - slip tab
        if (!$user_access && !$unaval_override)
        {
            $section++;
            continue;
        }

        //the default action is to set the name of each topic to null.
        $secname = $thissection->name;
        //this will set the name of undefined sections to a number. 

        if ($secname == null)
        {
            $secname = $secname . $num;
            $num++;
        }

        if (has_capability('moodle/course:viewhiddensections', $context) || $thissection->visible || (!$thissection->visible && $unaval_override))
        {   // Hidden for students
            if ($course->marker == $section)
                echo '<li id ="marker" class="markerselected"><a href="#section-' . $section . '" id = "marker" class="markerselected">' . $secname . '</a></li>';
            else
                echo '<li><a href="#section-' . $section . '">' . $secname . '</a></li>'; //prints each sectio
        }
        $section++;
    }
    echo '</ul>';

    echo '<div>'; //should be the div for content. 
    //this is the actual bits that we need.
    $section = $start_tab;
    $sectionmenu = array();
    $num = $start_tab;

    while ($section <= $course->numsections)
    {
        if (!empty($sections[$section]))
        {
            $thissection = $sections[$section];
        }
        else
        {
            $thissection = new stdClass;
            $thissection->course = $course->id;   // Create a new section structure
            $thissection->section = $section;
            $thissection->name = null;
            $thissection->summary = '';
            $thissection->summaryformat = FORMAT_HTML;
            $thissection->visible = 1;
            $thissection->id = $DB->insert_record('course_sections', $thissection);
        }

        //check if the current section is visible to user
        $unaval_override = course_get_format($course)->is_unavailable_override($thissection);

        //check if override is turned on (informs user section not avaliable)
        $user_access = course_get_format($course)->check_user_access($thissection);

        //if don't have access AND override "not avaliable" message not on - slip tab
        if (!$user_access && !$unaval_override)
        {
            $section++;
            continue;
        }

        //if user doesn't have access, but override is present - display not avaliable message
        if (!$user_access && $unaval_override)
        {
            echo '<div id="section-' . $section . '">';
            echo '<div class="right side"></div>';

            echo '<div class="content">';
            echo $tabtopicsrenderer->section_hidden($section);
            echo '</div>';
            echo '</div>';

            $section++;
            continue;
        }

        $showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);

        if (!empty($displaysection) and $displaysection != $section)
        {  // Check this topic is visible
            if ($showsection)
            {
                $sectionmenu[$section] = get_section_name($course, $thissection);
            }
            $section++;
            continue;
        }

        if ($showsection)
        {
            //what is course marker?
            $currenttopic = ($course->marker == $section);
            $currenttext = '';
            if (!$thissection->visible)
            {
                $sectionstyle = ' hidden';
            }
            else if ($currenttopic)
            {
                $sectionstyle = ' current';
                $currenttext = get_accesshide(get_string('currenttopic', 'format_tabtopics'));
            }
            else
            {
                $sectionstyle = '';
            }
            //the default action is to set the name of each topic to null.
            $secname = $thissection->name;
            //this will set the name of undefined sections to a number. 
            if ($secname == null)
            {
                $secname = $secname . $num;
                $num++;
            }

            if (has_capability('moodle/course:viewhiddensections', $context) || $thissection->visible)
            {
                echo '<div id="section-' . $section . '">';
                // Note, 'right side' is BEFORE content.
                echo '<div class="right side">';
                //Replace get_context_instance by the class for moodle 2.6+
                if(class_exists('context_module'))
                {
                    $context_check = context_course::instance($course->id);
                }
                else
                {
                    $context_check = get_context_instance(CONTEXT_COURSE, $course->id);
                }
                if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $context_check))
                {
                    if ($course->marker == $section)
                    {  // Show the "light globe" on/off
                        echo '<a href="view.php?id=' . $course->id . '&amp;marker=0&amp;sesskey=' . sesskey() . '#section-' . $section . '" title="' . $strmarkedthistopic . '">' . '<img src="' . $OUTPUT->pix_url('i/marked') . '" alt="' . $strmarkedthistopic . '" /></a><br />';
                    }
                    else
                    {
                        echo '<a href="view.php?id=' . $course->id . '&amp;marker=' . $section . '&amp;sesskey=' . sesskey() . '#section-' . $section . '" title="' . $strmarkthistopic . '">' . '<img src="' . $OUTPUT->pix_url('i/marker') . '" alt="' . $strmarkthistopic . '" /></a><br />';
                    }

                    if ($thissection->visible)
                    {   // Show the hide/show eye
                        echo '<a href="view.php?id=' . $course->id . '&amp;hide=' . $section . '&amp;sesskey=' . sesskey() . '#section-' . $section . '" title="' . $strtopichide . '">' .
                        '<img src="' . $OUTPUT->pix_url('i/hide') . '" class="icon hide" alt="' . $strtopichide . '" /></a><br />';
                    }
                    else
                    {
                        echo '<a href="view.php?id=' . $course->id . '&amp;show=' . $section . '&amp;sesskey=' . sesskey() . '#section-' . $section . '" title="' . $strtopicshow . '">' .
                        '<img src="' . $OUTPUT->pix_url('i/show') . '" class="icon hide" alt="' . $strtopicshow . '" /></a><br />';
                    }
                    if ($section > 1)
                    {   // Add a arrow to move section up
                        echo '<a href="view.php?id=' . $course->id . '&amp;random=' . rand(1, 10000) . '&amp;section=' . $section . '&amp;move=-1&amp;sesskey=' . sesskey() . '#section-' . ($section - 1) . '" title="' . $strmoveup . '">' .
                        '<img src="' . $OUTPUT->pix_url('t/up') . '" class="icon up" alt="' . $strmoveup . '" /></a><br />';
                    }

                    if ($section < $course->numsections)
                    {   // Add a arrow to move section down
                        echo '<a href="view.php?id=' . $course->id . '&amp;random=' . rand(1, 10000) . '&amp;section=' . $section . '&amp;move=1&amp;sesskey=' . sesskey() . '#section-' . ($section + 1) . '" title="' . $strmovedown . '">' .
                        '<img src="' . $OUTPUT->pix_url('t/down') . '" class="icon down" alt="' . $strmovedown . '" /></a><br />';
                    }
                }
                echo '</div>';

                echo '<div class="content">';
                if (!has_capability('moodle/course:viewhiddensections', $context) and !$thissection->visible)
                {   // Hidden for students
                    echo get_string('notavailable');
                }
                else
                {
                    $display = '';

                    //display section if avaliable
                    if (!is_null($thissection->name))
                        $display = $thissection->name;

                    //If not visible - show icon for people who can see it
                    if (!$thissection->visible)
                        $display .= "<img style='float:right' src='" . $OUTPUT->pix_url('i/show') . "'/>";

                    //output header for section
                    echo $OUTPUT->heading($display, 3, 'sectionname');

                    echo '<div class="summary">';
                    if ($thissection->summary)
                    {
                        //Replace get_context_instance by the class for moodle 2.6+
                        if(class_exists('context_module'))
                        {
                            $coursecontext = context_course::instance($course->id);
                        }
                        else
                        {
                            $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
                        }
                        $summarytext = file_rewrite_pluginfile_urls($thissection->summary, 'pluginfile.php', $coursecontext->id, 'course', 'section', $thissection->id);
                        $summaryformatoptions = new stdClass();
                        $summaryformatoptions->noclean = true;
                        $summaryformatoptions->overflowdiv = true;
                        echo format_text($summarytext, $thissection->summaryformat, $summaryformatoptions);
                    }
                    else
                    {
                        echo '&nbsp;';
                    }

                    //Replace get_context_instance by the class for moodle 2.6+
                    if(class_exists('context_module'))
                    {
                        $context_check = context_course::instance($course->id);
                    }
                    else
                    {
                        $context_check = get_context_instance(CONTEXT_COURSE, $course->id);
                    }
                    if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $context_check))
                    {
                        echo ' <a title="' . $streditsummary . '" href="editsection.php?id=' . $thissection->id . '">' .
                        '<img src="' . $OUTPUT->pix_url('t/edit') . '" class="icon edit" alt="' . $streditsummary . '" /></a><br /><br />';
                    }
                    echo '</div>';


                    echo $corerenderer->course_section_cm_list($course, $section);

                    echo '<br />';
                    if ($PAGE->user_is_editing())
                    {
                        echo $corerenderer->course_section_cm_list($course, $section);
                    }
                }

                //echo a conditional message if avaliable
                echo $tabtopicsrenderer->section_availability_message($thissection, has_capability('moodle/course:viewhiddensections', $context));

                echo '</div>';
                echo '</div>';
            }
        }
        unset($sections[$section]);
        $section++;
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';

    //Replace get_context_instance by the class for moodle 2.6+
    if(class_exists('context_module'))
    {
        $context_check = context_course::instance($course->id);
    }
    else
    {
        $context_check = get_context_instance(CONTEXT_COURSE, $course->id);
    }
    if (!$displaysection and $PAGE->user_is_editing() and has_capability('moodle/course:update', $context_check))
    {
        // print stealth sections if present
        $modinfo = get_fast_modinfo($course);
        foreach ($sections as $section => $thissection)
        {
            if (empty($modinfo->sections[$section]))
            {
                $section++;
                continue;
            }

            echo '<li id="section-' . $section . '" class="section main clearfix  yui3-dd-drop orphaned hidden">';

            echo '<div class="left side">';
            echo '</div>';
            // Note, 'right side' is BEFORE content.
            echo '<div class="right side">';
            echo '</div>';
            echo '<div class="content">';
            echo $OUTPUT->heading(get_string('orphanedactivities'), 3, 'sectionname');

            echo $corerenderer->course_section_cm_list($course, $thissection);

            echo '</div>';
            echo "</li>\n";
        }
    }

    echo "</ul>\n";

    $PAGE->requires->js_init_call('M.tabtopics.init', array($course->id, $isZeroTab), false, $jsmodule);

    if (!empty($sectionmenu))
    {
        $select = new single_select(new moodle_url('/course/view.php', array('id' => $course->id)), 'topic', $sectionmenu);
        $select->label = get_string('jumpto');
        $select->class = 'jumpmenu';
        $select->formid = 'sectionmenu';
        echo $OUTPUT->render($select);
    }
}
//this is the editing window
else
{
    // If Moodle 2.3 or more Generate the sections like the topics
    $renderer = $PAGE->get_renderer('format_topics');

    if (!empty($displaysection))
    {
        $renderer->print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection);
    }
    else
    {
        $renderer->print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused);
    }

    // Include course format js module
    $PAGE->requires->js('/course/format/topics/format.js');
}