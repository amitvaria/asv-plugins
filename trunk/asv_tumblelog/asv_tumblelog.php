<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_tumblelog';

$plugin['version'] = '0.1';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Implementing the greatness of tumblelogs';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 


@include_once('../zem_tpl.php');

if (0) {
	?>
# --- BEGIN PLUGIN HELP ---


# --- END PLUGIN HELP ---
	<?php
}

# --- BEGIN PLUGIN CODE ---

if (@txpinterface == 'admin')
{
	add_privs('asv_tumblelog','1,2,3,4'); // Allow only userlevels 1,2,3,4 acess to this plugin.
	register_tab('extensions', 'asv_tumblelog', "Tumblelog");
	register_callback("asv_tumblelog", "asv_tumblelog");
}

//--------------------------------------------------------------
// Utility Functions
//--------------------------------------------------------------
function asv_tumblelog_title($active)
{
	$titles = array('Settings', 'Feeds');
	$newtitles = array();
	foreach($titles as $title)
	{
		if($title!=$active)
		{
			$title='<a href="index.php?event=asv_tumblelog&step='.$title.'">'.$title.'</a>';
		}
		array_push($newtitles, $title);
	}
	
	return tag(join($newtitles, " | "), 'h1', ' style="text-align: center;"');
}
//--------------------------------------------------------------
function asv_section_popup($Section, $id)
{
	$rs = safe_column('name', 'txp_section', "name != 'default'");
  
	if ($rs)
	{
		return selectInput('tumblelogsection', $rs, $Section, false, '', $id);
	}
  
	return false;
}
//--------------------------------------------------------------
function asv_custom_popup($Custom, $id)
{
	$rs = safe_column('val', 'txp_prefs', "name LIKE 'custom_%_set'");
  
	if ($rs)
	{
		return selectInput('sourcelink', $rs, $Custom, false, '', $id);
	}
  
	return false;
}
//--------------------------------------------------------------
function asv_form_popup($Custom, $id, $name="")
{
	$rs = safe_column('name', 'txp_form', "1=1");
  
	if ($rs)
	{
		return selectInput($id, $rs, $Custom, false, '', $id);
	}
  
	return false;
}
//--------------------------------------------------------------
function asv_cat_popup($Custom, $id, $name="")
{
	$rs = getTree('root', 'article');

	if ($rs)
	{
		return treeSelectInput($name,$rs,$Custom, $id);
	}

	return false;
}
//-------------------------------------------------------------
function get_asv_tumblelog_prefs()
{
	$out = array();
	$r = safe_rows_start('name, val', 'txp_prefs', "event='asv_tumblelo'");
	if ($r) {
		while ($a = nextRow($r)) {
			$out[$a['name']] = $a['val'];
		}
		
		return lAtts(array(
			'sourcelink'	=> '',
			'tumblelogsection' => '',
			'simplepie' => '',
			'postform'	=> '',
			'quoteform'	=> '',
			'photoform'	=> '',
			'linkform' 	=> '',
			'rssfeedpage' => '',
			),$out);

	}
	return false;
}
// -------------------------------------------------------------
function asv_tumblelog_list_multiedit_form()
{
	$methods = array(
		'delete'          => gTxt('delete'),
	);

	return event_multiedit_form('list', $methods, '','','','','');
}
//--------------------------------------------------------------
function asv_tumblelog_verifyFeed($feed, $simplepie)
{
	//Get SimplePie
	require_once($simplepie);
	
	//Create and setup SimplePie Instance
	$thefeed = new SimplePie();
	$thefeed->set_feed_url($feed);
	$thefeed->enable_cache(false);
	$thefeed->handle_content_type();
	
	//Get the feed
	$success = $thefeed->init();
	
	return ($success)? $thefeed->get_title():  false;

}

