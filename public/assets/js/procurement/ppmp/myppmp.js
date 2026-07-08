var __mysys_ppmp_ent = new __mysys_ppmp_ent();
function __mysys_ppmp_ent() {  
	const mesiteurl = $('#__siteurl').attr('data-mesiteurl');

	this.my_add_ppmp_line = function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#ppmp_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#ppmp_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();

			// Enable the delete icon for the new row
			jQuery(clonedRow).find('.text-danger').removeClass('text-muted').off('click').on('click', function () {
				jQuery(this).closest('tr').remove();
			});

			// Assign new IDs
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid);
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(2).attr('id', 'col4' + mid);
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col5' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(3).attr('id', 'col6' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(4).attr('id', 'col7' + mid);
			jQuery(clonedRow).find('input[type=date]').eq(0).attr('id', 'col8' + mid);
			jQuery(clonedRow).find('input[type=date]').eq(1).attr('id', 'col9' + mid);
			jQuery(clonedRow).find('input[type=date]').eq(2).attr('id', 'col10' + mid);
			jQuery(clonedRow).find('input[type=date]').eq(3).attr('id', 'col11' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(5).attr('id', 'col12' + mid);
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col13' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(6).attr('id', 'col14' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(7).attr('id', 'col15' + mid);

			// Reset values

			jQuery(clonedRow).find('input[type=text]').eq(0).val('');
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(0).val('');
			jQuery(clonedRow).find('input[type=text]').eq(2).val('');
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=text]').eq(3).val('');
			jQuery(clonedRow).find('input[type=text]').eq(4).val('');
			jQuery(clonedRow).find('input[type=month]').eq(0).val('');
			jQuery(clonedRow).find('input[type=month]').eq(1).val('');
			jQuery(clonedRow).find('input[type=month]').eq(2).val('');
			jQuery(clonedRow).find('input[type=month]').eq(3).val('');
			jQuery(clonedRow).find('input[type=text]').eq(5).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
			jQuery(clonedRow).find('input[type=text]').eq(6).val('');
			jQuery(clonedRow).find('input[type=text]').eq(7).val('');

			// Insert the cloned row before the last row (footer row)
			jQuery('#ppmp_line_items tbody').append(clonedRow);

			// Make the new row visible
			jQuery(clonedRow).css({ 'display': '' });

			// Set the ID for the new row
			jQuery(clonedRow).attr('id', 'tr_rec_' + mid);

			// Focus on the first textarea of the cloned row
			var xobjArtItem = jQuery(clonedRow).find('textarea').eq(0).attr('id');
			jQuery('#' + xobjArtItem).focus();

		} catch (err) {
			var mtxt = 'There was an error on this page.\n';
			mtxt += 'Error description: ' + err.message;
			mtxt += '\nClick OK to continue.';
			alert(mtxt);
			return false;
		}
	}

	this.my_add_ppmp_line_above = function (elem) {
		try {
			var rowCount = jQuery('#ppmp_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the hidden template row
			var templateRow = jQuery('#ppmp_line_items tbody tr:hidden:first').clone();

			// Set new IDs and clear values
			jQuery(templateRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid);
			jQuery(templateRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid);
			jQuery(templateRow).find('input[type=text]').eq(2).attr('id', 'col4' + mid);
			jQuery(templateRow).find('input[type=number]').eq(1).attr('id', 'col5' + mid);
			jQuery(templateRow).find('input[type=text]').eq(3).attr('id', 'col6' + mid);
			jQuery(templateRow).find('input[type=text]').eq(4).attr('id', 'col7' + mid);
			jQuery(templateRow).find('input[type=month]').eq(0).attr('id', 'col8' + mid);
			jQuery(templateRow).find('input[type=month]').eq(1).attr('id', 'col9' + mid);
			jQuery(templateRow).find('input[type=month]').eq(2).attr('id', 'col10' + mid);
			jQuery(templateRow).find('input[type=month]').eq(3).attr('id', 'col11' + mid);
			jQuery(templateRow).find('input[type=text]').eq(5).attr('id', 'col12' + mid);
			jQuery(templateRow).find('input[type=number]').eq(2).attr('id', 'col13' + mid);
			jQuery(templateRow).find('input[type=text]').eq(6).attr('id', 'col14' + mid);
			jQuery(templateRow).find('input[type=text]').eq(7).attr('id', 'col15' + mid);

			// Insert above the clicked row
			var currentRow = jQuery(elem).closest('tr');
			templateRow.css('display', '').attr('id', 'tr_rec_' + mid);
			templateRow.insertAfter(currentRow);

			// Optional: focus the first input field
			jQuery(templateRow).find('input[type=text]').eq(0).focus();

		} catch (err) {
			alert('Error: ' + err.message);
		}
	}

	function generateRandomID(length) {
		const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		let result = '';
		for (let i = 0; i < length; i++) {
			result += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		return result;
	}

	this.__ppmp_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.myppmp-validation')
		// Loop over them and prevent submission
		Array.prototype.slice.call(forms)
		.forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (!form.checkValidity()) {
					event.preventDefault()
					event.stopPropagation()
				}
				try {
					event.preventDefault();
					event.stopPropagation();

					var recid = document.getElementById("recid");
					var ppmpno = document.getElementById("ppmpno");
					var end_user = document.getElementById("end_user");
					var fiscal_year = document.getElementById("fiscal_year");
					var project_title = document.getElementById("project_title");
					var responsibility_code = document.getElementById("responsibility_code");
					let is_indicative = document.getElementById("is_indicative").checked ? 1 : 0;
					let is_final = document.getElementById("is_final").checked ? 1 : 0;
					var prepared_by = document.getElementById("prepared_by");
					var submitted_by = document.getElementById("submitted_by");
					var rowcount1 = jQuery('.ppmpdata-list tr').length;
					var ppmpdtdata = [];
					var ppmpdata = '';
	
					for (var aa = 2; aa < rowcount1; aa++) {
						var clonedRow = jQuery('.ppmpdata-list tr:eq(' + aa + ')'); 

						var item_desc = clonedRow.find('input[type=text]').eq(0).val();
						var item_type = clonedRow.find('input[type=text]').eq(1).val();
						var quantity = clonedRow.find('input[type=number]').eq(0).val();
						var size = clonedRow.find('input[type=text]').eq(2).val();
						var unit_cost = clonedRow.find('input[type=number]').eq(1).val();
						var mop = clonedRow.find('input[type=text]').eq(3).val();
						var is_preproc = clonedRow.find('input[type=text]').eq(4).val();
						var proc_start = clonedRow.find('input[type=month]').eq(0).val();
						var proc_end = clonedRow.find('input[type=month]').eq(1).val();
						var expected_delivery_from = clonedRow.find('input[type=month]').eq(2).val();
						var expected_delivery_to = clonedRow.find('input[type=month]').eq(3).val();
						var funding_source = clonedRow.find('input[type=text]').eq(5).val();
						var estimated_budget = clonedRow.find('input[type=number]').eq(2).val();
						var attached_document = clonedRow.find('input[type=text]').eq(6).val();
						var remarks = clonedRow.find('input[type=text]').eq(7).val();

						ppmpdata = item_desc + 'x|x' + item_type + 'x|x' + quantity
						+ 'x|x' + size + 'x|x' + unit_cost + 'x|x' + mop
						+ 'x|x' + is_preproc + 'x|x' + proc_start + 'x|x' + proc_end
						+ 'x|x' + expected_delivery_from + 'x|x' + expected_delivery_to + 'x|x' + funding_source
						+ 'x|x' + estimated_budget + 'x|x' + attached_document + 'x|x' + remarks;
						ppmpdtdata.push(ppmpdata);
					}

					var mparam = { 
						recid: recid.value,
						ppmpno: ppmpno.value,
						end_user: end_user.value,
						fiscal_year: fiscal_year.value,
						project_title: project_title.value,
						responsibility_code: responsibility_code.value,
						prepared_by: prepared_by.value,
						submitted_by: submitted_by.value,
						ppmpdtdata: ppmpdtdata,
						is_indicative: is_indicative,
						is_final: is_final,
						
						meaction: 'PPMP-SAVE'
					}

					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'myppmp',
						context: document.body,
						data: eval(mparam),
						global: false,
						cache: false,
						success: function(data) {
							jQuery('.myppmp-outp-msg').html(data);
							return false;
						},
						error: function(xhr, status, error) { // display global error on the menu function
							alert('Error: ' + error);
							return false;
						} 
					}); 

				} catch(err) { 
					alert(err.message)
					return false;
				} //end try 
			}, false)
		}); //end forEach		
	};


	$(document).ready(function () {
        $('#datatablesSimple').DataTable({
            pageLength: 5,
            lengthChange: false,
            order: [[2, 'desc']],
            language: {
            search: "Search:"
            }
        });

        $('.revision').prop('disabled', true);

    });

    $(document).on('change', '.selUacs', function() {
        var selectedCode = $(this).find('option:selected').data('uacs');
        $(this).closest('tr').find('.uacs').val(selectedCode);
    });

    $(document).on('change', '#selProjectTitle', function() {
        var selected = $(this).find('option:selected');

        // Extract data from selected option
        var fund = selected.data('fund') || '';
        var division = selected.data('division') || '';
        var responsibility = selected.data('responsibility') || '';

        // Set the values into inputs
        $('#fund_cluster_code').val(fund);
        $('#division_name').val(division);
        $('#responsibility_code').val(responsibility);
    });

	this.__showPdfInModal = function(pdfUrl) {
		var pdfFrame = document.getElementById("pdfFrame");
		var pdfModal = new bootstrap.Modal(document.getElementById("pdfModal"));

		pdfFrame.src = pdfUrl;
		pdfModal.show();
	};

}; //end main
