M.local_elibrary = {};


M.local_elibrary.init_loan_resource = function(Y, loan_quota_available){
	Y.one('#loan_list tbody tr').removeClass('lastrow');
	Y.one("#resource_barcode").on("keypress", function(e){
		console.log('pressed')
		console.log(e.keyCode)
		if(e.keyCode == 13){
			e.preventDefault();
			var userid = Y.one("#user_id").get('value');
			var accessno = Y.one("#resource_barcode").get('value');
			var borrower = Y.one("#borrower").get('value');
			Y.use('local_elibrary', function(Y) {
				M.local_elibrary.insert_resource_to_loan_list(Y, userid, accessno, loan_quota_available, borrower);
			});
		}
	});
	
	Y.one("#button_enter_resource_barcode").on("click", function(e){
		e.preventDefault();
		var userid = Y.one("#user_id").get('value');
		var accessno = Y.one("#resource_barcode").get('value');
		Y.use('local_elibrary', function(Y) {
			M.local_elibrary.insert_resource_to_loan_list(Y, userid, accessno, loan_quota_available);
		});
	});
	
	Y.one("form#loan_list_form").on("submit", function(e){
		var tr_number = Y.one('#loan_list').all('tr').size() - 2;
		if(tr_number <= 0){
			e.preventDefault();
			alert(M.util.get_string('msg_loan_list_empty', 'local_elibrary'));
		}
	});
}

M.local_elibrary.init_return_resource = function(Y){
	Y.one('#return_list tbody tr').removeClass('lastrow');
	
	Y.one("#button_enter_resource_barcode").on("click", function(e){
		e.preventDefault();
		var accessno = Y.one("#resource_barcode").get('value');
		Y.use('local_elibrary', function(Y) {
			M.local_elibrary.insert_resource_to_return_list(Y, accessno);
		});
	});
	
	Y.one("#resource_barcode").on("keypress", function(e){
		if(e.keyCode == 13){
			e.preventDefault();
			var accessno = Y.one("#resource_barcode").get('value');
			Y.use('local_elibrary', function(Y) {
				M.local_elibrary.insert_resource_to_return_list(Y, accessno);
			});
		}
	});
	
	Y.one("form#return_list_form").on("submit", function(e){
		var tr_number = Y.one('#return_list').all('tr').size() - 2;
		if(tr_number <= 0){
			e.preventDefault();
			alert(M.util.get_string('msg_return_list_empty', 'local_elibrary'));
		}
	});
}

M.local_elibrary.init_renew_resource = function(Y){
	Y.one('#renew_list tbody tr').removeClass('lastrow');
	
	Y.one("#button_enter_resource_barcode").on("click", function(e){
		e.preventDefault();
		var accessno = Y.one("#resource_barcode").get('value');
		Y.use('local_elibrary', function(Y) {
			M.local_elibrary.insert_resource_to_renew_list(Y, accessno);
		});
	});
	
	Y.one("#resource_barcode").on("keypress", function(e){
		if(e.keyCode == 13){
			e.preventDefault();
			var accessno = Y.one("#resource_barcode").get('value');
			Y.use('local_elibrary', function(Y) {
				M.local_elibrary.insert_resource_to_renew_list(Y, accessno);
			});
		}
	});
	
	Y.one("form#renew_list_form").on("submit", function(e){
		var tr_number = Y.one('#renew_list').all('tr').size() - 2;
		if(tr_number <= 0){
			e.preventDefault();
			alert(M.util.get_string('msg_renew_list_empty', 'local_elibrary'));
		}
	});
}

