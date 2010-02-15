<?php
/**
 * Image helper class.
 *
 * @version		$Id: image.php 242 2010-02-10 23:06:09Z Shaun $
 *
 * @package		System
 * @subpackage	Helpers
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */

class image_Core {
	const GIF	= 0x00000001;
    const PNG	= 0x00000010;
    const JPG	= 0x00000100;
    const JPEG	= 0x00000100;
    
    public static function is_animated($input) {
		if(!file_exists($input)) {
			$filecontents = $input;
		} else {
			$filecontents = file_get_contents($input);
		}
	
		$str_loc=0;
		$count=0;
		while ($count < 2) {
			$where1=strpos($filecontents,"\x00\x21\xF9\x04",$str_loc);
			if ($where1 === FALSE) {
				break;
			} else {
				$str_loc=$where1+1;
				$where2=strpos($filecontents,"\x00\x2C",$str_loc);
			
				if ($where2 === FALSE) {
					break;
				} else {
					if ($where1+8 == $where2) {
						$count++;
					}
					$str_loc=$where2+1;
				}
			}
		}
		
		if ($count > 1) {
			return(true);
		} else {
			return(false);
		}
	}
	
	public static function mime($type) {
		switch($type) {
			case self::JPEG:	return "image/jpeg";
			case self::PNG:		return "image/png";
			default:			return "image/gif";
		}
	}
	
	public static function ext($type) {
		switch($type) {
			case self::JPEG:	return "jpg";
			case self::PNG:		return "png";
			default:			return "gif";
		}
	}
	
	public static function func($type) {
		switch($type) {
			case self::JPEG:	return "imagejpeg";
			case self::PNG:		return "imagepng";
			default:			return "imagegif";
		}
	}
	
	/* SQUARE FUNCTION DOES NOT WORK */
 	public static function Square($im, $clip=true, $noClipBG=array(000,000,000)) {
 		$w = imagesx($im);
 		$h = imagesy($im);
 		
		if($width < $height) {
			return self::ScaleHeight($im, $w, $h, 1, 1, $clip, $noClipBG);
		} else {
			return self::ScaleWidth($im, $w, $h, 1, 1, $clip, $noClipBG);
		}
 	}
 	
 	public static function Ratio($im, $w, $h, $clip=true, $noClipBG=array(000,000,000)) {
        $width = imagesx($im);
        $height = imagesy($im);
                
		if($w > $h) {
			return self::ScaleWide($im, $width, $height, $w, $h, $clip, $noClipBG);
		} elseif($w == $h) {
			return self::Square($im, $clip, $noClipBG);
		} else {
			return self::ScaleHigh($im, $width, $height, $w, $h, $clip, $noClipBG);			
		}
 	}
 	
 	public static function ScaleWide($im, $w, $h, $r_w, $r_h, $clip, $noClipBG) {
		if(($w / $h) < ($r_w / $r_h)) {
			return self::ScaleHeight($im, $w, $h, $r_w, $r_h, $clip, $noClipBG);
		} else {
			return self::ScaleWidth($im, $w, $h, $r_w, $r_h, $clip, $noClipBG);
		} 	
 	}
 	
 	public static function ScaleHigh($im, $w, $h, $r_w, $r_h, $clip, $noClipBG) {
		if(($w / $h) < ($r_w / $r_h)) {
			return self::ScaleHeight($im, $w, $h, $r_w, $r_h, $clip, $noClipBG);
		} else {
			return self::ScaleWidth($im, $w, $h, $r_w, $r_h, $clip, $noClipBG);
		} 		
 	}
 	
 	public static function ScaleHeight($im, $w, $h, $r_w, $r_h, $clip, $noClipBG) {
 		if($clip) {
			$n_width = $w;
			$n_height = ($w*$r_h)/$r_w;
			$x = 0;
			$y = intval(($h - $n_height) / 2); 
			return self::Crop($im, $x, $y, $n_width, $n_height, $noClipBG);
 		} else {			
			$n_height = $h;
			$n_width = ($h*$r_w)/$r_h;
	 		return self::ResizeAndPad($im, $n_width, $n_height, $noClipBG);
 		}
 	}
 	
 	public static function ScaleWidth($im, $w, $h, $r_w, $r_h, $clip, $noClipBG) {
 		if($clip) {
			$n_height = $h;
			$n_width = ($h*$r_w)/$r_h;
			$x = intval(($w - $n_width) / 2);
			$y = 0;
			return self::Crop($im, $x, $y, $n_height, $n_width, $noClipBG);		
 		} else {
			$n_width = $w;
			$n_height = ($w*$r_h)/$r_w;
	 		return self::ResizeAndPad($im, $n_width, $n_height, $noClipBG);
 		}
 	}
 	
 	public static function Crop($im, $start_x, $start_y, $run_x, $run_y, $noClipBG) {
        $width = imagesx($im);
        $height = imagesy($im);
        
        if($start_x > $width) {
        	$start_x = 0;
        }
        
        if($start_y > $height) {
        	$start_y = 0;
        }
        
        if(($start_x + $run_x) > $width) {
        	$run_x = $width-$start_x;
        }
        
        if(($start_y + $run_y) > $height) {
        	$run_y = $height-$start_y;
        }

		return self::Resize($im, $run_x, $run_y, $run_x, $run_y, 0, 0, $start_x, $start_y, $bgColor);
 	}
 	
