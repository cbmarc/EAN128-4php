<?php
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
    
    Copyright 2012 Marc Carné 
*/

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

/* ******************************************************************** */
/*                       ENCODING MAPS                                  */
/* ******************************************************************** */
include_once('encodings/Code128C_encoding.php');


class EAN1284php {

    public function __construct() {

    }

    public function barcode_outputfile($image, $filename, $mode) {
        /* output the image */
        $mode = strtolower($mode);
        if ($mode=='jpg' || $mode=='jpeg'){
            $filename .= '.jpg';
            imagejpeg($image,$filename);
        } else if ($mode=='gif'){
            $filename .= '.gif';
            imagegif($image, $filename);
        } else {
            $filename .= '.png';
            imagepng($image, $filename);
        }
    }

    public function barcode_outimage($text, $bars, $scale = 1, $mode = "png",$total_y = 0, $space = '', $filename) {
        /* we're going to use these globals */
        global $bar_color, $bg_color, $text_color;
        global $font_loc;
        /* set defaults if not specified */
        if ($scale<1) {
            $scale=2;
        }

        $total_y=(int)$total_y;

        if ($total_y<1) {
            $total_y=(int)$scale * 60;
        }

        if (!$space) {
            $space=array('top'=>2*$scale,'bottom'=>2*$scale,'left'=>2*$scale,'right'=>2*$scale);
        }

        /* count total width based on the number of bars we need to paint */
        $xpos=0;
        for ($i=0, $len = strlen($bars);$i<$len;$i++){
            $xpos+=1*$scale;
            $width=true;
        }

        /* allocate the image */
        $total_x = $xpos + $space['right'] + $space['right'];
        $xpos=$space['left'];
        if (!function_exists('imagecreate')){
            // GD is not installed or enabled
            print "You don't have the gd2 extension enabled\n";
            return '';
        }
        $im=imagecreate($total_x, $total_y);
        /* create image stuff */
        $col_bg=imagecolorallocate($im,$bg_color[0],$bg_color[1],$bg_color[2]);
        $col_bar=imagecolorallocate($im,$bar_color[0],$bar_color[1],$bar_color[2]);
        $col_text=imagecolorallocate($im,$text_color[0],$text_color[1],$text_color[2]);
        $height=round($total_y-$space['bottom']);

        /* paint the bars */
        for ($i=0, $len = strlen($bars);$i<$len;$i++){
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
            $this->barcode_outputfile($im, $filename, $mode);
        }
        return $im;
    }

    public function create($barcode, $filename = "") {
        $barcode_data = "";
        /* Code128C character matching */
        $encoding = new Code128cEncoding();
        $code128c_codes = $encoding->getCodeMap();
        try {
            $arr_barcode = str_split($barcode, 2);

            $checksum = (int) $code128c_codes['START_DATA'];
            // Get barcode data
            $i = 1;
            foreach ($arr_barcode as $pair) {
                $i++;
                $checksum += (int) $pair * $i;

                $trans_pair = $code128c_codes[$pair];
                if ($trans_pair != '') {
                    $barcode_data .= $trans_pair;
                } else {
                    throw new Exception('Incorrect barcode format.');
                }

            }
            $checksum += (int) $code128c_codes['FNC1_DATA'] * 1;
            $checksum = $checksum % 103;

            $code_keys = array_keys($code128c_codes);
            $barcode_data .= $code128c_codes[$code_keys[$checksum]];

            // Buid final barcode
            $final_barcode = $code128c_codes['START'] . $code128c_codes['FNC1'] . $barcode_data . $code128c_codes['STOP'] . $code128c_codes['TERMINATE'];

            // Draw
            return $this->barcode_outimage($barcode, $final_barcode, 1, 'PNG', 0, array('bottom' => 15, 'top' => 5, 'left' => 15, 'right' => 15), $filename);
        } catch (Exception $e) {
            print $e;
        }
        return null;
    }

    public function createImageBuffer($barcode) {
        // Set content type
        header('Content-Type: image/png');
        header('Content-Disposition: Attachment;filename=image.png');
        //header("Content-Length: " . strlen($im));

        // Create the barcode
        $im = $this->create($barcode);
        imagepng($im);
        imagedestroy($im);
    }

    public function printImageBuffer($barcode) {
        // Set response headers to return an image
        header('Content-type: image/png');

        // Create the barcode
        $im = $this->create($barcode);
        imagepng($im);
        imagedestroy($im);
    }

    public function createImageFile($barcode, $filename) {
        if ($filename != '') {
            $this->create($barcode, $filename);
        } else {
            print 'Filename must be specified. If you need a buffered image use createImageBuffer instead.';
        }
    }

}

if (isset($_GET['barcode'])) {
    $ean128 = new EAN1284php();
    $type = isset($_GET['type']) ? $_GET['type'] : 'default';

    $barcode = $_GET['barcode'];
    if ($barcode != '') {
        if ($type == 'attach') {
            $ean128->createImageBuffer($barcode);
        }  else {
            $ean128->printImageBuffer($barcode);
        }
    }
}