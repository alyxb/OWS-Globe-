<?php require_once("/var/www/dev/mongoTest/functions.php"); ?>

<html>
	<head>
	<meta charset="UTF-8">
	<title>Votes</title>
	<?php 
		
		// Set up Mongo
		//$mongo = new Mongo(); //old
		
		//auth call 
		$un = "xxxxxx";
		$pw = "xxxxxx";
		
		//database we have credentials to
		$dtb = "OWS";
		
		$coll = "peoplemic";
		
		//set our collection
		$collection = mongoCollection($un, $pw, $dtb, $coll);
		
		//------------------------------//
		
				
		$cursor = $collection->find();

	//	$cursor = iterator_to_array($cursor);
		
		//var_dump($cursor);
		
		
		foreach ($cursor as $need) {
		
	
		
			//echo $value." ";
			echo $need."</br>";
		
		}


	
	?>

	</head>
	
	<body>
		
		
		
	</body>

</html>