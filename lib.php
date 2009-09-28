<?php // $Id: lib.php,v 1.59.2.24 2008/08/19 05:17:14 tjhunt Exp $

$COLUMN_HEIGHT = 300;

define('SKILLMAP_PUBLISH_ANONYMOUS', '0');
define('SKILLMAP_PUBLISH_NAMES',     '1');

define('SKILLMAP_SHOWRESULTS_NOT',          '0');
define('SKILLMAP_SHOWRESULTS_AFTER_ANSWER', '1');
define('SKILLMAP_SHOWRESULTS_AFTER_CLOSE',  '2');
define('SKILLMAP_SHOWRESULTS_ALWAYS',       '3');

define('SKILLMAP_DISPLAY_HORIZONTAL',  '0');
define('SKILLMAP_DISPLAY_VERTICAL',    '1');

$SKILLMAP_PUBLISH = array (SKILLMAP_PUBLISH_ANONYMOUS  => get_string('publishanonymous', 'skillmap'),
                         SKILLMAP_PUBLISH_NAMES      => get_string('publishnames', 'skillmap'));

$SKILLMAP_SHOWRESULTS = array (SKILLMAP_SHOWRESULTS_NOT          => get_string('publishnot', 'skillmap'),
                         SKILLMAP_SHOWRESULTS_AFTER_ANSWER => get_string('publishafteranswer', 'skillmap'),
                         SKILLMAP_SHOWRESULTS_AFTER_CLOSE  => get_string('publishafterclose', 'skillmap'),
                         SKILLMAP_SHOWRESULTS_ALWAYS       => get_string('publishalways', 'skillmap'));

$SKILLMAP_DISPLAY = array (SKILLMAP_DISPLAY_HORIZONTAL   => get_string('displayhorizontal', 'skillmap'),
                         SKILLMAP_DISPLAY_VERTICAL     => get_string('displayvertical','skillmap'));

/// Standard functions /////////////////////////////////////////////////////////

function skillmap_user_outline($course, $user, $mod, $skillmap) {
    if ($responce = get_record('skillmap_responce', 'survey', $skillmap->survey, 'username', $user->username)) {
				$result = new Object();
				
				if ($responces = get_records('skillmap_responce_skill', 'responce', $responce->id)) {
					debugging($skillmap);
					debugging($responce);
					debugging($responces);
						$result->info = '';
						foreach($responces as $oCurrentResponce)
						{
							$result->info .=  "'".format_string(skillmap_get_option_text($skillmap, $responce->optionid))."'";
						}
				
						$result->time = $responce->timemodified;
						return $result;
				}
		}
    return NULL;
}


function skillmap_user_complete($course, $user, $mod, $skillmap) {
    if ($responce = get_record('skillmap_answers', "skillmapid", $skillmap->id, "userid", $user->id)) {
        $result->info = "'".format_string(skillmap_get_option_text($skillmap, $responce->optionid))."'";
        $result->time = $responce->timemodified;
        echo get_string("answered", "skillmap").": $result->info. ".get_string("updated", '', userdate($result->time));
    } else {
        print_string("notanswered", "skillmap");
    }
}


function skillmap_add_instance($skillmap) {
// Given an object containing all the necessary data,
// (defined by the form in mod.html) this function
// will create a new instance and return the id number
// of the new instance.

    $skillmap->timemodified = time();
		
    //insert answers
    if ($skillmap->id = insert_record("skillmap", $skillmap)) {
			/*
        foreach ($skillmap->option as $key => $value) {
            $value = trim($value);
            if (isset($value) && $value <> '') {
                $option = new object();
                $option->text = $value;
                $option->skillmapid = $skillmap->id;
                if (isset($skillmap->limit[$key])) {
                    $option->maxanswers = $skillmap->limit[$key];
                }
                $option->timemodified = time();
                insert_record("skillmap_options", $option);
            }
        }*/
    }
		
    return $skillmap->id;
}


