<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
include "config.php";
include "feedIndex.php";

$http_referer = $_SERVER['HTTP_REFERER'];
$isOriginValid = false;
$allowOriginDomain = "";	

foreach($ALLOWED_DOMAINS as $domain){	
	if(strrpos($http_referer,$domain) !== false){
		$isOriginValid = true;
		$allowOriginDomain = $domain;
	}
}

// uncomment this line if don't want to maintain a white list. This is not recommended
//$isOriginValid = true;

if(isset($_POST['feedUrl']) and $isOriginValid){	
	if($_POST['feedUrl'] != ''){
		
		$cache = new feedIndex();
		$cache->load();
		$result = $cache->search($_POST['feedUrl']);
		$channelTitle = "";
		$channelUrl = "";
		$channelDescription = "";
		
		if(count($result) > 0){
			$update = false;
			
			// check if it was updated in the last 24 hours
			$lastUpdated = new DateTime($result[0]['lastUpdated']);
			$lastUpdated->format('Y-m-d H:i:s');
			$now = new DateTime();
			$now->format('Y-m-d H:i:s');
			
			
			$interval = $lastUpdated->diff($now);
			
			if ( $interval->days > 0){
				$update = true;
			}
			
			//if the limit,  sort field or method has chane also update the file
			if(isset($_POST['sortField']) and $_POST['sortField'] != '' and $_POST['sortField'] != $result[0]['sort']['field']){
				$update = true;
			}
			
			if(isset($_POST['sortOrder']) and $_POST['sortOrder'] != '' and $_POST['sortOrder'] != $result[0]['sort']['order']){
				$update = true;

			}
			
			if(isset($_POST['sortType']) and $_POST['sortType'] != '' and $_POST['sortType'] != $result[0]['sort']['type']){
				$update = true;

			}
			
			if(isset($_POST['limit']) and $_POST['limit'] != '' and $_POST['limit'] != $result[0]['limit']){
				$update = true;
			}
					
			
			if ($update){
				$cache->getFeed($_POST['feedUrl'],$_POST['sortField'],$_POST['sortOrder'],$_POST['sortType'],$_POST['limit']);
				$cache->updateIndex($result[0]['id'],$_POST['feedUrl'],$_POST['sortField'],$_POST['sortOrder'],$_POST['sortType'],$_POST['limit']);
			}
			
			
			$feed = $BASE_FEED_URL.$result[0]['id'];
			$id = $result[0]['id'];	
				
			
		}else{
			
			$result2 = $cache->add($_POST['feedUrl'],$_POST['sortField'],$_POST['sortOrder'],$_POST['sortType'],$_POST['limit']);
			if($result2 != false){
				$cache->save();
				$feed = $BASE_FEED_URL.$result2['id'];		
				$id = $result2['id'];
			}else{
				$id = false;
			}
		}
		
		header("Access-Control-Allow-Origin: $allowOriginDomain");
	// uncomment this line (and comment out the one above) if don't want to maintain a white list. This is not recommended
	//	header("Access-Control-Allow-Origin: *");
		
		
		if($id != false){
			$objRss = new RSS($channelTitle,$channelUrl,$channelDescription,"en-us",'./');
			$objRss->parse($feed,false);
			echo $objRss->getJSON();
			$now = new DateTime();
			$log = $now->format("Y-m-d H:i:s")."|".$id.PHP_EOL;
			$name = "../cache/logs/requests/".$now->format("Y-m-d").".log";
			$fw = fopen($name,'a');
			fputs($fw,$log,strlen($log));
			fclose($fw);
			
		}else{
			echo("{}");
			$now = new DateTime();
			$log = $now->format("Y-m-d H:i:s")."| (ERROR) can't accees feed: ".$_POST['feedUrl'].PHP_EOL;
			$name = "../cache/logs/requests/".$now->format("Y-m-d").".log";
			$fw = fopen($name,'a');
			fputs($fw,$log,strlen($log));
			fclose($fw);
		}
	
	}//if($_GET['url'] != ''){
}// if(isset($_GET['url'])){


?>