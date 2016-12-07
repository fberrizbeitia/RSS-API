<?php
class atom2rss{
		
	static function toRSS($feed){
		
		$result = $feed;
		$chan = new DOMDocument(); 
		$loaded = $chan->loadXML($feed); /* load channel */
		if(!$loaded){
			//try to force convertion to utf-8
			$utf8 = utf8_encode($feed);
			$loaded2 = $chan->loadXML($utf8);
			if($loaded2){
				$result = $utf8;
			}
			
		}
		/* Validate if is a ATom feed*/
		$feedElement = $chan->getElementsByTagName('feed');
		if($feedElement->length > 0){
			$nameSpace = $feedElement->item(0)->namespaceURI;
			if($nameSpace == 'http://www.w3.org/2005/Atom'){
				$sheet = new DOMDocument(); 
				$sheet->load('atom2rss.xsl'); 
				$processor = new XSLTProcessor();
				$processor->registerPHPFunctions();
				$processor->importStylesheet($sheet);
				$result = $processor->transformToXML($chan); 
			}
		}//if($feedElement->length > 0){
		return $result;
	}
	
	
}


?>