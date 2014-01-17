$(function() {
    var form = $('#login-form');

    form.find('input:first').focus();

    // form validation
    form.validate({
        rules: {
            username: 'required',
            password: 'required'
        },
        submitHandler: function(form) {
            admin.showModal();

            var val = {login: true, username: $('#username').val(), password: $('#password').val()};
            $.post('index.php', val, function(response) {
                admin.hideModal();

                if ('ok' === response.status) {
                    window.location = 'admin.php';
                } else {
                    alert(response.message);
                }

            }, 'json');
        }
    });
});
