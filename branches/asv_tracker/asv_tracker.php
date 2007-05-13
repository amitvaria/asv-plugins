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
	
	if(!empty($asv_data)){
		$event = ($event == 'list') ? 'article' : $event; //I do this because the event list performing actions on multiple articles
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
	global $vars;
	$actions = array();
	
	$incoming = psa($vars);
	$step = gps('save') ? 'save' : $step;
	$step = gps('publish') ? 'publish' : $step;			
	
	switch($step){
			case "publish":	$actions[] = asv_actions_array('Created - '.($incoming['Title']), -1);
											break;
			case "save":    $actions[] = asv_actions_array('Updated', $incoming['ID']);
											break;
	}
	return $actions;
}

function asv_tracker_list($step=''){	
	$selected = ps('selected');
	$method = ps('edit_method');
	
	$actions = array();
	
	if($step == "list_multi_edit"){			
			switch ($method){
				case 'delete':					$action = 'Delete';
																break;
				case 'changeauthor':    $value = ps('AuthorID');
																$action = 'Updated author to '.$value;
																break; 
				case 'changecategory1': $value = ps('Category1');
																$action = 'Updated category1 to '.$value;
																break;
				case 'changecategory2':	$value = ps('Category2');
																$action='Updated category2 to '.$value;
																break;
				case 'changecomments':	$value = ps('Annotate');
																($value==0) ? $value="off" : $value="on";
																$action = 'Updated comments to '.$value;
																break;
				case 'changesection':		$value = ps('Section');
																$action = 'Updated sections to '.$value;
																break;
				case 'changestatus':		$value = ps('Status');
																$action = 'Updated status to '.$value;
																break;
			}
			
			foreach ($selected as $id){
				$actions[] = asv_actions_array($action, $id);
			}
			return $actions;
	}
	return '';
}

function asv_tracker_image($step=''){
	$id = ps('id');
	$actions = array();
	
	switch($step){
		case 'image_save':		$actions[] = asv_actions_array('Created/Updated', $id);
													break;
		case 'image_delete':	$actions[] = asv_actions_array('Deleted', $id);;
													break;
	}
	
	return $actions;
}

function asv_tracker_file($step=''){
	$id = ps('id');
	$actions = array();
	
	switch($step){
		case 'file_delete':				$actions[] = asv_actions_array('Deleted' ,$id);
															break;
		case 'file_save':					$actions[] = asv_actions_array('Updated', $id);
															break;
		case 'file_reset_count':	$actions[] = asv_actions_array('Reset Count', $id);
															break;
		case 'file_replace':			$actions[] = asv_actions_array('Replaced', $id);
															break;
		case 'file_insert':				$name = file_get_uploaded_name();
															$actions[] = asv_actions_array('Inserted '.$name, $id);
															break;
		case 'file_create':				$actions[] = asv_actions_array('Created - '.gps('filename'), -1);
															break;
	}
	
	return $actions;
}

function asv_tracker_link($step=''){
	global $vars;
	
	$varray = gpsa($vars);
	extract($varray);
	$actions = array();
	
	switch($step){
		case 'link_post':				$actions[] = asv_actions_array('Created - '.$linkname, -1);
														break;
		case 'link_save':				$actions[] = asv_actions_array('Updated', $id);
														break;
		case 'link_multi_edit': $method = ps('edit_method');
														$selected = ps('selected');
														if ($selected && $method == 'delete'){
															foreach ($selected as $id){
																$id = assert_int($id);
																$actions[] = asv_actions_array('Deleted', $id);
															}
														}
	}
	
	return $actions;
}

function asv_tracker_section($step=''){
	$name = ps('name');													
	$actions = array();
	
	switch($step){
		case 'section_create':	$actions[] = asv_actions_array('Created - '.$name, -1);
														break;
		case 'section_save':		$actions[] = asv_actions_array('Updated - '.$name, -1);
														break;
		case 'section_delete':	$actions[] = asv_actions_array('Deleted - '.$name, -1);
														break;
	}
	return $actions;
}

function asv_tracker_page($step=''){
	$actions = array();
	
	switch($step){
		case 'page_delete':	$name = ps('name');
												$actions[] = ($name != 'default') ? asv_actions_array('Deleted - '.$name, -1) : '';
												break;
		case 'page_save':		extract(doSlash(gpsa(array('name', 'html', 'copy'))));
												if($copy){
													$newname = doSlash(trim(preg_replace('/[<>&"\']/', '', gps('newname'))));
													if ($newname and !safe_field('name', 'txp_page', "name = '$newname'")){
														$actions[] = asv_actions_array('Copied - '.$name.' to '.$newname, -1);
													}
												}
												else{
													$actions[] = asv_actions_array('Updated - '.$name, -1);
												}
												break;
	}
	
	return $actions;	
}

function asv_tracker_form($step=''){
	global $vars;
	extract(doSlash(gpsa($vars)));
	$name = doSlash(trim(preg_replace('/[<>&"\']/', '', gps('name'))));
	$actions = array();
	
	switch($step){
		case 'form_save':				if($savenew and $name && in_array($type, array('article','comment','link','misc','file'))){
															$exists = safe_field('name', 'txp_form', "name = '$name'");
															if (!$exists){
																$actions[] = asv_actions_array('Created - '.$name, -1);
															}
														}
														elseif($name && in_array($type, array('article','comment','link','misc','file'))){
															$actions[] = asv_actions_array('Updated - '.$name, -1);
														}
														break;
		case 'form_multi_edit':	global $essential_forms;
														$method = ps('edit_method');
														$forms = ps('selected_forms');
														if (is_array($forms) && $method == 'delete'){
															foreach ($forms as $name){
																if (!in_array($name, $essential_forms)){
																	$actions[] = asv_actions_array('Delete - '.$name, -1);
																}
															}
														}
														break;
	}
	return $actions;
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

function asv_actions_array($action = '',$id = -1){
	return array("action" => $action, "id" => $id);
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
