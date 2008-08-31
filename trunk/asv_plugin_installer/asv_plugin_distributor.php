<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_plugin_repository';

$plugin['version'] = '1.0';
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
h1. asv_plugin_repository

h2. creates repositories of plugins for you to offer textpattern users

<a href="?event=asv_plugin_repo&step=install_plugin">Install</a> | <a href="?event=asv_plugin_repo&step=uninstall_plugin">Uninstall</a>

p. Many plugin developers have created repositories where Textpattern users can access their plugins, but it's always required users to reach out and find the plugins. This plugin is one half a larger solution to bring plugin updates to the backend. 

p. On install a tab is created under extensions called "Plugin Repository". Here developers can upload their plugins (serialized). A plugin feeds (plugins.rss) is created in the root of the Textpattern site. You can distribute the link to the plugins.rss file to users with the asv_pluginstaller installed. They can add the feed and access your plugins from their Textpattern backend.

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
if (@txpinterface == 'admin') {
  add_privs('asv_plugin_repo','1');
  // Add a new tab under 'extensions' called 'PlugInstaller', for the 'asv_pluginstall' event
  register_tab("extensions", "asv_plugin_repo", "Plugin Repository");
  // 'asv_pluginstaller' will be called to handle the 'asv_pluginstall' event
  register_callback("asv_plugin_repo", "asv_plugin_repo");
}

