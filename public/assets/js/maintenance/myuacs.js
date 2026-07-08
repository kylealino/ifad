var __mysys_uacs_ent = new __mysys_uacs_ent();
function __mysys_uacs_ent() {  
	const mesiteurl = $('#__siteurl').attr('data-mesiteurl');

	this.__uacs_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.myuacs-validation')
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
					var allotment_class = document.getElementById("allotment_class");
					var object_code = document.getElementById("object_code");
					var sub_object_code = document.getElementById("sub_object_code");
					var uacs_code = document.getElementById("uacs_code");

					var mparam = { 
						recid: recid.value,
						allotment_class: allotment_class.value,
						object_code: object_code.value,
						sub_object_code: sub_object_code.value,
						uacs_code: uacs_code.value,
						meaction: 'MAIN-SAVE'
					}

					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'myuacs',
						context: document.body,
						data: eval(mparam),
						global: false,
						cache: false,
						success: function(data) { //display html using divID
							jQuery('.uacs-outp-msg').html(data);
							return false;
						},
						error: function(xhr, status, error) { // display global error on the menu function
							alert(error.message)
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
	// 		const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
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
	// 		const deleteModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
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

}; //end main

// __mysys_uacs_ent.__delete_payee();