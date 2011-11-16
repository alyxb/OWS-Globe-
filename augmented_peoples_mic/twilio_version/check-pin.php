<?php
    // check-pin.php
    header('Content-type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8" ?>';
    
    require_once("/var/www/dev/mongoTest/functions.php");
 
    $pin = $_REQUEST['Digits'];
  	$caller = $_REQUEST['From'];

    //new random number string for PIN
	$micNumber = rand(1000,9999); 
	$micNumber = $micNumber.'';
	
	//-------- Set up Mongo DB----------//
	
	//auth call 
	$un = "xxxxxxxxx";
	$pw = "xxxxxxxxx";
	
	//database we have credentials to
	$dtb = "OWS";
	
	$coll = "miccheck";
	
	//set our collection
	$collection = mongoCollection($un, $pw, $dtb, $coll);
	
	//---------------------------------//

?>
<Response>
    <?php
    
        //creating new mic if user selected 1 on menu
		if ($pin == '1')
        {
        	//checking to see if random generated PIN is already in use
			$cursor = $collection->findOne(array('pin'=>$micNumber));
	
			//checking to see if PIN already in database, generating random until unique
			while ($cursor['pin'] == $micNumber){ 
				$micNumber = rand(1000,9999);
				$micNumber = $micNumber.'';
			}
	
			//----New JSON Object------//
			$mic = array(
				'pin' =>$micNumber
			);
			//------------------------//

			//inserting mic into DB
			insertMic($mic,$collection);			
        	
  			echo '<Say>Your mike check number is '.$micNumber.'</Say>';
            echo '<Dial>';
            echo '<Conference muted="false" startConferenceOnEnter="false" endConferenceOnExit="false" beep="false">'.$micNumber.'</Conference>';
            echo '</Dial>';
        }
        
        //if user typed a PIN at first menu...
		else 
        {
        
			//check to see if PIN is valid in DB
	        $cursor = $collection->findOne(array('pin'=>$pin)); 

				//if PIN exists, connect
				if ($cursor['pin'] == $pin){
					echo '<Say>Connecting to mike check number '.$pin.'</Say>';
										
		            echo '<Dial action="gather_conference.php">';
		            echo '<Conference muted="true" beep="false">'.$pin;
					//gather DTMF here doesn't work!! no support for this in Twilio
			    		    echo '<Gather action="/gather_conference.php" method="POST" numDigits="1" timeout="999999">';
			        			echo '<Say>Press 1</Say>';
			       			echo '</Gather>';
			        echo '</Conference>';
		            echo '</Dial>'; 
				}
				
				else {
		            // Invalid PIN entered. Go back to main menu
		            echo '<Say>Sorry, that PIN is not valid.</Say>';
		            echo '<Redirect>incoming-call.xml</Redirect>';
		        }	
        }

		//insert PIN into DB
		function insertMic($json,$collection){
			$safe_insert = true;
			$collection->insert($json,$safe_insert);
		}
		
		function micListen(){
			return $output;
		}
    ?>
</Response>


