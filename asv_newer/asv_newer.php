<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_newer';

$plugin['version'] = '1.0';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Extension of txp:newer';

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
function asv_newer($atts, $thing = false, $match = '')
{
	global $thispage, $pretext, $permlink_mode;

	extract(lAtts(array(
		'showalways' => 0,
		'link' => 'y',
	), $atts));
	
	$linked = ($link == 'y') ? true : false;
	$numPages = $thispage['numPages'];
	$pg				= $thispage['pg'];

	if ($numPages > 1 and $pg > 1)
	{
		$nextpg = ($pg - 1 == 1) ? 0 : ($pg - 1);

		$url = pagelinkurl(array(
			'pg'		 => $nextpg,
			's'			 => @$pretext['s'],
			'c'			 => @$pretext['c'],
			'q'			 => @$pretext['q'],
			'author' => @$pretext['author']
		));

		if ($thing && $linked)
		{
			return '<a href="'.$url.'"'.
				(empty($title) ? '' : ' title="'.$title.'"').
				'>'.parse($thing).'</a>';
		}
		
		if ($thing && !$linked)
		{
			return parse($thing);
		}

		return $url;
	}

	return ($showalways) ? parse($thing) : '';
}
# --- END PLUGIN CODE ---

?>
