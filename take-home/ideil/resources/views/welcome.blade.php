<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body>
<header>
    @if (Route::has('login'))
        <div class="top-right links">
            @auth
                <a href="{{ url('/home') }}">-> Home <-</a>
            @else
                <a href="{{ route('login') }}">Login</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Register</a>
                @endif
            @endauth
        </div>
        @endif
</header>
<div id="map"></div>
<div class="spacer"></div>
<script>
    let markers;
    let response;
    let xhr = new XMLHttpRequest();

    xhr.open('GET', '/api/markers', false);
    xhr.send();

    if (xhr.status !== 200) {
        alert(`${xhr.status}: ${xhr.statusText}`);
    } else {
        response = JSON.parse(xhr.response);
        markers = response;
    }

    function initMap() {
        let map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 50.7593, lng: 25.3424},
            zoom: 3
        });

        console.log(response)

        markers.map(element => {
            let marker = new google.maps.Marker({
                position: { lat: element.lat, lng: element.lng },
                animation: google.maps.Animation.DROP,
                map: map
            });

            // html block
            const markerTitle = `<h1>${element.title}</h1>`;
            const markerDescription = `<p>${element.description}</p>`;
            const markerLink = `<a href="/place/${element.id}">See more</a>`;

            // popup info window
            let infoWindow = new google.maps.InfoWindow({
                content: markerTitle + markerDescription + markerLink
            });

            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
        });


        if (response.total > 10) {
            // html block
            let showMore = document.createElement('a');
            showMore.innerText = 'Show more'
            showMore.href = response.next_page_url
            showMore.style.cursor = 'pointer'
            showMore.style.borderRadius = '5px'
            showMore.style.backgroundColor = 'white'
            showMore.style.padding = '15px 30px'
            showMore.style.marginBottom = '15px'
            showMore.style.fontSize = '20px'
            showMore.style.fontWeight = '600'

            map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(showMore);
        }
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCtRwPhPwGJgjuQJhNXq__cjCo6oU_XQdM&callback=initMap"
        async defer></script>
</body>
</html>
