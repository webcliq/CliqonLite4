<?php

/**
 *
 * @name image-text.php
 * @version 1.3
 * @author Jason Lau @ JasonLau.biz
 * @copyright 2011-2012
 * @license GNU/GPL Version 3+
 * @uses This app uses PHP and GD to generate image text in transparent PNG format from a font
 * file.
 * @example <img src="this_file.php?text=My Text&amp;font=/fonts/Academy-Engraved-LET-Regular.ttf&amp;transform=uc&amp;size=20&amp;angle=0&amp;color=ffffff&amp;padding=8&amp;spacing=2" />
 * @var text - The text to be converted to an image.
 * @var transform - Text transform. uc = uppercase, lc = lowercase, ucf = uppercase the first
 * letter in the phrase, ucw = uppercase the first letter in each word.
 * @var font - The path to the font file.
 * @var color - A six character hexidecimal color code. Example: FFFFFF (white)
 * @var size - The font size. Example: 24
 * @var angle - The textbox angle. Example: 0
 * @var padding - The padding around the text. Example: 4
 * @var spacing - The spacing between letters. Example: 2
 * @see DEFAULT_FONT constant on line 26 sets the default font. ALWAYS DEFINE A DEFAULT FONT!
 *
 */

define("DEFAULT_FONT", "browa.ttf");
define("DEFAULT_COLOR", "000000");
define("DEFAULT_SIZE", 24);
define("DEFAULT_ANGLE", 0);
define("DEFAULT_PADDING", 10);
define("DEFAULT_SPACING", 0);

$text = $_GET['text'];
if(isset($_GET['transform'])):
    switch ($_GET['transform']){
	case 'uc':
    $text = strtoupper($_GET['text']);
	break;

	case 'lc':
    $text = strtolower($_GET['text']);
	break;

	case 'ucf':
    $text = ucfirst($_GET['text']);
	break;

    case 'ucw':
    $text = ucwords($_GET['text']);
	break;
}
endif;

$font = $_GET['font'] ? $_GET['font'] : DEFAULT_FONT;
$color = (strlen($_GET['color']) == 6 && ctype_alnum($_GET['color']))  ? "0x" . $_GET['color'] : "0x" . DEFAULT_COLOR;
$size = (is_numeric($_GET['size'])) ? $_GET['size'] : DEFAULT_SIZE;
$angle = (is_numeric($_GET['angle'])) ? $_GET['angle'] : DEFAULT_ANGLE;
$padding = (is_numeric($_GET['padding'])) ? $_GET['padding']*4 : DEFAULT_PADDING*4;
$spacing = (is_numeric($_GET['spacing'])) ? $_GET['spacing'] : DEFAULT_SPACING;
$text_dimensions = calculateTextDimensions($text, $font, $size, $angle, $spacing);
$image_width = $text_dimensions["width"] + $padding;
$image_height = $text_dimensions["height"] + $padding;
header("content-type: image/png");
$new_image = imagecreatetruecolor($image_width, $image_height);
ImageFill($new_image, 0, 0, IMG_COLOR_TRANSPARENT);
imagesavealpha($new_image, true);
imagealphablending($new_image, false);
imagettftextSp($new_image, $size, $angle, $text_dimensions["left"] + ($image_width / 2) - ($text_dimensions["width"] / 2), $text_dimensions["top"] + ($image_height / 2) - ($text_dimensions["height"] / 2), $color, $font, $text, $spacing);
imagepng($new_image);
imagedestroy($new_image); 

function imagettftextSp($image, $size, $angle, $x, $y, $color, $font, $text, $spacing = 0)
{
    if ($spacing == 0)
    {
        imagettftext($image, $size, $angle, $x, $y, $color, $font, $text);
    }
    else
    {
        $temp_x = $x;
        for ($i = 0; $i < strlen($text); $i++)
        {
            $bbox = imagettftext($image, $size, $angle, $temp_x, $y, $color, $font, $text[$i]);
            $temp_x += $spacing + ($bbox[2] - $bbox[0]);
        }
    }
}

function calculateTextDimensions($text, $font, $size, $angle, $spacing) {
    $rect = imagettfbbox($size, $angle, $font, $text);
    $minX = min(array($rect[0],$rect[2],$rect[4],$rect[6]));
    $maxX = max(array($rect[0],$rect[2],$rect[4],$rect[6]));
    $minY = min(array($rect[1],$rect[3],$rect[5],$rect[7]));
    $maxY = max(array($rect[1],$rect[3],$rect[5],$rect[7]));
    $spacing = ($spacing*(strlen($text)+2));
    return array(
     "left"   => abs($minX) - 1,
     "top"    => abs($minY) - 1,
     "width"  => ($maxX - $minX)+$spacing,
     "height" => $maxY - $minY,
     "box"    => $rect
    );
} 

?>