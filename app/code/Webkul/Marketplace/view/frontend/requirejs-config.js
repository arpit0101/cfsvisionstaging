var config = {
    map: {
        '*': {
            colorpicker: 'Webkul_Marketplace/js/colorpicker',
            productGallery:     'Webkul_Marketplace/js/product-gallery',
            baseImage:          'Webkul_Marketplace/catalog/base-image-uploader',
            newVideoDialog:  'Webkul_Marketplace/js/new-video-dialog',
            openVideoModal:  'Webkul_Marketplace/js/video-modal',
            configurableAttribute:  'Webkul_Marketplace/catalog/product/attribute',
            notification: 'mage/backend/notification',
            productAttributes:  'Webkul_Marketplace/catalog/product-attributes',
            verifySellerShop: 'Webkul_Marketplace/js/account/verify-seller-shop',
            intlTelInput: 'Webkul_Marketplace/js/intl-tel-input',
            bootstrap: 'Webkul_Marketplace/js/bootstrap',
            carouFredSel: 'Webkul_Marketplace/js/jquery.carouFredSel-6.0.0-packed'
        }
    },
    paths: {
        "colorpicker": 'js/colorpicker',
        "carouFredSel": 'Webkul_Marketplace/js/jquery.carouFredSel-6.0.0-packed',
        "intlTelInput": 'Webkul_Marketplace/js/intl-tel-input',
        "bootstrap": [
'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min',
'Webkul_Marketplace/js/bootstrap'
]
    },
    shim: {
        "colorpicker" : ["jquery"],
        "intlTelInput" : ["jquery"],
        "bootstrap" : {  deps : [ 'jquery'] },
        "carouFredSel" : {  deps : ["jquery","bootstrap"] }
    }
};