function skillmap_update_instance($skillmap) {
// Given an object containing all the necessary data,
// (defined by the form in mod.html) this function
// will update an existing instance with new data.

    $skillmap->id = $skillmap->instance;
    /*$skillmap->timemodified = time();


    if (empty($skillmap->timerestrict)) {
        $skillmap->timeopen = 0;
        $skillmap->timeclose = 0;
    }

    //update, delete or insert answers
    foreach ($skillmap->option as $key => $value) {
        $value = trim($value);
        $option = new object();
        $option->text = $value;
        $option->skillmapid = $skillmap->id;
        if (isset($skillmap->limit[$key])) {
            $option->maxanswers = $skillmap->limit[$key];
        }
        $option->timemodified = time();
        if (isset($skillmap->optionid[$key]) && !empty($skillmap->optionid[$key])){//existing skillmap record
            $option->id=$skillmap->optionid[$key];
            if (isset($value) && $value <> '') {
                update_record("skillmap_options", $option);
            } else { //empty old option - needs to be deleted.
                delete_records("skillmap_options", "id", $option->id);
            }
        } else {
            if (isset($value) && $value <> '') {
                insert_record("skillmap_options", $option);
            }
        }
    }
		*/
    return update_record('skillmap', $skillmap);

}

function skillmap_show_form($skillmap, $user, $cm, $allresponses) {

//$cdisplay is an array of the display info for a skillmap $cdisplay[$optionid]->text  - text name of option.
//                                                                            ->maxanswers -maxanswers for this option
//                                                                            ->full - whether this option is full or not. 0=not full, 1=full
    $cdisplay = array();

    $aid = 0;
    $skillmapfull = false;
    $cdisplay = array();
		
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    foreach ($skillmap->option as $optionid => $text) {
        if (isset($text)) { //make sure there are no dud entries in the db with blank text values.
            $cdisplay[$aid]->optionid = $optionid;
            $cdisplay[$aid]->text = $text;
            $cdisplay[$aid]->maxanswers = $skillmap->maxanswers[$optionid];
            if (isset($allresponses[$optionid])) {
                $cdisplay[$aid]->countanswers = count($allresponses[$optionid]);
            } else {
                $cdisplay[$aid]->countanswers = 0;
            }
						
						/*
            if ($current = get_record('skillmap_responce skill', 'skillmapid', $skillmap->id, 'userid', $user->id, 'optionid', $optionid)) {
                $cdisplay[$aid]->checked = ' checked="checked" ';
            } else {
                $cdisplay[$aid]->checked = '';
            }
						*/
						/*
            if ( $skillmap->limitanswers && 
                ($cdisplay[$aid]->countanswers >= $cdisplay[$aid]->maxanswers) && 
                (empty($cdisplay[$aid]->checked)) ) {
                $cdisplay[$aid]->disabled = ' disabled="disabled" ';
            } else {
                $cdisplay[$aid]->disabled = '';
                if ($skillmap->limitanswers && ($cdisplay[$aid]->countanswers < $cdisplay[$aid]->maxanswers)) {
                    $skillmapfull = false; //set $skillmapfull to false - as the above condition hasn't been set.
                }
            }
						*/
            $aid++;
        }
    }

    switch ($skillmap->display) {
        case SKILLMAP_DISPLAY_HORIZONTAL:
            echo "<table cellpadding=\"20\" cellspacing=\"20\" class=\"boxaligncenter\"><tr>";
						
						echo '<th align="left" scope="col">&nbsp;</th>';
						
						foreach ($skillmap->option as $iSkillLevel => $sSkillLevel)
						{
							echo '<th align="center" scope="col">'.strip_tags($sSkillLevel).'</th>';
						}
						$sInterested = format_text($skillmap->survey_details->interested_label);
						echo '<th align="center" scope="col">'.strip_tags($sInterested).'</th>';
						
            echo "</tr>\n";    
						
						foreach($skillmap->skill as $iSkillID => $sSkillName)
						{
							echo "<tr><td align=\"left\" valign=\"top\">".str_replace("(","<br/>(",strip_tags($sSkillName))."</td>\n";
							
							foreach ($skillmap->option as $iSkillLevel => $sSkillLevel)
							{
								
                echo "<td align=\"center\" valign=\"top\">";
                echo "<input type=\"radio\" name=\"skilllevel_".$iSkillID."\" value=\"".$iSkillLevel."\" alt=\"".strip_tags($sSkillLevel)."\"". //$cd->checked.$cd->disabled.
								" />";
                /*if (!empty($cd->disabled)) {
                    echo format_text($cd->text."<br /><strong>".get_string('full', 'skillmap')."</strong>");
                } else {
                    echo format_text($cd->text);
                }*/
                echo "</td>\n";
							}
							
							echo "<td align=\"center\" valign=\"top\">";
                echo "<input type=\"checkbox\" value=\"1\" name=\"interested_".$iSkillID."\"  alt=\"".strip_tags($sInterested)."\"". //$cd->checked.$cd->disabled.
								" />";
                /*if (!empty($cd->disabled)) {
                    echo format_text($cd->text."<br /><strong>".get_string('full', 'skillmap')."</strong>");
                } else {
                    echo format_text($cd->text);
                }*/
                echo "</td>\n";
							
							
							echo "</tr>\n";
            }
            //echo "</tr>";
            echo "</table>\n";
            break;

        case SKILLMAP_DISPLAY_VERTICAL:
					echo "VERTICAL MODE UNSUPPORTED CURRENTLY";
					/*
            $displayoptions->para = false;
            echo "<table cellpadding=\"10\" cellspacing=\"10\" class=\"boxaligncenter\">";
            foreach ($cdisplay as $cd) {
                echo "<tr><td align=\"left\">";
                echo "<input type=\"radio\" name=\"answer\" value=\"".$cd->optionid."\" alt=\"".strip_tags(format_text($cd->text))."\"". $cd->checked.$cd->disabled." />";

                echo format_text($cd->text. ' ', FORMAT_MOODLE, $displayoptions); //display text for option.

                if ($skillmap->limitanswers && ($skillmap->showresults==SKILLMAP_SHOWRESULTS_ALWAYS) ){ //if limit is enabled, and show results always has been selected, display info beside each skillmap.
                    echo "</td><td>";

                    if (!empty($cd->disabled)) {
                        echo get_string('full', 'skillmap');
                    } elseif(!empty($cd->checked)) {
                                //currently do nothing - maybe some text could be added here to signfy that the skillmap has been 'selected'
                    } elseif ($cd->maxanswers-$cd->countanswers==1) {
                        echo ($cd->maxanswers - $cd->countanswers);
                        echo " ".get_string('spaceleft', 'skillmap');
                    } else {
                        echo ($cd->maxanswers - $cd->countanswers);
                        echo " ".get_string('spacesleft', 'skillmap');
                    }
                    echo "</td>";
                } else if ($skillmap->limitanswers && ($cd->countanswers >= $cd->maxanswers)) {  //if limitanswers and answers exceeded, display "full" beside the skillmap.
                    echo " <strong>".get_string('full', 'skillmap')."</strong>";
                }
                echo "</td>";
                echo "</tr>";
            }
        echo "</table>";
				*/
        break;
    }
    //show save skillmap button
    echo '<div class="button">';
    echo "<input type=\"hidden\" name=\"id\" value=\"$cm->id\" />";
    if (has_capability('mod/skillmap:choose', $context, $user->id, false)) { //don't show save button if the logged in user is the guest user.
        if ($skillmapfull) {
            print_string('skillmapfull', 'skillmap');
            echo "</br>";
        } else {
            echo "<input type=\"submit\" value=\"".get_string("savemyskillmap","skillmap")."\" />";
        }
				
        if ($skillmap->allowupdate && $aaa = get_record('skillmap_responce', 'responce', $skillmap->responce, 'username', $user->username)) {
            echo "<br /><a href='view.php?id=".$cm->id."&amp;action=delskillmap'>".get_string("removemyskillmap","skillmap")."</a>";
        }
    } else {
        print_string('havetologin', 'skillmap');
    }
    echo "</div>";
}

