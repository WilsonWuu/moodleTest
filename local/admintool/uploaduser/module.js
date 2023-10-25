$( document ).ready(function() {
	$("#upload_form2").on("submit", function(){
		var elchs = $( "input[name=ignorekeys]:not(:checked)" );
		var n = $( "input[name=ignorekeys]:not(:checked)" ).length;
		var lines = new Array();
		for (i=0; i<n; i++) {
			lines[i] = elchs.get(i).value;
		}
		var input = $("<input>").attr({"type":"hidden","name":"ignorelines"}).val(lines);
		$('#upload_form2').append(input);
		return true;
	});
	
	$("#ignorekeyall").click(function() {
		if($("#ignorekeyall").prop("checked"))
		{
			$("input[name=ignorekeys]").each(function() {
				$(this).prop("checked", true);
			});
		}
		else
		{
			$("input[name=ignorekeys]").each(function() {
				$(this).prop("checked", false);
			});          
		}
	});
});