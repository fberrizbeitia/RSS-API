<?php
include "RSS.php";
include "config.php";

class feedIndex {

	public $feeds;
	
	public function load(){
		$loadedRss = file_get_contents('index.xml');		
		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML($loadedRss);
		
		$feeds = $xmlDoc->getElementsByTagName('feed');
		
		foreach($feeds as $feed){
			$feedArr = array("id"=>'',"url"=>'',"lastUpdated"=>'',"limit"=>'',"sort"=>array("field"=>'',"type"=>'',"order"=>''));
			foreach($feed->childNodes as $i){
				switch($i->nodeName){
					case 'id':
						$feedArr["id"] = $i->nodeValue;
					break;
					case 'url':
						$feedArr["url"] = $i->nodeValue;
					break;
					case 'lastUpdated':
						$feedArr["lastUpdated"] = $i->nodeValue;
					break;
					
					case 'limit':
						$feedArr["limit"] = $i->nodeValue;
					break;
					
					case 'sort':
						$sortElements = $i->childNodes;				
						foreach($sortElements as $se){
							switch($se->nodeName){
								case 'field': 
									$feedArr["sort"]["field"] = $se->nodeValue;
								break;
								case 'type':
									$feedArr["sort"]["type"] = $se->nodeValue;
								break;
								case 'order':
									$feedArr["sort"]["order"] = $se->nodeValue;
								break;
							}
						}
					break;
				}//switch($i->nodeName){
			} //foreach($feed->childNodes as $i){
				
			$this->feeds[] = $feedArr;	
		}//foreach($feeds as $feed){
	}//public function load(){
	
	function search($url='',$sortField='',$sortOrder='',$limit='',$lastUpdated='',$id=''){
		$returnItems = array();
		foreach($this->feeds as $feed){
			$found = true;
			if($id != '' and $feed["id"] != $id){
				$found = false;
			}
			if($url != '' and trim($feed["url"]) != trim($url)){
				$found = false;
			}
			if($lastUpdated != '' and $feed["lastUpdated"] != $lastUpdated){
				$found = false;
			}
			if($limit != '' and $feed["limit"] != $limit){
				$found = false;
			}
			if($sortField != '' and $feed["sort"]["field"] != $sortField){
				$found = false;
			}

			if($sortOrder != '' and $feed["sort"]["order"] != $sortOrder){
				$found = false;
			}
			
			if($found){
				$returnItems[] = $feed;
			}

		}
		return $returnItems;
	}
	
	function updateIndex($id='',$url='',$sortField='',$sortOrder='',$sortType='',$limit=''){
		for($i = 0; $i < count($this->feeds); $i++){
			if($id != '' and $this->feeds[$i]["id"] == $id){
				if($limit != ''){$this->feeds[$i]["limit"] = $limit;}
				if($sortField != ''){$this->feeds[$i]["sort"]["field"] = $sortField;}
				if($sortOrder != ''){$this->feeds[$i]["sort"]["order"]  = $sortOrder;}
				if($sortType != ''){$this->feeds[$i]["sort"]["type"] = $sortType;}			
				$this->feeds[$i]["lastUpdated"] = date(DATE_RFC2822);
				$this->save();
			}			

		}
	}
	
	function getFeed($url,$sortField='',$sortOrder='',$sortType='',$limit=''){
		if($url != ''){
			$id = md5($url).".xml";				
			$objRss = new RSS('',$url,'',"en-us",'./');
			$objRss->parse($url);
			if(count($objRss->items) >= 1){				
				if($sortField != '' and $sortOrder!='' and $sortType!=''){
					$objRss->sortBy($sortField,$sortType,$sortOrder);
				}
					
				if($limit != ''){
					$objRss->cut($limit);	
				}
					
				$objRss->filename = "../cache/feeds/".$id;
				$objRss->echoRSS();
			
				return true;				
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	function add($url='',$sortField='',$sortOrder='',$sortType='',$limit=''){
		if($url != ''){
			//var_dump($url,$sortField,$sortOrder,$sortType,$limit);
			$id = md5($url).".xml";
			$test = $this->search($url,$sortField,$sortOrder,$limit,$id);
			if(count($test) == 0){
				if($this->getFeed($url,$sortField,$sortOrder,$sortType,$limit)){				
					$elem = array("id"=>$id,"url"=>$url,"lastUpdated"=>date(DATE_RFC2822),"limit"=>$limit,"sort"=>array("field"=>$sortField,"type"=>$sortType,"order"=>$sortOrder));
					$this->feeds[] = $elem;
					return $elem;
				}else{
					false;
				}
			}else{
				return $test;
			}
		}else{
			return false;
		}
	}//function add($url='',$sortField='',$sortOrder='',$sortType='',$limit=''){
	
	function save(){
		$dom = new DOMDocument('1.0', 'utf-8');
		$root = $dom->createElement('feeds');
		$dom->appendChild($root);
		
		foreach($this->feeds as $feed){
			$feedNode = $dom->createElement("feed");
				$urlCD = $dom->createCDATASection($feed["url"]);
				$urlNode = $dom->createElement("url");
				$urlNode->appendChild($urlCD);
			$feedNode->appendChild($urlNode);
			$feedNode->appendChild($dom->createElement("limit",$feed["limit"]));
			$feedNode->appendChild($dom->createElement("id",$feed["id"]));
			$feedNode->appendChild($dom->createElement("lastUpdated",$feed["lastUpdated"]));
				$sortNode = $dom->createElement("sort");
				$sortNode->appendChild($dom->createElement("field",$feed["sort"]["field"]));
				$sortNode->appendChild($dom->createElement("type",$feed["sort"]["type"]));
				$sortNode->appendChild($dom->createElement("order",$feed["sort"]["order"]));
			$feedNode->appendChild($sortNode);
			$root->appendChild($feedNode);
		}
		
		$dom->save("index.xml");
	}
		
}//class feedIndex{
	
?>

