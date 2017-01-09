<?php
class updateReport{
	public $path;
	public $list = array();
	public $errors = array();
	
	function __construct($path){
		$this->path = $path;
	}
	
	function load(){
		$handle = fopen($this->path,"r");
		if($handle !== FALSE){
			while( ($row = fgetcsv($handle,3000,"|")) !== FALSE){
				$this->list[]=$row;
				if($row[3]=='ERROR UPDATING'){
					$this->errors[] = $row;
				}
			}
		}else{
			return false;
		}
		fclose($handle);
		return true;
	}
	
	
}
?>