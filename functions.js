let current_weather_summary;
let days_forecast;
let chosen_location;

const today = new Date();
const tomorrow = new Date(today);
const week = new Date(today);
tomorrow.setDate(tomorrow.getDate() + 1);
week.setDate(tomorrow.getDate() + 5);
let dateinfoinputmin = tomorrow.toJSON().substring(0,10);
let dateinfoinputmax = week.toJSON().substring(0,10);
document.getElementById("date").value = dateinfoinputmin;
document.getElementById("date").min = dateinfoinputmin;
document.getElementById("date").max = dateinfoinputmax;

let geolocation;
let x = document.getElementById("coordinates");

let image = document.createElement('img');
image.setAttribute('src', "./weather/1.png");
let box = document.getElementById('weather_image');
box.appendChild(image);

const inital_location = "Stockholm";
// collect_weather_information(inital_location, flag="name");

const nameloc = document.getElementById("sumbitnameloc");
nameloc.addEventListener("click", validateForm);

const geoloc = document.getElementById("Btngeoloc");
geoloc.addEventListener("click", getLocation);

const forecast_call = document.getElementById("date");
forecast_call.addEventListener("click", week_forecast);

const weather_now_call = document.getElementById("current_weather");
weather_now_call.addEventListener("click", current_weather);

function validateForm(){
  let x = document.getElementById("location").value;
  console.log(x);
  var errorflag = document.getElementById("locationerror");
  if (x == "") {
    errorflag.innerHTML = "Name is required";
  }
  else{
    collect_weather_information(x, flag="name");
    /*
    if (!"/^[a-zA-Z' åäöÅÄÖ]*$/".test(x)){
      errorflag.innerHTML = "Not valid name";
      return;
    }
    else{
      
      windows.alert("yes");
      
    }
    */

  }
}

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
 collect_weather_information([position.coords.latitude, position.coords.longitude], flag="coords");   
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
  document.getElementById("chosen_date").innerHTML = "";
  document.getElementById("day_weather_type").innerHTML = "";
  document.getElementById("temperature_mean").innerHTML = "";
  document.getElementById("temperature_maxmin").innerHTML = "";
  document.getElementById("day_Wind_speed_and_direction").innerHTML = "";
  document.getElementById("percipitation").innerHTML = "";
  
  try {
    document.getElementById("chosen_location").innerHTML = "Weather in " + chosen_location + "\n";
    document.getElementById("current_weather_type").innerHTML = current_weather_summary[0] + "\n";
    document.getElementById("current_temperature").innerHTML = "Temperature: " + current_weather_summary[2] + " deg C\n";
    document.getElementById("current_Wind_speed_and_direction").innerHTML = "Wind speed and direction: " + current_weather_summary[3] + " " + current_weather_summary[4] + "\n";
    // document.getElementById("display_date").innerHTML = active_date;
    document.getElementById("local_time_today").innerHTML = "Local time(now): " + current_weather_summary[5] + "\n"; 
    var image_path = "./weather/" + current_weather_summary[1] + ".png";
  }
  catch{
    document.getElementById("chosen_location").innerHTML = "Weather in " + chosen_location + " is not available \n";
    document.getElementById("weather_type").innerHTML = "";
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
    document.getElementById("chosen_location").innerHTML = "Weather in " + chosen_location + "\n";
    var active_date = document.getElementById("date").value;
    document.getElementById("chosen_date").innerHTML = active_date + "\n"; 
    document.getElementById("day_weather_type").innerHTML = days_forecast[active_date][1] + "\n";
    document.getElementById("temperature_mean").innerHTML = "Overall temperature: " + days_forecast[active_date][3] + " C \n";
    document.getElementById("temperature_maxmin").innerHTML = "Max/min temperature: " + days_forecast[active_date][4] + "/"+ days_forecast[active_date][5] + " °C \n"; 
    document.getElementById("day_Wind_speed_and_direction").innerHTML = "Wind speed and direction: " + days_forecast[active_date][6] + " " + days_forecast[active_date][7] + "\n";
    document.getElementById("percipitation").innerHTML = "Total percipitation: " + days_forecast[active_date][8] + " mm \n";
    // document.getElementById("display_date").innerHTML = active_date; 
    var image_path = "./weather/" + days_forecast[active_date][2] + ".png";
  }
  catch{
    document.getElementById("chosen_location").innerHTML = "No forecast is avaible for " + chosen_location + " . Please choose another date. \n";
    document.getElementById("day_weather_type").innerHTML = "";
    document.getElementById("chosen_date").innerHTML = "";
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

function collect_weather_information(locationinformation, flag){
  const xhttp = new XMLHttpRequest();
  if (flag == "name"){
    xhttp.open("GET", "forecastcall.php?name="+locationinformation, true);
    //window.alert(locationinformation)
  }
  else{
    latitude = locationinformation[0];
    longitude = locationinformation[1];
    xhttp.open("GET", "forecastcall.php?lat=" + latitude + "&long=" + longitude, true);
  }
  
  xhttp.onload = function(){
    chosen_location = JSON.parse(this.responseText)[0];
    current_weather_summary = JSON.parse(this.responseText)[1];
    days_forecast = JSON.parse(this.responseText)[2];
    //window.alert(current_weather_summary);
    current_weather();
  }

  xhttp.send();
  
  return;
}
