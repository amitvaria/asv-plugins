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

if(gps('asv_amazon2_action') && gps('asv_searchindex') && gps('asv_keywords'))
{
	$asv_amazon = new asv_Amazon("US");
	echo $asv_amazon->request(gps('asv_searchindex'), gps('asv_keywords'), gps('asv_itempage'));
	
	
	exit;
}

function asv_amazon2 ($event, $step)
{
	$asv_searchIndex = array("Apparel", "Baby", "Blended", "Books", "Classical", "DVD", "DigitalMusic", "Electronics", "GourmetFood", "HealthPersonalCare", "Jewelry", "Kitchen", "Magazines", "Merchants", "Miscellaneous", "Music", "MusicTracks", "MusicalInstruments", "OfficeProducts", "OutdoorLiving", "PCHardware", "PetSupplies", "Photo", "Restaurants", "Software", "SportingGoods", "Tools", "Toys", "VHS", "Video", "VideoGames", "WirelessAccessories");

	$line = "<h3 class=\"plain\">";
	
	$line .= "<a href=\"#asv_amazon2\" onclick=\"toggleDisplay('asv_amazon2'); return false;\">Amazon2</a>";

	$line .= "</h3>";

	$form = "<form><fieldset><legend>Search</legend>".
		graf("<label for=\"asv_SearchIndex\">Choose a category</label><br />".
			selectInput('asv_SearchIndex', $asv_searchIndex, $asv_searchIndex, true,'','asv_SearchIndex')).
		graf("<label for=\"asv_Keywords\">Keywords</label><br />".
			fInput('text', 'asv_Keywords', '', 'edit', '', '', '20',  '', 'asv_Keywords')).
		fInput('submit','asv_Search','Search',"publish", '', 'asv_loadResults();return false;', '', '').
		fInput('button','asv_Cancel','Cancel',"publish", '', 'asv_cancelResults();return false;', '', '').
		"</fieldset></form>";
	
	$line .= "<div id=\"asv_amazon2\" style=\"display: none;\">$form</div>";
	
	$line = asv_safeJS($line);


	$js = <<<EOF
<script language="javascript" type="text/javascript">
<!--
/*var loc = document.getElementById('edit');
var brother = document.getElementById('write-status');

//loc.innerHTML = "$line" + loc.innerHTML;
var td = document.createElement('td');
td.id = 'asv_amazon2';
td.innerHTML = "$line";
loc.childNodes[1].rows[0].appendChild(td);
*/
var loc = document.getElementById('article-col-2');
loc.innerHTML = "$line" + loc.innerHTML;

function asv_onClick(elem)
{
	var dest = document.getElementById('body');
	dest.value += " " + elem;
}

function asv_loadResults()
{
	var searchindex = document.getElementById('asv_SearchIndex');
	var keywords = document.getElementById('asv_Keywords');

	var url ='/index.php?asv_amazon2_action=1&asv_keywords='+keywords.value+'&asv_searchindex='+searchindex.options[searchindex.selectedIndex].text+'&asv_itempage=1';	
	asv_request(url);
	
	
}
function asv_request(url){
	var dest = document.getElementById('asv_amazon2');
	var exists = document.getElementById('asv_amazon2Results'); ;
	if(!exists)
	{
		exists = document.createElement('div');
		exists.id = 'asv_amazon2Results';
		exists.innerHTML = '<fieldset><legend>Results</legend><div id="asv_amazon2ResultsData"><p>loading...</p></div></fieldset>';
		dest.appendChild(exists);
	}
	exists.childNodes[0].childNodes[1].innerHTML = 'loading...';
	xmlhttp=null
	// code for Mozilla, etc.
	if (window.XMLHttpRequest)
	{
		xmlhttp=new XMLHttpRequest()
	}
	// code for IE
	else if (window.ActiveXObject)
	{
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP")
	}
	if (xmlhttp!=null)
	{
		xmlhttp.onreadystatechange=asv_state_Change;
		try{
		xmlhttp.open("GET",url,true);
		}
		catch(e){
		alert(e)
		}
		xmlhttp.send(null);
	}
	else
	{
		alert("Your browser does not support XMLHTTP.")
	}
}

function asv_state_Change()
{			
// if xmlhttp shows "loaded"
if (xmlhttp.readyState==4)
  {
  // if "OK"
  if (xmlhttp.status==200)
    {
    // ...some code here...
    	asv_parseResponse(xmlhttp.responseText);
    }
  else
    {
    alert("Problem retrieving XML data")
    }
  }
}

function asv_cancelResults()
{
	var dest = document.getElementById('asv_amazon2'); 
	var remove = document.getElementById('asv_amazon2Results'); 
	if(remove)
	{
		dest.removeChild(remove);
	}
}
var doc;

function asv_parseResponse(response)
{
// code for IE
if (window.ActiveXObject)
  {
  doc=new ActiveXObject("Microsoft.XMLDOM");
  doc.async="false";
  doc.loadXML(response);
  }
// code for Mozilla, Firefox, Opera, etc.
else
  {
  var parser=new DOMParser();
  doc=parser.parseFromString(response,"text/xml");
  }
	
	var x=doc.documentElement;
	var dest = document.getElementById('asv_amazon2ResultsData');
	
	//check for errors
//	alert((x.getElementsByTagName('Errors')).nodeValue);
	if(x.getElementsByTagName('Errors').length > 0)
	{
	 dest.innerHTML = "no results found";
	 return;
	}
	
	
	
	var items = x.getElementsByTagName('Item');
	var itemPage = x.getElementsByTagName('ItemPage');
	var totalPages = x.getElementsByTagName('TotalPages');
	var keywords = x.getElementsByTagName('Keywords');
	var searchIndex = x.getElementsByTagName('SearchIndex');
	itemPage = itemPage[0].childNodes[0].nodeValue;
	totalPages = totalPages[0].childNodes[0].nodeValue;
	keywords = keywords[0].childNodes[0].nodeValue;
	searchIndex = searchIndex[0].childNodes[0].nodeValue;	
	
	var line = '';
	line += asv_prevnext_link(keywords, searchIndex, itemPage, totalPages);
	for(i=0; i<items.length; i++){
		var title = items[i].getElementsByTagName('Title');
		var imageURL = items[i].getElementsByTagName('SmallImage');
		var url = items[i].getElementsByTagName('DetailPageURL');
		
		var amazonHTML = '<a href="'+url[0].childNodes[0].nodeValue + '"><img src="' + imageURL[0].childNodes[0].childNodes[0].nodeValue + '" style="display: block;margin-left: auto;margin-right: auto;"/><span style="text-align: center">' + title[0].childNodes[0].nodeValue + '</span></a>' ;
		var click ='<a href="#" onclick="asv_onClick(\'' + title[0].childNodes[0].nodeValue + '\');return false;">add</a>';
		line += '<p>' + amazonHTML + '<hr style="padding: 0px; margin: 0px; height: 1px; color: #000;" />' + click + '</p>';
	}
	line+= '<hr />';
	
	line += asv_prevnext_link(keywords, searchIndex, itemPage, totalPages);
	
	
	dest.innerHTML = line;
	dest.setAttribute("style", "max-height: 500px; overflow: auto;");
}

function asv_prevnext_link(keywords, searchIndex, itemPage, totalPages)
{

	line='<p style="text-align: center;">';
	itemPage = parseInt(itemPage);
	var url ='/index.php?asv_amazon2_action=1&asv_keywords='+keywords+'&asv_searchindex='+searchIndex+'&asv_itempage=';
	var text = '';
	if(itemPage>1){
		var pItemPage = itemPage - 1;
		line += '<a href="#" onclick="asv_request(\''+url + '' + pItemPage+'\');return false;">previous</a> | ';
	}
	if(itemPage<totalPages){
		var nItemPage = itemPage + 1;
		line += '<a href="#" onclick="asv_request(\''+url + '' + nItemPage+'\');return false;">next</a>';
		
	}
	
	line+='<hr /></p>';
	return line;
}


// -->
</script>
EOF;

		echo $js;

}

