<html>
<head>
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
<style>
body{
	font-size: 20;
	font-family: 'Nunito', sans-serif;
}
.red{
	color: red;
}
.green{
	color: green;
}
strong{
	color: green;
}
</style>
</head>
<body>

<?php
	$filename = "smslist.txt";
	$responseMessages = array();
	$lines = file($filename);
	foreach ($lines as $line_num => $line) {
		if(trim($line)){
			$filePieces = explode(",", $line);
			$responseMessages[$filePieces[0]] = array('iOS' => $filePieces[1], 'Android' => $filePieces[2]);
		}
	}
	//var_dump($responseMessages);
	
	$namePOST = $_POST["name"];
	$namePOST = trim($namePOST); 
	$namePOST = strtolower($namePOST);
	$name = preg_replace("/[^A-Za-z0-9\s]/u", "", $namePOST); 
	$iosPOST = $_POST["ios"];
	$iosPOST = str_replace(' ', '', $iosPOST);
	$iosPOST = trim($iosPOST); 
	$ios = preg_replace("/[^A-Za-z0-9\/\.]/u", "", $iosPOST); 
	$androidPOST = $_POST["android"];
	$androidPOST = str_replace(' ', '', $androidPOST);
	$androidPOST = trim($androidPOST); 
	$android = preg_replace("/[^A-Za-z0-9\/\:\.]/u", "", $androidPOST); 
	$nameChanged = false;
	$iosChanged = false;
	$androidChanged = false;
	
	if($namePOST != $name){
		$nameChanged = true;
	}if($iosPOST != $ios){
		$iosChanged = true;
	}if($androidPOST != $android){
		$androidChanged = true;
	}

	$isNameIn = false;
	$errormsg = "";
	
	foreach ($responseMessages as $restaurant => $platforms) {
		if ($restaurant == $name) {
			$isNameIn = true;
			break;
		}
	}
	
	if($nameChanged || $iosChanged || $androidChanged){
		$errormsg = "One (or more) of your fields contained illegal characters.<br>Please go back and re-enter them.<br>Here are some suggestions:<br>";
		if($nameChanged){
			$errormsg .= "<br>" . $namePOST . " --> " . $name;
		}if($iosChanged){
			$errormsg .= "<br>" . $iosPOST . " --> " . $ios;
		}if($androidChanged){
			$errormsg .= "<br>" . $androidPOST . " --> " . $android;
		}
	}else{
		$type = $_POST["entryType"];
		if($type == "new"){
			if(!$isNameIn){
				$content = "";
				if($ios && $android && $name){
					$content = convertArrayToText($responseMessages);
					$content .= $name . "," . $ios . "," . $android . "\n";
					writeToFile($filename, $content);
				}else{
					$errormsg = "One (or more) of your fields are empty!<br>Please go back and enter them";
				}
			}else{
				$errormsg = "This restaurant already exists in the database.<br>Choose another name or check the replace button to replace the existing entry.";
			}
		}else if($type == "replace"){
			if($isNameIn){
				if($ios && $android && $name){
					$responseMessages[$name] = array('iOS' => $ios, 'Android' => $android . "\n");
					$content = convertArrayToText($responseMessages);
					writeToFile($filename, $content);
				}else{
					$errormsg = "One (or more) of your fields are empty!<br>Please go back and enter them";
				}
			}else{
				$errormsg = "This restaurant name does not exist.<br>Please check the name you entered and try again.";
			}
		}else if($type == "delete"){
			if($isNameIn){
				unset($responseMessages[$name]);
				$content = convertArrayToText($responseMessages);
				writeToFile($filename, $content);
			}else{
				$errormsg = "This restaurant name does not exist.<br>Please check the name you entered and try again.";
			}
		}else{
			$errormsg = "No radio button selected. <br>Perhaps you refreshed this page or skipped the form screen? Please submit again.";
		}
	}
	
	function convertArrayToText($responseMessages){
		foreach ($responseMessages as $key => $entry){
			$content .= $key . "," . implode(",", $entry);
		}
		return $content;
	}
	
	function writeToFile($filename, $content){
		$fp = fopen($filename, 'w');
		fwrite($fp, $content);
		fclose($fp);
	}
?>

Action Performed: <?php echo "<strong>" . $_POST["entryType"] . "</strong>"; ?><br>
Restaurant name: <?php echo "<strong>" . $name . "</strong>"; ?><br>
With iOS link: <?php echo "<a href='http://" . $ios . "'target='_blank'>" . $ios . "</a>"; ?><br> and Android link: <?php echo "<a href='http://" . $android . "'target='_blank'>" . $android . "</a>"; ?><br><br>
Status: 
<?php 
if($errormsg){
	echo " <p class='red'>ERROR: " . $errormsg . "</p>";
}else{
	if($type == "delete"){
		echo " <p class='green'>SUCCESS:<br>Entry deleted</p>";
	}else{
		echo " <p class='green'>SUCCESS:<br>Please click on the links above to ensure they are correct.</p>";
	}
}
?><br>
<a href="./smsform.html">Return to form submission screen</a>

</body>
</html>
