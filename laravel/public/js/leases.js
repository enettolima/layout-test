/*
 * Functions to be executed when the html loads
 */
$(function () {
	//Just to enable the ajax file uploader
	$('.uploader-button').each(function() {
		uploader_add_file($(this).data('id'));
	});
	//Flash a row after document is uploaded
	$('.flash').fadeOut().fadeIn();
	if($('#message-container').length>0){
		$('#message-container').delay(3000).slideUp();
	}

	//If button is clicked show modal and insert information on it
	$(".fa-trash").on('click', function (e) {
		var name = $(this).closest('tr').find('td:eq(1)').text();
		var filename = $(this).closest('tr').find('td:eq(2)').text();
		var id = $(this).parents('tr').find('input[type="hidden"]').val();

		$('#file_name').html(name);
		$('#doc_id').val(id);
		$('#doc_filename').val(filename);
		$('#myModal').modal('toggle');
	});
});

