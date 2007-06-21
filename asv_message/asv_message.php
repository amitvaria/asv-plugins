<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_message';

$plugin['version'] = '1.0';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Extends txp:message';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 0; 


@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
function asv_message($atts)
{
	global $thiscomment;

    extract(lAtts(array(
        'striptags' => 'n',
        'maxwords'  => -1,
		'wraptag'		=> 'p',
		'class' 	=> '',

    ),$atts));

	assert_comment();
	$thismessage = $thiscomment['message'];
	
	if ($striptags != 'n'){
		$thismessage = strip_tags($thismessage );
	}
	
	if ($maxwords >= 0){	
		$wrds = explode(' ', $thismessage );
		if(count($wrds) > $maxwords){
			$thismessage = '';
			for($i=0; $i<$maxwords; $i++) {
				$thismessage .= $wrds[$i].' ';
			}
			$thismessage = $thismessage .'&#8230;';
		} 
	}
	
	return doTag($thismessage, $wraptag, $class) ;
}
# --- END PLUGIN CODE ---

?>
