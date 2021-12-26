<?php


include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
include_once 'version.inc';

$pluginName = basename(dirname(__FILE__));


//include $messageQueuePluginPath . "functions.inc.php";

$PLAYLIST_NAME="";
$fpp_matrixtools_Plugin = "fpp-matrixtools";
$fpp_matrixtools_Plugin_Script = "scripts/matrixtools";
$messageQueue_Plugin = "FPP-Plugin-MessageQueue";
$matrixMessage_Plugin = "FPP-Plugin-Matrix-Message";
$messageQueuePluginPath = $settings['pluginDirectory'] . "/" . $messageQueue_Plugin."/";
include $messageQueuePluginPath . "functions.inc.php";

$MESSAGE_QUEUE_PLUGIN_ENABLED=false;


$logFile = $settings['logDirectory']."/".$pluginName.".log";


$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE", $messageQueue_Plugin));

if(file_exists( $pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script) && file_exists( $pluginDirectory."/".$messageQueue_Plugin )&& file_exists( $pluginDirectory."/".$matrixMessage_Plugin )){
	logEntry($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script." EXISTS: Enabling");
	$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

} else {
	if (!file_exists($pluginDirectory."/".$fpp_message_queue_Plugin )) {
		logEntry("Message Queue to Matrix Overlay plugin is not installed, cannot use this plugin with out it");
		echo "<h1>Message Queue to Matrix Overlay is not installed. Install the plugin and revisit this page to continue.</h1><br/>";	
	}
	if (!file_exists($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script)) {
	logEntry("FPP Matrix tools plugin is not installed, cannot use this plugin with out it");
	echo "<h1>FPP Matrix Tools plugin is not installed. Install the plugin and revisit this page to continue.</h1>";
	}
	if (!file_exists($pluginDirectory."/".$matrixMessage_Plugin)){
		logEntry("FPP Matrix Message plugin is not installed, cannot use this plugin with out it");
		echo "<h1>FPP Matrix Message plugin is not installed. Install the plugin and revisit this page to continue.</h1>";
	}

	exit(0);
}


$gitURL = "https://github.com/FalconChristmas/FPP-Plugin-EventDate.git";


//$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";


//logEntry("plugin update file: " . $pluginUpdateFile);


if (isset($pluginSettings['DEBUG'])) {
    $DEBUG = $pluginSettings['DEBUG'];
}


$Plugin_DBName = $messageQueueFile;
	
$db = new SQLite3($Plugin_DBName) or die('Unable to open database');
	
//create the default tables if they do not exist!
createTables();
?>

<html>
<head>
<style>

#scroll-container {
  border: 3px solid black;
  border-radius: 5px;
  overflow: hidden;
}

#scroll-text {
	
	font-weight: bold; 
	font-size: 30px;
	
  /* animation properties */
  -moz-transform: translateX(100%);
  -webkit-transform: translateX(100%);
  transform: translateX(100%);
  
  -moz-animation: my-animation 15s linear infinite;
  -webkit-animation: my-animation 15s linear infinite;
  animation: my-animation 15s linear infinite;
}

/* for Firefox */
@-moz-keyframes my-animation {
  from { -moz-transform: translateX(100%); }
  to { -moz-transform: translateX(-100%); }
}

/* for Chrome */
@-webkit-keyframes my-animation {
  from { -webkit-transform: translateX(100%); }
  to { -webkit-transform: translateX(-100%); }
}

