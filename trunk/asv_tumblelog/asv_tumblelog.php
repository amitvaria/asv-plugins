<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_tumblelog';

$plugin['version'] = '1.6.2';
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
h1. asv_tumblelog (v1.6.3)

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
if(gps('asv_tumblelog_updatefeeds')==1)
{
	global $txp_user;
	
	set_time_limit(120);
	
	extract(asv_tumblelog_get_prefs());
	
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
			ob_flush();
			flush();
			extract($a);

			if(!$LastUpdate)
				$LastUpdate='January 1, 1970';
				
				
			echo asv_tumblelog_importfeed(array(
				'feed'	=> $Feed,
				'simplepie' => $asv_tumblelog_simplepie,
				'type'	=> $Type,
				'category1'	=> $Category1,
				'category2' => $Category2,
				'section'	=> $asv_tumblelog_section,
				'form'		=> $Type,
				'linkfield'	=> $asv_tumblelog_sourcelink,
				'pubdate' => '',
				'comments'	=> $Annotate,
				'keywords'	=> $Keywords,
				'feed_id_field'	=> $asv_tumblelog_feed_id_field,
				'feed_id' => $ID,
				'lastupdate' => $LastUpdate,
				'importimage' => $Image,
				'convertvideo' => $Video,
				'AuthorID'	=> $AuthorID,
				));
		}
	}
	echo '</pre>';
	exit();

}
//--------------------------------------------------------------
function asv_tumblelog($event, $step)
{
	$step=($step=='')?'settings':$step;
	
	switch($step)
	{
		case 'settings': 
			asv_tumblelog_settings($step);
			break;
		case 'feeds':
			asv_tumblelog_feeds($step);
			break;
		case 'design':
			asv_tumblelog_formdesign($step);
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
function asv_tumblelog_settings($step)
{
	global $prefs;
	
	$message = '';
	
	if(gps('save_settings'))
	{
		extract(doSlash(gpsa(array('asv_tumblelog_feed_id_field', 'asv_tumblelog_sourcelink', 'asv_tumblelog_section', 'asv_tumblelog_simplepie', 'asv_tumblelog_linkform', 'asv_tumblelog_postform', 'asv_tumblelog_quoteform', 'asv_tumblelog_photoform', 'asv_tumblelog_videoform', 'asv_tumblelog_theight', 'asv_tumblelog_twidth', 'asv_tumblelog_tcrop', 'asv_tumblelog_vheight', 'asv_tumblelog_vwidth'))));

		($asv_tumblelog_sourcelink)? set_pref('asv_tumblelog_sourcelink', $asv_tumblelog_sourcelink, 'asv_tumblelog', ''):''; 
		
		($asv_tumblelog_section)? set_pref('asv_tumblelog_section', $asv_tumblelog_section, 'asv_tumblelog', ''): '';

		($asv_tumblelog_feed_id_field)? set_pref('asv_tumblelog_feed_id_field', $asv_tumblelog_feed_id_field, 'asv_tumblelog', ''):''; 
		
		(file_exists($asv_tumblelog_simplepie)) ? set_pref('asv_tumblelog_simplepie', $asv_tumblelog_simplepie, 'asv_tumblelog', ''): $message = "$asv_tumblelog_simplepie not found. ";		
		
		($asv_tumblelog_postform)? set_pref('asv_tumblelog_postform', $asv_tumblelog_postform, 'asv_tumblelog', ''):'';	
		
		($asv_tumblelog_quoteform)? set_pref('asv_tumblelog_quoteform', $asv_tumblelog_quoteform, 'asv_tumblelog', ''):'';	
		
		($asv_tumblelog_photoform)? set_pref('asv_tumblelog_photoform', $asv_tumblelog_photoform, 'asv_tumblelog', ''):'';	
		
		($asv_tumblelog_linkform)? set_pref('asv_tumblelog_linkform', $asv_tumblelog_linkform, 'asv_tumblelog', ''):'';		
		
		($asv_tumblelog_videoform)? set_pref('asv_tumblelog_videoform', $asv_tumblelog_videoform, 'asv_tumblelog', ''):'';
		
		($asv_tumblelog_theight)? set_pref('asv_tumblelog_theight', $asv_tumblelog_theight, 'asv_tumblelog', ''):'';
		
		($asv_tumblelog_twidth)? set_pref('asv_tumblelog_twidth', $asv_tumblelog_twidth, 'asv_tumblelog', ''):'';
			
		($asv_tumblelog_tcrop)? set_pref('asv_tumblelog_tcrop', '1', 'asv_tumblelog', ''):set_pref('asv_tumblelog_tcrop', '0', 'asv_tumblelog', '');
		
		($asv_tumblelog_vheight)? set_pref('asv_tumblelog_vheight', $asv_tumblelog_vheight, 'asv_tumblelog', ''):'';
		
		($asv_tumblelog_vwidth)? set_pref('asv_tumblelog_vwidth', $asv_tumblelog_vwidth, 'asv_tumblelog', ''):'';
		
		$message .= "Settings saved.";
		
	}
	
	pagetop('Tumblelog', $message);
	
	extract(asv_tumblelog_get_prefs());
	
	echo n.n.'<form name="tumblelog-admin" method="post" action="index.php?event=asv_tumblelog&step=settings">';
	
	echo asv_tumblelog_title($step).
	
		tag('General Settings' ,'h3', ' style="text-align: center;"').
		
		startTable('list').
					
			tr(td('Tumblelog Section').td(asv_tumblelog_section_popup($asv_tumblelog_section,
			'asv_tumblelog_section'))).
			
			tr(td('Source Link Field').td(asv_tumblelog_custom_popup($asv_tumblelog_sourcelink, 'asv_tumblelog_sourcelink'))).
						
			tr(td('Feed ID Field').td(asv_tumblelog_custom_popup($asv_tumblelog_feed_id_field, 'asv_tumblelog_feed_id_field'))).
			
			tr(td('SimplePie Path').td(fInput('text', 'asv_tumblelog_simplepie', $asv_tumblelog_simplepie))).
			
		endTable().
		
		tag(' ', 'p'). 
		
		tag('Forms' ,'h3', ' style="text-align: center;"').
		
		startTable('list').
		
			tr(td('Post').td(asv_tumblelog_form_popup($asv_tumblelog_postform, 'asv_tumblelog_postform'))).
			
			tr(td('Quote').td(asv_tumblelog_form_popup($asv_tumblelog_quoteform, 'asv_tumblelog_quoteform'))).
			
			tr(td('Photo').td(asv_tumblelog_form_popup($asv_tumblelog_photoform, 'asv_tumblelog_photoform'))).
			
			tr(td('Link').td(asv_tumblelog_form_popup($asv_tumblelog_linkform, 'asv_tumblelog_linkform'))).
			
			tr(td('Video ').td(asv_tumblelog_form_popup($asv_tumblelog_videoform, 'asv_tumblelog_videoform'))).
			
		endTable().
				
		tag(' ', 'p'). 
		
		tag('Image/Video' ,'h3', ' style="text-align: center;"').
		
		startTable('list').
		
			tr(td('Thumbnail Height').td(fInput('text', 'asv_tumblelog_theight', $asv_tumblelog_theight,'','','','5')).td('Thumbnail Width').td(fInput('text', 'asv_tumblelog_twidth', (($asv_tumblelog_twidth)?$asv_tumblelog_twidth:'0'),'','','','5')).td('Crop').td(checkbox('asv_tumblelog_tcrop','1', $asv_tumblelog_tcrop, '', 'asv_tumblelog_tcrop'))).
			
			tr(td('Video Height').td(fInput('text', 'asv_tumblelog_vheight', $asv_tumblelog_vheight,'','','','5')).td('Video Width').td(fInput('text', 'asv_tumblelog_vwidth', $asv_tumblelog_vwidth,'','','','5')).td().td()).
			
			tr(tda(fInput('submit','save_settings','save',"publish", '', '', '', 4), ' colspan=4')).
			
		endTable();
		
	echo n.n.'</form>'.
	'<h3 style="text-align:center"><a href="http://'.$prefs['siteurl'].'/?asv_tumblelog_updatefeeds=1">manually update feeds</a></h3>';
}
//--------------------------------------------------------------
function asv_tumblelog_feeds($step)
{
	global $txp_user;
	
	asv_tumblelog_verifyTable();
	
	$message = '';
	
	extract(doSlash(gpsa(array('rsspath', 'type', 'comments', 'category1', 'category2', 'asv_image', 'asv_video', 'keywords', 'ID', 'favicon',))));
				
	extract(asv_tumblelog_get_prefs());
	
	if($asv_tumblelog_simplepie)
	{
		if(gps('save_feeds')){
	
			if($type){
	
				if($rsspath){
				
					list($title, $favicon, $url) = asv_tumblelog_verifyfeed($rsspath, $asv_tumblelog_simplepie);
		
					if($title && $rsspath){
					
						$title = doSlash($title);
						
						if($ID && assert_int($ID)){
							safe_update('asv_tumblelog_feeds', 
								"Feed = '$rsspath',
								Title = '$title',
								Type = '$type',
								URL = '$url',
								Image = '$asv_image',
								Video = '$asv_video',
								Favicon = '$favicon',
								Category1 = '$category1',
								Category2 = '$category2',
								Keywords = '$keywords',
								AuthorID = '$txp_user',
								Annotate = '$comments'",
								
								"ID = $ID"
								);		
							$message = "Updated $title.";
						} else{
							safe_insert('asv_tumblelog_feeds', 
								"Feed = '$rsspath',
								Title = '$title',
								Type = '$type',
								Image = '$asv_image',
								Video = '$asv_video',
								URL = '$url',
								Favicon = '$favicon',
								Category1 = '$category1',
								Category2 = '$category2',
								Keywords = '$keywords',
								AuthorID = '$txp_user',
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
			
        $checked_image = array (
        	false,
        	false,
        	false
        );
		
        if ($asv_image) {
        	
        	$checked_image[$asv_image] = true;
			
        } else {
        	
        	$checked_image[0] = true;
			
        }
			
		echo n.n.'<form name="tumblelog-admin" method="post" action="index.php?event=asv_tumblelog&step=feeds">'.
				hInput('step', 'feeds').
				hInput('ID', $ID);
		
		echo asv_tumblelog_title($step).
		
			tag('<a href="index.php?event=asv_tumblelog&step=feeds">Add New Feed</a>' ,'h3', ' style="text-align: center;"').
			
			startTable('list').
			
				tr(td('Atom/RSS Path').td(fInput('text', 'rsspath', $rsspath))).
				
				tr(td('Import Images').td(radio('asv_image', '0', $checked_image[0]).'no '.radio('asv_image', '1', $checked_image[1]).'url '.radio('asv_image', '2', $checked_image[2]).'image')).
				
				tr(td('Convert Video').td(yesnoRadio('asv_video', ($asv_video == '0')? $asv_video : "1"))).
				
				tr(td('Form').td(asv_tumblelog_form_popup($type, 'type'))).
				
				tr(td('Comments').td(onoffRadio('comments', ($comments == '0')? $comments : "1"))).
				
				tr(td('Category1').td(asv_tumblelog_cat_popup($category1, 'category1'))).
				
				tr(td('Category2').td(asv_tumblelog_cat_popup($category2, 'category2'))).
				
				tr(td('Keywords').td(fInput('text', 'keywords', $keywords))).
				
				tr(tda(fInput('submit','save_feeds', ($ID)?'save':'add',"publish", '', '', '', 4), ' colspan=2')).
				
			endTable().'</form>';
			
		echo tag("Current Feeds", "h3", ' style="text-align:center"').
		
			'<form name="longform" method="post" action="index.php?event=asv_tumblelog&step=feeds">'.
				hInput('event', 'asv_tumblelog').
				hInput('step', 'feeds').
				hInput('action', 'delete').
			
				n.startTable('list', '', '', '', '90%').
					n.tr(
						n.column_head('ID', 'id', 'asv_tumblelog', false, '', '', '').	
						n.column_head('Favicon', 'favicon', 'asv_tumblelog', false, '', '', '').
						n.column_head('Title', 'title', 'asv_tumblelog', false, '', '', '').			
						n.column_head('URL', 'url', 'asv_tumblelog', false, '', '', '').			
						n.column_head('Feed', 'feed', 'asv_tumblelog', false, '', '', '').			
						n.column_head('Image', 'image', 'asv_tumblelog', false, '', '', '').		
						n.column_head('Video', 'video', 'asv_tumblelog', false, '', '', '').
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
				$link = "<a href=\"index.php?event=asv_tumblelog&step=feeds&rsspath=".urlencode($Feed)."&asv_image=".$Image."&type=".$Type."&comments=".$a['Annotate']."&category1=$Category1&category2=$Category2&keywords=$Keywords&ID=$ID&asv_video=".$Video."\">$Title</a>";
				
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
							td(($Video)?"yes":"no").
							td($Type).
							td(($Annotate)?"on":"off").
							td($Category1).
							td($Category2).
							td($Keywords).
							td($LastUpdate).
							td(fInput('checkbox', 'selected[]', $ID
						)));
					
			}
			echo n.tr(
					tda(select_buttons().asv_tumblelog_list_multiedit_form(),' colspan="9" style="text-align: right; border: none;"'));
		}
		echo n.endTable().'</form>';
	} else{
		pagetop('Tumblelog > Feeds', "SimplePie path required. Add under Settings.");
		echo asv_tumblelog_title($step);
	}
	
}
//--------------------------------------------------------------
function asv_tumblelog_formdesign($step)
{

	extract(doSlash(asv_tumblelog_get_prefs()));
	
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
	
	$where_array = array();
	if($asv_tumblelog_postform) $where_array[] = $asv_tumblelog_postform;
	if($asv_tumblelog_photoform) $where_array[] = $asv_tumblelog_photoform;
	if($asv_tumblelog_quoteform) $where_array[] = $asv_tumblelog_quoteform;
	if($asv_tumblelog_linkform) $where_array[] = $asv_tumblelog_linkform;
	if($asv_tumblelog_videoform) $where_array[] = $asv_tumblelog_videoform;
	
	// Get all the forms used by the feeds
	$rs = safe_rows_start('DISTINCT Type', 'asv_tumblelog_feeds', '1=1');
		
	if ($rs) {
		while ($a = nextRow($rs)) {
			$where_array[] = $a['Type'];
		}
	}
	
	$where = "name in ('".join("', '", $where_array)."') AND type ='article' ORDER BY name ASC";		
			
	$rs_forms = safe_rows_start("*", 'txp_form', $where);
	
    if ($rs_forms) {
    	
		while( $formdata = nextRow($rs_forms) ) {

	    	echo n . '<form name="post-form" method="post" action="index.php">' .
		            	n . hInput('event', 'asv_tumblelog') .
		            	n . hInput('step', 'design') .
		            	n . hInput('action', 'save') .
		            	n . hInput('form-name', $formdata['name']) .
		            	tr(tda("<b>" . $formdata['name'] . "</b>", ' style="text-align:center"')) .
		            	tr(td(text_area('form', '150', '500', $formdata['Form']))) .
		            	tr(tda(fInput('submit', 'save_settings', 'save', "publish", '', '', '', 4), ' style="text-align:right"')) . '</form>';
		}
	}
	
	echo endTable();
}
//--------------------------------------------------------------
function asv_tumblelog_pagedesign($step)
{

	extract(doSlash(asv_tumblelog_get_prefs()));
	
	if($asv_tumblelog_section)
	{
		$message = '';
	
		if(gps('action')=='save' && gps('page-name'))
		{		
			$rs = safe_update("txp_page", "user_html='".doSlash(gps('form'))."'", "name = '".doSlash(gps('page-name'))."'");
			$message = 'Page saved';
		}
		
		pagetop('Tumblelog', $message);
		
		echo asv_tumblelog_title($step);
		
		$rs = safe_row("page", 'txp_section', "name = '$asv_tumblelog_section'");
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
	} else{
	
		pagetop("Tumblelog > Page Design" , "Define the tumblelog section under Settings");
		
		echo asv_tumblelog_title($step);
	
	}
}
//--------------------------------------------------------------
function asv_tumblelog_pagestyle($step)
{

	extract(doSlash(asv_tumblelog_get_prefs()));
	
	if($asv_tumblelog_section)
	{
		$message = '';
		if(gps('action')=='save' && gps('style-name'))
		{	
			$form = doSlash(base64_encode(gps('form')));
			$rs = safe_update("txp_css", "css='".$form ."'", "name = '".doSlash(gps('style-name'))."'");
			$message = 'Style saved';
		}
		
		pagetop('Tumblelog', $message);
		
		echo asv_tumblelog_title($step);
		
		$rs = safe_row("css", 'txp_section', "name = '$asv_tumblelog_section'");
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
	} else{
	
		pagetop("Tumblelog > Page Style" , "Define the tumblelog section under Settings");
		
		echo asv_tumblelog_title($step);
	
	}
}
//--------------------------------------------------------------
function asv_tumblelog_bookmarklet($step)
{
	global $prefs;
	
	extract(asv_tumblelog_get_prefs());
	
	if($asv_tumblelog_section){
	
		pagetop('Tumblelog > Bookmarklet', '');
		
		
		echo asv_tumblelog_title($step);
		
		//Get custom field
		$linkfield_set = '';
		$custom_row = safe_row("*", 'txp_prefs', "val='$asv_tumblelog_sourcelink'");
		if($custom_row)
		{
			$linkfield_set = 'custom_'.$custom_row['position'];
		}
		else
		{
			$linkfield_set = "custom_1";
		}
		
		
		$bookmarklet = "javascript:var d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='http://".$prefs['siteurl']."/textpattern/index.php?',l=d.location,e=encodeURIComponent,p='event=asv_tumblelog&step=mini&bm=1&formname=".$asv_tumblelog_linkform."&sourceurl='+e(l.href)+'&title='+e(d.title)+'&body='+e(s),u=f+p;a=function(){if(!w.open(u,'t','toolbar=0,resizable=0,status=1,width=500,height=400'))l.href=u;};if(/Firefox/.test(navigator.userAgent))setTimeout(a,0);else a();void(0)";
		
		
		
		echo startTable('list').
			tr(tda('Add the following to your bookmarks to make a quick post', ' style="text-align:center"')).
			tr(tda("<a href=\"$bookmarklet\" title='Drag this link to your Bookmarks Bar. Click to learn more.'>Share on TXP</a>", ' style="text-align:center"')).
			endTable();
	} else{
	
		pagetop("Tumblelog > Bookmarklet" , "Define the tumblelog section under Settings");
		
		echo asv_tumblelog_title($step);
	
	}
}
//--------------------------------------------------------------
function asv_tumblelog_mini($step)
{
	global $txp_user, $vars, $txpcfg, $prefs;
	
	$message = '';
	
	extract($prefs);
	extract(asv_tumblelog_get_prefs());
	extract($txpcfg);
	
	if(!defined("IMPATH")) define("IMPATH",$path_to_site.'/'.$img_dir.'/');

	$incoming = gpsa(array('method', 'formname', 'sourceurl', 'title', 'body', 'category1', 'category2', 'keywords', 'photourl', 'photo_import'));
				
	$incoming = asv_tumblelog_textile_main_fields($incoming, $use_textile);
	extract(doSlash($incoming));

	
	if(gps('action')=='create')
	{
		
		$customField = asv_tumblelog_customfield($asv_tumblelog_sourcelink);
		
		if(($method=='photo') && ($photo_import)){
		
			$file_content = asv_tumblelog_filecontent($photourl);
							
			$ext = strrchr($photourl, '.');							
			$temp_file = tempnam(IMPATH, 'asv_tumblelog_image_');
			
			//write to file		
			$fh = fopen($temp_file, 'w');
			fwrite($fh, $file_content);
			fclose($fh);
			
			chmod($temp_file, '777');
			
			$thumb_h = (isset($asv_tumblelog_theight))? $asv_tumblelog_theight : "0";
			$thumb_w = (isset($asv_tumblelog_twidth))?  $asv_tumblelog_twidth: "0";
			$thumb_crop = (isset($asv_tumblelog_tcrop))?  $asv_tumblelog_tcrop: "0";							
			
			list($image_message, $photourl) = asv_tumblelogimage_data($temp_file,array(
				'name'=> $title, 'category' => '', 'caption' => '', 'alt' => '', 'date'=>'now()', 'thumb_w'=>$thumb_w, 'thumb_h'=>$thumb_h, 'thumb_crop'=>$thumb_crop));
		
		
		}
			
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
					Section         = '".$asv_tumblelog_section."',
					Category1       = '".$category1."',
					Category2       = '".$category2."',
					textile_body    =  1,
					textile_excerpt =  1,
					Annotate        =  1,
					override_form   = '".$formname."',
					url_title       = '',
					$customField 		= '".(($method!='post')? $sourceurl: "")."',
					AnnotateInvite  = '$comments_default_invite',
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
		    		$photourl = str_replace('.jpg', '_m.jpg', urldecode(asv_tumblelog_grabimgsrc($match)));
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
					tr(td('Form').td(asv_tumblelog_form_popup($asv_tumblelog_postform, 'formname'))).
					tr(td('Title').td(fInput('text', 'title', '', '', '', '', '50'))).
					tr(td('Post').td(text_area('body', '100', '250', $body))).
					tr(tda(fInput('submit', 'save', 'post', 'publish'), ' colspan=2 style="text-align: right"')).
					endTable()
				).
				td(
					startTable('list').
						tr(td()).
						tr(td('Category 1<br />'.asv_tumblelog_cat_popup($category1,'category1'))).
						tr(td('Category 2<br />'.asv_tumblelog_cat_popup($category2,'category2'))).
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
					tr(td('Form').td(asv_tumblelog_form_popup($asv_tumblelog_quoteform, 'formname'))).
					tr(td('Source URL').td(fInput('text', 'sourceurl', $sourceurl, '', '', '', '50'))).
					tr(td('Title').td(fInput('text', 'title', $title, '', '', '', '50'))).
					tr(td('Post').td(text_area('body', '100', '250', $body))).
					tr(tda(fInput('submit', 'save', 'post', 'publish'), ' colspan=2 style="text-align: right"')).
					endTable()
				).
				td(
					startTable('list').
						tr(td()).
						tr(td('Category 1<br />'.asv_tumblelog_cat_popup($category1,'category1'))).
						tr(td('Category 2<br />'.asv_tumblelog_cat_popup($category2,'category2'))).
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
				
		echo startTable('asv_tumblelog_link').
			tr(
				td(			
					startTable('list').
					tr(tda('Add a new link', ' colspan=2')).
					tr(td('Form').td(asv_tumblelog_form_popup($asv_tumblelog_linkform, 'formname'))).
					tr(td('Source URL').td(fInput('text', 'sourceurl', $sourceurl, '', '', '', '50'))).
					tr(td('Title').td(fInput('text', 'title', $title, '', '', '', '50'))).
					tr(td('Post').td(text_area('body', '100', '250', $body))).
					tr(tda(fInput('submit', 'save', 'post', 'publish'), ' colspan=2 style="text-align: right"')).
					endTable()
				).
				td(
					startTable('list').
						tr(td()).
						tr(td('Category 1<br />'.asv_tumblelog_cat_popup($category1,'category1'))).
						tr(td('Category 2<br />'.asv_tumblelog_cat_popup($category2,'category2'))).
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
					tr(td('Form').td(asv_tumblelog_form_popup($asv_tumblelog_photoform, 'formname'))).
					tr(td('Photo URL').td(fInput('text', 'photourl', $photourl, '', '', '', '50').'<br />'.checkbox('photo_import','1', '0')."Import to TXP")).
					tr(td('Source URL').td(fInput('text', 'sourceurl', $sourceurl, '', '', '', '50'))).
					tr(td('Title').td(fInput('text', 'title', $title, '', '', '', '50'))).
					tr(td('Post').td(text_area('body', '100', '250', $body))).
					tr(tda(fInput('submit', 'save', 'post', 'publish'), ' colspan=2 style="text-align: right"')).
					endTable()
				).
				td(
					startTable('list').
						tr(td()).
						tr(td('Category 1<br />'.asv_tumblelog_cat_popup($category1,'category1'))).
						tr(td('Category 2<br />'.asv_tumblelog_cat_popup($category2,'category2'))).
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
					tr(td('Form').td(asv_tumblelog_form_popup($asv_tumblelog_videoform, 'formname'))).
					tr(td('Source URL').td(fInput('text', 'sourceurl', $sourceurl, '', '', '', '50'))).
					tr(td('Title').td(fInput('text', 'title', $title, '', '', '', '50'))).
					tr(td('Post').td(text_area('body', '100', '250', $video_body))).
					tr(tda(fInput('submit', 'save', 'post', 'publish'), ' colspan=2 style="text-align: right"')).
					endTable()
				).
				td(
					startTable('list').
						tr(td()).
						tr(td('Category 1<br />'.asv_tumblelog_cat_popup($category1,'category1'))).
						tr(td('Category 2<br />'.asv_tumblelog_cat_popup($category2,'category2'))).
						tr(td('Keywords<br />'.fInput('text', 'keywords', ''))).
					endTable()
				));	
	echo '</form></body></html>';
	
	exit();
}
//--------------------------------------------------------------
function asv_tumblelog_update($step)
{
	global $txp_user;

	extract(asv_tumblelog_get_prefs());
	
	set_time_limit(120);

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
			ob_flush();
			flush();
			extract($a);

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
				
			echo asv_tumblelog_importfeed(array(
				'feed'	=> $Feed,
				'simplepie' => $asv_tumblelog_simplepie,
				'type'	=> $Type,
				'category1'	=> $Category1,
				'category2' => $Category2,
				'section'	=> $asv_tumblelog_section,
				'form'		=> $Type,
				'linkfield'	=> $asv_tumblelog_sourcelink,
				'pubdate' => '',
				'comments'	=> $Annotate,
				'keywords'	=> $Keywords,
				'feed_id_field'	=> $asv_tumblelog_feed_id_field,
				'feed_id' => $ID,
				'lastupdate' => $LastUpdate,
				'importimage' => $Image,
				'convertvideo' => $Video,
				'AuthorID'	=> $AuthorID,
				));
		}
	}
	echo '</pre>';
	exit();
}
//--------------------------------------------------------------

//--------------------------------------------------------------
// The beast that imports the feeds
//--------------------------------------------------------------
function asv_tumblelog_importfeed($atts)
{
	global $prefs, $txpcfg, $txp_user;
	extract(get_prefs());
	
	
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
			'lastupdate' => 'January 1, 1970',
			'importimage' => '0',
			'convertvideo' => '0',
			'AuthorID'	=> '',
			),$atts));
			
	$message = '';		
	
	//Get SimplePie
	require_once($simplepie);
	
	//Create and setup SimplePie Instance
	$thefeed = new SimplePie();
	$thefeed->set_feed_url($feed);
	$thefeed->set_favicon_handler('./plugins/asv_tumblelog_importfeed.php', 'favicon');
	$thefeed->enable_cache(false);
	$thefeed->handle_content_type();
	
	echo "Getting $feed\r\n";
	//Get the feed
	$success = $thefeed->init();
	
	if($success) {
		echo "\tSuccess!\r\n";
		$feeditems = $thefeed->get_items();
		$favicon = $thefeed->get_favicon();
		echo "\tFavicon - ".$favicon."\r\n";
		
		if($thefeed->get_item_quantity()>0){
			$latest_item = $thefeed->get_item(0);
			$latestfeedupdate = $latest_item->get_date('U');
			safe_update('asv_tumblelog_feeds', 'LastUpdate = from_unixtime('.$latestfeedupdate.')', "ID = '$feed_id'");
		}
		
		//reverse order
		$feeditems = asv_tumblelog_quickSort($feeditems);
		
		foreach($feeditems as $feeditem) {
			if($feeditem->get_date('U') > strtotime($lastupdate))
			{
				//Get the permalink
				$out['permalink'] = $feeditem->get_link();		
				
				// Get item title
				$out['title'] = addslashes(asv_tumblelog_specialparser($feeditem->get_title(), $out['permalink'], true, $convertvideo));
				
				
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
								
				if(!asv_tumblelog_beginswith($feeditem->get_description(), "<p"))
				{
					$out['body'] = tag(doSlash(asv_tumblelog_specialparser($feeditem->get_description(), $out['permalink'], false, $convertvideo)), 'p');
				}
				else
				{
					$out['body'] = doSlash(asv_tumblelog_specialparser($feeditem->get_description(), $out['permalink'], false, $convertvideo));
				}		
	
				//Check to see if the article has already been imported
				$exists = safe_count('textpattern', "Title = '".$out['title']."' AND Posted=$when AND Section='$section'");
								
				//If it hasn't then let's add it
				if($exists==0){
										
					//Import the image
					$image = '';
					if($importimage)
					{
						if(!defined("IMPATH")) define("IMPATH",$path_to_site.'/'.$img_dir.'/');
						$feedDescription = $feeditem->get_content();
						$image = asv_tumblelog_grabimage($feedDescription);
						$image = urldecode(asv_tumblelog_grabimgsrc($image));
						
						//Hack to get the larger size of flickr images
						if(strstr($image, "flickr.com"))
							$image = str_replace("_m.jpg", ".jpg", $image);
							
						if($importimage == '2')
						{			
							$file_content = asv_tumblelog_filecontent($image);
							
							$ext = strrchr($image, '.');							
							$temp_file = tempnam(IMPATH, 'asv_tumblelog_image_');
							
							//write to file		
							$fh = fopen($temp_file, 'w');
							fwrite($fh, $file_content);
							fclose($fh);
							
							chmod($temp_file, '777');
							
							$thumb_h = (isset($asv_tumblelog_theight))? $asv_tumblelog_theight : "0";
							$thumb_w = (isset($asv_tumblelog_twidth))?  $asv_tumblelog_twidth: "0";
							$thumb_crop = (isset($asv_tumblelog_tcrop))?  $asv_tumblelog_tcrop: "0";							
							
							list($image_message, $image_id) = asv_tumblelogimage_data($temp_file,array(
								'name'=> $out['title'], 'category' => '', 'caption' => '', 'alt' => '', 'date'=>$when, 'thumb_w'=>$thumb_w, 'thumb_h'=>$thumb_h, 'thumb_crop'=>$thumb_crop));
							if($image_id)
							{
								echo "\t$image_message\r\n";							
								$image = $image_id;
							}
						}
					}
					
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
						AuthorID        = '$AuthorID',
						Section         = '$section',
						Category1       = '$category1',
						Category2       = '$category2',
						textile_body    =  0,
						textile_excerpt =  0,
						Annotate        =  $comments,
						override_form   = '$form',
						url_title       = '',
						$linkfield_set 		= '".$out['permalink']."',
						AnnotateInvite  = '$comments_default_invite',
						uid             = '".md5(uniqid(rand(),true))."',
						feed_time       = $when, ".
						(($tempField = asv_tumblelog_customfield($feed_id_field))? "$tempField = '$feed_id'" : "''")
						
					);
					
					if($result)				
					{
						//do_pings();
						update_lastmod();
						echo "\tAdded - ".$out['title']."\r\n";
					}
				}
				else
				{
					echo "\tExists - ".$out['title']."\r\n";
				}
			}
			else
			{
				echo "\tNot importing ".$feeditem->get_title()."\r\n";
				echo "\t".$feeditem->get_date()."\t".$lastupdate."\r\n";
			}
		}
	}
	else {
		echo "\t".$thefeed->error;
	}
	flush();
	//return $message;
}
//--------------------------------------------------------------

//--------------------------------------------------------------
// Public tags
//--------------------------------------------------------------
function asv_tumblelog_favicon($atts, $thing)
{
	global $thisarticle;
	
	extract(asv_tumblelog_get_prefs());
			
	$url = fetch('Favicon', 'asv_tumblelog_feeds', 'ID', $thisarticle[$feed_id_field]);
	
	if($url)
		return $url;

	return '';	
}
//--------------------------------------------------------------
function asv_tumblelog_feed($atts, $thing)
{
	global $thisarticle;
	
	extract(asv_tumblelog_get_prefs());
			
	$url = fetch('Title', 'asv_tumblelog_feeds', 'ID', $thisarticle[$feed_id_field]);
	
	if($url)
		return $url;

	return '';	
}
//--------------------------------------------------------------
function asv_tumblelog_feedurl($atts, $thing)
{
	global $thisarticle;
	
	extract(asv_tumblelog_get_prefs());
			
	$url = fetch('Feed', 'asv_tumblelog_feeds', 'ID', $thisarticle[$feed_id_field]);
	
	if($url)
		return $url;

	return '';
}
//--------------------------------------------------------------
function asv_tumblelog_permalinkl($atts, $thing)
{
	global $thisarticle;
	
	extract(asv_tumblelog_get_prefs());
			
	$url = fetch('URL', 'asv_tumblelog_feeds', 'ID', $thisarticle[$feed_id_field]);
	
	if($url)
		return $url;

	return '';
}
//--------------------------------------------------------------

//--------------------------------------------------------------
// Utility functions
//--------------------------------------------------------------

function asv_tumblelog_grabimage ($text) 
{
    $text = html_entity_decode($text);
    $pattern = "/<img[^>]+\>/i";
    preg_match($pattern, $text, $matches);
    if($matches)	return $matches[0];
	return '';
}
//--------------------------------------------------------------
function asv_tumblelog_grabimgsrc($text) 
{
    $pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/'; 
    
	preg_match($pattern, $text, $link);
	
	if($link)	
		return urlencode($link[1]);
		
	return '';
}
//--------------------------------------------------------------
function asv_tumblelog_beginswith($str, $sub) 
{
    return (strncmp($str, $sub, strlen($sub)) == 0);
}
//--------------------------------------------------------------
function asv_tumblelogimage_data($file , $meta = '')
{
	/*
	 *  Modified from core to allow public uploading of images
	 */

	global $txpcfg, $extensions, $prefs, $file_max_upload_size;

	extract($txpcfg);
	
	extract(get_prefs());
	
	$extensions = array(0,'.gif','.jpg','.png','.swf',0,0,0,0,0,0,0,0,'.swf');
	
	if(!defined("IMPATH")) define("IMPATH",$path_to_site.'/'.$img_dir.'/');
	
	include_once(txpath.'/lib/class.thumb.php');

	list($w, $h, $extension) = getimagesize($file);

	if (($file !== false) && @$extensions[$extension])
	{
		$ext = $extensions[$extension];

		if ($meta == false)
		{
			$meta = array('category' => '', 'caption' => '', 'alt' => '');
		}

		extract($meta);

		$q ="
			name = '$name',
			ext = '$ext',
			w = $w,
			h = $h,
			alt = '$alt',
			caption = '$caption',
			category = '$category',
			date = $date,
			author = ''
		";

		if (empty($id))
		{
			$rs = safe_insert('txp_image', $q);

			$id = $GLOBALS['ID'] = mysql_insert_id();
		}

		else
		{
			$id = assert_int($id);

			$rs = safe_update('txp_image', $q, "id = $id");
		}

		if (!$rs)
		{
			return gTxt('image_save_error');
		}

		else
		{
			$newpath = IMPATH.$id.$ext;

			if (shift_uploaded_file($file, $newpath) == false)
			{
				$id = assert_int($id);

				safe_delete('txp_image', "id = $id");

				safe_alter('txp_image', "auto_increment = $id");

				if (isset($GLOBALS['ID']))
				{
					unset( $GLOBALS['ID']);
				}

				return $newpath.sp.gTxt('upload_dir_perms');
			}

			else
			{
				@chmod($newpath, 0644);

				// Auto-generate a thumbnail using the last settings
				$t = new txp_thumb( $id );
				
				
				if (isset($thumb_w) && is_numeric($thumb_w))
						$t->width = $thumb_w;
				
				if (isset($thumb_h) && is_numeric($thumb_h))
						$t->height = $thumb_h;

				$t->crop = ($thumb_crop == '1');
				$t->hint = '0';

				$t->write();
				
				$message = gTxt('image_uploaded', array('{name}' => $name));
				update_lastmod();

				return array($message, $id);
			}
		}
	}

	else
	{
		if ($file === false)
		{
			return upload_get_errormsg($error);
		}

		else
		{
			return gTxt('only_graphic_files_allowed');
		}
	}
}
//--------------------------------------------------------------
function asv_tumblelog_title($active)
{

	$titles = array('settings'=>'Settings', 'feeds'=>'Feeds', 'page-design'=>'Page Design', 'page-style'=>'Page Style', 'design'=>'Form Design', 'bookmarklet'=>'Bookmarklet');
	
	$newtitles = array();
	
	foreach($titles as $key=>$title) {
	
		if($title!=$active) {
			
			$title='<a href="index.php?event=asv_tumblelog&step='.$key.'">'.$title.'</a>';
		}
		
		array_push($newtitles, $title);
	}
	
	return tag(join($newtitles, " | "), 'h1', ' style="text-align: center;"');
}
//--------------------------------------------------------------
function asv_tumblelog_section_popup($Section, $id)
{
	$rs = safe_column('name', 'txp_section', "name != 'default'");
  
	if ($rs)
	{
		return selectInput($id, $rs, $Section, false, '', $id);
	}
  
	return false;
}
//--------------------------------------------------------------
function asv_tumblelog_custom_popup($custom, $id)
{
	$rs = safe_column('val', 'txp_prefs', "name LIKE 'custom_%_set'");
  
	if ($rs)
	{
		return selectInput($id, $rs, $custom, false, '', $id);
	}
  
	return false;
}
//--------------------------------------------------------------
function asv_tumblelog_form_popup($custom, $id)
{
	$rs = safe_column('name', 'txp_form', "type='article'");
  
	if ($rs)
	{
		return selectInput($id, $rs, $custom, false, '', $id);
	}
  
	return false;
}
//--------------------------------------------------------------
function asv_tumblelog_cat_popup($custom, $id)
{
	$rs = getTree('root', 'article');

	if ($rs)
	{
		return treeSelectInput($id,$rs,$custom, $id);
	}

	return false;
}
//-------------------------------------------------------------
function asv_tumblelog_get_prefs()
{
	$out = array();
	
	
	$r = safe_rows_start('name, val', 'txp_prefs', "event='asv_tumblelo' AND name LIKE 'asv_tumblelog_%'");
	if ($r) {
		while ($a = nextRow($r)) {
			$out[$a['name']] = $a['val'];
		}
		
		$needed_prefs = array(
			'asv_tumblelog_sourcelink'	=> '',
			'asv_tumblelog_section' => '',
			'asv_tumblelog_simplepie' => '',
			'asv_tumblelog_postform'	=> '',
			'asv_tumblelog_quoteform'	=> '',
			'asv_tumblelog_photoform'	=> '',
			'asv_tumblelog_linkform' 	=> '',
			'asv_tumblelog_videoform' => '',
			'asv_tumblelog_rssfeedpage' => '',
			'asv_tumblelog_feed_id_field' => '',
			'asv_tumblelog_theight' => '0',
			'asv_tumblelog_twidth' => '0',
			'asv_tumblelog_vheight' => '0',
			'asv_tumblelog_vwidth' => '0',
			'asv_tumblelog_tcrop' => '0'
			);
		
		$diff = array_diff(array_keys($needed_prefs), array_keys($out));
		
		if($diff)
		{
			foreach($diff as $val)
			{
				set_pref($val,  $needed_prefs[$val], 'asv_tumblelog', '');
			}
			return asv_tumblelog_get_prefs();
		}
		else
		{
			return $out;
		}
	}
}
// -------------------------------------------------------------
function asv_tumblelog_customfield($name)
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
    $methods = array (
    	'delete' => gTxt('delete'),
    	
    );
    
    return event_multiedit_form('list', $methods, '', '', '', '', '');
}
// -------------------------------------------------------------
function asv_tumblelog_quickSort($items) {//from bit_rss
    if(count($items) > 1) {
        $pivot = array_pop($items);
    
        $less = array();
        $more = array();
        for($i = 0; $i < count($items); $i++) {
            $cItem = $items[$i];
            if($cItem->get_date('U') >= $pivot->get_date('U')) {
                $more[] = $cItem;
            }else {
                $less[] = $cItem;
            }
        }
        
        return array_merge(asv_tumblelog_quickSort($less), array($pivot), asv_tumblelog_quickSort($more));
    }else {
        return $items;
    }
}
//--------------------------------------------------------------
function asv_tumblelog_verifyfeed($feed, $simplepie)
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
	
	$title = $thefeed->get_title();
	$logo = ($thefeed->get_image_url())? $thefeed->get_image_url() : $thefeed->get_favicon();
	$permalink = $thefeed->get_permalink();
	
	return ($success)? array($title, $logo, $permalink) :  false;

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
			`Video` int(2) NOT NULL default '0',
			`Annotate` int(2) NOT NULL default '0',
			`Type` varchar(128) NOT NULL default '',
			`Category1` varchar(128) NOT NULL default '',
			`Category2` varchar(128) NOT NULL default '',
			`Keywords` varchar(255) NOT NULL default '',
			 `AuthorID` varchar(64) NOT NULL default '',
			`LastUpdate` datetime,
			 PRIMARY KEY  (`ID`)
			 ) $tabletype PACK_KEYS=1 AUTO_INCREMENT=2 ");
			 
		if($rs = safe_show('COLUMNS', 'asv_tumblelog_feeds'))
		{
			if(count($rs)!=14)
			{
				$design_col = array('ID', 'Favicon', 'Title', 'Feed', 'URL', 'Image', 'Annotate', 'Type', 'Category1', 'Category2', 'Keywords', 'LastUpdate', 'Video', 'AuthorID');
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
						case 'Video':
							safe_alter('asv_tumblelog_feeds', "ADD `Video` int(2) NOT NULL default '0'");
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
						case 'AuthorID':
							safe_alter('asv_tumblelog_feeds', "Add `AuthorID` varchar(64) NOT NULL default ''");
							break;
							
					}
				}
			}
		}
	}
}
//--------------------------------------------------------------
function asv_tumblelog_filecontent($url)
{
	// create a new curl resource
	$ch = curl_init();
	// set URL and other appropriate options
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// grab URL, and return output
	$output = curl_exec($ch);
	//get errors		
	$err     = curl_errno( $ch );
	$errmsg  = curl_error( $ch );
	// close curl resource, and free up system resources
	curl_close($ch);
	return $output; 
}
//--------------------------------------------------------------
function asv_tumblelog_specialparser($input, $source, $title, $convert_video) {
	extract(asv_tumblelog_get_prefs());

	$video_height = '0';
	if (isset ($asv_tumblelog_vheight)) {
		if (intval($asv_tumblelog_vheight) > 0) {
			$video_height = $asv_tumblelog_vheight;
		}
	}

	$video_width = '0';
	if (isset ($asv_tumblelog_vwidth)) {
		if (intval($asv_tumblelog_vwidth) > 0) {
			$video_width = $asv_tumblelog_vwidth;
		}
	}

	if (strstr($source, "twitter.com")) {
		return preg_replace('/(\w+:) (\.*)/', '$2', $input);
	}
	elseif (strstr($source, "vimeo.com") && !$title && $convert_video) {
		$video_id = substr(strrchr($source, '/'), 1);

		if (!$video_height)
			$video_height = "327";
		if (!$video_width)
			$video_width = "480";

		$video_embed =<<<EOD
<p><object type="application/x-shockwave-flash" width="$video_width" height="$video_height" data="http://www.vimeo.com/moogaloop.swf?clip_id=$video_id&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=01AAEA">	<param name="quality" value="best" />	<param name="allowfullscreen" value="true" />	<param name="scale" value="showAll" />	<param name="movie" value="http://www.vimeo.com/moogaloop.swf?clip_id=$video_id&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=01AAEA" /></object></p>
EOD;
		return $video_embed;
	}
	elseif (strstr($source, "viddler.com") && !$title && $convert_video) {
		preg_match('/(.*?)[\\?&]token=(\w*)(.*)/', str_replace(array (
			"\n",
			"\r",
			"\t",
			" ",
			"\o",
			"\xOB"
		), '', $input), $matches);
		if ($matches) {
			$video_id = $matches[2];

			if (!$video_height)
				$video_height = "370";
			if (!$video_width)
				$video_width = "400";

			$video_embed =<<<EOD
<p><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="$video_width" height="$video_height" id="viddler"><param name="movie" value="http://www.viddler.com/player/$video_id/" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="true" /><embed src="http://www.viddler.com/player/$video_id/" width="$video_width" height="$video_height" type="application/x-shockwave-flash" allowScriptAccess="always" allowFullScreen="true" name="viddler" ></embed></object></p>
EOD;
			return $video_embed;
		}
	}
	elseif (strstr($source, "youtube.com") && !$title && $convert_video) {
		$video_id = preg_replace('/(.*?)[\\?&]v=([^\&#]*).*/', '$2', $source);

		if (!$video_height)
			$video_height = "355";
		if (!$video_width)
			$video_width = "425";

		$video_embed =<<<EOD
<p><object width="$video_width" height="$video_height"><param name="movie" value="http://www.youtube.com/v/$video_id&rel=1"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/$video_id&rel=1" type="application/x-shockwave-flash" wmode="transparent" width="$video_width" height="$video_height"></embed></object></p>
EOD;
		return $video_embed;
	}
	return $input;
}
// -------------------------------------------------------------
function asv_tumblelog_textile_main_fields($incoming, $use_textile) {
	global $txpcfg;

	include_once txpath . '/lib/classTextile.php';

	$textile = new Textile();

	$incoming['title_plain'] = $incoming['title'];
	
	$incoming['body_html'] = $textile->TextileThis(nl2br($incoming['body']));
	
	$incoming['title'] = $textile->TextileThis($incoming['title'], '', 1);

	return $incoming;
}
# --- END PLUGIN CODE ---

?>