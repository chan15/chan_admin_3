$(function() {
    var $idArray = [],
        $sortArray = [],
        $btnSaveSort = $('#btn-save-sort'),
        $btnDeleteAll = $('.btn-del-all'),
        $mainTable = $('#main-table');
        
    // prettyPhoto
    $("a[rel^='prettyPhoto']").prettyPhoto({
        social_tools: false,
        deeplinking: false
    });

	// change online status to no button
	$mainTable.on('click', '.btn-yes', function() {
        var $id = $(this).closest('tr').prop('id'),
            $tableField = $('#table-field').val(),
            $temp = $(this),
            $html = '<a href="#" class="btn-no">否</a>';

        $admin.showModal();

        $.post('ajax-change-online.php', {action: 0, tableField: $tableField, id: $id}, function() {
            $admin.hideModal();
            $temp.replaceWith($html);
        });

        return false;
	});

	// change online status to yes button
	$mainTable.on('click', '.btn-no', function() {
        var $id = $(this).closest('tr').prop('id'),
            $tableField = $('#table-field').val(),
            $temp = $(this),
            $html = '<a href="#" class="btn-yes">是</a>';

        $admin.showModal();

        $.post('ajax-change-online.php', {action: 1, tableField: $tableField, id: $id}, function() {
            $admin.hideModal();
            $temp.replaceWith($html);
        });

        return false;
	});

	// reset search form button
	$('#btn-reset-search').click(function() {
		window.location = $(this).attr('url');
	});

   // limit select change
    $('#limit').change(function() {
        $('#limit-form').submit();
    });

	// single delete button
	$('[name="btn-del"]').on('click', function() {
        var $id = $(this).prop('id'),
            $tableField = $('#table-field').val(),
            $temp = $(this);

        if (confirm('確定要刪除嗎？')) {
            $admin.showModal();
            $.post('multi_delete.php', {tableField: $tableField, id: $id}, function() {
                window.location.reload();	
            });
        }

        return false;
	});

	// delete all button
	$('.btn-del-all').on('click', function() {
		if ($('.check-item:checked').size() > 0) {
            if (confirm('確定要刪除嗎？')) {
					var $tableField = $('#table-field').val(),
						$idField = $('#id-field').val(),
						$id = [];
					$('.check-item:checked').each(function() {
						$id.push($(this).prop('id'));
					});
					$id = $id.join(',');

                    $admin.showModal();

					$.post('multi_delete.php', {tableField: $tableField, idField: $idField, id: $id}, function() {
						window.location.reload();	
					});
			};
            }
		return false;
	});

    // check all checkboxes when the one in a table head is checked:
    $('.check-all').on('click', function() {
        if ($(this).prop('checked')) {
            $('.check-item').prop('checked', true);
            $btnDeleteAll.stop().fadeTo('slow', 1);
    } else {
        $('.check-item').prop('checked', false);
        $btnDeleteAll.stop().fadeTo('slow', 0.5);
    }
    });

    $('.table').on('click', '.check-item', function() {
        if ($('.check-item:checked').size() != 0) {
            $btnDeleteAll.stop().fadeTo('slow', 1);
        } else {
            $btnDeleteAll.stop().fadeTo('slow', 0.5);
        }
    });

	// delete all button
	$btnDeleteAll.css('opacity', 0.5);

    $('.table > tbody > tr').each(function() {
        $idArray.push($(this).prop('id'));
        $sortArray.push($(this).attr('sort'));
    });

    $('#id-serial').val($idArray.join(','));
    $('#sort-serial').val($sortArray.join(','));

    // sort
    if ($('#sort-field').val() == 'yes') {
        var $html = '<span class="btn btn-default btn-xs sort-move">↕</span>';

        $('.table tr').each(function() {
            $(this).find('td:last').append($html);
        });

        $('.table > tbody').sortable({
            handle: '.sort-move',
            update: function() {
                $btnSaveSort.fadeIn();
                $idArray = [];

                $('.table > tbody > tr').each(function() {
                    $idArray.push($(this).prop('id'));
                });

                $('#id-serial').val($idArray.join(','));
            }
        });

        $btnSaveSort.on('click', function() {
            var $val = {tableField: $('#table-field').val(), idField: $('#id-field').val(), sortField: $('#sort-field').val(), idSerial: $('#id-serial').val(), sortSerial: $('#sort-serial').val()}
            $.post('sort.php', $val, function() {
                window.location.reload();
            })
        });
    }

    // save sort result button
    $('#btn-save-sort').on('click', function() {
        $admin.showModal();
    });
});
