var __mysys_products_ent = new __mysys_products_ent();
function __mysys_products_ent() {  
	const mesiteurl = $('#__siteurl').attr('data-mesiteurl');



	this.__products_saving = function() { 
		'use strict' 
		// Fetch all the forms we want to apply custom Bootstrap validation styles to
		var forms = document.querySelectorAll('.myproducts-validation')
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
					var product_code = document.getElementById("product_code");
					var product_desc = document.getElementById("product_desc");
					var uom = document.getElementById("uom");
					var price = document.getElementById("price");
					var quantity = document.getElementById("quantity");
					var remarks = document.getElementById("remarks");
					
					var mparam = { 
						recid: recid.value,
						product_code: product_code.value,
						product_desc: product_desc.value,
						uom: uom.value,
						price: price.value,
						quantity: quantity.value,
						remarks: remarks.value,
						meaction: 'PRODUCTS-SAVE'
					}

					jQuery.ajax({ // default declaration of ajax parameters
						type: "POST",
						url: mesiteurl + 'myproducts',
						context: document.body,
						data: eval(mparam),
						global: false,
						cache: false,
						success: function(data) {
							jQuery('.myproducts-outp-msg').html(data);
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
            order: [[0, 'desc']],
            language: {
            search: "Search:"
            }
        });


    });

}; //end main
