<?php

use Alisa\Support\Collection;

require __DIR__ . '/../vendor/autoload.php';

// 1. Создание коллекции
$collection = new Collection([
    'user' => [
        'name' => 'Alice',
        'age' => 30,
        'contacts' => [
            'email' => 'alice@example.com',
            'phones' => [
                'home' => '123-456',
                'work' => '789-012'
            ]
        ],
        'roles' => ['admin', 'editor']
    ],
    'products' => [
        'product_1' => ['name' => 'Laptop', 'price' => 999],
        'product_2' => ['name' => 'Phone', 'price' => 599],
        'product_3' => ['name' => 'Tablet', 'price' => 399]
    ],
    'settings' => [
        'theme' => 'dark',
        'notifications' => true
    ]
]);

// 2. Простые операции
echo "Количество элементов: " . $collection->count() . "\n";
echo "Все элементы:\n";
print_r($collection->all());

// 3. Получение значений
echo "Имя пользователя: " . $collection->get('user.name') . "\n"; // Alice
echo "Рабочий телефон: " . $collection->get('user.contacts.phones.work') . "\n"; // 789-012
echo "Роли пользователя:\n";
print_r($collection->get('user.roles'));

// 4. Установка значений
$collection->set('user.location', 'New York');
$collection->set('settings.theme', 'light');
echo "Новая тема: " . $collection->get('settings.theme') . "\n"; // light

// 5. Проверка существования
echo "Есть ли email? " . ($collection->has('user.contacts.email') ? 'Да' : 'Нет') . "\n"; // Да
echo "Есть ли адрес? " . ($collection->has('user.address') ? 'Да' : 'Нет') . "\n"; // Нет

// 6. Удаление
$collection->remove('user.contacts.phones.work');
echo "Рабочий телефон после удаления: " . ($collection->has('user.contacts.phones.work') ? 'Есть' : 'Нет') . "\n"; // Нет

// 7. Работа с масками (wildcards)
echo "Все продукты:\n";
print_r($collection->get('products.*'));

// Установка скидки 10% на все продукты
$collection->set('products.*.discount', 10);
echo "Продукты со скидкой:\n";
print_r($collection->get('products.*'));

// 8. JSON-сериализация
echo "JSON представление:\n";
echo $collection->toJson() . "\n";

// 9. ArrayAccess интерфейс
$collection['new_key'] = 'New Value';
echo "Новый ключ: " . $collection['new_key'] . "\n"; // New Value
unset($collection['new_key']);
echo "Новый ключ после удаления: " . (isset($collection['new_key']) ? 'Есть' : 'Нет') . "\n"; // Нет

// 10. Специальные случаи
$emptyCollection = new Collection();
$emptyCollection->set('deep.nested.value', 'Hello');
echo "Глубокое вложение: " . $emptyCollection->get('deep.nested.value') . "\n"; // Hello

// 11. Работа с пустым ключом
echo "Все данные через пустой ключ:\n";
print_r($collection->get(''));

// 12. Пример с изменением разделителя
$customCollection = new Collection(['a' => ['b' => ['c' => 42]]], '/');
echo "С кастомным разделителем: " . $customCollection->get('a/b/c') . "\n"; // 42

// 13. Пример с отключенным wildcard
$noWildcardCollection = new Collection(['a' => ['b' => 1, 'c' => 2]], '.', '');
$noWildcardCollection->set('a.*', 3); // Ничего не изменится, так как wildcard отключен
echo "Без wildcard: " . $noWildcardCollection->get('a.b') . "\n"; // 1

// 14. Сложный пример с wildcard
$complex = new Collection([
    'group1' => [
        'user1' => ['name' => 'John', 'score' => 80],
        'user2' => ['name' => 'Jane', 'score' => 90]
    ],
    'group2' => [
        'user3' => ['name' => 'Bob', 'score' => 75],
        'user4' => ['name' => 'Alice', 'score' => 85]
    ]
]);

// Получить все имена пользователей
echo "Все имена пользователей:\n";
print_r($complex->get('*.*.name'));

// Установить всем статус 'active'
$complex->set('*.*.status', 'active');
echo "Группы после добавления статуса:\n";
print_r($complex->all());

// Удалить все score
$complex->remove('*.*.score');
echo "Группы после удаления score:\n";
print_r($complex->all());