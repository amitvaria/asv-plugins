//--------------------------------------------------------------
// reusable code
//--------------------------------------------------------------
function asv_tumblelog_grabimg($atts,$thing)
{
	global $prefs;
	extract($prefs);
	if(!defined("IMPATH")) define("IMPATH",$path_to_site.'/'.$img_dir.'/');
	$image = returnImage(parse($thing));
	$image = urldecode(scrapeImage($image));
	//Check to see if it needs to be imported into TXP Image
	if($image)
	{			
		/*//get extension
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
		}*/
		return $image;
	}	
	return '';
//	$out['body'] = '<p><a href="'.$out['permalink'].'"><img src="'.hu.$img_dir."/".$imageID.$ext.'" /></a></p>';
}