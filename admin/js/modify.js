$(function() {
	var modifyForm = $('#modifyForm');

    // Modify page tab function
    $('#tab-zone a').on('click', function() {
        $(this).tab('show');
        return false;
    });

    // Initial ckeditor
    $('.ckeditor').ckeditor({
        customConfig: '../js/chan-config.js'
    });

    // Datepicker
    $('.date').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    // PrettyPhoto
    $("a[rel^='prettyPhoto']").prettyPhoto({
        social_tools: false,
        deeplinking: false
    })

	// Focus on first element
	modifyForm.find('input:text:first').focus();

	// Add class rule
	$.validator.addClassRules("isNeed", {
		required: true
	})

	$.validator.addClassRules("isEmail", {
		email: true
	})

	$.validator.addClassRules("isNumber", {
		number: true
	})

	// Go back button
	$('.btn-back').click(function() {
		window.history.back();
	});

	// Form validation
	modifyForm.validate({
		errorElement: 'span',
		errorClass: 'validation-error',
        errorPlacement: function(err, ele) {
            err.appendTo(ele.closest('div'));
        },
		submitHandler: function(form) {
            admin.showModal();
			$(form).ajaxSubmit({
				iframe: true,
				success: function(response) {
					if ('' === response) {
						window.location = $('#back-page').val();
					} else {
                        admin.hideModal();
                        alert(admin.replaceBr(response));
					}
				}
			});
		}
	});
});
