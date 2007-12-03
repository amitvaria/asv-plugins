<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_amazon';

$plugin['version'] = '2.1';
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
h1. asv_amazon

h2. version 2.0

h3. What's new?

A long time ago, I created a plugin called asv_amazon, which let you display products from Amazon on your site. It worked, but inserting the product required you to go to Amazon, copy the ASIN number, and paste into the TextPattern. What a hassle! Well I finally got around to writing the next version of asv_amazon. 

h3. How does it work?

Simple - when writing an article just click on the "Amazon" link in the top right column. You can search and add products to your post by clicking the "add" button next to each item.

h3. Creating a form

With version 1.0, you will have to have forms to generate the layout of the product on your page. When creating a new 'misc' form you can use the following values:

asv_url
asv_sImageUrl
asv_mImageUrl
asv_lImageUrl
asv_asin
asv_title

OR you can use the depecrated literals from the original version

asv_DetailPageURL
asv_SmallImageURL
asv_MediumImageURL
asv_LargeImageURL
asv_Title

asv_amazon2 will just replace these literals with their product counterpart, so:

bc. <a href="asv_amazon2_url"><img src="asv_amazon2_sImageUrl" /><br />asv_amazon2_title</a>

returns:

bc. <a href="http://www.amazon.com/gp/redirect.html%3FASIN=1590598326%26tag=ws%26lcode=xm2%26cID=2025%26ccmID=165953%26location=/o/ASIN/1590598326%253FSubscriptionId=0Y8F1YV1N2YSGJ1MC202"><img src="http://ec1.images-amazon.com/images/I/01yaq1etjKL.jpg" /><br />Textpattern Solutions: PHP-Based Content Management Made Easy (Solutions)</a>
which will display like this:

<a href="http://www.amazon.com/gp/redirect.html%3FASIN=1590598326%26tag=ws%26lcode=xm2%26cID=2025%26ccmID=165953%26location=/o/ASIN/1590598326%253FSubscriptionId=0Y8F1YV1N2YSGJ1MC202"><img src="http://ec1.images-amazon.com/images/I/01yaq1etjKL.jpg" /><br />Textpattern Solutions: PHP-Based Content Management Made Easy (Solutions)</a>

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
// asv_amazon2
// Designed around the need to include items from Amazon in your website
//-------------------------------------------------------------

if('admin' == @txpinterface) 
{
	add_privs('asv_amazon2','1,2,3,4'); // Allow only userlevels 1,2,3,4 acess to this plugin.
	register_tab('extensions', 'asv_amazon2', "Amazon Settings"); 
	register_callback("asv_amazon2", "asv_amazon2"); 
	
	register_callback('asv_amazon_backend', 'article');
	register_callback('asv_amazon_backend', 'page');
	register_callback('asv_amazon_backend', 'form');
	
}

//-------------------------------------------------------------

if(gps('asv_amazon2_action') && gps('asv_keywords'))
{
	$asv_amazon = new asv_Amazon("US");
	header('Content-Type: text/xml'); 
	echo $asv_amazon->requestItems(array(
								"Keywords" => gps('asv_keywords'), 
								"SearchIndex" => gps('asv_searchindex'), 
								"ItemPage" => gps('asv_itempage'),
								"Operation" => "ItemSearch"
							));
	exit;
}

//-------------------------------------------------------------

function asv_amazon2($event, $step)
{
	pagetop('Amazon Cache Settings');
	
	$asv_amazon_cachedir = '';
	$asv_amazon_cachedurl = '';
	
	
	if($step == 'save')
	{
		set_pref('asv_amazon_cachedir', gps('asv_amazon_cachedir'),'','');
		set_pref('asv_amazon_cacheurl', gps('asv_amazon_cacheurl'),'','');
	}
	
	extract(get_prefs());
	
	echo startTable('list').tr(td(form( 
				graf(tag('Path to cache: ', 'label').
					fInput('text', 'asv_amazon_cachedir', $asv_amazon_cachedir, 'edit', '', '', 30)).
				graf(tag('URL to cache: ', 'label').
					fInput('text', 'asv_amazon_cacheurl', $asv_amazon_cacheurl, 'edit', '', '', 30)).
				eInput('asv_amazon2').
				sInput('save').
		 		graf(fInput('submit', '', gTxt('Save'), ''))
				))).endTable();
}

