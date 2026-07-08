var __mysys_rci_ent = new __mysys_rci_ent();
function __mysys_rci_ent() {  
	const mesiteurl = $('#__siteurl').attr('data-mesiteurl');

	this.my_add_rci_line = function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#rci_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#rci_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
				// Enable the delete icon for the new row

			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for date field
            jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col6' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(0).val('');
	
			// Insert the cloned row before the last row (footer row)
			jQuery('#rci_line_items tbody').append(clonedRow);

			// Make the new row visible
			jQuery(clonedRow).css({ 'display': '' });
	
			// Set the ID for the new row
			jQuery(clonedRow).attr('id', 'tr_rec_' + mid);
	
			// Focus on the first input field of the cloned row
			var xobjArtItem = jQuery(clonedRow).find('input[type=text]').eq(0).attr('id');
			jQuery('#' + xobjArtItem).focus();
	
		} catch (err) {
			var mtxt = 'There was an error on this page.\\n';
			mtxt += 'Error description: ' + err.message;
			mtxt += '\\nClick OK to continue.';
			alert(mtxt);
			return false;
		}
	}

	this.my_add_rci_line_above = function (elem) {
		try {
			var rowCount = jQuery('#rci_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the hidden template row
			var templateRow = jQuery('#rci_line_items tbody tr:hidden:first').clone();

			// Set new IDs and clear values
			jQuery(templateRow).find('input[type=text]').each(function (i) {
				jQuery(this).val('').attr('id', 'col' + (2 + i) + mid);
			});
            jQuery(templateRow).find('input[type=number]').eq(0).val('').attr('id', 'col1' + mid);

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

	this.__rci_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.myrci-validation')
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
					var mds_branch = document.getElementById("mds_branch");
					var mds_accountno = document.getElementById("mds_accountno");
					var fund_cluster_code = document.getElementById("fund_cluster_code");
					var reportno = document.getElementById("reportno");
					// Prepare PS data DIRECT --
					var rowcount1 = jQuery('.rcidata-list tr').length;
					var rcidtdata = [];
					var rcidata = '';
	
					for (var aa = 2; aa < rowcount1; aa++) {
						var clonedRow = jQuery('.rcidata-list tr:eq(' + aa + ')'); 
						var lddapadano = clonedRow.find('input[type=text]').eq(0).val();
						var lddapada_date = clonedRow.find('input[type=date]').eq(0).val();
						var ckno = clonedRow.find('input[type=text]').eq(1).val();
						var dvno = clonedRow.find('input[type=text]').eq(2).val();
						var serialno = clonedRow.find('input[type=text]').eq(3).val();
						var responsibility_code = clonedRow.find('input[type=text]').eq(4).val();
						var payee_name = clonedRow.find('input[type=text]').eq(5).val();
						var particulars = clonedRow.find('input[type=text]').eq(6).val();
						var gross_amount = clonedRow.find('input[type=number]').eq(0).val();
						var philheath_amount = clonedRow.find('input[type=number]').eq(1).val();
						var tax_amount = clonedRow.find('input[type=number]').eq(2).val();
						var net_amount = clonedRow.find('input[type=number]').eq(3).val();
						
						rcidata = lddapadano + 'x|x' + lddapada_date + 'x|x' + ckno + 'x|x' + dvno + 'x|x' + serialno + 'x|x' + responsibility_code + 'x|x' + payee_name + 'x|x' + particulars + 'x|x' + gross_amount + 'x|x' + philheath_amount + 'x|x' + tax_amount + 'x|x' + net_amount;
						rcidtdata.push(rcidata);
					}

					var mparam = { 
						recid: recid.value,
						mds_branch: mds_branch.value,
						mds_accountno: mds_accountno.value,
						fund_cluster_code: fund_cluster_code.value,
						reportno: reportno.value,
						rcidtdata:rcidtdata,
						meaction: 'MAIN-SAVE'
					}

					console.log(rcidtdata);

					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'myrci',
						context: document.body,
						data: eval(mparam),
						global: false,
						cache: false,
						success: function(data) {
							jQuery('.myrci-outp-msg').html(data);
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

	function generateRandomID(length) {
		const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		let result = '';
		for (let i = 0; i < length; i++) {
			result += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		return result;
	}

	this.__showPdfInModal = function(pdfUrl) {
		var pdfFrame = document.getElementById("pdfFrame");
		var pdfModal = new bootstrap.Modal(document.getElementById("pdfModal"));

		pdfFrame.src = pdfUrl;
		pdfModal.show();
	};

	$(document).ready(function () {
        $('#datatablesSimple').DataTable({
            pageLength: 5,
            lengthChange: false,
            language: {
            search: "Search:"
            }
        });
    });

}; //end main
