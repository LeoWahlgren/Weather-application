<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style.css">
<!-- <script type="text/javascript" src="functions.js"></script> -->
</head>
<body>

<?php
$key =""; // Fill in your own api key, create a free account at MeteoSource
$todays_date = date('Y-m-d');
$tomorrows_date = date('Y-m-d', strtotime($todays_date." + 1 day"));
$weeksdate = date('Y-m-d', strtotime($todays_date." + 6 day"));
$name = $nameErr = "";
$weather_location = $weather_type = $temperature_mean = $local_time = $weather_date = $percipitation = "";
$image_num = 1;
// Default loaded weather info, make sure it's the right location id in the api for the city.
$default_location = "Stockholm";
list($current_weather,$week_summary) =  load_location_forecast($default_location);
$weather_location = $default_location;

// Html form search location
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (empty($_POST["name"])){
    $nameErr = "Name is required";
  }
  else{
    $name = test_input($_POST["name"]);
    if(!preg_match("/^[a-zA-Z' åäöÅÄÖ]*$/", $name)){
      $nameErr = "Not valid name"; 
    }else{$nameErr="";
      $urlsearch = "https://www.meteosource.com/api/v1/free/find_places_prefix?text=".urlencode($name)."&language=en&key=".$key;
      $datasearch = curlresponse($urlsearch);
      if (count($datasearch) == 0){
        $nameErr = "Not valid name";}
      else{
        $idcity = $datasearch[0]["place_id"];
        $weather_location = $datasearch[0]["name"];
        list($current_weather,$week_summary) = load_location_forecast($idcity);
      } 
    }  
  }      
}   

// Geolocation weather api call
if (isset($_GET['lat']) and isset($_GET['long'])){
  $currlatitude = strval($_GET['lat']);
  $currlongitude = strval($_GET['long']);
  $urlsearch = "https://www.meteosource.com/api/v1/free/nearest_place?lat=".$currlatitude."&lon=".$currlongitude."&key=".$key;
  $datasearch = curlresponse($urlsearch);
  $idcity = $datasearch["place_id"];
  $weather_location = $datasearch["name"];
  list($current_weather,$week_summary) = load_location_forecast($idcity);
}

