<?php
 

require 'tropo.class.php'; //standard tropo class
require 'lib/limonade.php'; //standard limonade framework
require '/var/www/dev/mongoTest/functions.php'; //mongo authentication function


dispatch_post('/', 'app_start');
function app_start() {

    //generate session, grab callid + caller number/username for admin potential
    $session = new Session();
	$caller = $session->getFrom();
	$id = $session->getcallid();
	$caller = $caller['id'];
	
	
	$collection = mongoAuth_session(); //grab session info (phone # + callid)
	

	//----New DB JSON Entry------//
	$newPerson = array(
		'sessionID' =>$id,
		'admin'=>$caller
	);
	
	insertMic($newPerson,$collection);	

	//------------------------//

	$tropo = new Tropo();

    $options = array("choices" => "[1-4 DIGITS]", "name" => "digits", "timeout" => 60, "mode" => "dtmf", "terminator" => "#");
 
    $tropo->ask("Welcome to the people's mike. To start a new mike, press 1 and then pound, otherwise enter a four digit mike PIN", $options);
  
    $tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=continue"));
      
    $tropo->RenderJson();
   
}
  
dispatch_post('/continue', 'app_continue');
function app_continue() {
    
    //grabbing needed returned values 
    $result = new Result();
    $pin = $result->getValue();
	$id = $result->getcallid();
	
	
	$collection = mongoAuth_session();
		
	$cursor = $collection->findOne(array('sessionID'=>$id)); //pulling caller number from matching callID
	$admin = $cursor['admin']; //current caller's phone number (for admin potential)
	
	$collection = mongoAuth();
		
   
    if ($pin == '1'){
    
		//---------------GENERATING NEW RANDOM PIN, CHECKING FOR DUPLICATE IN DB--------//
		
		$pinExists=true;

		//need to add a counter here, break while loop if it reaches 8999? occurences ---> then delete oldest DB entry
		while ($pinExists){
		
			$micNumber = rand(1000,9999); 
			$micNumber = $micNumber.''; //making it readable to tropo as string (to create a conference room in this name)
			
			$cursor = $collection->findOne(array('pin'=>$micNumber));
			
			if ($micNumber !== $cursor['pin']){
			
				$pinExists = false;
			}
		}
		
		//----------------------------------------------------------------------------//
		
		
		//----New Mic JSON Object------//
		$mic = array(
			'pin' =>$micNumber,
			'admin'=>$admin,
			'callIDs'=>array($id),
			'yes'=>array(),
			'no'=>array()
		);
		
		insertMic($mic,$collection);	

		//-----------------------------//
		

	    $tropo = new Tropo();
	    
	    $pinSep = wordwrap($micNumber, 1, " ", true); //making PIN speakable (i.e. 5432 is 5 4 3 2)
	    
	    $tropo->say('Your mike check number is '.$pinSep.'. Press pound for speaker options');
	 
		$tropo->conference(null, array("name" => "conference", "id" => $micNumber, "mute" => false, "terminator" => "#"));
		 
		$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=adminpanel"));
     
		$tropo->RenderJson();

    }
    
    else 
        {
        
	        $cursor = $collection->findOne(array('pin'=>$pin));
	        
	        $admin_auth = $cursor['admin']; //pulling admin's phone number from new mic object
	        
			if ($cursor['pin'] == $pin){

				$tropo = new Tropo();

				$newdata = array('$push' => array('callIDs' => $id));
				
				//$upsert = array("upsert" => $safe_insert);
				
				$collection->update(array('pin' => $pin), $newdata);
				
				$pinSep = wordwrap($pin, 1, " ", true); //making PIN speakable
				
				
				//if admin was disconnected they can reconnect to their conference with admin power here:
				if ($admin_auth == $admin){
				
					$tropo->say('Reconnecting to mike check number '.$pinSep.'as the speaker');
					
					$tropo->conference(null, array("name" => "conference", "id" => $pin, "mute" => false, "terminator" => "#"));

					$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=adminpanel"));
				}
				
				else {
				
					$tropo->say('Connecting to mike check number '.$pinSep.'. Press pound for voting options');
					
					$tropo->conference(null, array("name" => "conference", "id" => $pin, "mute" => true, "terminator" => "#"));
					
					$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=vote"));     
				}
				
				$tropo->RenderJson();
			}
			
			else {
			
				$tropo = new Tropo();
				
				$tropo->say('That is not a current mike check number');

				$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=wrongpin"));
				$tropo->RenderJson();	
	        }
    	}
}


