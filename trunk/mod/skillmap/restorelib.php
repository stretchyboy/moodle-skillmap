<?php //$Id: restorelib.php,v 1.30.6.1 2008/08/11 05:10:44 danmarsden Exp $
    //This php script contains all the stuff to backup/restore
    //skillmap mods

    //This is the "graphical" structure of the skillmap mod:
    //
    //                      skillmap
    //                    (CL,pk->id)----------|
    //                        |                |
    //                        |                |
    //                        |                |
    //                  skillmap_options         |
    //             (UL,pk->id, fk->skillmapid)   |
    //                        |                |
    //                        |                |
    //                        |                |
    //                   skillmap_answers        |
    //        (UL,pk->id, fk->skillmapid, fk->optionid)       
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files)
    //
    //-----------------------------------------------------------

    //This function executes all the restore procedure about this mod
    function skillmap_restore_mods($mod,$restore) {

        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;
            // if necessary, write to restorelog and adjust date/time fields
            if ($restore->course_startdateoffset) {
                restore_log_date_changes('Skillmap', $restore, $info['MOD']['#'], array('TIMEOPEN', 'TIMECLOSE'));
            }
            //traverse_xmlize($info);                                                                     //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //Now, build the SKILLMAP record structure
            $skillmap->course = $restore->course_id;
            $skillmap->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $skillmap->text = backup_todb($info['MOD']['#']['TEXT']['0']['#']);
            $skillmap->format = backup_todb($info['MOD']['#']['FORMAT']['0']['#']);
            $skillmap->publish = backup_todb($info['MOD']['#']['PUBLISH']['0']['#']);
            $skillmap->showresults = isset($info['MOD']['#']['SHOWRESULTS']['0']['#'])?backup_todb($info['MOD']['#']['SHOWRESULTS']['0']['#']):'';
            $skillmap->display = backup_todb($info['MOD']['#']['DISPLAY']['0']['#']);
            $skillmap->allowupdate = backup_todb($info['MOD']['#']['ALLOWUPDATE']['0']['#']);
            $skillmap->showunanswered = backup_todb($info['MOD']['#']['SHOWUNANSWERED']['0']['#']);
            $skillmap->limitanswers = backup_todb($info['MOD']['#']['LIMITANSWERS']['0']['#']); 
            $skillmap->timeopen = backup_todb($info['MOD']['#']['TIMEOPEN']['0']['#']);
            $skillmap->timeclose = backup_todb($info['MOD']['#']['TIMECLOSE']['0']['#']);
            $skillmap->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);

            //To mantain compatibilty, in 1.4 the publish setting meaning has changed. We
            //have to modify some things it if the release field isn't present in the backup file.
            if (! isset($info['MOD']['#']['SHOWRESULTS']['0']['#'])) {   //check for previous versions
                if (! isset($info['MOD']['#']['RELEASE']['0']['#'])) {  //It's a pre-14 backup filea
                    //Set the allowupdate field
                    if ($skillmap->publish == 0) { 
                        $skillmap->allowupdate = 1;
                    }
                    //Set the showresults field as defined by the old publish field
                    if ($skillmap->publish > 0) {
                        $skillmap->showresults = 1;
                    }
                    //Recode the publish field to its 1.4 meaning
                    if ($skillmap->publish > 0) {
                        $skillmap->publish--;
                    }
                } else { //it's post 1.4 pre 1.6
                    //convert old release values into new showanswer column.
                    $skillmap->showresults = backup_todb($info['MOD']['#']['RELEASE']['0']['#']);
                }
            }
            //The structure is equal to the db, so insert the skillmap
            $newid = insert_record ("skillmap",$skillmap);
            
            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,$mod->modtype,
                             $mod->id, $newid);
                
                //Check to see how answers (curently skillmap_options) are stored in the table 
                //If answer1 - answer6 exist, this is a pre 1.5 version of skillmap
                if (isset($info['MOD']['#']['ANSWER1']['0']['#']) || 
                    isset($info['MOD']['#']['ANSWER2']['0']['#']) || 
                    isset($info['MOD']['#']['ANSWER3']['0']['#']) || 
                    isset($info['MOD']['#']['ANSWER4']['0']['#']) || 
                    isset($info['MOD']['#']['ANSWER5']['0']['#']) || 
                    isset($info['MOD']['#']['ANSWER6']['0']['#']) ) {
              
                    //This is a pre 1.5 skillmap backup, special work begins
                    $options = array();
                    $options[1] = backup_todb($info['MOD']['#']['ANSWER1']['0']['#']);
                    $options[2] = backup_todb($info['MOD']['#']['ANSWER2']['0']['#']);
                    $options[3] = backup_todb($info['MOD']['#']['ANSWER3']['0']['#']);
                    $options[4] = backup_todb($info['MOD']['#']['ANSWER4']['0']['#']);
                    $options[5] = backup_todb($info['MOD']['#']['ANSWER5']['0']['#']);
                    $options[6] = backup_todb($info['MOD']['#']['ANSWER6']['0']['#']);
                
                    for($i = 1; $i < 7; $i++) { //insert old answers (in 1.4)  as skillmap_options (1.5) to db.  
                        if (!empty($options[$i])) {  //make sure this option has something in it!
                            $option->skillmapid = $newid;
                            $option->text = $options[$i];
                            $option->timemodified = $skillmap->timemodified;
                            $newoptionid = insert_record ("skillmap_options",$option);
                            //Save this skillmap_option to backup_ids
                            backup_putid($restore->backup_unique_code,"skillmap_options",$i,$newoptionid);
                        }
                    }
                 } else { //Now we are in a "standard" 1.5 skillmap, so restore skillmap_options normally
                     $status = skillmap_options_restore_mods($newid,$info,$restore);
                 }

                 //now restore the answers for this skillmap.
                 if (restore_userdata_selected($restore,'skillmap',$mod->id)) {
                    //Restore skillmap_answers
                    $status = skillmap_answers_restore_mods($newid,$info,$restore);     
                 }                               
            } else {
                $status = false;
            }

            //Do some output     
            if (!defined('RESTORE_SILENTLY')) {
                echo "<li>".get_string("modulename","skillmap")." \"".format_string(stripslashes($skillmap->name),true)."\"</li>";
            }
            backup_flush(300);

        } else {
            $status = false;
        }
        return $status;
    }

