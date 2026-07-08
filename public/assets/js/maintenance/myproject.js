var __mysys_project_ent = new __mysys_project_ent();
function __mysys_project_ent() {  
	const mesiteurl = $('#__siteurl').attr('data-mesiteurl');

	this.__project_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.myproject-validation')
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
					var project_title = document.getElementById("project_title");
					var fund_cluster_code = document.getElementById("selFundcluster");
					var fundcluster_id = document.getElementById("fundcluster_id");
					var division_name = document.getElementById("selDivision");
					var division_id = document.getElementById("division_id");
					var responsibility_code = document.getElementById("responsibility_code");
					var project_leader = document.getElementById("project_leader");
					var mfopaps_code = document.getElementById("mfopaps_code");

					
					var mparam = { 
						recid: recid.value,
						project_title: project_title.value,
						fund_cluster_code: fund_cluster_code.value,
						fundcluster_id: fundcluster_id.value,
						division_name: division_name.value,
						division_id: division_id.value,
						responsibility_code: responsibility_code.value,
						project_leader: project_leader.value,
						mfopaps_code: mfopaps_code.value,
						meaction: 'MAIN-SAVE'
					}

					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'myproject',
						context: document.body,
						data: eval(mparam),
						global: false,
						cache: false,
						success: function(data) { //display html using divID
							jQuery('.project-outp-msg').html(data);
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

// __mysys_project_ent.__delete_payee();