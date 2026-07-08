var __mysys_orsburs_ent = new __mysys_orsburs_ent();
function __mysys_orsburs_ent() {  
	const mesiteurl = $('#__siteurl').attr('data-mesiteurl');

	this.my_add_budget_line = function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('select').eq(0).val('');
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
	
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

	this.my_add_budget_indirect_line = function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_indirect_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_indirect_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field
			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('select').eq(0).val('');
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value
	
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

	this.my_add_budget_mooe_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_mooe_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_mooe_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field

			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('select').eq(0).val('');
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value

	
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

	this.my_add_budget_indirect_mooe_line= function () {
		try {
			// Get the total number of rows, excluding the footer row
			var rowCount = jQuery('#budget_mooe_indirect_line_items tbody tr').length;
			var mid = generateRandomID(10) + (rowCount + 1);
	
			// Clone the last data row (not the footer)
			var clonedRow = jQuery('#budget_mooe_indirect_line_items tbody tr:eq(' + (rowCount - 1) + ')').clone();
	
			jQuery(clonedRow).find('select').eq(0).val('').attr('id', 'col4' + mid);
			jQuery(clonedRow).find('input[type=text]').eq(0).attr('id', 'col2' + mid); // ID for second text field
			jQuery(clonedRow).find('input[type=number]').eq(0).attr('id', 'col3' + mid); // ID for date field

			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('select').eq(0).val('');
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value

	
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

			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value

	
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

			// Now reset only the debit and credit fields (input[type=number])
			
			jQuery(clonedRow).find('input[type=text]').eq(0).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=text]').eq(1).val('');  // Clear credit value
			jQuery(clonedRow).find('input[type=number]').eq(0).val('').attr('data-dtid', '');  // Clear credit value

	
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

	function generateRandomID(length) {
		const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		let result = '';
		for (let i = 0; i < length; i++) {
			result += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		return result;
	}
	

	this.__orsburs_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.myorsburs-validation')
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
					var serial_no = document.getElementById("serial_no");
					var program_title = document.getElementById("program_title");
					var project_title = document.getElementById("project_title");
					var fund_cluster_code = document.getElementById("fund_cluster_code");
					var funding_source = document.getElementById("funding_source");
					var responsibility_code = document.getElementById("responsibility_code");
					var mfopap = document.getElementById("mfopap");

					var payee_name = document.getElementById("payee_name");
					var payee_office = document.getElementById("payee_office");
					var payee_address = document.getElementById("payee_address");

					// Prepare PS data DIRECT --
					var rowcount1 = jQuery('.budgetdata-list tr').length;
					var budgetdtdata = [];
					var psdata = '';
	
					for (var aa = 1; aa < rowcount1; aa++) {
						var clonedRow = jQuery('.budgetdata-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						
						psdata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid;
						budgetdtdata.push(psdata);
					}

					// Prepare PS data INDIRECT --
					var rowcount11 = jQuery('.budgetdata-indirect-list tr').length;
					var budgetdtindirectdata = [];
					var psindirectdata = '';
	
					for (var aa = 1; aa < rowcount11; aa++) {
						var clonedRow = jQuery('.budgetdata-indirect-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						
						psindirectdata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid;
						budgetdtindirectdata.push(psindirectdata);
					}

					// Prepare MOEE data
					var rowcount2 = jQuery('.budgetmooedata-list tr').length;
					var budgetmooedtdata = [];
					var mooedata = '';
	
					for (var aa = 1; aa < rowcount2; aa++) {
						var clonedRow = jQuery('.budgetmooedata-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						
						mooedata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid;
						budgetmooedtdata.push(mooedata);
					}

					// Prepare MOEE data
					var rowcount22 = jQuery('.budgetmooedata-indirect-list tr').length;
					var budgetmooeindirectdtdata = [];
					var mooeindirectdata = '';
	
					for (var aa = 1; aa < rowcount22; aa++) {
						var clonedRow = jQuery('.budgetmooedata-indirect-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('select.selUacs').val();
						var uacs = clonedRow.find('input[type=text]').eq(0).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						
						mooeindirectdata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid;
						budgetmooeindirectdtdata.push(mooeindirectdata);
					}

					// Prepare CO data
					var rowcount3 = jQuery('.budgetcodata-list tr').length;
					var budgetcodtdata = [];
					var codata = '';
	
					for (var aa = 1; aa < rowcount3; aa++) {
						var clonedRow = jQuery('.budgetcodata-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('input[type=text]').eq(0).val();
						var uacs = clonedRow.find('input[type=text]').eq(1).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						
						codata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid;
						budgetcodtdata.push(codata);
					}

					// Prepare CO data
					var rowcount33 = jQuery('.budgetcodata-indirect-list tr').length;
					var budgetindirectcodtdata = [];
					var coindirectdata = '';
	
					for (var aa = 1; aa < rowcount33; aa++) {
						var clonedRow = jQuery('.budgetcodata-indirect-list tr:eq(' + aa + ')'); 
						var particulars = clonedRow.find('input[type=text]').eq(0).val();
						var uacs = clonedRow.find('input[type=text]').eq(1).val();
						var approved_budget = clonedRow.find('input[type=number]').eq(0).val();  
						var dtid = clonedRow.find('input[type=number]').eq(0).attr('data-dtid');
						
						coindirectdata = particulars + 'x|x' + uacs + 'x|x' + approved_budget + 'x|x' + dtid;
						budgetindirectcodtdata.push(coindirectdata);
					}

					var mparam = { 
						recid: recid.value,
						serial_no: serial_no.value,
						program_title: program_title.value,
						project_title: project_title.value,
						fund_cluster_code: fund_cluster_code.value,
						funding_source: funding_source.value,
						responsibility_code: responsibility_code.value,
						mfopap: mfopap.value,
						payee_name: payee_name.value,
						payee_office: payee_office.value,
						payee_address: payee_address.value,
						budgetdtdata: budgetdtdata,
						budgetdtindirectdata: budgetdtindirectdata,
						budgetmooedtdata: budgetmooedtdata,
						budgetmooeindirectdtdata: budgetmooeindirectdtdata,
						budgetcodtdata: budgetcodtdata,
						budgetindirectcodtdata: budgetindirectcodtdata,
						meaction: 'MAIN-SAVE'
					}

					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'myorsburs',
						context: document.body,
						data: eval(mparam),
						global: false,
						cache: false,
						success: function(data) {
							jQuery('.myorsburs-outp-msg').html(data);
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
	}; //

	// this.__delete_payee = function() {
	// 	const deleteBtn = document.getElementById('btn_delete');
	// 	const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
	
	// 	let recid = null; // store recid for use after confirmation
	
	// 	deleteBtn.addEventListener('click', function () {
	// 		recid = document.getElementById("recid").value;
	
	// 		// Show the modal
	// 		const deleteModal = new bootstrap.Modal(document.getElementById('confirmApproveModal'));
	// 		deleteModal.show();
	// 	});
	
	// 	confirmDeleteBtn.addEventListener('click', function () {
	// 		if (!recid) return;
	
	// 		const mparam = {
	// 			recid: recid,
	// 			meaction: 'MAIN-DELETE'
	// 		};
	
	// 		jQuery.ajax({
	// 			type: "POST",
	// 			url: mesiteurl + 'mypayee',
	// 			context: document.body,
	// 			data: mparam,
	// 			global: false,
	// 			cache: false,
	// 			success: function(data) {
	// 				jQuery('.me-mypayee-outp-msg').html(data);
	// 			},
	// 			error: function(xhr, status, error) {
	// 				toastr.error('[MYPAYEE-ENT', "Hello, Error Loading Page..." + error, {
	// 					closeButton: true,
	// 				});
	// 			}
	// 		});
	
	// 		// Close the modal
	// 		const deleteModal = bootstrap.Modal.getInstance(document.getElementById('confirmApproveModal'));
	// 		deleteModal.hide();
	// 	});
	// };
	
	// this.__delete_payee = function() {
    //     document.getElementById('btn_delete').addEventListener('click', function (event) { 
	// 		try { 
	// 			var recid = document.getElementById("recid");
	// 			var fname = document.getElementById("fname");
	// 			var lname = document.getElementById("lname");
				
	// 			var mparam = { 
	// 				recid: recid.value,
	// 				fname: fname.value,
	// 				lname: lname.value,
	// 				meaction: 'MAIN-DELETE'
	// 			}

	// 			jQuery.ajax({ // default declaration of ajax parameters
	// 				type: "POST",
	// 				url: mesiteurl + 'mycrud',
	// 				context: document.body,
	// 				data: eval(mparam),
	// 				global: false,
	// 				cache: false,
	// 				success: function(data) { //display html using divID
	// 					jQuery('.me-mycrud-outp-msg').html(data);
	// 					return false;
	// 				},
	// 				error: function(xhr, status, error) { // display global error on the menu function
	// 					//__mysys_apps.mybs_simple_toast('memsgtoastcont','metoastmsglang','align-items-center text-bg-danger border-0','Hello, Error Loading Page [TRXMGT-AP-ITEM-TAXDED-ENT]' + error);
	// 					toastr.error('[MYCRUD-ENT', "Hello, Error Loading Page..." + error, {
	// 					closeButton: true,
	// 					});
	// 					return false;
	// 				} 
	// 			});  
                            
    //             } catch(err) {
    //                 var mtxt = 'There was an error on this page.\n';
    //                 mtxt += 'Error description: ' + err.message;
    //                 mtxt += '\nClick OK to continue.';
    //                 alert(mtxt);
                    
    //                 return false;
        
    //             }  //end try	
	// 	}); 
    // }

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

	
	

}; //end main
 __mysys_orsburs_ent.__orsburs_saving();