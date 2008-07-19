<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_panels';

$plugin['version'] = '0.1';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Bring arrangable panels write screens to Textpattern';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 1; 


@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
if (@txpinterface == 'admin') {
	add_privs('asv_panels', '1');
	register_tab("extensions", "asv_panels", "Panels");
	register_callback("asv_panels", "asv_panels");
	
	//add tabs for each asv_panel
	
	//=============================================================
	//TESTING CODE ONLY
	//=============================================================
	add_privs('asv_test_panel', '1');
	register_tab("content", "asv_test_panel", "Test Panel");
	register_callback("asv_test_panel", "asv_test_panel");
	//=============================================================
}

function asv_panels_install($event, $step){
	//CREATE TABLE asv_panels
	if(safe_query("SHOW TABLES LIKE '".safe_pfx('asv_panels')."'"))
	{
		$version = mysql_get_server_info();

		//Use "ENGINE" if version of MySQL > (4.0.18 or 4.1.2)
		$tabletype = ( intval($version[0]) >= 5 || preg_match('#^4\.(0\.[2-9]|(1[89]))|(1\.[2-9])#',$version))
						? " ENGINE=MyISAM "
						: " TYPE=MyISAM ";

		$result = safe_query("CREATE TABLE IF NOT EXISTS `".PFX."asv_panels`(
			`name` varchar(64) NOT NULL default '',
			`design` text NOT NULL default '',
			 PRIMARY KEY  (`name`)
			 ) $tabletype PACK_KEYS=1 AUTO_INCREMENT=2 ");

		if($rs = safe_show('COLUMNS', 'asv_panels'))
		{
			$design_col = array('ID', 'name', 'design');
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
						safe_alter('asv_panels', "ADD `name` varchar(64) NOT NULL default ''");
						break;
					case 'design':
						safe_alter('asv_panels', "ADD `design` text NOT NULL default ''");
						break;
				}
			}
		}
	}
	//ADD COLUMN (PANEL) to textpattern
	
	asv_panels_list($event, $step);
}

function asv_panels_uninstall($event, $step){
	//REMOVE TABLE asv_panels
	safe_query("DROP TABLE IF EXISTS `".PFX."asv_panels`");
	//REMOVE COLUMN (PANEL) from textpattern
	
	asv_panels_list($event, $step);
}

function asv_test_panel($event, $step){
	//build a test array
	$data = 
		array(
			"header" => array(
				"test" => array(
					"title" => "whoot"
				)
			)
		);
		
	print_r($data);
					

	$panel = "3";
	pagetop("TEST PANEL");
	
	$rs = safe_row("*", "asv_panels", "name='$panel'");
	if($rs)
	{
		extract($rs);
		print_r(unserialize($design));
	}	
}

function asv_panels($event, $step){
	switch($step){
		case "create":
			asv_panels_create($event, $step);
			break;
		case "edit":
			asv_panels_edit("");
			break;
		case "save":
			asv_panels_save($event,$step);
			break;
		case "install":
			asv_panels_install($event, $step);
			break;
		case "uninstall":
			asv_panels_uninstall($event, $step);
			break;
		default:
			asv_panels_list($event, $step);			
			break;
	}
}

function asv_panels_list($event, $step)
{
	pagetop("Panels");
	echo <<<EOD
		<table id="list">
			<tr>
				<td><a href="index.php?event=asv_panels&step=install">install</a> | <a href="index.php?event=asv_panels&step=uninstall">unistall</a></td>
			</tr>
			<tr>
				<td><a href="index.php?event=asv_panels&step=create">create</a></td>
			</tr>
EOD;
	
	$rs = safe_rows_start("*", "asv_panels", "1=1");
	if($rs)
	{
		while($out = nextRow($rs))
		{
			echo "<tr><td><a href=\"index.php?event=asv_panels&step=create&name=".$out['name']."\">".$out['name']."</a></td></tr>";
		}
	}
	echo "</table>";		
}

function asv_panels_edit($message){
	pagetop("Edit", $message);
	
}

function asv_panels_save($event, $step)
{	
	ob_clean();
	global $vars, $step, $essential_forms;

	extract(doSlash(gpsa(array('savenew', 'name', 'design', 'oldname'))));
	$name = doSlash(trim(preg_replace('/[<>&"\']/', '', gps('name'))));

	if (!$name)
	{
		$step = "edit";
		exit($message);
		return;
	}


	if ($savenew)
	{
		$exists = safe_field('name', 'asv_panels', "name = '$name'");

		if ($exists)
		{
			$step = 'edit';
			$message = gTxt('Panel already exists', array('{name}' => $name));
			
			exit($message);
			return;
		}

		safe_insert('asv_panels', "name = '$name', design = '$design'");

		update_lastmod();

		$message = gTxt('Panel created', array('{name}' => $name));
		
		exit($message);
		return;
	}

	safe_update('asv_panels', "name = '$name', design = '$design'", "name = '$oldname'");

	update_lastmod();

	$message = gTxt('Panel edited', array('{name}' => $name));

	exit($message);
	return;
}


