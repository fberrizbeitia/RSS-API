<?php
include "get_fcontent.php";
include "atom2rss.php";

class RSS {
	public $basePath = '';
	public $filename ='';
	public $title = '';
	public $link = '';
	public $description = '';
	public $language = '';
	public $image = '';
	public $namespaces = array();
	public $items = array();
	
	function __construct($title='',$link='',$description='',$language='',$basePath='',$filename='',$namespaces=''){
		$this->title = $title;
		$this->description = $description;
		$this->language = $language;
		$this->link = $link;
		$this->basePath = $basePath;
		$this->filename = $filename;
	}
	
	public function addItem($title='',$description='',$link='',$pubdate='',$media=array(),$guid='',$author='',$category='',$extension=array()){
		$this->items[] = array("index"=>0,"title"=>$title,"description"=>$description,"link"=>$link,"pubDate"=>$pubdate,"media"=>$media,"guid"=>$guid,"author"=>$author,"category"=>$category,"extension"=>$extension);
	}
	
	public function getJSON(){
		return json_encode($this->items);
	}
	
	private function getExtendedValue($key,$itemElement){

		foreach($itemElement['extension'] as $ee){
			if($ee['name'] == $key){
				return $ee['value'];
			}
		}
		return false;
	}
	
	public function sortBy($field,$type='numeric',$order){
		//Pseudo
		// Add a index key to the items array
		//	If the index data type is a date, normalize first
		// usort the array using that key
		$i = 0;
		foreach($this->items as $item){
			//extract the proper value
			$indexVal = 0;
			if(array_key_exists($field,$item)){
				$indexVal = $item[$field];
			}else{
				//look in the extension
				$indexVal = $this->getExtendedValue($field,$item);
			} //}else{
			
			if(strtolower($type) == 'date'){
				if(is_numeric($indexVal)){ // is year only
					$indexVal = strtotime($indexVal."-01-01");
				}else{
					$indexVal = strtotime($indexVal);
				}
			}
			//now add the new idex value to the item element as a column
			$this->items[$i]['index']=$indexVal;
			$i++;
		}//foreach($this->items as $item){
		
		if(strtolower($order) == 'asc'){
			usort($this->items,function($a, $b){
				if(is_numeric($a['index']) and is_numeric($b['index'])){
					return $a['index'] - $b['index'];
				}else{
					return strcasecmp($a['index'],$b['index']);
				}
			});	
		}else{
			usort($this->items,function($a, $b){
				if(is_numeric($a['index']) and is_numeric($b['index'])){
					return $b['index'] - $a['index'];
				}else{
					return strcasecmp($b['index'],$a['index']);
				}
			});
		}
		
			
	} //public function sortBy($field,$type='numeric',$order){
	
	public function cut($length){
		if(count($this->items) > $length){
			$this->items = array_slice($this->items,0,$length);
		}
	}
	
	private function checkTagName($tagname){
		if($tagname == ''){
			return 'noname';
		}
		// Tagnames must start with a Letter
		if (ctype_alpha($tagname[0])){
			return $tagname;	
		}else{
			$shortened = substr($tagname,1,count($tagname));
			return $shortened;
		}			
	}
	
	private function stripInvalidXml($value) {
		$ret = "";
		$current;
		if (empty($value)) {
			return $ret;
		}
		$length = strlen($value);
		for ($i=0; $i < $length; $i++) {
			$current = ord($value{$i});
			if (($current == 0x9) || ($current == 0xA) || ($current == 0xD) || (($current >= 0x20) && ($current <= 0xD7FF)) || (($current >= 0xE000) && ($current <= 0xFFFD)) || (($current >= 0x10000) && ($current <= 0x10FFFF))) {
					$ret .= chr($current);
			}
			else {
				$ret .= "";
			}
		}
		return $ret;
	}
	
