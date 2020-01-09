/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');
const $ = require('jquery');
global.$ = global.jQuery = $;
require('bootstrap');

import '../../node_modules/Hinclude/hinclude.js';

function refreshOnlineUsers() {
    $.get('/set_online_user');
    $.getJSON("/get_online_user", function (data) {
        $('.user-activity').each(function () {
            $(this).empty();
        });
        $.each(data, function (key, val) {
            $('.user-' + val).append('<span class="glyphicon glyphicon-eye-open" aria-hidden="true" title="Online" style="color: yellow; font-size: 1.1em;"></span>');
        });
    });
}

$(document).ready(function () {
    refreshOnlineUsers();
    setInterval(refreshOnlineUsers, 20000);
});