function skillmap_user_submit_response($skillmap, $username, $courseid, $cm) {
	global $USER;
	
  $responce = get_record('skillmap_responce', 'survey', $skillmap->survey, 'username', $username);
		
		if(!$responce)
		{
			
			$newresponce = $responce; //new Object();
			$newresponce->username = $username;
			$newresponce->survey = $skillmap->survey;
			if (! insert_record("skillmap_responce", $newresponce)) {
                error("Could not save your skillmap");
            }
			$responce = get_record('skillmap_responce', 'survey', $skillmap->survey, 'username', $username);
		
		}
		
		var_dump($responce);
		
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    $countanswers=0;
		/*
    if($skillmap->limitanswers) {
			
        // Find out whether groups are being used and enabled
        if (groups_get_activity_groupmode($cm) > 0) {
            $currentgroup = groups_get_activity_group($cm);
        } else {
            $currentgroup = 0;
        }
        if($currentgroup) {
            // If groups are being used, retrieve responses only for users in
            // current group
            global $CFG;
            $responces = get_records_sql("
SELECT 
    ca.*
FROM 
    {$CFG->prefix}skillmap_answers ca
    INNER JOIN {$CFG->prefix}groups_members gm ON ca.userid=gm.userid
WHERE
    optionid=$formanswer
    AND gm.groupid=$currentgroup");
        } else {
            // Groups are not used, retrieve all answers for this option ID
            $responces = get_records("skillmap_answers", "optionid", $formanswer);
        }

        if ($responces) {
            foreach ($responces as $a) { //only return enrolled users.
                if (has_capability('mod/skillmap:choose', $context, $a->userid, false)) {
                    $countanswers++;
                }
            }
        }
        $maxans = $skillmap->maxanswers[$formanswer];	
    }
		*/
    //if (!($skillmap->limitanswers && ($countanswers >= $maxans) )) {
		//"skilllevel_"
		//"interested_"
								
		foreach($skillmap->skill as $iSkillID => $sSkillName)						
		{
			$iSkillLevel = optional_param('skilllevel_'.$iSkillID, '', PARAM_INT);
			
			if($iSkillLevel)
			{
			$current = get_record('skillmap_responce_skill', 'responce', $responce->id, 'skill', $iSkillID);
        if ($current) {

            $newanswer = $current;
            $newanswer->skilllevel = $iSkillLevel;
						$newanswer->skilllevel = optional_param('interested_'.$iSkillID, '', PARAM_BOOL);
            $newanswer->timemodified = time();
            if (! update_record("skillmap_responce_skill", $newanswer)) {
                error("Could not update your skillmap because of a database error");
            }
            add_to_log($courseid, "skillmap", "choose again", "view.php?id=$cm->id", $skillmap->id, $cm->id);
        } else {
            $newanswer = NULL;
            $newanswer->responce = $responce->id;
            $newanswer->skill = $iSkillID;
          	$newanswer->skilllevel = $iSkillLevel;
						$newanswer->interested = optional_param('interested_'.$iSkillID, '', PARAM_BOOL);
            $newanswer->timemodified = time();
            if (! insert_record("skillmap_responce_skill", $newanswer)) {
                error("Could not save your skillmap");
            }
            add_to_log($courseid, "skillmap", "choose", "view.php?id=$cm->id", $skillmap->id, $cm->id);
        }
				/*
				} else {
						if (!($current->optionid==$formanswer)) { //check to see if current skillmap already selected - if not display error
								error("this skillmap is full!");
						}
				}*/
			}
		}
}