//-------------------------------------------------------------

function asv_amazon2_display($atts,$thing)
{
	extract(lAtts(array(
		'url' => '',
		'smallimage' => '',
		'mediumimage' => '',
		'largeimage' => '',
		'asin' => '',
		'title' => '',
		'form' => '',
		'cache' => '',
		'locale' => 'US'
	), $atts));

	if($asin)
	{	
		$docache = ($cache=="y")? true: false;	
		$asv_amazon_request = new asv_Amazon($locale);						
		$asv_amazon2_contents = $asv_amazon_request->requestItem(array(
										"ItemId" => $asin,
										"Operation" => "ItemLookup"
									), $docache);		
		
		
		$url = $asv_amazon2_contents["asv_DetailPageURL"];
		$smallimage = $asv_amazon2_contents["asv_SmallImageURL"];
		$mediumimage = $asv_amazon2_contents["asv_MediumImageURL"];
		$largeimage = $asv_amazon2_contents["asv_LargeImageURL"];
		$title = $asv_amazon2_contents["asv_Title"];		
	}
	
	if($form)
	{
		$thing = fetch_form($form);
	}
	if($thing)
	{
		
		$thing = str_replace('asv_url', $url, $thing);
		$thing = str_replace('asv_sImageUrl', $smallimage, $thing);
		$thing = str_replace('asv_mImageUrl', $mediumimage, $thing);
		$thing = str_replace('asv_lImageUrl', $largeimage, $thing);
		$thing = str_replace('asv_asin', $asin, $thing);
		$thing = str_replace('asv_title', $title, $thing);
		
		$thing = str_replace('asv_amazon2_url', $url, $thing);
		$thing = str_replace('asv_amazon2_sImageUrl', $smallimage, $thing);
		$thing = str_replace('asv_amazon2_mImageUrl', $mediumimage, $thing);
		$thing = str_replace('asv_amazon2_lImageUrl', $largeimage, $thing);
		$thing = str_replace('asv_amazon2_asin', $asin, $thing);
		$thing = str_replace('asv_amazon2_title', $title, $thing);
		
		$thing = str_replace('asv_DetailPageURL', $url, $thing);
		$thing = str_replace('asv_SmallImageURL', $smallimage, $thing);
		$thing = str_replace('asv_MediumImageURL', $mediumimage, $thing);
		$thing = str_replace('asv_LargeImageURL', $largeimage, $thing);
		$thing = str_replace('asv_Title', $title, $thing);
	}
	else
	{
		$thing =  "<a href=\"".$url."\"><img src=\"".$smallimage."\" /><br />".$title."</a><br />";
	}
	
	return $thing;
}

//-------------------------------------------------------------

