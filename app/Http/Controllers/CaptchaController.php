<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaptchaController extends Controller
{
    /**
     * Generate captcha image
     */
    public function generateCaptcha()
    {
        // Create captcha text (6 characters)
        $captchaText = $this->generateRandomString(6);
        
        // Store in session
        session(['captcha' => $captchaText]);
        
        // Create image
        $width = 150;
        $height = 40;
        $image = imagecreate($width, $height);
        
        // Colors
        $bgColor = imagecolorallocate($image, 255, 255, 255); // White background
        $textColor = imagecolorallocate($image, 0, 0, 0); // Black text
        $lineColor = imagecolorallocate($image, rand(150, 220), rand(150, 220), rand(150, 220)); // Light gray lines
        
        // Add background noise (lines)
        for ($i = 0; $i < 50; $i++) {
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
        }
        
        // Add text
        $fontSize = 4;
        $x = ($width - strlen($captchaText) * imagefontwidth($fontSize)) / 2;
        $y = ($height - imagefontheight($fontSize)) / 2;
        
        imagestring($image, $fontSize, $x, $y, $captchaText, $textColor);
        
        // Add some dots for noise
        for ($i = 0; $i < 200; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $lineColor);
        }
        
        // Output image
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($image);
        
        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
    
    /**
     * Refresh captcha (same as generate but with different route)
     */
    public function refreshCaptcha()
    {
        return $this->generateCaptcha();
    }
    
    /**
     * Verify captcha via AJAX
     */
    public function verifyCaptcha(Request $request)
    {
        $userInput = $request->input('captcha');
        $sessionCaptcha = session('captcha');
        
        $isValid = $userInput && $sessionCaptcha && $userInput === $sessionCaptcha;
        
        return response()->json(['valid' => $isValid]);
    }
    
    /**
     * Generate random string for captcha
     */
    private function generateRandomString($length = 6)
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $numbers = '23456789'; // Hindari angka yang mirip huruf
        $characters = $letters . $numbers;

        do {
            $result = '';
            $hasNumber = false;

            for ($i = 0; $i < $length; $i++) {
                $char = $characters[rand(0, strlen($characters) - 1)];
                if (is_numeric($char)) {
                    $hasNumber = true;
                }
                $result .= $char;
            }

            // Ulangi kalau belum ada angka
        } while (!$hasNumber);

        return $result;
    }

}