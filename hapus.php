<?php
include 'koneksi.php';

$id = $_GET['id'];
$query = mysqli_query($conn, "DELETE FROM inventaris_edp WHERE id = '$id'");

if ($query) {
    header("Location: index.php");
} else {
    echo "Gagal menghapus data: " . mysqli_error($conn);
}
?>