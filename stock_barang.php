<?php
/**
 * ============================================================
 * STOCK BARANG - SISTEM INVENTARIS DIGITAL
 * ============================================================
 *
 * Database : db_inventaris_digital
 *
 * Entitas:
 * - Nama Barang
 * - Spesifikasi
 * - Nomer ND
 * - Jumlah Barang
 * - Tahun Pengadaan
 *
 * Fitur:
 * - Tambah barang
 * - Edit barang
 * - Hapus barang
 * - Search
 * - Statistik
 * - SweetAlert2
 * - Responsive
 *
 * PHP 7.4+
 * MariaDB / MySQL
 * ============================================================
 */

session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_inventaris_digital';

$conn = new mysqli(
    $host,
    $user,
    $pass,
    $db
);

if ($conn->connect_error) {
    die(
        'Koneksi database gagal: ' .
        htmlspecialchars(
            $conn->connect_error,
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

$conn->set_charset('utf8mb4');


/* ============================================================
   BUAT TABEL OTOMATIS JIKA BELUM ADA
   ============================================================ */

$conn->query("
    CREATE TABLE IF NOT EXISTS stock_barang (
        id INT AUTO_INCREMENT PRIMARY KEY,

        nama_barang VARCHAR(255) NOT NULL,

        spesifikasi TEXT NULL,

        nomer_nd VARCHAR(100) NULL,

        jumlah_barang DECIMAL(15,2)
            NOT NULL DEFAULT 0,

        tahun_pengadaan VARCHAR(4) NULL,

        created_at TIMESTAMP
            DEFAULT CURRENT_TIMESTAMP,

        updated_at TIMESTAMP
            DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,

        INDEX idx_nama_barang (nama_barang),

        INDEX idx_nomer_nd (nomer_nd),

        INDEX idx_tahun_pengadaan (tahun_pengadaan)

    ) ENGINE=InnoDB

    DEFAULT CHARSET=utf8mb4

    COLLATE=utf8mb4_unicode_ci
");


/* ============================================================
   MIGRASI OTOMATIS DATABASE LAMA
   ============================================================ */

/* Cek nomer_nd */
$cekND = $conn->query("
    SHOW COLUMNS
    FROM stock_barang
    LIKE 'nomer_nd'
");

if (
    $cekND &&
    $cekND->num_rows == 0
) {

    $conn->query("
        ALTER TABLE stock_barang

        ADD COLUMN nomer_nd
        VARCHAR(100) NULL

        AFTER spesifikasi
    ");
}


/* Cek tahun_pengadaan */
$cekTahun = $conn->query("
    SHOW COLUMNS
    FROM stock_barang
    LIKE 'tahun_pengadaan'
");

if (
    $cekTahun &&
    $cekTahun->num_rows == 0
) {

    $conn->query("
        ALTER TABLE stock_barang

        ADD COLUMN tahun_pengadaan
        VARCHAR(4) NULL

        AFTER jumlah_barang
    ");
}


/* ============================================================
   FUNCTION
   ============================================================ */

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


function formatJumlah($value)
{
    $n = (float)$value;

    return rtrim(
        rtrim(
            number_format(
                $n,
                2,
                ',',
                '.'
            ),
            '0'
        ),
        ','
    );
}


/* ============================================================
   MESSAGE
   ============================================================ */

$message = '';

$messageType = 'success';


/* ============================================================
   SIMPAN DATA BARU
   ============================================================ */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    ($_POST['action'] ?? '') === 'simpan'
) {

    $nama = trim(
        $_POST['nama_barang'] ?? ''
    );

    $spesifikasi = trim(
        $_POST['spesifikasi'] ?? ''
    );

    $nomerND = trim(
        $_POST['nomer_nd'] ?? ''
    );

    $jumlahRaw = str_replace(
        ',',
        '.',
        trim(
            $_POST['jumlah_barang'] ?? ''
        )
    );

    $tahun = trim(
        $_POST['tahun_pengadaan'] ?? ''
    );


    /* ========================================================
       VALIDASI
       ======================================================== */

    if ($nama === '') {

        $message =
            'Nama barang wajib diisi.';

        $messageType =
            'error';

    } elseif (
        $jumlahRaw === ''
        ||
        !is_numeric($jumlahRaw)
        ||
        (float)$jumlahRaw < 0
    ) {

        $message =
            'Jumlah barang harus berupa angka 0 atau lebih.';

        $messageType =
            'error';

    } elseif (
        $tahun !== ''
        &&
        !preg_match(
            '/^\d{4}$/',
            $tahun
        )
    ) {

        $message =
            'Tahun pengadaan harus 4 digit.';

        $messageType =
            'error';

    } else {

        $jumlah =
            (float)$jumlahRaw;


        $stmt = $conn->prepare("
            INSERT INTO stock_barang
            (
                nama_barang,
                spesifikasi,
                nomer_nd,
                jumlah_barang,
                tahun_pengadaan
            )
            VALUES (?, ?, ?, ?, ?)
        ");


        if (!$stmt) {

            $message =
                'Gagal membuat query: ' .
                $conn->error;

            $messageType =
                'error';

        } else {

            $stmt->bind_param(
                'sssds',
                $nama,
                $spesifikasi,
                $nomerND,
                $jumlah,
                $tahun
            );


            if ($stmt->execute()) {

                $message =
                    'Stock barang berhasil ditambahkan.';

                $messageType =
                    'success';

            } else {

                $message =
                    'Gagal menyimpan stock: ' .
                    $stmt->error;

                $messageType =
                    'error';
            }


            $stmt->close();
        }
    }
}


/* ============================================================
   UPDATE / EDIT
   ============================================================ */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    ($_POST['action'] ?? '') === 'update'
) {

    $id = (int)(
        $_POST['id'] ?? 0
    );


    $nama = trim(
        $_POST['nama_barang'] ?? ''
    );


    $spesifikasi = trim(
        $_POST['spesifikasi'] ?? ''
    );


    $nomerND = trim(
        $_POST['nomer_nd'] ?? ''
    );


    $jumlahRaw = str_replace(
        ',',
        '.',
        trim(
            $_POST['jumlah_barang'] ?? ''
        )
    );


    $tahun = trim(
        $_POST['tahun_pengadaan'] ?? ''
    );


    /* ========================================================
       VALIDASI EDIT
       ======================================================== */

    if ($id <= 0) {

        $message =
            'ID barang tidak valid.';

        $messageType =
            'error';

    } elseif ($nama === '') {

        $message =
            'Nama barang wajib diisi.';

        $messageType =
            'error';

    } elseif (
        $jumlahRaw === ''
        ||
        !is_numeric($jumlahRaw)
        ||
        (float)$jumlahRaw < 0
    ) {

        $message =
            'Jumlah barang harus berupa angka 0 atau lebih.';

        $messageType =
            'error';

    } elseif (
        $tahun !== ''
        &&
        !preg_match(
            '/^\d{4}$/',
            $tahun
        )
    ) {

        $message =
            'Tahun pengadaan harus 4 digit.';

        $messageType =
            'error';

    } else {

        $jumlah =
            (float)$jumlahRaw;


        /* ====================================================
           UPDATE
           ==================================================== */

        $stmt = $conn->prepare("
            UPDATE stock_barang

            SET
                nama_barang = ?,
                spesifikasi = ?,
                nomer_nd = ?,
                jumlah_barang = ?,
                tahun_pengadaan = ?

            WHERE id = ?
        ");


        if (!$stmt) {

            $message =
                'Gagal membuat query update: ' .
                $conn->error;

            $messageType =
                'error';

        } else {

            /*
             * s = string
             * s = string
             * s = string
             * d = double
             * s = string
             * i = integer
             *
             * TOTAL:
             * sss d s i
             *
             * Tanpa spasi:
             * sssdsi
             */

            $stmt->bind_param(
                'sssdsi',
                $nama,
                $spesifikasi,
                $nomerND,
                $jumlah,
                $tahun,
                $id
            );


            if ($stmt->execute()) {

                $message =
                    'Stock barang berhasil diperbarui.';

                $messageType =
                    'success';

            } else {

                $message =
                    'Gagal memperbarui stock: ' .
                    $stmt->error;

                $messageType =
                    'error';
            }


            $stmt->close();
        }
    }
}


/* ============================================================
   HAPUS
   ============================================================ */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    ($_POST['action'] ?? '') === 'hapus'
) {

    $id = (int)(
        $_POST['id'] ?? 0
    );


    if ($id <= 0) {

        $message =
            'ID barang tidak valid.';

        $messageType =
            'error';

    } else {

        $stmt = $conn->prepare("
            DELETE FROM stock_barang
            WHERE id = ?
        ");


        if (!$stmt) {

            $message =
                'Gagal membuat query hapus: ' .
                $conn->error;

            $messageType =
                'error';

        } else {

            $stmt->bind_param(
                'i',
                $id
            );


            if ($stmt->execute()) {

                if ($stmt->affected_rows > 0) {

                    $message =
                        'Stock barang berhasil dihapus.';

                    $messageType =
                        'success';

                } else {

                    $message =
                        'Data stock tidak ditemukan.';

                    $messageType =
                        'error';
                }

            } else {

                $message =
                    'Gagal menghapus data: ' .
                    $stmt->error;

                $messageType =
                    'error';
            }


            $stmt->close();
        }
    }
}


/* ============================================================
   DATA STOCK
   ============================================================ */

$keyword = trim(
    $_GET['search'] ?? ''
);

$stock = [];


if ($keyword !== '') {

    $like =
        '%' . $keyword . '%';


    $stmt = $conn->prepare("
        SELECT

            id,

            nama_barang,

            spesifikasi,

            nomer_nd,

            jumlah_barang,

            tahun_pengadaan,

            created_at

        FROM stock_barang

        WHERE

            nama_barang LIKE ?

            OR spesifikasi LIKE ?

            OR nomer_nd LIKE ?

            OR tahun_pengadaan LIKE ?

        ORDER BY

            nama_barang ASC,

            id DESC
    ");


    if ($stmt) {

        $stmt->bind_param(
            'ssss',
            $like,
            $like,
            $like,
            $like
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        while (
            $row =
            $result->fetch_assoc()
        ) {

            $stock[] =
                $row;
        }


        $stmt->close();
    }

} else {

    $result = $conn->query("
        SELECT

            id,

            nama_barang,

            spesifikasi,

            nomer_nd,

            jumlah_barang,

            tahun_pengadaan,

            created_at

        FROM stock_barang

        ORDER BY

            nama_barang ASC,

            id DESC
    ");


    if ($result) {

        while (
            $row =
            $result->fetch_assoc()
        ) {

            $stock[] =
                $row;
        }
    }
}


/* ============================================================
   STATISTIK
   ============================================================ */

$totalJenis =
    count($stock);


$totalJumlah =
    0;


foreach ($stock as $row) {

    $totalJumlah +=
        (float)$row['jumlah_barang'];
}


/* ============================================================
   DATA EDIT
   ============================================================ */

$editId = (int)(
    $_GET['edit'] ?? 0
);


$editData = null;


if ($editId > 0) {

    $stmt = $conn->prepare("
        SELECT

            id,

            nama_barang,

            spesifikasi,

            nomer_nd,

            jumlah_barang,

            tahun_pengadaan

        FROM stock_barang

        WHERE id = ?

        LIMIT 1
    ");


    if ($stmt) {

        $stmt->bind_param(
            'i',
            $editId
        );


        $stmt->execute();


        $editData =
            $stmt
            ->get_result()
            ->fetch_assoc();


        $stmt->close();
    }
}

?>
<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
Stock Barang - Inventaris Digital
</title>


<!-- FONT AWESOME -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
>


<!-- FONT -->

<link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>


<!-- SWEETALERT2 -->

<script
    src="https://cdn.jsdelivr.net/npm/sweetalert2@11"
></script>


<style>

:root{

    --bg:#07111f;

    --card:rgba(15,28,46,.82);

    --card2:#0d1a2b;

    --line:rgba(255,255,255,.09);

    --text:#f8fafc;

    --muted:#94a3b8;

    --primary:#3b82f6;

    --primary2:#2563eb;

    --success:#10b981;

    --danger:#ef4444;

    --shadow:0 24px 70px rgba(0,0,0,.35)

}


*{
    box-sizing:border-box
}


html{
    scroll-behavior:smooth
}


body{

    margin:0;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    color:var(--text);

    min-height:100vh;

    background:

        radial-gradient(
            circle at 10% 0%,
            rgba(59,130,246,.16),
            transparent 32%
        ),

        radial-gradient(
            circle at 90% 20%,
            rgba(16,185,129,.10),
            transparent 30%
        ),

        linear-gradient(
            180deg,
            #07111f 0%,
            #0b1424 55%,
            #07111f 100%
        );
}


body:before{

    content:"";

    position:fixed;

    inset:0;

    pointer-events:none;

    opacity:.35;

    background-image:

        linear-gradient(
            rgba(255,255,255,.025) 1px,
            transparent 1px
        ),

        linear-gradient(
            90deg,
            rgba(255,255,255,.025) 1px,
            transparent 1px
        );

    background-size:55px 55px;

    mask-image:

        radial-gradient(
            circle at center,
            #000 30%,
            transparent 90%
        );
}


.container{

    max-width:1500px;

    margin:auto;

    padding:28px;

    position:relative;

    z-index:1
}


/* ============================================================
   HEADER
   ============================================================ */

.topbar{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:18px;

    margin-bottom:24px
}


.brand{

    display:flex;

    align-items:center;

    gap:14px
}


.brand-icon{

    width:54px;

    height:54px;

    border-radius:17px;

    display:grid;

    place-items:center;

    font-size:22px;

    background:

        linear-gradient(
            135deg,
            var(--primary),
            var(--primary2)
        );

    box-shadow:

        0 12px 35px
        rgba(37,99,235,.3)
}


h1{

    margin:0;

    font-size:27px;

    letter-spacing:-.7px
}


.subtitle{

    margin-top:5px;

    color:var(--muted);

    font-size:13px
}


.back{

    display:inline-flex;

    align-items:center;

    gap:8px;

    color:#fff;

    text-decoration:none;

    padding:11px 16px;

    border:
        1px solid
        var(--line);

    border-radius:12px;

    background:
        rgba(255,255,255,.045);

    transition:.25s
}


.back:hover{

    color:#fff;

    background:
        rgba(255,255,255,.09);

    transform:
        translateY(-2px)
}


/* ============================================================
   ALERT
   ============================================================ */

.alert{

    padding:14px 17px;

    border-radius:13px;

    margin-bottom:18px;

    font-size:14px;

    border:1px solid
}


.alert.success{

    background:
        rgba(16,185,129,.10);

    border-color:
        rgba(16,185,129,.3);

    color:#a7f3d0
}


.alert.error{

    background:
        rgba(239,68,68,.10);

    border-color:
        rgba(239,68,68,.3);

    color:#fecaca
}


/* ============================================================
   CARD
   ============================================================ */

.card{

    background:

        linear-gradient(
            145deg,
            rgba(17,31,50,.90),
            rgba(8,20,34,.88)
        );

    border:
        1px solid
        var(--line);

    border-radius:22px;

    padding:24px;

    margin-bottom:22px;

    box-shadow:
        var(--shadow);

    backdrop-filter:
        blur(18px)
}


.card-title{

    display:flex;

    align-items:center;

    gap:11px;

    font-size:18px;

    font-weight:800;

    margin-bottom:20px
}


.card-title i{

    color:#60a5fa
}


/* ============================================================
   FORM
   ============================================================ */

.form-grid{

    display:grid;

    grid-template-columns:

        1.3fr
        1.8fr
        1fr
        .75fr
        .8fr;

    gap:15px
}


.field label{

    display:block;

    font-size:12px;

    color:var(--muted);

    font-weight:700;

    margin-bottom:8px
}


.field input,
.field textarea{

    width:100%;

    background:#071321;

    color:#fff;

    border:
        1px solid
        var(--line);

    border-radius:11px;

    padding:12px 13px;

    outline:none;

    font:inherit
}


.field textarea{

    min-height:46px;

    resize:vertical
}


.field input:focus,
.field textarea:focus{

    border-color:
        rgba(59,130,246,.75);

    box-shadow:

        0 0 0 3px
        rgba(59,130,246,.09)
}


.actions{

    display:flex;

    justify-content:flex-end;

    gap:10px;

    margin-top:19px
}


.btn{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    gap:8px;

    border:0;

    border-radius:11px;

    padding:11px 17px;

    color:#fff;

    font-weight:700;

    cursor:pointer;

    font-family:inherit;

    text-decoration:none;

    transition:.25s
}


.btn:hover{

    transform:
        translateY(-2px)
}


.btn-save{

    background:

        linear-gradient(
            135deg,
            var(--primary),
            var(--primary2)
        );

    box-shadow:

        0 10px 28px
        rgba(37,99,235,.23)
}


.btn-cancel{

    background:
        rgba(255,255,255,.06);

    border:
        1px solid
        var(--line)
}


/* ============================================================
   TOOLBAR
   ============================================================ */

.toolbar{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:15px;

    margin-bottom:18px
}


.stats{

    display:flex;

    gap:10px;

    flex-wrap:wrap
}


.stat{

    padding:9px 13px;

    border-radius:11px;

    background:
        rgba(255,255,255,.045);

    border:
        1px solid
        var(--line);

    font-size:12px;

    color:var(--muted)
}


.stat strong{

    color:#fff;

    font-size:15px;

    margin-left:5px
}


.search{

    display:flex;

    gap:8px;

    max-width:480px;

    width:100%
}


.search input{

    flex:1;

    background:#071321;

    color:#fff;

    border:
        1px solid
        var(--line);

    border-radius:11px;

    padding:11px 13px;

    font:inherit;

    outline:none
}


.search button{

    border:0;

    border-radius:11px;

    background:
        rgba(59,130,246,.16);

    color:#93c5fd;

    padding:0 15px;

    cursor:pointer
}


/* ============================================================
   TABLE
   ============================================================ */

.table-wrap{

    overflow:auto;

    border:
        1px solid
        var(--line);

    border-radius:15px
}


table{

    width:100%;

    border-collapse:collapse;

    min-width:1050px
}


th,
td{

    padding:14px 13px;

    border-bottom:
        1px solid
        var(--line);

    text-align:left;

    font-size:13px;

    vertical-align:top
}


th{

    background:#13243a;

    color:#94a3b8;

    font-size:11px;

    text-transform:uppercase;

    letter-spacing:.6px;

    position:sticky;

    top:0;

    z-index:2
}


td{

    color:#dbeafe
}


tr:hover td{

    background:
        rgba(59,130,246,.035)
}


.number{

    text-align:right;

    font-weight:800;

    color:#93c5fd;

    white-space:nowrap
}


.nd{

    color:#fbbf24;

    font-weight:700;

    white-space:nowrap
}


.tahun{

    color:#c4b5fd;

    font-weight:700;

    white-space:nowrap
}


.actions-cell{

    white-space:nowrap
}


.inline{

    display:inline
}


.empty{

    text-align:center;

    color:var(--muted);

    padding:38px
}


.badge-stock{

    display:inline-flex;

    align-items:center;

    gap:7px;

    padding:6px 10px;

    border-radius:9px;

    background:
        rgba(16,185,129,.09);

    border:
        1px solid
        rgba(16,185,129,.16);

    color:#86efac;

    font-weight:700
}


/* ============================================================
   BUTTON EDIT
   ============================================================ */

.btn-edit{

    background:
        rgba(59,130,246,.12);

    color:#93c5fd;

    border:
        1px solid
        rgba(59,130,246,.22);

    padding:8px 11px
}


.btn-edit:hover{

    background:
        rgba(59,130,246,.25);

    color:#bfdbfe
}


/* ============================================================
   BUTTON DELETE
   ============================================================ */

.btn-delete{

    background:
        rgba(239,68,68,.10);

    color:#fca5a5;

    border:
        1px solid
        rgba(239,68,68,.18);

    padding:8px 11px
}


.btn-delete:hover{

    background:
        rgba(239,68,68,.25);

    color:#fecaca
}


.note{

    font-size:12px;

    color:var(--muted);

    margin-top:8px
}


/* ============================================================
   RESPONSIVE
   ============================================================ */

@media(max-width:1200px){

    .form-grid{

        grid-template-columns:

            1fr
            1fr
            1fr;
    }

}


@media(max-width:900px){

    .form-grid{

        grid-template-columns:
            1fr
    }


    .toolbar{

        align-items:stretch;

        flex-direction:column
    }


    .search{

        max-width:none
    }

}


@media(max-width:650px){

    .container{

        padding:16px
    }


    .topbar{

        align-items:flex-start;

        flex-direction:column
    }


    h1{

        font-size:23px
    }


    .card{

        padding:18px
    }


    .actions{

        flex-direction:column
    }


    .actions .btn{

        width:100%
    }


    .stats{

        width:100%
    }

}

</style>

</head>


<body>

<div class="container">


    <!-- =====================================================
         HEADER
         ===================================================== -->

    <div class="topbar">

        <div class="brand">

            <div class="brand-icon">

                <i class="fas fa-boxes-stacked"></i>

            </div>

            <div>

                <h1>
                    Stock Barang
                </h1>

                <div class="subtitle">
                    Pengelolaan persediaan barang dan jumlah stok
                </div>

            </div>

        </div>


        <a
            class="back"
            href="index.php"
        >

            <i class="fas fa-arrow-left"></i>

            Kembali

        </a>

    </div>


    <!-- =====================================================
         FORM
         ===================================================== -->

    <section class="card">

        <div class="card-title">

            <i class="fas
                <?= $editData
                    ? 'fa-pen-to-square'
                    : 'fa-plus-circle'
                ?>">
            </i>

            <?= $editData
                ? 'Edit Stock Barang'
                : 'Input Stock Barang'
            ?>

        </div>


        <form
            method="post"
            autocomplete="off"
        >

            <input
                type="hidden"
                name="action"
                value="<?= $editData
                    ? 'update'
                    : 'simpan'
                ?>"
            >


            <?php if ($editData): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= e(
                        $editData['id']
                    ) ?>"
                >

            <?php endif; ?>


            <div class="form-grid">


                <!-- NAMA BARANG -->

                <div class="field">

                    <label>
                        Nama Barang *
                    </label>

                    <input
                        type="text"
                        name="nama_barang"
                        value="<?= e(
                            $editData['nama_barang']
                            ?? ''
                        ) ?>"
                        placeholder="Contoh: Keyboard Logitech"
                        required
                    >

                </div>


                <!-- SPESIFIKASI -->

                <div class="field">

                    <label>
                        Spesifikasi
                    </label>

                    <textarea
                        name="spesifikasi"
                        placeholder="Merk, tipe, model, ukuran, kapasitas, warna, dll."
                    ><?= e(
                        $editData['spesifikasi']
                        ?? ''
                    ) ?></textarea>

                </div>


                <!-- NOMER ND -->

                <div class="field">

                    <label>
                        Nomer ND
                    </label>

                    <input
                        type="text"
                        name="nomer_nd"
                        value="<?= e(
                            $editData['nomer_nd']
                            ?? ''
                        ) ?>"
                        placeholder="Contoh: ND/123/IX/2026"
                    >

                </div>


                <!-- JUMLAH -->

                <div class="field">

                    <label>
                        Jumlah Barang *
                    </label>

                    <input
                        type="number"
                        name="jumlah_barang"
                        min="0"
                        step="0.01"
                        value="<?= e(
                            $editData['jumlah_barang']
                            ?? ''
                        ) ?>"
                        placeholder="0"
                        required
                    >

                </div>


                <!-- TAHUN -->

                <div class="field">

                    <label>
                        Tahun Pengadaan
                    </label>

                    <input
                        type="text"
                        name="tahun_pengadaan"
                        value="<?= e(
                            $editData['tahun_pengadaan']
                            ?? ''
                        ) ?>"
                        placeholder="2026"
                        maxlength="4"
                        inputmode="numeric"
                        pattern="[0-9]{4}"
                    >

                </div>


            </div>


            <!-- BUTTON FORM -->

            <div class="actions">

                <?php if ($editData): ?>

                    <a
                        href="stock_barang.php"
                        class="btn btn-cancel"
                    >

                        <i class="fas fa-xmark"></i>

                        Batal

                    </a>

                <?php endif; ?>


                <button
                    type="submit"
                    class="btn btn-save"
                >

                    <i class="fas
                        <?= $editData
                            ? 'fa-floppy-disk'
                            : 'fa-plus'
                        ?>">
                    </i>

                    <?= $editData
                        ? 'Simpan Perubahan'
                        : 'Tambah Stock'
                    ?>

                </button>

            </div>

        </form>

    </section>


    <!-- =====================================================
         DATA STOCK
         ===================================================== -->

    <section class="card">


        <div class="toolbar">


            <div>

                <div
                    class="card-title"
                    style="margin-bottom:8px;"
                >

                    <i class="fas fa-table-list"></i>

                    Data Stock Barang

                </div>


                <div class="stats">

                    <span class="stat">

                        <i class="fas fa-cubes"></i>

                        Jenis

                        <strong>
                            <?= number_format(
                                $totalJenis,
                                0,
                                ',',
                                '.'
                            ) ?>
                        </strong>

                    </span>


                    <span class="stat">

                        <i class="fas fa-box"></i>

                        Total Barang

                        <strong>

                            <?= e(
                                formatJumlah(
                                    $totalJumlah
                                )
                            ) ?>

                        </strong>

                    </span>

                </div>

            </div>


            <!-- SEARCH -->

            <form
                method="get"
                class="search"
            >

                <input
                    type="search"
                    name="search"
                    value="<?= e($keyword) ?>"
                    placeholder="Cari nama / spesifikasi / ND / tahun..."
                >


                <button
                    type="submit"
                    title="Cari"
                >

                    <i class="fas fa-magnifying-glass"></i>

                </button>


                <?php if ($keyword !== ''): ?>

                    <a
                        href="stock_barang.php"
                        class="btn btn-cancel"
                        title="Reset"
                    >

                        <i class="fas fa-rotate-left"></i>

                    </a>

                <?php endif; ?>

            </form>

        </div>


        <!-- =================================================
             TABLE
             ================================================= -->

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th style="width:60px;">
                            No
                        </th>

                        <th>
                            Nama Barang
                        </th>

                        <th>
                            Spesifikasi
                        </th>

                        <th>
                            Nomer ND
                        </th>

                        <th
                            style="
                                width:150px;
                                text-align:right;
                            "
                        >
                            Jumlah Barang
                        </th>

                        <th
                            style="width:130px;"
                        >
                            Tahun Pengadaan
                        </th>

                        <th
                            style="width:130px;"
                        >
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (!$stock): ?>


                    <tr>

                        <td
                            colspan="7"
                            class="empty"
                        >

                            <i
                                class="fas fa-box-open"
                                style="
                                    font-size:30px;
                                    display:block;
                                    margin-bottom:10px;
                                "
                            ></i>


                            <?= $keyword
                                ? 'Data tidak ditemukan.'
                                : 'Belum ada data stock barang.'
                            ?>

                        </td>

                    </tr>


                <?php else: ?>


                    <?php foreach (
                        $stock as $i => $row
                    ): ?>


                        <tr>


                            <!-- NO -->

                            <td>
                                <?= $i + 1 ?>
                            </td>


                            <!-- NAMA -->

                            <td>

                                <strong>

                                    <?= e(
                                        $row['nama_barang']
                                    ) ?>

                                </strong>

                            </td>


                            <!-- SPESIFIKASI -->

                            <td>

                                <?php if (
                                    trim(
                                        $row['spesifikasi']
                                    ) !== ''
                                ): ?>

                                    <?= nl2br(
                                        e(
                                            $row['spesifikasi']
                                        )
                                    ) ?>

                                <?php else: ?>

                                    <span class="note">
                                        Tidak ada spesifikasi
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ND -->

                            <td>

                                <?php if (
                                    !empty(
                                        $row['nomer_nd']
                                    )
                                ): ?>

                                    <span class="nd">

                                        <i
                                            class="fas fa-file-lines"
                                        ></i>

                                        <?= e(
                                            $row['nomer_nd']
                                        ) ?>

                                    </span>

                                <?php else: ?>

                                    <span class="note">
                                        -
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- JUMLAH -->

                            <td class="number">

                                <span
                                    class="badge-stock"
                                >

                                    <i
                                        class="fas fa-box"
                                    ></i>

                                    <?= e(
                                        formatJumlah(
                                            $row['jumlah_barang']
                                        )
                                    ) ?>

                                </span>

                            </td>


                            <!-- TAHUN -->

                            <td>

                                <?php if (
                                    !empty(
                                        $row['tahun_pengadaan']
                                    )
                                ): ?>

                                    <span class="tahun">

                                        <i
                                            class="fas fa-calendar"
                                        ></i>

                                        <?= e(
                                            $row['tahun_pengadaan']
                                        ) ?>

                                    </span>

                                <?php else: ?>

                                    <span class="note">
                                        -
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- =================================================
                                 AKSI EDIT + HAPUS
                                 ================================================= -->

                            <td
                                class="actions-cell"
                            >


                                <!-- EDIT -->

                                <button
                                    type="button"
                                    class="btn btn-edit btn-edit-js"
                                    data-id="<?= e(
                                        $row['id']
                                    ) ?>"
                                    data-nama="<?= e(
                                        $row['nama_barang']
                                    ) ?>"
                                    title="Edit Barang"
                                >

                                    <i
                                        class="fas fa-pen"
                                    ></i>

                                </button>


                                <!-- HAPUS -->

                                <form
                                    method="post"
                                    class="inline form-hapus"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="hapus"
                                    >


                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= e(
                                            $row['id']
                                        ) ?>"
                                    >


                                    <button
                                        class="btn btn-delete"
                                        type="submit"
                                        title="Hapus Barang"
                                    >

                                        <i
                                            class="fas fa-trash"
                                        ></i>

                                    </button>

                                </form>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php endif; ?>


                </tbody>

            </table>

        </div>

    </section>

</div>


<!-- ============================================================
     JAVASCRIPT
     ============================================================ -->

<script>

/* ============================================================
   SAAT HALAMAN SELESAI DIMUAT
   ============================================================ */

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /* ====================================================
           TOMBOL EDIT
           ==================================================== */

        const editButtons =
            document.querySelectorAll(
                '.btn-edit-js'
            );


        editButtons.forEach(
            function (button) {


                button.addEventListener(
                    'click',
                    function () {


                        const id =
                            this.dataset.id;


                        const nama =
                            this.dataset.nama;


                        Swal.fire({

                            title:
                                'Edit Barang?',

                            html:
                                'Anda akan mengedit barang:<br><br>' +
                                '<strong>' +
                                escapeHtml(nama) +
                                '</strong>',

                            icon:
                                'question',

                            showCancelButton:
                                true,

                            confirmButtonText:
                                '<i class="fas fa-pen"></i> Ya, Edit',

                            cancelButtonText:
                                '<i class="fas fa-times"></i> Batal',

                            confirmButtonColor:
                                '#2563eb',

                            cancelButtonColor:
                                '#64748b',

                            reverseButtons:
                                true,

                            focusCancel:
                                true

                        }).then(
                            function (result) {


                                if (
                                    result.isConfirmed
                                ) {


                                    window.location.href =
                                        'stock_barang.php?edit=' +
                                        encodeURIComponent(id);


                                }

                            }
                        );

                    }
                );

            }
        );


        /* ====================================================
           TOMBOL HAPUS
           ==================================================== */

        const deleteForms =
            document.querySelectorAll(
                '.form-hapus'
            );


        deleteForms.forEach(
            function (form) {


                form.addEventListener(
                    'submit',
                    function (event) {


                        event.preventDefault();


                        const nama =
                            form.closest('tr')
                                ?.querySelector(
                                    'td:nth-child(2) strong'
                                )
                                ?.textContent
                                .trim()
                            || 'barang ini';


                        Swal.fire({

                            title:
                                'Hapus Barang?',

                            html:
                                'Data <strong>' +
                                escapeHtml(nama) +
                                '</strong><br>' +
                                'akan dihapus secara permanen.',

                            icon:
                                'warning',

                            showCancelButton:
                                true,

                            confirmButtonText:
                                '<i class="fas fa-trash"></i> Ya, Hapus',

                            cancelButtonText:
                                '<i class="fas fa-times"></i> Batal',

                            confirmButtonColor:
                                '#dc3545',

                            cancelButtonColor:
                                '#64748b',

                            reverseButtons:
                                true,

                            focusCancel:
                                true

                        }).then(
                            function (result) {


                                if (
                                    result.isConfirmed
                                ) {


                                    /*
                                     * form.submit()
                                     * digunakan agar event
                                     * submit tidak memanggil
                                     * SweetAlert kedua kali.
                                     */

                                    form.submit();


                                }

                            }
                        );

                    }
                );

            }
        );


        /* ====================================================
           AUTO FOCUS SAAT MODE EDIT
           ==================================================== */

        <?php if ($editData): ?>

        const namaInput =
            document.querySelector(
                'input[name="nama_barang"]'
            );


        if (namaInput) {

            setTimeout(
                function () {

                    namaInput.focus();

                    namaInput.select();

                },
                300
            );

        }

        <?php endif; ?>


        /* ====================================================
           NOTIFIKASI HASIL PROSES PHP
           ==================================================== */

        <?php if ($message !== ''): ?>

        Swal.fire({

            icon:
                <?= json_encode(
                    $messageType
                ) ?>,

            title:
                <?= json_encode(
                    $messageType === 'success'
                        ? 'Berhasil'
                        : 'Gagal'
                ) ?>,

            text:
                <?= json_encode(
                    $message
                ) ?>,

            confirmButtonColor:
                '#2563eb',

            timer:
                2500,

            timerProgressBar:
                true

        });

        <?php endif; ?>


    }
);


/* ============================================================
   ESCAPE HTML
   ============================================================ */

function escapeHtml(text)
{

    const div =
        document.createElement('div');


    div.textContent =
        text;


    return div.innerHTML;

}

</script>


</body>

</html>

<?php

$conn->close();

?>