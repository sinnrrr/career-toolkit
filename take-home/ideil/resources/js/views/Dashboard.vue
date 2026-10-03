<template>
    <section class="d-flex flex-column">
        <header>
            <div class="float-left">
                <h1>Hello, {{ this.user.name }}</h1>
                <p>Here, you can manage your markers,<br>you created on this website</p>
            </div>
            <div class="float-right">
                <router-link class="btn btn-secondary mt-1"
                             role="button"
                             :owner="user.id"
                             :to="{ name: 'create' }">
                    Create marker
                </router-link>
            </div>
        </header>
        <div class="content mt-5">
            <h3>Markers: <span id="markerCounter">{{ markers.length }}</span></h3>
            <hr>
            <transition>
                <router-view :owner="user.id"></router-view>
            </transition>
            <section id="markerStorage" v-for="marker in markers" :key="marker.id">
                <Default :marker="marker" />
            </section>
        </div>
    </section>
</template>

<script>
    import Default from "../components/Marker/Default";

    export default {
        name: "Dashboard",
        components: {
          Default
        },
        props: {
            user: Object,
            markers: Array,
            required: true
        }
    }
</script>
