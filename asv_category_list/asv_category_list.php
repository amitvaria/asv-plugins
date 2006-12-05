<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_category_list';

$plugin['version'] = '1.1';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Extends the functionality of category_list';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 0; 


@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---
h1. asv_category_list


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
function asv_category_list($atts) {
	global $s, $c;

	extract(lAtts(array(
		'active_class' => '',
		'break'        => br,
		'categories'   => '',
		'class'        => __FUNCTION__,
		'exclude'      => '',
		'label'        => '',
		'labeltag'     => '',
		'parent'       => '',
		'section'      => '',
		'this_section' => 0,
		'type'         => 'article',
		'wraptag'      => '',
		'activecategories' => 'n',
	), $atts));

	if ($categories) {
		$categories = do_list($categories);
		$categories = join("','", doSlash($categories));

		$rs = safe_rows_start('name, title', 'txp_category', 
		"type = '".doSlash($type)."' and name in ('$categories') order by field(name, '$categories')");
	}

	else {
		if ($exclude) {
			$exclude = do_list($exclude);

			$exclude = join("','", doSlash($exclude));

			$exclude = "and name not in('$exclude')";
		}

		if ($parent) {
			$qs = safe_row('lft, rgt', 'txp_category', "name = '".doSlash($parent)."'");

			if ($qs) {
				extract($qs);

				$rs = safe_rows_start('name, title', 'txp_category', 
					"(lft between $lft and $rgt) and type = '".doSlash($type)."' and name != 'default' $exclude order by lft asc");
			}
		}

		else {
			if($activecategories == 'n'){
				$rs = safe_rows_start('name, title', 'txp_category', 
				"type = '$type' and name not in('default','root') $exclude order by name");
			}
			else{
				$table = ($type == 'article') ? 'textpattern' : '';
				$table = ($type == 'image') ? 'txp_image' : '';
				$table = ($type == 'file') ? 'txp_file' : '' ;
				$table = ($type == 'link') ? 'txp_link' : '' ;

				$rs = safe_rows_start('name, title', $table, 
				"group by category");
				print_r($rs);
			}
		}
	}

	if ($rs) {
		$out = array();

		while ($a = nextRow($rs)) {
			extract($a);

			if ($name) {
				$section = ($this_section) ? ( $s == 'default' ? '' : $s ) : $section;

				$out[] = tag(str_replace('& ', '&#38; ', $title), 'a', 
				( ($active_class and ($c == $name)) ? ' class="'.$active_class.'"' : '' ).
				' href="'.pagelinkurl(array('s' => $section, 'c' => $name)).'"'
				);
			}
		}

		if ($out)
		{
			return doLabel($label, $labeltag).doWrap($out, $wraptag, $break, $class);
		}			
	}

	return '';
}

# --- END PLUGIN CODE ---

?>
