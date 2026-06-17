<?php
function searchData($data, $search) {
    [$searchPrice, $searchName] = $search;
    
    if ($searchPrice === null && $searchName === null) {
        return [];
    }
    
    $result = [];
    $seen = [];
    
    foreach ($data as $key => $item) {
        $matchPrice = $searchPrice !== null && isset($item['price']) && $item['price'] == $searchPrice;
        $matchName = $searchName !== null && isset($item['name']) && $item['name'] == $searchName;
        
        if ($matchPrice || $matchName) {
            $uniqueKey = $item['price'] . '|' . $item['name'];
            if (!isset($seen[$uniqueKey])) {
                $result[$key] = $item;
                $seen[$uniqueKey] = true;
            }
        }
    }
    
    return $result;
}
?>