	public function parse($feed,$showlog = 0){
		if ($showlog) { print_r('Start Parsing .............................................. '.PHP_EOL);}		
		$loadedRss = get_fcontent( $feed );		
		
		if($loadedRss[1]['http_code'] == 200){
			if ($showlog) { print_r('http code '.$loadedRss[1]['http_code'].' Succefully loaded file at URL: '.$loadedRss[1]['url']. PHP_EOL);}
			$xmlDoc = new DOMDocument();
			$cleanXML = $this->stripInvalidXml($loadedRss[0]);
			$status = $xmlDoc->loadXML(atom2rss::toRSS($cleanXML));
			$rssInfo =  $xmlDoc->getElementsByTagName('rss')->item(0);	
			// get the name spaces
	
			$xpath = new DOMXPath($xmlDoc);
			$context = $xmlDoc->documentElement;
			foreach( $xpath->query('namespace::*', $context) as $node ) {
				$NSname = explode(':',$node->nodeName);
				if(count($NSname) > 1){
					if($NSname[1] != "xml"){
					$this->namespaces[] = array("name"=>$NSname[1],"uri"=>$node->nodeValue);
					}
				}else{
					if ($showlog) { print_r('Error parsing namespace '.$node->nodeName. PHP_EOL);}
				}
			}
			
			$items = $xmlDoc->getElementsByTagName('item');
			$cont = 0;
			
			if($items->length == 0){
				// try to check for RDF vocabulary
				$items = $xmlDoc->getElementsByTagName('rss:items');	
				}
				
			if ($showlog) { print_r($items->length.' Items found in feed '.PHP_EOL);}	
			
			foreach ($items as $item){
				$rssItem = array('title'=>'','description'=>'','link'=>'','author'=>'','category'=>'','media'=>'','guid'=>'','pubDate'=>'','extension'=>array());
				foreach($item->childNodes as $i){
				   switch($i->nodeName){
					 case 'title':
						$rssItem['title'] = $i->nodeValue;
						break;
					 
					 case 'description':
						$rssItem['description'] = strip_tags( $i->nodeValue );
						break;
						
					 case 'link':
						$rssItem['link'] = $i->nodeValue;
						break;
						
					case 'pubDate':
						$rssItem['pubDate'] = $i->nodeValue;
						break;
					
					case 'guid':
						$rssItem['guid'] = $i->nodeValue;
						break;
					
					case 'author':
						$rssItem['author'] = $i->nodeValue;
						break;
					
					case 'category':
						$rssItem['category'] = $i->nodeValue;
						break;
					
					case 'enclosure':
						$rssItem['media'] = array('url'=>$i->getAttribute('url'),'type'=>$i->getAttribute('type'));
						break;
					
					case 'media:content':
						$rssItem['media'] = array('url'=>$i->getAttribute('url'),'type'=>$i->getAttribute('type'));
						break;
					
					case 'rss:title':
						$rssItem['title'] = $i->nodeValue;
						break;
						
					case 'rss:description':
						$rssItem['description'] = strip_tags( $i->nodeValue );
						break;
						
					case 'rss:link':
						$rssItem['link'] = $i->nodeValue;
						break;
						
					case 'rss:pubDate':
						$rssItem['pubdate'] = $i->nodeValue;
						break;
					
					default:
						if($i->nodeName != "#text"){
							$rssItem['extension'][] = array('name'=>$i->nodeName,"value"=>$i->nodeValue);
						}
					break;
		
					}//switch($i->nodeName){
						
				}//foreach($item->childNodes as $i){

				$this->addItem($rssItem['title'],$rssItem['description'],$rssItem['link'],$rssItem['pubDate'],$rssItem['media'],$rssItem['guid'],$rssItem['author'],$rssItem['category'],$rssItem['extension']);	
				if ($showlog) { print_r("Adding item with title: ".$rssItem['title'].PHP_EOL);}
			
			}	//foreach ($items as $item){
		}else{//if($loadedRss[1]['http_code'] == 200){
			if ($showlog) { print_r('http code '.$loadedRss[1]['http_code'].' Error loading file at URL: '.$loadedRss[1]['url']. PHP_EOL);}		
		}
	}
	

