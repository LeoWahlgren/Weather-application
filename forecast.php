<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php
$todays_date = date('Y-m-d');
$weeksdate = date('Y-m-d', strtotime($todays_date." + 7 day"));
$name = $nameErr = "";
$weather_location = $weather_type = $temperature_mean = $local_time = $weather_date = $percipitation = "";
$image_num = 1;
$days_summary =  load_weather_forecast("Stockholm");
$weather_location = "Stockholm";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["name"])){
      $nameErr = "Name is required";
    }
    else{
      $name = test_input($_POST["name"]);
      if(!preg_match("/^[a-zA-Z' åäöÅÄÖ]*$/", $name)){
        $nameErr = "Not valid name"; 
      }else{$nameErr="";
        $urlsearch = "https://www.meteosource.com/api/v1/free/find_places_prefix?text=".urlencode($name)."&language=en&key=l9txvsz3c4wfq3jo3s8wodiebh5oa3v3xzi59pef";
        $datasearch = curlresponse($urlsearch);
        if (count($datasearch) == 0){
          $nameErr = "Not valid name";}
        else{
          $idcity = $datasearch[0]["place_id"];
          $weather_location = $datasearch[0]["name"];
          $days_summary = load_weather_forecast($idcity);
        } 
      }        
}   }


if (isset($_GET['lat']) and isset($_GET['long'])){
  $currlatitude = strval($_GET['lat']);
  $currlongitude = strval($_GET['long']);
  $urlsearch = "https://www.meteosource.com/api/v1/free/nearest_place?lat=".$currlatitude."&lon=".$currlongitude."&key=l9txvsz3c4wfq3jo3s8wodiebh5oa3v3xzi59pef";
  $datasearch = curlresponse($urlsearch);
  $idcity = $datasearch["place_id"];
  $weather_location = $datasearch["name"];
  $days_summary = load_weather_forecast($idcity);
}

function load_weather_forecast($id){
  $urlweather = "https://www.meteosource.com/api/v1/free/point?place_id=".$id."&sections=daily&timezone=auto&language=en&units=metric&key=l9txvsz3c4wfq3jo3s8wodiebh5oa3v3xzi59pef";
  $data = curlresponse($urlweather);
  //var_dump($data["daily"]["data"][0]);
  $days = array();
  date_default_timezone_set($data["timezone"]);
  for ($i=0;$i < 6; $i++){
    $day_date = strval($data["daily"]["data"][$i]["day"]);
    $day_weather = strval(explode(".", strval($data["daily"]["data"][$i]["summary"]))[0]);
    $day_icon = $data["daily"]["data"][$i]["icon"];
    $day_meantemperature= strval($data["daily"]["data"][$i]["all_day"]["temperature"]);
    $day_maxtemperature = strval($data["daily"]["data"][$i]["all_day"]["temperature_max"]);
    $day_mintemperature = strval($data["daily"]["data"][$i]["all_day"]["temperature_min"]);
    $day_windspeed = strval($data["daily"]["data"][$i]["all_day"]["wind"]["speed"]);
    $day_winddirection = strval($data["daily"]["data"][$i]["all_day"]["wind"]["dir"]);
    $day_rain_amount = strval($data["daily"]["data"][$i]["all_day"]["precipitation"]["total"]);

    //$classname = "forecast".$day_date;
    //$$classname = new daily_forecast($day_date, $day_weather, $day_icon, $day_meantemperature, $day_maxtemperature, $day_mintemperature, $day_windspeed, $day_winddirection, $day_rain_amount); 
    //var_dump($$classname);
    $days[$day_date] = array($day_date, $day_weather, $day_icon, $day_meantemperature, $day_maxtemperature, $day_mintemperature, $day_windspeed, $day_winddirection, $day_rain_amount);

  }
  return $days;
}

function curlresponse($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);
    return $data;
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
$local_time = date('H:i');
?>

<form method="post" action=" <?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" >
<input id="location" type="text" placeholder="Search for location" name="name" value ="<?php echo $name;?>">
<span class="error">* <?php echo $nameErr;?></span>
<input type="submit" name="submit" value="Search location">
</form>

