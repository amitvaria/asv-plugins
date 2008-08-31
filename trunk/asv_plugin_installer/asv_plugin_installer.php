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
$plugin['type'] = 1; 


@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---
h1. asv_pluginstaller

h2. get plugins!

p. <a href="?event=asv_pluginstall&step=install_plugin">Install</a> | <a href="?event=asv_pluginstall&step=uninstall_plugin">Uninstall</a>

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
if (@txpinterface == 'admin') {
  add_privs('asv_pluginstall','1');
  // Add a new tab under 'extensions' called 'PlugInstaller', for the 'asv_pluginstall' event
  register_tab("extensions", "asv_pluginstall", "PlugInstaller");
  // 'asv_pluginstaller' will be called to handle the 'asv_pluginstall' event
  register_callback("asv_pluginstaller", "asv_pluginstall");
}

function asv_pluginstaller($event, $step) {
	global $prefs,$asv_skip_preview,$asv_activate, $asv_plugin_simplepie, $txpath;

	//-------------------------------------
	//setup prefs if not already installed
	if (!isset($asv_skip_preview)) {
		$rs = set_pref("asv_skip_preview", 0, "admin", "2", "yesnoradio");
		extract(get_prefs());
	}

	if (!isset($asv_activate)) {
		$rs = set_pref("asv_activate", 0, "admin", "2", "yesnoradio");
		extract(get_prefs());
	}
	
	if (!isset($asv_simplepie)) {
		$rs = set_pref("asv_plugin_simplepie", txpath.'/lib/simplepie.inc', "admin", "2");
		extract(get_prefs());
	}
	//-------------------------------------
	
	//-------------------------------------
	//save preferences
	if (ps("save")) {
			safe_update("txp_prefs", "val = '".ps('asv_skip_preview')."'","name = 'asv_skip_preview' and prefs_id ='1'");
			safe_update("txp_prefs", "val = '".ps('asv_activate')."'","name = 'asv_activate' and prefs_id ='1'");
			safe_update("txp_prefs", "val = '".ps('asv_plugin_simplepie')."'","name = 'asv_plugin_simplepie' and prefs_id ='1'");
			extract(get_prefs());
	}
	//-------------------------------------
	
	//-------------------------------------
	//install a new plugins	
	if (ps("install_new")) {
		$contents =  asv_fetchURL(ps("plugin"));
		if ($contents) {
			$plugin64 = preg_replace('@.*\$plugin=\'([\w=+/]+)\'.*@s', '$1', $contents);
			$_POST['plugin'] = $plugin64;
			$_POST['plugin64'] = $plugin64;
			$event = "plugin";
			include(txpath . '/include/txp_plugin.php');
			if ($asv_skip_preview) {
				$res = plugin_install();
				if ($asv_activate) safe_update('txp_plugin', "status = 1", "name = '".doSlash(ps('name'))."'");
				header("Location: index.php?event=asv_pluginstall");
			}
			exit;
		}
	}
	//-------------------------------------
	
	//-------------------------------------
	//add a new repository
	if ($step=="add_repo") {
		
		if(ps('asv_distributor_url')){
			//Get SimplePie
			require_once($asv_plugin_simplepie);
			
			$url = doSlash(ps("asv_distributor_url"));
			
			//Create and setup SimplePie Instance
			$thefeed = new SimplePie();
			$thefeed->set_feed_url($url);
			$thefeed->enable_cache(false);
			$thefeed->handle_content_type();
			
			//Get the feed
			$success = $thefeed->init();
			
			if($success && $thefeed->get_title()){
				//get the txp feed version
				$feed_version = $thefeed->get_channel_tags('', 'txpfeed');
					if($feed_version[0]['attribs']['']['version'] >= 1){
	
					$title = doSlash($thefeed->get_title());
					$exists = fetch('name', 'asv_plugin_sites', 'url', $url);
					if($exists){
						safe_update("asv_plugin_sites",
										"name = '$title',
										 url ='$url'",
										 "name = '$exists'");					
					}
					else{
						safe_insert("asv_plugin_sites",
										"name = '$title',
										 url ='$url'");					
					}
				}
				else{
					$message = gTxt('Not a txp plugin feed');
				}
			}
			else{
				$message = "feed not found";
			}
		}
		else{
			$message = "url required";
		}
	}
	//-------------------------------------
	//delete repository
	if ($step == "delete_repo") {
		if(gps('name')){
			safe_delete("asv_plugin_sites", "name = '".doSlash(gps('name'))."'");
		}
	}
	//-------------------------------------
	//refresh repository
	if ($step == "refresh_repo") {
		if(gps('name')){
			$url=fetch('url','asv_plugin_sites','name',gps('name'));
			$filename = txpath.'/cache/'.sha1($url).'.spc';
			if(file_exists($filename)){
				unlink($filename);
				$message = gTxt('Refreshed', array('{name}' => htmlspecialchars(gps('name'))));
			}
			else{
				$message = gTxt('Unable to Refresh', array('{name}' => htmlspecialchars(gps('name'))));
			}
		}
	}
	//-------------------------------------

	//-------------------------------------
	//Change status
	if ($step == "switch_status") {
		extract(gpsa(array('name', 'status')));
		$change = ($status) ? 0 : 1;
		safe_update('txp_plugin', "status = $change", "name = '".doSlash($name)."'");
	}
	//-------------------------------------

	//-------------------------------------
	//delete the plugin
	if ($step == "plugin_delete") {
		$name = doSlash(ps('plugtitle'));		
		safe_delete('txp_plugin', "name = '$name'");
	}
	//-------------------------------------
	
	//-------------------------------------
	//install the plugininstaller
	if ($step == "install_plugin") {
		asv_plugin_installer_install($event, $step);
	}
	//-------------------------------------
	
	//-------------------------------------
	//uninstall the pluginisntaller
	if ($step == "uninstall_plugin") {
		asv_plugin_installer_uninstall($event, $step);
	}
	//-------------------------------------
	
	$message = (isset($message))? $message : '';
	
	pagetop("PlugInstaller", $message);

	if (ps("install_new") && ps("name") && !$contents) {
			echo graf(strong("Could not connect to ".ps("name")), ' style="text-align:center;"');
	}
	
	//setup the output array
	$out = array();
	
	//start the table
	$out[] = startTable("list","","edit-table").n;
	
	//setup some common styles and vars for display
	$colspan = 8;
	$tdlatts = ' style="vertical-align:middle;"';
	$tdatts = ' style="text-align:center;vertical-align:middle;"';
	$preflab = ' style="text-align:right;vertical-align:middle"';			
	
	//show the preferences part
	$out[] = form(
		tr(tda(tag("PlugInstaller Preferences",'h1'), ' colspan="'.$colspan.'" style="text-align:center;background:#1f1f1f;color:#f1f1f1;padding: 10px 0 0;margin:0;"')).
		tr(tda("Skip Install Preview:", $preflab).tdcs(yesnoRadio("asv_skip_preview", $asv_skip_preview),$colspan-1)).
		tr(tda("Active on Install:", $preflab).tdcs(yesnoRadio("asv_activate", $asv_activate),$colspan-1)).
		tr(tda("Simplepie Path:", $preflab).tdcs(fInput("text","asv_plugin_simplepie", $asv_plugin_simplepie,'', '', '', '', '', ''),$colspan-1)).

		tr(tdcs(fInput("submit","save",gTxt("save_button"),"publish").eInput("asv_pluginstall").sInput('saveprefs'),$colspan-1)));
	
	//show the repositories
	$out[] = tr(tda(tag("Repositories",'h1'), ' colspan="'.$colspan.'" style="text-align:center;background:#1f1f1f;color:#f1f1f1;padding: 10px 0 0;margin:0;"'));

	$out[] = form(
		tr(tdcs(fInput("text","asv_distributor_url", '','', '', '', '100%', '', ''),$colspan-1).
			tda(fInput("submit","add_url",gTxt("add"),"publish").eInput("asv_pluginstall").sInput('add_repo'))));
	
	$rs = safe_rows('*','asv_plugin_sites', '1=1');
	
	if (file_exists($asv_plugin_simplepie)) {
		require_once($asv_plugin_simplepie);	
	
		//Create and setup SimplePie Instance
		$thefeed = new SimplePie();
		$thefeed->enable_cache(true);
		$thefeed->set_cache_name_function('sha1');
		$thefeed->handle_content_type();

		foreach($rs as $a){
			
			$thefeed->set_feed_url($a['url']);
			$success = $thefeed->init();
			//$rss = fetch_rss($a['url']);
	
			if ($success) {
	
				$myplugs = safe_rows("*, md5(code) as md5", "txp_plugin", "1 order by name");
	
	
				$out[] = tr(tda(tag($a['name'].' (<a href="?event=asv_pluginstall&step=refresh_repo&name='.$a['name'].'">refresh</a>'.' | <a href="?event=asv_pluginstall&step=delete_repo&name='.$a['name'].'">delete</a>)','h1'), ' colspan="'.$colspan.'" style="text-align:center;background:#1f1f1f;color:#f1f1f1;padding: 10px 0 0;margin:0;"'));
	
				$out[] = tr(
							tda(strong("Plugin Name"), $tdlatts).
							tda(strong("Your Version"), $tdatts).
							tda(strong("Current Version"), $tdatts).
							tda(strong("Active?"), $tdatts).
							tda(strong("Help"), $tdatts).
							tda(strong("Status"), $tdatts).
							tda(strong("Add"), $tdatts).
							tda(strong("Remove"), $tdatts)
						).n;
	
				foreach ($thefeed->get_items() as $item) {
					$installed = 0;
					$modified = 0;
					
					$title = $item->get_title();
					$version_array = $item->get_item_tags('', 'version');
					$version = $version_array[0]['data'];
					$link = $item->get_link();
					
	
					foreach($myplugs as $myplug){
						if (array_search($title,$myplug)) {
							$installed=$myplug['version'];
							$modified = (strtolower($myplug['md5']) != strtolower($myplug['code_md5']));
							break;
						}
					}
	
					$tratts="";
					$isInstalled = ($installed != 0);
	
					if (!$isInstalled) {
						$install_status = "Not installed";
						$tratts = ' class="spam"';
					} else {
						$install_status = ($myplug['version'] == $version) ? "No Updates" : " Update Available!";
					}
	
					$instlab = (!$isInstalled) ? gTxt('install') : gTxt('update');
					$inststy = (!$isInstalled || $install_status == "No Updates") ? "smallerbox" : "publish";
	
					$help = ($isInstalled) ?
						'<a href="?event=plugin'.a.'step=plugin_help'.a.'name='.$title.'">'.gTxt('help').'</a>' :
						"&nbsp;";
	
					$out[] =
						tr(
							tda($title." ".($modified ? " (modified)" : ''), $tdlatts).
							tda(($isInstalled) ? $installed : "&nbsp;", $tdatts).
							tda($version, $tdatts).
							tda(($isInstalled) ? asv_status_link($myplug['status'], $title, yes_no($myplug['status'])) : "&nbsp;", $tdatts).
							tda($help, $tdatts).
							tda($install_status, $tdatts).
							tda(form(
								'<input type="hidden" name="plugin" value="'.$link.'" />'.n.
								'<input type="hidden" name="name" value="'.$title.'" />'.n.
										fInput("submit", "install_new", $instlab, $inststy).n.
										eInput("asv_pluginstall").sInput("plugin_verify").n
									), $tdatts).n.
									tda(($isInstalled) ? dLink('asv_pluginstall', 'plugin_delete', 'plugtitle', $title) : "&nbsp;", $tdatts).n
						, $tratts).n;
				}
		
			} else {
				echo graf(strong("Could not connect to ".$a['name']), ' style="text-align:center;"');
			}
		}
		
		$out[] = endTable().n;

		echo implode('', $out);
		
	} else {
		echo graf(strong("Magpie Files Not Installed.".br."Place files in /textpattern/magpie"), ' style="text-align:center;"');
	}
}

// -------------------------------------------------------------

function asv_fetchURL( $url ) {
   		// create a new curl resource
		$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

		// grab URL and pass it to the browser
		$data = curl_exec($ch);

		// close curl resource, and free up system resources
		curl_close($ch);

		return $data;
		
}

// -------------------------------------------------------------

function asv_status_link($status,$name,$linktext) {
	$out = '<a href="index.php?';
	$out .= 'event=asv_pluginstall&#38;step=switch_status&#38;status='.
		$status.'&#38;name='.urlencode($name).'"';
	$out .= '>'.$linktext.'</a>';
	return $out;
}

function asv_plugin_installer_install($event, $step){
	//CREATE TABLE asv_panels
	if(safe_query("SHOW TABLES LIKE '".safe_pfx('asv_plugin_sites')."'"))
	{
		$version = mysql_get_server_info();

		//Use "ENGINE" if version of MySQL > (4.0.18 or 4.1.2)
		$tabletype = ( intval($version[0]) >= 5 || preg_match('#^4\.(0\.[2-9]|(1[89]))|(1\.[2-9])#',$version))
						? " ENGINE=MyISAM "
						: " TYPE=MyISAM ";

		$result = safe_query("CREATE TABLE IF NOT EXISTS `".PFX."asv_plugin_sites`(
			`name` varchar(64) NOT NULL default '',
			`url` varchar(255) NOT NULL default '',
			 PRIMARY KEY  (`name`)
			 ) $tabletype PACK_KEYS=1 AUTO_INCREMENT=2 ");

		if($rs = safe_show('COLUMNS', 'asv_plugin_sites'))
		{
			$design_col = array('ID', 'name', 'url');
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
						safe_alter('asv_plugin_sites', "ADD `name` varchar(64) NOT NULL default ''");
						break;
					case 'design':
						safe_alter('asv_plugin_sites', "ADD `url` varchar(255) NOT NULL default ''");
						break;
				}
			}
		}		
	}
}

function asv_plugin_installer_uninstall($event, $step){
	//REMOVE TABLE asv_plugin_sites
	safe_query("DROP TABLE IF EXISTS `".PFX."asv_plugin_sites`");
}


# --- END PLUGIN CODE ---

?>