function skillmap_show_reportlink($user, $cm) {
    $responsecount =0;
    foreach($user as $optionid => $userlist) {
        if ($optionid) {
            $responsecount += count($userlist);
        }
    }

    echo '<div class="reportlink">';
    echo "<a href=\"report.php?id=$cm->id\">".get_string("viewallresponses", "skillmap", $responsecount)."</a>";
    echo '</div>';
}

function skillmap_show_results($skillmap, $course, $cm, $allresponses, $forcepublish='') {
    global $CFG, $COLUMN_HEIGHT;
    
    print_heading(get_string("responses", "skillmap"));
    if (empty($forcepublish)) { //alow the publish setting to be overridden
        $forcepublish = $skillmap->publish;
    }

    if (!$allresponses) {
        print_heading(get_string("nousersyet"));
    }

    $totalresponsecount = 0;
    foreach ($allresponses as $optionid => $userlist) {
        if ($skillmap->showunanswered || $optionid) {
            $totalresponsecount += count($userlist);
        }
    }
    
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    $hascapfullnames = has_capability('moodle/site:viewfullnames', $context);
    
    $viewresponses = has_capability('mod/skillmap:readresponses', $context); 
    switch ($forcepublish) {
        case SKILLMAP_PUBLISH_NAMES:
            echo '<div id="tablecontainer">';
            if ($viewresponses) {
                echo '<form id="attemptsform" method="post" action="'.$_SERVER['PHP_SELF'].'" onsubmit="var menu = document.getElementById(\'menuaction\'); return (menu.options[menu.selectedIndex].value == \'delete\' ? \''.addslashes(get_string('deleteattemptcheck','quiz')).'\' : true);">';
                echo '<div>';
                echo '<input type="hidden" name="id" value="'.$cm->id.'" />';
                echo '<input type="hidden" name="mode" value="overview" />';
            }

            echo "<table cellpadding=\"5\" cellspacing=\"10\" class=\"results names\">";
            echo "<tr>";
  
            $columncount = array(); // number of votes in each column
            if ($skillmap->showunanswered) {
                $columncount[0] = 0;
                echo "<th class=\"col0 header\" scope=\"col\">";
                print_string('notanswered', 'skillmap');
                echo "</th>";
            }
            $count = 1;
            foreach ($skillmap->option as $optionid => $optiontext) {
                $columncount[$optionid] = 0; // init counters
                echo "<th class=\"col$count header\" scope=\"col\">";
                echo format_string($optiontext);
                echo "</th>";
                $count++;
            }
            echo "</tr><tr>";

            if ($skillmap->showunanswered) {
                echo "<td class=\"col$count data\" >";
                // added empty row so that when the next iteration is empty,
                // we do not get <table></table> erro from w3c validator
                // MDL-7861
                echo "<table class=\"skillmapresponse\"><tr><td></td></tr>";
                foreach ($allresponses[0] as $user) {
                    echo "<tr>";
                    echo "<td class=\"picture\">";
                    print_user_picture($user->id, $course->id, $user->picture);
                    echo "</td><td class=\"fullname\">";
                    echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">";
                    echo fullname($user, $hascapfullnames);
                    echo "</a>";
                    echo "</td></tr>";
                }
                echo "</table></td>";
            }
            $count = 0;
            foreach ($skillmap->option as $optionid => $optiontext) {
                    echo '<td class="col'.$count.' data" >';

                    // added empty row so that when the next iteration is empty,
                    // we do not get <table></table> erro from w3c validator
                    // MDL-7861
                    echo '<table class="skillmapresponse"><tr><td></td></tr>';
                    if (isset($allresponses[$optionid])) {
                        foreach ($allresponses[$optionid] as $user) {
                            $columncount[$optionid] += 1;
                            echo '<tr><td class="attemptcell">';
                            if ($viewresponses and has_capability('mod/skillmap:deleteresponses',$context)) {
                                echo '<input type="checkbox" name="attemptid[]" value="'. $user->id. '" />';
                            }
                            echo '</td><td class="picture">';
                            print_user_picture($user->id, $course->id, $user->picture);
                            echo '</td><td class="fullname">';
                            echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">";
                            echo fullname($user, $hascapfullnames);
                            echo '</a>';
                            echo '</td></tr>';
                       }
                    }
                    $count++;
                    echo '</table></td>';
            }
            echo "</tr><tr>";
            $count = 0;
            
            if ($skillmap->showunanswered) {
                echo "<td></td>";
            }
            
            foreach ($skillmap->option as $optionid => $optiontext) {
                echo "<td align=\"center\" class=\"count\">";
                /*if ($skillmap->limitanswers) {
                    echo get_string("taken", "skillmap").":";
                    echo $columncount[$optionid];
                    echo "<br/>";
                    echo get_string("limit", "skillmap").":";
                    $skillmap_option = get_record("skillmap_options", "id", $optionid);
                    echo $skillmap_option->maxanswers;
                } else {*/
                    if (isset($columncount[$optionid])) {
                        echo $columncount[$optionid];
                    }
                //}
                echo "</td>";
                $count++;
            }
            echo "</tr>";
            
            /// Print "Select all" etc.
						/*
            if ($viewresponses and has_capability('mod/skillmap:deleteresponses',$context)) {
                echo '<tr><td></td><td>';
                echo '<a href="javascript:select_all_in(\'DIV\',null,\'tablecontainer\');">'.get_string('selectall', 'quiz').'</a> / ';
                echo '<a href="javascript:deselect_all_in(\'DIV\',null,\'tablecontainer\');">'.get_string('selectnone', 'quiz').'</a> ';
                echo '&nbsp;&nbsp;';
                $options = array('delete' => get_string('delete'));
                echo choose_from_menu($options, 'action', '', get_string('withselected', 'quiz'), 'if(this.selectedIndex > 0) submitFormById(\'attemptsform\');', '', true);
                echo '<noscript id="noscriptmenuaction" style="display: inline;">';
                echo '<div>';
                echo '<input type="submit" value="'.get_string('go').'" /></div></noscript>';
                echo '<script type="text/javascript">'."\n".'document.getElementById("noscriptmenuaction").style.display = "none";'."\n".'</script>';
                echo '</td><td></td></tr>';
            }
            */
            echo "</table></div>";
            if ($viewresponses) {
                echo "</form></div>";
            }
            break;
        
        
        case SKILLMAP_PUBLISH_ANONYMOUS:

            echo "<table cellpadding=\"5\" cellspacing=\"0\" class=\"results anonymous\">";
            echo "<tr>";
            $maxcolumn = 0;
            if ($skillmap->showunanswered) {
                echo "<th  class=\"col0 header\" scope=\"col\">";
                print_string('notanswered', 'skillmap');
                echo "</th>";
                $column[0] = 0;
                foreach ($allresponses[0] as $user) {
                    $column[0]++;
                }
                $maxcolumn = $column[0];
            }
            $count = 1;

            foreach ($skillmap->option as $optionid => $optiontext) {
                echo "<th class=\"col$count header\" scope=\"col\">";
                echo format_string($optiontext);
                echo "</th>";
                
                $column[$optionid] = 0;
                if (isset($allresponses[$optionid])) {
                    $column[$optionid] = count($allresponses[$optionid]);
                    if ($column[$optionid] > $maxcolumn) {
                        $maxcolumn = $column[$optionid];
                    }
                } else {
                    $column[$optionid] = 0;
                }
            }
            echo "</tr><tr>";

            $height = 0;

            if ($skillmap->showunanswered) {
                if ($maxcolumn) {
                    $height = $COLUMN_HEIGHT * ((float)$column[0] / (float)$maxcolumn);
                }
                echo "<td style=\"vertical-align:bottom\" align=\"center\" class=\"col0 data\">";
                echo "<img src=\"column.png\" height=\"$height\" width=\"49\" alt=\"\" />";
                echo "</td>";
            }
            $count = 1;
            foreach ($skillmap->option as $optionid => $optiontext) {
                if ($maxcolumn) {
                    $height = $COLUMN_HEIGHT * ((float)$column[$optionid] / (float)$maxcolumn);
                }
                echo "<td style=\"vertical-align:bottom\" align=\"center\" class=\"col$count data\">";
                echo "<img src=\"column.png\" height=\"$height\" width=\"49\" alt=\"\" />";
                echo "</td>";
                $count++;
            }
            echo "</tr><tr>";


            if ($skillmap->showunanswered) {
                echo '<td align="center" class="col0 count">';
                //if (!$skillmap->limitanswers) {
                    echo $column[0];
                    echo '<br />('.format_float(((float)$column[0]/(float)$totalresponsecount)*100.0,1).'%)';
                //}
                echo '</td>';
            }
            $count = 1;
            foreach ($skillmap->option as $optionid => $optiontext) {
                echo "<td align=\"center\" class=\"col$count count\">";
                /*if ($skillmap->limitanswers) {
                    echo get_string("taken", "skillmap").":";
                    echo $column[$optionid].'<br />';
                    echo get_string("limit", "skillmap").":";
                    $skillmap_option = get_record("skillmap_options", "id", $optionid);
                    echo $skillmap_option->maxanswers;
                } else {*/
                    echo $column[$optionid];
                    echo '<br />('.format_float(((float)$column[$optionid]/(float)$totalresponsecount)*100.0,1).'%)';
                //}
                echo "</td>";
                $count++;
            }
            echo "</tr></table>";
            
            break;
    }
}


