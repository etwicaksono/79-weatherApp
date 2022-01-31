<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>WeatherApp</title>

  @if (isset($csrf))
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  @endif

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
    integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .bg-main {
      background-color: #baaeae;
    }
  </style>

  @stack('css')

</head>

<body>
  <nav class="navbar  sticky-top navbar-expand-lg navbar-light bg-main mb-3 px-5">
    <a class="navbar-brand font-weight-bolder text-white" href="{{ url("") }}">WeatherApp</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        {{-- <li class="nav-item active">
          <a class="nav-link" href="{{ url('my-pokemon') }}">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Link</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            Dropdown
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="#">Action</a>
            <a class="dropdown-item" href="#">Another action</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">Something else here</a>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
        </li> --}}
      </ul>
      <form class="form-inline my-2 my-lg-0">
        <input id="pac-input" class="controls form-control w-100" type="text" placeholder="Search City" />
      </form>
    </div>
  </nav>

  <div class="container px-5 mt-lg-5">
    <div class="row">
      <div class="col">
        <div class="card bg-secondary">
          <div class="card-header bg-main">
            <div class="row">
              <div class="col">
                <p class="text-white m-0 p-0" style="font-size: 2rem"><span class="font-weight-bold location">-</span>
                  As of <span class="time">-</span></p>
              </div>
            </div>
          </div>
          <div class="card-body">
            <span class="text-white font-weight-bold" style="font-size: 3rem"><span
                id="temperature">-</span>&deg;C</span>
            <img src="" alt="weather" class="float-right" style="height: 8rem" id="weather-icon">
            <h6 class="card-subtitle mb-2 text-white" id="condition">-</h6>
            <p class="text-white"><i class="fas fa-wind"></i> <span id="wind">-</span></p>
            <p class="text-white"><i class="fas fa-water"></i> <span id="humidity">-</span></p>
            <p class="text-white"><i class="fas fa-cloud-rain"></i> <span id="rain">-</span></p>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col">
        <div id="map" style="width:100%; height:50vh"></div>
      </div>
    </div>
    <p class="mt-5"><span class="h1">3 Days Weather</span> - <span class="location">-</span></p>
    <p>as of <span class="time">-</span></p>
    <div class="row">
      <div class="col">
        <table class="table" id="forecast-wrapper"></table>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js">
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous">
  </script>
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://kit.fontawesome.com/0f853fcb5c.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key={{ getenv('GOOGLE_API_KEY') }}&libraries=places&v=weekly"
    async></script>

  <script>
    $(function(){
            let baseurl = "{{ url('') }}/"
            let weather_key = "{{ getenv('WEATHER_API_KEY') }}"
            let map,marker,oldMarker

            
            mapsInit()       

            function getLocation(latitude="",longitude="",placeName=""){
              navigator.geolocation.getCurrentPosition(
              (position) => {
                let lati= (latitude!=""?latitude:position.coords.latitude)
                let longi= (longitude!=""?longitude:position.coords.longitude)
                // console.log("lati = "+lati);
                // console.log("longi = "+longi);

                $.ajax({
                  url:"http://api.weatherapi.com/v1/forecast.json",
                  method:"get",
                  dataType:"json",
                  data:{
                    key:weather_key,
                    q:lati+","+longi,
                    days:3,
                    hour:new Date().getHours()
                  },error:function(err){console.log(err);},
                  success:function(res){
                    console.log(res);
                    let location = $(".location")
                    let time = $(".time")
                    let temperature = $("#temperature")
                    let icon = $("#weather-icon")
                    let condition = $("#condition")
                    let wind = $("#wind")
                    let humidity = $("#humidity")
                    let rain = $("#rain")

                    if(placeName==""){
                      location.html(res.location.name+", "+res.location.region+", "+res.location.country)
                    }else{
                      location.html(placeName)
                    }
                    time.html(res.location.localtime.split(" ")[1])
                    temperature.html(res.current.temp_c)
                    icon.attr("src",res.current.condition.icon)
                    condition.html(res.current.condition.text)
                    wind.html(res.current.wind_kph + "kph, " + res.current.wind_dir)
                    humidity.html(res.current.humidity + "%")
                    rain.html(res.forecast.forecastday[0].hour[0].chance_of_rain + "%")

                    let output = ``
                    $.each(res.forecast.forecastday,function(key,value){
                      let date = new Date(value.date).toLocaleDateString("id-ID",{weekday:"long",year:"numeric",month:"long",day:"numeric"})
                      output += `
                      <tr>
                        <td class="align-middle">`+date+`</td>
                        <td class="align-middle"><img src="`+value.hour[0].condition.icon+`" alt="weather"><span>`+value.hour[0].condition.text+`</span></td>
                        <td class="align-middle">`+value.hour[0].temp_c+`&deg;C</td>
                        <td class="align-middle"><i class="fas fa-wind text-info"></i> ` + value.hour[0].wind_kph + `kph, `+ value.hour[0].wind_dir  +`</td>
                        <td class="align-middle"><i class="fas fa-water text-info"></i> ` + value.hour[0].humidity + `%</td>
                        <td class="align-middle"><i class="fas fa-cloud-rain text-info"></i> ` + value.hour[0].chance_of_rain + `%</td>
                      </tr>
                      `
                    })
                    $("#forecast-wrapper").html(output)
                  }
                })
              }
            );
            } 

            function mapsInit() {
              navigator.geolocation.getCurrentPosition(
              (position) => {
                let lati= position.coords.latitude
                let longi= position.coords.longitude
                let center = new google.maps.LatLng(lati, longi);
                let petaoption = {
                    zoom: 16,
                    center: center,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                };
                map = new google.maps.Map(document.getElementById("map"), petaoption);
                addMarker(lati,longi)
                google.maps.event.addListener(map, 'click', function(event) {
                    addMarker(event.latLng.lat(),event.latLng.lng());
                });
              })  
            initAutocomplete()              
            }

            function addMarker(lati,longi, placeName ="") {
              if(oldMarker){
                oldMarker.setMap(null)
              }

              let icon = {
                  url:  baseurl + "assets/maps-marker.png", // url
                  scaledSize: new google.maps.Size(50, 50), // scaled size
                  origin: new google.maps.Point(0, 0), // origin
                  anchor: new google.maps.Point(30, 60), // anchor
              };

              marker = new google.maps.Marker({
                  position: new google.maps.LatLng(lati, longi),
                  map: map,
                  icon: icon,
                  draggable:true
              });

              oldMarker = marker
              getLocation(lati,longi,placeName)
            }

            function initAutocomplete() {
              // Create the search box and link it to the UI element.
              const input = document.getElementById("pac-input");
              const searchBox = new google.maps.places.SearchBox(input);

              let markers = [];

              // Listen for the event fired when the user selects a prediction and retrieve
              // more details for that place.
              searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                  return;
                }

                // Clear out the old markers.
                markers.forEach((marker) => {
                  marker.setMap(null);
                });
                markers = [];

                // For each place, get the icon, name and location.
                const bounds = new google.maps.LatLngBounds();

                places.forEach((place) => {
                  if (!place.geometry || !place.geometry.location) {
                    console.log("Returned place contains no geometry");
                    return;
                  }

                  addMarker(place.geometry.location.lat(),place.geometry.location.lng(),place.formatted_address)
                  if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                  } else {
                    bounds.extend(place.geometry.location);
                  }
                });
                map.fitBounds(bounds);
              });
            }

        })
  </script>

</body>

</html>