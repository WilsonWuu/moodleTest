YUI().use("node-base", function(Y) {
	var cat_changed = function(e) {
		document.forms[0].submit();
	}
	Y.on('change', cat_changed, '#id_category');
	
});