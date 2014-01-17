var admin;
if (!admin) admin = {};

$(function() {
    var modal = $('.modal');

    // define modal
    modal.modal({
        keyboard: false,
        show: false,
        backdrop: 'static'
    });

    // show loading
    admin.showModal = function() {
        modal.modal('show');
    }

    // hide loading
    admin.hideModal = function() {
        modal.modal('hide');
    }

    admin.replaceBr = function($str) {
        return $str.replace(/<br>/gi, '\n');
    }
});