//--------------------------------------------------------------
function asv_tumblelog_verifyTable()
{
	if(safe_query("SHOW TABLES LIKE '".safe_pfx('asv_tumblelog_feeds')."'"))
	{
		$version = mysql_get_server_info();
		//Use "ENGINE" if version of MySQL > (4.0.18 or 4.1.2)
		$tabletype = ( intval($version[0]) >= 5 || preg_match('#^4\.(0\.[2-9]|(1[89]))|(1\.[2-9])#',$version)) 
						? " ENGINE=MyISAM " 
						: " TYPE=MyISAM ";
		$result = safe_query("CREATE TABLE IF NOT EXISTS `".PFX."asv_tumblelog_feeds`(
			`ID` int(11) NOT NULL auto_increment,
			`Title` varchar(255) NOT NULL default '',
			`Feed` varchar(255) NOT NULL default '',
			`Annotate` int(2) NOT NULL default '0',
			`Type` varchar(128) NOT NULL default '',
			`Category1` varchar(128) NOT NULL default '',
			`Category2` varchar(128) NOT NULL default '',
			`Keywords` varchar(255) NOT NULL default '',
			 PRIMARY KEY  (`ID`)
			 ) $tabletype PACK_KEYS=1 AUTO_INCREMENT=2 ");
	}
}
//--------------------------------------------------------------

function asv_tumblelog($event, $step)
{
	$step=($step=='')?'Settings':$step;
	
	switch($step)
	{
		case 'Settings': 
			asv_tumblelog_settings($step);
			break;
		case 'Feeds':
			asv_tumblelog_feeds($step);
			break;
	}
}

function asv_tumblelog_feeds($step)
{
	asv_tumblelog_verifyTable();
	
	$types = array('post'=>'post', 'quote'=>'quote', 'photo'=>'photo', 'link'=>'link');
	
	$message = '';
	
	extract(doSlash(lAtts(array(
		'rsspath' => '',
		'type'	=> '',
		'comments' => '',
		'category1' =>'',
		'category2' => '',
		'keywords'	=>'',
		'ID' =>'',
		),
		gpsa(array(
			'rsspath', 
			'type', 
			'comments', 
			'category1', 
			'category2', 
			'keywords',
			'ID'
			)))));
				
	extract(get_asv_tumblelog_prefs());
	
	if(gps('save_feeds'))
	{
		if(in_array($type,$types))
		{
			if($title = asv_tumblelog_verifyFeed($rsspath, $simplepie))
			{
				$title = doSlash($title);
				if($ID)
				{
					safe_update('asv_tumblelog_feeds', 
						"Feed = '$rsspath',
						Title = '$title',
						Type = '$type',
						Category1 = '$category1',
						Category2 = '$category2',
						Keywords = '$keywords',
						Annotate = '$comments'",
						"ID = $ID"
						);		
				}
				else
				{
					safe_insert('asv_tumblelog_feeds', 
						"Feed = '$rsspath',
						Title = '$title',
						Type = '$type',
						Category1 = '$category1',
						Category2 = '$category2',
						Keywords = '$keywords',
						Annotate = '$comments'"
						);					
				}
			}
			else
			{
				$message = "Not a valid feed.";
			}
		}
		else
		{
			$message = "Type is required.  - $type -";
		}
	}
	if(gps('action')=='delete')
	{
		$selected = ps('selected');
		if ($selected)
		{
			$ids = array();
			foreach ($selected as $id)
			{
				$id = assert_int($id);
				if (safe_delete('asv_tumblelog_feeds', "ID = $id"))
				{
					$ids[] = $id;
				}
			}
			$changed = join(', ', $ids);
			$message = "Removed $changed";
		}
	}	
	
	pagetop('Tumblelog', $message);
		
	echo n.n.'<form name="tumblelog-admin" method="post" action="index.php?event=asv_tumblelog&step=Feeds">'.
	hInput('step', 'Feeds').
	hInput('ID', $ID);
	
	echo asv_tumblelog_title($step).
	
		tag('<a href="index.php?event=asv_tumblelog&step=Feeds">Add New Feed</a>' ,'h3', ' style="text-align: center;"').
		
		startTable('list').
		
			tr(td('Atom/RSS Path').td(fInput('text', 'rsspath', $rsspath))).
			
			tr(td('Type').td(selectInput('type', $types, $type, 0))).
			
			tr(td('Comments').td(onoffRadio('comments', ($comments)?$comments:"1"))).
			
			tr(td('Category1').td(asv_cat_popup($category1, 'category1'))).
			
			tr(td('Category2').td(asv_cat_popup($category2, 'category2'))).
			
			tr(td('Keywords').td(fInput('text', 'keywords', $keywords))).
			
			tr(tda(fInput('submit','save_feeds', ($ID)?'save':'add',"publish", '', '', '', 4), ' colspan=2')).
			
		endTable().'</form>';
	echo tag("Current Feeds", "h3", ' style="text-align:center"').
		'<form name="longform" method="post" action="index.php?event=asv_tumblelog&step=Feeds">'.
		hInput('event', 'asv_tumblelog').
		hInput('step', 'Feeds').
		hInput('action', 'delete').
		
		n.startTable('list', '', '', '', '90%').
		n.tr(
			n.column_head('ID', 'id', 'list', true, '', '', '').
			n.column_head('Title', 'title', 'list', true, '', '', '').			
			n.column_head('Feed', 'feed', 'list', true, '', '', '').
			n.column_head('Type', 'type', 'list', true, '', '', '').
			n.column_head('Comments', 'comments', 'list', true, '', '', '').
			n.column_head('Category1', 'category1', 'list', true, '', '', '').
			n.column_head('Category2', 'category1', 'list', true, '', '', '').
			n.column_head('Keywords', 'Keywords', 'list', true, '', '', '').
			hCell()
		);
	$rs = safe_rows_start("*", "asv_tumblelog_feeds", "1=1");
	if($rs)
	{
		while($a = nextRow($rs))
		{
			extract($a);
			$link = "<a href=\"index.php?event=asv_tumblelog&step=Feeds&rsspath=$Feed&type=$Type$comments=$Annotate&category1=$Category1&category2=$Category2&keywords=$Keywords&ID=$ID\">$Title</a>";

			echo n.tr(			td($ID).td($link).td(substr($Feed,0,10).'...').td($Type).td(($Annotate)?"on":"off").td($Category1).td($Category2).td($Keywords).td(fInput('checkbox', 'selected[]', $ID)));
				
		}
		echo n.tr(
				tda( select_buttons().
 asv_tumblelog_list_multiedit_form(),' colspan="9" style="text-align: right; border: none;"'));
	}
		echo n.endTable().'</form>';
}