function skillmap_delete_responses($attemptids, $skillmapid) {

    if(!is_array($attemptids) || empty($attemptids)) {
        return false;
    }

    foreach($attemptids as $num => $attemptid) {
        if(empty($attemptid)) {
            unset($attemptids[$num]);
        }
    }

    foreach($attemptids as $attemptid) {
        if ($todelete = get_record('skillmap_answers', 'skillmapid', $skillmapid, 'userid', $attemptid)) {
            delete_records('skillmap_answers', 'skillmapid', $skillmapid, 'userid', $attemptid);
        }
    }
    return true;
}


function skillmap_delete_instance($id) {
// Given an ID of an instance of this module,
// this function will permanently delete the instance
// and any data that depends on it.

    if (! $skillmap = get_record("skillmap", "id", "$id")) {
        return false;
    }

    $result = true;

    if (! delete_records("skillmap_answers", "skillmapid", "$skillmap->id")) {
        $result = false;
    }

    if (! delete_records("skillmap_options", "skillmapid", "$skillmap->id")) {
        $result = false;
    }

    if (! delete_records("skillmap", "id", "$skillmap->id")) {
        $result = false;
    }

    return $result;
}

function skillmap_get_participants($skillmapid) {
//Returns the users with data in one skillmap
//(users with records in skillmap_responses, students)

    global $CFG;

    //Get students
    $students = get_records_sql("SELECT DISTINCT u.id, u.id
                                 FROM {$CFG->prefix}user u,
                                      {$CFG->prefix}skillmap_answers a
                                 WHERE a.skillmapid = '$skillmapid' and
                                       u.id = a.userid");

    //Return students array (it contains an array of unique users)
    return ($students);
}


function skillmap_get_option_text($skillmap, $id) {
// Returns text string which is the answer that matches the id
    if ($result = get_record("skillmap_options", "id", $id)) {
        return $result->text;
    } else {
        return get_string("notanswered", "skillmap");
    }
}



function skillmap_get_skillmap($skillmapid) {
global $CFG, $USER;
// Gets a full skillmap record
    if ($skillmap = get_record("skillmap", "id", $skillmapid)) {
				// TODO: Get the  survey
				if($skillmap->survey_details = get_record("skillmap_survey", "id", $skillmap->survey)){
						// TODO: Add a max number of questions
						// TODO: Add the only get the questions unanswered stuff in here
						// TODO: Add the skill map priority stuff in here
						if ($options = get_records_sql("SELECT * FROM ". $CFG->prefix ."skillmap_skilllevel WHERE skilllevel IN (".$skillmap->survey_details->levels.")")){
								foreach ($options as $option) {
										$skillmap->option[$option->skilllevel] = $option->name;
										$skillmap->maxanswers[$option->skilllevel] = 0; // FIXME: just carry over from choice
								}
								
								$aSkillIDs = array();
								
								if ($responce = get_record('skillmap_responce', 'survey', $skillmap->survey, 'username', $USER->username)) {
									if ($responces = get_records('skillmap_responce_skill', 'responce', $responce->id)) {
										foreach($responces as $responceobject)
										{
											$aSkillIDs[] = $responceobject->skill;
										}
									}
								}
								
								if(count($aSkillIDs))
								{
									$skills = get_records_sql("SELECT * FROM ". $CFG->prefix ."skillmap_skill WHERE id NOT IN (".join(",", $aSkillIDs).")");
								}
								else
								{
									$skills = get_records("skillmap_skill");
								}
								
								if($skills){
									$iCount = 0;
									//debugging($skills);
										foreach ($skills as $skill) {
										  if($iCount < 7)
											{
												$skillmap->skill[$skill->id] = $skill->name;
												$iCount ++;
											}
										}
										return $skillmap;
								}
								
						}
				}
		}
    return false;
}

function skillmap_get_view_actions() {
    return array('view','view all','report');
}

function skillmap_get_post_actions() {
    return array('choose','choose again');
}


/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the skillmap.
 * @param $mform form passed by reference
 */
function skillmap_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'skillmapheader', get_string('modulenameplural', 'skillmap'));
    $mform->addElement('advcheckbox', 'reset_skillmap', get_string('removeresponses','skillmap'));
}

