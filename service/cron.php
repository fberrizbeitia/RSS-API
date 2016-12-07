<?php
include "feedIndex.php";

$cache = new feedIndex();
$cache->load();
$log = "";
foreach($cache->feeds as $feed){
	
	if($cache->getFeed($feed['url'],$feed['sort']['field'],$feed['sort']['order'],$feed['sort']['type'],$feed['limit'])){
		$cache->updateIndex($feed['id']);
		$now = new DateTime();
		$log .= $now->format("Y-m-d H:i:s")."|".$feed['id']."|".$feed['url']."|UPDATED".PHP_EOL;	
	}else{
		$now = new DateTime();
		$log .= $now->format("Y-m-d H:i:s")."|".$feed['id']."|".$feed['url']."|ERROR UPDATING".PHP_EOL;	
	}

}//foreach($feeds as $feed){

$now = new DateTime();
$name = "../cache/logs/updates/".$now->format("Y-m-d").".log";
$fw = fopen($name,'w');
fputs($fw,$log,strlen($log));
fclose($fw);

?>