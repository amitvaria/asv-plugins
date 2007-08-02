<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_enclosure';

$plugin['version'] = '1.0';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Add Enclosure';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 


@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
register_callback('asv_enclosure', 'atom_entry');
register_callback('asv_enclosure', 'rss_entry');

function asv_enclosure($event, $step) {	
	global $thisarticle;
	if(isset($thisarticle['enclosure']) && ($thisarticle['enclosure']!=null || $thisarticle['enclosure']!=""))
	{
		return '<enclosure url="http://www.viddler.com/flash/publisher.swf?key='.$thisarticle['enclosure'].'" length="441" type="application/x-shockwave-flash" />';
	}
}




# --- END PLUGIN CODE ---

?>