 	public static function ResizeAndPad($im, $bounding_w, $bounding_h, $noClipBG) {
 		$width = imagesx($im);
		$height = imagesy($im);
	
 		if($bounding_w > $width) {
 			$x = intval(($bounding_w - $width) / 2);
 		} else {
 			$x = 0;
 		}
 		
 		if($bounding_h > $height) {
 			$y = intval(($bounding_h - $height) / 2);
 		} else {
 			$y = 0;
 		}
 		
 		return self::Pad($im, $bounding_w, $bounding_h, $x, $y, $noClipBG);
 	}
 	
 	public static function ScaleToBounding($im, $bounding_w, $bounding_h, $alpha = false) {
		$width = imagesx($im);
		$height = imagesy($im);
		
		if($width <= $bounding_w AND $height <= $bounding_h) {
			return $im;
		}
		
		if($bounding_w > $bounding_h) {
			if($width > $height) {
				$n_width = $bounding_w;
				$n_height = intval(($height * $bounding_w) / $width);
			} else {
				$n_height = $bounding_h;
				$n_width = intval(($width * $bounding_h) / $height);                
			}
		} else {
			if($width > $height) {
				$n_height = $bounding_h;
				$n_width = intval(($width * $bounding_h) / $height);                
			} else {
				$n_width = $bounding_w;
				$n_height = intval(($height * $bounding_w) / $width);
			}
		}
        
		if($width < $bounding_w AND $height < $bounding_h) {
			$n_width = $width;
			$n_height = $height;
		}
		
		// Fuck it...maybe one day...in the meantime:
		$n_width = $bounding_w;
		$n_height = intval(($height * $bounding_w) / $width);
				
		if($alpha) { 
			return self::ResizeAlpha($im, $n_width, $n_height, $width, $height);
		} else {
			return self::Resize($im, $n_width, $n_height, $width, $height);
		}
 	}
 	
	public static function ScaleToWidth($im, $n_width, $alpha = false) {
		$width = imagesx($im);
		$height = imagesy($im);
		
		$n_height = intval(($height * $n_width) / $width);

		if($alpha) { 
			return self::ResizeAlpha($im, $n_width, $n_height, $width, $height);
		} else {
			return self::Resize($im, $n_width, $n_height, $width, $height);
		}
	}

	public static function Resize($im, $w, $h, $o_w, $o_h, $dst_x=0, $dst_y=0, $src_x=0, $src_y=0, $bgColor=array(000,000,000)) {
		$image_p = imagecreatetruecolor($w, $h);
		$color = imagecolorallocate($image_p, $bgColor[0], $bgColor[1], $bgColor[2]);
		imagefill($image_p, 0, 0, $color);
		imagecopyresampled($image_p, $im, $dst_x, $dst_y, $src_x, $src_y, $w, $h, $o_w, $o_h);
		return $image_p;
	}
	
