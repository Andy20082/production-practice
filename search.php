<?php
function searchData($data, $search) {
    [$price, $name] = $search;
    
    if ($price === null && $name === null) return [];
    
    $seen = [];
    return array_values(array_filter($data, function($item) use ($price, $name, &$seen) {
        $match = ($price !== null && $item['price'] == $price) || 
                 ($name !== null && $item['name'] == $name);
        
        if ($match) {
            $key = $item['price'] . '|' . $item['name'];
            if (isset($seen[$key])) return false;
            $seen[$key] = true;
            return true;
        }
        return false;
    }));
}

// Дальше можете использовать функцию как угодно
$result = searchData($data, [100, 'Apple']);
// Или
$result = searchData($data, [null, 'Apple']);
// Или
$result = searchData($data, [100, null]);
?>