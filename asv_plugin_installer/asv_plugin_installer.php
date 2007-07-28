<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_plugin_installer';

$plugin['version'] = '0.1';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Adaption of Rob Sable\'s installer';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 0; 


@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---
h1. help!

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
/*if(@txpinterface == 'admin') {
	add_privs('asv_plugin_installer','1,2');
	register_tab('extensions', 'asv_plugin_installer', 'Plugin Installer');
	register_callback('asv_plugin_installer', 'asv_plugin_installer');
}*/

function asv_plugin_installer($thing){
	/*pagetop("Plugin Installer");
	echo 
	form(
		startTable("list").n.t
		.tr(
			td(
				hed("Plugin Installer", "1").n.t
				.graf("Begin by adding plugin repositories. You can find repositories everywhere.").n
			)
		)
		.tr(
			td(
				hed("Your Repositories", "2")
				.graf("This will contain a list of repositories you have added")					
			).
			td(
				htmlPre(asv_list_plugins())
			)
		)

		.endTable()
	);*/
	
	
}

function asv_list_plugins(){
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
			$out[]= "<item><title>".$name."</title><link>".$siteurl."/downloads/".$a['id']."</link><version>".$version."</version><description>".html_entity_decode($description)."</description></item>";
		}
	}
	
	
	return join('', $out) ;
}

# --- END PLUGIN CODE ---

?>