function asv_tumblelog_settings($step)
{
	if(gps('save_settings'))
	{
		extract(gpsa(array('sourcelink', 'tumblelogsection', 'simplepie', 'linkform', 'postform', 'quoteform', 'photoform')));
		
		($sourcelink)? set_pref('sourcelink', $sourcelink, 'asv_tumblelog', ''):''; 
		
		($tumblelogsection)? set_pref('tumblelogsection', $tumblelogsection, 'asv_tumblelog', ''):'';
		
		($simplepie)? set_pref('simplepie', $simplepie, 'asv_tumblelog', ''):'';		
		
		($postform)? set_pref('postform', $postform, 'asv_tumblelog', ''):'';	
		
		($quoteform)? set_pref('quoteform', $quoteform, 'asv_tumblelog', ''):'';	
		
		($photoform)? set_pref('photoform', $photoform, 'asv_tumblelog', ''):'';	
		
		($linkform)? set_pref('linkform', $linkform, 'asv_tumblelog', ''):'';		
		
	}
	
	pagetop('Tumblelog', (gps('save'))? 'Settings saved':'');
	
	extract(get_asv_tumblelog_prefs());
	
	echo n.n.'<form name="tumblelog-admin" method="post" action="index.php?event=asv_tumblelog">';
	
	echo asv_tumblelog_title($step).
	
		tag('General Settings' ,'h3', ' style="text-align: center;"').
		
		startTable('list').
		
			tr(td('Source Link Field').td(asv_custom_popup($sourcelink, 'sourcelink'))).
			
			tr(td('Tumblelog Section').td(asv_section_popup($tumblelogsection,
			'tumblelogsection'))).
			
			tr(td('SimplePie Path').td(fInput('text', 'simplepie', $simplepie))).
			
		endTable().
		
		tag(' ', 'p'). 
		
		tag('Forms' ,'h3', ' style="text-align: center;"').
		
		startTable('list').
		
			tr(td('Post Form').td(asv_form_popup($postform, 'postform'))).
			
			tr(td('Quote Form').td(asv_form_popup($quoteform, 'quoteform'))).
			
			tr(td('Photo Form').td(asv_form_popup($photoform, 'photoform'))).
			
			tr(td('Link Form').td(asv_form_popup($linkform, 'linkform'))).
			
			tr(tda(fInput('submit','save_settings','save',"publish", '', '', '', 4), ' colspan=2')).
			
		endTable();
		
	echo n.n.'</form>';
}

