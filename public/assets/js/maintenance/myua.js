var __mysys_myua_ent = new __mysys_myua_ent();
function __mysys_myua_ent() {  
	const mesiteurl = $('#__siteurl').attr('data-mesiteurl');

	this.__user_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.myua-validation')
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
					var full_name = document.getElementById("full_name");
					var division = document.getElementById("division");
					var section = document.getElementById("section");
					var position = document.getElementById("position");
					var username = document.getElementById("username");
					var hash_password = document.getElementById("hash_password");
					var hash_value = document.getElementById("hash_value");
					
					var mparam = { 
						recid: recid.value,
						full_name: full_name.value,
						division: division.value,
						section: section.value,
						position: position.value,
						username: username.value,
						hash_password: hash_password.value,
						hash_value: hash_value.value,
						meaction: 'MAIN-SAVE'
					}


					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'myua',
						context: document.body,
						data: eval(mparam),
						global: false,
						cache: false,
						success: function(data) { //display html using divID
							jQuery('.me-myua-outp-msg').html(data);
							return false;
						},
						error: function(xhr, status, error) { // display global error on the menu function
							//__mysys_apps.mybs_simple_toast('memsgtoastcont','metoastmsglang','align-items-center text-bg-danger border-0','Hello, Error Loading Page [TRXMGT-AP-ITEM-TAXDED-ENT]' + error);
							toastr.error('[MYUA-ENT', "Hello, Error Loading Page..." + error, {
							closeButton: true,
							});
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

	this.__user_access_saving = function() { 
		'use strict';
	
		var forms = document.querySelectorAll('.myua-access-validation');
	
		Array.prototype.slice.call(forms)
		.forEach(function (form) {
			form.addEventListener('change', function (event) {
				var target = event.target;
				
				if (target && target.classList.contains('form-check-input')) {
					try {
						event.preventDefault();
						event.stopPropagation();
	
						var dataId = target.getAttribute('data-id'); // get data-id from checkbox
						var accessStatus = target.checked ? 1 : 0; // 1 if checked, 0 if unchecked
						
						var mparam = { 
							recid: dataId,
							access_status: accessStatus,
							meaction: 'MAIN-ACCESS-SAVE'
						};
	
						jQuery.ajax({ 
							type: "POST",
							url: mesiteurl + 'myua',
							context: document.body,
							data: mparam,
							global: false,
							cache: false,
							success: function(data) { 
								jQuery('.me-myua-access-outp-msg').html(data);
								return false;
							},
							error: function(xhr, status, error) { 
								toastr.error('[MYUA-ACCESS]', "Hello, Error Loading Page..." + error, {
									closeButton: true,
								});
								return false;
							} 
						});
	
					} catch(err) { 
						alert(err.message);
						return false;
					}
				}
			}, false);
		});
	};
	
	this.__update_user_access = function() {
        document.getElementById('btn_add_useraccess').addEventListener('click', function (event) { 
			try { 

				var username = document.getElementById("username");
				var rowcount1 = jQuery('.accessdata-list tr').length;
				var accessdata = [];
				var mdata = '';

				for (var aa = 1; aa < rowcount1; aa++) {
					var clonedRow = jQuery('.accessdata-list tr:eq(' + aa + ')'); 
					var access_name = clonedRow.find('input[type=text]').eq(0).val();
					var access_code = clonedRow.find('input[type=text]').eq(1).val();
					var isChecked = clonedRow.find('input[type=checkbox]').eq(0).prop('checked');
					var dtid = clonedRow.find('input[type=text]').eq(0).attr('data-dtid');
					
					mdata = access_name + 'x|x' + access_code + 'x|x' + isChecked + 'x|x' + dtid;
					accessdata.push(mdata);
				}

                var mparam = {
					username: username.value,
					accessdata: accessdata,
                    meaction: 'MAIN-ACCESS-SAVE'
                };
                
                $.ajax({ // default declaration of ajax parameters
                    type: "POST",
                    url: mesiteurl + 'myua',
                    context: document.body,
                    data: eval(mparam),
                    global: false,
                    cache: false,
                    success: function(data)  { //display html using divID
                        jQuery('.me-myua-access-outp-msg').html(data);
                        return false;
                    },
                    error: function() { // display global error on the menu function
                        alert('error loading page...');
                        return false;
                    } 
                    });     
                            
                } catch(err) {
                    var mtxt = 'There was an error on this page.\n';
                    mtxt += 'Error description: CLASS SAVING ERROR' + err.message;
                    mtxt += '\nClick OK to continue.';
                    alert(mtxt);
                    
                    return false;
        
                }  //end try	
		}); 
    }
	
}; //end main

__mysys_myua_ent.__update_user_access();