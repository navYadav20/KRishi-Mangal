<?php
function get_translation($key) {
    // Load the appropriate language file
    $lang = $_SESSION['language'] ?? 'en';
    // Use a path relative to the project root
    $translations = include dirname(__DIR__) . '/lang/' . $lang . '.php';
    
    // Return the translation if it exists, otherwise return the key
    return $translations[$key] ?? $key;
}

// Helper function to translate with placeholders
function translate($key, $replacements = []) {
    $text = get_translation($key);
    foreach ($replacements as $placeholder => $value) {
        $text = str_replace("{{$placeholder}}", $value, $text);
    }
    return $text;
}