function asv_amazon_backend ($event, $step)
{
	$asv_amazon2_location = '';
	$asv_amazon2_textarea= '';
	
	switch($event){
		case "article": 
			$asv_amazon2_location = "advanced";
			$asv_amazon2_textarea = "body";
			break;
		case "page":
			$asv_amazon2_location = "misc-tags";
			$asv_amazon2_textarea = "html";
			break;
		case "form":
			$asv_amazon2_location = "article-tags";
			$asv_amazon2_textarea = "form";
			break;
	}
	
	$asv_searchIndex = array("Apparel", "Baby", "Blended", "Books", "Classical", "DVD", "DigitalMusic", "Electronics", "GourmetFood", "HealthPersonalCare", "Jewelry", "Kitchen", "Magazines", "Merchants", "Miscellaneous", "Music", "MusicTracks", "MusicalInstruments", "OfficeProducts", "OutdoorLiving", "PCHardware", "PetSupplies", "Photo", "Restaurants", "Software", "SportingGoods", "Tools", "Toys", "VHS", "Video", "VideoGames", "WirelessAccessories");

	$line = "<h3 class=\"plain\">";
	
	$line .= "<a href=\"#asv_amazon2\" onclick=\"$('#asv_amazon2wrapper').slideToggle('slow'); return false;\">Amazon</a>";

	$line .= "</h3>";

	$form = "<form onSubmit=\"asv_loadResults(); return false;\"><h3>Amazon</h3><p id=\"asv_amazon2_close\"><a href=\"#\" onclick=\"$('#asv_amazon2wrapper').slideToggle('slow'); return false;\">close</a></p><fieldset><legend>Search</legend>".
	
		graf("<label for=\"asv_SearchIndex\">Choose a category</label><br />".
		
			selectInput('asv_SearchIndex', $asv_searchIndex, $asv_searchIndex, false,'','asv_SearchIndex')).
				
		graf("<label for=\"asv_Keywords\">Keywords</label><br />".
		
			fInput('text', 'asv_Keywords', '', 'edit', '', '', '20',  '', 'asv_Keywords')).
			
		fInput('submit','asv_Search','Search',"publish", '', 'asv_loadResults();return false;', '', '').
			
		fInput('button','asv_Cancel','Cancel',"publish", '', 'asv_cancelResults();return false;', '', '').
		
		"</fieldset></form>";
		
	$line .= "<div id=\"asv_amazon2wrapper\" style=\"display:none\" ><div id=\"asv_amazon2\" >$form</div><div id=\"asv_amazon2form\" style=\"display:none\"></div><div id=\"asv_amazon2Results\" style=\"display:none\"></div></div>";
	
	$line = asv_safeJS($line);
	
	$rs = safe_column('name', 'txp_form', "type = 'misc'");

	$forms = '<p><label for="asv_amazon2_tagtype">Type</label>: '.selectInput('asv_amazon2_tagtype', array('form', 'form (cached)', 'hardcode'), array('form', 'form (cached)', 'hardcode'), false, '', 'asv_amazon2_tagtype').'</p>'; 
	
	$forms .= '<p><label for="asv_amazon2_form">Form</label>: ';
	
	if ($rs)
	{
		$forms .= selectInput('asv_amazon2_form', $rs, $form, true, '', 'asv_amazon2_form').'</p>';
	}
	else 
	{
		$forms .= '<select id="asv_amazon2_form"><option /></select></p>';
	}
	$asv_amazon2_imagesize = array('small', 'medium', 'large');
	
	$forms = asv_safeJS($forms);
	
	$js = <<<EOF
<script language="JavaScript" SRC="/textpattern/jquery.js">
</script>
<script language="javascript" type="text/javascript">
<!--

//------------------------------------------------------------- 
//Attach the amazon2 plugin to the page

 $(document).ready(function() {
	$("$line").insertBefore($("#$asv_amazon2_location").prev());
	
	$("#asv_amazon2wrapper").css("position", "absolute");
	$("#asv_amazon2wrapper").css("top", "80px");
	$("#asv_amazon2wrapper").css("right", "0px");
	$("#asv_amazon2wrapper").css("width", "200px");
	$("#asv_amazon2wrapper").css("background-color", "#ffffcc");
	$("#asv_amazon2wrapper").css("padding", "20px");
	$("#asv_amazon2wrapper h3").css("float", "left");
	$("#asv_amazon2wrapper h3").css("display", "inline");
	$("#asv_amazon2_close").css("text-align", "right");
   });

//-------------------------------------------------------------

function asv_loadResults()
{
	var keywords =	$("#asv_Keywords").val();
	var searchindex = $("#asv_SearchIndex option[@selected]").text();
	asv_request(keywords, searchindex, '1');
}

//-------------------------------------------------------------

function asv_request(keywords, searchindex, itempage){
	$('#asv_amazon2form').html('<fieldset><legend>Options</legend><form>$forms</form>');
	$('#asv_amazon2Results').html('<fieldset><legend>Results</legend><div id="asv_amazon2ResultsData"><p >loading...</p></div></fieldset>');
	$("#asv_amazon2ResultsData").css("padding", "0px 0px 10px 0px");
	$('#asv_amazon2form').show('slow');
	$('#asv_amazon2Results').show('slow');
	
	$.get('index.php',
	 	{asv_amazon2_action: "1", asv_keywords: escape(keywords), asv_searchindex: escape(searchindex), asv_itempage: itempage },
	   asv_parseResponse,
	   "xml"
	 );
}

//-------------------------------------------------------------

function asv_parseResponse(xml)
{	
	var itemPage = $('ItemPage', xml).text();
	var searchindex = $('SearchIndex', xml).text();
	var totalPages = $('TotalPages', xml).text();
	var keywords = $('Keywords', xml).text();
	var line ="";

	line += asv_prevnext_link(keywords, searchindex, itemPage, totalPages, "top");

	
	$(xml).find('Item').each(function(){
		var title = $('Title',this).text();
		var sImageURL = $("SmallImage > URL",this).text();
		var mImageURL = $("MediumImage > URL",this).text();
		var lImageURL = $("LargeImage > URL",this).text();
		var url = $('DetailPageURL',this).text();
		var asin = $('ASIN',this).text();
		
		
		var amazonHTML = '<a href="'+url+'"><img src="'+ sImageURL +'" style="display: block;margin-left: auto;margin-right: auto;"/><span style="text-align: center">' + title + '</span></a>' ;
		
		var form = '<form id="asv_amazon2_'+asin+'">' + 
					'<input type="hidden" name="asv_amazon2_title" value="'+title+'" />' +
					'<input type="hidden" name="asv_amazon2_sImageURL" value ="'+sImageURL+'" />' +
					'<input type="hidden" name="asv_amazon2_mImageURL" value ="'+mImageURL+'" />' +
					'<input type="hidden" name="asv_amazon2_lImageURL" value ="'+lImageURL+'" />' +
					'<input type="hidden" name="asv_amazon2_url" value ="'+url+'" />' +
					'<input type="hidden" name="asv_amazon2_asin" value ="'+asin+'" />' +
					'<input type="button" id="asv_amazon2_add_'+asin+'" style="float:right" onclick="asv_amazon2_addtoBody(this.parentNode)" class="publish" value="add" />'+  '</form>';
		
		line += '<p>' + form + amazonHTML  + '</p>';
    });

	line += asv_prevnext_link(keywords, searchindex, itemPage, totalPages, "bottom");
    
    $("#asv_amazon2ResultsData").html(line);
	$("#asv_amazon2ResultsData").css("max-height", "500px");
	$("#asv_amazon2ResultsData").css("overflow", "auto");
	$("#asv_amazon2ResultsData").css("padding", "0px 0px 10px 0px");
}

//-------------------------------------------------------------  

$.fn.appendVal = function(txt) {
    return this.each(function(){
        this.value += txt;
    });
}; 

//-------------------------------------------------------------  


function asv_amazon2_addtoBody(elem)
{
	var title = $('input:hidden[@name=asv_amazon2_title]', elem).val();
	var sImageURL = $('input:hidden[@name=asv_amazon2_sImageURL]', elem).val();
	var mImageURL = $('input:hidden[@name=asv_amazon2_mImageURL]', elem).val();
	var lImageURL = $('input:hidden[@name=asv_amazon2_lImageURL]', elem).val();
	var url = $('input:hidden[@name=asv_amazon2_url]', elem).val();
	var asin = $('input:hidden[@name=asv_amazon2_asin]', elem).val();
	var form = $("#asv_amazon2_form option[@selected]").text();
	
	switch($("#asv_amazon2_tagtype option[@selected]").text())
	{
		case "hardcode":
			var asv_tag = '<txp:asv_amazon2_display ';
			asv_tag += (title)? 'title="'+title+'" ' : "";
			asv_tag += (sImageURL)? 'smallimage="'+sImageURL+'" ' : "";
			asv_tag += (mImageURL)? 'mediumimage="'+mImageURL+'" ' : "";
			asv_tag += (lImageURL)? 'largeimage="'+lImageURL+'" ' : "";
			asv_tag += (url)? 'url="'+url+'" ' : "";
			asv_tag += (form)? 'form="'+form+'" ' : "";
			asv_tag += ' />';
			break;
		case "form":
			var asv_tag = '<txp:asv_amazon2_display ';
			asv_tag += (asin)? 'asin="'+asin+'" ' : "";
			asv_tag += (form)? 'form="'+form+'" ' : "";
			asv_tag += ' />';
			break;
		case "form (cached)":
			var asv_tag = '<txp:asv_amazon2_display ';
			asv_tag += (asin)? 'asin="'+asin+'" ' : "";
			asv_tag += (form)? 'form="'+form+'" ' : "";
			asv_tag += 'cache="y"';
			asv_tag += ' />';
			break;
	}
	//edInsertContent(document.getElementById('$asv_amazon2_textarea'), asv_tag);
	
	$('#$asv_amazon2_textarea').insertAtCaret(asv_tag);	
}

//-------------------------------------------------------------  

function asv_amazon2_addtoBody_Response(response)
{
	insertAtCaret($('#$asv_amazon2_textarea'), response);
}

//-------------------------------------------------------------

function asv_cancelResults()
{
	$('#asv_amazon2Results').slideUp('slow');
	$('#asv_amazon2form').slideUp('slow');
}

//-------------------------------------------------------------

function asv_prevnext_link(keywords, searchindex, itemPage, totalPages, loc)
{	
	line = '';
	
	if(parseInt(totalPages)>1)
	{
		if(loc=="bottom") line+='<hr />';
		
		line+='<p style="text-align: center;">';
		
		itemPage = parseInt(itemPage);
		
		var text = '';
		
		if(itemPage>1)
		{
			var pItemPage = itemPage - 1;
			
			line += '<a href="#" onclick="asv_request(\''+keywords + '\',\'' + searchindex + '\',\''+ pItemPage+'\');return false;">previous</a> | ';
			
		}
		if(itemPage<totalPages)
		{
			var nItemPage = itemPage + 1;
			
			line += '<a href="#" onclick="asv_request(\''+keywords + '\',\'' + searchindex + '\',\''+ nItemPage+'\');return false;">next</a>';	
			
		}
		
		line+='</p>';
		
		if(loc=="top") line+='<hr />';
	}
	
	return line;
}

//-------------------------------------------------------------

/**
 * Insert content at caret position (converted to jquery function)
 * @link http://alexking.org/blog/2003/06/02/inserting-at-the-cursor-using-javascript
 */
$.fn.insertAtCaret = function (myValue) {
	return this.each(function(){
		//IE support
		if (document.selection) {
			this.focus();
			sel = document.selection.createRange();
			sel.text = myValue;
			this.focus();
		}
		//MOZILLA/NETSCAPE support
		else if (this.selectionStart || this.selectionStart == '0') {
			var startPos = this.selectionStart;
			var endPos = this.selectionEnd;
			var scrollTop = this.scrollTop;
			this.value = this.value.substring(0, startPos)
			              + myValue
	                      + this.value.substring(endPos, this.value.length);
			this.focus();
			this.selectionStart = startPos + myValue.length;
			this.selectionEnd = startPos + myValue.length;
			this.scrollTop = scrollTop;
		} else {
			this.value += myValue;
			this.focus();
		}
	});
	
};

//-------------------------------------------------------------


// -->
</script>
EOF;
	echo $js;
}

