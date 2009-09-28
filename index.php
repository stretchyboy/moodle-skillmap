<?php  // $Id: index.php,v 1.32.2.6 2008/02/26 23:19:05 skodak Exp $

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id',PARAM_INT);   // course

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_course_login($course);

    add_to_log($course->id, "skillmap", "view all", "index?id=$course->id", "");

    $strskillmap = get_string("modulename", "skillmap");
    $strskillmaps = get_string("modulenameplural", "skillmap");
    $navlinks = array();
    $navlinks[] = array('name' => $strskillmaps, 'link' => '', 'type' => 'activity');
    $navigation = build_navigation($navlinks);

    print_header_simple("$strskillmaps", "", $navigation, "", "", true, "", navmenu($course));


    if (! $skillmaps = get_all_instances_in_course("skillmap", $course)) {
        notice(get_string('thereareno', 'moodle', $strskillmaps), "../../course/view.php?id=$course->id");
    }

    $sql = "SELECT cha.*
              FROM {$CFG->prefix}skillmap ch, {$CFG->prefix}skillmap_answers cha
             WHERE cha.skillmapid = ch.id AND
                   ch.course = $course->id AND cha.userid = $USER->id";

    $answers = array () ;
    if (isloggedin() and !isguestuser() and $allanswers = get_records_sql($sql)) {
        foreach ($allanswers as $aa) {
            $answers[$aa->skillmapid] = $aa;
        }
        unset($allanswers);
    }


    $timenow = time();

    if ($course->format == "weeks") {
        $table->head  = array (get_string("week"), get_string("question"), get_string("answer"));
        $table->align = array ("center", "left", "left");
    } else if ($course->format == "topics") {
        $table->head  = array (get_string("topic"), get_string("question"), get_string("answer"));
        $table->align = array ("center", "left", "left");
    } else {
        $table->head  = array (get_string("question"), get_string("answer"));
        $table->align = array ("left", "left");
    }

    $currentsection = "";

    foreach ($skillmaps as $skillmap) {
        if (!empty($answers[$skillmap->id])) {
            $answer = $answers[$skillmap->id];
        } else {
            $answer = "";
        }
        if (!empty($answer->optionid)) {
            $aa = format_string(skillmap_get_option_text($skillmap, $answer->optionid));
        } else {
            $aa = "";
        }
        $printsection = "";
        if ($skillmap->section !== $currentsection) {
            if ($skillmap->section) {
                $printsection = $skillmap->section;
            }
            if ($currentsection !== "") {
                $table->data[] = 'hr';
            }
            $currentsection = $skillmap->section;
        }
        
        //Calculate the href
        if (!$skillmap->visible) {
            //Show dimmed if the mod is hidden
            $tt_href = "<a class=\"dimmed\" href=\"view.php?id=$skillmap->coursemodule\">".format_string($skillmap->name,true)."</a>";
        } else {
            //Show normal if the mod is visible
            $tt_href = "<a href=\"view.php?id=$skillmap->coursemodule\">".format_string($skillmap->name,true)."</a>";
        }
        if ($course->format == "weeks" || $course->format == "topics") {
            $table->data[] = array ($printsection, $tt_href, $aa);
        } else {
            $table->data[] = array ($tt_href, $aa);
        }
    }
    echo "<br />";
    print_table($table);

    print_footer($course);

?>
