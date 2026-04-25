requirejs(['jquery'], function($){
    $(document).ready(function(){
        var setsInput = $('select[name="card_set"]');
		
		

		$('select[name="entity"]').change(function() {
			var entity = $(this).val() ?? '';
			
			setsInput.val('');
			setsInput.html('<option value=""></option>');

			var entityDataInput = $('select[name="'+entity+'"]');
			
			entityDataInput.val('');
			entityDataInput.html('<option value=""></option>');
			entityDataInput.show();

			$.ajax({
				showLoader: true,
                data: {form_key: window.FORM_KEY,entity:entity},
                type: 'GET',
                beforeSend: function () {
                    $('#loader').show();
                },
				url: '/backoffice/productattributeslink/import/getEntityData',
				dataType: 'json',
				success: function(resp) {
                    console.log(resp)
					if (resp.data.length) {
						entityDataInput.html('<option value="">--Please select--</option>');
						var options = resp.data.map(function(val, ind){
						    return $("<option></option>").val(val.value).html(val.label);
						});
						entityDataInput.append(options);
					} else {
						entityDataInput.html('<option value="">--No data found--</option>');
					}
				}
			});
		})
    });
});