function asv_plugin_repo($event, $step){
	
	global $prefs;//$asv_repo_name,$asv_repo_link,$asv_repo_description, $siteurl;
	
	extract($prefs);

	//-------------------------------------
	//save preferences
	if ($step=="save_prefs") {
			safe_update("txp_prefs", "val = '".ps('asv_repo_name')."'","name = 'asv_repo_name' and prefs_id ='1'");
			safe_update("txp_prefs", "val = '".ps('asv_repo_link')."'","name = 'asv_repo_link' and prefs_id ='1'");
			safe_update("txp_prefs", "val = '".ps('asv_repo_description')."'","name = 'asv_repo_description' and prefs_id ='1'");
			$asv_repo_name = fetch('val', 'txp_prefs', 'name', 'asv_repo_name');
			$asv_repo_link = fetch('val', 'txp_prefs', 'name', 'asv_repo_link');
			$asv_repo_description = fetch('val', 'txp_prefs', 'name', 'asv_repo_description');

	}
	//-------------------------------------
	
	//-------------------------------------
	//add code
	if($step=="addcode")
	{
	   	$plugin = ps('code');
	
		$plugin = preg_replace('@.*\$plugin=\'([\w=+/]+)\'.*@s', '$1', $plugin);
		$plugin = preg_replace('/^#.*$/m', '', $plugin);

	    if(trim($plugin)) {
	    	
	    		$plugin_code = $plugin;
	
	            $plugin = base64_decode($plugin);
	            if (strncmp($plugin,"\x1F\x8B",2)===0)
	                    $plugin = gzinflate(substr($plugin, 10));
	
	            if ($plugin = unserialize($plugin)) {
	
	                    if(is_array($plugin)){
	
	                            extract($plugin);
	
	                            $exists = fetch('name','asv_plugin_repo','name',$name);
	
	                            if ($exists) {
	                                    $rs = safe_update(
	                                       "asv_plugin_repo",
	                                            "author       = '".doSlash($author)."',
	                                            author_uri   = '".doSlash($author_uri)."',
	                                            version      = '".doSlash($version)."',
	                                            description  = '".doSlash($description)."',                                         
	                                            code         = '".doSlash($plugin_code)."'",
	                                            "name        = '".doSlash($name)."'"
	                                    );
	
	                            } else {
	
	                                    $rs = safe_insert(
	                                       "asv_plugin_repo",										   
	                                       "name         = '".doSlash($name)."',
	                                            author       = '".doSlash($author)."',
	                                            author_uri   = '".doSlash($author_uri)."',
	                                            version      = '".doSlash($version)."',
	                                            description  = '".doSlash($description)."',
	                                            code         = '".doSlash($plugin_code)."'"
	                                    );
	                            }
	
	                            if ($rs and $code)
	                            {
	                                    $message = gTxt('plugin added', array('{name}' => htmlspecialchars($name)));
										asv_plugin_repo_create_rss();
	                            }
	
	                            else
	                            {
	                                    $message = gTxt('plugin failed to be added', array('{name}' => htmlspecialchars($name)));
	
	                            }
	                    }
	            }
	
	            else
	            {
	                    $message = gTxt('bad_plugin_code');
	            }

		}
	}
	
	//-------------------------------------
	//remove plugin
	if($step == "delete_plugin"){
		$name = gps('name');
		$deleted = safe_delete("asv_plugin_repo", "name = '".doSlash($name)."'");
		if($deleted)
		{
			$message = gTxt('plugin removed', array('{name}' => htmlspecialchars($name)));
			asv_plugin_repo_create_rss();
		}
		else{
			$message = gTxt('unable to remove plugin', array('{name}' => htmlspecialchars($name)));
		}
	}
	//-------------------------------------
	//generate the rss
	if ($step == "create_rss") {
		asv_plugin_repo_create_rss();
		$message = gTxt('Feed Refreshed');
	}
	//-------------------------------------
	
	
	//-------------------------------------
	//install the plugininstaller
	if ($step == "install_plugin") {
		asv_plugin_repo_install($event, $step);
	}
	//-------------------------------------
	
	//-------------------------------------
	//uninstall the pluginisntaller
	if ($step == "uninstall_plugin") {
		asv_plugin_repo_uninstall($event, $step);
	}
	//-------------------------------------
	
	$message = (isset($message))? $message : '';
	
	pagetop("Plugin Repository", $message);

	//setup the output array
	$out = array();
	
	//start the table
	$out[] = startTable("list","","edit-table").n;
	
	//setup some common styles and vars for display
	$colspan = 3;
	$tdlatts = ' style="vertical-align:middle;"';
	$tdatts = ' style="text-align:center;vertical-align:middle;"';
	$preflab = ' style="text-align:right;vertical-align:middle"';	
	$tdcsatts = $tdatts.' colspan="3"';
	$tdcsratts = $preflab.' colspan="3"';
	$tratts="";		
	
	//show the preferences part
	$out[] = 
	tr(tda(tag("Plugin Feed Preferences",'h1'), ' colspan="'.$colspan.'" style="text-align:center;background:#1f1f1f;color:#f1f1f1;padding: 10px 0 0;margin:0;"')).
	tr(tda('<a href="?event=asv_plugin_repo&step=create_rss">'.gTxt('Refresh RSS Feed').'</a> | <a href="/plugins.rss">'.gTxt('View RSS Feed').'</a>',$tdcsatts)).
	form(
		tr(tda("Feed Title", $preflab).tdcs(fInput("text","asv_repo_name", $asv_repo_name,'', '', '', '', '', ''),$colspan-1)).
		tr(tda("Feed Link", $preflab).tdcs(fInput("text","asv_repo_link", $asv_repo_link,'', '', '', '', '', ''),$colspan-1)).
		tr(tda("Feed Description", $preflab).tdcs(fInput("text","asv_repo_description", $asv_repo_description,'', '', '', '', '', ''),$colspan-1)).
		tr(tda(fInput("submit","save",gTxt("save"),"publish").eInput("asv_plugin_repo").sInput('save_prefs'),$tdcsratts)));

    $out[] = form(
		tr(tda(tag("Plugin Repository",'h1'), ' colspan="'.$colspan.'" style="text-align:center;background:#1f1f1f;color:#f1f1f1;padding: 10px 0 0;margin:0;"')).
		tr(tdcs(text_area('code','100','400'), $colspan)).
		tr(tda(fInput("submit","add_code",gTxt("Add"),"publish").eInput("asv_plugin_repo").sInput('addcode'),$tdcsratts)));
	
	//show the repositories	
	$out[] = tr(
							tda(strong("Plugin Name"), $tdlatts).
							tda(strong("Version"), $tdatts).
							tda('&nbsp;', $tdatts)
						).n;
						
	$rs = safe_rows("*", "asv_plugin_repo", "1=1");
	
	foreach($rs as $a){
		$out[] = tr(
					tda($a['name'], $tdlatts).
					tda($a['version'], $tdatts).
					tda('<a href="?event=asv_plugin_repo&step=delete_plugin&name='.$a['name'].'">delete</a>', $tdatts)
				, $tratts).n;
	}	

	$out[] = endTable().n;

	echo implode('', $out);
}

