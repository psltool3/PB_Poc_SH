<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$captcha_code = substr(md5(rand()), 0, 5);
$_SESSION['captcha'] = $captcha_code;

header('Content-Type: image/jpeg');
header('Cache-Control: no-cache, no-store, must-revalidate');

$width = 120;
$height = 40;
$image = imagecreatetruecolor($width, $height);

$background_color = imagecolorallocate($image, 0, 0, 0);
$text_color = imagecolorallocate($image, 255, 255, 255);
$line_color = imagecolorallocate($image, 39, 152, 213);

imagefill($image, 0, 0, $background_color);

for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

$font = __DIR__ . '/arialbd.ttf';
if (file_exists($font) && function_exists('imagettftext')) {
    imagettftext($image, 18, rand(-5, 5), 20, 28, $text_color, $font, $captcha_code);
} else {
    imagestring($image, 5, 35, 12, $captcha_code, $text_color);
}

imagejpeg($image);
imagedestroy($image);
?>
