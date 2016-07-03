$(document).ready(function() {
	$('ul#menu ul').hide();
	$('ul#menu li').click(function() {
		if ($(this).children('ul').is(':hidden')) {
			$(this).children('ul').slideDown(300);
		} else {
			$(this).children('ul').slideUp(300);
		}
	});
});
