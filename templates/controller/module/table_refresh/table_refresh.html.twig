<script>
    function refreshTables() {
        var eventId = $('#event-table-event-id').val();
        var numberOfBets = $('#event-table-bet-numbers').val();
        if(eventId !== undefined) {
            $.ajax({
                method: "POST",
                url: '?module=table_refresh/isNeedRefreshEventTable&ajax=true',
                data: {eventId: eventId, numberOfBets: numberOfBets}
            }).done(function (data) {
                if (data == 1) {
                    $('#refreshing-able-table').fadeTo(500, 0.2, function () {
                        $('#refreshing-able-table').load('?module=table_refresh/getTable&ajax=true', {eventId: eventId}, function () {
                            $('#refreshing-able-table').fadeTo(900, 1);
                        });
                    });
                }
            });
        }

        var resultsCount = $('#results-count').val();
        if(resultsCount !== undefined) {
            $.ajax({
                method: "POST",
                url: '?module=table_refresh/isNeedRefreshResultTable&ajax=true',
                data: {resultsCount: resultsCount}
            }).done(function (data) {
                if (data == 1) {
                    $('#user-championship-result-table').fadeTo(500, 0.2, function () {
                        $('#user-championship-result-table').load('?module=userChampionship/index&ajax=true', function () {
                            $('#user-championship-result-table').fadeTo(900, 1);
                        });
                    });
                }
            });
        }

        var trophyResultId = $('#trophy-result-id').val();
        if(trophyResultId !== undefined) {
            $.ajax({
                method: "POST",
                url: '?module=table_refresh/isNeedRefreshTrophies&ajax=true',
                data: {trophyResultId: trophyResultId}
            }).done(function (data) {
                if (data == 1) {
                    $('#trophies-result-table').fadeTo(500, 0.2, function () {
                        $('#trophies-result-table').load('?module=trophies/index&ajax=true', function () {
                            $('#trophies-result-table').fadeTo(900, 1);
                        });
                    });
                }
            });
        }
    }

    $(document).ready(function () {
        setInterval(refreshTables, 10000);
    });
</script>