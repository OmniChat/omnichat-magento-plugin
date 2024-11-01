define(["jquery", "Magento_Ui/js/modal/alert"], function ($, alert) {
    "use strict";

    var label = $('label[for="vendor_omnichat_general_test_credentials"]');
    label.remove();
    $("#vendor_omnichat_general_test_credentials").attr(
        "value",
        "Validate credentials"
    );
    var originalKey = $("#vendor_omnichat_general_key").val();
    var originalToken = $("#vendor_omnichat_general_token").val();

    var testCredentials = function () {
        $("#vendor_omnichat_general_test_credentials").on("click", function () {
            var key = $("#vendor_omnichat_general_key").val();
            var token = $("#vendor_omnichat_general_token").val();

            if (!key.trim() || !token.trim()) {
                alert({
                    title: $.mage.__("Error"),
                    content: $.mage.__(
                        "Please, fill in the credentials before test."
                    ),
                    actions: {
                        always: function () {},
                    },
                });
                return;
            }

            if (key === originalKey && token === originalToken) {
                alert({
                    title: $.mage.__("Warning"),
                    content: $.mage.__(
                        "There were no changes to the keys."
                    ),
                    actions: {
                        always: function () {},
                    },
                });
                return;
            }

            $.ajax({
                url: "https://magento-api.omni.chat/v1/plugin/validate",
                type: "POST",
                headers: {
                    "x-api-key": key,
                    "x-api-secret": token,
                },
                success: function () {
                    alert({
                        title: $.mage.__("Success"),
                        content: $.mage.__("Credentials are valid."),
                        actions: {
                            always: function () {},
                        },
                    });
                },
                error: function (error) {
                    alert({
                        title: $.mage.__("Error"),
                        content: $.mage.__("Invalid credentials."),
                        actions: {
                            always: function () {},
                        },
                    });
                    console.log(error);
                },
            });
        });
    };

    return testCredentials;
});