	public static function ResizeAlpha($im, $w, $h, $o_w, $o_h, $dst_x=0, $dst_y=0, $src_x=0, $src_y=0) {
		$image_p = imagecreatetruecolor($w, $h);
		
		imagesavealpha($image_p, true);
		
		imagefill($image_p, 0, 0, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
		
		imagecopyresampled($image_p, $im, $dst_x, $dst_y, $src_x, $src_y, $w, $h, $o_w, $o_h);
		return $image_p;
	}
	
	public static function AddLayer($im, $im2, $x=0, $y=0, $opacity=100) {
		if($opacity <= 0) {
			return $im;
		} elseif($opacity >= 100) {
			imagecopy($im, $im2, $x, $y, 0, 0, imagesx($im2), imagesy($im2));
			imagedestroy($im2);
			return $im;
		} else {
			imagealphablending($im, true);
			imagealphablending($im2, true);
			imagecopymerge ($im, $im2, $x, $y, 0, 0, imagesx($im2), imagesy($im2), $opacity);
			imagedestroy($im2);
			return $im;
		}
	}
	
	public static function Pad($im, $w, $h, $x, $y, $bgColor) {
		$image_p = imagecreatetruecolor($w, $h);
		$color = imagecolorallocate($image_p, $bgColor[0], $bgColor[1], $bgColor[2]);
		imagefill($image_p, 0, 0, $color);
		return self::AddLayer($image_p, $im, $x, $y);
	}
	
	public static function Rotate($im, $degrees=90,$bg=0) {
		$im = imagerotate($im,$degrees,$bg);
		return $im;
	}
	
	public static function ZoomAndClipToBounding($im, $w, $h) {
		$image_w = imagesx($im);
		$image_h = imagesy($im);
		
		if($w > $h) {
			if($image_w > $image_h) {
				if(($image_h / $image_w) >= ($h / $w)) {
					// Both sizes are wide, not tall
					// Dest Ratio > Source Ratio
					// Set Width, Clip Height
					$im = self::ZoomWidthClipHeight($im, $w, $h);
				} else {
					// Both sizes are wide, not tall
					// Source Ratio > Dest Ratio
					// Set Height, Clip Width
					$im = self::ZoomHeightClipWidth($im, $w, $h);
				}
			} else {
					// Destination is Wide, Source is Long
					// Ratio's don't matter in this case
					// Set Width, Clip Height
					$im = self::ZoomWidthClipHeight($im, $w, $h);
			}		
		} else {
			if($image_h > $image_w) {
				if(($image_w  / $image_h) >= ($w / $h)) {
					// Both sizes are tall, not wide
					// Source Ratio > Dest Ratio
					// Set Height, Clip Width
					$im = self::ZoomHeightClipWidth($im, $w, $h);
				} else {
					// Both sizes are tall, not wide
					// Dest Ratio > Source Ratio
					// Set Width, Clip Height
					$im = self::ZoomWidthClipHeight($im, $w, $h);
				}
			} else {
					// Destination is Tall, Source is Wide
					// Ratio's don't matter in this case
					// Set Height, Clip Width
					$im = self::ZoomHeightClipWidth($im, $w, $h);
			}		
		}
		
		return $im;
	}
	
	public static function ZoomHeightClipWidth($im, $w, $h) {
		$image_w = imagesx($im);
		$image_h = imagesy($im);
		
		$new_h = $h;
		$new_w = intval(($image_w * $new_h) / $image_h);
		
		$y = 0;
		$x = intval(($new_w - $w) / 2);
		

		$im = self::Resize($im, $new_w, $new_h, $image_w, $image_h);
		return self::Resize($im, $w, $h, $w, $h, 0, 0, $x, $y);
	}
	
	public static function ZoomWidthClipHeight($im, $w, $h) {
		$image_w = imagesx($im);
		$image_h = imagesy($im);
		
		$new_w = $w;
		$new_h = intval(($image_h * $new_w) / $image_w);

		$y = intval(($new_h - $h) / 2);
		$x = 0;
		
		$im = self::Resize($im, $new_w, $new_h, $image_w, $image_h);
		return self::Resize($im, $w, $h, $w, $h, 0, 0, $x, $y);
	}
	
	public static function toString(&$im, $type='jpeg') {
		$type = ltrim(rtrim(strtolower($type)));
		ob_start();
			switch($type) {
				case 'gif':
					imagegif($im);
					break;
				case 'png':
					imagepng($im);
					break;
				case 'jpeg':
				case 'jpg':
				default:
					imagejpeg($im, null, 100);
					break;
			}
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	public static function AddLayerAnimation($imageString, $image) {
		$prefix = md5($file.time().$_SERVER['REMOTE_ADDR']);
		$original = '/tmp/'.$prefix.".original.gif";
		$sample = '/tmp/'.$prefix.".sample.png";
		$final = '/tmp/'.$prefix.".final.gif";
		file_put_contents($original, $imageString);
		imagepng($image, $sample);
		
		$cmd = "convert {$original} -draw \"image Over 0,0, 0,0 '{$sample}'\" {$final}";
		
		$x = 0;
		$imageString = null;
		while(@imagecreatefromstring($imageString) == false) {
			exec($cmd, $output);
			$imageString = file_get_contents($final);
			$x++;
			if($x > 20) {
				// fuck it.
				break;
			}
		}
		
		unlink($original);
		unlink($sample);
		unlink($final);
		
		return $imageString;
	}
	
	public static function ResizeAnimationToBounding($imageString,  $bounding_w, $bounding_h) {
		$im = imagecreatefromstring($imageString);
		$width = imagesx($im);
		$height = imagesy($im);
		imagedestroy($im);
		
		if($width > $height) {
			$n_width = $bounding_w;
			$n_height = intval(($height * $bounding_w) / $width);
		} else {
			$n_height = $bounding_h;
			$n_width = intval(($width * $bounding_h) / $height);				
		}
		  
		if($width < $bounding_w AND $height < $bounding_h) {
			$n_width = $width;
			$n_height = $height;
		}
		
		$prefix = md5($file.rand(0,1000).time().$_SERVER['REMOTE_ADDR']);
		$orginal = '/tmp/'.$prefix.".original.gif";
		$resized = '/tmp/'.$prefix.".resize.gif";
		
		file_put_contents($orginal, $imageString);
	
		//$cmd = "gifsicle -O -f --resize {$n_width}x{$n_height} {$orginal} > {$resized}";
		//exec($cmd." 2>&1", $output);
		$cmd = "convert {$orginal} -coalesce -resize {$n_width}x{$n_height} {$resized}";
		
		$x = 0;
		$imageString = null;
		while(@imagecreatefromstring($imageString) == false) {
			exec($cmd, $output);
			$imageString = file_get_contents($resized);
			$x++;
			if($x > 20) {
				// fuck it.
				break;
			}
		}
		
		unlink($orginal);
		unlink($resized);
		
		return $imageString;
	}
	
} // End Image Helper Class