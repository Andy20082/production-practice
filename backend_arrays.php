<?php
function analyzeData($data) {
    // Извлекаем все элементы
    $elements = [];
    foreach ($data['category'] as $key => $value) {
        $elements[] = [
            'id' => $key,
            'priority' => (int)$value['priority'],
            'user_count' => $value['views']['user_count'],
            'bot_count' => $value['views']['bot_count']
        ];
    }
    
    // Сортируем по priority
    usort($elements, fn($a, $b) => $a['priority'] <=> $b['priority']);
    
    // Статистика
    $botCounts = array_column($elements, 'bot_count');
    
    return [
        'max_bot' => max($botCounts),
        'min_bot' => min($botCounts),
        'sorted' => $elements
    ];
}

// Использование
$result = analyzeData($data);

echo "Максимальный bot_count: {$result['max_bot']}\n";
echo "Минимальный bot_count: {$result['min_bot']}\n";
echo "Сортировка по priority:\n";
foreach ($result['sorted'] as $item) {
    echo "  priority: {$item['priority']}, user_count: {$item['user_count']}, bot_count: {$item['bot_count']}\n";
}
?>