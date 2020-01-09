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
