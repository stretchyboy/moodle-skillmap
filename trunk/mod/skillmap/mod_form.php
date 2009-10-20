<?php
require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_skillmap_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $SKILLMAP_SHOWRESULTS, $SKILLMAP_PUBLISH, $SKILLMAP_DISPLAY;

        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('skillmapname', 'skillmap'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('htmleditor', 'text', get_string('skillmaptext', 'skillmap'));
        $mform->setType('text', PARAM_RAW);
        $mform->addRule('text', null, 'required', null, 'client');
        $mform->setHelpButton('text', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');

        $mform->addElement('format', 'format', get_string('format'));

//-------------------------------------------------------------------------------
        $repeatarray=array();
        $repeatarray[] = &MoodleQuickForm::createElement('header', '', get_string('skillmap','skillmap').' {no}');
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'option', get_string('skillmap','skillmap'));
        $repeatarray[] = &MoodleQuickForm::createElement('text', 'limit', get_string('limit','skillmap'));
        $repeatarray[] = &MoodleQuickForm::createElement('hidden', 'optionid', 0);

				
				
				$mform->addElement('header', 'timerestricthdr', get_string('limit', 'skillmap'));
				
				
				if($menu_survey = get_records_menu('skillmap_survey'))
				{
					$mform->addElement('select', 'survey', get_string('whichsurvey', 'skillmap'), $menu_survey);
				}
				
				if ($menu_learningstage = get_records_menu('skillmap_learningstage'))
				{
					$mform->addElement('select', 'learningstage', get_string('whichlearningstage', 'skillmap'), $menu_learningstage);
        }
				/*
				
        $menuoptions=array();
        $menuoptions[0] = get_string('disable');
        $menuoptions[1] = get_string('enable');
        $mform->addElement('header', 'timerestricthdr', get_string('limit', 'skillmap'));
        $mform->addElement('select', 'limitanswers', get_string('limitanswers', 'skillmap'), $menuoptions);
        $mform->setHelpButton('limitanswers', array('limit', get_string('limit', 'skillmap'), 'skillmap'));

        if ($this->_instance){
            $repeatno=count_records('skillmap_options', 'skillmapid', $this->_instance);
            $repeatno += 2;
        } else {
            $repeatno = 5;
        }

        $repeateloptions = array();
        $repeateloptions['limit']['default'] = 0;
        $repeateloptions['limit']['disabledif'] = array('limitanswers', 'eq', 0);
        $mform->setType('limit', PARAM_INT);

        $repeateloptions['option']['helpbutton'] = array('options', get_string('modulenameplural', 'skillmap'), 'skillmap');
        
				
				$mform->setType('option', PARAM_CLEAN);

        $mform->setType('optionid', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                    $repeateloptions, 'option_repeats', 'option_add_fields', 3);


*/

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'timerestricthdr', get_string('timerestrict', 'skillmap'));
        $mform->addElement('checkbox', 'timerestrict', get_string('timerestrict', 'skillmap'));
        $mform->setHelpButton('timerestrict', array("timerestrict", get_string("timerestrict","skillmap"), "skillmap"));


        $mform->addElement('date_time_selector', 'timeopen', get_string("skillmapopen", "skillmap"));
        $mform->disabledIf('timeopen', 'timerestrict');

        $mform->addElement('date_time_selector', 'timeclose', get_string("skillmapclose", "skillmap"));
        $mform->disabledIf('timeclose', 'timerestrict');

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'miscellaneoussettingshdr', get_string('miscellaneoussettings', 'form'));

        $mform->addElement('select', 'display', get_string("displaymode","skillmap"), $SKILLMAP_DISPLAY);

        $mform->addElement('select', 'showresults', get_string("publish", "skillmap"), $SKILLMAP_SHOWRESULTS);

        $mform->addElement('select', 'publish', get_string("privacy", "skillmap"), $SKILLMAP_PUBLISH);
        $mform->disabledIf('publish', 'showresults', 'eq', 0);

        $mform->addElement('selectyesno', 'allowupdate', get_string("allowupdate", "skillmap"));

        $mform->addElement('selectyesno', 'showunanswered', get_string("showunanswered", "skillmap"));


//-------------------------------------------------------------------------------
        $features = new stdClass;
        $features->groups = true;
        $features->groupings = true;
        $features->groupmembersonly = true;
        $features->gradecat = false;
        $this->standard_coursemodule_elements($features);
//-------------------------------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values){
			/*
        if (!empty($this->_instance) && ($options = get_records_menu('skillmap_options','skillmapid', $this->_instance, 'id', 'id,text'))
               && ($options2 = get_records_menu('skillmap_options','skillmapid', $this->_instance, 'id', 'id,maxanswers')) ) {
            $skillmapids=array_keys($options);
            $options=array_values($options);
            $options2=array_values($options2);

            foreach (array_keys($options) as $key){
                $default_values['option['.$key.']'] = $options[$key];
                $default_values['limit['.$key.']'] = $options2[$key];
                $default_values['optionid['.$key.']'] = $skillmapids[$key];
            }
        }
        if (empty($default_values['timeopen'])) {
            $default_values['timerestrict'] = 0;
        } else {
            $default_values['timerestrict'] = 1;
        }
				*/
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
				/*
        $skillmaps = 0;
        foreach ($data['option'] as $option){
            if (trim($option) != ''){
                $skillmaps++;
            }
        }

        if ($skillmaps < 1) {
           $errors['option[0]'] = get_string('fillinatleastoneoption', 'skillmap');
        }

        if ($skillmaps < 2) {
           $errors['option[1]'] = get_string('fillinatleastoneoption', 'skillmap');
        }
				*/
        return $errors;
    }

}
?>
