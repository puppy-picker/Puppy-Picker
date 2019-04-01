<!DOCTYPE html>
<html lang="en">
   <head>
      <title>Puppy Picker</title>
      <meta charset="utf-8" />
      <meta name="Author" content="Diane Nealon and Gabriella Mayorga" />
      <meta name="generator" content="Notepad++" />
     
   
   <link rel="stylesheet" type="text/css" href="./puppysearch.css" />

   </head>
   <body>
      <header id="h1" style = "border: 1px solid; padding: 1px 5px; font-size: 35px; text-align:center;"> 
         Puppies That Fit Your Search!
      </header>
      <?php
	  //Check if Submit was pressed in puppypicker.php
if (isset($_GET['Submit'])) {
    
	//connect to Database
    require_once('Connect.php');
    require_once('debughelp.php');
    
    $dbh = ConnectDB();
    
	//Use this to start building SQL query string
    $query_str = " WHERE ";
?>
     <u>Your Selected Puppy Qualities </u>
      <?php
    echo "<br/>";
    
	//check if size attribute selected in puppypicker.php and add to query string
    if (isset($_GET['size'])) {
        
        $sizeSelected      = $_GET['size'];
        $countSizeSelected = count($sizeSelected);
        
        if ($countSizeSelected > 0) {
            $query_str .= "size IN (" . "'" . implode("','", $sizeSelected) . "'";
            $varSize = "Size: " . implode(", ", $sizeSelected);
            echo $varSize . "<br/>";
            
        }
        
        if ($countSizeSelected > 0) {
            $query_str .= ")";
        }
        
        if ($countSizeSelected > 0) {
            $query_str .= " AND ";
        }
    }
    
		//check if activity attribute selected in puppypicker.php and add to query string
    if (isset($_GET['activity'])) {
        
        $activitySelected      = $_GET['activity'];
        $countActivitySelected = count($activitySelected);
        
        if ($countActivitySelected > 0) {
            $query_str .= "activity IN (" . "'" . implode("','", $activitySelected) . "'";
            $varActivity = "Activity Level: " . implode(", ", $activitySelected);
            echo $varActivity . "<br/>";
        }
        
        if ($countActivitySelected > 0) {
            $query_str .= ")";
        }
        
        if ($countActivitySelected > 0) {
            $query_str .= " AND ";
        }
    }
    
			//check if hair attribute selected in puppypicker.php and add to query string
    if (isset($_GET['hair'])) {
        
        $hairSelected      = $_GET['hair'];
        $countHairSelected = count($hairSelected);
        
        if ($countHairSelected > 0) {
            $query_str .= "hair IN (" . "'" . implode("','", $hairSelected) . "'";
            $varHair = "Hair Length: " . implode(", ", $hairSelected);
            echo $varHair . "<br/>";
        }
        
        if ($countHairSelected > 0) {
            $query_str .= ")";
        }
        
        if ($countHairSelected > 0) {
            $query_str .= " AND ";
        }
    }
	
	    if (isset($_GET['zip'])) {
        
        $zipCode      = $_GET['zip'];

    }
	
    $query_str = substr($query_str, 0, -5);
    
			//check if hair, activity, or size attributes selected in puppypicker.php and add to query string
    if (isset($_GET['hair']) || isset($_GET['activity']) || isset($_GET['size'])) {
		//build query string and run SQL query
        $sql  = "SELECT breed FROM Puppies" . $query_str;
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $result     = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt       = null;
        $pickedPups = json_decode(json_encode($result), true);
        
		//count number of results
        $countPups = count($pickedPups);
		
		//if there are more than one result from above search, then run code
		  if ($countPups >= 2) {
?>
        There are <?php echo $countPups; ?> matching puppies:
<?php
		  }
		 		  if ($countPups > 0 && $countPups < 2) {
?>
        There is <?php echo $countPups; ?> matching puppy:
<?php
		  } 
		  
		
		 //Petfinder API:
		$petfinderKey = 'KEY';
		$petfinderSecret = 'KEY';

		//using curl in PHP from https://stackoverflow.com/a/25425751 	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.petfinder.com/v2/oauth2/token");
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
		'client_id' => $petfinderKey,
		'client_secret' => $petfinderSecret,
		'grant_type' => 'client_credentials'
		));

		//using the Access Token: https://quizlet.com/api/2.0/docs/making-api-calls
		$petfinderData = json_decode(curl_exec($ch), true);
		$petfinderAccessToken = $petfinderData['access_token'];   

        //Display chosen puppies, loop through all of them
        foreach ($pickedPups as $key => $breed) {
            
            
            foreach ($breed as $attribute => $values) {
                
                if ($countPups > 0) {
                    
					//shift through array by one to get next result, save as replacepickedPups array
                    $replacepickedPups = array_shift($pickedPups);
					
					//remove replacepickedPups from array format and have each puppy saved in q after each loop
                    $puppyTypeResult  = implode(', ', $replacepickedPups);
                    
					//YouTube API Key
                    $youtubeKey = "KEY";
                    
                    // generate YouTube API URL
                    $feedURL   = file_get_contents("https://content.googleapis.com/youtube/v3/search?maxResults=1&part=snippet&q=" . urlencode($puppyTypeResult) . "%20facts&type=video&key=" . urldecode($youtubeKey));
                    $strDecode = json_decode($feedURL, true);
                                        
                    //video id:
                    $videoID = $strDecode["items"]["0"]["id"]["videoId"];
                    
                    //video title:
                    $videoTitle = $strDecode["items"]["0"]["snippet"]["title"];
                    
                    
                    //check if vidId already in database
                    $selectVidID = "SELECT vidID FROM vidPups WHERE vidID = '$videoID';";
                    $prepvidID   = $dbh->prepare($selectVidID);
                    $prepvidID->execute();
                    $vidIDResult = $prepvidID->fetchAll(PDO::FETCH_OBJ);
                    $prepvidID   = null;
                    
                    if ($vidIDResult == null) {
                        
                        //insert new videoId and videoTitle to database
                        $query   = "INSERT INTO vidPups (vidID, vidTitle) VALUES ('$videoID', '$videoTitle')";
                        $vidStmt = $dbh->prepare($query);
                        $vidStmt->execute();
                        $vidStmt = null;
                    }
                    
                    //select correct videoID to find youtube video
                    $selectVidID = "SELECT vidID FROM vidPups WHERE vidID = '$videoID';";
                    $prepvidID   = $dbh->prepare($selectVidID);
                    $prepvidID->execute();
                    $vidIDResult = $prepvidID->fetchAll(PDO::FETCH_OBJ);
                    $prepvidID   = null;
                    
                    //turn result from array to string and trim it
                    $vidIDResultString      = json_encode($vidIDResult);
                    $vidIDResultStringTrim1 = trim($vidIDResultString, '[{"vidID":"');
                    $vidIDResultStringTrim2 = trim($vidIDResultStringTrim1, '"}]');
                    
                    //display youtube video
		?>
		<div class="slideshow-container">

		<div class="mySlides fade">
		  <iframe src="https://www.youtube.com/embed/<?php echo $vidIDResultStringTrim2; ?> " allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"  style="width: 700px; height: 500px; " allowfullscreen></iframe>
							<div class="text"><?php echo $values; ?></div>

				<?php
	    
		//Search for dog by breed:  
		$petfinderApiUrl = "https://api.petfinder.com/v2/animals?breed=". urlencode($puppyTypeResult) . "&location=". urlencode($zipCode) ."&distance=100&status=adoptable&limit=3";
		  
		  
		$curl = curl_init($petfinderApiUrl);
		curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$petfinderAccessToken]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$jsonPetfinder = curl_exec($curl);
		curl_close($curl);

	
	$petStrDecode = json_decode($jsonPetfinder, true);

	
	//Dog Name 0
	$dogName0 = $petStrDecode["animals"]["0"]["name"];
	//Dog Age 0
	$dogAge0 = $petStrDecode["animals"]["0"]["age"];
	//Dog Gender 0
	$dogGender0 = $petStrDecode["animals"]["0"]["gender"];
	//Dog Contact 0
	$dogContactCity0 = $petStrDecode["animals"]["0"]["contact"]["address"]["city"];
	$dogContactState0 = $petStrDecode["animals"]["0"]["contact"]["address"]["state"];
	$dogContactZip0 = $petStrDecode["animals"]["0"]["contact"]["address"]["postcode"];
	$dogContactCountry0 = $petStrDecode["animals"]["0"]["contact"]["address"]["country"];
	//Dog URL 0
	$dogURL0 = $petStrDecode["animals"]["0"]["url"];
	//Dog Photo 0
	$dogPhoto0 = $petStrDecode["animals"]["0"]["photos"]["0"]["full"];
		
	$query   = "INSERT INTO adoptPups0 (dogName0, dogContactCity0, dogContactState0, 
				dogContactZip0, dogContactCountry0) 
				VALUES ('$dogName0', '$dogContactCity0', '$dogContactState0', '$dogContactZip0', '$dogContactCountry0')";
	$vidStmt = $dbh->prepare($query);
	$vidStmt->execute();
	$vidStmt = null;
	
	//Dog Name 1
	$dogName1 = $petStrDecode["animals"]["1"]["name"];
	//Dog Age 1
	$dogAge1 = $petStrDecode["animals"]["1"]["age"];
	//Dog Gender 1
	$dogGender1 = $petStrDecode["animals"]["1"]["gender"];
	//Dog Contact 1
	$dogContactCity1 = $petStrDecode["animals"]["1"]["contact"]["address"]["city"];
	$dogContactState1 = $petStrDecode["animals"]["1"]["contact"]["address"]["state"];
	$dogContactZip1 = $petStrDecode["animals"]["1"]["contact"]["address"]["postcode"];
	$dogContactCountry1 = $petStrDecode["animals"]["1"]["contact"]["address"]["country"];	
	//Dog URL 1
	$dogURL1 = $petStrDecode["animals"]["1"]["url"];
	//Dog Photo 1
	$dogPhoto1 = $petStrDecode["animals"]["1"]["photos"]["0"]["full"];				
  
  	$query   = "INSERT INTO adoptPups1 (dogName1, dogContactCity1, dogContactState1, dogContactZip1, dogContactCountry1) 
				VALUES ('$dogName1', '$dogContactCity1', '$dogContactState1', '$dogContactZip1', '$dogContactCountry1')";
	$vidStmt = $dbh->prepare($query);
	$vidStmt->execute();
	$vidStmt = null;
	
  	//Dog Name 2
	$dogName2 = $petStrDecode["animals"]["2"]["name"];
	//Dog Age 2
	$dogAge2 = $petStrDecode["animals"]["2"]["age"];
	//Dog Gender 2
	$dogGender2 = $petStrDecode["animals"]["2"]["gender"];
	//Dog Contact 2
	$dogContactCity2 = $petStrDecode["animals"]["2"]["contact"]["address"]["city"];
	$dogContactState2 = $petStrDecode["animals"]["2"]["contact"]["address"]["state"];
	$dogContactZip2 = $petStrDecode["animals"]["2"]["contact"]["address"]["postcode"];
	$dogContactCountry2 = $petStrDecode["animals"]["2"]["contact"]["address"]["country"];
	//Dog URL 2
	$dogURL2 = $petStrDecode["animals"]["2"]["url"];
	//Dog Photo 2
	$dogPhoto2 = $petStrDecode["animals"]["2"]["photos"]["0"]["full"];

	$query   = "INSERT INTO adoptPups2 (dogName2, dogContactCity2, dogContactState2, 
			dogContactZip2, dogContactCountry2) 
			VALUES ('$dogName2', '$dogContactCity2', '$dogContactState2', '$dogContactZip2', '$dogContactCountry2')";
	$vidStmt = $dbh->prepare($query);
	$vidStmt->execute();
	$vidStmt = null;
		
		
			 echo '<script>';
  echo 'console.log('. json_encode( $dogContactZip2 ) .')';
  echo '</script>';
  
  			 echo '<script>';
  echo 'console.log('. json_encode( $dogContactZip1 ) .')';
  echo '</script>';
  
  			 echo '<script>';
  echo 'console.log('. json_encode( $dogContactZip0 ) .')';
  echo '</script>';

	?>
