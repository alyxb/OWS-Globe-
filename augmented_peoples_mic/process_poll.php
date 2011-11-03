<?php require_once("/var/www/dev/mongoTest/functions.php"); ?>

<?php

	//------------MongoDB Connect---------//
				
	//auth call 
	$un = "xxxxxx";
	$pw = "xxxxxx";
	
	//database we have credentials to
	$dtb = "OWS";
	
	$coll = "peoplemic";
	
	//set our collection
	$collection = mongoCollection($un, $pw, $dtb, $coll);
	
	//---------------------------------------------//
				
	$userPhone = "12345678";

	// if the caller pressed anything but 1 or 2 send them back
	
	/*
	if($_REQUEST['Digits'] == '8' || $_REQUEST['Digits'] == '9' || $_REQUEST['Digits'] == '0') {
		header("Location: hello-monkey.php");
		die;
	}
	*/
	
	//store phone number 
	//store conference number ---> look it up by typing in 
	
	
	if ($_REQUEST['Digits'] == '1') {
		//----New JSON Object------//			
		$vote = array(
			'signal' =>'agree'
		);
		insertVoter($vote,$collection);	
		$say = 'Agree.';
					
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '2') {
		//----New JSON Object------//			
		$vote = array(
			'signal' =>'unsure'
		);
		insertVoter($vote,$collection);	
		$say = 'Unsure.';					
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '3') {
		//----New JSON Object------//			
		$vote = array(
			'signal' =>'disagree'
		);
		insertVoter($vote,$collection);	
		$say = 'Disagree.';					
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '4') {
		//----New JSON Object------//			
		$vote = array(
			'signal' =>'pointofprocess'
		);
		insertVoter($vote,$collection);	
		$say = 'Point of Process.';					
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '5') {
		//----New JSON Object------//			
		$vote = array(
			'signal' =>'pointofinformation'
		);
		insertVoter($vote,$collection);	
		$say = 'Point of Information.';				
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '6') {
		//----New JSON Object------//			
		$vote = array(
			'signal' =>'wrapitup'
		);
		insertVoter($vote,$collection);		
		$say = 'Wrap it up.';				
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '7') {
		//----New JSON Object------//			
		$vote = array(
			'signal' =>'block'
		);
		insertVoter($vote,$collection);		
		$say = 'Block.';				
	//---------------------------------------//
	}

	else {
		$say = "Try that again.";
	}
	
	// @end snippet
	// @start snippet
	$response = new Services_Twilio_Twiml();
	$response->say($say);
	//$response->hangup();
	header('Content-Type: text/xml');
	print $response;
	// @end snippet
?>