@keyframes my-animation {
  from {
    -moz-transform: translateX(100%);
    -webkit-transform: translateX(100%);
    transform: translateX(100%);
  }
  to {
    -moz-transform: translateX(-50%);
    -webkit-transform: translateX(-50%);
    transform: translateX(-50%);
  }	

</style>
</head>

<div id="EventDate" class="settings">
<fieldset>
<legend><?php echo $pluginName . " Version: ". $pluginVersion;?> Support Instructions</legend>

<p>Known Issues:
<ul>
<li>None
</ul>

<p>Configuration:
<ul>
<li>Configure the date and time of your event</li>
<li>Enter in the PRE TEXT that will appear before your countdown</li>
<li>Configure 12 or 24 hour countdown mode</li>

<li>Schedule the event in your Playlist to send the countdown out your Matrix</li>
</ul>

<p><b>This plugin requires ACCURATE time for its calculation.</b></p>
</div>
<div>

<p>ENABLE PLUGIN: <?PrintSettingCheckbox("Event Date Plugin", "ENABLED", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "", $changedFunction=""); ?> </p>
<p>Event Name: <?  PrintSettingTextSaved("EVENT_NAME", 0, 0, $maxlength = 32, $size = 32, $pluginName, $defaultValue = "The Event!", $callbackName = "updateOutputText", $changedFunction = "", $inputType = "text", $sData = array());?> </p>
<p>Event Date: <? PrintSettingSelect("MONTH", "MONTH", 0, 0, $defaultValue= "1", getMonths(), $pluginName, $callbackName = "updateOutputText", $changedFunction = ""); ?> 
<? PrintSettingSelect("DAY", "DAY", 0, 0, $defaultValue= "1", getDaysOfMonth(), $pluginName, $callbackName = "updateOutputText", $changedFunction = ""); ?>
 <? PrintSettingSelect("YEAR", "YEAR", 0, 0, $defaultValue= date("Y")+1, getYears(), $pluginName, $callbackName = "updateOutputText", $changedFunction = ""); ?>
 Hour: <? PrintSettingSelect("HOUR", "HOUR", 0, 0, $defaultValue= "0", getHours(), $pluginName, $callbackName = "updateOutputText", $changedFunction = ""); ?>
 Min: <? PrintSettingSelect("MIN", "MIN", 0, 0, $defaultValue= "0", getMinutes(), $pluginName, $callbackName = "updateOutputText", $changedFunction = ""); ?></p>
<p>Pre Text: <?  PrintSettingTextSaved("PRE_TEXT", 0, 0, $maxlength = 32, $size = 32, $pluginName, $defaultValue = "It is", $callbackName = "updateOutputText", $changedFunction = "", $inputType = "text", $sData = array());?> </p>
<p>Post Text <?  PrintSettingTextSaved("POST_TEXT", 0, 0, $maxlength = 32, $size = 32, $pluginName, $defaultValue = "until", $callbackName = "updateOutputText", $changedFunction = "", $inputType = "text", $sData = array());?> </p>
<p><h3>If the remaining time is more than a day then you can select to include the hours and/or minutes.</br>
If the remaining time is less than a day, the plugin will automatically display the hours and minutes remaining.</h3></p>
<p>Include Hours: <?PrintSettingCheckbox("INCLUDE_HOURS", "INCLUDE_HOURS", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "updateOutputTextHours", $changedFunction = ""); ?> </p>
<p>Include Minutes: <?PrintSettingCheckbox("INCLUDE_MINUTES", "INCLUDE_MINUTES", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "updateOutputTextMinutes", $changedFunction = ""); ?> </p>
<p>Your message will appear as:</p>
<div class= "marquee" id="scroll-container" >
<p id="scroll-text">temp text <p>

</div>
<input type=hidden name=LAST_READ value= <? $LAST_READ ?>>
<p><h3>If you want your message to display immediately when the command to run the countdown </br>
is activated then enable the Immediate Output. Otherwise the message will be stored in the </br>
Matrix Message Queue until you give the Matrix Message Queue the command to run.</h3></p>
<p>Immediately output to Matrix (Run MATRIX plugin): <? PrintSettingCheckbox("Immediate output to Matrix", "IMMEDIATE_OUTPUT", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = ""); ?> </p>
<p>The Matrix message Plugin location should be the default of 127.0.0.1 unless you have a specialized installation configuration.</p>
MATRIX Message Plugin Location: ;<?  PrintSettingTextSaved("MATRIX_LOCATION", 0, 0, $maxlength = 15, $size = 15, $pluginName, $defaultValue = "127.0.0.1", $callbackName = "", $changedFunction = "", $inputType = "text", $sData = array());?> 


</form>


<p>To report a bug, please file it against the sms Control plugin project on Git:<? echo $gitURL;?> 
</fieldset>
</div>
<br />

<script>
updateOutputText();

function updateOutputTextHours(updateOutput){
updateOutputText();	
}

function updateOutputTextMinutes(updateOutput){
updateOutputText();	
}
function updateOutputText(){
	
	var messageText= getMessageText();
	document.getElementById("scroll-text").innerHTML = messageText;
}
function getMessageText(){
	var eventName = document.getElementById("EVENT_NAME").value;
	var eventMonth = parseInt(document.getElementById("MONTH").value)-1;
	var eventDay = document.getElementById("DAY").value;
	var eventYear = document.getElementById("YEAR").value;
	var eventHour = document.getElementById("HOUR").value;
	var eventMin = document.getElementById("MIN").value;
	var preText = document.getElementById("PRE_TEXT").value;
	var postText = document.getElementById("POST_TEXT").value;
	var incHours = document.getElementById("INCLUDE_HOURS").checked;
	var incMin = document.getElementById("INCLUDE_MINUTES").checked;
	var eventDate = new Date(eventYear, eventMonth, eventDay, eventHour, eventMin  );
	var currentDate= new Date();
	var rawTimeDiff = Math.floor((eventDate - currentDate)/1000);
	var yearsToDate = Math.floor(rawTimeDiff/(60*60*24*365));
	var daysToDate = Math.floor(rawTimeDiff/(60*60*24))%365;
	var hoursToDate = Math.floor(rawTimeDiff/(60*60))%24;
	var minutesToDate = Math.floor(rawTimeDiff/60)%60 +1;
	var messageText = preText;

	if (yearsToDate >= 1){
		if (yearsToDate >=2){
			messageText += " " + yearsToDate + " years ";
		}else {
			messageText += " " + yearsToDate + " year ";
		}
	}else{
		messageText += " ";
	}

	if (daysToDate >= 1){
		if (daysToDate >=2){
			messageText += daysToDate + " days ";
		} else {
			messageText += daysToDate + " day ";			
		}
		if(incHours == true){			
			if (hoursToDate >=2) {
				messageText += hoursToDate + " hours ";
			} else {
				if (hoursToDate >= 1) {
					messageText += hoursToDate + " hour ";
				}
			}
		}
		
		if(incMin == true){
			if(incHours == false){
					minutesToDate += hoursToDate*60;
			}
			if (minutesToDate >=2) {
				messageText += minutesToDate + " minutes ";
			} else {
				messageText += minutesToDate + " minute ";
			}	
		}
	}else {
			
		if (hoursToDate >=2) {
			messageText += hoursToDate + " hours ";
		} else {
			if (hoursToDate >= 1) {
				messageText += hoursToDate + " hour ";
			}
		}
		
		if (minutesToDate >=2) {
			messageText += minutesToDate + " minutes ";
		} else {
			messageText += minutesToDate + " minute ";
		}	
	}           
        
	messageText += postText + " " + eventName;

	return messageText;
	

}
</script>

</html>
