require(['jquery'], function ($) {
	attrs = {
		calendar: false,
		day: false,
		month: false,
		year: false
	};
	reportlearningresources_setDatepickerAttrs(attrs);
	reporttype = $('input[type=radio][name=reporttype]:checked').val();
	if (reporttype) {
		reportlearningresources_checkreporttype(reporttype);
	}
	$('input[type=radio][name=reporttype]').change(function () {
		reportlearningresources_checkreporttype(this.value);
	});

	function reportlearningresources_checkreporttype(reporttype) {
		if (reporttype == 2) {
			//only year
			attrs = {
				calendar: false,
				day: false,
				month: false,
				year: true
			};
			reportlearningresources_setDatepickerAttrs(attrs);
		} else if (reporttype == 1) {
			//month and year
			attrs = {
				calendar: false,
				day: false,
				month: true,
				year: true
			};
			reportlearningresources_setDatepickerAttrs(attrs);
		} else if (reporttype == 0) {
			//day month year
			attrs = {
				calendar: true,
				day: true,
				month: true,
				year: true
			};
			reportlearningresources_setDatepickerAttrs(attrs);
		}
	}

	function reportlearningresources_setDatepickerAttrs(attrs) {
		$('a[name="startdate[calendar]"]').each(function () {
			$(this).attr('style', attrs.calendar ? '' : 'display:none');
		});
		$('a[name="enddate[calendar]"]').each(function () {
			$(this).attr('style', attrs.calendar ? '' : 'display:none');
		});
		$('#id_startdate_day').attr('disabled', !attrs.day);
		$('#id_startdate_month').attr('disabled', !attrs.month);
		$('#id_startdate_year').attr('disabled', !attrs.year);
		$('#id_enddate_day').attr('disabled', !attrs.day);
		$('#id_enddate_month').attr('disabled', !attrs.month);
		$('#id_enddate_year').attr('disabled', !attrs.year);
	}

});