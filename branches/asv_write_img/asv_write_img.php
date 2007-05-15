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
function asv_appendHead($content){
	$content .= "inserting additional information";
	return $content;
}

function asv_write_img($event, $step){
	ob_start('asv_appendHead');
	
	pagetop('NEW INTERFACE');
	
	ob_end_flush();
	
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
#container{ 
 width: 800px; 
 margin: 0 auto;
} 

#asv-main{
 float: right;
 width: 420px;
}
#asv_images{
width: 380px;
overflow: auto;
}
#asv_files{
width: 380px;
overflow: auto;
}
#asv_links{
width: 380px;
overflow: auto;
}
.clear{
 clear: both;
}
 </style> 

<link rel="stylesheet" href="/textpattern/js/jquery.tabs.css" type="text/css" media="print, projection, screen">
<!-- Additional IE/Win specific style sheet (Conditional Comments) -->
<!--[if lte IE 7]>
<link rel="stylesheet" href="/textpattern/js/jquery.tabs-ie.css" type="text/css" media="projection, screen">
<![endif]-->

<div id="container">
	<div id="asv-main">
		<fieldset>
			<legend>Articles</legend>
			<p>         <input type="text" id="title" name="Title" value="" class="edit" size="40" tabindex="1" /></p>
			<p><textarea id="body" name="Body" cols="55" rows="31" tabindex="2"></textarea></p>
		</fieldset>
	</div>
	<div id="asv-articles">
		<fieldset>
			<legend>Articles</legend>
			<table cellpadding="0" cellspacing="0" border="0" id="list" align="center" width="90%">

<tr>

	<th><a href="index.php?step=list&#38;event=list&#38;sort=id&#38;dir=asc">ID#</a></th>
	<th><a href="index.php?step=list&#38;event=list&#38;sort=posted&#38;dir=asc">Posted</a></th>
	<th><a href="index.php?step=list&#38;event=list&#38;sort=title&#38;dir=asc">Title</a></th>

	<th><a href="index.php?step=list&#38;event=list&#38;sort=section&#38;dir=asc">Section</a></th>
	<th class="articles_detail"><a href="index.php?step=list&#38;event=list&#38;sort=category1&#38;dir=asc">Category 1</a></th>
	<th class="articles_detail"><a href="index.php?step=list&#38;event=list&#38;sort=category2&#38;dir=asc">Category 2</a></th>
	<th><a href="index.php?step=list&#38;event=list&#38;sort=status&#38;dir=asc">Status</a></th>
	<th><a href="index.php?step=list&#38;event=list&#38;sort=author&#38;dir=asc">Author</a></th>
	<th class="articles_detail"><a href="index.php?step=list&#38;event=list&#38;sort=comments&#38;dir=asc">Comments</a></th><th>&#160;</th></tr>

<tr>
	<td><a href="?event=article&#38;step=edit&#38;ID=1">1</a>
<ul class="articles_detail">
	<li><a href="?event=article&#38;step=edit&#38;ID=1">Edit</a></li>
	<li><a href="http://127.0.0.1/article/1/first-post">View</a></li>
</ul></td>
	<td>13 May 2007 11:00 PM</td>
	<td><a href="?event=article&#38;step=edit&#38;ID=1">First Post</a></td>

	<td width="75"><span title="Article">article</span></td>
	<td width="100" class="articles_detail">&#160;</td>
	<td width="100" class="articles_detail">&#160;</td>
	<td width="50"><a href="http://127.0.0.1/article/1/first-post">Live</a></td>
	<td><span title="Amit Varia">variaas</span></td>
	<td width="50" class="articles_detail">
<ul>
	<li>On</li>

	<li><a href="index.php?event=discuss&#38;step=list&#38;search_method=parent&#38;crit=1">Manage</a> (1)</li>
</ul></td>
	<td><input type="checkbox" name="selected[]" value="1" /></td>