<h2>Adoptable Dogs Near You!</h2>

<table style="width:100%">

  <tr>
    <td><img src="<?php echo $dogPhoto0; ?>" alt="<?php echo $puppyTypeResult; ?>" width="200" height="200">   <br>
    <?php echo $dogName0; ?> <br>
    <?php echo $dogGender0; ?> <br>
	<?php echo $dogAge0; ?> <br>
    <a href="<?php echo $dogURL0; ?>" target="_blank" >More Info!</a></td>
	
     <td><img src="<?php echo $dogPhoto1; ?> " alt="<?php echo $puppyTypeResult; ?>"  width="200" height="200">   <br>
    <?php echo $dogName1; ?> <br>
    <?php echo $dogGender1; ?> <br>
	<?php echo $dogAge1; ?> <br>
    <a href="<?php echo $dogURL1; ?>" target="_blank">More Info!</a></td>
	
     <td><img src="<?php echo $dogPhoto2; ?> " alt="<?php echo $puppyTypeResult; ?>" width="200" height="200">   <br>
    <?php echo $dogName2; ?> <br>
    <?php echo $dogGender2; ?><br>
	<?php echo $dogAge2; ?> <br>
    <a href="<?php echo $dogURL2; ?>" target="_blank">More Info!</a></td>
  </tr>
</table>
	
</div>
</div>   		
<?php
				}
            }
        }
					  if ($countPups > 0) {
//Move to next or previous result
?>
<a class="prev" onclick="plusSlides(-1)">&#10094;</a>
<a class="next" onclick="plusSlides(1)">&#10095;</a>
		
	  <?php 	
    }

       
	
	

  } ?>
			
			
			
<br>

<script>
var slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  var i;
  var slides = document.getElementsByClassName("mySlides");
  var dots = document.getElementsByClassName("dot");
  if (n > slides.length) {slideIndex = 1}    
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";  
  }
  for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";  

}
</script>
<?php
    
}

//if no puppies from database match user selected attributes:
if (empty($values)) {
    echo "<br/>" . "Sorry, no puppies found in our database that match your request.";
}



?>     

</body>

</html>
