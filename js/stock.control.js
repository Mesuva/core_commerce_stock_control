var ccm_coreCommerceActiveProductField = '';
ccm_coreCommerceSetupSearch = function() {
	
	
	$("#ccm-core-commerce-product-sets-search-wrapper select").chosen().unbind();
	$("#ccm-core-commerce-product-sets-search-wrapper select").chosen().change(function() {
		var sel = $("#ccm-core-commerce-product-sets-search-wrapper option:selected");
		//$("#ccm-core-commerce-product-advanced-search").submit();
	});
 
}

