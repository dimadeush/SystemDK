<?php

session_start();

$jpeg_quality = 80;
$count=6; // number of symbols 
$width=100; // width
$height=20; // height
$font_size_min=12; // symbol min height
$font_size_max=12; // symbol max height
$font_file="font.ttf"; // path to the file
$char_angle_min=-35; // maximum slope of the symbol to the left
$char_angle_max=35; // maximum slope of the symbol to the right
//$char_angle_shadow=5; // size of the shadow
$char_align=16; // vertical symbol aligment
$start=5; // position of first symbol in the horizontal
$interval=16; // interval between the characters
$chars="0123456789"; // list of symbols
$noise=35; // noise level
$image=imagecreatetruecolor($width, $height);
$background_color=imagecolorallocate($image, 255, 255, 255); // rbg-background color
$font_color=imagecolorallocate($image, 00, 00, 00); // rbg-shadow color
imagefill($image, 0, 0, $background_color);
$str="";
$num_chars=strlen($chars);
for ($i=0; $i<$count; $i++)
 {
   $char=$chars[rand(0, $num_chars-1)];
   $font_size=rand($font_size_min, $font_size_max);
   $char_angle=rand($char_angle_min, $char_angle_max);
   putenv('GDFONTPATH=' . realpath('.'));
   imagettftext($image, $font_size, $char_angle, $start, $char_align, $font_color, $font_file, $char);
   //imagettftext($image, $font_size, $char_angle+$char_angle_shadow*(rand(0, 1)*2-1), $start, $char_align, $background_color, $font_file, $char);
   $start+=$interval;
   $str.=$char;
 }
if ($noise)
 {
   for ($i=0; $i<$width; $i++)
    {
      for ($j=0; $j<$height; $j++)
       {
         $rgb=imagecolorat($image, $i, $j);
         $r=($rgb>>16) & 0xFF;
         $g=($rgb>>8) & 0xFF;
         $b=$rgb & 0xFF;
         $k=rand(-$noise, $noise);
         $rn=$r+255*$k/100;
         $gn=$g+255*$k/100;
         $bn=$b+255*$k/100;
         if ($rn<0) $rn=0;
         if ($gn<0) $gn=0;
         if ($bn<0) $bn=0;
         if ($rn>255) $rn=255;
         if ($gn>255) $gn=255;
         if ($bn>255) $bn=255;
         $color=imagecolorallocate($image, $rn, $gn, $bn);
         imagesetpixel($image, $i, $j , $color);
       }
    }
 }
$_SESSION["captcha"]=$str;
header('Cache-Control: no-store, no-cache, must-revalidate'); 
header('Cache-Control: post-check=0, pre-check=0', FALSE); 
header('Pragma: no-cache');
if(function_exists("imagejpeg"))
 {
   header("Content-type: image/jpeg");
   imagejpeg($image, null, $jpeg_quality);
 }
elseif(function_exists("imagegif"))
 {
   header("Content-type: image/gif");
   imagegif($image);
 }
elseif(function_exists("imagepng"))
 {
   header("Content-type: image/png");
   imagepng($image);
 }
imagedestroy($image);

?>