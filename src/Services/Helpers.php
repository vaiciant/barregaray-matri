<?php

namespace App\Services;

class Helpers
{
    public static function generateRandomString($length = 6, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function sanitizeTextField($value): string
    {
        $value = trim($value);
        $value = stripslashes($value);
        return htmlspecialchars($value, ENT_QUOTES);
    }
}