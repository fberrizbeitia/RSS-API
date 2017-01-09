<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link href="styles.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

 <script>
  $( function() {
    $( "#reportDate" ).datepicker({
		dateFormat: "yy-mm-dd"
	});
	
	$( "input[type=submit]" ).button();
  } );
  	 
  </script>
  
</head>

<?php
include "updateReport.php";

if(isset($_POST['reportDate'])){
		
	$date = date_create($_POST['reportDate']);
}else{
	$date = date_create();
	}


?>
<body>
<h1>FeedAPI Update Error Report</h1>
<div id="description">
    <form action="index.php" method="post">
		    <p>View report for: <input name='reportDate' type="text" id="reportDate">
            <input type="submit" value="Show report">
            </p>
    </form>
</div>
<div id="report">
<h2>Error report for <?php echo date_format($date , 'Y-m-d');?></h2>
<?php
$path = "updates/".date_format($date , 'Y-m-d').".log";
$update = new updateReport($path);
if($update->load()){
	if(count($update->errors) > 0){
	?>
      <p><?php echo count($update->list)?> feeds were successfully updated</p>
	  <p><?php echo count($update->errors)?> errors occured during the update process on the following feeds:</p>
	  <ul>
		<?php
			foreach($update->errors as $error){
		?>
		<li><a href="<?php echo $error[2]?>" target="_blank"><?php echo $error[2]?></a></li>
        <br />
		<?php
			}
		?>
	  </ul>
	<?php
	}else{
	?>
		<p>No errors found in this update</p>
	<?php
	}
}else{
	?>
		<p>No log file was found for the requested date</p>
	<?php
}
?>
</div>

</body>
</html>