<?php

defined('MOODLE_INTERNAL') || die();

function empty_replace(&$variable, $key, $replace = ''){
	$replace = empty($replace) ? $key : $replace;
	$variable = isset($variable) && !empty($variable) ? $variable : $replace;
}

/**
 * copied from process_new_icon() in dirty 2.7's lib/gdlib.php
 * To maintain code integrity and update capability, core code in lib/gdlib.php should not be edited
 */
function process_new_icon_innoverz($context, $component, $filearea, $itemid, $originalfile, 
	$smallsize = array('width'=>35,'height'=>35), $nolsize = array('width'=>100,'height'=>100), $largesize = array('width'=>512,'height'=>512), $srcxyfromzero = false) {
    global $CFG;

    if (!is_file($originalfile)) {
        return false;
    }

    $imageinfo = getimagesize($originalfile);

    if (empty($imageinfo)) {
        return false;
    }

    $image = new stdClass();
    $image->width  = $imageinfo[0];
    $image->height = $imageinfo[1];
    $image->type   = $imageinfo[2];

    $t = null;
    switch ($image->type) {
        case IMAGETYPE_GIF:
            if (function_exists('imagecreatefromgif')) {
                $im = imagecreatefromgif($originalfile);
            } else {
                debugging('GIF not supported on this server');
                return false;
            }
            // Guess transparent colour from GIF.
            $transparent = imagecolortransparent($im);
            if ($transparent != -1) {
                $t = imagecolorsforindex($im, $transparent);
            }
            break;
        case IMAGETYPE_JPEG:
            if (function_exists('imagecreatefromjpeg')) {
                $im = imagecreatefromjpeg($originalfile);
            } else {
                debugging('JPEG not supported on this server');
                return false;
            }
            break;
        case IMAGETYPE_PNG:
            if (function_exists('imagecreatefrompng')) {
                $im = imagecreatefrompng($originalfile);
            } else {
                debugging('PNG not supported on this server');
                return false;
            }
            break;
        default:
            return false;
    }

    if (function_exists('imagepng')) {
        $imagefnc = 'imagepng';
        $imageext = '.png';
        $filters = PNG_NO_FILTER;
        $quality = 1;
    } else if (function_exists('imagejpeg')) {
        $imagefnc = 'imagejpeg';
        $imageext = '.jpg';
        $filters = null; // not used
        $quality = 90;
    } else {
        debugging('Jpeg and png not supported on this server, please fix server configuration');
        return false;
    }
	
	if ($srcxyfromzero) {
		$srcwidth = $image->width;
		$srcheight = $image->height;
		$isfinded = false;
		$scale = 1;
		while (!$isfinded) {
			
			if ($srcwidth > $nolsize['width'] * $scale && $srcheight > $nolsize['height'] * $scale) {
				$scale = $scale + 0.2;
			} else {
				$scale = $scale - 0.2;
				//echo $srcwidth . ' ' . ($nolsize['width'] * $scale) . ' ' . $srcheight . ' ' . ($nolsize['height'] * $scale); exit();
				$isfinded = true;
			}
		}
		$nolsize['width'] = floor($nolsize['width'] * $scale);
		$nolsize['height'] = floor($nolsize['height'] * $scale);
	}

    if (function_exists('imagecreatetruecolor')) {
        $im1 = imagecreatetruecolor($nolsize['width'], $nolsize['height']);
        $im2 = imagecreatetruecolor($smallsize['width'], $smallsize['height']);
        $im3 = imagecreatetruecolor($largesize['width'], $largesize['height']);
        if ($image->type != IMAGETYPE_JPEG and $imagefnc === 'imagepng') {
            if ($t) {
                // Transparent GIF hacking...
                $transparentcolour = imagecolorallocate($im1 , $t['red'] , $t['green'] , $t['blue']);
                imagecolortransparent($im1 , $transparentcolour);
                $transparentcolour = imagecolorallocate($im2 , $t['red'] , $t['green'] , $t['blue']);
                imagecolortransparent($im2 , $transparentcolour);
                $transparentcolour = imagecolorallocate($im3 , $t['red'] , $t['green'] , $t['blue']);
                imagecolortransparent($im3 , $transparentcolour);
            }

            imagealphablending($im1, false);
            $color = imagecolorallocatealpha($im1, 0, 0,  0, 127);
            imagefill($im1, 0, 0,  $color);
            imagesavealpha($im1, true);

            imagealphablending($im2, false);
            $color = imagecolorallocatealpha($im2, 0, 0,  0, 127);
            imagefill($im2, 0, 0,  $color);
            imagesavealpha($im2, true);

            imagealphablending($im3, false);
            $color = imagecolorallocatealpha($im3, 0, 0,  0, 127);
            imagefill($im3, 0, 0,  $color);
            imagesavealpha($im3, true);
        }
    } else {
        $im1 = imagecreate($nolsize['width'], $nolsize['height']);
        $im2 = imagecreate($smallsize['width'], $smallsize['height']);
        $im3 = imagecreate($largesize['width'], $largesize['height']);
    }

    $cx = $image->width / 2;
    $cy = $image->height / 2;

    if ($image->width < $image->height) {
        $half = floor($image->width / 2.0);
    } else {
        $half = floor($image->height / 2.0);
    }
	if ($srcxyfromzero) {
		imagecopybicubic($im1, $im, 0, 0, ($image->width-$nolsize['width'])/2, ($image->height-$nolsize['height'])/2, $nolsize['width'], $nolsize['height'], $nolsize['width'], $nolsize['height']);
	} else {
		imagecopybicubic($im1, $im, 0, 0, $cx - $half, $cy - $half, $nolsize['width'], $nolsize['height'], $half * 2, $half * 2);
	}
    imagecopybicubic($im2, $im, 0, 0, $cx - $half, $cy - $half, $smallsize['width'], $smallsize['height'], $half * 2, $half * 2);
    imagecopybicubic($im3, $im, 0, 0, 0, 0, $largesize['width'], $largesize['height'], $largesize['width'], $largesize['height']);

    $fs = get_file_storage();

    $icon = array('contextid'=>$context->id, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid, 'filepath'=>'/');

    ob_start();
    if (!$imagefnc($im1, NULL, $quality, $filters)) {
        // keep old icons
        ob_end_clean();
        return false;
    }
    $data = ob_get_clean();
    imagedestroy($im1);
    $icon['filename'] = 'f1'.$imageext;
    $fs->delete_area_files($context->id, $component, $filearea, $itemid);
    $file1 = $fs->create_file_from_string($icon, $data);

    ob_start();
    if (!$imagefnc($im2, NULL, $quality, $filters)) {
        ob_end_clean();
        $fs->delete_area_files($context->id, $component, $filearea, $itemid);
        return false;
    }
    $data = ob_get_clean();
    imagedestroy($im2);
    $icon['filename'] = 'f2'.$imageext;
    $fs->create_file_from_string($icon, $data);

    ob_start();
    if (!$imagefnc($im3, NULL, $quality, $filters)) {
        ob_end_clean();
        $fs->delete_area_files($context->id, $component, $filearea, $itemid);
        return false;
    }
    $data = ob_get_clean();
    imagedestroy($im3);
    $icon['filename'] = 'f3'.$imageext;
    $fs->create_file_from_string($icon, $data);

    return $file1->get_id();
}
