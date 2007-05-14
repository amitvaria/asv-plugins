<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_write_plugin';

$plugin['version'] = '0.1';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Displays the write page';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 


@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---


# --- END PLUGIN HELP ---
<?php


}

# --- BEGIN PLUGIN CODE ---
function asv_write_img($event, $step){
	pagetop('NEW INTERFACE');
	
	$script = <<<EOF
<script type="text/javascript" src="/textpattern/js/jquery.js"></script>
<script type="text/javascript" src="/textpattern/js/jquery.history_remote.pack.js"></script>
<script type="text/javascript" src="/textpattern/js/jquery.tabs.pack.js"></script>


<script type="text/javascript">
	$(function() {

		$('#container-1').tabs();


	});
</script>

<style type="text/css"> 
   #container-1{ 
     width: 240px; 
   } 
   
   #container-1 li{
    border: thin black solid;
   }
 </style> 

<link rel="stylesheet" href="/textpattern/js/jquery.tabs.css" type="text/css" media="print, projection, screen">
<!-- Additional IE/Win specific style sheet (Conditional Comments) -->
<!--[if lte IE 7]>
<link rel="stylesheet" href="/textpattern/js/jquery.tabs-ie.css" type="text/css" media="projection, screen">
<![endif]-->

<div id="container-1">
	<ul>
		<li><a href="#fragment-1"><span>List</span></a></li>
		<li><a href="#fragment-2"><span>Thumbnails</span></a></li>
	</ul>
	<div id="fragment-1">
		<p>First tab is active by default:</p>
		<pre><code>$(&#039;#container&#039;).tabs();</code></pre>
	</div>
	<div id="fragment-2">
		Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
		Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
	</div>
</div>
EOF;

	echo n.n.$script;
	return;
}

if (@txpinterface == 'admin') {
	register_tab("extensions", "asv_write_img", "NEW INTERFACE");
	register_callback("asv_write_img", "asv_write_img");
}
# --- END PLUGIN CODE ---

?>