function load_location_forecast($id){
  global $key;
  $urlweather = "https://www.meteosource.com/api/v1/free/point?place_id=".$id."&sections=all&timezone=auto&language=en&units=metric&key=".$key;
  $data = curlresponse($urlweather);
  date_default_timezone_set($data["timezone"]);
  //var_dump($data["daily"]["data"][0]);

  // Current weather
  $current_weather_type = strval($data["current"]["summary"]);
  $current_image_num = $data["current"]["icon_num"];
  $current_weather_temp = strval($data["current"]["temperature"]);
  $current_wind_speed = strval($data["current"]["wind"]["speed"]);
  $current_wind_direction = strval($data["current"]["wind"]["dir"]);
  $current_weather = array($current_weather_type, $current_image_num, $current_weather_temp, $current_wind_speed, $current_wind_direction);

  // Fill week forecast
  $week_forecast = array();
  for ($i=1;$i < 7; $i++){
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
    $week_forecast[$day_date] = array($day_date, $day_weather, $day_icon, $day_meantemperature, $day_maxtemperature, $day_mintemperature, $day_windspeed, $day_winddirection, $day_rain_amount);

  }
  return array($current_weather, $week_forecast);
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
<!-- option for suggestions, will be added
 </li>
 <li class="search_suggested_item">
 </li>
-->
</ol>


<div>
<label for="day">Choose day:</label>
<form>
<input type="date" id="date"  name="date"
       value= "<?php echo $tomorrows_date; ?>"
       min="<?php echo $tomorrows_date; ?>" max="<?php echo $weeksdate; ?>">
</form>
</div>

<button id="current_weather">Weather now</button>

<p id="coordinates"></p>
<p>Weather in: <?=$weather_location?> <br> </p>
<p id ="current_weather_type"></p>
<p id ="current_temperature"></p>
<p id ="current_Wind_speed_and_direction"></p>
<p id ="local_time_today"><br></p>

<!-- <p id ="display_date"><br> </p> -->
<p id ="day_weather_type"></p>
<p id ="temperature_mean"></p>
<p id ="temperature_maxmin"></p>
<p id ="day_Wind_speed_and_direction"></p>
<p id ="percipitation"> <br> </p>
<p id="weather_image"><br></p>
<p">Powered by MeteoSource API</p>

<script>
var geolocation;
var x = document.getElementById("coordinates");

const box = document.getElementById('weather_image');
const image = document.createElement('img');
box.appendChild(image);

current_weather();

var geoloc = document.getElementById("Btngeoloc");
geoloc.addEventListener("click", getLocation);

var forecast_call = document.getElementById("date");
forecast_call.addEventListener("click", week_forecast);

var weather_now_call = document.getElementById("current_weather");
weather_now_call.addEventListener("click", current_weather);

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
function current_weather(){
  document.getElementById("day_weather_type").innerHTML = "";
  document.getElementById("temperature_mean").innerHTML = "";
  document.getElementById("temperature_maxmin").innerHTML = "";
  document.getElementById("day_Wind_speed_and_direction").innerHTML = "";
  document.getElementById("percipitation").innerHTML = "";
  
  try {
    var weather_summary = <?php echo json_encode($current_weather); ?>; 
    document.getElementById("current_weather_type").innerHTML = weather_summary[0] + "\n";
    document.getElementById("current_temperature").innerHTML = "Temperature: " + weather_summary[2] + " °C\n";
    document.getElementById("current_Wind_speed_and_direction").innerHTML = "Wind speed and direction: " + weather_summary[3] + " " + weather_summary[4] + "\n";
    document.getElementById("local_time_today").innerHTML = "Local time(now): " + "<?php echo $local_time; ?>" + "\n";
    // document.getElementById("display_date").innerHTML = active_date; 
    var image_path = "./weather/" + weather_summary[1] + ".png";
  }
  catch{
    document.getElementById("weather_type").innerHTML = "No data available" + "\n";
    document.getElementById("temperature_mean").innerHTML ="";
    document.getElementById("Wind_speed_and_direction").innerHTML = "";
    var image_path = "./weather/1.png"; 
  }
  finally{
    image.setAttribute('src', image_path);
  } 

}


function week_forecast() {
  document.getElementById("current_weather_type").innerHTML = "";
  document.getElementById("current_temperature").innerHTML = "";
  document.getElementById("current_Wind_speed_and_direction").innerHTML = "";
  document.getElementById("local_time_today").innerHTML = "";
  try {
    var days_forecast = <?php echo json_encode($week_summary); ?>; 
    var active_date = document.getElementById("date").value;
    document.getElementById("day_weather_type").innerHTML = days_forecast[active_date][1] + "\n";
    document.getElementById("temperature_mean").innerHTML = "Overall temperature: " + days_forecast[active_date][3] + " °C \n";
    document.getElementById("temperature_maxmin").innerHTML = "Max/min temperature: " + days_forecast[active_date][4] + "/"+ days_forecast[active_date][5] + " °C \n"; 
    document.getElementById("day_Wind_speed_and_direction").innerHTML = "Wind speed and direction: " + days_forecast[active_date][6] + " " + days_forecast[active_date][7] + "\n";
    document.getElementById("percipitation").innerHTML = "Total percipitation: " + days_forecast[active_date][8] + " mm \n";
    // document.getElementById("display_date").innerHTML = active_date; 
    var image_path = "./weather/" + days_forecast[active_date][2] + ".png";
  }
  catch{
    document.getElementById("day_weather_type").innerHTML = "No data available \n";
    document.getElementById("temperature_mean").innerHTML ="";
    document.getElementById("temperature_maxmin").innerHTML = "";
    document.getElementById("day_Wind_speed_and_direction").innerHTML = "";
    document.getElementById("percipitation").innerHTML = "";
    var image_path = "./weather/1.png"; 
  }
  finally{
    image.setAttribute('src', image_path);
  } 
}

</script>




<?php
/* Use classes instead of array for day forecast? How to send PHP class to javascript?
class daily_forecast {
    function __construct($date, $weather_summary, $icon, $temperature_mean, $temperature_max, $temperature_min, $wind_speed, $wind_direction, $rain_amount) {
        $this->date = $date;
        $this->weather_summary = $weather_summary;
        $this->icon = $icon;
        $this->temperature_mean = $temperature_mean;
        $this->temperature_max = $temperature_max;
        $this->temperature_min = $temperature_min;
        $this->wind_speed = $wind_speed;
        $this->wind_direction = $wind_direction;
        $this->rain_amount = $rain_amount;
    }

    function get_date(){
      return $this->date;
    }
    function get_quickinfo(){
      return [$this->weather_summary, $this->icon, $this->temperature_mean, $this->rain_amount];
    }
}
*/
?>
-->
</body>
</html>
