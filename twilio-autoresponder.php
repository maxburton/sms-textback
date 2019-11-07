<?php
//ini_set('display_errors', 1);
// Code for Twilio Support Document: https://support.twilio.com/hc/en-us/articles/223134267-Building-an-SMS-Keyword-Response-Application
// Get the PHP helper library from twilio.com/docs/php/install
require __DIR__ . '/twilio-php-master/Twilio/autoload.php'; // Loads the library. This may vary depending on how you installed the library.
use Twilio\Rest\Client;

/*
** Your Account Sid and Auth Token from twilio.com/user/account
*/
$sid = "ACd9bc8f0d0a70e1245ccae122b1e3010d";
$token = "d9dd371c6621ef98dfe2bbc699cf9d4e";
$client = new Client($sid, $token);

/* 
** Array of response messages, to represent the function of a database.
*/
$responseMessages = array();
$lines = file('smslist.txt');
foreach ($lines as $line_num => $line) {
	if(trim($line)){
		$filePieces = explode(",", $line);
		$responseMessages[$filePieces[0]] = array('iOS' => $filePieces[1], 'Android' => $filePieces[2]);
	}
}
//var_dump($responseMessages);

/* 
** Default response message when receiving a message without key words.
*/
$defaultMessage = "You didn't enter a valid restaurant name and platform. Try: <restaurantname> <platform> e.g. raja ios";

/*
** Read the contents of the incoming message fields.
*/ 
$body = $_REQUEST['Body']; 
$to = $_REQUEST['From'];
$from = $_REQUEST['To'];

/*
** Remove formatting from $body until it is just lowercase   
** characters without punctuation or spaces.
*/
$platform = null;
$sendDefault = true; // Default message is sent unless key word is found in following loop.
$pieces = explode(" ", $body);

/*
** Choose the correct platform link to send.
*/
for ($i = 0; $i < count($pieces); $i++){  // TODO: Test again with original file to see if this change has broken it
	$result = preg_replace("/[^A-Za-z0-9]/u", "", $pieces[$i]); 
	$result = trim($result); 
	$result = strtolower($result); 
	if($result == "ios"){
		$platform = "iOS";
		unset($pieces[$i]);  // Delete that entry from array
	}else if($result == "android"){
		$platform = "Android";
		unset($pieces[$i]);
	}
}
$pieces = array_values($pieces);  // Reset array indices


/*
** Choose the correct message response and set default to false.
*/
$word = implode("", $pieces);

$result = preg_replace("/[^A-Za-z0-9]/u", "", $word); 
$result = trim($result); 
$result = strtolower($result); 
foreach ($responseMessages as $restaurant => $messages) {
	$restaurant_stripped = trim($restaurant); 
	$restaurant_stripped = preg_replace("/[^A-Za-z0-9]/u", "", $restaurant_stripped);
	if ($restaurant_stripped == $result) {
		if($platform){
			$body = "Download our " . ucwords($restaurant) . " app for " . $platform . "! \n\nLink: " . $messages[$platform];
			$sendDefault = false;
			break;
		}else{
			$body = "Download our " . ucwords($restaurant) . " app \nfor iOS: " . $messages['iOS'] . " \n\nor Android: " . $messages['Android'];
			$sendDefault = false;
			break;
		}
	}
}

// Send the correct response message.
if ($sendDefault != false) {
    $client->messages->create(
        $to,
        array(
            'from' => $from,
            'body' => $defaultMessage,
        )
    );
} else {
    $client->messages->create(
        $to,
        array(
            'from' => $from,
            'body' => $body,
        )
    );
}
