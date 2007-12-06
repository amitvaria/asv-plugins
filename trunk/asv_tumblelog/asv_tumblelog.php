<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_tumblelog';

$plugin['version'] = '1.5';
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
h1. asv_tumblelog

h2. the beginning of a lifestream

h3. Summary

p. I used to be an avid user of services like "Tumblr":http://www.tumblr.com, but found myself wanting more freedom and control over my posts. Please follow the setup instructions below to ensure that everything gets setup. asv_tumblelog will import feeds for you as TXP articles.

h3. Setup/Installation

p. asv_tumblelog requires "SimplePie":http://www.simplepie.org, so please grab the latest version. After activating the plugin you will have a new tab under "Extensions" called "Tumblelog". In this tab you have four options - Settings, Feeds, Page Design, Form Design.

p. Once everything is setup you'll need to setup a cron job to get a specific url to update the feeds. The path would be: http://websiteurl/?asv_tumblelog_updatefeeds=1 (note: this is not the textpattern folder, but the url to your main site).

h3. Settings

p. Before using asv_tumblelog you'll need to setup a couple things. 
* *Source Link Field* - select the custom field you would like to use to store the original link to the imported post
* *Tumblelog Section* - the section to import the feed into
* *SimplePie Path* - /the/path/to/your/SimplePie/install

* *Post Form* - the default form that should be used for posts organized as posts
* *Quote Form* - the default form that should be used for posts organized as quotes
* *Link Form* - the default form that should be used for posts organized as links
* *Photo Form* - the default form that should be used for posts organized as photos
* *Video Form* - the default form that should be used for posts organized as videos

h3. Feeds

p. In the 'Feeds' section you add/edit/view all your feeds you are importing. When adding a feed, fill in the information and choose the form you would like to use to display them. 

p. Some feeds have special handlers:
* Twitter - the "username: " part of the feed will be removed
* Viddler/YouTube/Vimeo - Any feed that come from these three sites will be automagically embedded as the body of your post
* Images - Any feed where you want to just display the image (i.e. Flickr) can be set when adding the feed. You can choose between importing the image into TXP or just referencing the remote link

h3. Page Design

p. Here you can edit the page that is associated to your tumblelog section.

h3. Page Style

p. Here you can edit the style that is associated to your tumblelog section.

h3. Form Design

p. Here you can edit any forms used for your tumblelog.

h3. Bookmarklet

p. Add this to your bookmarks and you can create quick posts for sites you visit.

h3. Reccommendations

