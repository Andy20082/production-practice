<?php
function validateRegistration($data) {
    $errors = [];
    
    // Email
    if (empty($data['email'])) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!str_contains($data['email'], '@')) {
        $errors[] = 'Email должен содержать символ @';
    } elseif (strlen($data['email']) <= 5) {
        $errors[] = 'Email должен быть длиннее 5 символов';
    }
    
    // Password
    if (empty($data['password'])) {
        $errors[] = 'Пароль обязателен для заполнения';
    } elseif (strlen($data['password']) <= 8) {
        $errors[] = 'Пароль должен быть длиннее 8 символов';
    } elseif (!preg_match('/[a-zA-Z]/', $data['password']) || !preg_match('/[0-9]/', $data['password'])) {
        $errors[] = 'Пароль должен содержать буквы и цифры';
    }
    
    // Repit_password
    if (empty($data['repit_password'])) {
        $errors[] = 'Повтор пароля обязателен для заполнения';
    } elseif ($data['password'] !== $data['repit_password']) {
        $errors[] = 'Пароли не совпадают';
    }
    
    // Phone_number
    if (!empty($data['phone_number']) && strlen((string)$data['phone_number']) <= 5) {
        $errors[] = 'Номер телефона должен быть длиннее 5 символов';
    }
    
    // Name
    if (empty($data['name'])) {
        $errors[] = 'Имя обязательно для заполнения';
    } elseif (!preg_match('/^[a-zA-Zа-яА-Я\s]+$/u', $data['name'])) {
        $errors[] = 'Имя может содержать только буквы';
    }
    
    // Came_from
    if (!empty($data['came_from'])) {
        $allowed = ['site', 'city', 'tv', 'others'];
        if (!in_array($data['came_from'], $allowed)) {
            $errors[] = 'Некорректное значение поля "Откуда узнали"';
        }
    }
    
    // Date_birth
    if (empty($data['date_birth'])) {
        $errors[] = 'Дата рождения обязательна для заполнения';
    } else {
        $birthDate = new DateTime($data['date_birth']);
        $today = new DateTime('now');
        $age = $today->diff($birthDate)->y;
        if ($age < 15 || $age > 67) {
            $errors[] = 'Возраст должен быть от 15 до 67 лет';
        }
    }
    
    if (!empty($errors)) {
        return ['status' => false, 'message' => implode('. ', $errors)];
    }
    
    return ['status' => true, 'message' => 'Данные успешно проверены'];
}
?>