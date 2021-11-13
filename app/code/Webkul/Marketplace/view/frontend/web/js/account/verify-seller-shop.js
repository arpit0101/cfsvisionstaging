/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.verifySellerShop', {
        options: {
            backUrl: '',
            formId: '#form-become-seller',
            profileurl: '#profileurl',
            becomeSellerBoxWrapper: '#wk-mp-become-seller-box-wrapper',
            available: '.available',
            unavailable: '.unavailable',
            button: '.button',
            pageLoader: '#wk-load'
        },
        _create: function () {
            var self = this;
            $(self.options.profileurl).on('keyup', function () {
                var profileUrlVal = $(this).val();
                $(self.options.profileurl).val(profileUrlVal.replace(/[^a-z^A-Z^0-9\.\-]/g,''));
            });
            $(self.options.profileurl).on('change', function (e) {
                self.callAjaxFunction();
            });
        },
        callAjaxFunction: function () {
            var self = this;
            $(self.options.button).addClass('disabled');
            var profileUrlVal = $(self.options.profileurl).val();
            $(self.options.available).remove();
            $(self.options.unavailable).remove();
            $(self.options.pageLoader).removeClass('no-display');
            $.ajax({
                type: "POST",
                url: self.options.ajaxSaveUrl,
                data: {
                    profileurl: profileUrlVal
                },
                success: function (response) {
                    $(self.options.pageLoader).addClass('no-display');
                    if (response==0) {
                        $(self.options.button).removeClass('disabled');
                        $(self.options.becomeSellerBoxWrapper).append($('<div/>').addClass('available message success').text(self.options.successMessage));
                    }else{
                        $(self.options.button).addClass('disabled');
                        $(self.options.becomeSellerBoxWrapper).append($('<div/>').addClass('available message error').text(self.options.errorMessage));
                    }
                },
                error: function (response) {
                    alert({
                        content: $t('There was error during saving seller shop data')
                    });
                }
            });
        }
    });
    return $.mage.verifySellerShop;
});
