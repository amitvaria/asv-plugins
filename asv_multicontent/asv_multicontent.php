<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_admin_ui';

$plugin['version'] = '0.0';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'jquery ui functionality to the backend';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 


@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---
first run

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

if (@txpinterface == 'admin')
{
	register_callback('asv_multicontent', 'article', '');
}

function asv_multicontent($event, $step='')
{
$js = <<<EOF
<script>
	$(document).ready(function(){
		$("#article-main").prepend("<a href='index.php?event=asv_image_form&bm=1&TB_iframe=true&height=600&width=750' title='Upload images and files' class='thickbox'>images</a>");
	});
</script>
<script type="text/javascript" src="http://www.amitvaria.com/txp2/plugins/jquery.js"></script>
<script type="text/javascript" src="http://www.amitvaria.com/txp2/plugins/thickbox.js"></script>
<link rel="stylesheet" href="http://www.amitvaria.com/txp2/plugins/thickbox.css" type="text/css" media="screen" />
EOF;

echo $js;
}

if (@txpinterface == 'admin') {
	add_privs('asv_image_form','1,2,3,4,5');
	register_callback("asv_image_form", "asv_image_form");
	register_callback("asv_insert_image", "image", "image_insert");
	
}

function asv_image_form($event, $step)
{
	extract(get_prefs());
	
	echo pagetop(gtxt('Upload Image'));
	
	$uploadform = upload_form(gTxt('upload_image'), 'upload', 'image_insert', 'image', '', $file_max_upload_size);
	$uploadform = str_replace('</form>', hInput('bm','1').hInput('asv_insert_image','1').'</form>', $uploadform);
	
	echo startTable('list').
			tr(td($uploadform)).
			endTable();
}

function asv_insert_image($event, $step)
{
	extract(get_prefs());
	$id = mysql_insert_id();

	
	if(ps('asv_insert_image')==1)
	{
		$ai_select = '';
		$siteurl = hu;
		$append =  "";
	$js = <<<EOF
	<script>
			$(document).ready(function(){
				$("#nav-primary").prepend('<p>add to article image:<select onchange="asv_insert_article_image(this);"><option value="simple">simple</option><option value="explicit">explicit</option></select></p>');
			});
			function asv_insert_article_image(item)
			{
				var type = $(item).val();
				if(type == "simple"){			
					window.parent.$('#article-image').val($('input:hidden[@name=id]').val());
				}
				else{
					window.parent.$('#article-image').val('$siteurl'+'$img_dir'+'/'+$('input:hidden[@name=id]').val()+'.jpg');
				}
				alert();
			}
			</script>
EOF;
		echo $js;
		}
	
}

# --- END PLUGIN CODE ---

?>
