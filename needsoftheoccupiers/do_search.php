<?php require_once("/var/www/dev/mongoTest/functions.php"); ?>


<?php
//if we got something through $_POST
if (isset($_POST['search'])) {


	//auth call 
	$un = "xxxxxxx";
	$pw = "xxxxxxx";
	
	//database we have credentials to
	$dtb = "OWS";
	
	$coll = "needs";
	
	//set our collection
	$collection = mongoCollection($un, $pw, $dtb, $coll);
	
	
	
	$cursor = $collection->find(array("need" => $_POST['search']));
	
	foreach ($cursor as $need) {
	   echo "<b>Occupy Location </b>".$need["location"] . "</br>";
	   echo "<b>Tweeted by </b>".$need["user"]."</br>";
	   echo "<b>Raw Tweet </b>".$need["rawtext"]."</br>"; 
	   echo "<b>Needs </b>";
	   
	   foreach ($need["need"] as $needarray) {
	   	echo " ".$needarray." ";
	   }
	   echo "</br></br>";
	}
			
    
}

?>