<?php

	//Name: Harrison Wainwright
	//CS316
	//Date: 11/21/16
	//Project 3

	if (isset($_GET['fileToSearch'])) {
		processForm();
	} else {
		displayForm();
	}


function processForm() {
	$myError = 0;

	$fileName = $_GET['fileToSearch'];

	if(okayfile($fileName)) // If fileName is valid, allow form to finish completion.
	{

		if(isset($_GET['statToSearch']) && isset($_GET['functWanted']))
		{

			$fileContents = slurpfile($fileName); // Get proper fileContents.

			$theStat = $_GET['statToSearch']; // Get stats value.
			printJSONerror(json_last_error());

			$theFunct = $_GET['functWanted']; // Get functWanted value.
			printJSONerror(json_last_error());

			$formCheck = doControl(); // Get stats values to check against one entered.

			foreach($formCheck->stats as $obj) // Go through stats values from UKgames.json.
			{
				if($obj == $theStat) // If stats value entered is a match allow search to run.
				{
					doSearch($fileContents, $theStat, $theFunct);
					return;
				}
			}

	        	echo "Incorrect value entered for stat", PHP_EOL; // Inform user of incorrect stats value entered.
        		return;
		}
		else if(!(isset($_GET['statToSearch']))) // If not set, catch the error and return.
		{
			echo "Stat to search for was not set", PHP_EOL;
			return;
		}
		else if(!(isset($_GET['functWanted'])))
		{
			echo "Function wanted was not set", PHP_EOL;
			return;
		}
	
	}
	else
	{
		echo "Invalid filename entered", PHP_EOL; // Inform user of incorrect fileName entered.
		return;
	}
}

//
//  Pre-conditions:  $f contains a string representation of a season file
//                   $s is a statistic we want to search for
//                   $w is what function (high or low) we want.
//
//  Post-conditions: Output of the information of the matching game.
//
function doSearch($f, $s, $w) {

	$opponent = "";
	$currStat = null; // Initialize total value.
        $gamesJSON = json_decode($f); // Decode the string to allow searching in the future.

        printJSONerror(json_last_error()); // Check for error after using json_decode.

        foreach($gamesJSON->games as $obj) // Go through the decoded object looking for certain stat.
        {
                if($w != "high" && $w != "low") // Check the input for funcWanted to ensure input is present.
                {
                        echo "Incorrect value entered for function wanted";
			return;
                }

                if($currStat == null) // If first entry, save it as total number.
                {
                        $currStat = $obj->{$s};
                	$opponent = $obj->{key($obj)};
		}
                else
                {
			// NOTE: both high and low go through entire list, therefore the last version of the high or low is displayed in the event of a tie.

                        if($w == "high") // If looking for high stat, compare object's stat accordingly.
                        {
                                if($currStat < $obj->{$s})
                                {
                                        $currStat = $obj->{$s}; // If stat is higher than total, replace total.
                                	$opponent = $obj->{key($obj)};
				}
                        }
                        else if($w == "low") // If looking for low stat, compare object's stat accordingly.
                        {
                                if($currStat > $obj->{$s})
                                {
                                        $currStat = $obj->{$s}; // If stat is lower than total, replace total.
                                	$opponent = $obj->{key($obj)};
				}
                        }

                }
        }

        echo "Found ", $w, " value for ", $s, " against ", $opponent, ": ", $currStat, PHP_EOL;
        return;
}

// Gets filename and checks validty. Returns boolean dependent on file validity.
function okayFile($f) {

	$fileContents = "";
        $fileContents = slurpfile($f);

	if($fileContents === FALSE) // If fileContents is null then file is not valid, return false.
	{
		return false;
	}
	else // Else return true.
	{
		return true;
	}
}

// Display form using html as well as filling combo boxes with values from UKgames.json.
function displayForm() {

	startHTML();

	$formFillers = doControl(); // Get values from UKgames.json.

	echo "
	<form action='P3_frame.php' method='get'>
	Select parameters to search:<br>
	";
	echo "
	<p>
	<select name='fileToSearch'> ";

	$enumFiles = $formFillers->files;

	foreach($enumFiles as $key=>$val) // Fill in file values into selection box.
	{
		echo "<option value = '", $val, "'>", $key, "</option>\n";
	}

	echo "</select>";

	echo "
	<p>
	<select name='statToSearch'> ";

        foreach($formFillers->stats as $obj) // Fill in statistic values into selection box.
        {
                echo "<option value = '", $obj, "'>", $obj, "</option>";
        }

	echo "</select>";

	echo "
	<p>
	<select name='functWanted'> ";
		echo "<option value='high'>High</option>";
		echo "<option value='low'>Low</option>";
	echo "</select>";

	echo "
	<p>
	<input type='submit' value='Do search'>
	";

	endHTML();
}

//
//  Read the file "UKgames.json" and return a JSON object.
//
//  Calling code should check return value for validity!
//
function doControl() {

$scoreFiles = "UKgames.json";
$results = "";

	$filesJSON = slurpfile($scoreFiles);

	if (strlen($filesJSON) > 0) {
		$results = json_decode($filesJSON);	
	}

	return $results;
}

//
//  Read in the contents of a file and return it as a string.
//  Need to check for errors......
//
function slurpfile($afile) {

	$contents = "";

	$contents = @file_get_contents($afile);
	printJSONerror(json_last_error());

	return $contents;
}

function startHTML() {

echo "
<html>
<head>
<title>Search records!</title>
</head>
<body>
<h1>Search records!</h1>
";

}

function endHTML() {

echo "
</body>
</html>
";

}

//
//  Pass in json_last_error() and this will print out the error, if any.
//
function printJSONerror($e) {

switch ($e) {
        case JSON_ERROR_NONE:
//            echo ' - No errors';
	      return;
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
	break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            echo ' - Unknown error';
        break;
    }

    echo PHP_EOL;

}

?>
