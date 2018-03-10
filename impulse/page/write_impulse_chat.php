
<?php 
require_once("../include/initialize.php"); 

require_once(LIB_PATH.DS.'database.php');

require_once('../include/AfricasTalkingGateway.php');


		// Specify your authentication credentials
		$africas_talking_username  = "sandbox";
		$apikey     = "88fff082c7881c7f7c1808ad32d4b268549f1e2f69cf5a7ae07dee33761d349e";
		//get user-input from url
		$text=substr($_GET["text"], 0, 254);

		$user_id = $session->user_id;

		$sql = "SELECT username FROM user WHERE id = $user_id";
		$result = $database->query($sql);

		$result   = $database->fetch_array($result);
		$username = $result['username'];

		//escaping is extremely important to avoid injections!
		// $nameEscaped = htmlentities(mysqli_real_escape_string($db,$username)); //escape username and limit it to 32 chars
		$textEscaped =$database->escape_value($text); //escape text and limit it to 128 chars

		//create query
		$sql="INSERT INTO message (username, text) VALUES ('$username', '$textEscaped')";
		//execute query
		if ($database->query($sql)) {
			//If the query was successful
			echo "Wrote message to db";
		}else{
			//If the query was NOT successful
			echo "An error occurred";
		}

		// And of course we want our recipients to know what we really do
		$message    = $textEscaped;
		// Create a new instance of our awesome gateway class
		$gateway    = new AfricasTalkingGateway($africas_talking_username, $apikey, "sandbox");

		$recipients = '"';
		

		$sql = "SELECT phone, status FROM user ";
		
        $result = $database->query($sql);
       
        while ($row = $database->fetch_array($result)) {
            
            $phone = $row['phone'];
            $status = $row['status'];
             if ($status == 'offline') {
                $recipients .= $phone .' ,';
             } 
            
		
        }
		 $recipients = $recipients . '"+2332433444331';

		$mes="";

		try 
		{ 
		  // Thats it, hit send and we'll take care of the rest. 
		  $results = $gateway->sendMessage($recipients, $message);
					
		  foreach($results as $result) {
			// status is either "Success" or "error message"
			$mes .=" Number: " .$result->number;
			$mes .=" Status: " .$result->status;
			$mes .= " MessageId: " .$result->messageId;
			$mes .=" Cost: "   .$result->cost."\n";
		  }
		}
		catch ( AfricasTalkingGatewayException $e )
		{
		  $mes .="Encountered an error while sending: ".$e->getMessage();
		  
		}
		$session->message($mes);

?>


