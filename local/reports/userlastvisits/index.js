$(document).ready(function() {
	$('#id_reporttype_1').prop('checked', true);
	$('#fgroup_id_reporttype').addClass('accesshide');
	//month and year
    attrs = {calendar:false, day:false, month:true, year:true};
    reportuserlastvisits_setDatepickerAttrs(attrs);		
});

function reportuserlastvisits_setDatepickerAttrs(attrs) {
	$('a[name="startdate[calendar]"]').each(function() {
		$(this).attr('style', attrs.calendar ? '' : 'display:none');
	});
	$('a[name="enddate[calendar]"]').each(function() {
		$(this).attr('style', attrs.calendar ? '' : 'display:none');
	});
	$('#id_startdate_day').attr('disabled', !attrs.day);
	$('#id_startdate_month').attr('disabled', !attrs.month);
	$('#id_startdate_year').attr('disabled', !attrs.year);
	$('#id_enddate_day').attr('disabled', !attrs.day);
	$('#id_enddate_month').attr('disabled', !attrs.month);
	$('#id_enddate_year').attr('disabled', !attrs.year);
}