<?PHP //$Id: block_resource_tags.php,v 1.28.2.7 2008/07/17 04:22:11 scyrma Exp $

define('RESOURCEDEFAULTTIMEWITHIN', 90);
define('RESOURCEDEFAULTNUMBEROFTAGS', 20);
define('RESOURCEDEFAULTSORT', 'name');

require_once($CFG->dirroot .'/tag/lib.php');

class block_resource_tags extends block_base {
    function init() {
        $this->version = 2007101509;
        $this->title = get_string('blocktagstitle', 'resource_taggedfile');
    }

    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return false;
    }

    function applicable_formats() {
        return array('all' => true, 'my' => false, 'tag' => false);
    }

    function instance_allow_config() {
        return true;
    }

    function specialization() {

        // load userdefined title and make sure it's never empty
        if (empty($this->config->title)) {
            $this->title = get_string('blocktagstitle','resource_taggedfile');
        } else {
            $this->title = $this->config->title;
        }
    }

    function get_content() {

        global $CFG, $SITE, $COURSE, $USER;
				//echo '<pre>'.var_export($CFG, true).'</pre>';
        if (empty($CFG->usetags)) {
            $this->content->text = '';
            return $this->content;
        }

        if (empty($this->config->timewithin)) {
            $this->config->timewithin = RESOURCEDEFAULTTIMEWITHIN;
        }
        if (empty($this->config->numberoftags)) {
            $this->config->numberoftags = RESOURCEDEFAULTNUMBEROFTAGS;
        }
        if (empty($this->config->sort)) {
            $this->config->sort = RESOURCEDEFAULTSORT;
        }

        if ($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        /// Get a list of tags

        $timewithin = time() - $this->config->timewithin * 24 * 60 * 60; /// convert to seconds

        $sql  = 'SELECT t.id, t.tagtype, t.rawname, t.name, COUNT(DISTINCT ti.id) AS ct ';
        $sql .= "FROM {$CFG->prefix}tag t, {$CFG->prefix}tag_instance ti, {$CFG->prefix}resource p ";
        $sql .= 'WHERE t.id = ti.tagid ';
        $sql .= 'AND p.id = ti.itemid ';
        $sql .= 'AND ti.itemtype = \'resource\' ';

        /*// admins should be able to read all tags      
        if (!has_capability('moodle/user:readuserresources', get_context_instance(CONTEXT_SYSTEM))) {
            $sql .= 'AND (p.publishstate = \'site\' or p.publishstate=\'public\') ';
        }*/
        $sql .= "AND ti.timemodified > {$timewithin} ";
        $sql .= 'GROUP BY t.id, t.tagtype, t.name, t.rawname ';
        $sql .= 'ORDER BY ct DESC, t.name ASC';
				
				//debugging($sql);
				
        if ($tags = get_records_sql($sql, 0, $this->config->numberoftags)) {

        /// There are 2 things to do:
        /// 1. tags with the same count should have the same size class
        /// 2. however many tags we have should be spread evenly over the
        ///    20 size classes

            $totaltags  = count($tags);
            $currenttag = 0;

            $size = 20;
            $lasttagct = -1;

            $etags = array();
            foreach ($tags as $tag) {

                $currenttag++;

                if ($currenttag == 1) {
                    $lasttagct = $tag->ct;
                    $size = 20;
                } else if ($tag->ct != $lasttagct) {
                    $lasttagct = $tag->ct;
                    $size = 20 - ( (int)((($currenttag - 1) / $totaltags) * 20) );
                }

                $tag->class = "$tag->tagtype s$size";
                $etags[] = $tag;

            }

        /// Now we sort the tag display order
            $CFG->tagsort = $this->config->sort;
            usort($etags, "resource_tags_sort");

        /// Finally we create the output
        /// Accessibility: markup as a list.
            $this->content->text .= "\n<ul class='inline-list'>\n";
						
            foreach ($etags as $tag) {
            /*    switch ($CFG->resourcelevel) {
                    case BLOG_USER_LEVEL:
                        $filtertype = 'user';
                        $filterselect = $USER->id;
                    break;

                    case BLOG_GROUP_LEVEL:
                        $filtertype = 'group';
                        $filterselect = groups_get_course_group($COURSE);
                    break;

                    case BLOG_COURSE_LEVEL:
                        $filtertype = 'course';
                        if (isset($COURSE->id)) {
                            $filterselect = $COURSE->id;
                        } else {
                            $filterselect = $this->instance->pageid;
                        }
                    break;

                    default:*/
                        /*if (isset($COURSE->id) && $COURSE->id != SITEID) {
                            $filtertype = 'course';
                            $filterselect = $COURSE->id;
                        } else {*/
                            $filtertype = 'site';
                            $filterselect = SITEID;
                       /* }
                /*    break;
                }
								*/
                $link = $CFG->wwwroot.'/mod/resource/type/taggedfile/index.php?id='.$COURSE->id.'&amp;filtertype='.$filtertype.'&amp;filterselect='.$filterselect.'&amp;tagid='.$tag->id;
                $this->content->text .= '<li><a href="'.$link.'" '.
                                        'class="'.$tag->class.'" '.
                                        'title="'.get_string('numberofentries','resource',$tag->ct).'">'.
                                        tag_display_name($tag) .'</a></li> ';
            }
            $this->content->text .= "\n</ul>\n";

        }
        return $this->content;
    }

    function instance_config_print() {
        global $CFG;

    /// set up the numberoftags select field
        $numberoftags = array();
        for($i=1;$i<=50;$i++) $numberoftags[$i] = $i;

    //// set up the timewithin select field
        $timewithin = array();
        $timewithin[10]  = get_string('numdays', '', 10);
        $timewithin[30]  = get_string('numdays', '', 30);
        $timewithin[60]  = get_string('numdays', '', 60);
        $timewithin[90]  = get_string('numdays', '', 90);
        $timewithin[120] = get_string('numdays', '', 120);
        $timewithin[240] = get_string('numdays', '', 240);
        $timewithin[365] = get_string('numdays', '', 365);

    /// set up sort select field
        $sort = array();
        $sort['name'] = get_string('tagtext', 'resource_taggedfile');
        $sort['id']   = get_string('tagdatelastused', 'resource_taggedfile');


        if (is_file($CFG->dirroot .'/blocks/'. $this->name() .'/config_instance.html')) {
            print_simple_box_start('center', '', '', 5, 'blockconfigglobal');
            include($CFG->dirroot .'/blocks/'. $this->name() .'/config_instance.html');
            print_simple_box_end();
        } else {
            notice(get_string('blockconfigbad'), str_replace('blockaction=', 'dummy=', qualified_me()));
        }
    }
}

function resource_tags_sort($a, $b) {
    global $CFG;

    if (empty($CFG->tagsort)) {
        return 0;
    } else {
        $tagsort = $CFG->tagsort;
    }

    if (is_numeric($a->$tagsort)) {
        return ($a->$tagsort == $b->$tagsort) ? 0 : ($a->$tagsort > $b->$tagsort) ? 1 : -1;
    } elseif (is_string($a->$tagsort)) {
        return strcmp($a->$tagsort, $b->$tagsort);
    } else {
        return 0;
    }
}

?>
