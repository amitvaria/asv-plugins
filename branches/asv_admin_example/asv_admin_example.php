<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_admin_example';

$plugin['version'] = '0.0';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'This plugin is just for testing purposes.';

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
$asv_pluginfeed = gps('pluginfeed');
if($asv_pluginfeed){
 asv_buildPluginFeed();
 exit;
}

function asv_buildPluginFeed(){
	global $file_base_path, $siteurl;
	$out[] = '';
	$rs = safe_rows_start('id, filename', 'txp_file', "1 order by filename");
	
	if ($rs and numRows($rs) > 0){
		while($a = nextRow($rs)){
			$asv_serialized = file_get_contents($file_base_path.'/'.$a['filename']);
			$asv_serialized = preg_replace('@.*\$plugin=\'([\w=+/]+)\'.*@s', '$1', $asv_serialized);
			$asv_serialized = preg_replace('/^#.*$/m', '', $asv_serialized);
			if(trim($asv_serialized)) {
				$asv_serialized = base64_decode($asv_serialized);
				if (strncmp($asv_serialized,"\x1F\x8B",2)===0)
					$asv_serialized = gzinflate(substr($asv_serialized, 10));
		
				if ($asv_serialized = unserialize($asv_serialized)) {
					if(is_array($asv_serialized)){
						extract($asv_serialized);
					}
				}
			}
			$description = str_replace('<', '< ', $description);
			$out[]= "<item><title>".$name."</title><link>"."http://".$siteurl."/file_download/".$a['id']."</link><version>".$version."</version><description>".htmlspecialchars($description)."</description></item>";
		}
	}
	
	$header = <<<EOF
<rss version="0.92"><channel><title>AmitVaria - textpattern plugins</title>
<link>http://www.amitvaria.com</link>
<description>AmitVaria Textpattern Plugin Feed</description>
EOF;
	
	$tail = "</channel></rss>";
	header("Content-Type: application/xml; charset=ISO-8859-1");  
	echo $header.join("",$out).$tail;
	return ;
}

# --- END PLUGIN CODE ---

?>
