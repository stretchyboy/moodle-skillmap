<?php //$Id: backuplib.php,v 1.11 2006/02/08 23:46:21 danmarsden Exp $
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

    function skillmap_backup_mods($bf,$preferences) {
        
        global $CFG;

        $status = true;

        //Iterate over skillmap table
        $skillmaps = get_records ("skillmap","course",$preferences->backup_course,"id");
        if ($skillmaps) {
            foreach ($skillmaps as $skillmap) {
                if (backup_mod_selected($preferences,'skillmap',$skillmap->id)) {
                    $status = skillmap_backup_one_mod($bf,$preferences,$skillmap);
                }
            }
        }
        return $status;
    }

    function skillmap_backup_one_mod($bf,$preferences,$skillmap) {

        global $CFG;
    
        if (is_numeric($skillmap)) {
            $skillmap = get_record('skillmap','id',$skillmap);
        }
    
        $status = true;

        //Start mod
        fwrite ($bf,start_tag("MOD",3,true));
        //Print skillmap data
        fwrite ($bf,full_tag("ID",4,false,$skillmap->id));
        fwrite ($bf,full_tag("MODTYPE",4,false,"skillmap"));
        fwrite ($bf,full_tag("NAME",4,false,$skillmap->name));
        fwrite ($bf,full_tag("TEXT",4,false,$skillmap->text));
        fwrite ($bf,full_tag("FORMAT",4,false,$skillmap->format));
        fwrite ($bf,full_tag("PUBLISH",4,false,$skillmap->publish));
        fwrite ($bf,full_tag("SHOWRESULTS",4,false,$skillmap->showresults));
        fwrite ($bf,full_tag("DISPLAY",4,false,$skillmap->display));
        fwrite ($bf,full_tag("ALLOWUPDATE",4,false,$skillmap->allowupdate));
        fwrite ($bf,full_tag("SHOWUNANSWERED",4,false,$skillmap->showunanswered));
        fwrite ($bf,full_tag("TIMEOPEN",4,false,$skillmap->timeopen));
        fwrite ($bf,full_tag("TIMECLOSE",4,false,$skillmap->timeclose));
        fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$skillmap->timemodified));

        //Now backup skillmap_options
        $status = backup_skillmap_options($bf,$preferences,$skillmap->id);

        //if we've selected to backup users info, then execute backup_skillmap_answers
        if (backup_userdata_selected($preferences,'skillmap',$skillmap->id)) {
            $status = backup_skillmap_answers($bf,$preferences,$skillmap->id);
        }
        //End mod
        $status =fwrite ($bf,end_tag("MOD",3,true));

        return $status;
    }

    //Backup skillmap_answers contents (executed from skillmap_backup_mods)
    function backup_skillmap_answers ($bf,$preferences,$skillmap) {

        global $CFG;

        $status = true;

        $skillmap_answers = get_records("skillmap_answers","skillmapid",$skillmap,"id");
        //If there is answers
        if ($skillmap_answers) {
            //Write start tag
            $status =fwrite ($bf,start_tag("ANSWERS",4,true));
            //Iterate over each answer
            foreach ($skillmap_answers as $cho_ans) {
                //Start answer
                $status =fwrite ($bf,start_tag("ANSWER",5,true));
                //Print answer contents
                fwrite ($bf,full_tag("ID",6,false,$cho_ans->id));
                fwrite ($bf,full_tag("USERID",6,false,$cho_ans->userid));
                fwrite ($bf,full_tag("OPTIONID",6,false,$cho_ans->optionid));
                fwrite ($bf,full_tag("TIMEMODIFIED",6,false,$cho_ans->timemodified));
                //End answer
                $status =fwrite ($bf,end_tag("ANSWER",5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("ANSWERS",4,true));
        }
        return $status;
    }


    //backup skillmap_options contents (executed from skillmap_backup_mods)
    function backup_skillmap_options ($bf,$preferences,$skillmap) {

        global $CFG;

        $status = true;
        
        $skillmap_options = get_records("skillmap_options","skillmapid",$skillmap,"id");
        //If there is options
        if ($skillmap_options) {            
            //Write start tag
            $status =fwrite ($bf,start_tag("OPTIONS",4,true));
            //Iterate over each answer
            foreach ($skillmap_options as $cho_opt) {
                //Start option
                $status =fwrite ($bf,start_tag("OPTION",5,true));
                //Print option contents
                fwrite ($bf,full_tag("ID",6,false,$cho_opt->id));
                fwrite ($bf,full_tag("TEXT",6,false,$cho_opt->text));
                fwrite ($bf,full_tag("MAXANSWERS",6,false,$cho_opt->maxanswers));
                fwrite ($bf,full_tag("TIMEMODIFIED",6,false,$cho_opt->timemodified));
                //End answer
                $status =fwrite ($bf,end_tag("OPTION",5,true));
            }
            //Write end tag
            $status =fwrite ($bf,end_tag("OPTIONS",4,true));
        }
        return $status;
    }
   
   ////Return an array of info (name,value)
   function skillmap_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {

        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += skillmap_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }
        //First the course data
        $info[0][0] = get_string("modulenameplural","skillmap");
        if ($ids = skillmap_ids ($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        //Now, if requested, the user_data
        if ($user_data) {
            $info[1][0] = get_string("responses","skillmap");
            if ($ids = skillmap_answer_ids_by_course ($course)) {
                $info[1][1] = count($ids);
            } else {
                $info[1][1] = 0;
            }
        }
        return $info;
    }

   ////Return an array of info (name,value)
   function skillmap_check_backup_mods_instances($instance,$backup_unique_code) {
        //First the course data
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';

        //Now, if requested, the user_data
        if (!empty($instance->userdata)) {
            $info[$instance->id.'1'][0] = get_string("responses","skillmap");
            if ($ids = skillmap_answer_ids_by_instance ($instance->id)) {
                $info[$instance->id.'1'][1] = count($ids);
            } else {
                $info[$instance->id.'1'][1] = 0;
            }
        }
        return $info;
    }

    //Return a content encoded to support interactivities linking. Every module
    //should have its own. They are called automatically from the backup procedure.
    function skillmap_encode_content_links ($content,$preferences) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        //Link to the list of skillmaps
        $buscar="/(".$base."\/mod\/skillmap\/index.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@SKILLMAPINDEX*$2@$',$content);

        //Link to skillmap view by moduleid
        $buscar="/(".$base."\/mod\/skillmap\/view.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@SKILLMAPVIEWBYID*$2@$',$result);

        return $result;
    }

    // INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

    //Returns an array of skillmaps id
    function skillmap_ids ($course) {

        global $CFG;

        return get_records_sql ("SELECT a.id, a.course
                                 FROM {$CFG->prefix}skillmap a
                                 WHERE a.course = '$course'");
    }
   
    //Returns an array of skillmap_answers id
    function skillmap_answer_ids_by_course ($course) {

        global $CFG;

        return get_records_sql ("SELECT s.id , s.skillmapid
                                 FROM {$CFG->prefix}skillmap_answers s,
                                      {$CFG->prefix}skillmap a
                                 WHERE a.course = '$course' AND
                                       s.skillmapid = a.id");
    }

    //Returns an array of skillmap_answers id
    function skillmap_answer_ids_by_instance ($instanceid) {

        global $CFG;

        return get_records_sql ("SELECT s.id , s.skillmapid
                                 FROM {$CFG->prefix}skillmap_answers s
                                 WHERE s.skillmapid = $instanceid");
    }
?>
