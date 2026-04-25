requirejs(['jquery'], function($){
    $(document).ready(function(){
        var setsInput = $('select[name="sets"]');
		var entityInput = $('select[name="entity"]');
		var categoriesInput = $('select[name="categories"]');

		$('select[name="entity"]').change(function() {
			var entity = $(this).val() ?? '';

			$('.field-categories').hide();
			categoriesInput.val('');
			categoriesInput.html('<option value=""></option>');
			$('#datalocation_entity-error').remove();
			$('#datalocation_categories-error').remove();

			if (entity) {
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
							categoriesInput.html('<option value="">Select...</option>');
							var options = resp.data.map(function(val, ind){
							    return $("<option></option>").val(val.value).html(val.label);
							});
							$('.field-categories label span').text(resp.attribute.label)
							categoriesInput.append(options);
							$('.field-categories').show();
						} else {
							entityInput.after('<label id="datalocation_entity-error" class="mage-error" for="datalocation_entity">No data found on this categories.</label>');
						}
					}
				});
			}
		});

		// Sets
		categoriesInput.change(function() {
			var entity = entityInput.val() ?? '';
			var categories = $(this).val() ?? '';

			$('.field-sets').hide();
			setsInput.val('');
			setsInput.html('<option value=""></option>');
			$('#datalocation_sets-error').remove();
			$('#datalocation_categories-error').remove();

			if (entity) {
				$.ajax({
					showLoader: true,
	                data: {form_key: window.FORM_KEY,entity:entity,categories:categories},
	                type: 'GET',
	                beforeSend: function () {
	                    $('#loader').show();
	                },
					url: '/backoffice/productattributeslink/import/getSetsData',
					dataType: 'json',
					success: function(resp) {
	                    console.log(resp)
						if (resp.data.length) {
							setsInput.html('<option value="">Select...</option>');
							var options = resp.data.map(function(val, ind){
							    return $("<option></option>").val(val.value).html(val.label);
							});
							setsInput.append(options);
							$('.field-sets').show();
						} else {
							categoriesInput.after('<label id="datalocation_categories-error" class="mage-error" for="datalocation_categories">No sets found. Please upload sets first.</label>');
						}
					}
				});
			}
		});
    });
});