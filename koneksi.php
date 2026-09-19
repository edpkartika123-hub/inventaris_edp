<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "db_inventaris_digital"
);

if (!$conn) {
    die(
        "Koneksi database gagal: " .
        mysqli_connect_error()
    );
}

mysqli_set_charset(
    $conn,
    "utf8mb4"
);