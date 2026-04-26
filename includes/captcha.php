<?php
// ==========================================
// FILE: includes/captcha.php
// CAPTCHA Image Generator with Strikethrough & Distortion
// PHP 5.x Compatible
// ==========================================

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate random 5-character code (A-Z, 0-9)
$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha_code = '';
for ($i = 0; $i < 5; $i++) {
    $captcha_code .= $characters[rand(0, strlen($characters) - 1)];
}

// Store CAPTCHA in session (uppercase for case-insensitive comparison)
$_SESSION['captcha'] = $captcha_code;

// Create image
$width = 150;
$height = 50;
$image = imagecreatetruecolor($width, $height);

// Define colors
$bg_color = imagecolorallocate($image, 245, 245, 245); // Light gray background
$text_color = imagecolorallocate($image, 0, 0, 0); // Black text
$line_color = imagecolorallocate($image, 200, 200, 200); // Light gray for lines
$noise_color = imagecolorallocate($image, 100, 100, 100); // Gray for noise
$strikethrough_color = imagecolorallocate($image, 255, 0, 0); // Red for strikethrough

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Add random noise (dots)
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

// Add random lines
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// Add each character with random rotation and position
$x = 15;
for ($i = 0; $i < strlen($captcha_code); $i++) {
    $char = $captcha_code[$i];
    
    // Random font size (18-24)
    $font_size = rand(18, 24);
    
    // Random angle (-15 to 15 degrees)
    $angle = rand(-15, 15);
    
    // Random Y position (30-40)
    $y = rand(32, 42);
    
    // Load font (using built-in font - 5 is largest built-in)
    // For better quality, you can use a TTF font, but built-in works
    $font = 5;
    
    // Calculate bounding box for built-in font
    $char_width = imagefontwidth($font);
    $char_height = imagefontheight($font);
    
    // For built-in fonts, we can't rotate, so we'll use imagestring
    // For better distortion, we'll use imagettftext if GD has freetype support
    if (function_exists('imagettftext')) {
        // Try to use TTF font if available (create fonts directory)
        $font_file = dirname(__FILE__) . '/../assets/fonts/arial.ttf';
        if (file_exists($font_file)) {
            imagettftext($image, $font_size, $angle, $x, $y, $text_color, $font_file, $char);
            $x += $font_size - 5;
        } else {
            // Fallback to built-in font
            imagestring($image, $font, $x, $y - 15, $char, $text_color);
            $x += $char_width + 8;
        }
    } else {
        // No TTF support, use built-in font
        imagestring($image, $font, $x, $y - 15, $char, $text_color);
        $x += $char_width + 8;
    }
}

// Add strikethrough lines
$strikethrough_y1 = rand(20, 30);
$strikethrough_y2 = rand(20, 30);
imageline($image, 5, $strikethrough_y1, $width - 5, $strikethrough_y2, $strikethrough_color);

// Add second strikethrough for more effect
$strikethrough_y3 = rand(35, 45);
$strikethrough_y4 = rand(35, 45);
imageline($image, 5, $strikethrough_y3, $width - 5, $strikethrough_y4, $strikethrough_color);

// Add border
imagerectangle($image, 0, 0, $width - 1, $height - 1, $text_color);

// Output image as PNG
header('Content-Type: image/png');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

imagepng($image);
imagedestroy($image);
?>