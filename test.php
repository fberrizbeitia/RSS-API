<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RSS API</title>
<script   src="https://code.jquery.com/jquery-1.11.3.js"   integrity="sha256-IGWuzKD7mwVnNY01LtXxq3L84Tm/RJtNCYBfXZw3Je0="   crossorigin="anonymous"></script>
<script src="lib/feed.js"></script>


<style type="text/css">
body {
	font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #666;
}
</style>
</head>

<body>
<h1>RSS Feed API</h1>
<hr />
<table width="800" border="0" align="center" cellpadding="10" cellspacing="0">
  <tr>
    <td bgcolor="#EEE">
   	  <form id="form1" name="form1" method="post" action="test.php">
   	    <p>
   	      <label>Feed URL
   	        <?php
            if (isset($_POST['url'])){
				$urlval = $_POST['url'];
			}else{
				$urlval = '';
			}
			?>
            
   	        
   	        <input name="url" type="text" id="url" value="<?php echo $urlval?>" size="100" />
          </label>
        </p>
   	    <p>
   	      <label>Sort By
   	        <input type="text" name="sortBy" id="sortBy" />
          </label>
   	      <label>Sort Order
   	        <select name="sortOrder" id="sortOrder">
   	          <option value="asc">asc</option>
   	          <option value="desc" selected="selected">desc</option>
            </select>
          </label>
   	      <label>Sort data type
   	        <select name="sortType" id="sortType">
   	          <option selected="selected">Numeric</option>
   	          <option>Date</option>
   	          <option>String</option>
  </select>
          </label>
        </p>
   	    <p>
   	      <label>Number of items
   	        <input name="limit" type="text" id="limit" value="5" />
          </label>
   	      <label>Description Lenght
   	        <input name="descriptionLenght" type="text" id="descriptionLenght" value="300" />
          </label>
   	      <label>Show Images
          <select name="showImages" id="showImages">
            <option value="1">yes</option>
            <option value="0" selected="selected">no</option>
          </select>
        </label>
   	    </p>
   	    <p>
   	      <input type="submit" name="Try" id="Try" value="Try" />
        </p>
    </form></td>
  </tr>
  <tr>
    <td><div id='feedCont'></div></td>
  </tr>
</table>



</body>

</html>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	echo ("<script>
		var test = new rss();
		test.displayAsinc('".$_POST['url']."','feedCont', '".$_POST['sortBy']."','".$_POST['sortType']."','".$_POST['sortOrder']."',".$_POST['limit'].",".$_POST['descriptionLenght'].",".$_POST['showImages'].");
	</script>
");		 
}
?>
