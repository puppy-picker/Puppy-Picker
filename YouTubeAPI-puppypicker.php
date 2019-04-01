	
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
				Puppy Picker 
</header>


<form action="puppysearch.php"  method="GET">


<h2>Size</h2>
   Small: <input type="checkbox" name="size[]" value = "small"/><br>
   Medium: <input type="checkbox" name="size[]" value = "medium"/><br>
   Large: <input type="checkbox" name="size[]" value = "large"/>
    

	<h2>Activity</h2>
   Calm: <input type="checkbox" name="activity[]" value = "calm"/><br>
   Energetic: <input type="checkbox" name="activity[]" value = "energetic"/><br>
   Regular: <input type="checkbox" name="activity[]" value = "regular"/><br>
   Lots: <input type="checkbox" name="activity[]" value = "lots of activity"/>



<h2>Hair</h2>
   Short: <input type="checkbox" name="hair[]" value="short" /><br>
   Medium: <input type="checkbox" name="hair[]" value = "medium" /><br>
   Long: <input type="checkbox" name="hair[]" value = "long"/>

<h2>Zip Code</h2>
 <input type = "text" name="zip"/><br>

 <input type = "reset" value="Reset"/>
	
	<input type = "submit" name = "Submit" value="Submit"/>

 </form> 





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
