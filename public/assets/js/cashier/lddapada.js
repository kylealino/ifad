var __mysys_lddapada_ent = new __mysys_lddapada_ent();
function __mysys_lddapada_ent() {  
	const mesiteurl = $('#__siteurl').attr('data-mesiteurl');

	//PS
	this.my_add_budget_line = function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
				// Enable the delete icon for the new row

			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for date field
            jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col6' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(0).val('');
	
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_line_items tbody').append(clonedRow);

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

	this.my_add_budget_line_above = function (elem) {
		try {
			var rowCount = jQuery('#budget_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the hidden template row
			var templateRow = jQuery('#budget_line_items tbody tr:hidden:first').clone();

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

	this.my_add_budget_indirect_line = function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_indirect_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_indirect_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for date field
            jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col6' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(0).val('');
	
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_indirect_line_items tbody').append(clonedRow);
	
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

	this.my_add_budget_indirect_line_above = function (elem) {
		try {
			var rowCount = jQuery('#budget_indirect_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the hidden template row
			var templateRow = jQuery('#budget_indirect_line_items tbody tr:hidden:first').clone();

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

	//MOOE
	this.my_add_budget_mooe_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_mooe_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_mooe_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for date field
            jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col6' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(0).val('');
	
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_mooe_line_items tbody').append(clonedRow);

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

	this.my_add_budget_mooe_line_above = function (elem) {
		try {
			var rowCount = jQuery('#budget_mooe_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the hidden template row
			var templateRow = jQuery('#budget_mooe_line_items tbody tr:hidden:first').clone();

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

	this.my_add_budget_indirect_mooe_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_mooe_indirect_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_mooe_indirect_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for date field
            jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col6' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(0).val('');

			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_mooe_indirect_line_items tbody').append(clonedRow);

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

	this.my_add_budget_indirect_mooe_line_above = function (elem) {
		try {
			var rowCount = jQuery('#budget_mooe_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the hidden template row
			var templateRow = jQuery('#budget_mooe_line_items tbody tr:hidden:first').clone();

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

	//CO
	this.my_add_budget_co_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_co_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_co_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for date field
            jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col6' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(0).val('');
	
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_co_line_items tbody').append(clonedRow);

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

	this.my_add_budget_co_line_above = function (elem) {
		try {
			var rowCount = jQuery('#budget_co_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the hidden template row
			var templateRow = jQuery('#budget_co_line_items tbody tr:hidden:first').clone();

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

			// Recalculate if needed
			this.__direct_co_totals();

		} catch (err) {
			alert('Error: ' + err.message);
		}
	}

	this.my_add_budget_indirect_co_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_indirect_co_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_indirect_co_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for date field
            jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col6' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');
            jQuery(clonedRow).find('input[type=text]').eq(2).val('');
			jQuery(clonedRow).find('input[type=text]').eq(3).val('');
			jQuery(clonedRow).find('input[type=text]').eq(4).val('');
			jQuery(clonedRow).find('input[type=text]').eq(5).val('');
			jQuery(clonedRow).find('input[type=number]').eq(0).val('');

			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_indirect_co_line_items tbody').append(clonedRow);

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

	this.my_add_budget_indirect_co_line_above = function (elem) {
		try {
			var rowCount = jQuery('#budget_indirect_co_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the hidden template row
			var templateRow = jQuery('#budget_indirect_co_line_items tbody tr:hidden:first').clone();

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

	function generateRandomID(length) {
		const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		let result = '';
		for (let i = 0; i < length; i++) {
			result += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		return result;
	}
	
	this.__lddapada_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.mylddapada-validation')
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
					var lddapadano = document.getElementById("lddapadano");
					var mds_branch = document.getElementById("mds_branch");
					var mds_accountno = document.getElementById("mds_accountno");
					var lddapada_date = document.getElementById("lddapada_date");
					var fund_cluster_code = document.getElementById("fund_cluster_code");
					var funding_source = document.getElementById("funding_source");
					var ada_approver = document.getElementById("ada_approver");
					let is_ci = document.getElementById("is_ci").checked ? 1 : 0;
					var container = document.getElementById("selected_dvno_list");
					var items = container.querySelectorAll('.badge');

					var dvno_list = [];

					items.forEach(function(el) {
						dvno_list.push(el.getAttribute('data-dvno'));
					});

					// Prepare PS data DIRECT --
					var rowcount1 = jQuery('.dvdata-list tr').length;
					var dvdtdata = [];
					var psdata = '';
	
					for (var aa = 2; aa < rowcount1; aa++) {
						var clonedRow = jQuery('.dvdata-list tr:eq(' + aa + ')'); 
						var dvno = clonedRow.find('input[type=text]').eq(0).val();
						var payee_name = clonedRow.find('input[type=text]').eq(1).val();
						var payee_account_num = clonedRow.find('input[type=text]').eq(2).val();
						var serialno = clonedRow.find('input[type=text]').eq(3).val();
						var uacs_code = clonedRow.find('input[type=text]').eq(4).val();
						var gross_amount = clonedRow.find('input[type=number]').eq(0).val();
						var total_deduction = clonedRow.find('input[type=number]').eq(1).val();
						var net_amount = clonedRow.find('input[type=number]').eq(2).val();  
						var remarks = clonedRow.find('textarea').eq(0).val(); 
						
						psdata = dvno + 'x|x' + payee_name + 'x|x' + payee_account_num + 'x|x' + serialno + 'x|x' + uacs_code + 'x|x' + gross_amount + 'x|x' + total_deduction + 'x|x' + net_amount + 'x|x' + remarks;
						dvdtdata.push(psdata);
					}

					var mparam = { 
						recid: recid.value,
						lddapadano: lddapadano.value,
						mds_branch: mds_branch.value,
						mds_accountno: mds_accountno.value,
						lddapada_date: lddapada_date.value,
						fund_cluster_code: fund_cluster_code.value,
						funding_source: funding_source.value,
						ada_approver: ada_approver.value,
						is_ci: is_ci,
						dvdtdata:dvdtdata,
						dvno_list: dvno_list,
						meaction: 'MAIN-SAVE'
					}

					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'mylddapada',
						context: document.body,
						data: eval(mparam),
						global: false,
						cache: false,
						success: function(data) {
							jQuery('.mylddapada-outp-msg').html(data);
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

	//CO - TOTAL PER LINE - DIRECT
	this.__direct_co_totals = function () {
        jQuery('.budgetcodata-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var revision = parseFloat(row.find('.revision').val()) || 0;

            var total = approved + revision;

            row.find('.proposed_revision').val(total.toFixed(2));
        });
    };

	this.__combined_totals = function () {
		let totalApprovedCombined = 0;
		let totalProposedCombined = 0;

		// PS DIRECT COST TABLE
		jQuery('.budgetdata-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let revision = parseFloat(row.find('.revision').val()) || 0;

			let total = approved + revision;

			row.find('.proposed_revision').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;
		});

		// MOOE DIRECT COST TABLE
		jQuery('.budgetmooedata-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let revision = parseFloat(row.find('.revision').val()) || 0;

			let total = approved + revision;

			row.find('.proposed_revision').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;
		});

		// CO DIRECT COST TABLE
		jQuery('.budgetcodata-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let revision = parseFloat(row.find('.revision').val()) || 0;

			let total = approved + revision;

			row.find('.proposed_revision').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;
		});

		// SET VALUES TO INPUT FIELDS
		jQuery('#total_approved_combined').val(totalApprovedCombined.toFixed(2));
		jQuery('#total_proposed_combined').val(totalProposedCombined.toFixed(2));
	};

	this.__toggleExtensionFields = function(checkbox){
		var extFields = document.getElementById("extension_fields");
        extFields.style.display = checkbox.checked ? "block" : "none";
	}

	this.__saob_print = function(pdfUrl) {
		var pdfFrame = document.getElementById("pdfFrame");
		var placeholder = document.getElementById("pdfPlaceholder");

		const month = document.getElementById("month").value;
		const year = document.getElementById("year").value;

		if (!month || !year) {
			toastr.error('Please select both month and year.', 'Oops!', {
				progressBar: true,
				closeButton: true,
				timeOut: 2000,
			});
			return;
		}

		let url = new URL(pdfUrl, window.location.origin);
		url.searchParams.set('month', month);
		url.searchParams.set('year', year);

		// Set iframe src and show iframe
		pdfFrame.src = url.toString();
		pdfFrame.style.display = "block";

		// Hide the placeholder
		placeholder.style.display = "none";
	};

	this.__showPdfInModal = function(pdfUrl) {
		var pdfFrame = document.getElementById("pdfFrame");
		var pdfModal = new bootstrap.Modal(document.getElementById("pdfModal"));

		pdfFrame.src = pdfUrl;
		pdfModal.show();
	};

	//APPROVAL
	this.__approve_certa = function() {
		const approveBtn = document.getElementById('btn_approve');
		const confirmApproveBtn = document.getElementById('confirmApproveBtn');
		var serialno = document.getElementById("serialno");
		var funding_source = document.getElementById("funding_source");

		console.log('button approve is clicked...');
	
		let recid = null; // store recid for use after confirmation
	
		approveBtn.addEventListener('click', function () {
			recid = document.getElementById("recid");
			approver = document.getElementById("approved_by");
			remarks = document.getElementById("approved_remarks");
	
			// Show the modal
			const approveModal = new bootstrap.Modal(document.getElementById('confirmApproveModal'));
			approveModal.show();
		});
	
		confirmApproveBtn.addEventListener('click', function () {
			if (!recid) return;
	
			const mparam = {
				recid: recid.value,
				approver: approver.value,
				remarks: remarks.value,
				serialno: serialno.value,
				funding_source: funding_source.value,
				meaction: 'MAIN-APPROVE-A'
			};
	
			jQuery.ajax({
				type: "POST",
				url: mesiteurl + 'mylddapada',
				context: document.body,
				data: eval(mparam),
				global: false,
				cache: false,
				success: function(data) {
					jQuery('.mylddapada-outp-msg').html(data);

					// Close the approve modal after successful approval
					const approveModal = bootstrap.Modal.getInstance(document.getElementById('confirmApproveModal'));
					approveModal.hide();
				},
				error: function(xhr, status, error) {
					alert('Error: ' + error);
				}
			});
	
			// Close the modal
			const approveModal = bootstrap.Modal.getInstance(document.getElementById('confirmApproveModal'));
			approveModal.hide();
		});
	};

	this.__disapprove_certa = function() {
		const disapproveBtn = document.getElementById('btn_disapprove');
		console.log('Disapprove Button Clicked');
		const confirmDisapproveBtn = document.getElementById('confirmDisapproveBtn');
	
		let recid = null; // store recid for use after confirmation
	
		disapproveBtn.addEventListener('click', function () {
			recid = document.getElementById("recid");
			approver = document.getElementById("disapproved_by");
			remarks = document.getElementById("disapproved_remarks");
	
			// Show the modal
			const disapproveModal = new bootstrap.Modal(document.getElementById('confirmDisapproveModal'));
			disapproveModal.show();
		});
	
		confirmDisapproveBtn.addEventListener('click', function () {
			if (!recid) return;
	
			const mparam = {
				recid: recid.value,
				approver: approver.value,
				remarks: remarks.value,
				meaction: 'MAIN-DISAPPROVE-A'
			};
	
			jQuery.ajax({
				type: "POST",
				url: mesiteurl + 'mylddapada',
				context: document.body,
				data: mparam,
				global: false,
				cache: false,
				success: function(data) {
					jQuery('.mylddapada-outp-msg').html(data);

					// Close the approve modal after successful approval
					const approveModal = bootstrap.Modal.getInstance(document.getElementById('confirmDisapproveModal'));
					approveModal.hide();
				},
				error: function(xhr, status, error) {
					alert('Error: ' + error);
				}
			});
	
			// Close the modal
			const disapproveModal = bootstrap.Modal.getInstance(document.getElementById('confirmDisapproveBtn'));
			disapproveModal.hide();
		});
	};

	this.__approve_certb = function() {
		const approveBtn = document.getElementById('btn_approve');
		const confirmApproveBtn = document.getElementById('confirmApproveBtn');

		console.log('button approve is clicked...');
	
		let recid = null; // store recid for use after confirmation
	
		approveBtn.addEventListener('click', function () {
			recid = document.getElementById("recid");
			approver = document.getElementById("approved_by");
			remarks = document.getElementById("approved_remarks");
	
			// Show the modal
			const approveModal = new bootstrap.Modal(document.getElementById('confirmApproveModal'));
			approveModal.show();
		});
	
		confirmApproveBtn.addEventListener('click', function () {
			if (!recid) return;
	
			const mparam = {
				recid: recid.value,
				approver: approver.value,
				remarks: remarks.value,
				meaction: 'MAIN-APPROVE-B'
			};
	
			jQuery.ajax({
				type: "POST",
				url: mesiteurl + 'mylddapada',
				context: document.body,
				data: eval(mparam),
				global: false,
				cache: false,
				success: function(data) {
					jQuery('.mylddapada-outp-msg').html(data);

					// Close the approve modal after successful approval
					const approveModal = bootstrap.Modal.getInstance(document.getElementById('confirmApproveModal'));
					approveModal.hide();
				},
				error: function(xhr, status, error) {
					alert('Error: ' + error);
				}
			});
	
			// Close the modal
			const approveModal = bootstrap.Modal.getInstance(document.getElementById('confirmApproveModal'));
			approveModal.hide();
		});
	};

	this.__disapprove_certb = function() {
		const disapproveBtn = document.getElementById('btn_disapprove');
		console.log('Disapprove Button Clicked');
		const confirmDisapproveBtn = document.getElementById('confirmDisapproveBtn');
	
		let recid = null; // store recid for use after confirmation
	
		disapproveBtn.addEventListener('click', function () {
			recid = document.getElementById("recid");
			approver = document.getElementById("disapproved_by");
			remarks = document.getElementById("disapproved_remarks");
	
			// Show the modal
			const disapproveModal = new bootstrap.Modal(document.getElementById('confirmDisapproveModal'));
			disapproveModal.show();
		});
	
		confirmDisapproveBtn.addEventListener('click', function () {
			if (!recid) return;
	
			const mparam = {
				recid: recid.value,
				approver: approver.value,
				remarks: remarks.value,
				meaction: 'MAIN-DISAPPROVE-B'
			};
	
			jQuery.ajax({
				type: "POST",
				url: mesiteurl + 'mylddapada',
				context: document.body,
				data: mparam,
				global: false,
				cache: false,
				success: function(data) {
					jQuery('.mylddapada-outp-msg').html(data);

					// Close the approve modal after successful approval
					const approveModal = bootstrap.Modal.getInstance(document.getElementById('confirmDisapproveModal'));
					approveModal.hide();
				},
				error: function(xhr, status, error) {
					alert('Error: ' + error);
				}
			});
	
			// Close the modal
			const disapproveModal = bootstrap.Modal.getInstance(document.getElementById('confirmDisapproveBtn'));
			disapproveModal.hide();
		});
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

  	$(document).on('change', '.selUacs', function() {
        var selectedCode = $(this).find('option:selected').data('uacs');
        $(this).closest('tr').find('.uacs').val(selectedCode);
    });

    $(document).on('change', '.selProject', function() {
        var selectedRC = $(this).find('option:selected').data('rc');
        var selectedMFO = $(this).find('option:selected').data('mfo');
        $(this).closest('tr').find('.responsibility_code').val(selectedRC);
        $(this).closest('tr').find('.mfopaps_code').val(selectedMFO);
    });

    $(document).on('change', '#payee_name', function() {
        var selected = $(this).find('option:selected');

        // Extract data from selected option
        var office = selected.data('office') || '';
        var address = selected.data('address') || '';
        
        // Set the values into inputs
        $('#payee_office').val(office);
        $('#payee_address').val(address);

    });

	$(document).on('change', '#certified_a', function() {
        var selected = $(this).find('option:selected');

        // Extract data from selected option
        var position = selected.data('position') || '';
        
        // Set the values into inputs
        $('#position_a').val(position);

    });

	$(document).on('change', '#certified_b', function() {
        var selected = $(this).find('option:selected');

        // Extract data from selected option
        var position = selected.data('position') || '';
        
        // Set the values into inputs
        $('#position_b').val(position);

    });

    // $(document).on('change', '#program_title', function() {
    //     var selected = $(this).find('option:selected');

    //     // Extract data from selected option
    //     var projectitle = selected.data('projectitle') || '';
    //     var rescode = selected.data('rescode') || '';
    //     var fundcode = selected.data('fundcode') || '';
        
    //     // Set the values into inputs
    //     $('#project_title').val(projectitle);
    //     $('#fund_cluster_code').val(rescode);
    //     $('#responsibility_code').val(fundcode);

    // });
}; //end main
