$(function() {
	var $modifyForm = $('#modifyForm');

    // modify page tab function
    $('#tab-zone a').on('click', function() {
        $(this).tab('show');
        return false;
    });

    // initial ckeditor
    $('.ckeditor').ckeditor();
	
    // prettyPhoto
    $("a[rel^='prettyPhoto']").prettyPhoto({
        social_tools: false,
        deeplinking: false
    })

	// focus on first element
	$modifyForm.find('input:text:first').focus();

	// add class rule
	$.validator.addClassRules("isNeed", {
		required: true
	})
	
	$.validator.addClassRules("isEmail", {
		email: true
	})

	$.validator.addClassRules("isNumber", {
		number: true
	})

	// go back button
	$('.btn-back').click(function() {
		window.history.back();
	});

	// form validation
	$modifyForm.validate({
		errorElement: 'span',
		errorClass: 'validation-error',
        errorPlacement: function(err, ele) {
            err.appendTo(ele.closest('div'));
        },
		submitHandler: function(form) {
            $admin.showModal();
			$(form).ajaxSubmit({
				iframe: true,
				success: function($res) {
					if ($res == '') {
						window.location = $('#back-page').val();
					} else {
                        $admin.hideModal();
                        alert($admin.replaceBr($res));
					}
				}
			});
		}
	});
});
