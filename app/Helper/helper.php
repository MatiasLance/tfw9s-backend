<?php

if(!function_exists('containsCentralCoast')){
    function containsCentralCoast(string $name, bool $fuzzy = false): bool 
    {
        $name = trim($name);
        
        if ($fuzzy) {
            $patterns = [
                '/central\s+coast/i',
                '/central-coast/i',
                '/cental\s+coast/i',
                '/c\.\s*c\./i'
            ];
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $name)) {
                    return true;
                }
            }
            return false;
        }
        
        return stripos($name, 'central coast') !== false;
    }
}