p. You'll learn that you have a lot of room for flexibility and customization with asv_tumblelog. I would recommend installing plugins like rss_auto_excerpt and tru_tags. With rss_auto_excerpt you can truncate posts and tru_tags will let you implement a tagging solution using the 'Keywords' field.
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

	$titles = array('Settings'=>'Settings', 'Feeds'=>'Feeds', 'page-design'=>'Page Design', 'page-style'=>'Page Style', 'Design'=>'Form Design', 'bookmarklet'=>'Bookmarklet');
	$newtitles = array();
	foreach($titles as $key=>$title)
	{
		if($title!=$active)
		{
			$title='<a href="index.php?event=asv_tumblelog&step='.$key.'">'.$title.'</a>';
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
		return selectInput($id, $rs, $Custom, false, '', $id);
	}
  
	return false;
}
//--------------------------------------------------------------
function asv_form_popup($Custom, $id, $name="")
{
	$rs = safe_column('name', 'txp_form', "type='article'");
  
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
		return treeSelectInput($id,$rs,$Custom, $id);
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
			'videoform' => '',
			'rssfeedpage' => '',
			'feed_id_field' => '',
			),$out);

	}
	return false;
}
// -------------------------------------------------------------
function asv_tumblelog_getCustomField($name)
{
	$name = doSlash($name);
	$custom_row = safe_row("*", 'txp_prefs', "val='$name'");
	if($custom_row)
	{
		return 'custom_'.$custom_row['position'];
	}
	else
	{
		return false;
	}
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
	
	return ($success)? array($thefeed->get_title(), $thefeed->get_favicon(), $thefeed->get_permalink()) :  false;

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
			`Favicon` varchar(255) NOT NULL default '',
			`Title` varchar(255) NOT NULL default '',
			`Feed` varchar(255) NOT NULL default '',
			`URL` varchar(255) NOT NULL default '',
			`Image` int(3) NOT NULL default '0',
			`Annotate` int(2) NOT NULL default '0',
			`Type` varchar(128) NOT NULL default '',
			`Category1` varchar(128) NOT NULL default '',
			`Category2` varchar(128) NOT NULL default '',
			`Keywords` varchar(255) NOT NULL default '',
			`LastUpdate` datetime,
			 PRIMARY KEY  (`ID`)
			 ) $tabletype PACK_KEYS=1 AUTO_INCREMENT=2 ");
			 
		if($rs = safe_show('COLUMNS', 'asv_tumblelog_feeds'))
		{
			if(count($rs)!=12)
			{
				$design_col = array('ID', 'Favicon', 'Title', 'Feed', 'URL', 'Image', 'Annotate', 'Type', 'Category1', 'Category2', 'Keywords', 'LastUpdate');
				$exist_col = array();
				foreach($rs as $col)
				{
					$exist_col[] = $col['Field'];
				}
				$diff = array_diff($design_col, $exist_col);
				foreach($diff as $col)
				{
					switch($col)
					{
						case 'ID':
							safe_alter('asv_tumblelog_feeds', 'ADD `ID` int(11) NOT NULL auto_increment');
							break;
						case 'Favicon':
							safe_alter('asv_tumblelog_feeds', "ADD `Favicon` varchar(255) NOT NULL default ''");
							break;
						case 'Title':
							safe_alter('asv_tumblelog_feeds', "ADD `Title` varchar(255) NOT NULL default ''");
							break;
						case 'Feed':
							safe_alter('asv_tumblelog_feeds', "ADD `Feed` varchar(255) NOT NULL default ''" );
							break;
						case 'URL':
							safe_alter('asv_tumblelog_feeds', "ADD `URL` varchar(255) NOT NULL default ''");
							break;
						case 'Image':
							safe_alter('asv_tumblelog_feeds', "ADD `Image` int(3) NOT NULL default '0'");
							break;
						case 'Annotate':
							safe_alter('asv_tumblelog_feeds', "ADD `Annotate` int(2) NOT NULL default '0'");
							break;
						case 'Type':
							safe_alter('asv_tumblelog_feeds', "ADD `Type` varchar(128) NOT NULL default ''");
							break;					
						case 'Category1':
							safe_alter('asv_tumblelog_feeds', "ADD `Category1` varchar(128) NOT NULL default ''");
							break;					
						case 'Category2':
							safe_alter('asv_tumblelog_feeds', "ADD `Category2` varchar(128) NOT NULL default ''");
							break;					
						case 'Keywords':
							safe_alter('asv_tumblelog_feeds', "ADD `Keywords` varchar(255) NOT NULL default ''");
							break;
						case 'LastUpdate':
							safe_alter('asv_tumblelog_feeds', "ADD `LastUpdate` datetime");
							break;
							
					}
				}
			}
		}
	}
}
//--------------------------------------------------------------
function asv_tumblelog_trimtwitter($input, $source, $title)
{
	if(strstr($source, "twitter.com"))
	{
		return preg_replace('/(\w+:) (\.*)/', '$2', $input);
	}
	elseif(strstr($source, "vimeo.com") && !$title)
	{
		$video_id = substr(strrchr($source, '/'), 1);
		
$video_embed = <<<EOD
<p><object type="application/x-shockwave-flash" width="400" height="327" data="http://www.vimeo.com/moogaloop.swf?clip_id=$video_id&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=01AAEA">	<param name="quality" value="best" />	<param name="allowfullscreen" value="true" />	<param name="scale" value="showAll" />	<param name="movie" value="http://www.vimeo.com/moogaloop.swf?clip_id=$video_id&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=01AAEA" /></object></p>
EOD;
		return $video_embed;
	}
	elseif(strstr($source, "viddler.com") && !$title)
	{		
		preg_match('/(.*?)[\\?&]token=(\w*)(.*)/', str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', $input), $matches);
		if($matches)
		{
			$video_id = $matches[2];
$video_embed = <<<EOD
<p><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="400" height="370" id="viddler"><param name="movie" value="http://www.viddler.com/player/$video_id/" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><embed src="http://www.viddler.com/player/$video_id/" width="400" height="370" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" name="viddler" ></embed></object></p>
EOD;
			return $video_embed;
		}
	}
	elseif(strstr($source, "youtube.com"))
	{
		$video_id = preg_replace('/(.*?)[\\?&]v=([^\&#]*).*/', '$2',$source);
$video_embed = <<<EOD
<p><object width="425" height="355"><param name="movie" value="http://www.youtube.com/v/$video_id&rel=1"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/$video_id&rel=1" type="application/x-shockwave-flash" wmode="transparent" width="425" height="355"></embed></object></p>
EOD;
		return $video_embed;
	}
	return $input;
}
// -------------------------------------------------------------
function asv_tumblelog_textile_main_fields($incoming, $use_textile)
{
	global $txpcfg;
	
	include_once txpath.'/lib/classTextile.php';
	$textile = new Textile();
	
	$incoming['title_plain'] = $incoming['title'];	
	$incoming['body_html'] = $textile->TextileThis(nl2br($incoming['body']));
	$incoming['title'] = $textile->TextileThis($incoming['title'],'',1);
	
	return $incoming;
}
//--------------------------------------------------------------
function asv_tumblelog_feeds_list($atts,$thing)
{
}

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
		case 'Design':
			asv_tumblelog_design($step);
			break;
		case 'page-design':
			asv_tumblelog_pagedesign($step);
			break;
		case 'page-style':
			asv_tumblelog_pagestyle($step);
			break;
		case 'bookmarklet':
			asv_tumblelog_bookmarklet($step);
			break;
		case 'mini':
			asv_tumblelog_mini($step);
			break;
		case 'update':
			asv_tumblelog_update($step);
			break;
	}
}
//--------------------------------------------------------------
function asv_tumblelog_mini($step)
{
	global $txp_user, $vars, $txpcfg, $prefs;
	
	$message = '';
	
	extract($prefs);
	extract(get_asv_tumblelog_prefs());

	$incoming = gpsa(array('method', 'formname', 'sourceurl', 'title', 'body', 'category1', 'category2', 'keywords', 'photourl'));
				
	$incoming = asv_tumblelog_textile_main_fields($incoming, $use_textile);
	extract(doSlash($incoming));

	
	if(gps('action')=='create')
	{
		
		$customField = asv_tumblelog_getCustomField($sourcelink);
			
		$result = safe_insert("textpattern",
					"Title           = '".$title."',
					Title_html	='".$title_plain."',
					Body            = '".$body."',
					Body_html       = '".$body_html."',
					Excerpt         = '',
					Excerpt_html    = '',
					Image           = '".(($method=='photo')? $photourl: "")."',
					Keywords        = '".$keywords."',
					Status          =  4,
					Posted          =  now(),
					LastMod         =  now(),
					AuthorID        = '$txp_user',
					Section         = '".$tumblelogsection."',
					Category1       = '".$category1."',
					Category2       = '".$category2."',
					textile_body    =  1,
					textile_excerpt =  1,
					Annotate        =  1,
					override_form   = '".$formname."',
					url_title       = '',
					$customField 		= '".(($method!='post')? $sourceurl: "")."',
					AnnotateInvite  = 'comments',
					uid             = '".md5(uniqid(rand(),true))."',
					feed_time       = now()"
				);
				
				if($result)				
				{
					//do_pings();
					update_lastmod();
					$message = "Added - ".$title;
					
					extract(lAtts(array(
						'method'	=> '',
						'formname' => '',
						'sourceurl' => '',
						'title'	=> '',
						'body'	=> '',
						'category1'	=> '',
						'category2' 	=> '',
						'keywords' => '',
						'photourl'	=> '',
						), array()));
				}
				else
				{
					$message = "Unable to save - ".$title;
				}
	}

	pagetop('Post to Tumblelog', $message);
	
	//Grab contents of url
	$output = '';
	if($sourceurl)
	{
		// create a new curl resource
		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $sourceurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// grab URL, and return output
		$output = curl_exec($ch);
		//get errors		
		$err     = curl_errno( $ch );
		$errmsg  = curl_error( $ch );
		// close curl resource, and free up system resources
		curl_close($ch);
	}
	
	$suggestion = 'link';
	$video_body = '';
	if($body) 
		$suggestion = 'quote';
		
	if($output)
	{
		if(strstr($sourceurl, "flickr.com"))
		{
			$pic_id = preg_replace('/(.*)\/photos\/(.*)\/(\w+)\//', '$3', $sourceurl);
			$flat_output = ereg_replace("[\n\r\t]", " ", $output);
		    preg_match_all("/<img[^>]+\>/", $flat_output , $photo_matches);
		    foreach($photo_matches[0] as $match)
		    {
		    	if(strstr($match, $pic_id))
		    	{
		    		$photourl = str_replace('.jpg', '_m.jpg', urldecode(scrapeImage($match)));
		    		$suggestion = 'photo';
		    	}
		    }
		}
		elseif(strstr($sourceurl, "vimeo.com"))
		{
			$video_id = substr(strrchr($sourceurl, '/'), 1);		
$video_embed = <<<EOD
<p><object type="application/x-shockwave-flash" width="400" height="327" data="http://www.vimeo.com/moogaloop.swf?clip_id=$video_id&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=01AAEA">	<param name="quality" value="best" />	<param name="allowfullscreen" value="true" />	<param name="scale" value="showAll" />	<param name="movie" value="http://www.vimeo.com/moogaloop.swf?clip_id=$video_id&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=01AAEA" /></object></p>
EOD;
			$video_body = ($video_id)?$video_embed:$body;
			$suggestion = 'video';
		}
		elseif(strstr($sourceurl, "viddler.com"))
		{
			preg_match('/(.*?)[\\?&]token=(\w*)(.*)/', str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', nl2br($output)), $matches);
			if($matches)
			{
				$video_id = $matches[2];
$video_embed = <<<EOD
<p><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="400" height="370" id="viddler"><param name="movie" value="http://www.viddler.com/player/$video_id/" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><embed src="http://www.viddler.com/player/$video_id/" width="400" height="370" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" name="viddler" ></embed></object></p>
EOD;
				$video_body = $video_embed;
				$suggestion = 'video';
			}
		}
		elseif(strstr($sourceurl, "youtube.com"))
		{
			$video_id = preg_replace('/(.*?)[\\?&]v=([^\&#]*).*/', '$2',$sourceurl);
$video_embed = <<<EOD
<p><object width="425" height="355"><param name="movie" value="http://www.youtube.com/v/$video_id&rel=1"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/$video_id&rel=1" type="application/x-shockwave-flash" wmode="transparent" width="425" height="355"></embed></object></p>
EOD;
			$video_body = ($video_id)?$video_embed:$body;
			$suggestion = 'video';
		}
	}
	
	
echo <<<EOD
<script type="text/javascript">
	function asv_tumblelog_mini_choose(id){
		$('#asv_tumblelog_link').hide();
		$('#asv_tumblelog_post').hide();
		$('#asv_tumblelog_photo').hide();
		$('#asv_tumblelog_quote').hide();
		$('#asv_tumblelog_video').hide();

		$(id).show();
	}
	$(document).ready(function() {
		asv_tumblelog_mini_choose('#asv_tumblelog_$suggestion');
	});
</script>	

EOD;
	
	$title = stripslashes($title);
	$body = str_replace("\\n", "\n", $body);
	
	echo tag('<a href="#" onclick="asv_tumblelog_mini_choose(\'#asv_tumblelog_post\');">post</a> | '.
		 '<a href="#" onclick="asv_tumblelog_mini_choose(\'#asv_tumblelog_quote\');">quote</a> | '.
		 '<a href="#" onclick="asv_tumblelog_mini_choose(\'#asv_tumblelog_link\');">link</a> | '.
		 '<a href="#" onclick="asv_tumblelog_mini_choose(\'#asv_tumblelog_photo\');">photo</a> | '.
		 '<a href="#" onclick="asv_tumblelog_mini_choose(\'#asv_tumblelog_video\');">video</a>',
		 	'h1', ' style="text-align:center"');
	
	//post
	echo '<form name="post" method="post" action="index.php">'.
		n.hInput('event', 'asv_tumblelog').
		n.hInput('step', 'mini').
		n.hInput('action', 'create').
		n.hInput('method', 'post').
		n.hInput('bm', '1');
				
		echo startTable('asv_tumblelog_post').
			tr(
				td(			
					startTable('list').
					tr(tda('Add a new post', ' colspan=2')).
					tr(td('Form').td(asv_form_popup($postform, 'formname'))).
					tr(td('Title').td(fInput('text', 'title', '', '', '', '', '50'))).
					tr(td('Post').td(text_area('body', '100', '250', $body))).
					tr(tda(fInput('submit', 'save', 'post', 'publish'), ' colspan=2 style="text-align: right"')).
					endTable()
				).
				td(
					startTable('list').
						tr(td()).
						tr(td('Category 1<br />'.asv_cat_popup($category1,'category1'))).
						tr(td('Category 2<br />'.asv_cat_popup($category2,'category2'))).
						tr(td('Keywords<br />'.fInput('text', 'keywords', ''))).
					endTable()
				));	
	echo '</form></body></html>';
	
	//quote
	echo '<form name="quote" method="post" action="index.php">'.
		n.hInput('event', 'asv_tumblelog').
		n.hInput('step', 'mini').
		n.hInput('action', 'create').
		n.hInput('method', 'quote').
		n.hInput('bm', '1');
				
		echo startTable('asv_tumblelog_quote').
			tr(
				td(			
					startTable('list').
					tr(tda('Add a new quote', ' colspan=2')).
					tr(td('Form').td(asv_form_popup($quoteform, 'formname'))).
					tr(td('Source URL').td(fInput('text', 'sourceurl', $sourceurl, '', '', '', '50'))).
					tr(td('Title').td(fInput('text', 'title', $title, '', '', '', '50'))).
					tr(td('Post').td(text_area('body', '100', '250', $body))).
					tr(tda(fInput('submit', 'save', 'post', 'publish'), ' colspan=2 style="text-align: right"')).
					endTable()
				).
				td(
					startTable('list').
						tr(td()).
						tr(td('Category 1<br />'.asv_cat_popup($category1,'category1'))).
						tr(td('Category 2<br />'.asv_cat_popup($category2,'category2'))).
						tr(td('Keywords<br />'.fInput('text', 'keywords', ''))).
					endTable()
				));	
	echo '</form></body></html>';
	
	//link
	echo '<form id="" name="link" method="post" action="index.php">'.
		n.hInput('event', 'asv_tumblelog').
		n.hInput('step', 'mini').
		n.hInput('action', 'create').
		n.hInput('method', 'link').
		n.hInput('bm', '1');
				
		echo startTable('asv_tumblelog_link', 'center').
			tr(
				td(			
					startTable('list').
					tr(tda('Add a new link', ' colspan=2')).
					tr(td('Form').td(asv_form_popup($linkform, 'formname'))).
					tr(td('Source URL').td(fInput('text', 'sourceurl', $sourceurl, '', '', '', '50'))).
					tr(td('Title').td(fInput('text', 'title', $title, '', '', '', '50'))).
					tr(td('Post').td(text_area('body', '100', '250', $body))).
					tr(tda(fInput('submit', 'save', 'post', 'publish'), ' colspan=2 style="text-align: right"')).
					endTable()
				).
				td(
					startTable('list').
						tr(td()).
						tr(td('Category 1<br />'.asv_cat_popup($category1,'category1'))).
						tr(td('Category 2<br />'.asv_cat_popup($category2,'category2'))).
						tr(td('Keywords<br />'.fInput('text', 'keywords', ''))).
					endTable()
				));	
	echo '</form></body></html>';
	
	//photo
	echo '<form name="photo" method="post" action="index.php">'.
		n.hInput('event', 'asv_tumblelog').
		n.hInput('step', 'mini').
		n.hInput('action', 'create').
		n.hInput('method', 'photo').
		n.hInput('bm', '1');
				
		echo startTable('asv_tumblelog_photo').
			tr(
				td(			
					startTable('list').
					tr(tda('Add a new photo', ' colspan=2')).
					tr(td('Form').td(asv_form_popup($photoform, 'formname'))).
					tr(td('Photo URL').td(fInput('text', 'photourl', $photourl, '', '', '', '50'))).
					tr(td('Source URL').td(fInput('text', 'sourceurl', $sourceurl, '', '', '', '50'))).
					tr(td('Title').td(fInput('text', 'title', $title, '', '', '', '50'))).
					tr(td('Post').td(text_area('body', '100', '250', $body))).
					tr(tda(fInput('submit', 'save', 'post', 'publish'), ' colspan=2 style="text-align: right"')).
					endTable()
				).
				td(
					startTable('list').
						tr(td()).
						tr(td('Category 1<br />'.asv_cat_popup($category1,'category1'))).
						tr(td('Category 2<br />'.asv_cat_popup($category2,'category2'))).
						tr(td('Keywords<br />'.fInput('text', 'keywords', ''))).
					endTable()
				));	
	echo '</form></body></html>';
	
	//video
	echo '<form id="" name="video" method="post" action="index.php">'.
		n.hInput('event', 'asv_tumblelog').
		n.hInput('step', 'mini').
		n.hInput('action', 'create').
		n.hInput('method', 'video').
		n.hInput('bm', '1');
				
		echo startTable('asv_tumblelog_video', 'center').
			tr(
				td(			
					startTable('list').
					tr(tda('Add a new video', ' colspan=2')).
					tr(td('Form').td(asv_form_popup($videoform, 'formname'))).
					tr(td('Source URL').td(fInput('text', 'sourceurl', $sourceurl, '', '', '', '50'))).
					tr(td('Title').td(fInput('text', 'title', $title, '', '', '', '50'))).
					tr(td('Post').td(text_area('body', '100', '250', $video_body))).
					tr(tda(fInput('submit', 'save', 'post', 'publish'), ' colspan=2 style="text-align: right"')).
					endTable()
				).
				td(
					startTable('list').
						tr(td()).
						tr(td('Category 1<br />'.asv_cat_popup($category1,'category1'))).
						tr(td('Category 2<br />'.asv_cat_popup($category2,'category2'))).
						tr(td('Keywords<br />'.fInput('text', 'keywords', ''))).
					endTable()
				));	
	echo '</form></body></html>';
	
	exit();
}
//--------------------------------------------------------------
function asv_tumblelog_bookmarklet($step)
{
	global $prefs;
	
	pagetop('Tumblelog > Bookmarklet', '');
	
	extract(get_asv_tumblelog_prefs());
	
	echo asv_tumblelog_title($step);
	
	//Get custom field
	$linkfield_set = '';
	$custom_row = safe_row("*", 'txp_prefs', "val='$sourcelink'");
	if($custom_row)
	{
		$linkfield_set = 'custom_'.$custom_row['position'];
	}
	else
	{
		$linkfield_set = "custom_1";
	}
	
	
	$bookmarklet = "javascript:var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='http://".$prefs['siteurl']."/textpattern/index.php?',l=d.location,e=encodeURIComponent,p='event=asv_tumblelog&step=mini&bm=1&formname=".$linkform."&sourceurl='+e(l.href)+'&title='+e(d.title)+'&body='+e(s),u=f+p;a=function(){if(!w.open(u,'t','toolbar=0,resizable=0,status=1,width=500,height=400'))l.href=u;};if(/Firefox/.test(navigator.userAgent))setTimeout(a,0);else a();void(0)";
	
	
	
	echo startTable('list').
		tr(tda('Add the following to your bookmarks to make a quick post', ' style="text-align:center"')).
		tr(tda("<a href=\"$bookmarklet\" title='Drag this link to your Bookmarks Bar. Click to learn more.'>Share on TXP</a>", ' style="text-align:center"')).
		endTable();
}
//--------------------------------------------------------------
function asv_tumblelog_pagestyle($step)
{

	extract(doSlash(get_asv_tumblelog_prefs()));
	
	$message = '';
	if(gps('action')=='save' && gps('style-name'))
	{	
		$form = doSlash(base64_encode(gps('form')));
		$rs = safe_update("txp_css", "css='".$form ."'", "name = '".doSlash(gps('style-name'))."'");
		$message = 'Style saved';
	}
	
	pagetop('Tumblelog', $message);
	
	echo asv_tumblelog_title($step);
	
	$rs = safe_row("css", 'txp_section', "name = '$tumblelogsection'");
	if($rs)
	{
		$page_rs = safe_row('*', 'txp_css', "name='".$rs['css']."'");
		if($page_rs)
		{
			extract($page_rs);
			$thecss = base64_decode($css);
		}
	}
	
	
	echo n.startTable('list', '', '', '', '').		
		n.'<form name="post-form" method="post" action="index.php">'.
		n.hInput('event', 'asv_tumblelog').
		n.hInput('step', 'page-style').
		n.hInput('action', 'save').
		n.hInput('style-name', $name).
		tr(tda("Edit the style that handles the tumblelog section <b>".$name."</b>.", ' style="text-align:center"')).
		tr(td(text_area('form', '800', '600', ($thecss)?$thecss:''))).
		tr(tda(fInput('submit','save_settings','save',"publish", '', '', '', 4), ' style="text-align:right"')).'</form>'.
	endTable();
}
//--------------------------------------------------------------
function asv_tumblelog_pagedesign($step)
{

	extract(doSlash(get_asv_tumblelog_prefs()));
	
	$message = '';
	if(gps('action')=='save' && gps('page-name'))
	{		
		$rs = safe_update("txp_page", "user_html='".doSlash(gps('form'))."'", "name = '".doSlash(gps('page-name'))."'");
		$message = 'Page saved';
	}
	
	pagetop('Tumblelog', $message);
	
	echo asv_tumblelog_title($step);
	
	$rs = safe_row("page", 'txp_section', "name = '$tumblelogsection'");
	if($rs)
	{
		$page_rs = safe_row('*', 'txp_page', "name='".$rs['page']."'");
		if($page_rs)
		{
			extract($page_rs);
		}
	}
	
	
	echo n.startTable('list', '', '', '', '').		
		n.'<form name="post-form" method="post" action="index.php">'.
		n.hInput('event', 'asv_tumblelog').
		n.hInput('step', 'page-design').
		n.hInput('action', 'save').
		n.hInput('page-name', $name).
		tr(tda("Edit the page that handles the tumblelog section <b>".$name."</b>.", ' style="text-align:center"')).
		tr(td(text_area('form', '800', '600', ($user_html)?$user_html:''))).
		tr(tda(fInput('submit','save_settings','save',"publish", '', '', '', 4), ' style="text-align:right"')).'</form>'.
	endTable();
}
//--------------------------------------------------------------
function asv_tumblelog_design($step)
{

	extract(doSlash(get_asv_tumblelog_prefs()));
	
	$message = '';

	if(gps('action')=='save')
	{		
		$rs = safe_update("txp_form", "Form='".doSlash(gps('form'))."'", "name = '".doSlash(gps('form-name'))."' AND type='article'");
		if($rs)
		{
			$message = doSlash(gps('form-name'))." saved.";
		}
		else
		{
			$message = "Error saving ".doSlash(gps('form-name'));
		}		
	}
	
	pagetop('Tumblelog', $message);
	
	echo asv_tumblelog_title($step);


	echo n.startTable('list', '', '', '', '').
		tr(tda("Below is a list of all the forms you're using for your feeds and the forms <br />set as defaults for your tumbles.", ' style="text-align:center"'));
	
	$more_forms = array($postform, $photoform, $quoteform, $linkform);
	
	$rs = safe_rows_start('DISTINCT Type', 'asv_tumblelog_feeds', '1=1 ORDER BY Type ASC');	
	if ($rs) {
		while ($a = nextRow($rs)) {
			$formdata = safe_row("*", 'txp_form', "name = '".$a['Type']."' AND type='article'");
			if($formdata)
			{
				if(in_array($a['Type'], $more_forms))
				{
					unset($more_forms[array_search($a['Type'], $more_forms)]);
				}
				echo n.'<form name="post-form" method="post" action="index.php">'.
					n.hInput('event', 'asv_tumblelog').
					n.hInput('step', 'Design').
					n.hInput('action', 'save').
					n.hInput('form-name', $formdata['name']).
					tr(tda("<b>".$formdata['name']."</b>", ' style="text-align:center"')).
					tr(td(text_area('form', '150', '500', $formdata['Form']))).
					tr(tda(fInput('submit','save_settings','save',"publish", '', '', '', 4), ' style="text-align:right"')).'</form>';
			}
		}
	}
	
	foreach($more_forms as $single)
	{
		$formdata = safe_row("*", 'txp_form', "name = '".$single."' AND type='article'");
			if($formdata)
			{
				echo n.'<form name="post-form" method="post" action="index.php">'.
					n.hInput('event', 'asv_tumblelog').
					n.hInput('step', 'Design').
					n.hInput('action', 'save').
					n.hInput('form-name', $formdata['name']).
					tr(tda("<b>".$formdata['name']."</b>", ' style="text-align:center"')).
					tr(td(text_area('form', '150', '500', $formdata['Form']))).
					tr(tda(fInput('submit','save_settings','save',"publish", '', '', '', 4), ' style="text-align:right"')).'</form>';
			}
	}
	
	echo endTable();
}
//--------------------------------------------------------------
function asv_tumblelog_feeds($step)
{
	
	asv_tumblelog_verifyTable();
	
	$types = array('post'=>'post', 'quote'=>'quote', 'photo'=>'photo', 'link'=>'link', 'video'=>'video');
	
	$message = '';
	
	extract(doSlash(gpsa(array('rsspath', 'type', 'comments', 'category1', 'category2', 'image', 'keywords', 'ID', 'favicon',))));
				
	extract(get_asv_tumblelog_prefs());
	
	if(gps('save_feeds')){

		if($type){

			if($rsspath){
			
				list($title, $favicon, $url) = asv_tumblelog_verifyFeed($rsspath, $simplepie);
	
				if($title && $rsspath){
				
					$title = doSlash($title);
					
					if(assert_int($ID)){
						safe_update('asv_tumblelog_feeds', 
							"Feed = '$rsspath',
							Title = '$title',
							Type = '$type',
							URL = '$url',
							Image = '$image',
							Favicon = '$favicon',
							Category1 = '$category1',
							Category2 = '$category2',
							Keywords = '$keywords',
							Annotate = '$comments'",
							"ID = $ID"
							);		
						$message = "Updated $title.";
					} else{
						safe_insert('asv_tumblelog_feeds', 
							"Feed = '$rsspath',
							Title = '$title',
							Type = '$type',
							Image = '$image',
							URL = '$url',
							Favicon = '$favicon',
							Category1 = '$category1',
							Category2 = '$category2',
							Keywords = '$keywords',
							Annotate = '$comments'"
							);			
						$message = "Added $title.";		
					}
				} else{
					$message = "Not a valid feed.";
				}
			} else{
				$message = "A feed is required.";
			}
		} else{
			$message = "Type is required.";
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
			
			tr(td('Import Images').td(radio('image', '0').'no '.radio('image', '1', false).'url '.radio('image', '2', false).'image')).
			
			tr(td('Form').td(asv_form_popup($type, 'type'))).
			
			tr(td('Comments').td(onoffRadio('comments', ($comments == '0')? $comments : "1"))).
			
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
			n.column_head('ID', 'id', 'asv_tumblelog', false, '', '', '').	
			n.column_head('Favicon', 'favicon', 'asv_tumblelog', false, '', '', '').
			n.column_head('Title', 'title', 'asv_tumblelog', false, '', '', '').			
			n.column_head('URL', 'url', 'asv_tumblelog', false, '', '', '').			
			n.column_head('Feed', 'feed', 'asv_tumblelog', false, '', '', '').			
			n.column_head('Image', 'image', 'asv_tumblelog', false, '', '', '').
			n.column_head('Form', 'type', 'asv_tumblelog', false, '', '', '').
			n.column_head('Comments', 'comments', 'asv_tumblelog', false, '', '', '').
			n.column_head('Category1', 'category1', 'asv_tumblelog', false, '', '', '').
			n.column_head('Category2', 'category1', 'asv_tumblelog', false, '', '', '').
			n.column_head('Keywords', 'Keywords', 'asv_tumblelog', false, '', '', '').
			n.column_head('Last Update', 'Last Update', 'asv_tumblelog', false, '', '', '').
			hCell()
		);
	$rs = safe_rows_start("*", "asv_tumblelog_feeds", "1=1");
	if($rs)
	{
		while($a = nextRow($rs))
		{
			extract($a);
			$link = "<a href=\"index.php?event=asv_tumblelog&step=Feeds&rsspath=".urlencode($Feed)."&type=".$Type."&comments=".$a['Annotate']."&category1=$Category1&category2=$Category2&keywords=$Keywords&ID=$ID\">$Title</a>";
			
			switch($Image)
			{
				case '0': 
					$translateImage = "no";
					break;
				case '1':
					$translateImage = "url";
					break;
				case '2':
					$translateImage = "image";
					break;
			}
			
			echo n.tr(			
				td($ID).
				td(($Favicon)? "<img src='$Favicon' />":'').
				td($link).
				td($URL).	
				td(substr($Feed,0,25).'...').				
				td($translateImage).
				td($Type).
				td(($Annotate)?"on":"off").
				td($Category1).
				td($Category2).
				td($Keywords).
				td($LastUpdate).
				td(fInput('checkbox', 'selected[]', $ID)));
				
		}
		echo n.tr(
				tda(select_buttons().asv_tumblelog_list_multiedit_form(),' colspan="9" style="text-align: right; border: none;"'));
	}
		echo n.endTable().'</form>';
}
//--------------------------------------------------------------
function asv_tumblelog_settings($step)
{
	global $prefs;
	
	if(gps('save_settings'))
	{
		extract(gpsa(array('feed_id_field', 'sourcelink', 'tumblelogsection', 'simplepie', 'linkform', 'postform', 'quoteform', 'photoform', 'videoform')));
		
		($sourcelink)? set_pref('sourcelink', $sourcelink, 'asv_tumblelog', ''):''; 
		
		($tumblelogsection)? set_pref('tumblelogsection', $tumblelogsection, 'asv_tumblelog', ''):'';

		($feed_id_field)? set_pref('feed_id_field', $feed_id_field, 'asv_tumblelog', ''):''; 
		
		($simplepie)? set_pref('simplepie', $simplepie, 'asv_tumblelog', ''):'';		
		
		($postform)? set_pref('postform', $postform, 'asv_tumblelog', ''):'';	
		
		($quoteform)? set_pref('quoteform', $quoteform, 'asv_tumblelog', ''):'';	
		
		($photoform)? set_pref('photoform', $photoform, 'asv_tumblelog', ''):'';	
		
		($linkform)? set_pref('linkform', $linkform, 'asv_tumblelog', ''):'';		
		
		($videoform)? set_pref('videoform', $videoform, 'asv_tumblelog', ''):'';		
		
	}
	
	pagetop('Tumblelog', (gps('save'))? 'Settings saved':'');
	
	extract(get_asv_tumblelog_prefs());
	
	echo n.n.'<form name="tumblelog-admin" method="post" action="index.php?event=asv_tumblelog">';
	
	echo asv_tumblelog_title($step).
	
		tag('General Settings' ,'h3', ' style="text-align: center;"').
		
		startTable('list').
					
			tr(td('Tumblelog Section').td(asv_section_popup($tumblelogsection,
			'tumblelogsection'))).
			
			tr(td('Source Link Field').td(asv_custom_popup($sourcelink, 'sourcelink'))).
						
			tr(td('Feed ID Field').td(asv_custom_popup($feed_id_field, 'feed_id_field'))).
			
			tr(td('SimplePie Path').td(fInput('text', 'simplepie', $simplepie))).
			
		endTable().
		
		tag(' ', 'p'). 
		
		tag('Forms' ,'h3', ' style="text-align: center;"').
		
		startTable('list').
		
			tr(td('Post').td(asv_form_popup($postform, 'postform'))).
			
			tr(td('Quote').td(asv_form_popup($quoteform, 'quoteform'))).
			
			tr(td('Photo').td(asv_form_popup($photoform, 'photoform'))).
			
			tr(td('Link').td(asv_form_popup($linkform, 'linkform'))).
			
			tr(td('Video ').td(asv_form_popup($videoform, 'videoform'))).
			
			tr(tda(fInput('submit','save_settings','save',"publish", '', '', '', 4), ' colspan=2')).
			
		endTable();
		
	echo n.n.'</form>'.
	'<h3 style="text-align:center"><a href="http://'.$prefs['siteurl'].'/?asv_tumblelog_updatefeeds=1">manually update feeds</a></h3>';
}
//--------------------------------------------------------------
function asv_tumblelog_update($step)
{
	global $txp_user;
	extract(get_asv_tumblelog_prefs());
	if(gps('asv_tumblelog_updatefeed_id') && assert_int(gps('asv_tumblelog_updatefeed_id'))){
		$rID = gps('asv_tumblelog_updatefeed_id');	
		$where = "ID='".doSlash($rID)."'";
	} else{
		$where = "1=1";
	}
	
	$rs = safe_rows_start("*", "asv_tumblelog_feeds", $where);
	echo '<pre>';
	if($rs)
	{
		while($a = nextRow($rs))
		{
			extract($a);

			safe_update('asv_tumblelog_feeds', 'LastUpdate = now()', "ID = '$ID'");

			if(!$LastUpdate)
				$LastUpdate='January 1, 1970';
						
			if(gps('asv_tumblelog_overridetime')==1){			
			
				if($txp_user){
				
					$LastUpdate = 'January 1, 1970';
					
					} else{
					
					echo "You must be logged in to run this command.";
					die();
				}
			}
				
			echo asv_rssgrab(array(
				'feed'	=> $Feed,
				'simplepie' => $simplepie,
				'type'	=> $Type,
				'category1'	=> $Category1,
				'category2' => $Category2,
				'section'	=> $tumblelogsection,
				'form'		=> $Type,
				'linkfield'	=> $sourcelink,
				'pubdate' => '',
				'comments'	=> $Annotate,
				'keywords'	=> $Keywords,
				'feed_id_field'	=> $feed_id_field,
				'feed_id' => $ID,
				'lastupdate' => $LastUpdate,
				));
		}
	}
	echo '</pre>';
	exit();
}
//--------------------------------------------------------------
if(gps('asv_tumblelog_updatefeeds')==1)
{
	global $txp_user;
	extract(get_asv_tumblelog_prefs());
	if(gps('asv_tumblelog_updatefeed_id') && assert_int(gps('asv_tumblelog_updatefeed_id'))){
		$rID = gps('asv_tumblelog_updatefeed_id');	
		$where = "ID='".doSlash($rID)."'";
	} else{
		$where = "1=1";
	}
	
	$rs = safe_rows_start("*", "asv_tumblelog_feeds", $where);
	echo '<pre>';
	if($rs)
	{
		while($a = nextRow($rs))
		{
			extract($a);

			safe_update('asv_tumblelog_feeds', 'LastUpdate = now()', "ID = '$ID'");

			if(!$LastUpdate)
				$LastUpdate='January 1, 1970';
				
				
			echo asv_rssgrab(array(
				'feed'	=> $Feed,
				'simplepie' => $simplepie,
				'type'	=> $Type,
				'category1'	=> $Category1,
				'category2' => $Category2,
				'section'	=> $tumblelogsection,
				'form'		=> $Type,
				'linkfield'	=> $sourcelink,
				'pubdate' => '',
				'comments'	=> $Annotate,
				'keywords'	=> $Keywords,
				'feed_id_field'	=> $feed_id_field,
				'feed_id' => $ID,
				'lastupdate' => $LastUpdate,
				));
		}
	}
	echo '</pre>';
	exit();

}
//--------------------------------------------------------------
function asv_tumblelog_favicon($atts, $thing)
{
	global $thisarticle;
	
	extract(get_asv_tumblelog_prefs());
			
	$url = fetch('Favicon', 'asv_tumblelog_feeds', 'ID', $thisarticle[$feed_id_field]);
	
	if($url)
		return $url;

	return '';	
}
//--------------------------------------------------------------
function asv_tumblelog_feed($atts, $thing)
{
	global $thisarticle;
	
	extract(get_asv_tumblelog_prefs());
			
	$url = fetch('Title', 'asv_tumblelog_feeds', 'ID', $thisarticle[$feed_id_field]);
	
	if($url)
		return $url;

	return '';	
}
//--------------------------------------------------------------
function asv_tumblelog_feedurl($atts, $thing)
{
	global $thisarticle;
	
	extract(get_asv_tumblelog_prefs());
			
	$url = fetch('Feed', 'asv_tumblelog_feeds', 'ID', $thisarticle[$feed_id_field]);
	
	if($url)
		return $url;

	return '';
}
//--------------------------------------------------------------
function asv_tumblelog_permalinkl($atts, $thing)
{
	global $thisarticle;
	
	extract(get_asv_tumblelog_prefs());
			
	$url = fetch('URL', 'asv_tumblelog_feeds', 'ID', $thisarticle[$feed_id_field]);
	
	if($url)
		return $url;

	return '';
}
//--------------------------------------------------------------
function asv_rssgrab($atts)
{
	global $prefs, $txpcfg, $txp_user;
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
			'keywords'	=> '',
			'feed_id_field' => '',
			'feed_id' =>'',
			'lastupdate' => 'January 1, 1970'
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
			if($feeditem->get_date('U') > strtotime($lastupdate))
			{
				//Get the permalink
				$out['permalink'] = $feeditem->get_link();		
				
				// Get item title
				$out['title'] = addslashes(asv_tumblelog_trimtwitter($feeditem->get_title(), $out['permalink'], true));
				
				
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
				/*if($type=="media")
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
						$out['body'] = '<p><object type="application/x-shockwave-flash" width="506" height="414" data="'.$enc_link.'">
											<param name="quality" value="high" />
											<param name="allowfullscreen" value="true" />
											<param name="scale" value="showAll" />
											<param name="movie" value="'.$enc_link.'" />
										</object><p>';
					}
				}*/
				$image = '';
				if($out['image'])
				{
					if(!defined("IMPATH")) define("IMPATH",$path_to_site.'/'.$img_dir.'/');
					$feedDescription = $feeditem->get_content();
					$image = returnImage($feedDescription);
					$image = urldecode(scrapeImage($image));
					if(strstr($image, "flickr.com"))
						$image = str_replace("_m.jpg", ".jpg", $image);
					//Check to see if it needs to be imported into TXP Image
					if($out['image'] == '2')
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
						$image = "http://".$prefs['siteurl']."/".$img_dir.'/'.$imageID.$ext;
					}
				}
				
				if(!beginsWith($feeditem->get_description(), "<p"))
				{
					$out['body'] = tag(doSlash(asv_tumblelog_trimtwitter($feeditem->get_description(), $out['permalink'], false)), 'p');
				}
				else
				{
					$out['body'] = doSlash(asv_tumblelog_trimtwitter($feeditem->get_description(), $out['permalink'], false));
				}		
	
				//Check to see if the article has already been imported
				$exists = safe_count('textpattern', "Title = '".$out['title']."' AND Posted=$when AND Section='$section'");
				
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
					$message .= "\t\t$linkfield - custom\r\n";
					$custom_row = safe_row("*", 'txp_prefs', "val='$linkfield'");
					if($custom_row)
					{
						$linkfield_set = 'custom_'.$custom_row['position'];
					}
					else
					{
						$linkfield_set = "custom_1";
					}
					$result = safe_insert("textpattern",
						"Title           = '".$out['title']."',
						Body            = '".$out['body']."',
						Body_html       = '".$out['body']."',
						Excerpt         = '',
						Excerpt_html    = '',
						Image           = '".$image."',
						Keywords        = '$keywords',
						Status          =  4,
						Posted          =  $when,
						LastMod         =  now(),
						AuthorID        = '$txp_user',
						Section         = '$section',
						Category1       = '$category1',
						Category2       = '$category2',
						textile_body    =  0,
						textile_excerpt =  0,
						Annotate        =  1,
						override_form   = '$form',
						url_title       = '',
						$linkfield_set 		= '".$out['permalink']."',
						AnnotateInvite  = 'comments',
						uid             = '".md5(uniqid(rand(),true))."',
						feed_time       = $when, ".
						(($tempField = asv_tumblelog_getCustomField($feed_id_field))? "$tempField = '$feed_id'" : "''")
						
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
			else
			{
				$message.= "\tNot importing ".$feeditem->get_title()."\r\n";
				$message.= "\t".$feeditem->get_date('U')."\t".strtotime($lastupdate)."\r\n";
			}
		}
	}
	else {
		$message .= "\t".$thefeed->error;
	}
	return $message;
}
//--------------------------------------------------------------
//helper functions
//--------------------------------------------------------------
//Get an image
function returnImage ($text) 
{
    $text = html_entity_decode($text);
    $pattern = "/<img[^>]+\>/i";
    preg_match($pattern, $text, $matches);
    if($matches)	return $matches[0];
	return '';
}
//--------------------------------------------------------------
function scrapeImage($text) 
{
    
    $pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/'; 
    
	preg_match($pattern, $text, $link);
	if($link)	return urlencode($link[1]);
	return '';

}
//--------------------------------------------------------------
function beginsWith($str, $sub) 
{
    return (strncmp($str, $sub, strlen($sub)) == 0);
}
//--------------------------------------------------------------
# --- END PLUGIN CODE ---

?>