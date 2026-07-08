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
			jQuery(clonedRow).find('select').eq(1).val('').attr('id', 'col7' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(3).attr('id', 'col6' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('select').eq(0).val('');
			jQuery(clonedRow).find('select').eq(1).val('');
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('0.00').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('0.00');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('0.00');
			jQuery(clonedRow).find('input[type=number]').eq(3).val('0.00');
	
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

			var rowCount = jQuery('#budget_line_items tbody tr:visible').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// clone current row structure
			var templateRow = jQuery(elem).closest('tr').clone();

			// enable delete
			templateRow.find('.text-danger')
				.removeClass('text-muted')
				.off('click')
				.on('click', function () {
					jQuery(this).closest('tr').remove();
					window.__direct_ps_totals();
				});

			// reset selects
			templateRow.find('select').eq(0).val('').attr('id', 'col4' + mid);
			templateRow.find('select').eq(1).val('').attr('id', 'col7' + mid);

			// reset text
			templateRow.find('input[type=text]')
				.eq(0)
				.val('')
				.attr('id', 'col2' + mid);

			// reset numbers
			templateRow.find('input[type=number]').each(function (i) {
				jQuery(this)
					.val('0.00')
					.attr('id', 'col' + (3 + i) + mid)
					.attr('data-dtid', '');
			});

			templateRow.attr('id', 'tr_rec_' + mid);

			var currentRow = jQuery(elem).closest('tr');

			// insert ABOVE
			templateRow.insertAfter(currentRow);

			// focus first field
			templateRow.find('input[type=text]').eq(0).focus();

			// recompute totals
			this.__direct_ps_totals();

		} catch (err) {
			alert('Error: ' + err.message);
		}
	};

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
			jQuery(clonedRow).find('select').eq(1).val('').attr('id', 'col7' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col6' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('select').eq(0).val('');
			jQuery(clonedRow).find('select').eq(1).val('');
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('0.00').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('0.00');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('0.00');
			jQuery(clonedRow).find('input[type=number]').eq(3).val('0.00');
	
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
			jQuery(templateRow).find('select').eq(1).val('').attr('id', 'col7' + mid);
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=number]').each(function (i) {
				jQuery(this).val('0.00').attr('id', 'col' + (3 + i) + mid).attr('data-dtid', '');
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
	
			jQuery(clonedRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
			jQuery(clonedRow).find('select').eq(1).val('').attr('id', 'col7' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col6' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('select').eq(0).val('');
			jQuery(clonedRow).find('select').eq(1).val('');
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
			jQuery(clonedRow).find('input[type=number]').eq(3).val('');
	
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

			jQuery(templateRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
			jQuery(templateRow).find('select').eq(1).val('').attr('id', 'col7' + mid);
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=number]').each(function (i) {
				jQuery(this).val('').attr('id', 'col' + (3 + i) + mid).attr('data-dtid', '');
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
					var project_title = document.getElementById("project_title");
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
						var object_code = clonedRow.find('select.selObject').val();
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var todate_realignment = clonedRow.find('input[type=number]').eq(1).val();  
						var proposed_realignment = clonedRow.find('input[type=number]').eq(2).val();  
						var revised_allotment = clonedRow.find('input[type=number]').eq(3).val();  
						var january_revision  = clonedRow.find('input[type=hidden]').eq(0).val();
						var february_revision  = clonedRow.find('input[type=hidden]').eq(1).val();
						var march_revision  = clonedRow.find('input[type=hidden]').eq(2).val();
						var april_revision  = clonedRow.find('input[type=hidden]').eq(3).val();
						var may_revision  = clonedRow.find('input[type=hidden]').eq(4).val();
						var june_revision  = clonedRow.find('input[type=hidden]').eq(5).val();
						var july_revision  = clonedRow.find('input[type=hidden]').eq(6).val();
						var august_revision  = clonedRow.find('input[type=hidden]').eq(7).val();
						var september_revision  = clonedRow.find('input[type=hidden]').eq(8).val();
						var october_revision  = clonedRow.find('input[type=hidden]').eq(9).val();
						var november_revision  = clonedRow.find('input[type=hidden]').eq(10).val();
						var december_revision  = clonedRow.find('input[type=hidden]').eq(11).val();

						psdata = 
							object_code + 'x|x' + 
							particulars + 'x|x' + 
							uacs + 'x|x' + 
							approved_budget + 'x|x' + 
							todate_realignment + 'x|x' + 
							proposed_realignment + 'x|x' + 
							revised_allotment + 'x|x' +
							january_revision + 'x|x' +
							february_revision + 'x|x' +
							march_revision + 'x|x' +
							april_revision + 'x|x' +
							may_revision + 'x|x' +
							june_revision + 'x|x' +
							july_revision + 'x|x' +
							august_revision + 'x|x' +
							september_revision + 'x|x' +
							october_revision + 'x|x' +
							november_revision + 'x|x' +
							december_revision;

						budgetdtdata.push(psdata);
					}

					// Prepare MOEE data
					var rowcount2 = jQuery('.budgetmooedata-list tr').length;
					var budgetmooedtdata = [];
					var mooedata = '';
	
					for (var aa = 2; aa < rowcount2; aa++) {
						var clonedRow = jQuery('.budgetmooedata-list tr:eq(' + aa + ')'); 
						var object_code = clonedRow.find('select.selObject').val();
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var todate_realignment = clonedRow.find('input[type=number]').eq(1).val();  
						var proposed_realignment = clonedRow.find('input[type=number]').eq(2).val();  
						var revised_allotment = clonedRow.find('input[type=number]').eq(3).val();  
						var january_revision  = clonedRow.find('input[type=hidden]').eq(0).val();
						var february_revision  = clonedRow.find('input[type=hidden]').eq(1).val();
						var march_revision  = clonedRow.find('input[type=hidden]').eq(2).val();
						var april_revision  = clonedRow.find('input[type=hidden]').eq(3).val();
						var may_revision  = clonedRow.find('input[type=hidden]').eq(4).val();
						var june_revision  = clonedRow.find('input[type=hidden]').eq(5).val();
						var july_revision  = clonedRow.find('input[type=hidden]').eq(6).val();
						var august_revision  = clonedRow.find('input[type=hidden]').eq(7).val();
						var september_revision  = clonedRow.find('input[type=hidden]').eq(8).val();
						var october_revision  = clonedRow.find('input[type=hidden]').eq(9).val();
						var november_revision  = clonedRow.find('input[type=hidden]').eq(10).val();
						var december_revision  = clonedRow.find('input[type=hidden]').eq(11).val();
						
						mooedata = 
							object_code + 'x|x' + 
							particulars + 'x|x' + 
							uacs + 'x|x' + 
							approved_budget + 'x|x' + 
							todate_realignment + 'x|x' + 
							proposed_realignment + 'x|x' + 
							revised_allotment + 'x|x' +
							january_revision + 'x|x' +
							february_revision + 'x|x' +
							march_revision + 'x|x' +
							april_revision + 'x|x' +
							may_revision + 'x|x' +
							june_revision + 'x|x' +
							july_revision + 'x|x' +
							august_revision + 'x|x' +
							september_revision + 'x|x' +
							october_revision + 'x|x' +
							november_revision + 'x|x' +
							december_revision;
						budgetmooedtdata.push(mooedata);
					}

					// Prepare CO data
					var rowcount3 = jQuery('.budgetcodata-list tr').length;
					var budgetcodtdata = [];
					var codata = '';
	
					for (var aa = 2; aa < rowcount3; aa++) {
						var clonedRow = jQuery('.budgetcodata-list tr:eq(' + aa + ')'); 
						var object_code = clonedRow.find('select.selObject').val();
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var todate_realignment = clonedRow.find('input[type=number]').eq(1).val();  
						var proposed_realignment = clonedRow.find('input[type=number]').eq(2).val();  
						var revised_allotment = clonedRow.find('input[type=number]').eq(3).val();  
						var january_revision  = clonedRow.find('input[type=hidden]').eq(0).val();
						var february_revision  = clonedRow.find('input[type=hidden]').eq(1).val();
						var march_revision  = clonedRow.find('input[type=hidden]').eq(2).val();
						var april_revision  = clonedRow.find('input[type=hidden]').eq(3).val();
						var may_revision  = clonedRow.find('input[type=hidden]').eq(4).val();
						var june_revision  = clonedRow.find('input[type=hidden]').eq(5).val();
						var july_revision  = clonedRow.find('input[type=hidden]').eq(6).val();
						var august_revision  = clonedRow.find('input[type=hidden]').eq(7).val();
						var september_revision  = clonedRow.find('input[type=hidden]').eq(8).val();
						var october_revision  = clonedRow.find('input[type=hidden]').eq(9).val();
						var november_revision  = clonedRow.find('input[type=hidden]').eq(10).val();
						var december_revision  = clonedRow.find('input[type=hidden]').eq(11).val();
						
						codata = 
							object_code + 'x|x' + 
							particulars + 'x|x' + 
							uacs + 'x|x' + 
							approved_budget + 'x|x' + 
							todate_realignment + 'x|x' + 
							proposed_realignment + 'x|x' + 
							revised_allotment + 'x|x' +
							january_revision + 'x|x' +
							february_revision + 'x|x' +
							march_revision + 'x|x' +
							april_revision + 'x|x' +
							may_revision + 'x|x' +
							june_revision + 'x|x' +
							july_revision + 'x|x' +
							august_revision + 'x|x' +
							september_revision + 'x|x' +
							october_revision + 'x|x' +
							november_revision + 'x|x' +
							december_revision;
						budgetcodtdata.push(codata);
					}

					var mparam = { 
						recid: recid.value,
						trxno: trxno.value,
						program_title: program_title.value,
						project_title: project_title.value,
						department: department.value,
						agency: agency.value,
						current_year: current_year.value,
						budgetdtdata: budgetdtdata,
						budgetmooedtdata: budgetmooedtdata,
						budgetcodtdata: budgetcodtdata,
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

		jQuery('#budget_line_items tbody tr:visible').each(function () {

			var row = jQuery(this);

			var approved = parseFloat(row.find('.approved_budget').val()) || 0;
			var todate = parseFloat(row.find('.todate_realignment').val()) || 0;
			var proposed = parseFloat(row.find('.proposed_realignment').val()) || 0;

			var total = approved + todate + proposed;

			row.find('.revised_allotment').val(total.toFixed(2));

		});

	};

	//MOOE - TOTAL PER LINE - DIRECT
	this.__direct_mooe_totals = function () {
        jQuery('.budgetmooedata-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var todate = parseFloat(row.find('.todate_realignment').val()) || 0;
			var proposed = parseFloat(row.find('.proposed_realignment').val()) || 0;

            var total = approved + todate + proposed;

            row.find('.revised_allotment').val(total.toFixed(2));
        });
    };

	//CO - TOTAL PER LINE - DIRECT
	this.__direct_co_totals = function () {
        jQuery('.budgetcodata-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var todate = parseFloat(row.find('.todate_realignment').val()) || 0;
			var proposed = parseFloat(row.find('.proposed_realignment').val()) || 0;

            var total = approved + todate + proposed;

            row.find('.revised_allotment').val(total.toFixed(2));
        });
    };

	this.__combined_totals = function () {
		let totalApprovedCombined = 0;
		let totalProposedCombined = 0;
		let recid = jQuery("#recid").val(); 
		let is_jan = jQuery("#is_jan_val").val(); 
		// PS DIRECT COST TABLE
		jQuery('.budgetdata-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let todate = parseFloat(row.find('.todate_realignment').val()) || 0;
			let proposed = parseFloat(row.find('.proposed_realignment').val()) || 0;
			let total = approved + todate + proposed;

			row.find('.revised_allotment').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;


		});

		// MOOE DIRECT COST TABLE
		jQuery('.budgetmooedata-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let todate = parseFloat(row.find('.todate_realignment').val()) || 0;
			let proposed = parseFloat(row.find('.proposed_realignment').val()) || 0;
			let total = approved + todate + proposed;

			row.find('.revised_allotment').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;

		});

		// CO DIRECT COST TABLE
		jQuery('.budgetcodata-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let todate = parseFloat(row.find('.todate_realignment').val()) || 0;
			let proposed = parseFloat(row.find('.proposed_realignment').val()) || 0;
			let total = approved + todate + proposed;

			row.find('.revised_allotment').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;
		});

		// SET VALUES TO INPUT FIELDS
		// Format with commas + 2 decimals
		$('#total_approved_combined').val(
		Number(totalApprovedCombined || 0).toLocaleString('en-US', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		})
		);

		$('#total_proposed_combined').val(
		Number(totalProposedCombined || 0).toLocaleString('en-US', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		})
		);



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

	function monthToDateRange(month) {
		const [year, m] = month.split('-');
		const start = `${year}-${m}-01`;
		const end   = new Date(year, m, 0).toISOString().slice(0, 10);
		return { start, end };
	}

	this.__saob_export_csv_range  = function(pdfUrl) {

		const from = document.getElementById("date_from").value;
		const to   = document.getElementById("date_to").value;

		if (!from || !to) {
			toastr.error('Please select date range.', 'Oops!');
			return;
		}

		const fromDate = monthToDateRange(from);
		const toDate   = monthToDateRange(to);

		let url = new URL(pdfUrl, window.location.origin);
		url.searchParams.set('date_from', fromDate.start);
		url.searchParams.set('date_to', toDate.end);

		document.getElementById("pdfFrame").src = url.toString();
	};

	this.__saob_export_csv_month = function(urlPath) {

		const monthInput = document.getElementById("month_year").value;
		const monthly_program_title = document.getElementById("monthly_program_title").value;

		if (!monthInput) {
			toastr.error('Please select month and year.', 'Oops!');
			return;
		}

		// monthInput format: YYYY-MM
		const [year, month] = monthInput.split('-');

		// First day
		const startDate = `${year}-${month}-01`;

		// Last day (auto compute)
		const lastDay = new Date(year, month, 0).getDate();
		const endDate = `${year}-${month}-${lastDay}`;

		let url = new URL(urlPath, window.location.origin);
		url.searchParams.set('date_from', startDate);
		url.searchParams.set('date_to', endDate);
		url.searchParams.set('monthly_program_title', monthly_program_title);

		window.location.href = url.toString();
	};

	this.__saob_export_csv = function(urlPath) {
		
		const month = document.getElementById("month").value;
		const year = document.getElementById("year").value;

		if (!month || !year) {
			toastr.error('Please select month and year.', 'Oops!');
			return;
		}

		let url = new URL(urlPath, window.location.origin);
		url.searchParams.set('month', month);
		url.searchParams.set('year', year);

		window.location.href = url.toString();
	};

	this.__printSavings = function(baseUrl) {

		const monthInput = document.getElementById("month_year").value;
		const monthly_program_title = document.getElementById("monthly_program_title").value;

		// Validate Program Title
		if (!monthly_program_title) {
			toastr.error('Please select Program Title first.', 'Oops!');
			return;
		}

		// Validate Month
		if (!monthInput) {
			toastr.error('Please select month and year.', 'Oops!');
			return;
		}

		// Extract year from month input (format: YYYY-MM)
		const year = monthInput.split("-")[0];

		// Build URL with parameters
		const pdfUrl = baseUrl + 
			'?meaction=SAVINGS-PRINT' +
			'&program_title=' + encodeURIComponent(monthly_program_title) +
			'&current_year=' + encodeURIComponent(year) +
			'&month=' + encodeURIComponent(monthInput);
		
		console.log(year);
		console.log(monthInput);

		// Show PDF in modal
		this.__showPdfInModal(pdfUrl);
	};


	this.__showPdfInModal = function(pdfUrl) {
		var pdfFrame = document.getElementById("pdfSavings");
		var pdfModal = new bootstrap.Modal(document.getElementById("pdfModalSavings"));

		pdfFrame.src = pdfUrl;
		pdfModal.show();
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

		$('#tbl_savings').DataTable({
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
			$('.approved_budget').prop('disabled', isChecked);
            $('.proposed_realignment').prop('disabled', !isChecked);

        });
        $('#is_feb').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
        });
        $('#is_mar').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
        });
        $('#is_apr').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
        });
        $('#is_may').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
        });
        $('#is_jun').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
        });
        $('#is_jul').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
        });
        $('#is_aug').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
        });
        $('#is_sep').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
        });
        $('#is_oct').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
        });
        $('#is_nov').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
        });
        $('#is_dec').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.proposed_realignment').prop('disabled', !isChecked);
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

	//SAVINGS ENTRY

	this.my_add_savings_line = function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#savings_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#savings_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
				// Enable the delete icon for the new row
			jQuery(clonedRow).find('.text-danger').removeClass('text-muted').off('click').on('click', function () {
				jQuery(this).closest('tr').remove();
			});

			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(2).attr('id', 'col3' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col5' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col6' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(3).attr('id', 'col7' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(4).attr('id', 'col8' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');
			jQuery(clonedRow).find('input[type=text]').eq(2).val('');
			jQuery(clonedRow).find('input[type=number]').eq(0).val('');
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
			jQuery(clonedRow).find('input[type=number]').eq(3).val('');
			jQuery(clonedRow).find('input[type=number]').eq(4).val('');
	
			// Insert the cloned row before the last row (footer row)
			jQuery('#savings_line_items tbody').append(clonedRow);

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

	this.my_add_savings_line_above = function (elem) {
		try {
			var rowCount = jQuery('#savings_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the hidden template row
			var templateRow = jQuery('#savings_line_items tbody tr:hidden:first').clone();

			// Set new IDs and clear values
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=text]').eq(1).val('').attr('id', 'col2' + mid);
			jQuery(templateRow).find('input[type=text]').eq(2).val('').attr('id', 'col3' + mid);
			jQuery(templateRow).find('input[type=number]').each(function (i) {
				jQuery(this).val('').attr('id', 'col' + (5 + i) + mid).attr('data-dtid', '');
			});

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

	this.__saob_savings_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.mysaob-savings-validation')
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

					var project_id = document.getElementById("project_id");
					var sprogram_title = document.getElementById("sprogram_title");
					var savings_date = document.getElementById("savings_date");
					

					// Prepare PS data DIRECT --
					var rowcount1 = jQuery('.savingsdata-list tr').length;
					var savingsdtdata = [];
					var psdata = '';
	
					for (var aa = 2; aa < rowcount1; aa++) {
						var clonedRow = jQuery('.savingsdata-list tr:eq(' + aa + ')'); 
						var project_title = clonedRow.find('input[type=text]').eq(0).val();
						var responsibility_code = clonedRow.find('input[type=text]').eq(1).val();
						var project_leader = clonedRow.find('input[type=text]').eq(2).val();
						var declared_savings = clonedRow.find('input[type=number]').eq(0).val();  
						var other_expenses = clonedRow.find('input[type=number]').eq(1).val();  
						var cna = clonedRow.find('input[type=number]').eq(2).val();  
						var total_obligations = clonedRow.find('input[type=number]').eq(3).val();  
						var dtid = clonedRow.find('input[type=number]').eq(4).val();  
						psdata = project_title + 'x|x' + responsibility_code + 'x|x' + project_leader + 'x|x' + declared_savings + 'x|x' + other_expenses + 'x|x' + cna + 'x|x' + total_obligations + 'x|x' + dtid;
						savingsdtdata.push(psdata);
					}

					
					var mparam = { 
						project_id: project_id.value,
						sprogram_title: sprogram_title.value,
						savings_date: savings_date.value,
						savingsdtdata: savingsdtdata,
						meaction: 'SAVINGS-SAVE'
					}

					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'mysaobrpt',
						context: document.body,
						data: mparam,
						global: false,
						cache: false,
						success: function(data) {
							jQuery('.mysaob-outp-savings-msg').html(data);
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

}; //end main