/**
 * Course reset form defaults.
 */
function skillmap_reset_course_form_defaults($course) {
    return array('reset_skillmap'=>1);
}

/**
 * Actual implementation of the rest coures functionality, delete all the
 * skillmap responses for course $data->courseid.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function skillmap_reset_userdata($data) {
    global $CFG;

		// TODO: skillmap_reset_userdata
		/*
    $componentstr = get_string('modulenameplural', 'skillmap');
    $status = array();

    if (!empty($data->reset_skillmap)) {
        $skillmapssql = "SELECT ch.id
                         FROM {$CFG->prefix}skillmap ch
                        WHERE ch.course={$data->courseid}";

        delete_records_select('skillmap_answers', "skillmapid IN ($skillmapssql)");
        $status[] = array('component'=>$componentstr, 'item'=>get_string('removeresponses', 'skillmap'), 'error'=>false);
    }

    /// updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates('skillmap', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }
*/
    return $status;
}

function skillmap_get_response_data($skillmap, $cm, $groupmode) {
    global $CFG, $USER;

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

/// Get the current group
    if ($groupmode > 0) {
        $currentgroup = groups_get_activity_group($cm);
    } else {
        $currentgroup = 0;
    }

/// Initialise the returned array, which is a matrix:  $allresponses[responseid][userid] = responseobject
    $allresponses = array();

/// First get all the users who have access here
/// To start with we assume they are all "unanswered" then move them later
    $allresponses[0] = get_users_by_capability($context, 'mod/skillmap:choose', 'u.id, u.picture, u.firstname, u.lastname, u.idnumber', 'u.firstname ASC', '', '', $currentgroup, '', false, true);

		// TODO: sort out getting the responces from the right responce record
		//mdl_skillmap_responce
		
		/// Get all the recorded responses for this skillmap
		$rawresponses = get_records_sql("SELECT s.* FROM ".$CFG->prefix."skillmap_responce_skill s JOIN ".$CFG->prefix."skillmap_responce r ON (r.id = s.responce) WHERE survey = ".$skillmap->survey);


/// Use the responses to move users into the correct column

    if ($rawresponses) {
			//debugging($rawresponses);
        /*foreach ($rawresponses as $response) {
            if (isset($allresponses[0][$response->userid])) {   // This person is enrolled and in correct group
                $allresponses[0][$response->userid]->timemodified = $response->timemodified;
                $allresponses[$response->optionid][$response->userid] = clone($allresponses[0][$response->userid]);
                unset($allresponses[0][$response->userid]);   // Remove from unanswered column
            }
        }
				*/
    }

    return $allresponses;
}

/**
 * Returns all other caps used in module
 */
function skillmap_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

?>
