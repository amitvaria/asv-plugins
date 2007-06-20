<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_recent_comments';

$plugin['version'] = '1.8';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Extends on the functionality of txp:recent_comments';

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
function asv_recent_comments($atts)
{
	global $pretext;
	extract(lAtts(array(
		'break'		 => br,
		'class'		 => __FUNCTION__,
		'label'		 => '',
		'labeltag' => '',
		'limit'		 => 10,
		'sort'     => 'posted desc',
		'wraptag'	 => '',
		'form'		=> '',
		'saware'	=> 'n',
		'caware'		=> 'n',
		'sections'		=> '',
		'categories'	=> '',
		'sexclude'	=> '',
		'cexclude'	=> '',
		'showhome' => 'y',
		'idaware' => 'n',
		'id' => '',
		'clabel' => '',
		'slabel' => '',
		'idexclude' => ''
	), $atts));
	
	($idaware == 'y') ? $id = $pretext['id'] : $id;
	if($id){
		$rs = safe_rows_start('*', 'txp_discuss', 
		"visible = ".VISIBLE."  and parentid = $id order by $sort limit 0,$limit");
	}
	else{
		($saware == 'y') ? $sections = $pretext['s'] : $sections;
		($sections == 'default' && $showhome='y') ? $sections = '' : $sections;
		if ($sections)
		{
			$sections = do_list($sections);
			$sections = join("','", doSlash($sections));			
			$sinclude = "and textpattern.Section in('$sections')";			
		}
		
		($caware == 'y') ? $categories = $pretext['c'] : $categories;		
		if($categories){
			$categories = do_list($categories);
			$categories = join("','", doSlash($categories)); 		
			$cinclude = "and (textpattern.Category1 in('$categories') OR textpattern.Category2 in('$categories'))";
		}
		
		if ($sexclude || $cexclude){
				$sexclude = do_list($sexclude);
				$sexclude = join("','", doSlash($sexclude));
				$sexclude = "and textpattern.Section not in('$sexclude')";
				
				$cexclude = do_list($cexclude);
				$cexclude = join("','", doSlash($cexclude));
				$cexclude = "and (textpattern.Category1 not in('$cexclude') OR textpattern.Category2 not in('$cexclude'))";
		}
		if ($idexclude){
				$idexclude = do_list($idexclude);
				$idexclude = join("','", doSlash($idexclude));
				$idexclude = "and textpattern.ID not in(1)";
		
		}
	if(empty($sections) && empty($categories) && empty($sexclude) && empty($cexclude) && empty($idexclude)){	
		$rs = safe_rows_start('*', 'txp_discuss', 
			"visible = ".VISIBLE." order by $sort limit 0,$limit");
			
	}
	else{
		$rs = safe_rows_start('txp_discuss.*, textpattern.Section, textpattern.Category1, textpattern.Category2', 'txp_discuss, textpattern', "txp_discuss.parentID = textpattern.ID AND visible = ".VISIBLE." $sinclude $cinclude $sexclude $cexclude $idexclude order by $sort limit 0,$limit");
	}
	}
	($form != '') ? $formed = fetch_form($form) : '';
		
	if ($rs)
	{
		$out = array();

		while ($c = nextRow($rs))
		{
			$a = safe_row('*, ID as thisid, unix_timestamp(Posted) as posted', 
				'textpattern', 'ID = '.$c['parentid']);

			if ($a['Status'] >= 4)
			{
				if($formed){
					populateArticleData($a);
					$GLOBALS['thiscomment'] = $c;
					$out[] = parse($formed);
					unset($GLOBALS['thiscomment']);
					unset($GLOBALS['thisarticle']);
				}
				else{
					$out[] = href(
						$c['name'].' ('.escape_title($a['Title']).')', 
						permlinkurl($a).'#c'.$c['discussid']
					);
				}
			}
		}
		
		(!empty($slabel) && !empty($pretext['s'])) ? $label = preg_replace('/\$s/', fetch_section_title($pretext['s']), $slabel) : $label;
		(!empty($clabel) && !empty($pretext['c'])) ? $label = preg_replace('/\$c/', fetch_category_title($pretext['c']), $clabel) : $label;
		
		
		if ($out)
		{
			return doLabel($label, $labeltag).doWrap($out, $wraptag, $break, $class);
		}
	}

	return '';
}
# --- END PLUGIN CODE ---

?>
