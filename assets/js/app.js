/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../../node_modules/bootstrap/dist/css/bootstrap.min.css');
require('../css/app.css');
const $ = require('jquery');
global.$ = global.jQuery = $;
require('bootstrap');

function refreshOnlineUsers() {
    $.getJSON("/get_online_user", function (data) {
        // Először reseteljük az összes user-activity elem színét
        $('.user-activity').css('background-color', 'transparent');

        $.each(data, function (key, val) {
            // Itt csak a színt fogjuk változtatni
            $('.user-' + val).css('background-color', 'green');
        });
    });
}

$(document).ready(function () {
    setTimeout(refreshOnlineUsers, 3000);
    setInterval(refreshOnlineUsers, 20000);
});