function asv_panels_js($default){
$savenew = " oldname: \"$default\",";
if($default=="")
	$savenew = " savenew: \"1\",";

echo <<<EOD
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="http://dev.jquery.com/view/tags/ui/latest/ui/ui.core.js"></script>
	<script src="http://dev.jquery.com/view/tags/ui/latest/ui/ui.sortable.js"></script>
			
	<script>
	(function($){
		var updateUpDown = function(sortable){
			$('dl:not(.ui-sortable-helper)', sortable)
				.removeClass('first').removeClass('last')
				.find('.up, .down').removeClass('disabled').end()
				.filter(':first').addClass('first').find('.up').addClass('disabled').end().end()
				.filter(':last').addClass('last').find('.down').addClass('disabled').end().end();
		};
		
		var moveUpDown = function(){
			var link = $(this),
				dl = link.parents('dl'),
				prev = dl.prev('dl'),
				next = dl.next('dl');
		
			if(link.is('.up') && prev.length > 0)
				dl.insertBefore(prev);
		
			if(link.is('.down') && next.length > 0)
				dl.insertAfter(next);
		
			updateUpDown(dl.parent());
		};
		
		var addItem = function(){
			var sortable = $(this).parents('.ui-sortable');
			var options = '<span class="options"><a class="up">up</a><a class="down">down</a></span>';
			var tpl = '<dl class="sort"><dt>{name}' + options + '</dt><dd>{desc}</dd></dl>';
			var html = tpl.replace(/{name}/g, 'Dynamic name :D').replace(/{desc}/g, 'Description');
		
			sortable.append(html).sortable('refresh').find('a.up, a.down').bind('click', moveUpDown);
			updateUpDown(sortable);
		};
		
		var emptyTrashCan = function(item){
			item.remove();
		};
		
		var sortableChange = function(e, ui){
			if(ui.sender){
				var w = ui.element.width();
				ui.placeholder.width(w);
				ui.helper.css("width",ui.element.children().width());
			}
		};
		
		var sortableUpdate = function(e, ui){
			if(ui.element[0].id == 'trashcan'){
				emptyTrashCan(ui.item);
			} else {
				updateUpDown(ui.element[0]);
				if(ui.sender)
					updateUpDown(ui.sender[0]);
			}
		};
		
		var savePage = function(){
			var data = new Array();			
			$("#container dl").each(function(i){
				var item = new Array(this.id);
				$(this + " dd").each(function(j){
					item[$(this).attr("class")] = $(this + " input").val();
				});
				if (typeof data[$("#body").parent().attr("id")] == 'undefined') // Any scope
					data[$("#body").parent().attr("id")] = new Array();
				data[$("#body").parent().attr("id")].push(item);				
			});
			alert(js_array_to_php_array(data));
			
			$.post(
					"index.php?event=asv_panels&step=save",
					{
EOD;

echo $savenew;

echo <<<EOF
name:$("#name").val(), design: data },
					function(txt){
						alert( txt);
					}
				);
				
		};
		
		$(document).ready(function(){
			var els = ['#header', '#content', '#sidebar', '#footer', '#trashcan', '#fields'];
			var \$els = $(els.toString());
			
			/*$('h2', \$els.slice(0,-1)).append('<span class="options"><a class="add">add</a></span>');*/
			/*$('dt', \$els).append('<span class="options"><a class="up">up</a><a class="down">down</a></span>');*/
			
			$('a.add').bind('click', addItem);
			$('a.up, a.down').bind('click', moveUpDown);
			$('#save').bind('click', savePage);
			
			/*\$els.each(function(){
				updateUpDown(this);
			});*/
			
			\$els.sortable({
				items: '> dl',
				handle: 'dt',
				cursor: 'move',
				//cursorAt: { top: 2, left: 2 },
				//opacity: 0.8,
				//helper: 'clone',
				appendTo: 'body',
				//placeholder: 'clone',
				//placeholder: 'placeholder',
				connectWith: els,
				start: function(e,ui) {
					ui.helper.css("width", ui.item.width());
				},
				change: sortableChange,
				update: sortableUpdate
			});
		});
		
		$(window).bind('load',function(){
			setTimeout(function(){
				$('#overlay').fadeOut(function(){
					$('body').css('overflow', 'auto');
				});
			}, 750);
		});
	})(jQuery);

	function js_array_to_php_array (a)
	{
    var a_php = "";
    var total = 0;
    for (var key in a)
    {
        ++ total;
        a_php = a_php + "s:" +
                String(key).length + ":\"" + String(key) + "\";s:" +
                String(a[key]).length + ":\"" + String(a[key]) + "\";";
    }
    a_php = "a:" + total + ":{" + a_php + "}";
    return a_php;

	}

	</script>
EOF;
}

