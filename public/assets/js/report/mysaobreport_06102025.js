var __mysys_saob_rpt_ent = new __mysys_saob_rpt_ent();
function __mysys_saob_rpt_ent() {  
	const mesiteurl = $('#__siteurl').attr('data-mesiteurl');

	this.my_add_budget_line = function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
				// Enable the delete icon for the new row
			jQuery(clonedRow).find('.text-danger').removeClass('text-muted').off('click').on('click', function () {
				jQuery(this).closest('tr').remove();
			});

			jQuery(clonedRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('select').eq(0).val('');
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
	
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_line_items tbody').append(clonedRow);

			this.__direct_ps_totals();
	
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
			jQuery(templateRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=number]').each(function (i) {
				jQuery(this).val('').attr('id', 'col' + (2 + i) + mid).attr('data-dtid', '');
			});

			// Insert above the clicked row
			var currentRow = jQuery(elem).closest('tr');
			templateRow.css('display', '').attr('id', 'tr_rec_' + mid);
			templateRow.insertAfter(currentRow);

			// Optional: focus the first input field
			jQuery(templateRow).find('input[type=text]').eq(0).focus();

			// Recalculate if needed
			this.__direct_ps_totals();

		} catch (err) {
			alert('Error: ' + err.message);
		}
	}

	// this.my_add_budget_indirect_line = function () {
	// 	try {
	// 		// Get the total number of rows, excluding the footer row
	// 		var rowCount = jQuery('#budget_indirect_line_items tbody tr').length;
	// 		var mid = generateRandomID(10) + (rowCount + 1);
	
	// 		// Clone the last data row (not the footer)
	// 		var clonedRow = jQuery('#budget_indirect_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
	// 		jQuery(clonedRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
	// 		jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
	// 		jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
	// 		jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
	// 		jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
	// 		// Now reset only the debit and credit fields (input[type=number])
			
	// 		jQuery(clonedRow).find('select').eq(0).val('');
	// 		jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
	// 		jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
	// 		jQuery(clonedRow).find('input[type=number]').eq(1).val('');
	// 		jQuery(clonedRow).find('input[type=number]').eq(2).val('');

	
	// 		// Insert the cloned row before the last row (footer row)
	// 		jQuery('#budget_indirect_line_items tbody').append(clonedRow);
	
	// 		this.__indirect_ps_totals();

	// 		// Make the new row visible
	// 		jQuery(clonedRow).css({ 'display': '' });
	
	// 		// Set the ID for the new row
	// 		jQuery(clonedRow).attr('id', 'tr_rec_' + mid);
	
	// 		// Focus on the first input field of the cloned row
	// 		var xobjArtItem = jQuery(clonedRow).find('input[type=text]').eq(0).attr('id');
	// 		jQuery('#' + xobjArtItem).focus();
	
	// 	} catch (err) {
	// 		var mtxt = 'There was an error on this page.\\n';
	// 		mtxt += 'Error description: ' + err.message;
	// 		mtxt += '\\nClick OK to continue.';
	// 		alert(mtxt);
	// 		return false;
	// 	}
	// }

	// this.my_add_budget_indirect_line_above = function (elem) {
	// 	try {
	// 		var rowCount = jQuery('#budget_indirect_line_items tbody tr').length;
	// 		var mid = generateRandomID(10) + (rowCount + 1);

	// 		// Clone the hidden template row
	// 		var templateRow = jQuery('#budget_indirect_line_items tbody tr:hidden:first').clone();

	// 		// Set new IDs and clear values
	// 		jQuery(templateRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
	// 		jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
	// 		jQuery(templateRow).find('input[type=number]').each(function (i) {
	// 			jQuery(this).val('').attr('id', 'col' + (2 + i) + mid).attr('data-dtid', '');
	// 		});

	// 		// Insert above the clicked row
	// 		var currentRow = jQuery(elem).closest('tr');
	// 		templateRow.css('display', '').attr('id', 'tr_rec_' + mid);
	// 		templateRow.insertAfter(currentRow);

	// 		// Optional: focus the first input field
	// 		jQuery(templateRow).find('input[type=text]').eq(0).focus();

	// 		// Recalculate if needed
	// 		this.__indirect_ps_totals();

	// 	} catch (err) {
	// 		alert('Error: ' + err.message);
	// 	}
	// }

	this.my_add_budget_mooe_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_mooe_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_mooe_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('select').eq(0).val('');
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
	
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_mooe_line_items tbody').append(clonedRow);

			this.__direct_mooe_totals();
	
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
			jQuery(templateRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=number]').each(function (i) {
				jQuery(this).val('').attr('id', 'col' + (2 + i) + mid).attr('data-dtid', '');
			});

			// Insert above the clicked row
			var currentRow = jQuery(elem).closest('tr');
			templateRow.css('display', '').attr('id', 'tr_rec_' + mid);
			templateRow.insertAfter(currentRow);

			// Optional: focus the first input field
			jQuery(templateRow).find('input[type=text]').eq(0).focus();

			// Recalculate if needed
			this.__direct_mooe_totals();

		} catch (err) {
			alert('Error: ' + err.message);
		}
	}

	// this.my_add_budget_indirect_mooe_line= function () {
	// 	try {
	// 		// Get the total number of rows, excluding the footer row
	// 		var rowCount = jQuery('#budget_mooe_indirect_line_items tbody tr').length;
	// 		var mid = generateRandomID(10) + (rowCount + 1);
	
	// 		// Clone the last data row (not the footer)
	// 		var clonedRow = jQuery('#budget_mooe_indirect_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
	// 		jQuery(clonedRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
	// 		jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
	// 		jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
	// 		jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
	// 		jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
	// 		// Now reset only the debit and credit fields (input[type=number])
			
	// 		jQuery(clonedRow).find('select').eq(0).val('');
	// 		jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
	// 		jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
	// 		jQuery(clonedRow).find('input[type=number]').eq(1).val('');
	// 		jQuery(clonedRow).find('input[type=number]').eq(2).val('');


	
	// 		// Insert the cloned row before the last row (footer row)
	// 		jQuery('#budget_mooe_indirect_line_items tbody').append(clonedRow);

	// 		this.__indirect_mooe_totals();

	// 		// Make the new row visible
	// 		jQuery(clonedRow).css({ 'display': '' });
	
	// 		// Set the ID for the new row
	// 		jQuery(clonedRow).attr('id', 'tr_rec_' + mid);
	
	// 		// Focus on the first input field of the cloned row
	// 		var xobjArtItem = jQuery(clonedRow).find('input[type=text]').eq(0).attr('id');
	// 		jQuery('#' + xobjArtItem).focus();
	
	// 	} catch (err) {
	// 		var mtxt = 'There was an error on this page.\\n';
	// 		mtxt += 'Error description: ' + err.message;
	// 		mtxt += '\\nClick OK to continue.';
	// 		alert(mtxt);
	// 		return false;
	// 	}
	// }

	// this.my_add_budget_indirect_mooe_line_above = function (elem) {
	// 	try {
	// 		var rowCount = jQuery('#budget_mooe_line_items tbody tr').length;
	// 		var mid = generateRandomID(10) + (rowCount + 1);

	// 		// Clone the hidden template row
	// 		var templateRow = jQuery('#budget_mooe_line_items tbody tr:hidden:first').clone();

	// 		// Set new IDs and clear values
	// 		jQuery(templateRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
	// 		jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
	// 		jQuery(templateRow).find('input[type=number]').each(function (i) {
	// 			jQuery(this).val('').attr('id', 'col' + (2 + i) + mid).attr('data-dtid', '');
	// 		});

	// 		// Insert above the clicked row
	// 		var currentRow = jQuery(elem).closest('tr');
	// 		templateRow.css('display', '').attr('id', 'tr_rec_' + mid);
	// 		templateRow.insertAfter(currentRow);

	// 		// Optional: focus the first input field
	// 		jQuery(templateRow).find('input[type=text]').eq(0).focus();

	// 		// Recalculate if needed
	// 		this.__direct_mooe_totals();

	// 	} catch (err) {
	// 		alert('Error: ' + err.message);
	// 	}
	// }

	this.my_add_budget_co_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_co_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_co_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field

			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
	
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_co_line_items tbody').append(clonedRow);

			this.__direct_co_totals();

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
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=text]').eq(1).val('').attr('id', 'col2' + mid);
			jQuery(templateRow).find('input[type=number]').each(function (i) {
				jQuery(this).val('').attr('id', 'col' + (2 + i) + mid).attr('data-dtid', '');
			});

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

	// this.my_add_budget_indirect_co_line= function () {
	// 	try {
	// 		// Get the total number of rows, excluding the footer row
	// 		var rowCount = jQuery('#budget_indirect_co_line_items tbody tr').length;
	// 		var mid = generateRandomID(10) + (rowCount + 1);
	
	// 		// Clone the last data row (not the footer)
	// 		var clonedRow = jQuery('#budget_indirect_co_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
	// 		jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
	// 		jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for second text field
	// 		jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
	// 		jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
	// 		jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field

	// 		// Now reset only the debit and credit fields (input[type=number])
			
	// 		jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
	// 		jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
	// 		jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
	// 		jQuery(clonedRow).find('input[type=number]').eq(1).val('');
	// 		jQuery(clonedRow).find('input[type=number]').eq(2).val('');

	
	// 		// Insert the cloned row before the last row (footer row)
	// 		jQuery('#budget_indirect_co_line_items tbody').append(clonedRow);

	// 		this.__indirect_co_totals();
	
	// 		// Make the new row visible
	// 		jQuery(clonedRow).css({ 'display': '' });
	
	// 		// Set the ID for the new row
	// 		jQuery(clonedRow).attr('id', 'tr_rec_' + mid);
	
	// 		// Focus on the first input field of the cloned row
	// 		var xobjArtItem = jQuery(clonedRow).find('input[type=text]').eq(0).attr('id');
	// 		jQuery('#' + xobjArtItem).focus();
	
	// 	} catch (err) {
	// 		var mtxt = 'There was an error on this page.\\n';
	// 		mtxt += 'Error description: ' + err.message;
	// 		mtxt += '\\nClick OK to continue.';
	// 		alert(mtxt);
	// 		return false;
	// 	}
	// }

	// this.my_add_budget_indirect_co_line_above = function (elem) {
	// 	try {
	// 		var rowCount = jQuery('#budget_indirect_co_line_items tbody tr').length;
	// 		var mid = generateRandomID(10) + (rowCount + 1);

	// 		// Clone the hidden template row
	// 		var templateRow = jQuery('#budget_indirect_co_line_items tbody tr:hidden:first').clone();

	// 		// Set new IDs and clear values
	// 		jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
	// 		jQuery(templateRow).find('input[type=text]').eq(1).val('').attr('id', 'col2' + mid);
	// 		jQuery(templateRow).find('input[type=number]').each(function (i) {
	// 			jQuery(this).val('').attr('id', 'col' + (2 + i) + mid).attr('data-dtid', '');
	// 		});

	// 		// Insert above the clicked row
	// 		var currentRow = jQuery(elem).closest('tr');
	// 		templateRow.css('display', '').attr('id', 'tr_rec_' + mid);
	// 		templateRow.insertAfter(currentRow);

	// 		// Optional: focus the first input field
	// 		jQuery(templateRow).find('input[type=text]').eq(0).focus();

	// 		// Recalculate if needed
	// 		this.__indirect_co_totals();

	// 	} catch (err) {
	// 		alert('Error: ' + err.message);
	// 	}
	// }

	function generateRandomID(length) {
		const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		let result = '';
		for (let i = 0; i < length; i++) {
			result += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		return result;
	}
	
	this.__saob_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.mysaob-validation')
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
					var trxno = document.getElementById("trxno");
					var program_title = document.getElementById("program_title");
					var department = document.getElementById("department");
					var agency = document.getElementById("agency");
					var current_year = document.getElementById("current_year");
					
					//new checkbox fields
					let is_jan = document.getElementById("is_jan").checked ? 1 : 0;
					let is_feb = document.getElementById("is_feb").checked ? 1 : 0;
					let is_mar = document.getElementById("is_mar").checked ? 1 : 0;
					let is_apr = document.getElementById("is_apr").checked ? 1 : 0;
					let is_may = document.getElementById("is_may").checked ? 1 : 0;
					let is_jun = document.getElementById("is_jun").checked ? 1 : 0;
					let is_jul = document.getElementById("is_jul").checked ? 1 : 0;
					let is_aug = document.getElementById("is_aug").checked ? 1 : 0;
					let is_sep = document.getElementById("is_sep").checked ? 1 : 0;
					let is_oct = document.getElementById("is_oct").checked ? 1 : 0;
					let is_nov = document.getElementById("is_nov").checked ? 1 : 0;
					let is_dec = document.getElementById("is_dec").checked ? 1 : 0;

					var total_approved_combined = document.getElementById("total_approved_combined");
					var total_proposed_combined = document.getElementById("total_proposed_combined");

					// Prepare PS data DIRECT --
					var rowcount1 = jQuery('.budgetdata-list tr').length;
					var budgetdtdata = [];
					var psdata = '';
	
					for (var aa = 2; aa < rowcount1; aa++) {
						var clonedRow = jQuery('.budgetdata-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var revision = clonedRow.find('input[type=number]').eq(1).val();  
						var proposed_revision = clonedRow.find('input[type=number]').eq(2).val();  
						psdata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + revision + 'x|x' + proposed_revision;
						budgetdtdata.push(psdata);
					}

					// Prepare PS data INDIRECT --
					var rowcount11 = jQuery('.budgetdata-indirect-list tr').length;
					var budgetdtindirectdata = [];
					var psindirectdata = '';
	
					for (var aa = 2; aa < rowcount11; aa++) {
						var clonedRow = jQuery('.budgetdata-indirect-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var revision = clonedRow.find('input[type=number]').eq(1).val();  
						var proposed_revision = clonedRow.find('input[type=number]').eq(2).val();  
						
						psindirectdata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + revision + 'x|x' + proposed_revision;
						budgetdtindirectdata.push(psindirectdata);
					}

					// Prepare MOEE data
					var rowcount2 = jQuery('.budgetmooedata-list tr').length;
					var budgetmooedtdata = [];
					var mooedata = '';
	
					for (var aa = 2; aa < rowcount2; aa++) {
						var clonedRow = jQuery('.budgetmooedata-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var revision = clonedRow.find('input[type=number]').eq(1).val();  
						var proposed_revision = clonedRow.find('input[type=number]').eq(2).val();
						
						mooedata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + revision + 'x|x' + proposed_revision;
						budgetmooedtdata.push(mooedata);
					}

					// Prepare MOEE data
					var rowcount22 = jQuery('.budgetmooedata-indirect-list tr').length;
					var budgetmooeindirectdtdata = [];
					var mooeindirectdata = '';
	
					for (var aa = 2; aa < rowcount22; aa++) {
						var clonedRow = jQuery('.budgetmooedata-indirect-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var revision = clonedRow.find('input[type=number]').eq(1).val();  
						var proposed_revision = clonedRow.find('input[type=number]').eq(2).val();
						
						mooeindirectdata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + revision + 'x|x' + proposed_revision;
						budgetmooeindirectdtdata.push(mooeindirectdata);
					}

					// Prepare CO data
					var rowcount3 = jQuery('.budgetcodata-list tr').length;
					var budgetcodtdata = [];
					var codata = '';
	
					for (var aa = 2; aa < rowcount3; aa++) {
						var clonedRow = jQuery('.budgetcodata-list tr:eq(' + aa + ')'); 
						var expense_item = clonedRow.find('input[type=text]').eq(0).val();
						var uacs = clonedRow.find('input[type=text]').eq(1).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var revision = clonedRow.find('input[type=number]').eq(1).val();  
						var proposed_revision = clonedRow.find('input[type=number]').eq(2).val();  
						
						codata = expense_item + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + revision + 'x|x' + proposed_revision;
						budgetcodtdata.push(codata);
					}

					// Prepare CO data
					var rowcount33 = jQuery('.budgetcodata-indirect-list tr').length;
					var budgetindirectcodtdata = [];
					var coindirectdata = '';
	
					for (var aa = 2; aa < rowcount33; aa++) {
						var clonedRow = jQuery('.budgetcodata-indirect-list tr:eq(' + aa + ')'); 
						var expense_item = clonedRow.find('input[type=text]').eq(0).val();
						var uacs = clonedRow.find('input[type=text]').eq(1).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var revision = clonedRow.find('input[type=number]').eq(1).val();  
						var proposed_revision = clonedRow.find('input[type=number]').eq(2).val();  		
						
						coindirectdata = expense_item + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + revision + 'x|x' + proposed_revision;
						budgetindirectcodtdata.push(coindirectdata);
					}

					var mparam = { 
						recid: recid.value,
						trxno: trxno.value,
						program_title: program_title.value,
						department: department.value,
						agency: agency.value,
						current_year: current_year.value,
						budgetdtdata: budgetdtdata,
						budgetdtindirectdata: budgetdtindirectdata,
						budgetmooedtdata: budgetmooedtdata,
						budgetmooeindirectdtdata: budgetmooeindirectdtdata,
						budgetcodtdata: budgetcodtdata,
						budgetindirectcodtdata: budgetindirectcodtdata,
						is_jan: is_jan,
						is_feb: is_feb,
						is_mar: is_mar,
						is_apr: is_apr,
						is_may: is_may,
						is_jun: is_jun,
						is_jul: is_jul,
						is_aug: is_aug,
						is_sep: is_sep,
						is_oct: is_oct,
						is_nov: is_nov,
						is_dec: is_dec,
						total_approved_combined:total_approved_combined.value,
						total_proposed_combined: total_proposed_combined.value,
						meaction: 'MAIN-SAVE'
					}

					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'mysaobrpt',
						context: document.body,
						data: eval(mparam),
						global: false,
						cache: false,
						success: function(data) {
							jQuery('.mysaob-outp-msg').html(data);
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

	this.__approve_budget = function() {
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
				meaction: 'MAIN-APPROVE'
			};
	
			jQuery.ajax({
				type: "POST",
				url: mesiteurl + 'mybudgetallotment',
				context: document.body,
				data: eval(mparam),
				global: false,
				cache: false,
				success: function(data) {
					jQuery('.mybudgetallotment-outp-msg').html(data);

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

	this.__disapprove_budget = function() {
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
				meaction: 'MAIN-DISAPPROVE'
			};
	
			jQuery.ajax({
				type: "POST",
				url: mesiteurl + 'mybudgetallotment',
				context: document.body,
				data: mparam,
				global: false,
				cache: false,
				success: function(data) {
					jQuery('.mybudgetallotment-outp-msg').html(data);

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

	$('#uploadForm').on('submit', function(e) {
		e.preventDefault(); // Prevent default form submission

		// Create a FormData object to hold the form data
		var formData = new FormData(this);

		$.ajax({
			url: mesiteurl + 'mybudgetallotment',
			method: 'POST',
			data: formData,
			contentType: false, // Don't set content type for FormData
			processData: false, // Prevent jQuery from processing the data
			success: function(data) {
				// Insert the response message (success or error) into the placeholder div
				$('.mybudgetallotment-outp-msg').html(data);
				return false;
			},
			error: function(xhr, status, error) {
				// Handle errors (if any)
				$('.mybudgetallotment-outp-msg').html('<div class="alert alert-danger">An error occurred while uploading the file.</div>');
			}
		});
	});

	//PS - TOTAL PER LINE - DIRECT
	this.__direct_ps_totals = function () {
        jQuery('.budgetdata-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var revision = parseFloat(row.find('.revision').val()) || 0;

            var total = approved + revision;

            row.find('.proposed_revision').val(total.toFixed(2));
        });
    };
	//PS - TOTAL PER LINE - INDIRECT
	this.__indirect_ps_totals = function () {
        jQuery('.budgetdata-indirect-list tr').each(function () {
            var row = jQuery(this);

  			var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var revision = parseFloat(row.find('.revision').val()) || 0;

            var total = approved + revision;

            row.find('.proposed_revision').val(total.toFixed(2));
        });
    };

	//MOOE - TOTAL PER LINE - DIRECT
	this.__direct_mooe_totals = function () {
        jQuery('.budgetmooedata-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var revision = parseFloat(row.find('.revision').val()) || 0;

            var total = approved + revision;

            row.find('.proposed_revision').val(total.toFixed(2));
        });
    };

	//MOOE - TOTAL PER LINE - INDIRECT
	this.__indirect_mooe_totals = function () {
        jQuery('.budgetmooedata-indirect-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var revision = parseFloat(row.find('.revision').val()) || 0;

            var total = approved + revision;

            row.find('.proposed_revision').val(total.toFixed(2));
        });
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

	//CO - TOTAL PER LINE - INDIRECT
	this.__indirect_co_totals = function () {
        jQuery('.budgetcodata-indirect-list tr').each(function () {
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

		// PS INDIRECT COST TABLE
		jQuery('.budgetdata-indirect-list tr').each(function () {
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

		// MOOE INDIRECT COST TABLE
		jQuery('.budgetmooedata-indirect-list tr').each(function () {
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

		// CO INDIRECT COST TABLE
		jQuery('.budgetcodata-indirect-list tr').each(function () {
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

        // Toggle all based on the checkbox
        $('#is_jan').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_feb').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_mar').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_apr').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_may').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_jun').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_jul').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_aug').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_sep').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_oct').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_nov').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        $('#is_dec').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.revision').prop('disabled', !isChecked);
        });
        
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

}; //end main