//-------------------------------------------------------------

function asv_safeJS($line)
{
	return str_replace("\n", "", addslashes($line));
}

//-------------------------------------------------------------
	
class asv_Amazon
{
	var $api_key = "0Y8F1YV1N2YSGJ1MC202";
	var $contentType = "text%2Fxml";
	var $operation = "ItemSearch";
	var $service = "AWSECommerceService";
	var $baseURL = "http://ecs.amazonaws.com/onca/xml?";
	var $searchIndex = "Blended";
	var $responseGroup = "Images,Small";
	var $version = "2005-03-23";
	
	var $locale;
	
	//-------------------------------------------------------------
	
	function asv_Amazon($locale)
	{
		$this->locale = $locale;
		
		switch($locale)
		{
			case "US":
				$this->baseURL = "http://webservices.amazon.com/onca/xml?";
				break;
			case "DE":
				$this->baseURL = "http://webservices.amazon.de/onca/xml?";
				break;
			case "JP":
				$this->baseURL = "http://webservices.amazon.co.jp/onca/xml?";
				break;
			case "FR":
				$this->baseURL = "http://webservices.amazon.fr/onca/xml?";
				break;
			case "CA":
				$this->baseURL = "http://webservices.amazon.ca/onca/xml?";
				break;
			default:
				$this->baseURL = "http://webservices.amazon.com/onca/xml?";
				break;
		}
	}
	