dispatch_post('/vote', 'vote_tally');
function vote_tally() {

	$tropo = new Tropo();
					
	$options = array("choices" => "[1 DIGIT]", "name" => "digit", "timeout" => 60, "mode" => "dtmf");

    $tropo->ask("1 for yes, 0 for no, 9 for vote stats", $options);
    
    $tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=vote_collect"));
    
    $tropo->on(array("event" => "incomplete", "next" => "tropo_everything.php?uri=return_conf"));

	$tropo->RenderJson();	
}


dispatch_post('/vote_collect', 'vote_collected');
function vote_collected() {

	$result = new Result();
	$id = $result->getcallid();
    $vote = $result->getValue();
    
	$collection = mongoAuth();

    $cursor = $collection->findOne(array('callIDs'=>$id));

	$pin = $cursor['pin'];
    
  	$yes = 'yes';
   	$no = 'no';
   
	$tropo = new Tropo();
	
	//this is an awful voting system (where its just adding a "yes" string to an array then counting the number of yes strings)...will fix eventually using $inc :P
	if ($vote == "1"){
		
		$newdata = array('$push' => array('yes' => $yes));
		$upsert = array("upsert" => $safe_insert);
				
		$collection->update(array('pin' => $pin), $newdata, $upsert);
		
		$tropo->say('you voted yes');
	}
	
	else if ($vote == "0"){
	
		$newdata = array('$push' => array('no' => $no));
		$upsert = array("upsert" => $safe_insert);
						
		$collection->update(array('pin' => $pin), $newdata, $upsert);	
		$tropo->say('you voted no');
	}
	
	//again, super lazy way to do voting, sorry :0
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

	$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=return_conf"));

	$tropo->RenderJson();	

}


dispatch_post('/return_conf', 'conf');
function conf() {

	$result = new Result();
	$id = $result->getcallid();
	$collection = mongoAuth();

	$cursor = $collection->findOne(array('callIDs'=>$id));

	$micpin = $cursor['pin'];

		$tropo = new Tropo();

		$tropo->conference(null, array("name" => "conference", "id" => $micpin, "mute" => true, "terminator" => "#"));
		
		$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=vote"));
		     
		$tropo->RenderJson();
}


//if user enters wrong PIN, revert back here...can't redo new Session, so need this or it won't work!
dispatch_post('/wrongpin', 'app_start2');
function app_start2() {

	$tropo = new Tropo();
	
    $options = array("choices" => "[1-4 DIGITS]", "name" => "digits", "timeout" => 60, "mode" => "dtmf", "terminator" => "#");
 
    $tropo->ask("Welcome to the people's mike. To start a new mike, press 1 and then pound, otherwise enter a four digit mike PIN", $options);
  
    $tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=continue"));

    $tropo->RenderJson();
}

//---------------------------------------------------------------//
//------------------MIC ADMIN CONTROLS---------------------------//
//---------------------------------------------------------------//

dispatch_post('/adminpanel', 'admin_commands');
function admin_commands() {

	$tropo = new Tropo();
					
	$options = array("choices" => "[1 DIGIT]", "name" => "digit", "timeout" => 60, "mode" => "dtmf");

    $tropo->ask("press 1 for mike pin, 9 for current poll stats, 3 to clear poll stats", $options);
    
    $tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=admin_collect"));
    
    $tropo->on(array("event" => "incomplete", "next" => "tropo_everything.php?uri=admin_return_conf"));

	$tropo->RenderJson();	
}

