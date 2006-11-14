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

<h1>asv_tracker</h1>
<p>I created this plugin to allow administrators to track changes to their database. You can view changes under 'Extensions > txp tracker', but for right now it will only changes to articles, files, and images. The current vision is to complete adding all the possible ways to change the database, allow user granularity on which changes to track, and eventually email notification on changes.</p>

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

function asv_tracker(){
	global $txp_user;
	
	asv_create_table();
	$event = gps('event');
	$action = '';
    
	$func = 'asv_tracker_'.$event;
	
	if(is_callable($func)){
		$asv_data = $func(gps('step'));
	}
	else{
		$action = gps('step');
		$id = -1;
	}
	
	if(!empty($asv_data) && $event != 'list'){
		extract($asv_data);
		safe_insert(safe_pfx_j('asv_tracker'), 'user="'.doSlash($txp_user).'", event="'.doSlash($event).'", action="'.doSlash($action).'", action_id="'.assert_int($id).'"');
	}
	
	if(!empty($asv_data) && $event == 'list'){
		$event='article'; //I do this because the event list performing actions on multiple articles
		foreach ($asv_data as $asv_item){
			extract($asv_item);
			safe_insert(safe_pfx_j('asv_tracker'), 'user="'.doSlash($txp_user).'", event="'.doSlash($event).'", action="'.doSlash($action).'", action_id="'.assert_int($id).'"');
		}
	}
}

function asv_create_table(){
	safe_query("CREATE TABLE IF NOT EXISTS ".safe_pfx_j('asv_tracker')."(  id int(4) NOT NULL auto_increment,  user varchar(64) collate utf8_general_ci NOT NULL default '',  access timestamp NOT NULL default CURRENT_TIMESTAMP, event varchar(25) collate utf8_general_ci NOT NULL default '', action varchar(255) collate utf8_general_ci NOT NULL default '', action_id int(4), PRIMARY KEY  (id));");
}

function asv_tracker_interface($event, $step){ 	
	if($event == 'list'){$event = 'article';}//I do this because the event list performing actions on multiple articles
	
	asv_create_table();
	pagetop('TXP Tracker');
	
	$filter = gps('filter');
	$filter_value = gps('filter_value');
	$sort = gps('sort');
	$order = gps('order');
	$where = (!empty($filter)) ? doSlash($filter).'="'.doSlash($filter_value).'"' : '1=1';
	$sort_order = (!empty($sort)) ? 'order by '.doSlash($sort).' '.doSlash($order) : 'order by id DESC';
	
	$rs = safe_rows_start("*",safe_pfx_j('asv_tracker'), $where." ".$sort_order);
	
	echo startTable('list').assHead('<a href="index.php?event=asv_tracker_interface&sort=user&order='.(($order == 'ASC') ? 'DESC' : 'ASC').'">user</a>',
																	'<a href="index.php?event=asv_tracker_interface&sort=access&order='.(($order == 'ASC') ? 'DESC' : 'ASC').'">access</a>',
																	'<a href="index.php?event=asv_tracker_interface&sort=event&order='.(($order == 'ASC') ? 'DESC' : 'ASC').'">event</a>',
																	'action',
																	'<a href="index.php?event=asv_tracker_interface&sort=action_id&order='.(($order == 'ASC') ? 'DESC' : 'ASC').'">item</a>');
	while ($a = nextRow($rs)) {
		extract($a);
		echo
			tr(
				td('<a href="index.php?event=asv_tracker_interface&filter=user&filter_value='.$user.'">'.$user.'</a>').
				td($access).
				td('<a href="index.php?event=asv_tracker_interface&filter=event&filter_value='.$event.'">'.$event.'</a>').
				td($action).
				td('<a href="index.php?event=asv_tracker_interface&filter=action_id&filter_value='.$action_id.'">'.$action_id.'</a>')
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
	
	$action='';
	
	switch($step){
			case "publish":	$action = 'Create';
											break;
			case "save":    $action = 'Update';
											break;
	}
	
	return (!empty($action)) ? array("action" => $action, "id" => $incoming['ID']) :  '';
}

function asv_tracker_list($step=''){
	global $txp_user;
	
	$selected = ps('selected');
	$method = ps('edit_method');
	
	$action='';
	
	if($step == "list_multi_edit"){			
			switch ($method){
				case 'delete':					$action = 'Delete';
																break;
				case 'changeauthor':    $value = ps('AuthorID');
																$action = 'Update author to '.$value;
																break; 
				case 'changecategory1': $value = ps('Category1');
																$action = 'Update category1 to '.$value;
																break;
				case 'changecategory2':	$value = ps('Category2');
																$action='Update category2 to '.$value;
																break;
				case 'changecomments':	$value = ps('Annotate');
																($value==0) ? $value="off" : $value="on";
																$action = 'Update comments to '.$value;
																break;
				case 'changesection':		$value = ps('Section');
																$action = 'Update sections to '.$value;
																break;
				case 'changestatus':		$value = ps('Status');
																$action = 'Update status to '.$value;
																break;
			}
			
			$actions = array();
			foreach ($selected as $id){
				$actions[] = array("action" => $action, "id" => $id);
			}
			return $actions;
	}
	return '';
}

function asv_tracker_image($step=''){
	$id = ps('id');
	$action='';
	
	switch($step){
		case 'image_save':		$action = 'Create or update image '.$id;
													break;
		case 'image_delete':	$action = 'Delete image '.$id;
													break;
	}
	
	return (!empty($action)) ? array("action" => $action ,"id" => $id) :  '';
}

function asv_tracker_file($step=''){
	$id = ps('id');
	$action='';
	
	switch($step){
		case 'file_delete':				$action = 'Delete file';
															break;
		case 'file_save':					$action = 'Update file';
															break;
		case 'file_reset_count':	$action = 'Reset the count on file';
															break;
		case 'file_replace':			$action = 'Replace file';
															break;
		case 'file_insert':				$name = file_get_uploaded_name();
															$action = 'The user attempted to insert file';
															break;
		case 'file_create':				$action = 'The user attempted to create file - '.gps('filename');
															$id = -1;
															break;
	}
	
	return (!empty($action)) ? array("action" => $action, "id" => $id) : '';
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
	register_tab('extensions', 'asv_tracker_interface', "txp tracker"); 
	register_callback('asv_tracker_interface', 'asv_tracker_interface');
	add_privs('asv_tracker_interface','1,2');
	$asv_track = array(
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
	foreach ($asv_track as $asv_event){
			register_callback('asv_tracker', $asv_event);
	}
}

# --- END PLUGIN CODE ---

?>