	//-------------------------------------------------------------
	
	function requestItem($requestItems, $docache)
	{		
		extract(get_prefs());

		
		$cache_localpath = "$asv_amazon_cachedir/$this->locale-".$requestItems['ItemId'];
		$cacheurl = $tempdir.$cache_localpath;
	
		if($docache && file_exists($cacheurl) && time() - filemtime($cacheurl) <300)
		{
			$url = $cacheurl;
		}
		else
		{
			$url = $this->buildURL($requestItems);
		}
		

		$data = $this->fetchURL($url);
		
		$contents = $this->parse($data);
		
		if($docache)
		{
			if($cacheurl != $url)
			{
				$this->cache($cache_localpath, $data);
			}
			
			foreach($contents as $key=>$value)
			{
				switch($key)
				{
					case "asv_SmallImageURL":
					case "asv_MediumImageURL":
					case "asv_LargeImageURL":
						$cache_localpath = $asv_amazon_cachedir."/".basename($value);
						
						if(!file_exists($cache_localpath) || time() - filemtime($cache_localpath) >300)
						{
							$data = $this->fetchURL($value);
							$this->cache($cache_localpath, $data);
						}

						$contents[$key] = $asv_amazon_cacheurl.'/'.basename($value);
				}
			}
		}
		
		return $contents;
	}
	