<ol>
<li class="search_suggested_item">
<a id = "Btngeoloc" href="javascript:void(0);">
 <img src = "./icon-geolocation.png" x="0" y="0" width="16" height="16" viewBox="0 0 24 24" focusable="false" aria-hidden="true"></img>
<span>Close by</span>
</a>
 </li>
 <li class="search_suggested_item">
 </li>
</ol>

<div>
<label for="day">Choose day:</label>
<form>
<input type="date" id="date"  name="date"
       value= "<?php echo $todays_date; ?>"
       min="<?php echo $todays_date; ?>" max="<?php echo $weeksdate; ?>">
</form>
</div>


<p id="coordinates"></p>
<p>Weather in: <?=$weather_location?> <br> </p>
<p id ="display_date"><br> </p>
<p id ="weather_type"><br> </p>
<p id ="temperature_mean"> <br> </p>
<p id ="temperature_maxmin"> <br> </p>
<p id ="Wind_speed_and_direction"> <br> </p>
<p id ="percipitation"> <br> </p>
<p id ="local_time_today"> <br> </p>
<p id="weather_image"><br></p>
<p">Powered by MeteoSource API</p>

<script>
var geolocation;
const box = document.getElementById('weather_image');
const image = document.createElement('img');
box.appendChild(image);
// var num = "<?php echo $image_num; ?>";
// console.log(num);


var x = document.getElementById("coordinates");
display_forecast();

var geoloc = document.getElementById("Btngeoloc");
geoloc.addEventListener("click", getLocation);

var forecast_call = document.getElementById("date")
forecast_call.addEventListener("mouseover", display_forecast);
forecast_call.addEventListener("hashchange", display_forecast);

function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(getPosition, showError);
  } else {
    x.innerHTML = "Geolocation is not supported by this browser.";
  }
  
}

function getPosition(position) {
 //x.innerHTML = "Latitude: " + position.coords.latitude +
 // "<br>Longitude: " + position.coords.longitude;
 var curloc = window.location.href;
  window.location=curloc+'?lat='+position.coords.latitude+'&long='+position.coords.longitude; //get, hur ändrar man till post?
  
    
}
function showError(error) {
  switch(error.code) {
    case error.PERMISSION_DENIED:
      x.innerHTML = "User denied the request for Geolocation. Check if you blocked this site for accessing your location."
      break;
    case error.POSITION_UNAVAILABLE:
      x.innerHTML = "Location information is unavailable."
      break;
    case error.TIMEOUT:
      x.innerHTML = "The request to get user location timed out."
      break;
    case error.UNKNOWN_ERROR:
      x.innerHTML = "An unknown error occurred."
      break;
  }
}


function display_forecast() {
  try {
    var days_forecast = <?php echo json_encode($days_summary); ?>; 
    var active_date = document.getElementById("date").value;
    document.getElementById("weather_type").innerHTML = days_forecast[active_date][1];
    document.getElementById("temperature_mean").innerHTML = "Overall temperature: " + days_forecast[active_date][3] + " °C";
    document.getElementById("temperature_maxmin").innerHTML = "Max/min temperature: " + days_forecast[active_date][4] + "/"+ days_forecast[active_date][5] + " °C"; 
    document.getElementById("Wind_speed_and_direction").innerHTML = "Wind speed and direction: " + days_forecast[active_date][6] + " " + days_forecast[active_date][7];
    document.getElementById("percipitation").innerHTML = days_forecast[active_date][8] + " mm";
    document.getElementById("display_date").innerHTML = active_date;
    var image_path = "./weather/" + days_forecast[active_date][2] + ".png";
    //if (isToday(active_date)){
    //  document.getElementById("local_time_today").innerHTML = "Local time(now): " + "<?php echo $local_time; ?>";
    //  }
  }
  catch{
    document.getElementById("weather_type").innerHTML = "No data available";
    var image_path = "./weather/1.png"; 
  }
  finally{
    image.setAttribute('src', image_path);
  } 
}
/*
function isToday(someDate){
  const today = new Date()
  return someDate.getDate() == today.getDate() &&
    someDate.getMonth() == today.getMonth() &&
    someDate.getFullYear() == today.getFullYear()
}

*/


</script>
</body>
</html>
