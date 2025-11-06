<?php

if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = 'USD')
    {
        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'Y-m-d')
    {
        if (!$date) return null;
        
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('generate_random_string')) {
    function generate_random_string($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $randomString;
    }
}

if (!function_exists('get_user_initial')) {
    function get_user_initial($name)
    {
        return strtoupper(substr($name, 0, 1));
    }
}

if (!function_exists('calculate_percentage')) {
    function calculate_percentage($part, $total)
    {
        if ($total == 0) return 0;
        return round(($part / $total) * 100, 2);
    }
}