function asv_panels_create($event, $step)
{	
	pagetop("Panels");
	
	$gps_name = gps("name");
	if($gps_name)
	{
		$rs = safe_row("*", "asv_panels", "name='$gps_name'");
		
		if($rs)
		{
			extract($rs);
		}
	}
	
	if(isset($name))
	{
		asv_panels_js($name);
	}
	else
	{
		asv_panels_js("");
	}
	
	asv_panels_style();
		
	echo "<table id=\"list\"><tr><td>";
	
	if(isset($name))
	{
		echo "<input id=\"name\" type=\"text\" value=\"$name\" />";
	}
	else
	{
		echo "<input id=\"name\" type=\"text\" />";
	}
	
	echo "</td></tr><tr><td>";
	
	if(isset($design))
	{
		echo $design;
	}
	else
	{
		asv_panels_default_panel();
	}
	
	echo "</td></tr><tr><td><button id=\"save\">save</button></table>";
}
function asv_panels_default_panel(){
echo <<<EOD
	<div id="container">
		<div id="header" class="ui-sortable">
			<h2>Header</h2>
		</div>
		
		<div id="content" class="ui-sortable">
			<h2>Content</h2>
		</div>
		
		<div id="sidebar" class="ui-sortable">
			<h2>Sidebar</h2>
		</div>
		
		<div class="clear"></div>
		
		<div id="footer" class="ui-sortable">
			<h2>Footer</h2>
		</div>
		
		<div class="clear"></div>

		<div id="fields" class="ui-sortable">
			<h2>Available Fields</h2>
			<dl id="title" class="sort">
				<dt>title</dt>
				<dd><input type="checkbox" />Include label</dd>
			</dl>
			<dl id="body" class="sort">
				<dt>body</dt>
				<dd><input type="checkbox" />Include label</dd>
			</dl>
			<dl id="excerpt" class="sort"><dt>excerpt</dt></dl>
			<dl id="section" class="sort"><dt>section</dt></dl>
			<dl id="category1" class="sort"><dt>category1</dt></dl>
			<dl id="category2" class="sort"><dt>category2</dt></dl>
			<dl id="status" class="sort"><dt>status</dt></dl>
			<dl id="keywords" class="sort"><dt>keywords</dt></dl>
			<dl id="article_image" class="sort"><dt>article image</dt></dl>
			<dl id="url_only_title" class="sort"><dt>url-only title</dt></dl>
			<dl id="comments" class="sort"><dt>comments</dt></dl>
			<dl id="timestamp" class="sort"><dt>timestamp</dt></dl>
			<dl id="article_markup" class="sort"><dt>article markup</dt></dl>
			<dl id="excerpt_markup" class="sort"><dt>excerpt markup</dt></dl>
			<dl id="override_form" class="sort"><dt>override form</dt></dl>
		</div>
	</div>
EOD;
}

function asv_panels_style(){
echo <<<EOD
	<style>
		#list { background-color:#666; color:#FFF; font:11px/1.5 Arial, sans-serif; overflow:hidden;  }
		h1 { font-size:18px; margin:0 0 20px; }
		a { color:#FFF; }

		.clear { clear:both; font-size:1px; line-height:1px; }

		#overlay { background:#666; height:100%; left:0; position:absolute; top:0; width:100%; z-index:2000; }
		#overlay #preloader { background:url(loader_bg.gif) no-repeat; height:50px; left:50%; line-height:50px; margin:-25px 0 0 -25px; position:absolute; text-align:center; top:50%; width:50px; }
		#overlay #preloader img { margin:11px 0 0 0; vertical-align:middle; }

		.ui-sortable { background-color:#FFF; border:1px solid #555; color:#222; margin:0 15px 15px 0; padding:0 10px 10px; width:175px; }
		.ui-sortable h2 { background-color:#555; border-top:3px solid #666; color:#FFF; font-size:11px; margin:0 -10px 10px; line-height:2; padding:0 10px; }

		dl.sort { color:#222; margin:10px 0; }
		#uidemo dl.first { margin-top:0; }
		#uidemo dl.last { margin-bottom:0; }

		dl.sort dt { background-color:#666; color:#FFF; cursor:move; height:2em; line-height:2; padding:0 6px; position:relative; }
		dl.sort dd { background-color:#FFF; margin:0; padding:3px 6px; }

		.ui-sortable-helper { width:175px; }
		.placeholder { border:1px dashed #AAA; }

		span.options { cursor:default; font-size:1px; line-height:1px; position:absolute; }
		span.options a { background-color:#FFF; cursor:pointer; display:block; float:left; text-indent:-9000px; }

		.ui-sortable h2 span.options { right:10px; top:8px; width:30px; }
		.ui-sortable h2 span.options a { height:12px; width:30px; }

		dl.sort dt span.options { right:5px; top:5px; width:27px; }
		dl.sort dt span.options a { height:12px; width:12px; }
		dl.sort dt span.options a.up { margin-right:3px; }
		dl.sort dt span.options a.disabled { background-color:#555; cursor:default; }

		#container { float:left; }
		#header { width:638px; }
		#content { float:left; width:400px; }
		#sidebar { float:left; width:200px; }
		#footer { width:638px; }
	</style>
EOD;
}
# --- END PLUGIN CODE ---

?>
