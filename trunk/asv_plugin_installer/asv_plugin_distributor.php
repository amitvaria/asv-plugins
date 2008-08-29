<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_pluginfeed';

$plugin['version'] = '0.1';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Creates a feed that can be read by asv_plugininstaller';

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

if(gps('pluginfeed')==1)
{
	global $file_base_path, $siteurl;
	$out[] = '';
	$rs = safe_rows_start('id, filename', 'txp_file', "category='pluginfeed' order by filename");
	
	header("Content-Type: application/xml; charset=ISO-8859-1"); 
	echo '<rss version="0.92"><channel>';
	echo '<title>Amit Varia\'s Plugins</title>';
	echo '<link>http://www.amitvaria.com</link>';
	echo '<description>my plugins</description>';

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
			echo "<item><title>".$name."</title><link>".$siteurl."/files/".$a['filename']."</link><version>".$version."</version><description>".html_entity_decode($description)."</description></item>";
		}
	}

	echo "</channel></rss>";

	exit();

}

# --- END PLUGIN CODE ---

?>