</tr>

<tr><td colspan="2" style="text-align: left; border: none;">
<input type="checkbox" name="cb_toggle_articles_detail" id="cb_toggle_articles_detail" value="1" class="checkbox" onclick="toggleClassRemember('articles_detail');" /> <label for="cb_toggle_articles_detail">Show Detail</label> <script type="text/javascript">
<!--
setClassRemember('articles_detail');addEvent(window, 'load', function(){setClassRemember('articles_detail');});
// -->
</script>
</td><td colspan="9" style="text-align: right; border: none;">Select: <input type="button" name="selall" value="All" class="smallerboxsp" title="select all" onclick="selectall();" /><input type="button" name="selnone" value="None" class="smallerboxsp" title="select none" onclick="deselectall();" /><input type="button" name="selrange" value="Range" class="smallerboxsp" title="select range" onclick="selectrange();" /><label for="withselected">With selected:</label>&#160;<select name="edit_method" class="list" id="withselected" onchange="poweredit(this); return false;">

	<option value="" selected="selected"></option>
	<option value="changesection">Change section</option>
	<option value="changecategory1">Change Category1</option>
	<option value="changecategory2">Change Category2</option>
	<option value="changestatus">Change status</option>
	<option value="changecomments">Change comments</option>

	<option value="changeauthor">Change author</option>
	<option value="delete">Delete</option>
</select>
<input type="hidden" name="event" value="list" />
<input type="hidden" name="step" value="list_multi_edit" />
<input type="hidden" name="page" value="1" />
<input type="submit" name="" value="Go" class="smallerbox" /></td></tr>

</table>
		</fieldset>
	</div>
	<div id="asv_images">
		<fieldset>
			<legend>Images</legend>
			
			<form class="upload-form" method="post" enctype="multipart/form-data" action="index.php">
<div>
<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
<input type="hidden" name="event" value="image" />
<input type="hidden" name="step" value="image_insert" />
<input type="hidden" name="id" value="" />
<input type="hidden" name="sort" value="" />

<input type="hidden" name="dir" value="" />
<input type="hidden" name="page" value="" />
<input type="hidden" name="search_method" value="" />
<input type="hidden" name="crit" value="" />
<p><label for="image-upload">Upload image</label>&#160;<a target="_blank" href="http://rpc.textpattern.com/help/?item=upload&#38;lang=en" onclick="popWin(this.href); return false;" class="pophelp">?</a>&#160;<input type="file" name="thefile" value="" class="edit" id="image-upload" />&#160;<input type="submit" name="" value="Upload" class="smallerbox" /></p>
</div>
</form>


<form action="index.php" method="post" style="margin: auto; text-align: center;"><p><label for="image-search">Search</label>&#160;<select id="image-search" name="search_method" class="list">
	<option value="id">ID#</option>
	<option value="name" selected="selected">Name</option>

	<option value="category">Category</option>
	<option value="author">Author</option>
</select>&#160;<input type="text" name="crit" value="" size="15" class="edit" /><input type="hidden" name="event" value="image" /><input type="hidden" name="step" value="image_list" /><input type="submit" name="search" value="Go" class="smallerbox" /></p></form>


<table cellpadding="0" cellspacing="0" border="0" id="list" align="center">

<tr>
	<th><a href="index.php?step=list&#38;event=image&#38;sort=id&#38;dir=asc">ID#</a></th><th>&#160;</th>
	<th><a href="index.php?step=list&#38;event=image&#38;sort=date&#38;dir=asc">Date</a></th>

	<th><a href="index.php?step=list&#38;event=image&#38;sort=name&#38;dir=asc">Name</a></th>
	<th><a href="index.php?step=list&#38;event=image&#38;sort=thumbnail&#38;dir=asc">Thumbnail</a></th><th>Tags</th>
	<th><a href="index.php?step=list&#38;event=image&#38;sort=category&#38;dir=asc">Category</a></th>
	<th><a href="index.php?step=list&#38;event=image&#38;sort=author&#38;dir=asc">Author</a></th><th>&#160;</th></tr>

