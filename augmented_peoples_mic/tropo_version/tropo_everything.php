<?php
 

require 'tropo.class.php';
require 'lib/limonade.php';
require '/var/www/dev/mongoTest/functions.php';


	

dispatch_post('/', 'app_start');
function app_start() {

    $session = new Session();

	$caller = $session->getFrom();
	
	$id = $session->getcallid();

	$caller = $caller['id'];
	
	
	//-------- Set up Mongo----------//

	//auth call 
	$un = "xxxxxxxx";
	$pw = "xxxxxxxx";
	
	//database we have credentials to
	$dtb = "OWS";
	
	$coll = "micsession";
	
	//set our collection
	$collection = mongoCollection($un, $pw, $dtb, $coll);
				
	//------------------------------//
	

	//----New JSON Object------//
	$newPerson = array(
		'sessionID' =>$id,
		'admin'=>$caller
	);
	
	insertMic($newPerson,$collection);	

	//------------------------//
	

	$tropo = new Tropo();

    $options = array("choices" => "[1-4 DIGITS]", "name" => "digits", "timeout" => 60, "mode" => "dtmf", "terminator" => "#");
 
    $tropo->ask("Welcome to the people's mike. To start a new mike, press 1 and then pound, otherwise enter a four digit mike PIN", $options);
  
    $tropo->on(array("event" => "continue", "next" => "test.php?uri=continue"));
  
    $tropo->RenderJson();
    
   
   
}
  
dispatch_post('/continue', 'app_continue');
function app_continue() {
  	

    
    $result = new Result();
     
    $pin = $result->getValue();
    
	$id = $result->getcallid();
	
	

	//-------- Set up Mongo----------//

	//auth call 
	$un = "xxxxxxxx";
	$pw = "xxxxxxxx";
	
	//database we have credentials to
	$dtb = "OWS";
	
	$coll = "miccheck";
	
	//set our collection
	$collection = mongoCollection($un, $pw, $dtb, $coll);
				
	//------------------------------//
		
	$admin = "123456789";
   
    if ($pin == '1'){
    
    	$micNumber = rand(1000,9999); 
		$micNumber = $micNumber.'';
    
    	$cursor = $collection->findOne(array('pin'=>$micNumber));


		while ($cursor['pin'] == $micNumber){
		
			$micNumber = rand(1000,9999);
			$micNumber = $micNumber.'';
		}
	
		
		//----New JSON Object------//
		$mic = array(
			'pin' =>$micNumber,
			'admin'=>$admin,
			'callIDs'=>array($id),
			'yes'=>array(),
			'no'=>array()
		);
		
		insertMic($mic,$collection);	

		//------------------------//
		

	    $tropo = new Tropo();
	    
	    $pinSep = wordwrap($micNumber, 1, " ", true); //making PIN speakable
	    
	    $tropo->say('Your mike check number is '.$pinSep);
	 
		$tropo->conference(null, array("name" => "conference", "id" => $micNumber, "mute" => false, "terminator" => "#"));
		 
		$tropo->say("We hope you had fun, call back soon!");
		     
		$tropo->RenderJson();

    }
    
    else 
        {
        
	        $cursor = $collection->findOne(array('pin'=>$pin));
	        

			if ($cursor['pin'] == $pin){
			
				$tropo = new Tropo();
				
				
				
				$newdata = array('$push' => array('callIDs' => $id));
				
				//$upsert = array("upsert" => $safe_insert);
				
				$collection->update(array('pin' => $pin), $newdata);
				
				
				

				
				$pinSep = wordwrap($pin, 1, " ", true); //making PIN speakable

				$tropo->say('Connecting to mike check number '.$pinSep);
				
				
				$tropo->conference(null, array("name" => "conference", "id" => $pin, "mute" => true, "terminator" => "#"));
				
				$tropo->on(array("event" => "continue", "next" => "test.php?uri=vote"));

		 
				//$tropo->say("We hope you had fun, call back soon!");
				     
				$tropo->RenderJson();
				
			}
			
			else {
			
				$tropo = new Tropo();
				
				$tropo->say('That is not a current mike check number');

				$tropo->on(array("event" => "continue", "next" => "test.php"));
				$tropo->RenderJson();	
	        }
		        

			
    	}
    	
    	

}


