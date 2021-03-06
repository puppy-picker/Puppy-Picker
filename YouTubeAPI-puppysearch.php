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
		$petfinderKey = 'key';
		$petfinderSecret = 'key';

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
                    $youtubeKey = "key";
                    
                    // generate YouTube API URL
					$context = stream_context_create(['http' => [
					'ignore_errors' => true,
						]]);
						
					$ytURL = "https://content.googleapis.com/youtube/v3/search?maxResults=1&part=snippet&q=" . urlencode($puppyTypeResult) . "%20facts&type=video&key=" . urldecode($youtubeKey);
                    $feedURL   = file_get_contents($ytURL, false, $context);
					
   					$strDecode = json_decode($feedURL, true);


					if(array_key_exists("error", $strDecode)) 
					{
						
					$puppyTypeResult = '"' . $puppyTypeResult . '"';
					
					//select correct videoID to find youtube video based on breed
                    $selectVidID = "SELECT vidID FROM vidPups WHERE breed = $puppyTypeResult ORDER BY ID DESC LIMIT 1;";
                    $prepvidID   = $dbh->prepare($selectVidID);
                    $prepvidID->execute();
                    $vidIDResult = $prepvidID->fetchAll(PDO::FETCH_OBJ);
                    $prepvidID   = null;
                    
					
                    //turn result from array to string and trim it
                    $vidIDResultString      = json_encode($vidIDResult);
                    $vidIDResultStringTrim1 = trim($vidIDResultString, '[{"vidID":"');
                    $vidIDResultStringTrim2 = trim($vidIDResultStringTrim1, '"}]');					
					}
					else{

                    //video id:
                    $videoID = $strDecode["items"]["0"]["id"]["videoId"];
                    $videoID = '"' . $videoID . '"';

                    //video title:
                    $videoTitle = $strDecode["items"]["0"]["snippet"]["title"];
                    $videoTitle = '"' . $videoTitle . '"';
                    
                    //check if vidId already in database
                    $selectVidID = "SELECT vidID FROM vidPups WHERE vidID = $videoID;";
                    $prepvidID   = $dbh->prepare($selectVidID);
                    $prepvidID->execute();
                    $vidIDResult = $prepvidID->fetchAll(PDO::FETCH_OBJ);
                    $prepvidID   = null;

                    if ($vidIDResult == null) {
						
						$puppyTypeResult = '"' . $puppyTypeResult . '"';

                        
                        //insert new videoId and videoTitle to database
                        $query   = "INSERT INTO vidPups (vidID, vidTitle, breed) VALUES ($videoID, $videoTitle, $puppyTypeResult);";
                        $vidStmt = $dbh->prepare($query);
                        $vidStmt->execute();
                        $vidStmt = null;
						
                    }
                    
                    //select correct videoID to find youtube video
                    $selectVidID = "SELECT vidID FROM vidPups WHERE vidID = $videoID ORDER BY ID DESC LIMIT 1;";
                    $prepvidID   = $dbh->prepare($selectVidID);
                    $prepvidID->execute();
                    $vidIDResult = $prepvidID->fetchAll(PDO::FETCH_OBJ);
                    $prepvidID   = null;
                    
					
                    //turn result from array to string and trim it
                    $vidIDResultString      = json_encode($vidIDResult);
                    $vidIDResultStringTrim1 = trim($vidIDResultString, '[{"vidID":"');
                    $vidIDResultStringTrim2 = trim($vidIDResultStringTrim1, '"}]');
                    }
					
                    //display youtube video for each select dog breed:
		?>
		<div class="slideshow-container">

		<div class="mySlides fade">
		  <iframe src="https://www.youtube.com/embed/<?php echo $vidIDResultStringTrim2; ?> " allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"  style="width: 700px; height: 500px; " allowfullscreen></iframe>
							<div class="text"><?php echo $values; ?></div>

				<?php
	    
				if(!empty($zipCode)){
  
		//Search for dog by breed on Petfinder:  
		$puppyTypeResult = trim($puppyTypeResult, '"');
		
		$puppyTypeResult = trim($puppyTypeResult, '"');

		$petfinderApiUrl = "https://api.petfinder.com/v2/animals?breed=". urlencode($puppyTypeResult) . "&location=". urlencode($zipCode) ."&distance=50&status=adoptable&limit=3";
		  
		  
		$curl = curl_init($petfinderApiUrl);
		curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$petfinderAccessToken]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$jsonPetfinder = curl_exec($curl);
		curl_close($curl);

	
	$petStrDecode = json_decode($jsonPetfinder, true);


  
  	//Dog Total Count
	$dogTotalCount = $petStrDecode["pagination"]["total_count"];
  
  if($dogTotalCount > 0){
	  
//DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 -->
  if($dogTotalCount == 1 || $dogTotalCount == 2 || $dogTotalCount == 3 || $dogTotalCount > 3){

	//Dog Name 0
	$dogName0 = $petStrDecode["animals"]["0"]["name"];
	$dogName0 =  '"' . $dogName0 .'"';
	
	//Dog ID 0
	$dogID0 = $petStrDecode["animals"]["0"]["id"];
	$dogID0 =  '"' . $dogID0 .'"';
	
	//Dog Age 0
	$dogAge0 = $petStrDecode["animals"]["0"]["age"];
	$dogAge0 =  '"' . $dogAge0 .'"';

	//Dog Gender 0
	$dogGender0 = $petStrDecode["animals"]["0"]["gender"];
	$dogGender0 =  '"' . $dogGender0 .'"';

	//Dog Contact 0
	$dogContactCity0 = $petStrDecode["animals"]["0"]["contact"]["address"]["city"];
	$dogContactCity0 =  '"' . $dogContactCity0 .'"';
	
	$dogContactState0 = $petStrDecode["animals"]["0"]["contact"]["address"]["state"];
	$dogContactState0 =  '"' . $dogContactState0 .'"';
	
	$dogContactZip0 = $petStrDecode["animals"]["0"]["contact"]["address"]["postcode"];
	$dogContactZip0 =  '"' . $dogContactZip0 .'"';
	
	$dogContactCountry0 = $petStrDecode["animals"]["0"]["contact"]["address"]["country"];
	$dogContactCountry0 =  '"' . $dogContactCountry0 .'"';

	//Dog URL 0
	$dogURL0 = $petStrDecode["animals"]["0"]["url"];
	
	//Dog Photo 0
	$dogPhotoLength0 = $petStrDecode["animals"]["0"]["photos"];


	$dogPhotoLength0Count = count($dogPhotoLength0);
	if($dogPhotoLength0Count == 0){
		
		$dogPhoto0 =  "noPhoto.png";

	}
	else{
	
	$dogPhoto0 = $petStrDecode["animals"]["0"]["photos"]["0"]["full"];
		
	}
  
			$pupQuery0   = "INSERT INTO adoptPups0 (dogName0, dogGender0, dogAge0, petfinderID0, dogContactCity0, dogContactState0, dogContactZip0, dogContactCountry0) VALUES ($dogName0, $dogGender0, $dogAge0, $dogID0, $dogContactCity0, $dogContactState0, $dogContactZip0, $dogContactCountry0)";
			$pupStmt0 = $dbh->prepare($pupQuery0);
			$pupStmt0->execute();
			$pupStmt0 = null;

			//select from petfinder dog demographis for each dog in group 0
			$selectDogDemo0 = "SELECT dogName0, dogGender0, dogAge0 FROM adoptPups0 WHERE petfinderID0 = $dogID0 Limit 1;";
			$prepDogDemo0   = $dbh->prepare($selectDogDemo0);
			$prepDogDemo0->execute();
			$fetchDogDemo0 = $prepDogDemo0->fetchAll(PDO::FETCH_OBJ);
			$prepDogDemo0   = null;
			$dogDemo0 = json_decode(json_encode($fetchDogDemo0), true);

			//select from petfinder COUNTRY for each dog in group 0
			$selectPetfinder0 = "SELECT dogContactCountry0 AS '' FROM adoptPups0 WHERE petfinderID0 = $dogID0 Limit 1;";
			$prepselectPetfinder0   = $dbh->prepare($selectPetfinder0);
			$prepselectPetfinder0->execute();
			$PetfinderCountry0 = $prepselectPetfinder0->fetchAll(PDO::FETCH_OBJ);
			$prepselectPetfinder0   = null;
			$PetfinderCountry0Encode = json_encode($PetfinderCountry0);
			$PetfinderCountry0EncodeTrim1 = trim($PetfinderCountry0Encode, '[{"":"');
            $PetfinderCountry0EncodeTrim2 = trim($PetfinderCountry0EncodeTrim1, '"}]');
                    


			//select from petfinder ZIP CODE for each dog in group 0
			$selectPetfinderZ0 = "SELECT dogContactZip0 AS '' FROM adoptPups0 WHERE petfinderID0 = $dogID0 Limit 1;";
			$prepselectPetfinderZ0   = $dbh->prepare($selectPetfinderZ0);
			$prepselectPetfinderZ0->execute();
			$PetfinderZip0 = $prepselectPetfinderZ0->fetchAll(PDO::FETCH_OBJ);
			$prepselectPetfinderZ0   = null;
			$PetfinderZip0Encode = json_encode($PetfinderZip0);
			$PetfinderZip0EncodeTrim1 = trim($PetfinderZip0Encode, '[{"":"');
            $PetfinderZip0EncodeTrim2 = trim($PetfinderZip0EncodeTrim1, '"}]');



	$baseTomTomURL = 'api.tomtom.com';
	$suppliesQuery = 'pet supplies';
	$tomtomkey1 = 'key';
	
	//part 1: getting lat and lon values from an entered zip
	//here you would use the $dogContactZip0
	//This first url gets lat and lon values for a more accurate result in part 2
	$url = "https://" . urlencode($baseTomTomURL)."/search/2/structuredGeocode.json?key=".urlencode($tomtomkey1)."&countryCode=".urlencode($PetfinderCountry0EncodeTrim2)."&limit=10&postalCode=".urlencode($PetfinderZip0EncodeTrim2);

	
	$content = file_get_contents($url);
	$json = json_decode($content, true);
	    
	//finally!
	 $lat = $json["results"]["0"]["position"]["lat"];
	 $lon = $json["results"]["0"]["position"]["lon"];
	
	//example of what url should look like to get json of pet stores
	//https://api.tomtom.com/search/2/poiSearch/pet%20store.json?limit=3&countrySet=US&lat=39.71246&lon=-74.25372&key=CALC1EJWVupfChMGr8CT7vpEzll896Gs
	
	//Part 2: this will yeild the json we need for the pet stores (ignore the naming convention)
	$petshops = "https://".urlencode($baseTomTomURL)."/search/2/categorySearch/".urlencode($suppliesQuery).".json?limit=3&countrySet=".urlencode($PetfinderCountry0EncodeTrim2)."&lat=".urlencode($lat)."&lon=".urlencode($lon). "&radius=48281&key=".urlencode($tomtomkey1); //finally!
	
	$petResults = file_get_contents($petshops);

	$info = json_decode($petResults, true);


		//parsing out information
		//this is what we hope to echo out
		$store_name = $info["results"]["0"]["poi"]["name"];
		$store_name = '"' . $store_name . '"';
		
		$store_phone = $info["results"]["0"]["poi"]["phone"];
		$store_phone = '"' . $store_phone . '"';
		
		$store_address = $info["results"]["0"]["address"]["freeformAddress"];
		$store_address = '"' . $store_address . '"';
		
		$store_zip = $info["results"]["0"]["address"]["postalCode"];
		$store_zip = '"' . $store_zip . '"';
	
		$insertion = "INSERT INTO stores (name, phone, address, zip, petfinderID) VALUES ($store_name, $store_phone, $store_address, $store_zip, $dogID0)";
		$insertionStmt = $dbh->prepare($insertion);
		$insertionStmt->execute();
		$insertionStmt = null;
	
	
	//END OF IMPORTANT CODE FOR PUPPY SEARCH
$query0 = "SELECT DISTINCT * FROM stores WHERE petfinderID = $dogID0 Limit 1";
	
	 $query0Stmt = $dbh->prepare($query0);
        $query0Stmt->execute();
        $result0     = $query0Stmt->fetchAll(PDO::FETCH_OBJ);
        $query0Stmt       = null;
        $stores0 = json_decode(json_encode($result0), true);
		
//DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 -->

//DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 -->
  if($dogTotalCount == 2 || $dogTotalCount == 3 || $dogTotalCount > 3){

		//Dog Name 1
	$dogName1 = $petStrDecode["animals"]["1"]["name"];
	$dogName1 =  '"' . $dogName1 .'"';
	
	//Dog ID 1
	$dogID1 = $petStrDecode["animals"]["1"]["id"];
	$dogID1 =  '"' . $dogID1 .'"';
	
	//Dog Age 1
	$dogAge1 = $petStrDecode["animals"]["1"]["age"];
	$dogAge1 =  '"' . $dogAge1 .'"';

	//Dog Gender 1
	$dogGender1 = $petStrDecode["animals"]["1"]["gender"];
	$dogGender1 =  '"' . $dogGender1 .'"';

	//Dog Contact 1
	$dogContactCity1 = $petStrDecode["animals"]["1"]["contact"]["address"]["city"];
	$dogContactCity1 =  '"' . $dogContactCity1 .'"';
	
	$dogContactState1 = $petStrDecode["animals"]["1"]["contact"]["address"]["state"];
	$dogContactState1 =  '"' . $dogContactState1 .'"';
	
	$dogContactZip1 = $petStrDecode["animals"]["1"]["contact"]["address"]["postcode"];
	$dogContactZip1 =  '"' . $dogContactZip1 .'"';
	
	$dogContactCountry1 = $petStrDecode["animals"]["1"]["contact"]["address"]["country"];
	$dogContactCountry1 =  '"' . $dogContactCountry1 .'"';

	//Dog URL 1
	$dogURL1 = $petStrDecode["animals"]["1"]["url"];
	
	//Dog Photo 1
	$dogPhotoLength1 = $petStrDecode["animals"]["1"]["photos"];


	$dogPhotoLength1Count = count($dogPhotoLength1);
	if($dogPhotoLength1Count == 0){
		
		$dogPhoto1 =  "noPhoto.png";

	}
	else{
	
	$dogPhoto1 = $petStrDecode["animals"]["1"]["photos"]["0"]["full"];
		
	}
  
			$pupQuery1   = "INSERT INTO adoptPups1 (dogName1, dogGender1, dogAge1, petfinderID1, dogContactCity1, dogContactState1, dogContactZip1, dogContactCountry1) VALUES ($dogName1, $dogGender1, $dogAge1, $dogID1, $dogContactCity1, $dogContactState1, $dogContactZip1, $dogContactCountry1)";
			$pupStmt1 = $dbh->prepare($pupQuery1);
			$pupStmt1->execute();
			$pupStmt1 = null;

			//select from petfinder dog demographis for each dog in group 1
			$selectDogDemo1 = "SELECT dogName1, dogGender1, dogAge1 FROM adoptPups1 WHERE petfinderID1 = $dogID1 Limit 1;";
			$prepDogDemo1   = $dbh->prepare($selectDogDemo1);
			$prepDogDemo1->execute();
			$fetchDogDemo1 = $prepDogDemo1->fetchAll(PDO::FETCH_OBJ);
			$prepDogDemo1   = null;
			$dogDemo1 = json_decode(json_encode($fetchDogDemo1), true);

			//select from petfinder COUNTRY for each dog in group 1
			$selectPetfinder1 = "SELECT dogContactCountry1 AS '' FROM adoptPups1 WHERE petfinderID1 = $dogID1 Limit 1;";
			$prepselectPetfinder1   = $dbh->prepare($selectPetfinder1);
			$prepselectPetfinder1->execute();
			$PetfinderCountry1 = $prepselectPetfinder1->fetchAll(PDO::FETCH_OBJ);
			$prepselectPetfinder1   = null;
			$PetfinderCountry1Encode = json_encode($PetfinderCountry1);
			$PetfinderCountry1EncodeTrim1 = trim($PetfinderCountry1Encode, '[{"":"');
            $PetfinderCountry1EncodeTrim2 = trim($PetfinderCountry1EncodeTrim1, '"}]');
                    
			//select from petfinder ZIP CODE for each dog in group 1
			$selectPetfinderZ1 = "SELECT dogContactZip1 AS '' FROM adoptPups1 WHERE petfinderID1 = $dogID1 Limit 1;";
			$prepselectPetfinderZ1   = $dbh->prepare($selectPetfinderZ1);
			$prepselectPetfinderZ1->execute();
			$PetfinderZip1 = $prepselectPetfinderZ1->fetchAll(PDO::FETCH_OBJ);
			$prepselectPetfinderZ1   = null;
			$PetfinderZip1Encode = json_encode($PetfinderZip1);
			$PetfinderZip1EncodeTrim1 = trim($PetfinderZip1Encode, '[{"":"');
            $PetfinderZip1EncodeTrim2 = trim($PetfinderZip1EncodeTrim1, '"}]');


	$baseTomTomURL = 'api.tomtom.com';
	$suppliesQuery = 'pet supplies';
	$tomtomkey2 = 'key';
	
		//part 1: getting lat and lon values from an entered zip
	//here you would use the $dogContactZip0
	//This first url gets lat and lon values for a more accurate result in part 1
	$url1 = "https://" . urlencode($baseTomTomURL)."/search/2/structuredGeocode.json?key=".urlencode($tomtomkey2)."&countryCode=".urlencode($PetfinderCountry1EncodeTrim2)."&limit=10&postalCode=".urlencode($PetfinderZip1EncodeTrim2); //added (Diane)



	$content1 = file_get_contents($url1);
	$json1 = json_decode($content1, true);
	    
	//finally!
	 $lat1 = $json1["results"]["0"]["position"]["lat"];
	 $lon1 = $json1["results"]["0"]["position"]["lon"];
	
	//example of what url should look like to get json of pet stores
	//https://api.tomtom.com/search/1/poiSearch/pet%10store.json?limit=3&countrySet=US&lat=39.71146&lon=-74.15371&key=CALC1EJWVupfChMGr8CT7vpEzll896Gs
	
	//Part 1: this will yeild the json we need for the pet stores (ignore the naming convention)
	$petshops1 = "https://".urlencode($baseTomTomURL)."/search/2/categorySearch/".urlencode($suppliesQuery).".json?limit=3&countrySet=".urlencode($PetfinderCountry1EncodeTrim2)."&lat=".urlencode($lat1)."&lon=".urlencode($lon1)."&radius=48281&key=".urlencode($tomtomkey2); //added (Diane)

  
	$petResults1 = file_get_contents($petshops1);
	

	$info1 = json_decode($petResults1, true);
	
//parsing out information
		//this is what we hope to echo out
		$store_name1 = $info1["results"]["0"]["poi"]["name"];
		$store_name1 = '"' . $store_name1 . '"';
		
		$store_phone1 = $info1["results"]["0"]["poi"]["phone"];
		$store_phone1 = '"' . $store_phone1 . '"';
		
		$store_address1 = $info1["results"]["0"]["address"]["freeformAddress"];
		$store_address1 = '"' . $store_address1 . '"';
		
		$store_zip1 = $info1["results"]["0"]["address"]["postalCode"];
		$store_zip1 = '"' . $store_zip1 . '"';
	
		$insertion1 = "INSERT INTO stores (name, phone, address, zip, petfinderID) VALUES ($store_name1, $store_phone1, $store_address1, $store_zip1, $dogID1)";
		$insertionStmt1 = $dbh->prepare($insertion1);
		$insertionStmt1->execute();
		$insertionStmt1 = null;

		//END OF IMPORTANT CODE FOR PUPPY SEARCH
	$query1 = "SELECT DISTINCT * FROM stores WHERE petfinderID = $dogID1 Limit 1";
	 $query1Stmt = $dbh->prepare($query1);
        $query1Stmt->execute();
        $result1   = $query1Stmt->fetchAll(PDO::FETCH_OBJ);
        $query1Stmt       = null;
        $stores1 = json_decode(json_encode($result1), true);
			
//DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 -->

//DOG2 DOG2 DOG2 DOG2 DOG2 DOG2	DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 -->
  if($dogTotalCount == 3 || $dogTotalCount > 3){
  	
	//Dog Name 2
	$dogName2 = $petStrDecode["animals"]["2"]["name"];
	$dogName2 =  '"' . $dogName2 .'"';
	
	//Dog ID 2
	$dogID2 = $petStrDecode["animals"]["2"]["id"];
	$dogID2 =  '"' . $dogID2 .'"';
	
	//Dog Age 2
	$dogAge2 = $petStrDecode["animals"]["2"]["age"];
	$dogAge2 =  '"' . $dogAge2 .'"';

	//Dog Gender 2
	$dogGender2 = $petStrDecode["animals"]["2"]["gender"];
	$dogGender2 =  '"' . $dogGender2 .'"';

	//Dog Contact 2
	$dogContactCity2 = $petStrDecode["animals"]["2"]["contact"]["address"]["city"];
	$dogContactCity2 =  '"' . $dogContactCity2 .'"';
	
	$dogContactState2 = $petStrDecode["animals"]["2"]["contact"]["address"]["state"];
	$dogContactState2 =  '"' . $dogContactState2 .'"';
	
	$dogContactZip2 = $petStrDecode["animals"]["2"]["contact"]["address"]["postcode"];
	$dogContactZip2 =  '"' . $dogContactZip2 .'"';
	
	$dogContactCountry2 = $petStrDecode["animals"]["2"]["contact"]["address"]["country"];
	$dogContactCountry2 =  '"' . $dogContactCountry2 .'"';

	//Dog URL 2
	$dogURL2 = $petStrDecode["animals"]["2"]["url"];
	
	//Dog Photo 2
	$dogPhotoLength2 = $petStrDecode["animals"]["2"]["photos"];


	$dogPhotoLength2Count = count($dogPhotoLength2);

	if($dogPhotoLength2Count == 0){
		
		
		$dogPhoto2 =  "noPhoto.png";

	}
	else{
	
	$dogPhoto2 = $petStrDecode["animals"]["2"]["photos"]["0"]["full"];
		
	}
  
 	$pupQuery2   = "INSERT INTO adoptPups2 (dogName2, dogGender2, dogAge2, petfinderID2, dogContactCity2, dogContactState2, dogContactZip2, dogContactCountry2) VALUES ($dogName2, $dogGender2, $dogAge2, $dogID2, $dogContactCity2, $dogContactState2, $dogContactZip2, $dogContactCountry2)";
			$pupStmt2 = $dbh->prepare($pupQuery2);
			$pupStmt2->execute();
			$pupStmt2 = null;

			//select from petfinder dog demographis for each dog in group 2
			$selectDogDemo2 = "SELECT dogName2, dogGender2, dogAge2 FROM adoptPups2 WHERE petfinderID2 = $dogID2 Limit 1;";
			$prepDogDemo2   = $dbh->prepare($selectDogDemo2);
			$prepDogDemo2->execute();
			$fetchDogDemo2 = $prepDogDemo2->fetchAll(PDO::FETCH_OBJ);
			$prepDogDemo2   = null;
			$dogDemo2 = json_decode(json_encode($fetchDogDemo2), true);

			//select from petfinder COUNTRY for each dog in group 2
			$selectPetfinder2 = "SELECT dogContactCountry2 AS '' FROM adoptPups2 WHERE petfinderID2 = $dogID2 Limit 1;";
			$prepselectPetfinder2   = $dbh->prepare($selectPetfinder2);
			$prepselectPetfinder2->execute();
			$PetfinderCountry2 = $prepselectPetfinder2->fetchAll(PDO::FETCH_OBJ);
			$prepselectPetfinder2   = null;
			$PetfinderCountry2Encode = json_encode($PetfinderCountry2);
			$PetfinderCountry2EncodeTrim1 = trim($PetfinderCountry2Encode, '[{"":"');
            $PetfinderCountry2EncodeTrim2 = trim($PetfinderCountry2EncodeTrim1, '"}]');
                    
			//select from petfinder ZIP CODE for each dog in group 2
			$selectPetfinderZ2 = "SELECT dogContactZip2 AS '' FROM adoptPups2 WHERE petfinderID2 = $dogID2 Limit 1;";
			$prepselectPetfinderZ2   = $dbh->prepare($selectPetfinderZ2);
			$prepselectPetfinderZ2->execute();
			$PetfinderZip2 = $prepselectPetfinderZ2->fetchAll(PDO::FETCH_OBJ);
			$prepselectPetfinderZ2   = null;
			$PetfinderZip2Encode = json_encode($PetfinderZip2);
			$PetfinderZip2EncodeTrim1 = trim($PetfinderZip2Encode, '[{"":"');
            $PetfinderZip2EncodeTrim2 = trim($PetfinderZip2EncodeTrim1, '"}]');

	$baseTomTomURL = 'api.tomtom.com';
	$suppliesQuery = 'pet supplies';
	$tomtomkey3 = 'key';
	
	//part 1: getting lat and lon values from an entered zip
	//here you would use the $dogContactZip2
	//This first url gets lat and lon values for a more accurate result in part 2
	$url2 = "https://" . urlencode($baseTomTomURL)."/search/2/structuredGeocode.json?key=".urlencode($tomtomkey3)."&countryCode=".urlencode($PetfinderCountry2EncodeTrim2)."&limit=12&postalCode=".urlencode($PetfinderZip2EncodeTrim2);

	
	$content2 = file_get_contents($url2);
	$json2 = json_decode($content2, true);
	    
	//finally!
	 $lat2 = $json2["results"]["0"]["position"]["lat"];
	 $lon2 = $json2["results"]["0"]["position"]["lon"];
	
	//example of what url should look like to get json of pet stores
	//https://api.tomtom.com/search/2/poiSearch/pet%22store.json?limit=3&countrySet=US&lat=39.71246&lon=-74.25372&key=CALC1EJWVupfChMGr8CT7vpEzll896Gs
	
	//Part 2: this will yeild the json we need for the pet stores (ignore the naming convention)
	$petshops2 = "https://".urlencode($baseTomTomURL)."/search/2/categorySearch/".urlencode($suppliesQuery).".json?limit=3&countrySet=".urlencode($PetfinderCountry2EncodeTrim2)."&lat=".urlencode($lat2)."&lon=".urlencode($lon2)."&radius=48281&key=".urlencode($tomtomkey3); //finally!
	
	$petResults2 = file_get_contents($petshops2);

	$info2 = json_decode($petResults, true);


		//parsing out information
		//this is what we hope to echo out
		$store_name2 = $info2["results"]["0"]["poi"]["name"];
		$store_name2 = '"' . $store_name2 . '"';
		
		$store_phone2 = $info2["results"]["0"]["poi"]["phone"];
		$store_phone2 = '"' . $store_phone2 . '"';
		
		$store_address2 = $info2["results"]["0"]["address"]["freeformAddress"];
		$store_address2 = '"' . $store_address2 . '"';
		
		$store_zip2 = $info2["results"]["0"]["address"]["postalCode"];
		$store_zip2 = '"' . $store_zip2 . '"';
	
		$insertion2 = "INSERT INTO stores (name, phone, address, zip, petfinderID) VALUES ($store_name2, $store_phone2, $store_address2, $store_zip2, $dogID2)";
		$insertionStmt2 = $dbh->prepare($insertion2);
		$insertionStmt2->execute();
		$insertionStmt2 = null;
	
	
	//END OF IMPORTANT CODE FOR PUPPY SEARCH
$query2 = "SELECT DISTINCT * FROM stores WHERE petfinderID = $dogID2 Limit 1";
	
	 $query2Stmt = $dbh->prepare($query2);
        $query2Stmt->execute();
        $result2     = $query2Stmt->fetchAll(PDO::FETCH_OBJ);
        $query2Stmt       = null;
        $stores2 = json_decode(json_encode($result2), true);

//DOG2 DOG2 DOG2 DOG2 DOG2 DOG2	DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 -->
  }}}}