<tr>
	<td width="20">1</td>

	<td width="35">
<ul>
	<li><a href="?event=image&#38;step=image_edit&#38;id=1&#38;sort=&#38;dir=desc&#38;page=1&#38;search_method=&#38;crit=">Edit</a></li>
	<li><a href="http://127.0.0.1/images/1.gif">View</a></li>
</ul></td>
	<td width="75">22 Jul 2005 04:37 PM</td>
	<td width="75"><a href="?event=image&#38;step=image_edit&#38;id=1&#38;sort=&#38;dir=desc&#38;page=1&#38;search_method=&#38;crit=">divider.gif</a></td>
	<td width="75">No</td>

	<td width="85"><ul><li><a target="_blank" href="?event=tag&#38;tag_name=image&#38;id=1&#38;ext=.gif&#38;w=400&#38;h=1&#38;alt=&#38;caption=&#38;type=textile" onclick="popWin(this.href); return false;">Textile</a></li><li><a target="_blank" href="?event=tag&#38;tag_name=image&#38;id=1&#38;ext=.gif&#38;w=400&#38;h=1&#38;alt=&#38;caption=&#38;type=textpattern" onclick="popWin(this.href); return false;">Textpattern</a></li><li><a target="_blank" href="?event=tag&#38;tag_name=image&#38;id=1&#38;ext=.gif&#38;w=400&#38;h=1&#38;alt=&#38;caption=&#38;type=xhtml" onclick="popWin(this.href); return false;">XHTML</a></li></ul></td>
	<td width="75"><span title="Site Design">site-design</span></td>
	<td width="75"><span title="Amit Varia">variaas</span></td>
	<td width="10"><form action="index.php" method="post" onsubmit="return confirm('Really delete?');"><input type="submit" name="" value="&#215;" class="smallerbox" /><input type="hidden" name="event" value="image" /><input type="hidden" name="step" value="image_delete" /><input type="hidden" name="id" value="1" /></form></td>
</tr>
</table>
		</fieldset>
	</div>
	<div id="asv_files">
		<fieldset>
			<legend>Files</legend>
			<form class="upload-form" method="post" enctype="multipart/form-data" action="index.php">
<div>
<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
<input type="hidden" name="event" value="file" />
<input type="hidden" name="step" value="file_insert" />
<input type="hidden" name="id" value="" />
<input type="hidden" name="sort" value="" />

<input type="hidden" name="dir" value="" />
<input type="hidden" name="page" value="" />
<input type="hidden" name="search_method" value="" />
<input type="hidden" name="crit" value="" />
<p><label for="file-upload">Upload file</label>&#160;<a target="_blank" href="http://rpc.textpattern.com/help/?item=upload&#38;lang=en" onclick="popWin(this.href); return false;" class="pophelp">?</a>&#160;<input type="file" name="thefile" value="" class="edit" id="file-upload" />&#160;<input type="submit" name="" value="Upload" class="smallerbox" /></p>
</div>
</form>


<form action="index.php" method="post" style="margin: auto; text-align: center;"><p><label for="file-search">Search</label>&#160;<select id="file-search" name="search_method" class="list">
	<option value="id">ID#</option>
	<option value="filename" selected="selected">Name</option>

	<option value="description">Description</option>
	<option value="category">Category</option>
