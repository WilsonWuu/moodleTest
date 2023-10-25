YUI().use("node-base", function(Y) {
	var cat_changed = function(e) {
		document.forms['category_list'].submit();
	}
	Y.on('change', cat_changed, '#id_category');
});