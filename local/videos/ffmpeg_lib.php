<?php

function getVideoDuration($videoFile) {
	global $CFG;
	$ffmpeg = $CFG->ffmpeg;
	$percent = 100;
	ob_start();
	passthru("$ffmpeg -i \"". $videoFile . "\" 2>&1");
	$duration = ob_get_contents();
	ob_end_clean();

	preg_match('/Duration: (.*?),/', $duration, $matches);
	$duration = $matches[1];
	$duration_array = explode(':', $duration);

	$duration = (int)$duration_array[0] * 3600 + (int)$duration_array[1] * 60 + (int)$duration_array[2];
	$time = $duration * $percent / 100;
	return $time;
}

function createImageFromVideo($videoFile, $imageFile) {
   global $CFG;
   $ffmpeg = $CFG->ffmpeg;
   $size = "172x128";
   //$getFromSecond = round(getVideoDuration($videoFile)/2);
   if (getVideoDuration($videoFile) >= 60) {
	   $getFromSecond = 60;
   } else {
	   $getFromSecond = 10;
   }
   createFolderForFilePath($imageFile);
   $cmd = "$ffmpeg -i \"$videoFile\" -an -ss $getFromSecond -s $size \"$imageFile\"";
   if(!shell_exec($cmd))
   {
      return true;
   }
   else
   {
      return false;
   }
}

function createFolderForFilePath($filepath) {
	$filedir = substr($filepath, 0, strrpos($filepath, '/'));
	if (!file_exists($filedir)) {
		mkdir($filedir, 0755, true);
	}
}
?>