function skillmap_options_restore_mods($skillmapid,$info,$restore) {

        global $CFG;

        $status = true;

        $options = $info['MOD']['#']['OPTIONS']['0']['#']['OPTION'];

        //Iterate over options
        for($i = 0; $i < sizeof($options); $i++) {
            $opt_info = $options[$i];
            //traverse_xmlize($opt_info);                                                                 //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($opt_info['#']['ID']['0']['#']);
            $olduserid = isset($opt_info['#']['USERID']['0']['#'])?backup_todb($opt_info['#']['USERID']['0']['#']):'';

            //Now, build the SKILLMAP_OPTIONS record structure
            $option->skillmapid = $skillmapid;
            $option->text = backup_todb($opt_info['#']['TEXT']['0']['#']);
            $option->maxanswers = backup_todb($opt_info['#']['MAXANSWERS']['0']['#']);
            $option->timemodified = backup_todb($opt_info['#']['TIMEMODIFIED']['0']['#']);

            //The structure is equal to the db, so insert the skillmap_options
            $newid = insert_record ("skillmap_options",$option);

            //Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br />";
                    }
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,"skillmap_options",$oldid,
                             $newid);
            } else {
                $status = false;
            }
        }

        return $status;
    }

    //This function restores the skillmap_answers
    function skillmap_answers_restore_mods($skillmapid,$info,$restore) {

        global $CFG;

        $status = true;
        if (isset($info['MOD']['#']['ANSWERS']['0']['#']['ANSWER'])) {
            $answers = $info['MOD']['#']['ANSWERS']['0']['#']['ANSWER'];

            //Iterate over answers
            for($i = 0; $i < sizeof($answers); $i++) {
                $ans_info = $answers[$i];
                //traverse_xmlize($sub_info);                                                                 //Debug
                //print_object ($GLOBALS['traverse_array']);                                                  //Debug
                //$GLOBALS['traverse_array']="";                                                              //Debug

                //We'll need this later!!
                $oldid = backup_todb($ans_info['#']['ID']['0']['#']);
                $olduserid = backup_todb($ans_info['#']['USERID']['0']['#']);

                //Now, build the SKILLMAP_ANSWERS record structure
                $answer->skillmapid = $skillmapid;
                $answer->userid = backup_todb($ans_info['#']['USERID']['0']['#']);
                $answer->optionid = backup_todb($ans_info['#']['OPTIONID']['0']['#']);
                $answer->timemodified = backup_todb($ans_info['#']['TIMEMODIFIED']['0']['#']);

                //If the answer contains SKILLMAP_ANSWER, it's a pre 1.5 backup
                if (!empty($ans_info['#']['SKILLMAP_ANSWER']['0']['#'])) {
                    //optionid was, in pre 1.5 backups, skillmap_answer
                    $answer->optionid = backup_todb($ans_info['#']['SKILLMAP_ANSWER']['0']['#']);
                }

                //We have to recode the optionid field
                $option = backup_getid($restore->backup_unique_code,"skillmap_options",$answer->optionid);
                if ($option) {
                    $answer->optionid = $option->new_id;
                }

                //We have to recode the userid field
                $user = backup_getid($restore->backup_unique_code,"user",$answer->userid);
                if ($user) {
                    $answer->userid = $user->new_id;
                }

                //The structure is equal to the db, so insert the skillmap_answers
                $newid = insert_record ("skillmap_answers",$answer);

                //Do some output
                if (($i+1) % 50 == 0) {
                    if (!defined('RESTORE_SILENTLY')) {
                        echo ".";
                        if (($i+1) % 1000 == 0) {
                            echo "<br />";
                        }
                    }
                    backup_flush(300);
                }

                if ($newid) {
                    //We have the newid, update backup_ids
                    backup_putid($restore->backup_unique_code,"skillmap_answers",$oldid,
                             $newid);
                } else {
                    $status = false;
                }
            }
        }
        return $status;
    }

    //Return a content decoded to support interactivities linking. Every module
    //should have its own. They are called automatically from
    //skillmap_decode_content_links_caller() function in each module
    //in the restore process
    function skillmap_decode_content_links ($content,$restore) {
            
        global $CFG;
            
        $result = $content;
                
        //Link to the list of skillmaps
                
        $searchstring='/\$@(SKILLMAPINDEX)\*([0-9]+)@\$/';
        //We look for it
        preg_match_all($searchstring,$content,$foundset);
        //If found, then we are going to look for its new id (in backup tables)
        if ($foundset[0]) {
            //print_object($foundset);                                     //Debug
            //Iterate over foundset[2]. They are the old_ids
            foreach($foundset[2] as $old_id) {
                //We get the needed variables here (course id)
                $rec = backup_getid($restore->backup_unique_code,"course",$old_id);
                //Personalize the searchstring
                $searchstring='/\$@(SKILLMAPINDEX)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/skillmap/index.php?id='.$rec->new_id,$result);
                } else { 
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/skillmap/index.php?id='.$old_id,$result);
                }
            }
        }

        //Link to skillmap view by moduleid

        $searchstring='/\$@(SKILLMAPVIEWBYID)\*([0-9]+)@\$/';
        //We look for it
        preg_match_all($searchstring,$result,$foundset);
        //If found, then we are going to look for its new id (in backup tables)
        if ($foundset[0]) {
            //print_object($foundset);                                     //Debug
            //Iterate over foundset[2]. They are the old_ids
            foreach($foundset[2] as $old_id) {
                //We get the needed variables here (course_modules id)
                $rec = backup_getid($restore->backup_unique_code,"course_modules",$old_id);
                //Personalize the searchstring
                $searchstring='/\$@(SKILLMAPVIEWBYID)\*('.$old_id.')@\$/';
                //If it is a link to this course, update the link to its new location
                if($rec->new_id) {
                    //Now replace it
                    $result= preg_replace($searchstring,$CFG->wwwroot.'/mod/skillmap/view.php?id='.$rec->new_id,$result);
                } else {
                    //It's a foreign link so leave it as original
                    $result= preg_replace($searchstring,$restore->original_wwwroot.'/mod/skillmap/view.php?id='.$old_id,$result);
                }
            }
        }

        return $result;
    }

    //This function makes all the necessary calls to xxxx_decode_content_links()
    //function in each module, passing them the desired contents to be decoded
    //from backup format to destination site/course in order to mantain inter-activities
    //working in the backup/restore process. It's called from restore_decode_content_links()
    //function in restore process
    function skillmap_decode_content_links_caller($restore) {
        global $CFG;
        $status = true;
        
        if ($skillmaps = get_records_sql ("SELECT c.id, c.text
                                   FROM {$CFG->prefix}skillmap c
                                   WHERE c.course = $restore->course_id")) {
                                               //Iterate over each skillmap->text
            $i = 0;   //Counter to send some output to the browser to avoid timeouts
            foreach ($skillmaps as $skillmap) {
                //Increment counter
                $i++;
                $content = $skillmap->text;
                $result = restore_decode_content_links_worker($content,$restore);
                if ($result != $content) {
                    //Update record
                    $skillmap->text = addslashes($result);
                    $status = update_record("skillmap",$skillmap);
                    if (debugging()) {
                        if (!defined('RESTORE_SILENTLY')) {
                            echo '<br /><hr />'.s($content).'<br />changed to<br />'.s($result).'<hr /><br />';
                        }
                    }
                }
                //Do some output
                if (($i+1) % 5 == 0) {
                    if (!defined('RESTORE_SILENTLY')) {
                        echo ".";
                        if (($i+1) % 100 == 0) {
                            echo "<br />";
                        }
                    }
                    backup_flush(300);
                }
            }
        }

        return $status;
    }

    //This function converts texts in FORMAT_WIKI to FORMAT_MARKDOWN for
    //some texts in the module
    function skillmap_restore_wiki2markdown ($restore) {
    
        global $CFG;

        $status = true;

        //Convert skillmap->text
        if ($records = get_records_sql ("SELECT c.id, c.text, c.format
                                         FROM {$CFG->prefix}skillmap c,
                                              {$CFG->prefix}backup_ids b
                                         WHERE c.course = $restore->course_id AND
                                               c.format = ".FORMAT_WIKI. " AND
                                               b.backup_code = $restore->backup_unique_code AND
                                               b.table_name = 'skillmap' AND
                                               b.new_id = c.id")) {
            foreach ($records as $record) {
                //Rebuild wiki links
                $record->text = restore_decode_wiki_content($record->text, $restore);
                //Convert to Markdown
                $wtm = new WikiToMarkdown();
                $record->text = $wtm->convert($record->text, $restore->course_id);
                $record->format = FORMAT_MARKDOWN;
                $status = update_record('skillmap', addslashes_object($record));
                //Do some output
                $i++;
                if (($i+1) % 1 == 0) {
                    if (!defined('RESTORE_SILENTLY')) {
                        echo ".";
                        if (($i+1) % 20 == 0) {
                            echo "<br />";
                        }
                    }
                    backup_flush(300);
                }
            }

        }
        return $status;
    }

    //This function returns a log record with all the necessay transformations
    //done. It's used by restore_log_module() to restore modules log.
    function skillmap_restore_logs($restore,$log) {
                    
        $status = false;
                    
        //Depending of the action, we recode different things
        switch ($log->action) {
        case "add":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "update":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "choose":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "choose again":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "view":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "view all":
            $log->url = "index.php?id=".$log->course;
            $status = true;
            break;
        case "report":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "report.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        default:
            if (!defined('RESTORE_SILENTLY')) {
                echo "action (".$log->module."-".$log->action.") unknown. Not restored<br />";                 //Debug
            }
            break;
        }

        if ($status) {
            $status = $log;
        }
        return $status;
    }
?>
