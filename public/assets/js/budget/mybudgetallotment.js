var __mysys_budget_allotment_ent = new __mysys_budget_allotment_ent();
function __mysys_budget_allotment_ent() {  
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

			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col3' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(3).attr('id', 'col6' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(4).attr('id', 'col7' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(2).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
			jQuery(clonedRow).find('input[type=number]').eq(3).val('');
			jQuery(clonedRow).find('input[type=number]').eq(4).val('');
	
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
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=text]').eq(1).val('').attr('id', 'col2' + mid);
			jQuery(templateRow).find('input[type=text]').eq(2).val('').attr('id', 'col3' + mid);
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
			this.__direct_ps_totals();

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
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col3' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(3).attr('id', 'col6' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(4).attr('id', 'col7' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(2).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
			jQuery(clonedRow).find('input[type=number]').eq(3).val('');
			jQuery(clonedRow).find('input[type=number]').eq(4).val('');
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_indirect_line_items tbody').append(clonedRow);
	
			this.__indirect_ps_totals();

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
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=text]').eq(1).val('').attr('id', 'col2' + mid);
			jQuery(templateRow).find('input[type=text]').eq(2).val('').attr('id', 'col3' + mid);
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
			this.__indirect_ps_totals();

		} catch (err) {
			alert('Error: ' + err.message);
		}
	}

	this.my_add_budget_mooe_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_mooe_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_mooe_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col3' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(3).attr('id', 'col6' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(4).attr('id', 'col7' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(2).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
			jQuery(clonedRow).find('input[type=number]').eq(3).val('');
			jQuery(clonedRow).find('input[type=number]').eq(4).val('');

	
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
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=text]').eq(1).val('').attr('id', 'col2' + mid);
			jQuery(templateRow).find('input[type=text]').eq(2).val('').attr('id', 'col3' + mid);
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
			this.__direct_mooe_totals();

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
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col3' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(3).attr('id', 'col6' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(4).attr('id', 'col7' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(2).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
			jQuery(clonedRow).find('input[type=number]').eq(3).val('');
			jQuery(clonedRow).find('input[type=number]').eq(4).val('');

	
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_mooe_indirect_line_items tbody').append(clonedRow);

			this.__indirect_mooe_totals();

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

			// Set new IDs and clear values
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=text]').eq(1).val('').attr('id', 'col2' + mid);
			jQuery(templateRow).find('input[type=text]').eq(2).val('').attr('id', 'col3' + mid);
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
			this.__direct_mooe_totals();

		} catch (err) {
			alert('Error: ' + err.message);
		}
	}

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
			jQuery(clonedRow).find('input[type=number]').eq(3).attr('id', 'col6' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(4).attr('id', 'col7' + mid); // ID for date field

			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
			jQuery(clonedRow).find('input[type=number]').eq(3).val('');
			jQuery(clonedRow).find('input[type=number]').eq(4).val('');
	
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

	this.my_add_budget_indirect_co_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_indirect_co_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_indirect_co_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(1).attr('id', 'col4' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(2).attr('id', 'col5' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(3).attr('id', 'col6' + mid); // ID for date field
			jQuery(clonedRow).find('input[type=number]').eq(4).attr('id', 'col7' + mid); // ID for date field

			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(1).val('');
			jQuery(clonedRow).find('input[type=number]').eq(2).val('');
			jQuery(clonedRow).find('input[type=number]').eq(3).val('');
			jQuery(clonedRow).find('input[type=number]').eq(4).val('');

	
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_indirect_co_line_items tbody').append(clonedRow);

			this.__indirect_co_totals();
	
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

			// Set new IDs and clear values
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=text]').eq(1).val('').attr('id', 'col2' + mid);
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
			this.__indirect_co_totals();

		} catch (err) {
			alert('Error: ' + err.message);
		}
	}

	this.my_add_budget_ac_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_ac_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_ac_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col1' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=text]').eq(1).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field

			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
			// Insert the cloned row before the last row (footer row)
			jQuery('#budget_ac_line_items tbody').append(clonedRow);

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

	this.my_add_budget_ac_line_above = function (elem) {
		try {
			var rowCount = jQuery('#budget_ac_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);

			// Clone the hidden template row
			var templateRow = jQuery('#budget_ac_line_items tbody tr:hidden:first').clone();

			// Set new IDs and clear values
			jQuery(templateRow).find('input[type=text]').eq(0).val('').attr('id', 'col1' + mid);
			jQuery(templateRow).find('input[type=text]').eq(1).val('').attr('id', 'col2' + mid);
			jQuery(templateRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field

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
	
	this.__budget_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.mybudgetallotment-validation')
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
					var project_title = document.getElementById("project_title");
					var responsibility_code = document.getElementById("responsibility_code");
					var fund_cluster_code = document.getElementById("fund_cluster_code");
					var division_name = document.getElementById("division_name");
					var project_leader = document.getElementById("project_leader");

					//newly added fields
					var program_title = document.getElementById("program_title");
					var project_duration = document.getElementById("project_duration");
					var duration_from = document.getElementById("duration_from");
					var duration_to = document.getElementById("duration_to");
					var program_leader = document.getElementById("program_leader");
					var monitoring_agency = document.getElementById("monitoring_agency");
					var collaborating_agencies = document.getElementById("collaborating_agencies");
					var implementing_agency = document.getElementById("implementing_agency");
					var funding_agency = document.getElementById("funding_agency");

					//new checkbox fields
					let is_realign1 = document.getElementById("is_realign1").checked ? 1 : 0;
					let is_realign2 = document.getElementById("is_realign2").checked ? 1 : 0;
					let is_realign3 = document.getElementById("is_realign3").checked ? 1 : 0;

					//total of ps,mooe & co 
					var total_approved_combined = document.getElementById("total_approved_combined");
					var total_proposed_combined = document.getElementById("total_proposed_combined");

					var tagging = document.getElementById("tagging");

					var with_extension = document.getElementById("with_extension").checked ? 1 : 0;
					var extended_from = document.getElementById("extended_from");
					var extended_to = document.getElementById("extended_to");
					var lddap_refno = document.getElementById("lddap_refno");

					// Prepare PS data DIRECT --
					var rowcount1 = jQuery('.budgetdata-list tr').length;
					var budgetdtdata = [];
					var psdata = '';
	
					for (var aa = 2; aa < rowcount1; aa++) {
						var clonedRow = jQuery('.budgetdata-list tr:eq(' + aa + ')'); 
						var expense_item = clonedRow.find('input[type=text]').eq(0).val();
						var particulars = clonedRow.find('input[type=text]').eq(1).val();
						var uacs = clonedRow.find('input[type=text]').eq(2).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var r1_approved_budget = clonedRow.find('input[type=number]').eq(1).val();  
						var r2_approved_budget = clonedRow.find('input[type=number]').eq(2).val();  
						var r3_approved_budget = clonedRow.find('input[type=number]').eq(3).val();  
						var proposed_realignment = clonedRow.find('input[type=number]').eq(4).val();  
						psdata = expense_item + 'x|x' + particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + r1_approved_budget + 'x|x' + r2_approved_budget + 'x|x' + r3_approved_budget + 'x|x' + proposed_realignment;
						budgetdtdata.push(psdata);
					}

					// Prepare PS data INDIRECT --
					var rowcount11 = jQuery('.budgetdata-indirect-list tr').length;
					var budgetdtindirectdata = [];
					var psindirectdata = '';
	
					for (var aa = 2; aa < rowcount11; aa++) {
						var clonedRow = jQuery('.budgetdata-indirect-list tr:eq(' + aa + ')'); 
						var expense_item = clonedRow.find('input[type=text]').eq(0).val();
						var particulars = clonedRow.find('input[type=text]').eq(1).val();
						var uacs = clonedRow.find('input[type=text]').eq(2).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var r1_approved_budget = clonedRow.find('input[type=number]').eq(1).val();  
						var r2_approved_budget = clonedRow.find('input[type=number]').eq(2).val();  
						var r3_approved_budget = clonedRow.find('input[type=number]').eq(3).val();  
						var proposed_realignment = clonedRow.find('input[type=number]').eq(4).val();
						
						psindirectdata = expense_item + 'x|x' + particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + r1_approved_budget + 'x|x' + r2_approved_budget + 'x|x' + r3_approved_budget + 'x|x' + proposed_realignment;
						budgetdtindirectdata.push(psindirectdata);
					}

					// Prepare MOEE data
					var rowcount2 = jQuery('.budgetmooedata-list tr').length;
					var budgetmooedtdata = [];
					var mooedata = '';
	
					for (var aa = 2; aa < rowcount2; aa++) {
						var clonedRow = jQuery('.budgetmooedata-list tr:eq(' + aa + ')'); 
						var expense_item = clonedRow.find('input[type=text]').eq(0).val();
						var particulars = clonedRow.find('input[type=text]').eq(1).val();
						var uacs = clonedRow.find('input[type=text]').eq(2).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var r1_approved_budget = clonedRow.find('input[type=number]').eq(1).val();  
						var r2_approved_budget = clonedRow.find('input[type=number]').eq(2).val();  
						var r3_approved_budget = clonedRow.find('input[type=number]').eq(3).val();  
						var proposed_realignment = clonedRow.find('input[type=number]').eq(4).val();
						
						mooedata = expense_item + 'x|x' + particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + r1_approved_budget + 'x|x' + r2_approved_budget + 'x|x' + r3_approved_budget + 'x|x' + proposed_realignment;
						budgetmooedtdata.push(mooedata);
					}

					// Prepare MOEE data
					var rowcount22 = jQuery('.budgetmooedata-indirect-list tr').length;
					var budgetmooeindirectdtdata = [];
					var mooeindirectdata = '';
	
					for (var aa = 2; aa < rowcount22; aa++) {
						var clonedRow = jQuery('.budgetmooedata-indirect-list tr:eq(' + aa + ')'); 
						var expense_item = clonedRow.find('input[type=text]').eq(0).val();
						var particulars = clonedRow.find('input[type=text]').eq(1).val();
						var uacs = clonedRow.find('input[type=text]').eq(2).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var r1_approved_budget = clonedRow.find('input[type=number]').eq(1).val();  
						var r2_approved_budget = clonedRow.find('input[type=number]').eq(2).val();  
						var r3_approved_budget = clonedRow.find('input[type=number]').eq(3).val();  
						var proposed_realignment = clonedRow.find('input[type=number]').eq(4).val();
						
						mooeindirectdata = expense_item + 'x|x' + particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + r1_approved_budget + 'x|x' + r2_approved_budget + 'x|x' + r3_approved_budget + 'x|x' + proposed_realignment;
						budgetmooeindirectdtdata.push(mooeindirectdata);
					}

					// Prepare CO data
					var rowcount3 = jQuery('.budgetcodata-list tr').length;
					var budgetcodtdata = [];
					var codata = '';
	
					for (var aa = 2; aa < rowcount3; aa++) {
						var clonedRow = jQuery('.budgetcodata-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('input[type=text]').eq(0).val();
						var uacs = clonedRow.find('input[type=text]').eq(1).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var r1_approved_budget = clonedRow.find('input[type=number]').eq(1).val();  
						var r2_approved_budget = clonedRow.find('input[type=number]').eq(2).val();  
						var r3_approved_budget = clonedRow.find('input[type=number]').eq(3).val();  
						var proposed_realignment = clonedRow.find('input[type=number]').eq(4).val();  
						
						codata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + r1_approved_budget + 'x|x' + r2_approved_budget + 'x|x' + r3_approved_budget + 'x|x' + proposed_realignment;
						budgetcodtdata.push(codata);
					}

					// Prepare CO data
					var rowcount33 = jQuery('.budgetcodata-indirect-list tr').length;
					var budgetindirectcodtdata = [];
					var coindirectdata = '';
	
					for (var aa = 2; aa < rowcount33; aa++) {
						var clonedRow = jQuery('.budgetcodata-indirect-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('input[type=text]').eq(0).val();
						var uacs = clonedRow.find('input[type=text]').eq(1).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						var r1_approved_budget = clonedRow.find('input[type=number]').eq(1).val();  
						var r2_approved_budget = clonedRow.find('input[type=number]').eq(2).val();  
						var r3_approved_budget = clonedRow.find('input[type=number]').eq(3).val();  
						var proposed_realignment = clonedRow.find('input[type=number]').eq(4).val();  
						
						coindirectdata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid + 'x|x' + r1_approved_budget + 'x|x' + r2_approved_budget + 'x|x' + r3_approved_budget + 'x|x' + proposed_realignment;
						budgetindirectcodtdata.push(coindirectdata);
					}

					var rowcount4 = jQuery('.budgetacdata-list tr').length;
					var budgetacdtdata = [];
					var acdata = '';
	
					for (var aa = 2; aa < rowcount4; aa++) {
						var clonedRow = jQuery('.budgetacdata-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('input[type=text]').eq(0).val();
						var uacs = clonedRow.find('input[type=text]').eq(1).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						
						acdata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid;
						budgetacdtdata.push(acdata);
					}

					var mparam = { 
						recid: recid.value,
						trxno: trxno.value,
						project_title: project_title.value,
						responsibility_code: responsibility_code.value,
						fund_cluster_code: fund_cluster_code.value,
						division_name: division_name.value,
						project_leader: project_leader.value,
						//newly added fields
						program_title: program_title.value,
						project_duration: project_duration.value,
						duration_from: duration_from.value,
						duration_to: duration_to.value,
						program_leader: program_leader.value,
						monitoring_agency: monitoring_agency.value,
						collaborating_agencies: collaborating_agencies.value,
						implementing_agency: implementing_agency.value,
						funding_agency: funding_agency.value,
						tagging: tagging.value,
						budgetdtdata: budgetdtdata,
						budgetdtindirectdata: budgetdtindirectdata,
						budgetmooedtdata: budgetmooedtdata,
						budgetmooeindirectdtdata: budgetmooeindirectdtdata,
						budgetcodtdata: budgetcodtdata,
						budgetindirectcodtdata: budgetindirectcodtdata,
						budgetacdtdata: budgetacdtdata,
						//checkboxes
						is_realign1: is_realign1,
						is_realign2: is_realign2,
						is_realign3: is_realign3,
						//total
						total_approved_combined:total_approved_combined.value,
						total_proposed_combined: total_proposed_combined.value,
						//extended duration
						with_extension: with_extension,
						extended_from:extended_from.value,
						extended_to:extended_to.value,
						lddap_refno:lddap_refno.value,
						meaction: 'MAIN-SAVE'
					}


					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'mybudgetallotment',
						context: document.body,
						data: eval(mparam),
						global: false,
						cache: false,
						success: function(data) {
							jQuery('.mybudgetallotment-outp-msg').html(data);
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
            var r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
            var r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
            var r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

            var total = approved + r1 + r2 + r3;

            row.find('.proposed_realignment').val(total.toFixed(2));
        });
    };
	//PS - TOTAL PER LINE - INDIRECT
	this.__indirect_ps_totals = function () {
        jQuery('.budgetdata-indirect-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
            var r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
            var r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

            var total = approved + r1 + r2 + r3;

            row.find('.proposed_realignment').val(total.toFixed(2));
        });
    };

	//MOOE - TOTAL PER LINE - DIRECT
	this.__direct_mooe_totals = function () {
        jQuery('.budgetmooedata-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
            var r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
            var r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

            var total = approved + r1 + r2 + r3;

            row.find('.proposed_realignment').val(total.toFixed(2));
        });
    };

	//MOOE - TOTAL PER LINE - INDIRECT
	this.__indirect_mooe_totals = function () {
        jQuery('.budgetmooedata-indirect-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
            var r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
            var r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

            var total = approved + r1 + r2 + r3;

            row.find('.proposed_realignment').val(total.toFixed(2));
        });
    };

	//CO - TOTAL PER LINE - DIRECT
	this.__direct_co_totals = function () {
        jQuery('.budgetcodata-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
            var r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
            var r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

            var total = approved + r1 + r2 + r3;

            row.find('.proposed_realignment').val(total.toFixed(2));
        });
    };

	//CO - TOTAL PER LINE - INDIRECT
	this.__indirect_co_totals = function () {
        jQuery('.budgetcodata-indirect-list tr').each(function () {
            var row = jQuery(this);

            var approved = parseFloat(row.find('.approved_budget').val()) || 0;
            var r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
            var r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
            var r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

            var total = approved + r1 + r2 + r3;

            row.find('.proposed_realignment').val(total.toFixed(2));
        });
    };

	this.__combined_totals = function () {
		let totalApprovedCombined = 0;
		let totalProposedCombined = 0;

		// PS DIRECT COST TABLE
		jQuery('.budgetdata-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
			let r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
			let r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

			let total = approved + r1 + r2 + r3;

			row.find('.proposed_realignment').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;
		});

		// PS INDIRECT COST TABLE
		jQuery('.budgetdata-indirect-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
			let r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
			let r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

			let total = approved + r1 + r2 + r3;

			row.find('.proposed_realignment').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;
		});

		// MOOE DIRECT COST TABLE
		jQuery('.budgetmooedata-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
			let r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
			let r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

			let total = approved + r1 + r2 + r3;

			row.find('.proposed_realignment').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;
		});

		// MOOE INDIRECT COST TABLE
		jQuery('.budgetmooedata-indirect-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
			let r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
			let r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

			let total = approved + r1 + r2 + r3;

			row.find('.proposed_realignment').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;
		});

		// CO DIRECT COST TABLE
		jQuery('.budgetcodata-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
			let r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
			let r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

			let total = approved + r1 + r2 + r3;

			row.find('.proposed_realignment').val(total.toFixed(2));

			totalApprovedCombined += approved;
			totalProposedCombined += total;
		});

		// CO INDIRECT COST TABLE
		jQuery('.budgetcodata-indirect-list tr').each(function () {
			let row = jQuery(this);

			let approved = parseFloat(row.find('.approved_budget').val()) || 0;
			let r1 = parseFloat(row.find('.r1_approved_budget').val()) || 0;
			let r2 = parseFloat(row.find('.r2_approved_budget').val()) || 0;
			let r3 = parseFloat(row.find('.r3_approved_budget').val()) || 0;

			let total = approved + r1 + r2 + r3;

			row.find('.proposed_realignment').val(total.toFixed(2));

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

	this.__showPdfInModal = function(pdfUrl) {
		var pdfFrame = document.getElementById("pdfFrame");
		var pdfModal = new bootstrap.Modal(document.getElementById("pdfModal"));

		pdfFrame.src = pdfUrl;
		pdfModal.show();
	};


}; //end main
