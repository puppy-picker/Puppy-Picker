<!DOCTYPE html>
<html lang="en">
   <head>
      <title>Puppy Picker</title>
      <meta charset="utf-8" />
      <meta name="Author" content="Diane Nealon and Gabriella Mayorga" />
      <meta name="generator" content="Notepad++" />
      <style>
         /* Customize the label (the container) */
         .container {
         display: block;
         position: relative;
         padding-left: 35px;
         margin-bottom: 12px;
         cursor: pointer;
         font-size: 15px;
         -webkit-user-select: none;
         -moz-user-select: none;
         -ms-user-select: none;
         user-select: none;
         }
         /* Hide the browser's default checkbox */
         .container input {
         position: absolute;
         opacity: 0;
         cursor: pointer;
         height: 0;
         width: 0;
         }
         /* Create a custom checkbox */
         .checkmark {
         position: absolute;
         top: 0;
         left: 0;
         height: 15px;
         width: 15px;
         background-color: #eee;
         }
         /* On mouse-over, add a grey background color */
         .container:hover input ~ .checkmark {
         background-color: #ccc;
         }
         /* When the checkbox is checked, add a blue background */
         .container input:checked ~ .checkmark {
         background-color: #664d00;
         }
         /* Create the checkmark/indicator (hidden when not checked) */
         .checkmark:after {
         content: "";
         position: absolute;
         display: none;
         }
         /* Show the checkmark when checked */
         .container input:checked ~ .checkmark:after {
         display: block;
         }
         /* Style the checkmark/indicator */
         .container .checkmark:after {
         left: 5px;
         top: 5px;
         width: 3px;
         height: 7px;
         border: solid #ffcccc;
         border-width: 0 3px 3px 0;
         -webkit-transform: rotate(45deg);
         -ms-transform: rotate(45deg);
         transform: rotate(45deg);
         }
         body{
         background: #fff2cc;
         font-family: Helvetica;
         }
      </style>
   </head>
   <body>
      <header id="h1" style = "border: 1px solid; padding: 1px 5px; font-size: 35px; text-align:center;"> 
         Breeds That Fit Your Search!
      </header>
      <?php
         if (isset($_GET['Submit'])) {
         	
         	require_once('Connect.php');
         	require_once('debughelp.php');
         	
         	$dbh = ConnectDB();
         
         $query_str = " WHERE ";
         ?>
      <u>Your Selected Puppy Qualities </u>
      <?php
         echo "<br/>";
         
         if (isset($_GET['size'])) {
         
         	$sizeSelected     = $_GET['size'];
         	$countSizeSelected = count($sizeSelected);
         	
         	if ($countSizeSelected > 0){
         		$query_str .= "size IN (" . "'" .implode("','",$sizeSelected). "'";
         		$varSize = "Size: " .implode(", ",$sizeSelected);
         		echo $varSize . "<br/>";
         
         	}
         	
         	if ($countSizeSelected > 0){
         		$query_str .= ")";
         	}		
         	
         	if ($countSizeSelected >  0){
         		$query_str .= " AND ";
         	}
         }
         
         if (isset($_GET['activity'])) {
         	
         	$activitySelected = $_GET['activity'];
         	$countActivitySelected = count($activitySelected);
         	
         	if ($countActivitySelected > 0){
         		$query_str .= "activity IN (" . "'" .implode("','",$activitySelected). "'";
         		$varActivity = "Activity Level: " .implode(", ",$activitySelected);
         		echo $varActivity . "<br/>";
         	}
         	
         	if ($countActivitySelected > 0){
         		$query_str .= ")";
         	}		
         	
         	if ($countActivitySelected >  0){
         		$query_str .= " AND ";
         	}
         }
         
         if (isset($_GET['hair'])) {
         
         	$hairSelected     = $_GET['hair'];		
         	$countHairSelected = count($hairSelected);
         
         	if ($countHairSelected > 0){
         		$query_str .= "hair IN (" . "'" .implode("','",$hairSelected). "'";
         		$varHair= "Hair Length: " .implode(", ",$hairSelected);
         		echo $varHair . "<br/>";			
         	}
         	
         	if ($countHairSelected > 0){
         		$query_str .= ")";
         	}		
         	
         	if ($countHairSelected >  0){
         		$query_str .= " AND ";
         	}		
         }
         	
         $query_str = substr($query_str, 0, -5);
         
       if (isset($_GET['hair']) || isset($_GET['activity']) || isset($_GET['size']))  { 
         $sql  = "SELECT breed FROM Puppies" . $query_str;
         $stmt = $dbh->prepare($sql);
         $stmt->execute();
         $result = $stmt->fetchAll(PDO::FETCH_OBJ);
         $stmt   = null;
		 
	   }
         ?> 
      <?php
	         if (isset($_GET['hair']) || isset($_GET['activity']) || isset($_GET['size']))  { 

         $pickedPups = json_decode(json_encode($result), true);
			 }
			 
         $noPups = "Sorry, no puppies found in our database.";
         
         $facts = " facts";
          
       if (isset($_GET['hair']) || isset($_GET['activity']) || isset($_GET['size']))  { 
		  
         //Display chosen puppies
         foreach ($pickedPups as $key => $breed) {
         	echo "\n";
         	foreach ($breed as $attribute => $values) {
         		echo "<br/>" . $values . "<br/>";
         		
         		$valfact = $values . $facts;
         		
         		$puppyArJS[] = $valfact;
         //Take chosen puppies and run through YouTube API to find videos	
         
         			?>
      <script>
         // Load the client interfaces for the YouTube Analytics and Data APIs, which
         // are required to use the Google APIs JS client. More info is available at
         // https://developers.google.com/api-client-library/javascript/dev/dev_jscript#loading-the-client-library-and-the-api
         	
         	function init() {
                     gapi.client.setApiKey('AIzaSyAKTRJZ39cWh0Ia3zJ7Eeqg513JYfbGiMw');
                     gapi.client.load('youtube', 'v3', function() {
                             search();
                     });
             }
      </script>
      <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
      <script src="https://apis.google.com/js/client.js?onload=googleApiClientReady"> </script>
      <script src="https://apis.google.com/js/client.js?onload=init"></script>
      <script>
         // pass PHP variable declared above to JavaScript variable
         var jsValues = <?php echo json_encode($puppyArJS) ?>;
         
         var test = jsValues.toString().split(',').length;
         // Search for a specified string.	
             function search() {
         		
         		if(test ==1 ){
                     var q = jsValues;
                     var request = gapi.client.youtube.search.list({
                                q: q,
                             part: 'snippet',
         					type: "video",
         		            maxResults: 1,      					
                     });
                     request.execute(function(response) {
                             var str = JSON.stringify(response.result);
                             $('#search-container').html('<pre>' + str + '</pre>');
                     });
             }
         	else{
         	         	
         	var replaceJSValues = jsValues.shift();

         	var q = replaceJSValues;
                     var request = gapi.client.youtube.search.list({
                                q: q,
                             part: 'snippet',
         					type: "video",
         		            maxResults: 1,
         
         					
                     });
                     request.execute(function(response) {
                             var str = JSON.stringify(response.result);
                             $('#search-container').html('<pre>' + str + '</pre>');
                     
					 obj = JSON.parse(str);

					 				console.log(obj);

					 });
         
         	}}
         	

      </script>
      <?php
         }}}      }
         if(empty($values)){
         
         echo "<br/>" . $noPups;
         }
         
     ?>     
      <br style="clear: both;" />
      <hr />
      <footer id = "f1" style="border: 1px solid; padding: 1px 5px">
         D. Nealon G. Mayorga
         <span style="float: right;">
         <a href="http://validator.w3.org/check/referer">HTML5</a> /
         <a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3">
         CSS3 </a>
         </span>
      </footer>
   </body>
</html>
