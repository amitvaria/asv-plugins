<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_petiton';

$plugin['version'] = '0.1';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Allow petitioners to show their support';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 0; 


@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
function asv_petition_request($atts, $thing)
{
	extract(lAtts(array(
		'form' => '',
		'class' => '',
		'id' =>'',
		)));
	
	if($form == ''){
		return "A form is required.";
	}
	
	return "to be implemented...";
}

function asv_petiton_name_input($atts, $things)
{
	extract(lAtts(array(
		'form' => '',
		)));
	
	return "to be implemented...";
}

function asv_petition_email_input($atts, $thing)
{
	return "to be implemented...";
	
}

function asv_petition_comment_input($att, $thing)
{
	return "to be implemented...";
}

function asv_petition($atts, $thing)
{
	return "to be implemented...";
}



# --- END PLUGIN CODE ---

?>
