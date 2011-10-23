dropOpen = false;
function dropClose() {
	$('.sub').hide();
	dropOpen = false;
}
function dropOpen() {
	$('.sub').show();
	dropOpen = true;
}
$(document).ready( function() {
	dropClose();
	$("#tdropdown").hover(
		function() {
		off = $("#tdropdown a:first").position();
		var x = off.left-105;
		var y = off.top+24;
			dropOpen = true;
			$('.sub')
				.show()
				.css('position','absolute')
				.css('left',x)
				.css('top',y);
		$("#tdropdown a.top_link").css('backgroundColor','#dde9ff');
		},
		function() {
			$("#tdropdown a.top_link").css('backgroundColor','#b9d3ff');
			dropClose();
		}
	)
});