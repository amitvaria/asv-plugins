<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_search_input';

$plugin['version'] = '1.0';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Extends the functionality of the built-in txp:search_input tag';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 0; 


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
function asv_search_input($atts) // input form for search queries
{
	global $q, $permlink_mode;
	extract(lAtts(array(
		'form'    => 'search_input',
		'wraptag' => 'p',
		'size'    => '15',
		'label'   => '',
		'labelwrap' => 'p',
		'labelclass' => '',
		'button'  => '',
		'section' => '',
		'buttonclass' => '',
		'boxclass' =>''
	),$atts));	

	if ($form) {
		$rs = fetch('form','txp_form','name',$form);
		if ($rs) {
			return $rs;
		}
	}
	
	(!empty($labelclass)) ? $class = "class=".$labelclass : $class='';
	
	$head = (!empty($label) && !empty($labelwrap)) ? tag($label, $labelwrap, $class) : '';
	$buttonclassed = (!empty($buttonclass)) ? 'class="'.$buttonclass.'"' : '';
	$sub = (!empty($button)) ? '<input type="submit" '.$buttonclassed.' value="'.$button.'" />' : '';
	$out = fInput('text','q',$q, $boxclass,'','',$size);
	$out = (!empty($label)) ? $out.$sub : $out.$sub;
	$out = ($wraptag) ? tag($out,$wraptag) : $out;

	if (!$section)
		return '<form action="'.hu.'" method="get">'.$head.$out.'</form>';

	$url = pagelinkurl(array('s'=>$section));	
	return '<form action="'.$url.'" method="get">'.$head.$out.'</form>';
}

# --- END PLUGIN CODE ---

?>
