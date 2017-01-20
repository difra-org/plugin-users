/*
function addAdditionalField() {
	$('#additionalsAddFields').show();
	$('#addedFields').append('<tr><th><input type = "text" name="additional_name[]" class="full-width" placeholder="Название" /></th>' +
		'<td><input type="text" name="additional_value[]" class="full-width" placeholder="Значение" /></td></tr>');
}
*/

function changePassEnabler() {
	var dis = $('#changePw').is(':checked');
	if (dis ===
		true) {
		$('#newPw').removeAttr('disabled');
	} else {
		$('#newPw').attr('disabled', 'disabled');
	}
}
