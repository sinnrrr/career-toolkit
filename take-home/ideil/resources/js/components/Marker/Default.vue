<template>
    <article :id="marker.id">
        <section v-if="!editMode" class="wrapper">
            <div>
                <small>Latitude: {{ marker.lat }}</small>
                <br>
                <small>Longitude: {{ marker.lng }}</small>
            </div>
            <div class="button-group">
                <a :href="'/place/' + marker.id" class="btn btn-info">Read</a>
                <button @click="editMode = true" class="btn btn-warning">Update</button>
                <button @click="deleteMarker" class="btn btn-danger">Delete</button>
            </div>
            <div>
                <span>Created at {{ marker.created_at }}</span>
            </div>
        </section>
        <div v-if="!editMode">
            <h4><a :href="'/place/' + marker.id">{{ marker.title }}</a></h4>
            <p>{{ marker.description }}</p>
        </div>
        <div v-else class="d-flex flex-column">

            <label for="markerTitle">Title</label>
            <input id="markerTitle"
                   v-model="marker.title"
                   type="text"
                   class="form-control">

            <label for="markerDescription">Description</label>
            <textarea id="markerDescription"
                      v-model="marker.description"
                      cols="30" rows="5"
                      class="form-control">
            </textarea>

            <div class="ml-auto mt-3">
                <button class="btn btn-primary" @click="updateMarker">Update</button>
                <button class="btn btn-danger" @click="editMode = false">Cancel</button>
            </div>
        </div>
    </article>
</template>

<script>
    export default {
        name: "Default",
        props: {
            marker: Object,
            required: true
        },
        methods: {
            deleteMarker: function (e) {
                let xhr = new XMLHttpRequest();
                let markerBlock = document.getElementById(this.marker.id);

                xhr.open('DELETE', `/api/markers/${this.marker.id}`, false);
                xhr.send();

                if (xhr.status !== 200) {
                    alert(`${xhr.status}: ${xhr.statusText}`);
                } else {
                    let popup = document.getElementById('popup');
                    let notify = document.getElementById('notify');
                    let markerCounter = document.getElementById('markerCounter');

                    //parsing response
                    const response = JSON.parse(xhr.response);

                    // removing marker
                    markerBlock.remove();

                    // decreasing comment counter
                    markerCounter.innerText = eval(`${markerCounter.innerText} - 1`);

                    // setting up popup
                    notify.innerText = response.message;
                    popup.className = 'show';

                    function removePopup() {
                        popup.className = 'hide'
                    }

                    setTimeout(removePopup, 3000);
                }
            },
            updateMarker: function (e) {
                let xhr = new XMLHttpRequest();

                // preparing data to transfer
                const data = {
                    owner_id: this.marker.owner_id,
                    lat: this.marker.lat,
                    lng: this.marker.lng,
                    title: this.marker.title,
                    description: this.marker.description,
                }

                xhr.open('PUT', `/api/markers/${this.marker.id}`, false);
                xhr.setRequestHeader("Content-Type", "application/json");
                xhr.setRequestHeader("Accept", "application/json");
                xhr.send(JSON.stringify(data));

                if (xhr.status !== 200) {
                    alert(`${xhr.status}: ${xhr.statusText}`);
                    console.log(xhr.response);
                } else {
                    let popup = document.getElementById('popup');
                    let notify = document.getElementById('notify');

                    //parsing response
                    const response = JSON.parse(xhr.response);

                    this.editMode = false;

                    // setting up popup
                    notify.innerText = response.message;
                    popup.className = 'show';

                    function removePopup() {
                        popup.className = 'hide'
                    }

                    setTimeout(removePopup, 3000);
                }
            }

        },
        data: function () {
            return {
                editMode: false
            }
        }
    }
</script>

<style scoped>
    article {
        border: 1px solid gray;
        border-radius: 5px;
        padding: 30px;
        overflow-wrap: break-word;
        margin-bottom: 15px;
        min-height: 170px;
    }

    .wrapper {
        float: right;
        border-left: 1px solid black;
        margin-left: 30px;
    }

    .wrapper > div {
        padding: 0 15px;
        text-align: right;
    }

    .button-group {
        float: right;
    }

    .button-group > button {
        margin: 0 5px;
    }

    .button-group > button:last-child {
        margin: 0;
    }

    textarea {
        min-height: 60px;
        max-height: 300px;
    }
</style>