M.local_elibrary.insert_resource_to_loan_list = function(Y, userid, accessno, loan_quota_available, borrower) {
	console.log('insert_resource_to_loan_list')
	Y.one("#resource_barcode").set('value', '');
	Y.one('#barcode_enter_result').setContent('');
	var tr_number = Y.one('#loan_list').all('tr').size() - 2;
	if(tr_number >= loan_quota_available){
		Y.one('#barcode_enter_result').setContent(M.util.get_string('msg_over_loan_quota', 'local_elibrary'));
		return false;
	}
	
	if(Y.one('form#loan_list_form input.loan_accessno[value="' + accessno + '"]') != null){
		Y.one('#barcode_enter_result').setContent(M.util.get_string('msg_resource_already_entered', 'local_elibrary'));
		return false;
	}
	
	Y.io(M.cfg.wwwroot+"/local/elibrary/ajax/insert_resource_to_loan_list.php", {
		method: 'POST',
		data: 'userid=' + userid + '&accessno=' + accessno + '&borrower' + borrower,
		on:{
			start:function(o){
				//Y.one('#barcode_enter_result').setContent("loading");
			},
			success:function(id, res){
				var data = JSON.parse(res.responseText); // Response data.
				if(data.status == 'fail'){
					Y.one('#barcode_enter_result').setContent(M.util.get_string(data.msg, 'local_elibrary'));
				}else if(data.status == 'success'){
					var resource_data = JSON.parse(data.data);
					Y.one('#barcode_enter_result').setContent(M.util.get_string('msg_success_to_input_resource', 'local_elibrary', resource_data.accessno));
					Y.one('form#loan_list_form').appendChild('<input type="hidden" class="loan_accessno" name="loan_accessno[]" value="' + resource_data.accessno + '" />');
					var hidden_row = Y.one('#loan_list tbody tr').cloneNode(true);
					hidden_row.removeClass('r0');
					hidden_row.addClass('r' + (tr_number + 1));
					hidden_row.all('td.c0').setContent(resource_data.accessno);
					hidden_row.all('td.c1').setContent(resource_data.title);
					hidden_row.all('td.c2').setContent(resource_data.author);
					hidden_row.all('td.c3').setContent(resource_data.publisher);
					hidden_row.all('td.c4 .button_delete_from_loan_list').setAttribute('ref', resource_data.accessno);
					hidden_row.all('td.c4 .button_delete_from_loan_list').on("click", function(e){
						Y.one('form#loan_list_form input.loan_accessno[value="' + e.target.getAttribute('ref') + '"]').remove();
						Y.one(e.target).ancestor().ancestor().remove();
					});
					hidden_row.show();
					Y.one('#loan_list tbody').appendChild(hidden_row);
				}
			},
			failure:function(o){
				Y.one('#barcode_enter_result').setContent(M.util.get_string('msg_fail_to_input_resource', 'local_elibrary', resource_data.accessno));
			}
		}
	});
};

M.local_elibrary.insert_resource_to_return_list = function(Y, accessno) {
	Y.one("#resource_barcode").set('value', '');
	Y.one('#barcode_enter_result').setContent('');

	var tr_number = Y.one('#return_list').all('tr').size() - 1;
	
	if(Y.one('form#return_list_form input.return_accessno[value="' + accessno + '"]') != null){
		Y.one('#barcode_enter_result').setContent(M.util.get_string('msg_resource_already_entered', 'local_elibrary'));
		return false;
	}
	
	Y.io(M.cfg.wwwroot+"/local/elibrary/ajax/insert_resource_to_return_list.php", {
		method: 'POST',
		data: 'accessno=' + accessno,
		on:{
			start:function(o){
				//Y.one('#barcode_enter_result').setContent("loading");
			},
			success:function(id, res){
				var data = JSON.parse(res.responseText); // Response data.
				if(data.status == 'fail'){
					Y.one('#barcode_enter_result').setContent(M.util.get_string(data.msg, 'local_elibrary'));
				}else if(data.status == 'success'){
					var resource_data = JSON.parse(data.data);
					Y.one('#barcode_enter_result').setContent(M.util.get_string('msg_success_to_input_resource', 'local_elibrary', resource_data.accessno));
					Y.one('form#return_list_form').appendChild('<input type="hidden" class="return_accessno" name="return_accessno[]" value="' + resource_data.accessno + '" />');
					var hidden_row = Y.one('#return_list tbody tr').cloneNode(true);
					hidden_row.removeClass('r0');
					hidden_row.addClass('r' + tr_number);
					hidden_row.all('td.c0').setContent(resource_data.accessno);
					hidden_row.all('td.c1').setContent(resource_data.title);
					var date = new Date(resource_data.returndate * 1000);
					hidden_row.all('td.c2').setContent(date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate());
					var delay = 0;
					var current = new Date().getTime();
					current = Math.round(current / 1000);
					if(current >= resource_data.returndate){
						delay = Math.ceil((current - resource_data.returndate) / 3600 / 24);
					}
					hidden_row.all('td.c3').setContent(delay + M.util.get_string('days', 'local_elibrary'));
					hidden_row.all('td.c4 .button_delete_from_return_list').setAttribute('ref', resource_data.accessno);
					hidden_row.all('td.c4 .button_delete_from_return_list').on("click", function(e){
						Y.one('form#return_list_form input.return_accessno[value="' + e.target.getAttribute('ref') + '"]').remove();
						Y.one(e.target).ancestor().ancestor().remove();
					});
					hidden_row.show();
					Y.one('#return_list tbody').appendChild(hidden_row);
				}
			},
			failure:function(o){
				Y.one('#barcode_enter_result').setContent(M.util.get_string('msg_fail_to_input_resource', 'local_elibrary', resource_data.accessno));
			}
		}
	});
};

