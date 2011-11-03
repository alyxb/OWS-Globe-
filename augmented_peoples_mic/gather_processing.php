<?php
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

if ($_REQUEST['Digits'] == '1') {

		echo "<Response><Say>You started a new mike, your mike number is".rand(1000,9999)."</Say></Response>";
			
}

	
	else if ($_REQUEST['Digits'] == '2') {
		echo "<Response><Say>Unsure</Say></Response>";					
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '3') {

		echo "<Response><Say>Disagree</Say></Response>";				
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '4') {

		echo "<Response><Say>Point of Process</Say></Response>";				
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '5') {
	
		echo "<Response><Say>Point of Information</Say></Response>";			
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '6') {
	
		echo "<Response><Say>Wrap it Up</Say></Response>";			
	//---------------------------------------//
	}
	
	else if ($_REQUEST['Digits'] == '7') {
	
		echo "<Response><Say>Block</Say></Response>";			
	//---------------------------------------//
	}

	
	else {
	
		echo "<Response><Say>You joined mike check number " . $_REQUEST['Digits'] . ". Press 1 to agree. 2 if unsure, 3 if disagree, 4 for point of process, 5 for point of information, 6 to wrap it up, 7 for block, 8 for current vote stats, 9 to leave this mic. 0 for help. Goodbye for now. </Say></Response>";
	
	}
?>