	//-------------------------------------------------------------
	
	function requestItems($requestItems)
	{		
		$url = $this->buildURL($requestItems);
		
		return $this->fetchURL($url);
	}
	
	//-------------------------------------------------------------
	
	function parse($data)
	{
		
		extract(get_prefs());
		
		$parser = xml_parser_create();
		$amazon_parser = &new Amazon2Parser();
		xml_set_object($parser, $amazon_parser);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($parser, "startElement", "endElement");
		xml_set_character_data_handler($parser, "characterData");
	
		if (!xml_parse($parser, $data)) 
		{
			print(sprintf("XML error: %s at line %d",
			xml_error_string(xml_get_error_code($parser)),
			xml_get_current_line_number($parser)));
		}
	
		xml_parser_free($parser);
		
		return $amazon_parser->getContentsArray();
	}
	
	//-------------------------------------------------------------
	
	function cache($localpath, $data)
	{
		$fp = fopen($localpath, 'w');
		fwrite($fp, $data);
		fclose($fp);
	}
	
	//-------------------------------------------------------------
	
	function fetchURL($url)
	{
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
		
	//-------------------------------------------------------------
	
	function buildURL /* private */($requestItems)
	{
		$url = $this->baseURL.
				"Service=".$this->service.
				"&SubscriptionId=".$this->api_key.
				"&Version=".$this->version.
				"&ResponseGroup=".$this->responseGroup;
				
		$this->requestedItems = $requestItems;
		
		foreach($requestItems as $key=>$value)
		{
				$url .= '&'.$key.'='.$value;
		}
		
		return $url;
	}

	//-------------------------------------------------------------
	
	function getResponse()
	{
		return $this->response;
	}
}

//-------------------------------------------------------------

class Amazon2Parser
{
//-------------------------------------------------------------
/* This class is a helper to the asv_amazon function. The purpose of this class is to parse the xml data and return the information to the asv_amazon function.*/
   var $asv_contents;	//hold all the data to return
   var $asv_flag;	//hold the current tag name

   //-------------------------------------------------------------

   function Amazon2Parser()
   {
   }
   
   //-------------------------------------------------------------

   function startElement($parser, $tagName, $attrs){
	   switch ($tagName) {
		   case "DetailPageURL":
		   case "SmallImage":
		   case "MediumImage":
		   case "LargeImage":
		   case "ItemAttributes":
			   $this->asv_flag = $tagName;
			   break;
		   case "URL":
			   if($this->asv_flag == "SmallImage")
				   $this->asv_flag = "SmallImageURL";
			   if($this->asv_flag == "MediumImage")
				   $this->asv_flag = "MediumImageURL";
			   if($this->asv_flag == "LargeImage")
				   $this->asv_flag = "LargeImageURL";
			   break;
		   case "Title":
			   if($this->asv_flag == "ItemAttributes")
				   $this->asv_flag = "Title";
			   break;
	   }
   }
   
   //-------------------------------------------------------------

   function endElement($parser, $tagName){
   }

   //-------------------------------------------------------------
   
   function characterData($parser, $data){
	   switch ($this->asv_flag) {
		   case "DetailPageURL":
		   case "SmallImageURL":
		   case "MediumImageURL":
		   case "LargeImageURL":
		   case "Title":
			   $this->asv_contents["asv_".$this->asv_flag] = $data;
			   $this->asv_flag = '';
			   break;
	   }
   }
   