if(gps('updatefeeds')==1)
{
	
	extract(get_asv_tumblelog_prefs());
	$rs = safe_rows_start("*", "asv_tumblelog_feeds", "1=1");
	echo '<pre>';
	if($rs)
	{
		while($a = nextRow($rs))
		{
			extract($a);
			switch($Type)
			{
				case "post":
					$form = $postform;
					break;
				case "quote":
					$form = $quoteform;
					break;
				case "photo":
					$form = $photoform;
					break;
				case "link":
					$form = $linkform;
					break;
			}
			echo asv_rssgrab(array(
				'feed'	=> $Feed,
				'simplepie' => $simplepie,
				'type'	=> $Type,
				'category1'	=> $Category1,
				'category2' => $Category2,
				'section'	=> $tumblelogsection,
				'form'		=> $form,
				'linkfield'	=> $sourcelink,
				'pubdate' => '',
				'comments'	=> $Annotate
				));
		}
	}
	echo '</pre>';
	exit();

}

function asv_rssgrab($atts)
{
	global $prefs, $txpcfg;
	extract($prefs);
	
	extract(lAtts(array(
			'feed'       => '',
			'simplepie'  => $txpcfg['txpath'].'/lib/simplepie.inc',
			'type'		=> 'simple',
			'category1'	=> '',
			'category2' => '',
			'section'	=> 'default',
			'form'		=> '',
			'linkfield'	=> 'custom_1',
			'pubdate' => '',
			'comments'	=> 'on',
			),$atts));
			
	$message = '';		
	
	//Get SimplePie
	require_once($simplepie);
	
	//Create and setup SimplePie Instance
	$thefeed = new SimplePie();
	$thefeed->set_feed_url($feed);
	$thefeed->set_favicon_handler('./plugins/asv_rssgrab.php', 'favicon');
	$thefeed->enable_cache(false);
	$thefeed->handle_content_type();
	
	$message .= "Getting $feed\r\n";
	//Get the feed
	$success = $thefeed->init();
	
	if($success) {
		$message .= "\tSuccess!\r\n";
		$feeditems = $thefeed->get_items();
		$favicon = $thefeed->get_favicon();
		$message .= "\tFavicon - ".$favicon."\r\n";
		foreach($feeditems as $feeditem) {		
			// Get item title
			$out['title'] = addslashes($feeditem->get_title());
			$feeditems = $thefeed->get_items();
			
			//Get the permalink
			$out['permalink'] = $feeditem->get_link();		
			
			//Get the image
			$out['image'] = $favicon;
			
			// Check and retrieve date
			if($pubdate!='')
			{
				$dateTaken = $feeditem->get_item_tags('http://purl.org/dc/elements/1.1/', 'date.Taken');
				print_r($dateTaken[0]['data']);
				die("not yet implemented");		
			}
			elseif($feeditem->get_date()) {
				$out["posted"] = $feeditem->get_date('U');
				$when = "from_unixtime(".$out['posted'].")";
			}
			else
			{
				$when = 'now()';
			}	
			
			//Get the body
			if($type=="media")
			{
				$encs = $feeditem->get_enclosures();
				foreach($encs as $enclosure)
				{
					print_r($enclosure);
					if(!is_null($enclosure->get_link())){
						$enc_link = $enclosure->get_link();
					}
					else
					{
						$enc_link = $enc_link;
					}
					$enc_link = str_replace('&amp;', '&', $enc_link);
					$out['body'] = '<object type="application/x-shockwave-flash" width="506" height="414" data="'.$enc_link.'">
	                                    <param name="quality" value="high" />
	                                    <param name="allowfullscreen" value="true" />
	                                    <param name="scale" value="showAll" />
	                                    <param name="movie" value="'.$enc_link.'" />
	                                </object>';
				}
			}
			elseif($type=="photo")
			{
				if(!defined("IMPATH")) define("IMPATH",$path_to_site.'/'.$img_dir.'/');
				$feedDescription = $feeditem->get_content();
				$image = returnImage($feedDescription);
				$image = urldecode(scrapeImage($image));
				//Check to see if it needs to be imported into TXP Image
				if($image)
				{			
					//get extension
					$ext = strrchr($image, '.');
					$check = safe_field('ID', 'txp_image', "NAME = '".$out['title']."' AND DATE = $when");		
					if($check)
					{
						$imageID = $check;
					}
					else
					{
						safe_insert('txp_image',
							"name = '".$out['title']."',
							ext = '$ext',
							date = $when"
						);
						$imageID = mysql_insert_id();
						// create a new curl resource
						$ch = curl_init();
						// set URL and other appropriate options
						curl_setopt($ch, CURLOPT_URL, "$image");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						// grab URL, and return output
						$output = curl_exec($ch);
						// close curl resource, and free up system resources
						curl_close($ch);
						//write to file
						$filename = IMPATH.basename($image);				
						$fh = fopen(IMPATH.$imageID.$ext, 'w');
						fwrite($fh, $output);
						fclose($fh);
						$shortpath = basename($image);
						$message .= "\tImported $filename\r\n";
					}
				}				
				$out['body'] = '<a href="'.$out['permalink'].'"><img src="'.hu.$img_dir."/".$imageID.$ext.'" /></a>';
			}
			else
			{
				if(!beginsWith($feeditem->get_description(), "<p"))
				{
					$out['body'] = dotag(addslashes($feeditem->get_description()), 'p');
				}
				else
				{
					$out['body'] = addslashes($feeditem->get_description());
				}
			}			

			//Check to see if the article has already been imported
			$exists = safe_count('textpattern', "Title = '".$out['title']."' AND Posted=$when");
			
			//If it hasn't then let's add it
			if($exists==0){
				//Check to see if category1 exists
				if($category1 && !fetch_category_title($category1))
				{
					//Create new category
					$name = sanitizeForUrl($category1);
					$exists = safe_field('name', 'txp_category', "name = '".doSlash($name)."' and type = 'article'");
					if (!$exists)
					{
						$q = safe_insert('txp_category', "name = '".doSlash($name)."', title = '".doSlash($category1)."', type = 'article', parent = 'root'");
						rebuild_tree('root', 1, 'article');
					}
				}
				//Check to see if category2 exists
				if($category2 && !fetch_category_title($category2))
				{
					//Create new category
					$name = sanitizeForUrl($category2);
					$exists = safe_field('name', 'txp_category', "name = '".doSlash($name)."' and type = 'article'");
					if (!$exists)
					{
						$q = safe_insert('txp_category', "name = '".doSlash($name)."', title = '".doSlash($category2)."', type = 'article', parent = 'root'");
						rebuild_tree('root', 1, 'article');
					}
				}
				
				//Get custom field
				$custom_row = safe_row("*", 'txp_prefs', "val='$linkfield'");
				if($custom_row)
				{
					$linkfield = 'custom_'.$custom_row['position'];
				}
				else
				{
					$linkfield = "custom_1";
				}
				$result = safe_insert("textpattern",
					"Title           = '".$out['title']."',
					Body            = '".$out['body']."',
					Body_html       = '".$out['body']."',
					Excerpt         = '',
					Excerpt_html    = '',
					Image           = '".$favicon."',
					Keywords        = '',
					Status          =  4,
					Posted          =  $when,
					LastMod         =  now(),
					AuthorID        = '',
					Section         = '$section',
					Category1       = '$category1',
					Category2       = '$category2',
					textile_body    =  0,
					textile_excerpt =  0,
					Annotate        =  1,
					override_form   = '$form',
					url_title       = '',
					$linkfield 		= '".$out['permalink']."',
					AnnotateInvite  = 'comments',
					uid             = '".md5(uniqid(rand(),true))."',
					feed_time       = $when"
				);
				
				if($result)				
				{
					//do_pings();
					update_lastmod();
					$message .= "\tAdded - ".$out['title']."\r\n";
				}
			}
			else
			{
				$message .= "\tExists - ".$out['title']."\r\n";
			}
		}
	}
	else {
		$message .= "\t".$thefeed->error;
	}
	return $message;
}

//helper functions
////////////////////////////////////////////////////////////////
//Get an image
function returnImage ($text) {
    $text = html_entity_decode($text);
    //echo $text;
    $pattern = "/<img[^>]+\>/i";
    preg_match($pattern, $text, $matches);
    $text = $matches[0];
    return $text;
}

////////////////////////////////////////////////////////////////
//Filter out image url only
function scrapeImage($text) {
    
    $pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/'; 
    
preg_match($pattern, $text, $link);

$link = $link[1];
$link = urlencode($link);
return $link;

}

function beginsWith($str, $sub) {
    return (strncmp($str, $sub, strlen($sub)) == 0);
	}
# --- END PLUGIN CODE ---

?>