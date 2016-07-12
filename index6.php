<?php

function getStatusCodeMessage($status)
{
    	// Status Code definitions
    	$codes = Array(
       	100 => 'Continue',
       	101 => 'Switching Protocols',
       	200 => 'OK',
       	201 => 'Created',
       	202 => 'Accepted',
       	203 => 'Non-Authoritative Information',
       	204 => 'No Content',
       	205 => 'Reset Content',
       	206 => 'Partial Content',
       	300 => 'Multiple Choices',
       	301 => 'Moved Permanently',
       	302 => 'Found',
       	303 => 'See Other',
       	304 => 'Not Modified',
       	305 => 'Use Proxy',
       	306 => '(Unused)',
       	307 => 'Temporary Redirect',
       	400 => 'Bad Request',
       	401 => 'Unauthorized',
       	402 => 'Payment Required',
       	403 => 'Forbidden',
       	404 => 'Not Found',
       	405 => 'Method Not Allowed',
       	406 => 'Not Acceptable',
       	407 => 'Proxy Authentication Required',
       	408 => 'Request Timeout',
       	409 => 'Conflict',
       	410 => 'Gone',
       	411 => 'Length Required',
       	412 => 'Precondition Failed',
       	413 => 'Request Entity Too Large',
       	414 => 'Request-URI Too Long',
       	415 => 'Unsupported Media Type',
       	416 => 'Requested Range Not Satisfiable',
       	417 => 'Expectation Failed',
       	500 => 'Internal Server Error',
  	501 => 'Not Implemented',
      	502 => 'Bad Gateway',
    	503 => 'Service Unavailable',
     	504 => 'Gateway Timeout',
     	505 => 'HTTP Version Not Supported');
	return (isset($codes[$status])) ? $codes[$status] : '';
}


function sendResponse($status = 200, $body = '', $content_type = 'text/html')
{
	$status_header = 'HTTP/1.1 '. $status. ' '. getStatusCodeMessage($status);
	//header($status_header);
	//header('Content-type: '. $content_type);
	echo $body;
}

class RetrieveAPI
{
	private $db;

	function __construct()
	{
		$this->db = new mysqli("localhost", "root", "SunLotus1!", "Apps");
		$this->db->autocommit(FALSE);
	}

	function __destruct()
	{
		$this->db->close();
	}

	function retrieveAndSend() 
	{
		if(isset($_POST["app_id"]) && (isset($_POST["new_question"]) == false))
		{
			$app_id = $_POST["app_id"];
			$question_id = 1;

			//check database for questions.
			$message = $this->db->prepare('SELECT question_id FROM questions WHERE app_id =? LIMIT 20');
			$message->bind_param("i", $app_id);		
			$message->execute();
			$message->bind_result($question_id);
			
			$index = 0;
			$Data = Array();
			while($message->fetch() )
			{
				$Data[$index] = $question_id;
				//$Data = array_merge($Data,$question_id);
				$index++;
			}
				
			$message->close();
			
			//Check if question question
			if((strlen($question_id) <=0))
			{
				sendResponse(400, 'Invalid id');
				return false;
			}
			


			/*********************CHECK DATABASE FOR TOKENS FOR EACH QUESTION********/

			$Data1 = array();
			foreach($Data as $x => $x_value)
			{
				$question_id = $x_value;	
				if($message = $this->db->prepare('SELECT questions.question, tokens.token_description, tokens.yes_value, tokens.no_value FROM questions, tokens WHERE questions.question_id = tokens.question_id AND questions.question_id =?'))
				{
					$message->bind_param("i", $x_value);		
					$message->execute();
					$message->bind_result($question, $token_description, $yes_value, $no_value);
					
					$index = 0;

					$qString = "";

					while($message->fetch() )
					{
						if($index == 0)
						{
							$qString = "$question"; 
						}
						$qString = $qString."|"."$token_description"."|"."$yes_value"."|"."$no_value";
						$index++;
					}
					$qArray1 = array( "$question_id" => "$qString");
					$Data1 = array_merge($Data1, $qArray1);
					
				}
				else
				{
					printf("%s \n\n", $this->db->error);
				}
			}

			
			
			$message->close();

			$result = array(
			"question_retrieved" => $question,
			);
			sendResponse(200, json_encode($Data1));
			return true;
		}
		else if(isset($_POST["new_question"]))
		{

			$new_qtn = $_POST["new_question"];
			$app_id = $_POST["app_id"];
			$new_yes_token = 0;
			$new_no_token = 0;
			$new_token_description = 'default';
			$new_question_type = 'SOCIAL';
			$question_id = 0;
			$this->db->commit();

			//insert into question table in database
			$query = "INSERT INTO questions (app_id, question, question_type) VALUES(?,?,?)";
			if($message = $this->db->prepare($query))
			{
				
				$message->bind_param('sss', $app_id, $new_qtn, $new_question_type);
				if($message->execute())
				{
					$question_id = $message->insert_id;
				}
				
				$message->close();
			}
			else
			{
				$result = array("question_inserted" => "Question insert failed!");
				sendResponse(200, json_encode($result));

				return false;
			}


			//Insert tokens
			if($question_id)
			{
				//check number of tokens
				$counts = 0;
				while($counts < 30)
				{
					$token_description = "token_description"."$counts"; 
					if(isset($_POST["$token_description"]))
					{
						$counts++;
					}
					else
					{
						break;
					}
				}
		
				//insert each token into tokens table
				for($i = 0 ; $i < $counts;$i++)
				{
					$token_description = "token_description".$i;
					$yes_value = "token_yes_value".$i;
					$no_value = "token_no_value".$i;


					if(   (isset($_POST["$token_description"])) && (isset($_POST["$yes_value"])) && (isset($_POST["$no_value"])) )								{
						$new_token_description = $_POST["$token_description"];
						$new_yes_token = $_POST["$yes_value"];
						$new_no_token = $_POST["$no_value"];
						

						$query = "INSERT INTO tokens (token_description, yes_value, no_value, question_id) VALUES(?,?,?,?)";
						
						if($message = $this->db->prepare($query))
						{
							$message->bind_param('sssi', $new_token_description, $new_yes_token, $new_no_token, $question_id);
							if($message->execute())
							{
								
							}
							else
							{
								printf("token insert error %s \n\n", $this->db->error);
							}
							
						}
					}
					else
					{
						printf("token insert error %s \n\n", $this->db->error);
					}
					
						
				}
				$message->close();
				$result = array("question_inserted" => "Success!");
				sendResponse(200, json_encode($result));
				return true;
			}
			else
			{
				$result = array("question_inserted" => "Token insert failed!");
				sendResponse(200, json_encode($result));
				return false;
			}
			
			return true;

		}
		sendResponse(400, 'Invalid request 1');
		return false;
	}

}


$api = new RetrieveAPI;
$api->retrieveAndSend();
?>