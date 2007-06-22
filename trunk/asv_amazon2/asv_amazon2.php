<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_amazon2';

$plugin['version'] = '0.2';
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
//-------------------------------------------------------------

if('admin' == @txpinterface) 
{
	register_callback('asv_amazon2', 'article');
}

if(gps('asv_amazon2_action') && gps('asv_keywords'))
{
	$asv_amazon = new asv_Amazon("US");
	header('Content-Type: text/xml'); 
	echo $asv_amazon->request(gps('asv_keywords'), gps('asv_searchindex'), gps('asv_itempage'));
	exit;
}

//-------------------------------------------------------------

function asv_amazon2 ($event, $step)
{
	$asv_searchIndex = array("Apparel", "Baby", "Blended", "Books", "Classical", "DVD", "DigitalMusic", "Electronics", "GourmetFood", "HealthPersonalCare", "Jewelry", "Kitchen", "Magazines", "Merchants", "Miscellaneous", "Music", "MusicTracks", "MusicalInstruments", "OfficeProducts", "OutdoorLiving", "PCHardware", "PetSupplies", "Photo", "Restaurants", "Software", "SportingGoods", "Tools", "Toys", "VHS", "Video", "VideoGames", "WirelessAccessories");

	$line = "<h3 class=\"plain\">";
	
	$line .= "<a href=\"#asv_amazon2\" onclick=\"$('#asv_amazon2wrapper').slideToggle('slow'); return false;\">Amazon2</a>";

	$line .= "</h3>";

	$form = "<form onSubmit=\"asv_loadResults(); return false;\"><fieldset><legend>Search</legend>".
	
		graf("<label for=\"asv_SearchIndex\">Choose a category</label><br />".
		
			selectInput('asv_SearchIndex', $asv_searchIndex, $asv_searchIndex, true,'','asv_SearchIndex')).
				
		graf("<label for=\"asv_Keywords\">Keywords</label><br />".
		
			fInput('text', 'asv_Keywords', '', 'edit', '', '', '20',  '', 'asv_Keywords')).
			
		fInput('submit','asv_Search','Search',"publish", '', 'asv_loadResults();return false;', '', '').
			
		fInput('button','asv_Cancel','Cancel',"publish", '', 'asv_cancelResults();return false;', '', '').
		
		"</fieldset></form>";
		
	$line .= "<div id=\"asv_amazon2wrapper\" style=\"display:none\" ><div id=\"asv_amazon2\" >$form</div><div id=\"asv_amazon2form\" style=\"display:none\"></div><div id=\"asv_amazon2Results\" style=\"display:none\"></div></div>";
	
	$line = asv_safeJS($line);
	
	$forms = array();
	
	$forms[0] = asv_safeJS('<txp:asv_amazon2 asin="[asv_amazon2_asin]"><txp:asv_amazon2_title /><txp:asv_amazon2_imageURL url="[asv_amazon2_imgURLm]" /><txp:asv_amazon2_url /></txp:asv_amazon2>');
	
	$js = <<<EOF
<SCRIPT LANGUAGE="JavaScript" SRC="/textpattern/jquery.js">
</SCRIPT>
<script language="javascript" type="text/javascript">
<!--

//------------------------------------------------------------- 
//Attach the amazon2 plugin to the page

 $(document).ready(function() {
	$("$line").insertBefore("#write-status");
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
	$('#asv_amazon2form').html('<fieldset><legend>Choose Form</legend><form><select name="amazonForm"><option>default</option></select></form>');
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
		
		var form = '<form id="asv_amazon2_'+asin+' onsubmit="asv_amazon2_addtoBody(\''+asin+'\')">' + 
					'<input type="hidden" name="asv_amazon2_title" value="'+title+'" />' +
					'<input type="hidden" name="asv_amazon2_sImageURL" value ="'+sImageURL+'" />' +
					'<input type="hidden" name="asv_amazon2_mImageURL" value ="'+mImageURL+'" />' +
					'<input type="hidden" name="asv_amazon2_lImageURL" value ="'+lImageURL+'" />' +
					'<input type="hidden" name="asv_amazon2_url" value ="'+url+'" />' +
					'<input type="hidden" name="asv_amazon2_asin" value ="'+asin+'" />' +
					'<input type="button" style="float:right" onclick="asv_amazon2_addtoBody(\''+asin+'\')" class="publish" value="add" />'
				   '</form>';
		
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

function asv_amazon2_addtoBody(asin)
{
	var elem = $('#asv_amazon2_'+asin);
	
	var title = $('input:hidden[@name=asv_amazon2_title]').val();
	var sImageURL = $('input:hidden[@name=asv_amazon2_sImageURL]').val();
	var mImageURL = $('input:hidden[@name=asv_amazon2_mImageURL]').val();
	var lImageURL = $('input:hidden[@name=asv_amazon2_lImageURL]').val();
	var url = $('input:hidden[@name=asv_amazon2_sImageURL]').val();
	
	var line = '';
	line += "$forms[0]";
	line = line.replace('[asv_amazon2_asin]', asin);
	line = line.replace('[asv_amazon2_imgURLs]', sImageURL);
	line = line.replace('[asv_amazon2_imgURLm]', mImageURL);
	line = line.replace('[asv_amazon2_imgURLl]', lImageURL);
	line = line.replace('[asv_amazon2_url]', url);
	
	$('#body').appendVal('\\n' + line);
	
	
}

//-------------------------------------------------------------  
//Add the Amazon information to the body of the article

function asv_onClick(elem)
{
	$('#body').append(" a");
}

//-------------------------------------------------------------

function asv_cancelResults()
{
	$('#asv_amazon2Results').hide('slow');
	$('#asv_amazon2form').hide('slow');
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
		$this->$locale = $locale;
	}
	
	//-------------------------------------------------------------
	
	function request($keywords, $searchindex, $itemPage = "1")
	{
		$url = $this->buildURL(doSlash($keywords), doSlash($searchindex), doSlash($itemPage));

		// create a new curl resource
		$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);

		// grab URL and pass it to the browser
		curl_exec($ch);

		// close curl resource, and free up system resources
		curl_close($ch);
	}
	
	//-------------------------------------------------------------
	
	function buildURL /* private */($keywords, $searchindex, $itemPage = "1")
	{
		$url = $this->baseURL.
				"Service=".$this->service.
				"&Operation=".$this->operation.
				"&SubscriptionId=".$this->api_key.
				"&SearchIndex=".$searchindex.
				"&Keywords=".$keywords.
				"&ResponseGroup=".$this->responseGroup.
				"&ItemPage=".$itemPage."&Version=".$this->version;
		
		return $url;
	}
	
	//-------------------------------------------------------------
}

//-------------------------------------------------------------

# --- END PLUGIN CODE ---
?>