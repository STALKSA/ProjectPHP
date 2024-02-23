<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

error_log('Actual REMOTE_ADDR: ' . $_SERVER['REMOTE_ADDR']);

$expectedToken = "VALID_TOKEN";
$receivedToken = isset($_GET['token']) ? $_GET['token'] : '';

if ($receivedToken !== $expectedToken) {
    echo json_encode(['error' => 'Неверный токен']);
    exit;
}

$conn = new mysqli("127.0.0.1", "root", "root", "exam_db");

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Функция для подготовки ответа с ошибкой
function sendError($message)
{
    echo json_encode(['error' => $message]);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'getUnprocessedOrders') {
    $sql = "SELECT * FROM orders WHERE status = 'новый'";
    $result = $conn->query($sql);
    $unprocessedOrders = [];

    if (!$result) {
        sendError('Ошибка при выполнении запроса: ' . $conn->error);
    }

    while ($order = $result->fetch_assoc()) {
        $unprocessedOrders[] = $order;
    }

    echo json_encode($unprocessedOrders);
}

if (isset($_GET['action']) && $_GET['action'] === 'getOrderById') {
    $orderId = isset($_GET['orderId']) ? $_GET['orderId'] : '';

    if (!is_numeric($orderId)) {
        sendError('Некорректный идентификатор заказа');
    }

    $sql = "SELECT * FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        sendError('Ошибка при подготовке запроса: ' . $conn->error);
    }

    $stmt->bind_param("i", $orderId); // "i" - тип данных integer
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        sendError('Ошибка при выполнении запроса: ' . $stmt->error);
    }

    $order = $result->fetch_assoc();

    if ($order) {
        echo json_encode($order);
    } else {
        sendError('Заказ с указанным идентификатором не найден');
    }

    $stmt->close();
}

$conn->close();