dispatch_post('/vote', 'vote_tally');
function vote_tally() {


	$tropo = new Tropo();
					
	$options = array("choices" => "[1 DIGIT]", "name" => "digit", "timeout" => 99999, "mode" => "dtmf");

    $tropo->ask("1 for yes, 0 for no, pound for vote stats", $options);
    
    $tropo->on(array("event" => "continue", "next" => "test.php?uri=vote_collect"));

	$tropo->RenderJson();	


}


dispatch_post('/vote_collect', 'vote_collected');
function vote_collected() {


	$result = new Result();
	
	$id = $result->getcallid();

    $vote = $result->getValue();
    

	//-------- Set up Mongo----------//

	//auth call 
	$un = "xxxxxxxx";
	$pw = "xxxxxxxx";
	
	//database we have credentials to
	$dtb = "OWS";
	
	$coll = "miccheck";
	
	//set our collection
	$collection = mongoCollection($un, $pw, $dtb, $coll);
				
	//------------------------------//


    
    $cursor = $collection->findOne(array('callIDs'=>$id));

	$pin = $cursor['pin'];
    
   $yes = 'yes';
   $no = 'no';
    
    
	$tropo = new Tropo();
	
	if ($vote == "1"){
	
		//$newyes = $cursor['yes']+1;
	
		$newdata = array('$push' => array('yes' => $yes));
						
		$collection->update(array('pin' => $pin), $newdata);
		
		$tropo->say('you voted yes');
	
	
	}
	
	else if ($vote == "0"){
	
		$newdata = array('$push' => array('no' => $no));
						
		$collection->update(array('pin' => $pin), $newdata);
			
		$tropo->say('you voted no');
	
	}
	
	else if ($vote == "9"){
	
		$yesCount=0;
		$noCount=0;
		
		foreach ($cursor['yes'] as $key=>$value){
			
			$yesCount++;
		
		}
		
		foreach ($cursor['no'] as $key=>$value){
			
			$noCount++;
		
		}
		
	
		$tropo->say('the current vote is '.$yesCount.' yes and '.$noCount.'no');
	
	}
	
	
	else {
	
	$tropo->say('please try again');
	
	}
				
	
	
	$tropo->on(array("event" => "continue", "next" => "test.php?uri=return_conf"));

	
	$tropo->RenderJson();	


}


dispatch_post('/return_conf', 'conf');
function conf() {

	
	$result = new Result();
	
	$id = $result->getcallid();
	


	//-------- Set up Mongo----------//

	//auth call 
	$un = "xxxxxxxx";
	$pw = "xxxxxxxx";
	
	//database we have credentials to
	$dtb = "OWS";
	
	$coll = "miccheck";
	
	//set our collection
	$collection = mongoCollection($un, $pw, $dtb, $coll);
				
	//------------------------------//
	
	$cursor = $collection->findOne(array('callIDs'=>$id));


	$micpin = $cursor['pin'];
	

		
		$tropo = new Tropo();
				
		
		$tropo->conference(null, array("name" => "conference", "id" => $micpin, "mute" => true, "terminator" => "#"));
		
		$tropo->on(array("event" => "continue", "next" => "test.php?uri=vote"));

 
		//$tropo->say("We hope you had fun, call back soon!");
		     
		$tropo->RenderJson();



}



  
run();



		function insertMic($json,$collection){
		
			$safe_insert = true;
			$collection->insert($json,$safe_insert);
		}
		
		function micListen(){
		
			return $output;
		
		}
		
		function mongoConnect($collection){
		
			//-------- Set up Mongo----------//
	
			//auth call 
			$un = "xxxxxxxx";
			$pw = "xxxxxxxx";
			
			//database we have credentials to
			$dtb = "OWS";
			
			$coll = "miccheck";
			
			//set our collection
			$collection = mongoCollection($un, $pw, $dtb, $coll);
			
			return $collection;
			
			//------------------------------//
		
		}


 
?>