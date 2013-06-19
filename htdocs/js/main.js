jQuery.noConflict();

function prepInputs(){
	jQuery('input, textarea').placeholder()
		.filter('[type="text"], [type="email"], [type="tel"], [type="password"]').addClass('text').end()
		.filter('[type="checkbox"]').addClass('checkbox').end()
		.filter('[type="radio"]').addClass('radiobutton').end()
		.filter('[type="submit"]').addClass('submit').end()
		.filter('[type="image"]').addClass('buttonImage');
}

jQuery(document).ready(function() {
    prepInputs();
});