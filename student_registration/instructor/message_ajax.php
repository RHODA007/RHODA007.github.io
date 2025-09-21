<?php
session_start();
require 'includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['instructor'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$instructor_id = $_SESSION['instructor']['id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'fetch') {
    $chatType = $_GET['type'] ?? '';
    $chatId   = intval($_GET['id'] ?? 0);

    if ($chatType === 'student' && $chatId > 0) {
        // Fetch messages between instructor and student
        $stmt = $pdo->prepare("
            SELECT id, sender_id, receiver_id, message, created_at
            FROM messages
            WHERE 
                (sender_id = :instructor AND receiver_id = :student)
                OR
                (sender_id = :student AND receiver_id = :instructor)
            ORDER BY created_at ASC
        ");
        $stmt->execute(['instructor' => $instructor_id, 'student' => $chatId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $messages = array_map(function($row) use ($instructor_id) {
            return [
                "id"      => $row['id'],
                "text"    => $row['message'],
                "sender"  => $row['sender_id'] == $instructor_id ? "you" : "them",
                "time"    => $row['created_at']
            ];
        }, $rows);

        echo json_encode($messages);
        exit;
    }

    if ($chatType === 'group' && $chatId > 0) {
        // Fetch group messages
        $stmt = $pdo->prepare("
            SELECT m.id, m.sender_id, m.message, m.created_at, u.name
            FROM group_messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.group_id = :group_id
            ORDER BY m.created_at ASC
        ");
        $stmt->execute(['group_id' => $chatId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $messages = array_map(function($row) use ($instructor_id) {
            return [
                "id"      => $row['id'],
                "text"    => $row['message'],
                "sender"  => $row['sender_id'] == $instructor_id ? "you" : $row['name'],
                "time"    => $row['created_at']
            ];
        }, $rows);

        echo json_encode($messages);
        exit;
    }

    echo json_encode([]);
    exit;

} elseif ($action === 'send') {
    // Expect JSON body
    $data = json_decode(file_get_contents("php://input"), true);
    $chatType = $data['type'] ?? '';
    $chatId   = intval($data['id'] ?? 0);
    $message  = trim($data['message'] ?? '');

    if ($message === '') {
        echo json_encode(["success" => false, "message" => "Message cannot be empty"]);
        exit;
    }

    if ($chatType === 'student' && $chatId > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, message, created_at) 
            VALUES (:sender, :receiver, :msg, NOW())
        ");
        $stmt->execute([
            'sender'   => $instructor_id,
            'receiver' => $chatId,
            'msg'      => $message
        ]);
        echo json_encode(["success" => true]);
        exit;
    }

    if ($chatType === 'group' && $chatId > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO group_messages (group_id, sender_id, message, created_at)
            VALUES (:group_id, :sender, :msg, NOW())
        ");
        $stmt->execute([
            'group_id' => $chatId,
            'sender'   => $instructor_id,
            'msg'      => $message
        ]);
        echo json_encode(["success" => true]);
        exit;
    }

    echo json_encode(["success" => false, "message" => "Invalid chat"]);
    exit;

} else {
    echo json_encode(["error" => "Invalid action"]);
    exit;
}