	public function echoRSS($showlog = false){
		
		if ($showlog) { print_r('Start Writing ...................................'.PHP_EOL);}
		
	//	var_dump($this->items);
		
		$dom = new DOMDocument('1.0', 'utf-8');
		$root = $dom->createElement('rss');
		$dom->appendChild($root);
		
		if( count($this->namespaces) > 0 ){
			foreach($this->namespaces as $ns){
				try {
					$root->appendChild($dom->createAttribute("xmlns:".$ns["name"]))->appendChild($dom->createTextNode($ns["uri"]) );
				} catch (Exception $e) {
					if ($showlog) { print_r ('Exception captured: '.$e->getMessage().PHP_EOL );}
				}
				
			}
		}
			
		$version = $dom->createAttribute('version');
		$version->value = '2.0';
			
		$root->appendChild($version);
				$channel = $dom->createElement('channel');
					
					$channel->appendChild($dom->createElement('title',$this->title));	
					$channel->appendChild($dom->createElement('description',$this->description));
					
					if($this->filename != ""){
						$channel->appendChild($dom->createElement('link',$this->filename));
					}else{
						$link = $dom->createElement('link');
						$link->appendChild($dom->createCDATASection($this->link));
						$channel->appendChild($link);
					}
					
					$channel->appendChild($dom->createElement('pubDate',date(DATE_RFC2822)));
					$channel->appendChild($dom->createElement('language',$this->language));
					
				$root->appendChild($channel);
				
				
				foreach($this->items as $item){
					
					if ($showlog) { print_r('Writing item: '.$item['title'].PHP_EOL);}
					
					$itemElem = $dom->createElement('item');
						
						$titleCdata = $dom->createTextNode($item['title']);
						$titleElem = $dom->createElement('title');
						$titleElem->appendChild($titleCdata);
					$itemElem->appendChild($titleElem);
					
						$descriptionCdata = $dom->createCDATASection($item['description']);
						$descriptionElem = $dom->createElement('description');
						$descriptionElem->appendChild($descriptionCdata);
					$itemElem->appendChild($descriptionElem);
					
						$itemLink = $dom->createElement('link');
						$linkText = $dom->createCDATASection($item['link']);
						$itemLink->appendChild($linkText);	
					$itemElem->appendChild($itemLink);
					
					if($item['author'] != ''){
						$itemElem->appendChild($dom->createElement('author',$item['author']));
					}
					
					if($item['guid'] != ''){
						$guidCD = $dom->createCDATASection($item['guid']);
						$guidElem = $dom->createElement('guid');
						$guidElem->appendChild($guidCD);
						$itemElem->appendChild($guidElem);
					}
					
					if($item['pubDate'] != ''){
						$itemElem->appendChild($dom->createElement('pubDate',$item['pubDate']));
					}
					
					if($item['category'] != ''){
						$itemElem->appendChild($dom->createElement('category',$item['category']));
					}
					
					if(isset($item['media']['url']) and isset($item['media']['type'])){
						$imageNode = $dom->createElement('enclosure');
							$urlAtr = $dom->createAttribute('url');
							$urlAtr->value = htmlentities($item['media']['url']);
							$imageNode->appendChild($urlAtr);
							
							$imgType = $dom->createAttribute('type');
							$imgType->value = $item['media']['type'];
							$imageNode->appendChild($imgType);				
							
							$lengthAttr = $dom->createAttribute('length');
							$lengthAttr->value = "0";
							$imageNode->appendChild($lengthAttr);
						$itemElem->appendChild($imageNode);
					}
										
					if(count($item['extension'] > 0)){
					//attach the extended elements	
						foreach($item['extension'] as $extendedElement){
							//just in case create a cdata node to accomodate any type of content
							$cdExtNode = $dom->createCDATASection($extendedElement['value']);
							//echo $extendedElement['name'];
							$extNode = $dom->createElement($this->checkTagName($extendedElement['name']));
							$extNode->appendChild($cdExtNode);
							$itemElem->appendChild($extNode);
						}
					}
					
					$channel->appendChild($itemElem);
				}

		if($this->filename != ''){
			$dom->save($this->filename);
		}else{
			header('Content-Type: application/xml; charset=utf-8');
			print $dom->saveXML();
		}

	}
	
}

?>