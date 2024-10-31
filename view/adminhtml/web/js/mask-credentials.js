define(["jquery"], function ($) {
    "use strict";

    var maskCredentials = function () {
        var keyField = $("#vendor_omnichat_general_key");
        var tokenField = $("#vendor_omnichat_general_token");

        var keyClone = $("<input>");
        var tokenClone = $("<input>");

        keyClone.attr("id", "vendor_omnichat_general_key_clone");
        keyClone.attr("type", "text");
        tokenClone.attr("id", "vendor_omnichat_general_token_clone");
        tokenClone.attr("type", "text");

        keyField.hide();
        tokenField.hide();

        keyField.after(keyClone);
        tokenField.after(tokenClone);

        var maskedKey = keyField.val().replace(/./g, "*");
        var maskedToken = tokenField.val().replace(/./g, "*");

        keyClone.val(maskedKey);
        tokenClone.val(maskedToken);

        $("form").on("submit", function () {
            if (keyClone.val() !== maskedKey) {
                keyField.val(keyClone.val());
            }
            if (tokenClone.val() !== maskedToken) {
                tokenField.val(tokenClone.val());
            }
        });
    };

    return maskCredentials;
});
