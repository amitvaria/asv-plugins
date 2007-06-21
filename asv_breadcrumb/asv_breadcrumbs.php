<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_breadcrumbs';

$plugin['version'] = '1.0';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Adds breadcrumbs to TXP';

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
function asv_breadcrumb($atts)
{
	global $pretext,$thisarticle,$sitename;
	
	extract(lAtts(array(
	'wraptag' => 'p',
	'sep' => '&#160;&#187;&#160;',
	'link' => 'y',
	'label' => $sitename,
	'title' => '',
	'class' => '',
	'linkclass' => 'noline',
	'showsection' => 'true',
	'homepage' => 'false',
	'catsep' => '/'
	),$atts));
	
	$linked = ($link == 'y')? true: false;
	$homepaged = ($homepage == 'y')? true: false;
	$showsectioned = ($showsection == 'y')? true: false;
	if ($linked) $label = doTag($label,'a',$linkclass,' href="'.hu.'"');
	
	$content = array();
	extract($pretext);
	
	if($showsectioned && !empty($s) && $s!= 'default')
	{ 
		$section_title = ($title) ? fetch_section_title($s) : $s;
		$section_title_html = escape_title($section_title);
		$content[] = ($linked)? (
		doTag($section_title_html,'a',$linkclass,' href="'.pagelinkurl(array('s'=>$s)).'"')
		):$section_title_html;
	}
	
	if(empty($thisarticle))
	{
		$category1 = empty($c)? '': $c;
	}
	else{
		$category1 = empty($thisarticle['category1'])? "" : $thisarticle['category1'];
		$category2 = empty($thisarticle['category2'])? "" : $thisarticle['category2'];
	}

	foreach (getTreePath($category1, 'article') as $cat) 
	{
		if ($cat['name'] != 'root') 
		{
			$category_title_html = $title? escape_title($cat['title']) : $cat['name'];
			$cat1holder[] = ($linked)? 
			doTag($category_title_html,'a',$linkclass,' href="'.pagelinkurl(array('c'=>$cat['name'])).'"')
			:$category_title_html;
		}
	}

	foreach (getTreePath($category2, 'article') as $cat) 
	{
		if ($cat['name'] != 'root') 
		{
			$category_title_html = $title? escape_title($cat['title']) : $cat['name'];
			$cat2holder[] = ($linked)? 
			doTag($category_title_html,'a',$linkclass,' href="'.pagelinkurl(array('c'=>$cat['name'])).'"')
			:$category_title_html;
		}
	}
	
	if(!empty($cat2holder))
	{
	 $content[] = join($sep, $cat1holder).$catsep.join($sep, $cat2holder);
	}
	else
	{
		if(!empty($cat1holder))
		{
			$content[] = join($sep, $cat1holder);
		}
	}

	//Add the label at the end, to prevent breadcrumb for home page
	//		if (!empty($content)) $content = array_merge(array($label),$content);
	if ($homepaged)
	{ 
		$content = array_merge(array($label),$content);
	}
	else 
	{
		if (!empty($content)) 
			$content = array_merge(array($label),$content);
	}
	
	//Add article title without link if we're on an individual archive page?
	return doTag(join($sep, $content), $wraptag, $class);
}
# --- END PLUGIN CODE ---
?>