M.local_elibrary.insert_resource_to_renew_list = function(Y, accessno) {
	Y.one("#resource_barcode").set('value', '');
	Y.one('#barcode_enter_result').setContent('');

	var tr_number = Y.one('#renew_list').all('tr').size() - 1;
	
	if(Y.one('form#renew_list_form input.renew_accessno[value="' + accessno + '"]') != null){
		Y.one('#barcode_enter_result').setContent(M.util.get_string('msg_resource_already_entered', 'local_elibrary'));
		return false;
	}
	
	Y.io(M.cfg.wwwroot+"/local/elibrary/ajax/insert_resource_to_renew_list.php", {
		method: 'POST',
		data: 'accessno=' + accessno,
		on:{
			start:function(o){
				//Y.one('#barcode_enter_result').setContent("loading");
			},
			success:function(id, res){
				var data = JSON.parse(res.responseText); // Response data.
				if(data.status == 'fail'){
					Y.one('#barcode_enter_result').setContent(M.util.get_string(data.msg, 'local_elibrary'));
				}else if(data.status == 'success'){
					var resource_data = JSON.parse(data.data);
					Y.one('#barcode_enter_result').setContent(M.util.get_string('msg_success_to_input_resource', 'local_elibrary', resource_data.accessno));
					Y.one('form#renew_list_form').appendChild('<input type="hidden" class="renew_accessno" name="renew_accessno[]" value="' + resource_data.accessno + '" />');
					var hidden_row = Y.one('#renew_list tbody tr').cloneNode(true);
					hidden_row.removeClass('r0');
					hidden_row.addClass('r' + tr_number);
					hidden_row.all('td.c0').setContent(resource_data.accessno);
					hidden_row.all('td.c1').setContent(resource_data.title);
					var oldreturndate = new Date(resource_data.oldreturndate * 1000);
					hidden_row.all('td.c2').setContent(oldreturndate.getFullYear() + '-' + (oldreturndate.getMonth()+1) + '-' + oldreturndate.getDate());
					var newreturndate = new Date(resource_data.newreturndate * 1000);
					hidden_row.all('td.c3').setContent(newreturndate.getFullYear() + '-' + (newreturndate.getMonth()+1) + '-' + newreturndate.getDate());
					hidden_row.all('td.c4 .button_delete_from_renew_list').setAttribute('ref', resource_data.accessno);
					hidden_row.all('td.c4 .button_delete_from_renew_list').on("click", function(e){
						Y.one('form#renew_list_form input.renew_accessno[value="' + e.target.getAttribute('ref') + '"]').remove();
						Y.one(e.target).ancestor().ancestor().remove();
					});
					hidden_row.show();
					Y.one('#renew_list tbody').appendChild(hidden_row);
				}
			},
			failure:function(o){
				Y.one('#barcode_enter_result').setContent(M.util.get_string('msg_fail_to_input_resource', 'local_elibrary', resource_data.accessno));
			}
		}
	});
};