</select>&#160;<input type="text" name="crit" value="" size="15" class="edit" /><input type="hidden" name="event" value="file" /><input type="hidden" name="step" value="file_list" /><input type="submit" name="search" value="Go" class="smallerbox" /></p></form>
<table cellpadding="0" cellspacing="0" border="0" id="list" align="center">
<tr>
	<th><a href="index.php?step=list&#38;event=file&#38;sort=id&#38;dir=asc">ID#</a></th>	<td>&#160;</td>

	<th><a href="index.php?step=list&#38;event=file&#38;sort=filename&#38;dir=asc">Name</a></th>

	<th><a href="index.php?step=list&#38;event=file&#38;sort=description&#38;dir=asc">Description</a></th>
	<th><a href="index.php?step=list&#38;event=file&#38;sort=category&#38;dir=asc">Category</a></th><th>Tags</th><th>Status</th>
	<th><a href="index.php?step=list&#38;event=file&#38;sort=downloads&#38;dir=asc">Downloads</a></th><th>&#160;</th></tr><tr>
	<td>2</td>
	<td width="65"><ul><li><a href="?event=file&#38;step=file_edit&#38;id=2&#38;sort=&#38;dir=desc&#38;page=1&#38;search_method=&#38;crit=">Edit</a></li><li><a href="http://127.0.0.1/file_download/2">Download</a></li></ul></td>

	<td width="125"><a href="?event=file&#38;step=file_edit&#38;id=2&#38;sort=&#38;dir=desc&#38;page=1&#38;search_method=&#38;crit=">asv_breadcrumb_v1.0.txt</a></td>
	<td width="150">&#160;</td>
	<td width="90"><span title="plugins">plugins</span></td>
	<td width="75">
<ul>
	<li><a target="_blank" href="?event=tag&#38;tag_name=file_download_link&#38;id=2&#38;description=&#38;filename=asv_breadcrumb_v1.0.txt&#38;type=textile" onclick="popWin(this.href, 400, 250); return false;">Textile</a></li>
	<li><a target="_blank" href="?event=tag&#38;tag_name=file_download_link&#38;id=2&#38;description=&#38;filename=asv_breadcrumb_v1.0.txt&#38;type=textpattern" onclick="popWin(this.href, 400, 250); return false;">Textpattern</a></li>

	<li><a target="_blank" href="?event=tag&#38;tag_name=file_download_link&#38;id=2&#38;description=&#38;filename=asv_breadcrumb_v1.0.txt&#38;type=xhtml" onclick="popWin(this.href, 400, 250); return false;">XHTML</a></li>
</ul></td>
	<td width="45"><span class="ok">Ok</span></td>
	<td width="25">None</td>
	<td width="10"><form action="index.php" method="post" onsubmit="return confirm('Really delete?');"><input type="submit" name="" value="&#215;" class="smallerbox" /><input type="hidden" name="event" value="file" /><input type="hidden" name="step" value="file_delete" /><input type="hidden" name="id" value="2" /></form></td>
</tr><tr>
	<td>1</td>
	<td width="65"><ul><li><a href="?event=file&#38;step=file_edit&#38;id=1&#38;sort=&#38;dir=desc&#38;page=1&#38;search_method=&#38;crit=">Edit</a></li><li><a href="http://127.0.0.1/file_download/1">Download</a></li></ul></td>

	<td width="125"><a href="?event=file&#38;step=file_edit&#38;id=1&#38;sort=&#38;dir=desc&#38;page=1&#38;search_method=&#38;crit=">asv_amazon_v0.4.txt</a></td>
	<td width="150">My plugin</td>
	<td width="90"><span title="plugins">plugins</span></td>
	<td width="75">
<ul>
	<li><a target="_blank" href="?event=tag&#38;tag_name=file_download_link&#38;id=1&#38;description=My+plugin&#38;filename=asv_amazon_v0.4.txt&#38;type=textile" onclick="popWin(this.href, 400, 250); return false;">Textile</a></li>
	<li><a target="_blank" href="?event=tag&#38;tag_name=file_download_link&#38;id=1&#38;description=My+plugin&#38;filename=asv_amazon_v0.4.txt&#38;type=textpattern" onclick="popWin(this.href, 400, 250); return false;">Textpattern</a></li>

	<li><a target="_blank" href="?event=tag&#38;tag_name=file_download_link&#38;id=1&#38;description=My+plugin&#38;filename=asv_amazon_v0.4.txt&#38;type=xhtml" onclick="popWin(this.href, 400, 250); return false;">XHTML</a></li>
