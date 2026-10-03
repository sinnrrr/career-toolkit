<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/place.css') }}">
</head>
<body>
<div class="container">
    <div id="popup">
        <p id="notify"></p>
    </div>
    <div class="w-100 py-3">
        <a href="/"><- Go back</a>
    </div>
    <div class="d-flex flex-column float-right">
        <section class="d-flex flex-column bg-secondary text-white p-3 rounded text-right">
            <div>
                <span>Created by: <b>{{ $userData->name }}</b></span><br>
                <span>at {{ $markerData->created_at }}</span>
                <a class="d-block mt-2 text-white" href="mailto:{{ $userData->email }}">{{ $userData->email }}</a>
            </div>

            @if($markerData->created_at != $markerData->updated_at)
                <br>
                <div>
                    <span>Last update: {{ $markerData->updated_at }}</span>
                </div>
            @endif
        </section>
        <div class="float-right text-right mt-2">
            <small>Latitude: {{ $markerData->lat }}</small><br>
            <small>Longitude: {{ $markerData->lng }}</small>
        </div>
    </div>
    <div>
        <h1><b>{{ $markerData->title }}</b></h1>
        <p>{{ $markerData->description }}</p>
        @if(!empty($photoData))
            @foreach($photoData as $photo)
                <img src="{{ asset("storage/{$photo->content}") }}" alt="">
            @endforeach
        @endif
    </div>
    <section class="my-5">
        <h3>Comments: <span id="commentCounter">{{ $commentData->total() }}</span></h3>
        <hr>
        @if($checkAuth)
            <div class="px-5">
                <span>Only registered users can leave a comments!</span>
            </div>
        @endif
        <div class="input-group mb-3 px-5">
            <input type="text"
                   id="commentInput"
                   class="form-control rounded-left"
                   placeholder="Place a comment"
                   aria-describedby="basic-addon2"
                {{ $checkAuth ? 'disabled' : '' }}>
            <div class="input-group-append">
                <button
                    id="submitButton"
                    class="btn btn-outline-secondary {{ $checkAuth ? 'disabled' : '' }}"
                    type="button">Post
                </button>
            </div>
        </div>
        <div id="articles" class="px-5">
            @foreach($commentData as $comment)
                <article>
                    <h4>{{ $commentUserData[$comment->owner_id] }}</h4>
                    <p>{{ $comment->body }}</p>
                    <div><span>Created at {{ $comment->created_at }}</span></div>
                </article>
            @endforeach
        </div>
        <div class="pagination">{{ $commentData->links() }}</div>
    </section>
</div>
<script>
    function removePopup() {
        popup.className = 'remove';
    }

    function displayPopup(message) {
        // setting notify text
        notify.innerText = message;

        // showing popup
        popup.className = 'show';

        setTimeout(removePopup, 3000);
    }

    const submitButton = document.getElementById('submitButton');
    const notify = document.getElementById('notify');
    const marker_id = {{ $markerData->id }};
    const owner_id = {{ $authID }};

    let articles = document.getElementById('articles');

    submitButton.addEventListener('click', () => {
        let commentField = document.getElementById('commentInput');
        let commentCounter = document.getElementById('commentCounter');
        let body = commentField.value;

        const popup = document.getElementById('popup');

        // preparing data
        let dataForm = {marker_id, owner_id, body};
        let xhr = new XMLHttpRequest();

        // doing AJAX request to API endpoint
        xhr.open('POST', '/api/comments', false);
        xhr.setRequestHeader("Content-type", "application/json");
        xhr.setRequestHeader("Accept", "application/json");
        xhr.send(JSON.stringify(dataForm));

        let response = JSON.parse(xhr.response);

        // checking if got any errors
        if (xhr.status !== 200) {
            alert(`${xhr.status}: ${xhr.statusText}`);
            displayPopup(response.message);
        } else {
            console.log(xhr.response)

            const userName = "{{ $authName }}";

            // fetching response data
            let response = JSON.parse(xhr.response);

            // date reformatting
            response.created_at = new Date(response.created_at).toJSON();
            response.created_at = response.created_at.substring(0, response.created_at.length - 5);
            response.created_at = response.created_at.replace(/T/g, " ");

            // html block
            const commentTitle = `<h4>${userName}</h4>`;
            const commentDescription = `<p>${response.body}</p>`;
            const commentDate = `<div><span>Created at ${response.created_at}</span></div>`;

            // adding new comment
            articles.innerHTML =
                '<article>' + commentTitle + commentDescription + commentDate + '</article>' + articles.innerHTML;

            // increasing comment counter
            commentCounter.innerText = eval(`${commentCounter.innerText} + 1`);

            // erasing comment field
            commentField.value = '';

            displayPopup(response.message);
        }
    });
</script>
</body>
</html>
