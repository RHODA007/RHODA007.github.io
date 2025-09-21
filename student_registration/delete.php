<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);

        echo "<script>alert('Student deleted successfully!'); window.location='view.php';</script>";
    } catch (PDOException $e) {
        die("Error deleting student: " . $e->getMessage());
    }
} else {
    echo "<script>alert('Invalid student ID!'); window.location='view.php';</script>";
}
header("Location: view.php?msg=Student+Registered+Successfully");
exit;