</ul></td>
	<td width="45"><span class="ok">Ok</span></td>
	<td width="25">None</td>
	<td width="10"><form action="index.php" method="post" onsubmit="return confirm('Really delete?');"><input type="submit" name="" value="&#215;" class="smallerbox" /><input type="hidden" name="event" value="file" /><input type="hidden" name="step" value="file_delete" /><input type="hidden" name="id" value="1" /></form></td>
</tr>
</table>
		</fieldset>
	</div>
	<div id="asv_links">
		<fieldset>
			<legend>Links</legend>
			<form action="index.php" method="post" style="margin-bottom: 25px;"><table cellpadding="3" cellspacing="0" border="0" id="edit" align="center">
<tr><td class="noline" style="text-align: right; vertical-align: middle;"><label for="link-title">Title </label></td><td class="noline"><input type="text" name="linkname" value="" size="30" class="edit" tabindex="1" id="link-title" /></td></tr><tr><td class="noline" style="text-align: right; vertical-align: middle;"><label for="link-sort">Sort Value </label></td><td class="noline"><input type="text" name="linksort" value="" size="15" class="edit" tabindex="2" id="link-sort" /></td></tr><tr><td class="noline" style="text-align: right; vertical-align: middle;"><label for="link-url">URL <a target="_blank" href="http://rpc.textpattern.com/help/?item=link_url&#38;lang=en" onclick="popWin(this.href); return false;" class="pophelp">?</a></label></td><td class="noline"><input type="text" name="url" value="" size="30" class="edit" tabindex="3" id="link-url" /></td></tr><tr><td class="noline" style="text-align: right; vertical-align: middle;"><label for="link-category">Category <a target="_blank" href="http://rpc.textpattern.com/help/?item=link_category&#38;lang=en" onclick="popWin(this.href); return false;" class="pophelp">?</a></label></td>	<td>

<select id="link-category"  name="category" class="list">
	<option value="" selected="selected">&nbsp;</option>
	<option value="textpattern">Textpattern</option>
</select> [<a href="?event=category&#38;step=list">Edit</a>]</td>
</tr><tr><td style="text-align: right; vertical-align: top;"><label for="link-description">Description</label>&#160;<a target="_blank" href="http://rpc.textpattern.com/help/?item=link_description&#38;lang=en" onclick="popWin(this.href); return false;" class="pophelp">?</a></td>	<td><textarea id="link-description" name="description" cols="40" rows="7" tabindex="4"></textarea></td>
</tr><tr>	<td>&#160;</td>

	<td><input type="submit" name="" value="Save" class="publish" /></td>
</tr>
</table>
<input type="hidden" name="event" value="link" /><input type="hidden" name="step" value="link_post" /><input type="hidden" name="id" value="" /><input type="hidden" name="search_method" value="" /><input type="hidden" name="crit" value="" /></form>



<form action="index.php" method="post" style="margin: auto; text-align: center;"><p><label for="link-search">Search</label>&#160;<select id="link-search" name="search_method" class="list">
	<option value="id">ID#</option>
	<option value="name" selected="selected">Name</option>
	<option value="description">Description</option>

	<option value="category">Category</option>
</select>&#160;<input type="text" name="crit" value="" size="15" class="edit" /><input type="hidden" name="event" value="link" /><input type="hidden" name="step" value="link_edit" /><input type="submit" name="search" value="Go" class="smallerbox" /></p></form>


<form action="index.php" method="post" name="longform" onsubmit="return verify('Are you sure?')"><table cellpadding="0" cellspacing="0" border="0" id="list" align="center">

