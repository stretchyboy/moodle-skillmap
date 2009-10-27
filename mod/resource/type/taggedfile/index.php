<?php // $Id: index.php,v 1.27.2.5 2008/02/05 21:39:53 skodak Exp $

    require_once("../../../../config.php");
		$id = required_param( 'id', PARAM_INT ); // course

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_course_login($course, true);

    if ($course->id != SITEID) {
        require_login($course->id);
    }
    add_to_log($course->id, "resource", "view tagged", "index.php?id=$course->id", "");

    $strresource = get_string("modulename", "resource");
    $strresources = get_string("modulenameplural", "resource");
    $strweek = get_string("week");
    $strtopic = get_string("topic");
    $strname = get_string("name");
    $strsummary = get_string("summary");
    $strlastmodified = get_string("lastmodified");

    $navlinks = array();
    $navlinks[] = array('name' => $strresources, 'link' => '', 'type' => 'activityinstance');
    $navigation = build_navigation($navlinks);
		
		
		$iTag = optional_param( 'tagid', PARAM_INT ); // course
		
		if(!$iTag)
		{
		
			$sTag = optional_param( 'tags', PARAM_TAG ); // course
			if($sTag)
			{
				$aTags = tag_find_tags($sTags);
				
				if($aTags)
				{
					$oMinTag = array_shift($aTags);
					$iTag = $oMinTag->id;
				}
			}
		}
		
		$oTag = get_record('tag', 'id', $iTag);
		
		if(!$oTag)
		{
			//error not sure how yet
			
		}
    print_header("$course->shortname: $strresources", $course->fullname, $navigation,
                 "", "", true, "", navmenu($course));
		
		
		//Todo: print the description
		
		print_heading($oTag->name);
		
		if(isset($oTag->description) && $oTag->description)
		{
			print_box(format_text($oTag->description, FORMAT_MOODLE));
		}
		
    if (! $resources = get_all_tagged_instances_in_courses($oTag->name, "resource", array($course->id => $course))) {
        notice(get_string('thereareno', 'moodle', $strresources), "../../course/view.php?id=$course->id");
        exit;
    }

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname, $strsummary);
        $table->align = array ("center", "left", "left");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname, $strsummary);
        $table->align = array ("center", "left", "left");
    } else {
        $table->head  = array ($strlastmodified, $strname, $strsummary);
        $table->align = array ("left", "left", "left");
    }

    $currentsection = "";
    $options->para = false;
    foreach ($resources as $resource) {
        if ($course->format == "weeks" or $course->format == "topics") {
            $printsection = "";
            if ($resource->section !== $currentsection) {
                if ($resource->section) {
                    $printsection = $resource->section;
                }
                if ($currentsection !== "") {
                    $table->data[] = 'hr';
                }
                $currentsection = $resource->section;
            }
        } else {
            $printsection = '<span class="smallinfo">'.userdate($resource->timemodified)."</span>";
        }
        if (!empty($resource->extra)) {
            $extra = urldecode($resource->extra);
        } else {
            $extra = "";
        }
        if (!$resource->visible) {      // Show dimmed if the mod is hidden
            $table->data[] = array ($printsection, 
                    "<a class=\"dimmed\" $extra href=\"view.php?id=$resource->coursemodule\">".format_string($resource->name,true)."</a>",
                    format_text($resource->summary, FORMAT_MOODLE, $options) );

        } else {                        //Show normal if the mod is visible
            $table->data[] = array ($printsection, 
                    "<a $extra href=\"view.php?id=$resource->coursemodule\">".format_string($resource->name,true)."</a>",
                    format_text($resource->summary, FORMAT_MOODLE, $options) );
        }
    }

    echo "<br />";

    print_table($table);

    print_footer($course);
		
		
		exit;

		

/**
 * Returns an array of all the active instances of a particular module in given courses, sorted in the order they are defined
 *
 * Returns an array of all the active instances of a particular
 * module in given courses, sorted in the order they are defined
 * in the course. Returns an empty array on any errors.
 *
 * The returned objects includle the columns cw.section, cm.visible,
 * cm.groupmode and cm.groupingid, cm.groupmembersonly, and are indexed by cm.id.
 *
 * @param string $modulename The name of the module to get instances for
 * @param array $courses an array of course objects.
 * @return array of module instance objects, including some extra fields from the course_modules
 *          and course_sections tables, or an empty array if an error occurred.
 */
function get_all_tagged_instances_in_courses($sTag, $modulename, $courses, $userid=NULL, $includeinvisible=false) {
    global $CFG;
		
		
		
    $outputarray = array();

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return $outputarray;
    }
		

    if (!$rawmods = get_records_sql("SELECT cm.id AS coursemodule, m.*, cw.section, cm.visible AS visible,
                                            cm.groupmode, cm.groupingid, cm.groupmembersonly
                                       FROM {$CFG->prefix}course_modules cm,
                                            {$CFG->prefix}course_sections cw,
                                            {$CFG->prefix}modules md,
                                            {$CFG->prefix}$modulename m,
																						{$CFG->prefix}tag_instance ti,
																						{$CFG->prefix}tag t
                                      WHERE cm.course IN (".implode(',',array_keys($courses)).") AND
                                            cm.instance = m.id AND
                                            cm.section = cw.id AND
                                            md.name = '$modulename' AND
                                            md.id = cm.module AND
																						ti.itemid = cm.instance AND
																						ti.tagid = t.id AND 
																						t.name = '$sTag'")) {
        return $outputarray;
    }

    require_once($CFG->dirroot.'/course/lib.php');

    foreach ($courses as $course) {
        $modinfo = get_fast_modinfo($course, $userid);

        if (empty($modinfo->instances[$modulename])) {
            continue;
        }

        foreach ($modinfo->instances[$modulename] as $cm) {
            if (!$includeinvisible and !$cm->uservisible) {
                continue;
            }
            if (!isset($rawmods[$cm->id])) {
                continue;
            }
            $instance = $rawmods[$cm->id];
            if (!empty($cm->extra)) {
                $instance->extra = urlencode($cm->extra); // bc compatibility
            }
            $outputarray[] = $instance;
        }
    }

    return $outputarray;
}
?>
