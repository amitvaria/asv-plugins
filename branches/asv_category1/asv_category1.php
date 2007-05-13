<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = '';

$plugin['version'] = '';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = '';

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

function asv_category1($atts, $thing = '')
{
	global $thisarticle, $s, $permlink_mode;

	assert_article();

	extract(lAtts(array(
	'class'				 => '',
	'link'				 => 'n',
	'title'				 => true,
	'section'			 => '',
	'this_section' => false,
	'wraptag'			 => '',
	), $atts));

	$linked = ($link == "y")? true: false;
	if ($thisarticle['category1'])
	{
		$section = ($this_section) ? ( $s == 'default' ? '' : $s ) : $section;
		$category = $thisarticle['category1'];

		$label = ($title) ? fetch_category_title($category) : $category;
		$label = ($thing) ? $thing : $label;
		if ($linked)
		{
			$out = '<a'.
			($permlink_mode != 'messy' ? ' rel="tag"' : '').
			' href="'.pagelinkurl(array('s' => $section, 'c' => $category)).'"'.
			($title ? ' title="'.$label.'"' : '').
			'>'.$label.'</a>';
		}

		else
		{
			$out = $label;
		}

		return doTag($out, $wraptag, $class);
	}
}
# --- END PLUGIN CODE ---

?>