<tr>
	<th><a href="index.php?step=list&#38;event=link&#38;sort=id&#38;dir=desc">ID#</a></th><th>&#160;</th>
	<th><a href="index.php?step=list&#38;event=link&#38;sort=name&#38;dir=desc">Name</a></th>
	<th><a href="index.php?step=list&#38;event=link&#38;sort=description&#38;dir=desc">Description</a></th>

	<th><a href="index.php?step=list&#38;event=link&#38;sort=category&#38;dir=desc">Category</a></th>
	<th><a href="index.php?step=list&#38;event=link&#38;sort=date&#38;dir=desc">Date</a></th><th>&#160;</th></tr><tr>
	<td width="20">2</td>
	<td width="35">
<ul>
	<li><a href="?event=link&#38;step=link_edit&#38;id=2&#38;sort=&#38;dir=asc&#38;page=1&#38;search_method=&#38;crit=">Edit</a></li>
	<li><a href="http://textpattern.net/">View</a></li>

</ul></td>
	<td width="125"><a href="?event=link&#38;step=link_edit&#38;id=2&#38;sort=&#38;dir=asc&#38;page=1&#38;search_method=&#38;crit=">TextBook</a></td>
	<td width="150">&#160;</td>
	<td width="125"><span title="Textpattern">textpattern</span></td>
	<td width="75">20 Jul 2005 12:54 PM</td>
	<td><input type="checkbox" name="selected[]" value="2" /></td>
</tr><tr>
	<td width="20">1</td>

	<td width="35">
<ul>
	<li><a href="?event=link&#38;step=link_edit&#38;id=1&#38;sort=&#38;dir=asc&#38;page=1&#38;search_method=&#38;crit=">Edit</a></li>
	<li><a href="http://textpattern.com/">View</a></li>
</ul></td>
	<td width="125"><a href="?event=link&#38;step=link_edit&#38;id=1&#38;sort=&#38;dir=asc&#38;page=1&#38;search_method=&#38;crit=">Textpattern</a></td>
	<td width="150">&#160;</td>
	<td width="125"><span title="Textpattern">textpattern</span></td>

	<td width="75">20 Jul 2005 12:54 PM</td>
	<td><input type="checkbox" name="selected[]" value="1" /></td>
</tr><tr>
	<td width="20">3</td>
	<td width="35">
<ul>
	<li><a href="?event=link&#38;step=link_edit&#38;id=3&#38;sort=&#38;dir=asc&#38;page=1&#38;search_method=&#38;crit=">Edit</a></li>
	<li><a href="http://textpattern.org/">View</a></li>

</ul></td>
	<td width="125"><a href="?event=link&#38;step=link_edit&#38;id=3&#38;sort=&#38;dir=asc&#38;page=1&#38;search_method=&#38;crit=">Txp Resources</a></td>
	<td width="150">&#160;</td>
	<td width="125"><span title="Textpattern">textpattern</span></td>
	<td width="75">20 Jul 2005 12:55 PM</td>
	<td><input type="checkbox" name="selected[]" value="3" /></td>
</tr>

<tr><td colspan="7" style="text-align: right; border: none;">Select: <input type="button" name="selall" value="All" class="smallerboxsp" title="select all" onclick="selectall();" /><input type="button" name="selnone" value="None" class="smallerboxsp" title="select none" onclick="deselectall();" /><input type="button" name="selrange" value="Range" class="smallerboxsp" title="select range" onclick="selectrange();" /><label for="withselected">With selected:</label>&#160;<select name="edit_method" class="list" id="withselected">

	<option value="" selected="selected"></option>
	<option value="delete">Delete</option>
</select>
<input type="hidden" name="event" value="link" />
<input type="hidden" name="step" value="link_multi_edit" />
<input type="hidden" name="page" value="1" />
<input type="submit" name="" value="Go" class="smallerbox" /></td></tr>
</table>
</form>
		</fieldset>
	</div>
	<div class="clear"></div>
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
