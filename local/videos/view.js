
require(['jquery'], function($) {
	//var test_canvas = document.createElement("canvas") //try and create sample canvas element
	//var canvascheck=(test_canvas.getContext)? true : false //check if object supports getContext() method, a method of the canvas element
	if (isnonconformance) {
		$('#bottom_w3c_logo').prop('src', nonconformanceimagelink);
		$('#bottom_w3c_logo').prop('alt', 'Non-conformance Logo');
		$('#bottom_w3c_link').prop('href', M.cfg.wwwroot + '/mod/page/view.php?id=' + nonconformancepageid);
	}	
	videos_view_video_player_setup();

	function videos_view_video_player_setup() {
		$('video').on('loadstart', function (event) {
			$(this).addClass('loading');
		  });
		$('video').on('canplay', function (event) {
			$(this).removeClass('loading');
			$(this).attr('poster', '');
		});
	}
});