<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      index.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
header("Expires: Mon, 30 Nov 2009 00:39:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0");
header("Cache-Control: max-age=0");
header("Pragma: no-cache");
$folder = false;
$slideWidth = 400;
$title = 'Title';
$theme = 'systemdk';

if (!empty($_GET['dir'])) {
    $folder = trim(strip_tags($_GET['dir']));
}

if (!empty($_GET['width'])) {
    $slideWidth = intval($_GET['width']);
}

if (!empty($folder) and is_dir('userfiles/image/' . $folder)) {
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'
         . '<html>'
         . '<head>'
         . '<title>' . $title . '</title>'
         . '<script type="text/javascript" src="/flowplayer/jquery.min.js"></script>'
         . '<link type="text/css" href="/themes/' . $theme . '/css/magnific-popup.css" rel="stylesheet">'
         . '<script type="text/javascript" src="/themes/' . $theme . '/js/jquery.magnific-popup.min.js"></script>'
         . '<script type="text/javascript" src="/themes/' . $theme . '/js/jquery.bxslider.min.js"></script>'
         . '<link type="text/css" href="/themes/' . $theme . '/css/jquery.bxslider.css" rel="stylesheet">'
         . '</head>'
         . '<body style="margin: 0;">'
         . '<table border="0" cellpadding="0"  cellspacing="0"><tr align="left"><td><div class="slider">';
    $handle = opendir('userfiles/image/' . $folder);
    while (false !== ($file = readdir($handle))) {

        if ((preg_match("/[.]/i", $file)) && !in_array($file, ['.', '..'])) {
            echo '<div class="slide">';
            echo '<a class="popup-link" href="/userfiles/image/' . $folder . '/' . $file . '"><img src="/userfiles/image/' . $folder . '/' . $file
                 . '" border="0" alt=""></a>';
            echo '</div>';
        }

    }
    closedir($handle);
    echo '</div></td></tr></table>'
         . '<script type="text/javascript">'
         . "$(window).load(function(){"
         . "$('.slider').bxSlider({"
         . 'slideWidth: ' . $slideWidth . ','
         . 'minSlides: 2,'
         . 'maxSlides: 2,'
         . 'slideMargin: 10,'
         . 'auto: true,'
         . 'autoControls: true'
         . '});'
         . "$('.popup-link').magnificPopup({"
         . "type: 'image'"
         . '});'
         . '});'
         . '</script>'
         . '</body>'
         . '</html>';
}