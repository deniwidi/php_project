<?php
require_once 'config.php';

// Ambil & validasi ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    // Gunakan Prepared Statement untuk keamanan
    $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: index.php?msg=deleted");
        exit;
    } else {
        $stmt->close();
        $conn->close();
        // Jika gagal, tetap redirect ke index dengan pesan error
        header("Location: index.php?msg=delete_failed");
        exit;
    }
}

// Jika ID tidak valid
$conn->close();
header("Location: index.php");
exit;
?>
