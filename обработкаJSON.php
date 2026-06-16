<?php

/**
 * Обрабатывает JSON-ответ от API и преобразует изображения в соответствии с условиями
 * 
 * @param string $jsonString JSON строка с данными
 * @param string $outputDir Директория для сохранения изображений
 * @return array Массив $data с обработанными изображениями
 */
function processApiResponse($jsonString, $outputDir = '/image_folder') {
    // Декодируем JSON
    $response = json_decode($jsonString, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Ошибка декодирования JSON: ' . json_last_error_msg());
    }
    
    // Проверяем наличие ключа 'call'
    if (!isset($response['call']) || !is_array($response['call'])) {
        throw new Exception('Отсутствует ключ "call" или он не является массивом');
    }
    
    $result = [];
    $callData = $response['call'];
    
    // Создаем директорию, если её нет
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }
    
    // Обрабатываем каждый продукт
    foreach ($callData as $productName => $productData) {
        // Проверяем условие tradeble = true
        if (!isset($productData['tradeble']) || $productData['tradeble'] !== 'true') {
            continue; // Пропускаем продукты с tradeble != true
        }
        
        // Проверяем наличие необходимых данных
        if (!isset($productData['image_name']) || empty($productData['image_name'])) {
            continue; // Пропускаем если нет имени изображения
        }
        
        if (!isset($productData['image']['base64']) || empty($productData['image']['base64'])) {
            continue; // Пропускаем если нет base64 данных
        }
        
        $imageName = $productData['image_name'];
        $base64Data = $productData['image']['base64'];
        $link = isset($productData['image']['link']) ? $productData['image']['link'] : '';
        $name = isset($productData['name']) ? $productData['name'] : $productName;
        
        // Извлекаем и декодируем base64 данные
        $imageData = extractBase64Image($base64Data);
        
        if ($imageData === null) {
            continue; // Пропускаем если не удалось извлечь изображение
        }
        
        // Генерируем имя файла
        $filename = $imageName . '.jpeg';
        $filePath = rtrim($outputDir, '/') . '/' . $filename;
        
        // Сохраняем изображение
        if (file_put_contents($filePath, $imageData) === false) {
            continue; // Пропускаем если не удалось сохранить
        }
        
        // Формируем запись для результата
        $result[] = [
            'image_name' => $imageName,
            'link' => $link,
            'file_path' => $filePath,
            'name' => $name
        ];
    }
    
    return $result;
}

/**
 * Извлекает и декодирует base64 изображение
 * 
 * @param string $base64String Строка с base64 данными (может содержать префикс)
 * @return string|null Декодированные бинарные данные или null в случае ошибки
 */
function extractBase64Image($base64String) {
    // Удаляем префикс, если он есть (например, "data:image/jpeg;base64,")
    if (strpos($base64String, 'base64,') !== false) {
        $base64String = substr($base64String, strpos($base64String, 'base64,') + 7);
    }
    
    // Декодируем base64
    $imageData = base64_decode($base64String);
    
    if ($imageData === false) {
        return null;
    }
    
    return $imageData;
}

/**
 * Альтернативная версия с использованием регулярного выражения для извлечения base64
 */
function extractBase64ImageAlt($base64String) {
    // Ищем base64 данные после запятой
    if (preg_match('/base64,(.*)$/', $base64String, $matches)) {
        $base64String = $matches[1];
    }
    
    return base64_decode($base64String);
}

// Пример использования
try {
    // Пример JSON из задания
    $jsonExample = '{"call": {"product_name": {"tradeble": "true","name": "main_window"},"image_name": "sun1","image": { "link": "https://product_web", "base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAAAAAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUpGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAB9AGQDASIAAhEBAxEB/8QAHAAAAgIDAQEAAAAAAAAAAAAABAUDBgECBwAI/8QAQBAAAgEDAwIEBAQEBAUDBQAAAQIDAAQRBRIhMUEGE1FhInGBkRQyobEHI0LB0VLh8BUzQ1NicoKSov/EABkBAAMBAQEAAAAAAAAAAAAAAAABAgMEBf/EACQRAQEAAgIDAAIBBQEAAAAAAAABAhEDIRIxQQQTIlEyYYE0/9oADAMBAAIRAxEAPwDmTLz/AF"]}}';
    
    // Обрабатываем JSON
    $result = processApiResponse($jsonExample);
    
    // Выводим результат
    print_r($result);
    
} catch (Exception $e) {
    echo 'Ошибка: ' . $e->getMessage();
}

/**
 * Функция для обработки нескольких JSON объектов
 * 
 * @param array $jsonStrings Массив JSON строк
 * @param string $outputDir Директория для сохранения
 * @return array Объединенный результат
 */
function processMultipleResponses($jsonStrings, $outputDir = '/image_folder') {
    $allResults = [];
    
    foreach ($jsonStrings as $index => $jsonString) {
        try {
            $results = processApiResponse($jsonString, $outputDir);
            $allResults = array_merge($allResults, $results);
        } catch (Exception $e) {
            // Логируем ошибку, но продолжаем обработку
            error_log("Ошибка обработки JSON #{$index}: " . $e->getMessage());
        }
    }
    
    return $allResults;
}

/**
 * Функция для обработки JSON из URL
 * 
 * @param string $url URL для получения JSON
 * @param string $outputDir Директория для сохранения
 * @return array Результат обработки
 */
function processApiUrl($url, $outputDir = '/image_folder') {
    $jsonString = file_get_contents($url);
    
    if ($jsonString === false) {
        throw new Exception('Не удалось получить данные по URL: ' . $url);
    }
    
    return processApiResponse($jsonString, $outputDir);
}

// Дополнительные вспомогательные функции

/**
 * Проверяет, является ли строка валидным base64 изображением
 */
function isValidBase64Image($base64String) {
    // Удаляем префикс если есть
    if (strpos($base64String, 'base64,') !== false) {
        $base64String = substr($base64String, strpos($base64String, 'base64,') + 7);
    }
    
    // Проверяем, что строка содержит только допустимые символы
    if (!preg_match('/^[a-zA-Z0-9\/+]+={0,2}$/', $base64String)) {
        return false;
    }
    
    // Проверяем, что декодированные данные являются изображением
    $data = base64_decode($base64String);
    if ($data === false) {
        return false;
    }
    
    // Проверяем сигнатуру JPEG
    if (strpos($data, "\xFF\xD8\xFF") === 0) {
        return true;
    }
    
    // Можно добавить проверку других форматов
    
    return false;
}

/**
 * Генерирует безопасное имя файла
 */
function generateSafeFilename($imageName, $extension = 'jpeg') {
    // Удаляем недопустимые символы
    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', $imageName);
    
    // Если имя пустое, генерируем случайное
    if (empty($safeName)) {
        $safeName = uniqid('img_', true);
    }
    
    return $safeName . '.' . $extension;
}

/**
 * Логирование ошибок
 */
function logError($message, $context = []) {
    $logEntry = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $logEntry .= ' - ' . json_encode($context);
    }
    error_log($logEntry);
}