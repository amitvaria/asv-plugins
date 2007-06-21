<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_amazon2';

$plugin['version'] = '0.1';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Add Amazon items to your TXP article';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 


@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---
h1. asv_amazon2

_more help to come_

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
// asv_amazon2
// Designed around the need to include items from Amazon in your website
if('admin' == @txpinterface) 
{
	//register_tab('extensions', 'txp_my_admin_page', "my page name"); 
	//add_privs('txp_my_admin_page','1,2,3,4'); 
	register_callback('asv_amazon2', 'article');
}

function asv_amazon2 ($event, $step)
{
	$line = "<h3 class=\\\"plain\\\">";
	
	$line .= "<a href=\\\"#asv_amazon2\\\" onclick=\\\"toggleDisplay(\'asv_amazon2\'); return false;\\\">Amazon2</a>";

	$line .= "</h3>";
	
	$divHTML = "<p>This will be the new location of the amazon plugin!</p>";
	
	$line .= "<div id=\\\"asv_amazon2\\\" style=\\\"display: none;\\\">$divHTML</div>";


	$js = <<<EOF
<script language="javascript" type="text/javascript">
<!--
var loc = document.getElementById('article-col-1');
var brother = document.getElementById('textile_help');

loc.innerHTML = "$line" + loc.innerHTML;

function asv_onClick(elem){
	var dest = document.getElementById('body');
	dest.value += " n hello";
}

// -->
</script>
EOF;

		echo $js;

}


# --- END PLUGIN CODE ---

?>