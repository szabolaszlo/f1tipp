function reloadModules() {
    $.getJSON("/module/entity_counts", function (data) {
        $('.reload-able-module').each(function () {
            try {
                let entities = JSON.parse($(this).attr('data-module-entities'));
                let count = 0;
                $.each(entities, function (index, entity) {
                    count += data[entity];
                });
                if (parseInt($(this).attr('data-module-count')) !== count
                    && parseInt($(this).attr('data-module-count')) !== -1) {
                    let noAnimation = !!parseInt($(this).attr('data-module-no-animation'));
                    if (noAnimation) {
                        $(this).load('/module/' + $(this).attr('data-module-route'));
                    } else {
                        $(this).fadeTo(500, 0.2, function () {
                            $(this).load('/module/' + $(this).attr('data-module-route'), function () {
                                $(this).fadeTo(900, 1);
                            });
                        });
                    }
                }
                $(this).attr('data-module-count', count);
            } catch (e) {
            }
        })
    });
}

$(document).ready(function () {
    setInterval(reloadModules, 5000);
});