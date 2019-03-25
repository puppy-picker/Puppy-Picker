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
    $query_str = substr($query_str, 0, -5);
    
    if (isset($_GET['hair']) || isset($_GET['activity']) || isset($_GET['size'])) {
        $sql  = "SELECT breed FROM Puppies" . $query_str;
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $result     = $stmt->fetchAll(PDO::FETCH_OBJ);
        $stmt       = null;
        $pickedPups = json_decode(json_encode($result), true);
        
        $countPups = count($pickedPups);
?>
        There are <?php echo $countPups; ?> matching breeds:
<?php
        //Display chosen puppies
        foreach ($pickedPups as $key => $breed) {
            
            
            foreach ($breed as $attribute => $values) {
                
                if ($countPups > 0) {
                    
                    $replacepickedPups = array_shift($pickedPups);
                    $q                 = implode(', ', $replacepickedPups);
                    
                    $key = "AIzaSyAKTRJZ39cWh0Ia3zJ7Eeqg513JYfbGiMw";
                    
                    // generate YouTube API URL
                    $feedURL   = file_get_contents("https://content.googleapis.com/youtube/v3/search?maxResults=1&part=snippet&q=" . urlencode($q) . "%20facts&type=video&key=" . urldecode($key));
                    $strDecode = json_decode($feedURL, true);
                    
                    echo '<script>';
                    echo 'console.log(' . json_encode($strDecode) . ')';
                    echo '</script>';
                    
                    //video id:
                    $videoID = $strDecode["items"]["0"]["id"]["videoId"];
                    
                    //video title:
                    $videoTitle = $strDecode["items"]["0"]["snippet"]["title"];
                    
                    
                    //check if vidId already in database
                    $selectVidID = "SELECT vidID FROM vidPups WHERE vidID = '$videoID' LIMIT 1;";
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
                    $selectVidID = "SELECT vidID FROM vidPups WHERE vidID = '$videoID' LIMIT 1;";
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
  <iframe src="https://www.youtube.com/embed/<?php echo $vidIDResultStringTrim2; ?> " allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"  style="width: 800px; height: 600px; " allowfullscreen></iframe>
                    <div class="text"><?php echo $values; ?></div>
</div>

</div>   
        <?php
                    
                }
            }
        }
    }
?>
<a class="prev" onclick="plusSlides(-1)">&#10094;</a>
<a class="next" onclick="plusSlides(1)">&#10095;</a>

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
    echo "<br/>" . "Sorry, no puppies found in our database.";
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