//display petfinder dogs plus nearest pet shops to them:	
	else{
	
		    echo "<br/>" . "Sorry, no dogs found in Petfinder that match your request." . "<br/>";

  }
	  if($dogTotalCount > 0){
?>
<h2>Adoptable Dogs Near You!</h2>

<table style="width:100%">

	  <?php }
	  if($dogTotalCount == 1 || $dogTotalCount == 2 || $dogTotalCount == 3 || $dogTotalCount > 3){

?>
 <!--	DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 DOG 0 -->

  <tr>
    <td><img src="<?php echo $dogPhoto0; ?>" alt="<?php echo $puppyTypeResult; ?>" width="200" height="200">   <br>
	 <?php  
	 
    foreach($dogDemo0 as $d0){
    			echo $d0['dogName0'] ;
				echo "<br>";
    			echo $d0['dogGender0'] ;
				echo "<br>";
    			echo $d0['dogAge0'] ;
				echo "<br>";

    }
	
	?>
    <a href="<?php echo $dogURL0; ?>" target="_blank" >Petfinder Page!</a>
	
		<?php
    	echo "<br>";

	
    
    ?>
 Pet Supplies Shop Near this Dog:
 <?php
    foreach($stores0 as $s0){
				    	echo "<br>";

		echo "<table style='width:100%'>";	
    		echo "<tr>";
    			echo "<td>". $s0['name'] . "</td>";
    		echo "</tr>";
    	echo "</table>";
    	
    	echo "<table style='width:100%'>";	
    		echo "<tr>";
    			echo "<td>". $s0['phone'] . "</td>";
    		echo "</tr>";
    	echo "</table>";
    	
    	echo "<table style='width:100%'>";	
    		echo "<tr>";
    			echo "<td>".  $s0['address'] . "</td>";
    		echo "</tr>";
    	echo "</table>";
    	echo "<br>";
    }
	
	?>
	</td>
	<?php    if($dogTotalCount == 2 || $dogTotalCount == 3 || $dogTotalCount > 3){
?>
 <!--	DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 DOG 1 -->
     <td><img src="<?php echo $dogPhoto1; ?> " alt="<?php echo $puppyTypeResult; ?>"  width="200" height="200">   <br>
	 <?php  
	 
    foreach($dogDemo1 as $d1){
    			echo $d1['dogName1'] ;
				echo "<br>";
    			echo $d1['dogGender1'] ;
				echo "<br>";
    			echo $d1['dogAge1'] ;
				echo "<br>";

    }
	
	?>
    <a href="<?php echo $dogURL1; ?>" target="_blank">Petfinder Page!</a><br>
	
		<?php
			

 ?>
 Pet Supplies Shop Near this Dog:
 <?php  
    foreach($stores1 as $s1){
				    	echo "<br>";

		echo "<table style='width:100%'>";	

    		echo "<tr>";
    			echo "<td>". $s1['name'] . "</td>";
    		echo "</tr>";
    	echo "</table>";
    	
    	echo "<table style='width:100%'>";	
    		echo "<tr>";
    			echo "<td>".  $s1['phone'] . "</td>";
    		echo "</tr>";
    	echo "</table>";
    	
    	echo "<table style='width:100%'>";	
    		echo "<tr>";
    			echo "<td>".  $s1['address'] . "</td>";
    		echo "</tr>";
    	echo "</table>";
    	echo "<br>";
    }
	
	?>
	</td>
<!--- DOG2 DOG2 DOG2 DOG2 DOG2 DOG2	DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 DOG2 -->
<?php    if($dogTotalCount == 3 || $dogTotalCount > 3){
?>
     <td><img src="<?php echo $dogPhoto2; ?> " alt="<?php echo $puppyTypeResult; ?>" width="200" height="200">   <br>
	 <?php  
	 
    foreach($dogDemo2 as $d2){
    			echo $d2['dogName2'] ;
				echo "<br>";
    			echo $d2['dogGender2'] ;
				echo "<br>";
    			echo $d2['dogAge2'] ;
				echo "<br>";

    }
	
	?>
    <a href="<?php echo $dogURL2; ?>" target="_blank">Petfinder Page!</a><br>
	
	<?php
		

 ?>
 Pet Supplies Shop Near this Dog:
 <?php
    foreach($stores2 as $s2){
		echo "<br>";

		echo "<table style='width:100%'>";	

    		echo "<tr>";
    			echo "<td>".  $s2['name'] . "</td>";
    		echo "</tr>";
    	echo "</table>";
    	
    	echo "<table style='width:100%'>";	
    		echo "<tr>";
    			echo "<td>".  $s2['phone'] . "</td>";
    		echo "</tr>";
    	echo "</table>";
    	
    	echo "<table style='width:100%'>";	
    		echo "<tr>";
    			echo "<td>".  $s2['address'] . "</td>";
    		echo "</tr>";
    	echo "</table>";
    	echo "<br>";
    }
}}}
	?>
	</td>
  </tr>
</table>
<?php
				
				
						}
?>
</div>
</div>   		
<?php
	
            }
        }}

			 
    			
  
	
					  if ($countPups > 0) {
//Move to next or previous result
?>
<a class="prev" onclick="plusSlides(-1)">&#10094;</a>
<a class="next" onclick="plusSlides(1)">&#10095;</a>
		
	  <?php 	
    }   
	} }
	
?>
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
    




//if no puppies from database match user selected attributes:
if (empty($values)) {
    echo "<br/>" . "Sorry, no puppies found in our database that match your request.";
}



?>     

</body>

</html>
