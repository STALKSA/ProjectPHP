<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$expectedToken = "VALID_TOKEN";
$receivedToken = isset($_GET['token']) ? $_GET['token'] : '';

if ($receivedToken !== $expectedToken) {
    echo json_encode(['error' => 'Неверный токен']);
    exit;
}

// $allowedIP = "VALID_IP_ADDRESS";
// $clientIP = $_SERVER['REMOTE_ADDR'];

// if ($clientIP !== $allowedIP) {
//     echo json_encode(['error' => 'Неверный IP-адрес']);
//     exit;
// }

$conn = new mysqli("127.0.0.1", "root", "root", "exam_db");

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

if (isset($_GET['action']) && $_GET['action'] === 'getUnprocessedOrders') {
    $sql = "SELECT * FROM orders WHERE status = 'новый'";
    $result = $conn->query($sql);
    $unprocessedOrders = [];

    while ($order = $result->fetch_assoc()) {
        $unprocessedOrders[] = $order;
    }

    echo json_encode($unprocessedOrders);
}

if (isset($_GET['action']) && $_GET['action'] === 'getOrderById') {
    $orderId = isset($_GET['orderId']) ? $_GET['orderId'] : '';
    $sql = "SELECT * FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $orderId); // "i" - тип данных integer
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();

        echo json_encode($order);
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Ошибка при подготовке запроса']);
    }
}

$conn->close();
