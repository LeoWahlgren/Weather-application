<?php
$key ="l9txvsz3c4wfq3jo3s8wodiebh5oa3v3xzi59pef"; // Fill in your own api key, create a free account at MeteoSource
// Default loaded weather info, make sure it's the right location-id in the api for the city.

// Html form search location
if (isset($_GET["name"])) {
  $name = test_input($_GET["name"]);
  $urlsearch = "https://www.meteosource.com/api/v1/free/find_places_prefix?text=".urlencode($name)."&language=en&key=".$key;
  $datasearch = curlresponse($urlsearch);
  if (count($datasearch) == 0){
    echo json_encode("");
  }
  else{
    $idcity = $datasearch[0]["place_id"];
    $weather_location = $datasearch[0]["name"];
    list($current_weather,$week_summary) = load_location_forecast($idcity);
    echo json_encode(array($weather_location, $current_weather,$week_summary));
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
  echo json_encode(array($weather_location, $current_weather,$week_summary));
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
  $local_time = date('H:i');
  $current_weather = array($current_weather_type, $current_image_num, $current_weather_temp, $current_wind_speed, $current_wind_direction, $local_time);

  // Fill week forecast
  $week_forecast = array();
  for ($i=1;$i < 7; $i++){
    $day_date = strval($data["daily"]["data"][$i]["day"]);
    $day_weather = strval(explode(".", strval($data["daily"]["data"][$i]["summary"]))[0]);
    $day_icon = $data["daily"]["data"][$i]["icon"];
    $day_meantemperature = strval($data["daily"]["data"][$i]["all_day"]["temperature"]);
    $day_maxtemperature = strval($data["daily"]["data"][$i]["all_day"]["temperature_max"]);
    $day_mintemperature = strval($data["daily"]["data"][$i]["all_day"]["temperature_min"]);
    $day_windspeed = strval($data["daily"]["data"][$i]["all_day"]["wind"]["speed"]);
    $day_winddirection = strval($data["daily"]["data"][$i]["all_day"]["wind"]["dir"]);
    $day_rain_amount = strval($data["daily"]["data"][$i]["all_day"]["precipitation"]["total"]);

    //$classname = "forecast".$day_date;
    //$$classname = new daily_forecast($day_date, $day_weather, $day_icon, $day_meantemperature, $day_maxtemperature, $day_mintemperature, $day_windspeed, $day_winddirection, $day_rain_amount); 
    //var_dump($day_meantemperature);
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


?>

<?php
/*
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