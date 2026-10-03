<template>
    <article :id="marker.id">
        <div class="d-flex flex-column">
            <span>Drag marker to point a place</span>
            <GmapMap
                :center="{lat: marker.lat, lng: marker.lng}"
                :zoom="4"
                map-type-id="terrain"
                style="width: 100%; height: 300px">
                <GmapMarker
                    :key="marker.id"
                    :position="{lat: marker.lat, lng: marker.lng}"
                    :draggable="true"
                    @dragend="updateCoordinates"
                />
            </GmapMap>

            <span>..or</span>

            <label for="latitude">Latitude</label>
            <input id="latitude" type="number" class="form-control" v-model="marker.lat"/>

            <label for="longitude">Longitude</label>
            <input id="longitude" type="number" class="form-control mb-3" v-model="marker.lng"/>

            <label for="markerTitle">Title</label>
            <input id="markerTitle"
                   v-model="marker.title"
                   type="text"
                   class="form-control">

            <input id="markerPhoto"
                   type="file"
                   class="form-control-file my-3">

            <label for="markerDescription">Description</label>
            <textarea id="markerDescription"
                      v-model="marker.description"
                      cols="30" rows="5"
                      class="form-control">
            </textarea>

            <div class="ml-auto mt-3">
                <button class="btn btn-primary" @click="createMarker">Create</button>
                <button class="btn btn-danger" @click="$router.go(-1)">Cancel</button>
            </div>
        </div>
    </article>
</template>

<script>
    import Vue from 'vue';
    import Default from "./Default";

    export default {
        name: "Create",
        props: {
          owner: Number
        },
        components: {
          Default
        },
        methods: {
            updateCoordinates: function (location) {
                let lat = Number(location.latLng.lat())
                let lng = Number(location.latLng.lng())

                this.marker.lat = parseFloat(lat.toFixed(4));
                this.marker.lng = parseFloat(lng.toFixed(4));
            },
            createMarker: function (e) {
                let xhr = new XMLHttpRequest();
                let popup = document.getElementById('popup');
                let notify = document.getElementById('notify');
                let markerPhoto = document.getElementById('markerPhoto');
                let markerCounter = document.getElementById('markerCounter');

                // preparing data to transfer
                const data = {
                    owner_id: this.marker.owner_id,
                    lat: this.marker.lat,
                    lng: this.marker.lng,
                    title: this.marker.title,
                    description: this.marker.description,
                }

                xhr.open('POST', '/api/markers/', false);
                xhr.setRequestHeader("Accept", "application/json");
                xhr.setRequestHeader("Content-Type", "application/json");
                xhr.send(JSON.stringify(data));

                if (xhr.status !== 200) {
                    alert(`${xhr.status}: ${xhr.statusText}`);
                    console.log(xhr.response);
                } else {
                    //parsing response
                    const response = JSON.parse(xhr.response);

                    if (markerPhoto.files.length > 0) {
                        let xhr = new XMLHttpRequest();
                        let formData = new FormData();

                        formData.append('marker_id', response.id);
                        formData.append('owner_id', response.owner_id);
                        formData.append('content', markerPhoto.files[0]);

                        xhr.open('POST', '/api/photos', false);
                        xhr.send(formData);

                        if (xhr.status !== 200) {
                            alert(`${xhr.status}: ${xhr.statusText}`);
                            console.log(xhr.response);
                        }
                    }

                    this.$router.push('/home');

                    markerCounter.innerText = eval(`${markerCounter.innerText} + 1`);

                    // setting up popup
                    notify.innerText = response.message;
                    popup.className = 'show';

                    function removePopup() {
                        popup.className = 'hide'
                    }

                    // date reformatting
                    response.created_at = new Date(response.created_at).toJSON();
                    response.created_at = response.created_at.substring(0, response.created_at.length - 5);
                    response.created_at = response.created_at.replace(/T/g, " ");

                    // rendering Default component
                    // new Vue({
                    //     el: '#markerStorage',
                    //     template: `<Default :marker='${JSON.stringify(response)}' />`,
                    //     components: { Default }
                    // })

                    setTimeout(removePopup, 3000);
                }
            }
        },
        data: function () {
            return {
                marker: {
                    lat: 50.7593,
                    lng: 25.3424,
                    title: 'Hello world',
                    description: 'This is my new marker',
                    owner_id: this.owner
                }
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

    textarea {
        min-height: 60px;
        max-height: 300px;
    }
</style>