dispatch_post('/admin_collect', 'admin_collected');
function admin_collected() {

	$result = new Result();
	$id = $result->getcallid();
    $vote = $result->getValue();
	$collection = mongoAuth();

    $cursor = $collection->findOne(array('callIDs'=>$id));

	$pin = $cursor['pin'];
    
  	$yes = 'yes';
    $no = 'no';
    
	$tropo = new Tropo();
	
	//the current mic pin
	if ($vote == "1"){
	
		$pinSep = wordwrap($pin, 1, " ", true); //making PIN speakable

		$tropo->say('the mike pin is '.$pinSep);
		
		$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=admin_return_conf"));
		
	}
	
	//
	else if ($vote == "3"){
		
		$options = array("choices" => "[1 DIGIT]", "name" => "digit", "timeout" => 60, "mode" => "dtmf");
	
	    $tropo->ask("press 3 again to clear poll, or press any other key to cancel", $options);
	    
	    $tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=admin_poll_clear"));
	    
	    $tropo->on(array("event" => "incomplete", "next" => "tropo_everything.php?uri=admin_return_conf"));
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
		
		$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=admin_return_conf"));
	
	}
	
	else {
	
		$tropo->say('please try again');
		$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=admin_return_conf"));
	}
	
	$tropo->RenderJson();	
}

dispatch_post('/admin_return_conf', 'admin_conf');
function admin_conf() {

	$result = new Result();
	$id = $result->getcallid();
	$collection = mongoAuth();
	$cursor = $collection->findOne(array('callIDs'=>$id));

	$pin = $cursor['pin'];

		$tropo = new Tropo();

		$tropo->conference(null, array("name" => "conference", "id" => $pin, "mute" => false, "terminator" => "#"));
		
		$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=adminpanel"));
		     
		$tropo->RenderJson();
}

dispatch_post('/admin_poll_clear', 'admin_clear');
function admin_clear() {

	$result = new Result();
	$id = $result->getcallid();
	$vote = $result->getValue();


	$collection = mongoAuth();
	$cursor = $collection->findOne(array('callIDs'=>$id));

	$pin = $cursor['pin'];
	
	$tropo = new Tropo();

	if ($vote == "3"){
	
		//clearing votes here
		
		$yes = 'yes';
		$no = 'no';

		$cleardata_yes = array('$pull' => array('yes' => $yes));
		$cleardata_no = array('$pull' => array('no' => $no));

		$collection->update(array('pin' => $pin), $cleardata_yes, array('upsert' => true));
		$collection->update(array('pin' => $pin), $cleardata_no, array('upsert' => true));

		$tropo->say('the poll was cleared');
	}

	else {

		$tropo->say('the poll was not cleared');
	}

		$tropo->conference(null, array("name" => "conference", "id" => $pin, "mute" => false, "terminator" => "#"));
		
		$tropo->on(array("event" => "continue", "next" => "tropo_everything.php?uri=adminpanel"));
		     
		$tropo->RenderJson();
}

//---------------------------------------------------------------//
//---------------------------------------------------------------//
//---------------------------------------------------------------//

  
run();


//---------------FUNCTIONS-----------------------//

		function insertMic($json,$collection){
		
			$safe_insert = true;
			$collection->insert($json,$safe_insert);
		}

		function mongoAuth(){
			
			//auth call 
			$un = "xxxxxxxx";
			$pw = "xxxxxxxx";
			
			//database we have credentials to
			$dtb = "OWS";
			
			$coll = "miccheck";
			
			//set our collection
			$collection = mongoCollection($un, $pw, $dtb, $coll);
			
			return $collection;				
		}
		
		function mongoAuth_session(){
			
			//auth call 
			$un = "xxxxxxxxx";
			$pw = "xxxxxxxxx";
			
			//database we have credentials to
			$dtb = "OWS";
			
			$coll = "micsession";
			
			//set our collection
			$collection = mongoCollection($un, $pw, $dtb, $coll);
			
			return $collection;			
		}
//---------------------------------------------------//

 
?>