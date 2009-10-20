<?php  // $Id: view.php,v 1.102.2.8 2008/08/15 01:49:26 danmarsden Exp $

    require_once("../../config.php");
    require_once("lib.php");

    $id         = required_param('id', PARAM_INT);                 // Course Module ID
    $action     = optional_param('action', '', PARAM_ALPHA);
    $attemptids = optional_param('attemptid', array(), PARAM_INT); // array of attempt ids for delete action
    
    if (! $cm = get_coursemodule_from_id('skillmap', $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $course = get_record("course", "id", $cm->course)) {
        error("Course is misconfigured");
    }

    require_course_login($course, false, $cm);

    if (!$skillmap = skillmap_get_skillmap($cm->instance)) {
        error("Course module is incorrect");
    }
    
    $strskillmap = get_string('modulename', 'skillmap');
    $strskillmaps = get_string('modulenameplural', 'skillmap');

    if (!$context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
        print_error('badcontext');
    }

    if ($action == 'delskillmap') {
       /* if ($skillmap_responce = get_record('skillmap_responce', 'id', $skillmap->id, 'userid', $USER->id)) {
            //print_object($answer);
            delete_records('skillmap_answers', 'id', $answer->id);
        }*/
    }

/// Submit any new data if there is any

    if ($form = data_submitted() && has_capability('mod/skillmap:choose', $context)) {
				$timenow = time();
        if (has_capability('mod/skillmap:deleteresponses', $context)) {
            if ($action == 'delete') { //some responses need to be deleted     
                skillmap_delete_responses($attemptids, $skillmap->id); //delete responses.
                redirect("view.php?id=$cm->id");
            }
        }
				/*
				exit;
        $answer = optional_param('answer', '', PARAM_INT);

        if (empty($answer)) {
            redirect("view.php?id=$cm->id", get_string('mustchooseone', 'skillmap'));
        } else {
				*/
            skillmap_user_submit_response($skillmap, $USER->username, $course->id, $cm);
        //}
        redirect("view.php?id=$cm->id");
        exit;
    }

		// FIXME: With debugging on this is chucking a context error ????????????
		/// Display the skillmap and possibly results
    $navigation = build_navigation('', $cm);
    print_header_simple(format_string($skillmap->name), "", $navigation, "", "", true,
                  update_module_button($cm->id, $course->id, $strskillmap), navmenu($course, $cm));

    add_to_log($course->id, "skillmap", "view", "view.php?id=$cm->id", $skillmap->id, $cm->id);

    /// Check to see if groups are being used in this skillmap
    $groupmode = groups_get_activity_groupmode($cm);
    
    if ($groupmode) {
        groups_get_activity_group($cm, true);
        groups_print_activity_menu($cm, 'view.php?id='.$id);
    }
		
    $allresponses = skillmap_get_response_data($skillmap, $cm, $groupmode);   // Big function, approx 6 SQL calls per user

    
    if (has_capability('mod/skillmap:readresponses', $context)) {
        skillmap_show_reportlink($allresponses, $cm);
    }

    echo '<div class="clearer"></div>';

    if ($skillmap->text) {
        print_box(format_text($skillmap->text, $skillmap->format), 'generalbox', 'intro');
    }

    $current = false;  // Initialise for later
    //if user has already made a selection, and they are not allowed to update it, show their selected answer.
		/*if (!empty($USER->id) && ($current = get_record('skillmap_responce', 'survey', $skillmap->survey, 'username', $USER->username)) &&
        empty($skillmap->allowupdate) ) {
        print_simple_box(get_string("yourselection", "skillmap", userdate($skillmap->timeopen)).": ".format_string(skillmap_get_option_text($skillmap, $current->optionid)), "center");
    }*/
		
		// TODO: add something for when there are no answers

/// Print the form
    $skillmapopen = true;
    $timenow = time();
    if ($skillmap->timeclose !=0) {
        if ($skillmap->timeopen > $timenow ) {
            print_simple_box(get_string("notopenyet", "skillmap", userdate($skillmap->timeopen)), "center");
            print_footer($course);
            exit;
        } else if ($timenow > $skillmap->timeclose) {
            print_simple_box(get_string("expired", "skillmap", userdate($skillmap->timeclose)), "center");
            $skillmapopen = false;
        }
    }

    if ( (!$current or $skillmap->allowupdate) and $skillmapopen and
          has_capability('mod/skillmap:choose', $context) ) {
    // They haven't made their skillmap yet or updates allowed and skillmap is open

        echo '<form id="form" method="post" action="view.php">';        

        skillmap_show_form($skillmap, $USER, $cm, $allresponses);

        echo '</form>';

        $skillmapformshown = true;
    } else {
        $skillmapformshown = false;
    }



    if (!$skillmapformshown) {

        $sitecontext = get_context_instance(CONTEXT_SYSTEM);

        if (has_capability('moodle/legacy:guest', $sitecontext, NULL, false)) {      // Guest on whole site
            $wwwroot = $CFG->wwwroot.'/login/index.php';
            if (!empty($CFG->loginhttps)) {
                $wwwroot = str_replace('http:','https:', $wwwroot);
            }
            notice_yesno(get_string('noguestchoose', 'skillmap').'<br /><br />'.get_string('liketologin'),
                         $wwwroot, $_SERVER['HTTP_REFERER']);

        } else if (has_capability('moodle/legacy:guest', $context, NULL, false)) {   // Guest in this course only
            $SESSION->wantsurl = $FULLME;
            $SESSION->enrolcancel = $_SERVER['HTTP_REFERER'];

            print_simple_box_start('center', '60%', '', 5, 'generalbox', 'notice');
            echo '<p align="center">'. get_string('noguestchoose', 'skillmap') .'</p>';
            echo '<div class="continuebutton">';
            print_single_button($CFG->wwwroot.'/course/enrol.php?id='.$course->id, NULL, 
                                get_string('enrolme', '', format_string($course->shortname)), 'post', $CFG->framename);
            echo '</div>'."\n";
            print_simple_box_end();

        }
    }

    // print the results at the bottom of the screen

    if ( $skillmap->showresults == SKILLMAP_SHOWRESULTS_ALWAYS or
        ($skillmap->showresults == SKILLMAP_SHOWRESULTS_AFTER_ANSWER and $current ) or
        ($skillmap->showresults == SKILLMAP_SHOWRESULTS_AFTER_CLOSE and !$skillmapopen ) )  {

        skillmap_show_results($skillmap, $course, $cm, $allresponses); //show table with students responses.

    } else if (!$skillmapformshown) {
        print_simple_box(get_string('noresultsviewable', 'skillmap'), 'center');
    } 


    print_footer($course);


?>
