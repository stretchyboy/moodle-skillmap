<?php // $Id: resource.class.php,v 1.71.2.21 2009/01/08 02:19:44 jerome Exp $
require_once ($CFG->dirroot.'/mod/resource/type/file/resource.class.php');
include_once($CFG->dirroot.'/tag/lib.php');

/**
* Extend the base resource class for file resources
*/
class resource_taggedfile extends resource_file {

    function resource_taggedfile($cmid=0) {
        parent::resource_file($cmid);
    }

    var $parameters;
    var $maxparameters = 5;
		
		function display_embedded()
		{
			if (!empty($this->resource->summary)) 
			{
				global $CFG;
				//echo '<pre>'.var_export($this, true).'</pre>';

				$sText = '';
				 
				$modinfo = get_fast_modinfo($this->course);
		
				// Get module icon
				if (!empty($modinfo->cms[$this->cm->id]->icon)) {
						$icon = $CFG->pixpath.'/'.urldecode($modinfo->cms[$this->cm->id]->icon);
				} else {
						$icon = "$CFG->modpixpath/{$this->cm->modname}/icon.gif";
				}
		
				$name = format_string($this->cm->name);
				$alt  = get_string('modulename', $this->cm->modname);
				$alt  = s($alt);
		
				$sText  = "<img src=\"$icon\" alt=\"$alt\" class=\"icon\" />";
				$sText .= "<a title=\"$alt\" href=\"$CFG->wwwroot/mod/{$this->cm->modname}/view.php?id={$this->cm->id}\">$name</a>";
				
				$formatoptions = new object();
        $formatoptions->noclean = true;
				
				$sText .= format_text($this->resource->summary, FORMAT_MOODLE, $formatoptions, $this->course->id);
				return $sText;
      }
			//return $this->display();
			
		}
		
		function setup_elements(&$mform)
		{
			echo "setup_elements";
			global $CFG, $RESOURCE_WINDOW_OPTIONS;
			
			parent::setup_elements($mform);
			
			if (!empty($CFG->usetags)) {
					$mform->addElement('header', 'tagshdr', get_string('tags', 'tag'));
					$mform->createElement('select', 'otags', get_string('otags','tag'));
					
					$js_escape = array(
							"\r"    => '\r',
							"\n"    => '\n',
							"\t"    => '\t',
							"'"     => "\\'",
							'"'     => '\"',
							'\\'    => '\\\\'
					);
					
					$otagsselEl =& $mform->addElement('select', 'otags', get_string('otags', 'tag'), array(), 'size="5"');
					$otagsselEl->setMultiple(true);
					$this->otags_select_setup(&$mform);
					
					$mform->addElement('textarea', 'ptags', get_string('ptags', 'tag'), array('cols'=>'40', 'rows'=>'5'));
					$mform->setType('ptagsadd', PARAM_NOTAGS);
			}
		}
		
    function otags_select_setup(&$mform){
        global $CFG;
        if ($otagsselect =& $mform->getElement('otags')) {
            $otagsselect->removeOptions();
        }
        $namefield = empty($CFG->keeptagnamecase) ? 'name' : 'rawname';
        if ($otags = get_records_sql_menu('SELECT id, '.$namefield.' from '.$CFG->prefix.'tag WHERE tagtype=\'official\' ORDER by name ASC')){
            $otagsselect->loadArray($otags);
        }
    }
		
		function add_instance($resource) {
				$resourceid = parent::add_instance($resource);
				$this->add_tags_info($resourceid);
				return $resourceid;
    }
		
    function update_instance($resource) {
        $resourceid = parent::update_instance($resource);
				$this->add_tags_info($resourceid);
				return $resourceid;
    }
		
		function add_tags_info($resourceid) {
				
				$tags = array();
				if ($otags = optional_param('otags', '', PARAM_INT)) {
						foreach ($otags as $tagid) {
								// TODO : make this use the tag name in the form
								if ($tag = tag_get('id', $tagid)) {
										$tags[] = $tag->name;
								}
						}
				}
		
				$manual_tags = optional_param('ptags', '', PARAM_NOTAGS);
				$tags = array_merge($tags, explode(',', $manual_tags));
				
				tag_set('resource', $resourceid, $tags);
		}
		
		/*function set_parameters() {
        parent::set_parameters();
				
				echo "set_parameters = ".nl2br(var_export($this, true))."\n<br>";
					
				
			//	if (!empty($this->_instance)) {
        //    if($res = get_record('resource', 'id', (int)$this->_instance)) {
          //      $type = $res->type;
           // }
				//			
				
        if ($itemptags = tag_get_tags_csv('resource', $this->resource->id, TAG_RETURN_TEXT, 'default')) {
            $this->parameters['ptags'] = $itemptags;
        }
        
        if ($itemotags = tag_get_tags_array('resource', $this->resource->id, 'official')) {
            $this->parameters['otags'] = array_keys($itemotags);
        }
    }*/
		
		 function definition_after_data() {
        $mform =& $this->_form;
				print_object($mform);
				parent::definition_after_data();
		 }
}

?>
