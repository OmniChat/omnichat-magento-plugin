define(["jquery", "Magento_Ui/js/modal/alert"], function ($, alert) {
    "use strict";

    var label = $('label[for="vendor_omnichat_general_test_credentials"]');
    label.remove();
    $("#vendor_omnichat_general_test_credentials").attr("value", "Validate credentials");

    var testCredentials = function () {
        $("#vendor_omnichat_general_test_credentials").on("click", function () {
            var key = $("#vendor_omnichat_general_key").val();
            var token = $("#vendor_omnichat_general_token").val();
            var keyClone = $("#vendor_omnichat_general_key_clone").val();
            var tokenClone = $("#vendor_omnichat_general_token_clone").val();

            if (keyClone && !keyClone.split('').every((char) => char === "*")) {
                key = keyClone;
            }

            if (tokenClone && !tokenClone.split('').every((char) => char === "*")) {
                token = tokenClone;
            }

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

            $.ajax({
                url: "https://magento-api.omni.chat/plugin/validate",
                type: "POST",
                headers: {
                    "x-api-key": key,
                    "x-api-secret": token,
                },
                success: function () {
                    alert({
                        title: $.mage.__("Success"),
                        content: $.mage.__(
                            "Credentials are valid."
                        ),
                        actions: {
                            always: function () {},
                        },
                    });
                },
                error: function (error) {
                    alert({
                        title: $.mage.__("Error"),
                        content: $.mage.__(
                            "Invalid credentials."
                        ),
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
