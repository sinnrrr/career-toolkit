require('./bootstrap');

import Vue from 'vue';
import VueRouter from "vue-router";
import * as VueGoogleMaps from 'vue2-google-maps';

Vue.use(VueRouter);
Vue.use(VueGoogleMaps, {
    load: {
        key: 'AIzaSyCtRwPhPwGJgjuQJhNXq__cjCo6oU_XQdM'
    }
})

import {routes} from './routes.js';

const router = new VueRouter({routes, mode: 'history'});

// auto import
const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

const app = new Vue({
    el: '#app',
    router
});
