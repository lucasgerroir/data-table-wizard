var init_additional_columns = function() {
	
	
}

function createColumn() {
	
	var content_holder = jQuery("#data-column");
	
	content_holder.append(create_label("testing", "test"));
	content_holder.append(create_input("text", "test", "test", "test"));
	console.log(content_holder);
}

function create_label( text, id) {
	
	var label = jQuery("<label></label>");
	label = label.append( text );
	label.attr({
			    htmlFor: id
	});
	
	return label;
}

function create_input(type, text, value, name) {
	
	var input = jQuery("<input></input>");
	input.attr({
		type: type,
		value: value,
		name: name
	});
	
	return input;
}



jQuery(document).ready( init_additional_columns );