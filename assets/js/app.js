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

(function (global) {
    function checkFakeBet(selectClass, submitId) {

        $(selectClass).change(function () {
            var duplicate = '';
            var nullRecords = false;

            var selects = [];
            $(selectClass).each(function () {
                selects.push($(this).val());
            });

            for (let i = 0; i < selects.length; i++) {
                for (let j = 0; j < selects.length; j++) {
                    if (i !== j) {
                        if (selects[i] === selects[j] && selects[i] !== "empty") {
                            duplicate = selects[i];
                        }
                    }
                }
            }

            $(selectClass).each(function () {
                if ($(this).val() == duplicate && duplicate !== "") {
                    $(this).css('border', '3px solid red');
                } else {
                    $(this).css('border', '1px solid #ccc');
                }
                if ($(this).val() == "empty") {
                    nullRecords = true;
                }
            });

            if (duplicate == '' && !nullRecords) {
                $(submitId).each(function () {
                    $(this).prop('disabled', false)
                });
            } else {
                $(submitId).each(function () {
                    $(this).prop('disabled', true)
                });
            }
        });
    }

    global.bundleObj = {
        checkFakeBet: checkFakeBet,
    }
})(window)

function reloadModules() {
    $.getJSON("/module/entity_counts", function (data) {
        $('.reload-able-module').each(function () {
            try {
                let entities = JSON.parse($(this).attr('data-module-entities'));
                let count = 0;
                $.each(entities, function (index, entity) {
                    count += data[entity];
                });
                if (parseInt($(this).attr('data-module-count')) !== 0
                    && parseInt($(this).attr('data-module-count')) < count) {
                    $(this).fadeTo(500, 0.2, function () {
                        $(this).load('/module/' + $(this).attr('data-module-route'), function () {
                            $(this).fadeTo(900, 1);
                        });
                    });
                }
                $(this).attr('data-module-count', count);
            } catch (e) {
            }
        })
    });
}

$(document).ready(function () {
    refreshOnlineUsers();
    reloadModules();
    setInterval(refreshOnlineUsers, 20000);
    setInterval(reloadModules, 3000);
});