function asv_safeJS($line)
{
	return str_replace("\n", "", addslashes($line));
}

class asv_Amazon
{
	var $api_key = "0Y8F1YV1N2YSGJ1MC202";
	var $contentType = "text%2Fxml";
	var $operation = "ItemSearch";
	var $service = "AWSECommerceService";
	var $baseURL = "http://ecs.amazonaws.com/onca/xml?";
	var $responseGroup = "Medium";
	var $locale;
	
	function asv_Amazon($locale)
	{
		$this->$locale = $locale;
		
	}
	
	function request($searchIndex, $keywords, $itemPage = "1")
	{
		$url = $this->buildURL(doSlash($searchIndex), doSlash($keywords), doSlash($itemPage));
		// create a new curl resource
		$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);

		// grab URL and pass it to the browser
		curl_exec($ch);

		// close curl resource, and free up system resources
		curl_close($ch);

		//return "not implemented yet";
	}
	
	function buildURL /* private */($searchIndex, $keywords, $itemPage = "1")
	{
		$url = $this->baseURL."Service=".$this->service."&Operation=".$this->operation."&SubscriptionId=".$this->api_key."&SearchIndex=".$searchIndex."&Keywords=".$keywords."&ResponseGroup=".$this->responseGroup."&ItemPage=".$itemPage;
		return $url;
	}
}
# --- END PLUGIN CODE ---

?>