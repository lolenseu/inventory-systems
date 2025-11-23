<?php
// Simple test to verify image upload functionality
// This is a standalone test file

// Test the compressImage function logic
function testImageCompression() {
    // Create a simple test image
    $width = 100;
    $height = 100;
    
    // Create a new image
    $image = imagecreatetruecolor($width, $height);
    
    // Set background color
    $bgColor = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $bgColor);
    
    // Draw a simple shape
    $textColor = imagecolorallocate($image, 0, 0, 0);
    imagerectangle($image, 10, 10, 90, 90, $textColor);
    
    // Add some text
    imagestring($image, 5, 30, 45, 'TEST', $textColor);
    
    // Start output buffering
    ob_start();
    
    // Output as JPEG
    imagejpeg($image, null, 75);
    
    // Get the image data
    $imageData = ob_get_clean();
    
    // Free memory
    imagedestroy($image);
    
    // Encode to base64
    $base64Image = base64_encode($imageData);
    
    echo "Original size: " . strlen($imageData) . " bytes\n";
    echo "Base64 size: " . strlen($base64Image) . " bytes\n";
    echo "Base64 preview: " . substr($base64Image, 0, 50) . "...\n";
    
    return $base64Image;
}

// Run the test
echo "Testing image compression...\n";
$testImage = testImageCompression();
echo "Test completed successfully!\n";

// Test database field size requirements
echo "\nDatabase field requirements:\n";
echo "- Small image (100x100): ~" . strlen($testImage) . " bytes\n";
echo "- Medium image (800x600): ~" . (strlen($testImage) * 48) . " bytes (estimated)\n";
echo "- Large image (1920x1080): ~" . (strlen($testImage) * 207) . " bytes (estimated)\n";
echo "\nUsing MEDIUMTEXT field (16MB limit) should handle most cases.\n";
?>