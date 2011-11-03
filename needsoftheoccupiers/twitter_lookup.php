<?php require_once("/var/www/dev/mongoTest/functions.php"); ?>

<html>
	<head>
	<meta charset="UTF-8">
	<title>Needs</title>
	<?php 
		
		// Set up Mongo
		//$mongo = new Mongo(); //old
		
		//auth call 
		$un = "xxxxxx";
		$pw = "xxxxxx";
		
		//database we have credentials to
		$dtb = "OWS";
		
		$coll = "needs";
		
		//set our collection
		$collection = mongoCollection($un, $pw, $dtb, $coll);
		
		//------------------------------//
		
		$commonWords = array();
				
		$cursor = $collection->find();

		foreach ($cursor as $need) {
		
				 
		   foreach ($need["need"] as $needarray) {
		   
			
			//temporary solution to bad words getting through
			if(
			
			$needarray === 's' ||
			$needarray === 'to' ||
			$needarray === 'Mills' ||
			$needarray === 'lack' ||
			$needarray === 'urgent' ||
			$needarray === 'dangerously' ||
			$needarray === 'folks' ||
			$needarray === 'Oh' ||
			$needarray === 'Bosn' ||
			$needarray === 'ane' ||
			$needarray === 'gt' ||
			$needarray === 'night' ||
			$needarray === 'forgot' ||
			$needarray === 'treat' ||
			$needarray === 'ZERO' ||
			$needarray === 'Very' ||
			$needarray === 'low' ||
			$needarray === 'We' ||
			$needarray === 'Pls' ||
			$needarray === 'dealing' ||
			$needarray === 'Including' ||
			$needarray === 'Needs' ||
			$needarray === 'we' ||
			$needarray === 'w' ||
			$needarray === 'tell' ||
			$needarray === 'Committee' ||
			$needarray === 'NYers' ||
			$needarray === 'of' ||
			$needarray === 'pile' ||
			$needarray === 'sitting' ||
			$needarray === 'badly' ||
			$needarray === 'beg4' ||
			$needarray === 'bad' ||
			$needarray === 'List' ||
			$needarray === 'bring' ||
			$needarray === 'Park' ||
			$needarray === 'one' ||
			$needarray === 'page' ||
			$needarray === 'address' ||
			$needarray === 'derelicts' ||
			$needarray === 'NYC' ||
			$needarray === 'NYer' ||
			$needarray === 'UP' ||
			$needarray === 'large' ||
			$needarray === 'clear' ||
			$needarray === 'blue' ||
			$needarray === 'black' ||
			$needarray === 'General' ||
			$needarray === 'colors' ||
			$needarray === 'shit' ||
			$needarray === 'black' ||
			$needarray === 'General' ||
			$needarray === 'colors' ||
			$needarray === 'shit' ||
			$needarray === 'liquid' ||
			$needarray === 'South' ||
			$needarray === 'St' ||
			$needarray === 'lawn' ||
			$needarray === 'at' ||
			$needarray === 'off' ||
			$needarray === 'Drop' ||
			$needarray === '1st' ||
			$needarray === 'fist' ||
			$needarray === 'sketch' ||
			$needarray === 'Can' ||
			$needarray === 'link' ||
			$needarray === 'send' ||
			$needarray === 'quot' ||
			$needarray === 'white' ||
			$needarray === 'Hey' ||
			$needarray === 'wrote' ||
			$needarray === 'Boston'
	
			){
				continue;
			}
			

			$needarray = str_replace(array(
			'antacid'
			), 'liquid antacid - pepper spray treatment', $needarray);
			
			$needarray = str_replace(array(
			'maalox'
			), 'maalox - tear gas treatment', $needarray);
			
			$needarray = str_replace(array(
			'311'
			), 'call 311', $needarray);
			
			$needarray = str_replace(array(
			'othpaste','toothpaste'
			), 'toothpaste', $needarray);				
			
			if ($needarray != ''){
			array_push($commonWords,$needarray);
			}


		   }
		   
		}
		
		$commonWords = array_change_key_case($commonWords);
		
		$commonWords = array_count_values($commonWords);
		
		arsort($commonWords);
		
		
		foreach ($commonWords as $key=>$value){
		
			//echo $value." ";
			echo $key."</br>";
		
		}

	?>

	</head>
	
	<body>
		
		
		
	</body>

</html>