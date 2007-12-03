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
	//add_privs('asv_tumblelog','1,2,3,4'); // Allow only userlevels 1,2,3,4 acess to this plugin.
	register_tab('extensions', 'asv_tumblelog', "tumblelog");
	register_callback("asv_tumblelog", "asv_tumblelog");
}

function asv_tumblelog($event, $step)
{
	pagetop();
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