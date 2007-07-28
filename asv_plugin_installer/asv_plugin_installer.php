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
h1. help!

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
	global $prefs,$rss_skip_preview,$rss_activate, $asv_plugin_source;

	if (!isset($rss_skip_preview)) {
		$rs = set_pref("rss_skip_preview", 0, "admin", "2", "yesnoradio");
	}

	if (!isset($rss_activate)) {
		$rs = set_pref("rss_activate", 0, "admin", "2", "yesnoradio");
	}
	
	if (!isset($asv_plugin_source)) {
		$rs = set_pref("asv_plugin_source", 'http://www.wilshireone.com/?pluginfeed=1', "admin", "2");
		$asv_plugin_source = 'http://www.wilshireone.com/?pluginfeed=1';
	}

	if (ps("save")) {
			pagetop("PlugInstaller", "Preferences Saved");
			safe_update("txp_prefs", "val = '".ps('rss_skip_preview')."'","name = 'rss_skip_preview' and prefs_id ='1'");
			safe_update("txp_prefs", "val = '".ps('rss_activate')."'","name = 'rss_activate' and prefs_id ='1'");
			safe_update("txp_prefs", "val = '".ps('asv_plugin_source')."'","name = 'asv_plugin_source' and prefs_id ='1'");
			header("Location: index.php?event=asv_pluginstall");
			exit;
	}

	if (ps("install_new")) {
		$contents =  rss_fetchURL(ps("plugin"));
		if ($contents) {
			$plugin64 = preg_replace('@.*\$plugin=\'([\w=+/]+)\'.*@s', '$1', $contents);
			$_POST['plugin'] = $plugin64;
			$_POST['plugin64'] = $plugin64;
			$event = "plugin";
			include(txpath . '/include/txp_plugin.php');
			if ($rss_skip_preview) {
				$res = plugin_install();
				if ($rss_activate) safe_update('txp_plugin', "status = 1", "name = '".doSlash(ps('name'))."'");
				header("Location: index.php?event=asv_pluginstall");
			}
			exit;
		}
	}

	if ($step == "switch_status") {
		extract(gpsa(array('name', 'status')));
		$change = ($status) ? 0 : 1;
		safe_update('txp_plugin', "status = $change", "name = '".doSlash($name)."'");
	}

	if ($step == "plugin_delete") {
		$name = doSlash(ps('plugtitle'));
		safe_delete('txp_plugin', "name = '$name'");
	}

  pagetop("PlugInstaller");

	if (ps("install_new") && !$contents) {
			echo graf(strong("Could not connect to wilshire|one (asv'd)."), ' style="text-align:center;"');
	}

	$magfiles = txpath . '/magpie/rss_fetch.inc';

	if (file_exists($magfiles)) {
		require_once($magfiles);

		$MAGPIE_CACHE_ON = "1";
		$MAGPIE_CACHE_DIR = "cache";
		$MAGPIE_CACHE_AGE = "1800";
		$MAGPIE_CACHE_FRESH_ONLY = "0";

		//		$rss = fetch_rss("http://www.wilshireone.com/?pluginfeed=1");
		$rss = fetch_rss($asv_plugin_source);
		
		//dmp($rss);

		if ($rss) {

			$myplugs = safe_rows("*, md5(code) as md5", "txp_plugin", "1 order by name");

			$tdlatts = ' style="vertical-align:middle;"';
			$tdatts = ' style="text-align:center;vertical-align:middle;"';
			$colspan = 8;

			$out = array();
			$out[] = startTable("list","","edit-table").n;

			$out[] = tr(tda(tag("wilshire|one PlugInstaller (asv'd)",'h1'), ' colspan="'.$colspan.'" style="text-align:center;background:#1f1f1f;color:#f1f1f1;padding: 10px 0 0;margin:0;"'));

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

			foreach(array_slice($rss->items,0) as $plug) {
				$installed = 0;
				$modified = 0;
				extract($plug);

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
						tda(($isInstalled) ? rss_status_link($myplug['status'], $title, yes_no($myplug['status'])) : "&nbsp;", $tdatts).
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

			$preflab = ' style="text-align:right;vertical-align:middle"';
					

			$out[] = form(
				tr(tda(tag("wilshire|one PlugInstaller (asv'd) Preferences",'h1'), ' colspan="'.$colspan.'" style="text-align:center;background:#1f1f1f;color:#f1f1f1;padding: 10px 0 0;margin:0;"')).
				tr(tda(tag('Source: ', 'label').fInput('text', 'asv_plugin_source', $asv_plugin_source, 'edit', '', '', 30))).
					tr(tda("Skip Install Preview:", $preflab).tdcs(yesnoRadio("rss_skip_preview", $rss_skip_preview),8)).
				tr(tda("Active on Install:", $preflab).tdcs(yesnoRadio("rss_activate", $rss_activate),8)).
				tr(tdcs(fInput("submit","save",gTxt("save_button"),"publish").eInput("asv_pluginstall").sInput('saveprefs'),8)).
				tr(tdcs(graf(href("Visit wilshire|one Textpattern Plugins",'http://www.wilshireone.com/textpattern-plugins'), ' style="text-align:center;"'),8)));

			$out[] = endTable().n;


			echo implode('', $out);
		} else {
			echo graf(strong("Could not connect to wilshire|one."), ' style="text-align:center;"');
		}
	} else {
		echo graf(strong("Magpie Files Not Installed.".br."Place files in /textpattern/magpie"), ' style="text-align:center;"');
	}
}

// -------------------------------------------------------------

function rss_fetchURL( $url ) {
/*
   $url_parsed = parse_url($url);
   $host = $url_parsed["host"];
   $port = 80;
   if ((isset($url_parsed["port"])) && $url_parsed["port"]!=0)
       $port = $url_parsed["port"];
   $path = $url_parsed["path"];
   if ((isset($url_parsed["query"])) && $url_parsed["query"] != "")
       $path .= "?".$url_parsed["query"];

   $out = "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n";

   $fp = fsockopen($host, $port, $errno, $errstr, 20);

   fwrite($fp, $out);
   $body = false;
   $in = "";

   stream_set_timeout($fp, 5);
   $info = stream_get_meta_data($fp);
   while (!feof($fp) && !$info['timed_out']) {
       $s = fgets($fp, 1024);
       if ( $body )
           $in .= $s;
       if ( $s == "\r\n" )
           $body = true;
   }

   fclose($fp);

   return $in;
   */
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

function rss_status_link($status,$name,$linktext) {
	$out = '<a href="index.php?';
	$out .= 'event=asv_pluginstall&#38;step=switch_status&#38;status='.
		$status.'&#38;name='.urlencode($name).'"';
	$out .= '>'.$linktext.'</a>';
	return $out;
}



# --- END PLUGIN CODE ---

?>
