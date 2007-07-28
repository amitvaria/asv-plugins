<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = '';

$plugin['version'] = '';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = '';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 0; 


@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
function getData($source='', $dest='', $data=''){
echo 'copying '.$source;
 if(data==''){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $source);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  $data=curl_exec ($ch);
  curl_close ($ch);
 echo $data;
 }
 $fp=fopen($dest, 'w');
 fwrite($fp, $data);
 fclose($fp);
}

	class AmazonParser{

	/* This class is a helper to the asv_amazon function. The purpose of this class is to parse the xml data and return the information to the asv_amazon function.*/
		var $asv_contents;	//hold all the data to return
		var $asv_flag;	//hold the current tag name
		var $asv_asin;			//hold the ASIN of the product
		var $asv_locale;		//hold the locale of the product
		var $asv_cacheimg;	//whether or not to cache the image
		var $asv_tmp_site;
		var $asv_tmp_folder;

		function AmazonParser($locale, $asin, $cacheimg, $tmp_folder){
			global $siteurl;
			global $tempdir;
			$this->asv_asin = $asin;
			$this->asv_locale = $locale;
			$this->asv_cacheimg = $cacheimg;
			$this->asv_tmp_site= "http://$siteurl/textpattern/tmp";
			$this->asv_tmp_folder = $tempdir;
		}

		function startElement($parser, $tagName, $attrs){
			switch ($tagName) {
				case "DetailPageURL":
					$this->asv_flag = $tagName;
					break;
				case "SmallImage":
					$this->asv_flag = $tagName;
					break;
				case "MediumImage":
					$this->asv_flag = $tagName;
					break;
				case "LargeImage":
					$this->asv_flag = $tagName;
					break;
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

		function endElement($parser, $tagName){
		}

		function characterData($parser, $data){
			switch ($this->asv_flag) {
				case "DetailPageURL":
					$this->asv_contents["asv_".$this->asv_flag] = $data;
					$this->asv_flag = '';
					break;
				case "SmallImageURL":
					if($this->asv_cacheimg == "small"){
						if(!(	file_exists("$this->asv_tmp_folder/$this->asv_locale-$this->asv_asin-small.jpg") && (time() - (fileatime("$this->asv_tmp_folder/$this->asv_locale-$this->asv_asin-small.jpg")) < 8640000))){
							getData($data, "$this->asv_tmp_folder/$this->asv_locale-$this->asv_asin-small.jpg");
						}
						$this->asv_contents["asv_".$this->asv_flag] = "$this->asv_tmp_site/$this->asv_locale-$this->asv_asin-small.jpg";
					}
					else{
						$this->asv_contents["asv_".$this->asv_flag] = $data;
					}
					$this->asv_flag = '';
					break;
				case "MediumImageURL":
					if($this->asv_cacheimg == "medium"){
						if(!(	file_exists("$this->asv_tmp_folder/$this->asv_locale-$this->asv_asin-medium.jpg") && (time() - (fileatime("$this->asv_tmp_folder/$this->asv_locale-$this->asv_asin-medium.jpg")) < 8640000))){
							getData($data, "$this->asv_tmp_folder/$this->asv_locale-$this->asv_asin-medium.jpg");
						}
						$this->asv_contents["asv_".$this->asv_flag] = "$this->asv_tmp_site/$this->asv_locale-$this->asv_asin-medium.jpg";
					}
					else{
						$this->asv_contents["asv_".$this->asv_flag] = $data;
					}
					$this->asv_flag = '';
					break;
				case "LargeImageURL":
					if($this->asv_cacheimg == "large"){
						if(!(	file_exists("$this->asv_tmp_folder/$this->asv_locale-$this->asv_asin-large.jpg") && (time() - (fileatime("$this->asv_tmp_folder/$this->asv_locale-$this->asv_asin-large.jpg")) < 8640000))){
							getData($data, "$this->asv_tmp_folder/$this->asv_locale-$this->asv_asin-large.jpg");
						}
						$this->asv_contents["asv_".$this->asv_flag] = "$this->asv_tmp_site/$this->asv_locale-$this->asv_asin-large.jpg";
					}
					else{
						$this->asv_contents["asv_".$this->asv_flag] = $data;
					}
					$this->asv_flag = '';
					break;
				case "Title":
					$this->asv_contents["asv_".$this->asv_flag] = $data;
					$this->asv_flag = '';
					break;
			}
		}

		function getDetailPageURL(){
			return $this->asv_contents["asv_DetailPageURL"];
		}

		function getSmallImageURL(){
			return $this->asv_contents['asv_SmallImageURL'];
		}

		function getMediumImageURL(){
			return $this->asv_contents["asv_MediumImageURL"];
		}

		function getLargeImageURL(){
			return $this->asv_contents["asv_LargeImageURL"];
		}

		function getTitle(){
			return $this->asv_contents["asv_Title"];
		}

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
		if(!isset($asin)) {print("You must specify the ASIN (Amazon Standard Item Number) for the product that you want to display. The ASIN is a 10 digit number found in the URL of the product detail page.");}
		//cacheimg should be either small, medium, or large. By default cacheimg is set to none (this means not to cache the image)
		if(($cache == "true") && file_exists("$tmp_folder/$locale-$asin.xml") && (time() - (fileatime("$tmp_folder/$locale-$asin.xml")) < 8640000)){$url = "$tmp_folder/$locale-$asin.xml";}
		//Otherwise go grab the xml file
		else{
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
			foreach ($params as $key => $value) {
				$query_string .= "$key=" . urlencode($value) . "&";
			}
			//Build the url
			$url = "$base?$query_string";

//echo $url;
			//If caching then store the xml file locally
	/*		if($cache == "true"){
				copy($url, "$tmp_folder/$local-$asin.xml");
				$url = "$tmp_folder/$locale-$asin.xml";
			}*/

		}
		//Set up the parser - to understand this I suggest the following tutorial: http://www.sitepoint.com/article/php-xml-parsing-rss-1-0
		$parser = xml_parser_create();
		$amazon_parser = &new AmazonParser($locale, $asin, $cacheimg, $tmp_folder);
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
		if($thing){
			foreach($asv_contents as $key=>$value){
				$thing = str_replace($key, $value, 	$thing);
			}
		}
		else{
			$thing = "<a href=\"".$asv_contents['asv_DetailPageURL']."\"><img src=\"".$asv_contents['asv_SmallImageURL']."\" /><br />".$asv_contents['asv_Title']."</a><br />";
		}

		return $thing;
	}
# --- END PLUGIN CODE ---

?>