function asv_plugin_repo_install($event, $step){
	//setup prefs
	set_pref("asv_repo_name", "repository", "admin", "2");
	set_pref("asv_repo_link", $siteurl, "admin", "2");
	set_pref("asv_repo_description", "my plugin repository", "admin", "2");
	
	//CREATE TABLE asv_panels
	if(safe_query("SHOW TABLES LIKE '".safe_pfx('asv_plugin_repo')."'"))
	{
		$version = mysql_get_server_info();

		//Use "ENGINE" if version of MySQL > (4.0.18 or 4.1.2)
		$tabletype = ( intval($version[0]) >= 5 || preg_match('#^4\.(0\.[2-9]|(1[89]))|(1\.[2-9])#',$version))
						? " ENGINE=MyISAM "
						: " TYPE=MyISAM ";

		$result = safe_query("CREATE TABLE IF NOT EXISTS `".PFX."asv_plugin_repo`(
			`name` varchar(64) NOT NULL default '',
			`author` varchar(128) NOT NULL default '',
			`author_uri` varchar(128) NOT NULL default '',
			`description` text NOT NULL default '',
			`version` varchar(10) NOT NULL default '',
			`code` text NOT NULL default '',
			 PRIMARY KEY  (`name`)
			 ) $tabletype PACK_KEYS=1 AUTO_INCREMENT=2 ");

		if($rs = safe_show('COLUMNS', 'asv_plugin_repo'))
		{
			$design_col = array('name', 'author', 'author_uri', 'description', 'version', 'code');
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
					case 'name':
						safe_alter('asv_plugin_repo', "ADD `name` varchar(64) NOT NULL default ''");
						break;
					case 'author':
						safe_alter('asv_plugin_repo', "ADD `author` varchar(128) NOT NULL default ''");
						break;
					case 'author_uri':
						safe_alter('asv_plugin_repo', "ADD `author_uri` varchar(128) NOT NULL default ''");
						break;
					case 'description':
						safe_alter('asv_plugin_repo', "ADD `description` text NOT NULL default ''");
						break;
					case 'version':
						safe_alter('asv_plugin_repo', "ADD `version` varchar(10) NOT NULL default ''");
						break;
					case 'code':
						safe_alter('asv_plugin_repo', "ADD `code` text NOT NULL default ''");
						break;
				}
			}
		}		
	}
}

function asv_plugin_repo_uninstall($event, $step){
	//REMOVE TABLE asv_plugin_sites
	safe_query("DROP TABLE IF EXISTS `".PFX."asv_plugin_repo`");
}

function asv_plugin_repo_create_rss(){

	global $file_base_path, $siteurl, $txpath, $prefs,$asv_repo_name,$asv_repo_link,$asv_repo_description;
	$out[] = '';
	
	$rs = safe_rows("*", "asv_plugin_repo", "1=1 ORDER BY name");
	
	$out[] = header("Content-Type: application/xml; charset=ISO-8859-1"); 
	$out[] =  '<rss version="0.92"><channel>';
	$out[] =  '<txpfeed version="1.0" />';
	$out[] =  '<title>'.$asv_repo_name.'</title>';
	$out[] =  '<link>'.$asv_repo_link.'</link>';
	$out[] =  '<description>'.$asv_repo_description.'</description>';

	foreach($rs as $a){
		extract($a);
		$out[] =  "<item><title>".$name."</title><link>http://".$siteurl."?asv_plugin_repo_code=".$name."</link><version>".$version."</version><description>".html_entity_decode($description)."</description></item>";
	}
	
	$out[] =  "</channel></rss>";
	
	$fh = fopen($txpath.'../plugins.rss', 'w') or die("can't open file");
	fwrite($fh, implode('', $out));

	fclose($fh);
	
	//echo implode('', $out);
	
	$out=array();
	
	header("Location: index.php?event=asv_plugin_repo");

}

if(gps('asv_plugin_repo_code')!=""){
	$asv_plugin_code = safe_row("code", "asv_plugin_repo", "name = '".doSlash(gps('asv_plugin_repo_code'))."'");
	echo $asv_plugin_code['code'];
	exit();
}

# --- END PLUGIN CODE ---

?>