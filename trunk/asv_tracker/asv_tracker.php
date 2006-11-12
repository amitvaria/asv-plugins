<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_tracker';

$plugin['version'] = '0.7';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'This plugin lets you track changes to your install';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 


@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. Textile-formatted help goes here

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

function asv_tracker($event, $step){
    global $txp_user;
    
    safe_query("CREATE TABLE IF NOT EXISTS ".safe_pfx_j('asv_tracker')."(  id int(4) NOT NULL auto_increment,  user varchar(64) collate latin1_general_ci NOT NULL default '',  access datetime NOT NULL default '0000-00-00 00:00:00',  action varchar(255) collate latin1_general_ci NOT NULL default '',  PRIMARY KEY  (id));");
    
    $func = 'asv_tracker_'.gps('event');
    print $func;
    if(is_callable($func)){
        $func(gps('step'));
    }
    else{
        $action = '';
        $action = 'event - '.gps('event').'/ step - '.gps('step');
        safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="'.$action.'"');
    }
}

function asv_tracker_interface(){  
	safe_query("CREATE TABLE IF NOT EXISTS ".safe_pfx_j('asv_tracker')."(  id int(4) NOT NULL auto_increment,  user varchar(64) collate latin1_general_ci NOT NULL default '',  access datetime NOT NULL default '0000-00-00 00:00:00',  action varchar(255) collate latin1_general_ci NOT NULL default '',  PRIMARY KEY  (id));");
    
	pagetop('TXP Tracker');
	
	$rs = safe_rows_start("*",safe_pfx_j('asv_tracker'), "1=1 order by id");
	
	echo
	startTable('list').assHead('user','access','action');
	while ($a = nextRow($rs)) {
		extract($a);
		echo
			tr(
					td($user).
					td($access).
					td($action)
			);
		unset($user, $access, $action);
	}
	echo tr(td('purge logs'));
	echo endTable();
}

function asv_tracker_article($step=''){
    global $txp_user, $vars, $txpcfg, $prefs;

    extract($prefs);
    $incoming = psa($vars);
    
    $save = gps('save');
    if ($save) $step = 'save';
            
    $publish = gps('publish');
    if ($publish) $step = 'publish';
    
    switch($step){
        case "publish":    safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to create article #'.$incoming['ID'].'"');
                        break;
        case "save":    safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to update article #'.$incoming['ID'].'"');
                        break;
    }
}

function asv_tracker_list($step=''){
	global $txp_user;
	
	$selected = ps('selected');
	$method = ps('edit_method');
	$ids = join(', ', $selected);
	
	if($step == "list_multi_edit"){
			switch ($method){
				case 'delete':					safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to delete articles '.$ids.'"');
																break;
				case 'changeauthor':    $value = has_privs('article.edit') ? ps('AuthorID') : '';
																safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to update authors for '.$ids.' to '.$value.'"');
																break;
				case 'changecategory1': $value = ps('Category1');
																safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to update category1 for '.$ids.' to '.$value.'"');
																break;
				case 'changecategory2':	$value = ps('Category2');
																safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to update category2 for '.$ids.' to '.$value.'"');
																break;
				case 'changecomments':	$value = ps('Annotate');
																($value==0) ? $value="off" : $value="on";
																safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to update comments for '.$ids.' to '.$value.'"');
																break;
				case 'changesection':		$value = ps('Section');
																safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to update sections for '.$ids.' to '.$value.'"');
																break;
				case 'changestatus':		$value = ps('Status');
																safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to update status for '.$ids.' to '.$value.'"');
																break;
			}
	}
}

function asv_tracker_image($step=''){
	global $txp_user;
	$id = ps('id');
	
	switch($step){
		case 'image_save':		safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to create or update image #'.$id.'"');
													break;
		case 'image_delete':	safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to delete image #'.$id.'"');
													break;
	}
}

function asv_tracker_file($step=''){
	global $txp_user;
	
	switch($step){
		case 'file_delete':				safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to delete file #'.assert_int(ps('id')).'"');
															break;
		case 'file_save':					safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to update and save file #'.assert_int(ps('id')).'"');
															break;
		case 'file_reset_count':	safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to reset the count on file #'.assert_int(ps('id')).'"');
															break;
		case 'file_replace':			safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to replace file #'.assert_int(ps('id')).'"');
															break;
		case 'file_insert':				$name = file_get_uploaded_name();
															safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to insert file - '.$name.'"');
															break;
		case 'file_create':				extract(doSlash(gpsa(array('filename'))));
															safe_insert(safe_pfx_j('asv_tracker'), 'user="'.$txp_user.'", access=now(), action="The user attempted to create file - '.$filename.'"');
															break;
	}
}

function asv_tracker_link($step=''){
}

function asv_tracker_section($step=''){
}

function asv_tracker_page($step=''){
}

function asv_tracker_form($step=''){
}

function asv_tracker_css($step=''){
}

function asv_tracker_diag($step=''){
}

function asv_tracker_prefs($step=''){
}

function asv_tracker_admin($step=''){
}

function asv_tracker_log($step=''){
}

function asv_tracker_import($step=''){
}

if(@txpinterface == 'admin') {
    add_privs('asv_tracker','1,2');
    register_tab('extensions', 'asv_tracker_interface', "txp tracker"); 
    register_callback('asv_tracker_interface', 'asv_tracker_interface');
    $track = array(
                'admin',
                'article', 
                'category', 
                'css', 
                'diag', 
                'file', 
                'form', 
                'image', 
                'import', 
                'link', 
                'list', 
                'log', 
                'page', 
                'plugin', 
                'prefs', 
                'section'
            );
    foreach ($track as $event){
        register_callback('asv_tracker', $event);
    }

}

# --- END PLUGIN CODE ---

?>
