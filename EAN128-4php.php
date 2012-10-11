<?
/*
   This file is part of EAN128-4php.

    EAN128-4php is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    EAN128-4php is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with EAN128-4php.  If not, see <http://www.gnu.org/licenses/>.
    
    Copyright 2012 Marc Carné - http://www.garageofapps.com
*/

/* ******************************************************************** */
/*                       ENCODING MAPS                                  */
/* ******************************************************************** */					
include_once('encodings/Code128C_encoding.php');

/* ******************************************************************** */
/*                          COLORS                                      */
/* ******************************************************************** */
$bar_color=Array(0,0,0);
$bg_color=Array(255,255,255);
$text_color=Array(0,0,0);

/* ******************************************************************** */
/*                            FONT                                      */
/* ******************************************************************** */
$font_loc=dirname(__FILE__)."/"."FreeSans.ttf";

function barcode_outputfile($image, $filename, $mode) {
	/* output the image */
    $mode=strtolower($mode);
    if ($mode=='jpg' || $mode=='jpeg'){
		$filename = $filename . ".jpg";
		imagejpeg($image,$filename);
    } else if ($mode=='gif'){
		$filename = $filename . ".gif";
		imagegif($image, $filename);
    } else {
		$filename = $filename . ".png";
		imagepng($image, $filename);
    }
}

function barcode_outimage($text, $bars, $scale = 1, $mode = "png",$total_y = 0, $space = '', $filename) {
	/* we're going to use these globals */
    global $bar_color, $bg_color, $text_color;
    global $font_loc;
    /* set defaults if not specified */
    if ($scale<1) $scale=2;
    $total_y=(int)($total_y);
    if ($total_y<1) $total_y=(int)$scale * 60;
    if (!$space)
      $space=array('top'=>2*$scale,'bottom'=>2*$scale,'left'=>2*$scale,'right'=>2*$scale);
    
    /* count total width based on the number of bars we need to paint */
    $xpos=0;
    for ($i=0;$i<strlen($bars);$i++){
		$xpos+=1*$scale;
		$width=true;
    }

    /* allocate the image */
    $total_x=( $xpos )+$space['right']+$space['right'];
    $xpos=$space['left'];
    if (!function_exists("imagecreate")){
    	// GD is not installed or enabled
		print "You don't have the gd2 extension enabled\n";
		return "";
    }
    $im=imagecreate($total_x, $total_y);
    /* create image stuff */
    $col_bg=ImageColorAllocate($im,$bg_color[0],$bg_color[1],$bg_color[2]);
    $col_bar=ImageColorAllocate($im,$bar_color[0],$bar_color[1],$bar_color[2]);
    $col_text=ImageColorAllocate($im,$text_color[0],$text_color[1],$text_color[2]);
    $height=round($total_y-$space['bottom']);

    /* paint the bars */
    for ($i=0;$i<strlen($bars);$i++){
		$val=strtolower($bars[$i]);
		$h=$height;
		if ($val == "1") {
			imagefilledrectangle($im, $xpos, $space['top'], $xpos, $h, $col_bar);
		}
		$xpos+=1*$scale;
    }
    /* write out the text */
	$fontsize=$scale * 8;
	$fontheight=$total_y-($fontsize/2.5)+2;
	@imagettftext($im, $fontsize, 0, ($total_x / 2)-(strlen($text)*$fontsize/2.68), $fontheight, $col_text, $font_loc, $text);
 	
 	if ($filename != "") {
	 	/* write the file */
	 	barcode_outputfile($im, $filename, $mode);
	 	imagedestroy($im);
 	}
}

function create($barcode, $filename) {
	$barcode_data = "";
	/* Code128C character matching */
	$code128c_codes = getCode128CMap();
	try {
		$arr_barcode = str_split($barcode, 2);
	
		$checksum = intval($code128c_codes["START_DATA"]);
		// Get barcode data
		$i = 1;
		foreach ($arr_barcode as $pair) {
			$i++;
			$checksum += (intval($pair) * $i);
			$trans_pair = $code128c_codes[$pair];
			if ($trans_pair != "") {
				$barcode_data .= $trans_pair;
			} else {
				throw new Exception("Incorrect barcode format.");
			}
		}
		$checksum += (intval($code128c_codes["FNC1_DATA"])*1);
		$checksum = $checksum % 103;
		$barcode_data .= $code128c_codes[str_pad($checksum, 2, '0', STR_PAD_LEFT)];
		// Buid final barcode
		$final_barcode = $code128c_codes["START"] . $code128c_codes["FNC1"] . $barcode_data . $code128c_codes["STOP"] . $code128c_codes["TERMINATE"];
	
		// Draw
		barcode_outimage($barcode, $final_barcode, 1, "PNG", 0, array("bottom" => 15, "top" => 5, "left" => 15, "right" => 15), $filename);
	} catch (Exception $e) {
		print $e;
	}
}

function createImageBuffer($barcode) {
	// Set content type
	$cachefile = "cacheBarcode";
	header('Content-Type: image/png');
	header('Content-Disposition: Attachment;filename=image.png');
	create($barcode, $cachefile);
	try {
		$fp = fopen($cachefile . ".png", 'rb'); // stream the image directly from the cachefile
		fpassthru($fp);
	} catch (Exception $e) {
		print $e;
	}
}

function createImageFile($barcode, $filename) {
	if ($filename != "") {
		create($barcode, $filename);
	} else {
		print "Filename must be specified. If you need a buffered image use createImageBuffer instead.";
	}
}

if (!isset($_GET["barcode"])) {
	$barcode = $_GET['barcode'];
	if ($barcode != "") {
		echo createImageBuffer($barcode);
	}
}
?>}