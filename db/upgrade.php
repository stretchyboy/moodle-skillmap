<?php  //$Id: upgrade.php,v 1.1.8.1 2008/05/01 20:39:47 skodak Exp $

// This file keeps track of upgrades to 
// the choice module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_skillmap_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

		
		echo $oldversion;
		
/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

 if ($result && $oldversion < 2009092902) { //New version in version.php
     
	 
	      
    /// Define table skillmap_learningstage to be created
        $table = new XMLDBTable('skillmap_learningstage');

    /// Adding fields to table skillmap_learningstage
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, '255', null, null, null, null, null, null);

    /// Adding keys to table skillmap_learningstage
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

    /// Launch create table for skillmap_learningstage
        $result = $result && create_table($table);
				
				
				
		/// Define field timemodified to be added to skillmap_responce
        $table = new XMLDBTable('skillmap_responce');
        $field = new XMLDBField('timemodified');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null, 'survey');

    /// Launch add field timemodified
        $result = $result && add_field($table, $field);
				
				
		/// Define field skillmap to be added to skillmap_responce
        $table = new XMLDBTable('skillmap_responce');
        $field = new XMLDBField('skillmap');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '20', XMLDB_UNSIGNED, null, null, null, null, null, 'timemodified');

    /// Launch add field skillmap
        $result = $result && add_field($table, $field);
				
				
		/// Define field learningstage to be added to skillmap
        $table = new XMLDBTable('skillmap');
        $field = new XMLDBField('learningstage');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '20', XMLDB_UNSIGNED, null, null, null, null, null, 'timemodified');

    /// Launch add field learningstage
        $result = $result && add_field($table, $field);
				
				
		/// Rename field interested_label on table skillmap_survey to teach_question
        $table = new XMLDBTable('skillmap_survey');
        $field = new XMLDBField('interested_label');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, null, null, 'name');

    /// Launch rename field interested_label
        $result = $result && rename_field($table, $field, 'teach_question');
				
		/// Define field interested_question to be dropped from skillmap_survey
        $table = new XMLDBTable('skillmap_survey');
        $field = new XMLDBField('interested_question');

    /// Launch drop field interested_question
        $result = $result && drop_field($table, $field);
				
    /// Launch add field id
        $result = $result && add_field($table, $field);
				
	 
 }


 if ($result && $oldversion < 2009092904) { //New version in version.php
    /// Define field learn_question to be added to skillmap_survey
        $table = new XMLDBTable('skillmap_survey');
        $field = new XMLDBField('learn_question');
        $field->setAttributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, null, null, 'teach_question');

    /// Launch add field learn_question
        $result = $result && add_field($table, $field);
 
 }
 
  if ($result && $oldversion < 2009092905) { //New version in version.php

    /// Rename field interested on table skillmap_responce_skill to learn
        $table = new XMLDBTable('skillmap_responce_skill');
        $field = new XMLDBField('interested');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', null, null, null, null, null, '0', 'skilllevel');

    /// Launch rename field interested
        $result = $result && rename_field($table, $field, 'teach');
				

 }
 
  
  if ($result && $oldversion < 2009092906) { //New version in version.php

  
    /// Define field learn to be added to skillmap_responce_skill
        $table = new XMLDBTable('skillmap_responce_skill');
        $field = new XMLDBField('learn');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, null, 'teach');

    /// Launch add field learn
        $result = $result && add_field($table, $field);
 
 }

     if ($result && $oldversion < 2009100102) {

    /// Define key survey (foreign) to be dropped form skillmap_responce
        $table = new XMLDBTable('skillmap_responce');
        $key = new XMLDBKey('survey');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('survey'), 'skillmap_survey', array('id'));

    /// Launch drop key survey
        $result = $result && drop_key($table, $key);
				
				
    /// Define field survey to be dropped from skillmap_responce
        $table = new XMLDBTable('skillmap_responce');
        $field = new XMLDBField('survey');

    /// Launch drop field survey
        $result = $result && drop_field($table, $field);
    }
 
		
    return $result;
}

?>
