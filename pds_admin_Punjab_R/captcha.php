<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$captcha_code = substr(md5(rand()), 0, 4);
$_SESSION['captcha'] = $captcha_code;

header("Content-Type: image/svg+xml");

$lines = '';
for ($i = 0; $i < 5; $i++) {
    $x1 = rand(0, 140);
    $y1 = rand(0, 45);
    $x2 = rand(0, 140);
    $y2 = rand(0, 45);
    $lines .= "  <line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" stroke=\"rgb(39,152,213)\" stroke-width=\"2\" />\n";
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<svg width="140" height="45" xmlns="http://www.w3.org/2000/svg">
  <rect width="100%" height="100%" fill="#060505"/>
<?php echo $lines; ?>
  <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="25" font-weight="bold" letter-spacing="5"><?php echo $captcha_code; ?></text>
</svg>