   //-------------------------------------------------------------

   function getContentsArray(){
	   return $this->asv_contents;
   }

}

function asv_amazon($att, $thing=''){
/* asv_amazon is the main function for the plugin. When the plugin tags are inserted into the page, this function will be called.
	$att - an array that holds the following values: $asin, [$locale, $cache, $cacheimg]
	$thing - a string that holds the string in between the open an close tags of this plugin
*/
	global $tempdir;
	global $siteurl;
	
	//Setting defaults! - Note any settings in the tags will override these settings
	$locale="us"; // us/de/fr/jp/ca/uk
	$cacheimg="none"; // none/small/medium/large
	$cache="none"; // true/false
	$tmp_folder = $tempdir;
	$path = "http://$siteurl/textpattern/tmp";

	//Make sure that the array $att is a proper array before extracting all the contents into variable names of their keys
	is_array($att) ? extract($att) : print("asv_amazon: Please refer to the manual to properly define the asv_amazon tag.");
	
	//An ASIN must be provided for this plugin
	if(!isset($asin)) 
	{
		print("You must specify the ASIN (Amazon Standard Item Number) for the product that you want to display. The ASIN is a 10 digit number found in the URL of theproduct detail page.");
	}
	
	//cacheimg should be either small, medium, or large. By default cacheimg is set to none (this means not to cache the image)
	if(($cache == "true") && file_exists("$tmp_folder/$locale-$asin.xml") && (time() - (fileatime("$tmp_folder/$locale-$asin.xml")) < 8640000))
	{
		$url = "$tmp_folder/$locale-$asin.xml";
	}		
	else
	{//Otherwise go grab the xml file
	
		$base = '';
		
		//Set the base url to the appropriate locale
		switch ($locale) {
			case "us":
				$base = 'http://webservices.amazon.com/onca/xml';
				break;
			case "uk":
				$base = 'http://webservices.amazon.co.uk/onca/xml';
				break;
			case "de":
				$base = 'http://webservices.amazon.de/onca/xml';
				break;
			case "jp":
				$base = 'http://webservices.amazon.co.jp/onca/xml';
				break;
			case "fr":
				$base = 'http://webservices.amazon.fr/onca/xml';
				break;
			case "ca":
				$base = 'http://webservices.amazon.ca/onca/xml';
				break;
		}
		
		$query_string = '';
		
		$params = array(
			'Service' => 'AWSECommerceService',
			'SubscriptionId' => '0DZWD9BKQ6DHPH4XA2G2' , // You can specify your own developer tag if you have registered to be an Amazon web services developer
			'Operation' => 'ItemLookup',
			'ItemId' => $asin,
			'ResponseGroup' => 'Medium',
		);
		
		foreach ($params as $key => $value) 
		{
			$query_string .= "$key=" . urlencode($value) . "&";
		}
		
		//Build the url
		$url = "$base?$query_string";
	}
	
	//Set up the parser - to understand this I suggest the following tutorial: http://www.sitepoint.com/article/php-xml-parsing-rss-1-0
	$parser = xml_parser_create();
	$amazon_parser = &new Amazon2Parser($locale, $asin, $cacheimg, $tmp_folder);
	xml_set_object($parser, $amazon_parser);
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
	xml_set_element_handler($parser, "startElement", "endElement");
	xml_set_character_data_handler($parser, "characterData");

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$data=curl_exec ($ch);
	curl_close ($ch);
	if (!xml_parse($parser, $data)) {
		print(sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($parser)),
					xml_get_current_line_number($parser)));
	}

	xml_parser_free($parser);

	//Grab the contents from the AmazonParser Class
	$asv_contents = $amazon_parser->getContentsArray();

	//Properly synthesize the HTML to return
	if($thing)
	{
		foreach($asv_contents as $key=>$value)
		{
			$thing = str_replace($key, $value, 	$thing);
		}
	}
	else
	{
		$thing = "<a href=\"".$asv_contents['asv_DetailPageURL']."\"><img src=\"".$asv_contents['asv_SmallImageURL']."\" /><br />".$asv_contents['asv_Title']."</a><br />";
	}
	return $thing;
}
# --- END PLUGIN CODE ---
?>