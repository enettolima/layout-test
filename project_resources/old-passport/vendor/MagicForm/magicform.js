$(document).ready(function() {
	$('form').each(function() {
		var form = $(this);
		form.submit(validate_all);
		
		$('[@validate]', form).each(function() {
			var element = $(this);
			
			element.change(function() {
				validate(form, element);
			});
			
			element.keyup(function() {
				validate(form, element);
			});
		});
	});
});

function validate_all() { 
	var valid = true;
	var form = $(this);
	
	$("[@validate]", form).each(function() {
		var element = $(this);

		if (!validate(form, element)) {
			valid = false;
		}
	});
	
	if (!valid) {
		alert('There are some invalid values in the form, please correct these before saving');
	}
	return valid;
}

function validate(form, element) {
	if (element.attr('validate') == null)
		return true;

	var rules = element.attr('validate').split(';').map(trim);
	var inputName = element.attr('name');
	
	var valid = true;
	jQuery.each(rules, function() {
		var rule = this;
		var arguments = rule.split(' ');
		var validator = arguments.shift();
		arguments.unshift(element.val());
		
		var localValid = window['validate_' + validator].apply(element, arguments);
		if (!localValid)
			valid = false;
			
		$('label.error[@for][@validator=' + validator + ']', form).each(function() {
			var element = $(this);
			
			if (element.attr('for') != inputName)
				return;
			
			set_state(element, localValid)
		});
	});

	$('label.error[@for]', form).not('[@validator]').each(function() {
		var element = $(this);

		if (element.attr('for') != inputName)
			return;
		
		set_state(element, valid);
	});
	
	return valid;
}

function set_state(element, valid) {
	var states = ['invalid', 'valid', 'none'];
	
	jQuery.each(states, function(i, state) {
		element.removeClass('validatestate_' + state);
	});
	
	element.addClass('validatestate_' + states[valid ? 1 : 0]);
}

function trim(value) {
	return value.replace(/^\s+|\s+$/g, "");
}

/* Validators */

function validate_strlen(value, max, min) {
	if (value == null || max == null)
		return false;
	
	if (min == null) {
		if (value.length != max)
			return false;
	} else {
		if (value.length > max || value.length < min)
			return false;
	}
	
	return true;
}

function validate_words(value) {

	return !value.match(/[^A-Za-z' ]/);
}

function validate_phonenumber(value, max, min) {
	var numlen = value.replace(/\D/g, "").length;
	return numlen == 11 || numlen == 10;
}

function validate_alnum(value) {

	return !value.match(/\W/);
}