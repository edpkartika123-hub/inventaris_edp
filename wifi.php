```php
<?php
/**
 * ============================================================
 * DATA WIFI / ACCESS POINT
 * Sistem Inventaris Digital
 * Database : db_inventaris_digital
 * Tabel    : wifi
 * ============================================================
 */

session_start();

/* ============================================================
   KONFIGURASI DATABASE
   ============================================================ */

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_inventaris_digital';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');


/* ============================================================
   HELPER
   ============================================================ */

function clean($value)
{
    return trim((string)$value);
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}


/* ============================================================
   PILIHAN GEDUNG
   ============================================================ */

$gedungValid = [
    'Kartika 1',
    'Kartika 2',
    'Kartika 3',
    'CVC',
    'CICU'
];


/* ============================================================
   PILIHAN LANTAI
   ============================================================ */

$lantaiValid = [
    'Lantai 1',
    'Lantai 2',
    'Lantai 3',
    'Lantai 4',
    'Lantai 5',
    'Lantai 6'
];


/* ============================================================
   PESAN
   ============================================================ */

$message = '';
$messageType = '';


/* ============================================================
   DATA EDIT
   ============================================================ */

$editData = null;


/* ============================================================
   PROSES TAMBAH DATA
   ============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    $gedung            = clean($_POST['gedung'] ?? '');
    $lantai            = clean($_POST['lantai'] ?? '');
    $port              = clean($_POST['port'] ?? '');
    $ip                = clean($_POST['ip'] ?? '');

    $ssid1             = clean($_POST['ssid1'] ?? '');
    $ssid2             = clean($_POST['ssid2'] ?? '');
    $ssid3             = clean($_POST['ssid3'] ?? '');
    $ssid4             = clean($_POST['ssid4'] ?? '');

    $lokasi_wifi       = clean($_POST['lokasi_wifi'] ?? '');
    $koneksi_switch    = clean($_POST['koneksi_switch'] ?? '');
    $tipe_access_point = clean($_POST['tipe_access_point'] ?? '');
    $status            = clean($_POST['status'] ?? 'Aktif');
    $keterangan        = clean($_POST['keterangan'] ?? '');


    if (!in_array($gedung, $gedungValid, true)) {

        $message = 'Gedung yang dipilih tidak valid.';
        $messageType = 'error';

    } elseif (!in_array($lantai, $lantaiValid, true)) {

        $message = 'Lantai yang dipilih tidak valid.';
        $messageType = 'error';

    } else {

        $sql = "
            INSERT INTO wifi (
                gedung,
                lantai,
                port,
                ip,
                ssid1,
                ssid2,
                ssid3,
                ssid4,
                lokasi_wifi,
                koneksi_switch,
                tipe_access_point,
                status,
                keterangan
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "sssssssssssss",
                $gedung,
                $lantai,
                $port,
                $ip,
                $ssid1,
                $ssid2,
                $ssid3,
                $ssid4,
                $lokasi_wifi,
                $koneksi_switch,
                $tipe_access_point,
                $status,
                $keterangan
            );

            if ($stmt->execute()) {

                $message = 'Data WiFi berhasil ditambahkan.';
                $messageType = 'success';

            } else {

                $message = 'Gagal menyimpan data: ' . $stmt->error;
                $messageType = 'error';
            }

            $stmt->close();

        } else {

            $message = 'Query gagal diproses: ' . $conn->error;
            $messageType = 'error';
        }
    }
}


/* ============================================================
   PROSES UPDATE DATA
   ============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

    $id = (int)($_POST['id'] ?? 0);

    $gedung            = clean($_POST['gedung'] ?? '');
    $lantai            = clean($_POST['lantai'] ?? '');
    $port              = clean($_POST['port'] ?? '');
    $ip                = clean($_POST['ip'] ?? '');

    $ssid1             = clean($_POST['ssid1'] ?? '');
    $ssid2             = clean($_POST['ssid2'] ?? '');
    $ssid3             = clean($_POST['ssid3'] ?? '');
    $ssid4             = clean($_POST['ssid4'] ?? '');

    $lokasi_wifi       = clean($_POST['lokasi_wifi'] ?? '');
    $koneksi_switch    = clean($_POST['koneksi_switch'] ?? '');
    $tipe_access_point = clean($_POST['tipe_access_point'] ?? '');
    $status            = clean($_POST['status'] ?? 'Aktif');
    $keterangan        = clean($_POST['keterangan'] ?? '');


    if ($id <= 0) {

        $message = 'ID data tidak valid.';
        $messageType = 'error';

    } elseif (!in_array($gedung, $gedungValid, true)) {

        $message = 'Gedung yang dipilih tidak valid.';
        $messageType = 'error';

    } elseif (!in_array($lantai, $lantaiValid, true)) {

        $message = 'Lantai yang dipilih tidak valid.';
        $messageType = 'error';

    } else {

        $sql = "
            UPDATE wifi SET
                gedung = ?,
                lantai = ?,
                port = ?,
                ip = ?,
                ssid1 = ?,
                ssid2 = ?,
                ssid3 = ?,
                ssid4 = ?,
                lokasi_wifi = ?,
                koneksi_switch = ?,
                tipe_access_point = ?,
                status = ?,
                keterangan = ?
            WHERE id = ?
        ";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "sssssssssssssi",
                $gedung,
                $lantai,
                $port,
                $ip,
                $ssid1,
                $ssid2,
                $ssid3,
                $ssid4,
                $lokasi_wifi,
                $koneksi_switch,
                $tipe_access_point,
                $status,
                $keterangan,
                $id
            );

            if ($stmt->execute()) {

                $message = 'Data WiFi berhasil diperbarui.';
                $messageType = 'success';

            } else {

                $message = 'Gagal memperbarui data: ' . $stmt->error;
                $messageType = 'error';
            }

            $stmt->close();

        } else {

            $message = 'Query update gagal: ' . $conn->error;
            $messageType = 'error';
        }
    }
}


/* ============================================================
   PROSES HAPUS DATA
   ============================================================ */

if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    if ($id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM wifi WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {

                $message = 'Data WiFi berhasil dihapus.';
                $messageType = 'success';

            } else {

                $message = 'Gagal menghapus data: ' . $stmt->error;
                $messageType = 'error';
            }

            $stmt->close();
        }
    }
}


/* ============================================================
   PROSES EDIT
   ============================================================ */

if (isset($_GET['edit'])) {

    $id = (int)$_GET['edit'];

    if ($id > 0) {

        $stmt = $conn->prepare("
            SELECT *
            FROM wifi
            WHERE id = ?
            LIMIT 1
        ");

        if ($stmt) {

            $stmt->bind_param("i", $id);
            $stmt->execute();

            $resultEdit = $stmt->get_result();

            if ($resultEdit->num_rows > 0) {

                $editData = $resultEdit->fetch_assoc();

            } else {

                $message = 'Data WiFi tidak ditemukan.';
                $messageType = 'error';
            }

            $stmt->close();
        }
    }
}


/* ============================================================
   PENCARIAN
   ============================================================ */

$search = clean($_GET['search'] ?? '');

if ($search !== '') {

    $keyword = '%' . $search . '%';

    $stmt = $conn->prepare("
        SELECT *
        FROM wifi
        WHERE
            gedung LIKE ?
            OR lantai LIKE ?
            OR port LIKE ?
            OR ip LIKE ?
            OR ssid1 LIKE ?
            OR ssid2 LIKE ?
            OR ssid3 LIKE ?
            OR ssid4 LIKE ?
            OR lokasi_wifi LIKE ?
            OR koneksi_switch LIKE ?
            OR tipe_access_point LIKE ?
            OR status LIKE ?
            OR keterangan LIKE ?
        ORDER BY id DESC
    ");

    $stmt->bind_param(
        "sssssssssssss",
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $stmt->close();

} else {

    $result = $conn->query("
        SELECT *
        FROM wifi
        ORDER BY id DESC
    ");
}


/* ============================================================
   STATISTIK
   ============================================================ */

$totalData = 0;
$totalAktif = 0;
$totalTidakAktif = 0;

$statResult = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'Aktif') AS aktif,
        SUM(status <> 'Aktif') AS tidak_aktif
    FROM wifi
");

if ($statResult) {

    $stat = $statResult->fetch_assoc();

    $totalData = (int)($stat['total'] ?? 0);
    $totalAktif = (int)($stat['aktif'] ?? 0);
    $totalTidakAktif = (int)($stat['tidak_aktif'] ?? 0);
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

<title>Data WiFi / Access Point</title>


<style>

/* ============================================================
   RESET
   ============================================================ */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


html {
    scroll-behavior: smooth;
}


body {

    font-family:
        Inter,
        Segoe UI,
        Arial,
        Helvetica,
        sans-serif;

    min-height: 100vh;

    color: #e5e7eb;

    background:
        radial-gradient(
            circle at 10% 10%,
            rgba(37,99,235,.22),
            transparent 30%
        ),
        radial-gradient(
            circle at 90% 20%,
            rgba(14,165,233,.18),
            transparent 28%
        ),
        radial-gradient(
            circle at 50% 100%,
            rgba(139,92,246,.15),
            transparent 35%
        ),
        #020617;

    background-attachment: fixed;
}


/* ============================================================
   SCROLLBAR
   ============================================================ */

::-webkit-scrollbar {
    width: 9px;
    height: 9px;
}

::-webkit-scrollbar-track {
    background: rgba(15,23,42,.6);
}

::-webkit-scrollbar-thumb {
    background: rgba(96,165,250,.45);
    border-radius: 20px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(96,165,250,.7);
}


/* ============================================================
   CONTAINER
   ============================================================ */

.container {

    width: min(96%, 1550px);

    margin:
        25px auto
        50px;

}


/* ============================================================
   GLASS
   ============================================================ */

.glass {

    background:
        linear-gradient(
            135deg,
            rgba(255,255,255,.10),
            rgba(255,255,255,.035)
        );

    border:
        1px solid
        rgba(255,255,255,.13);

    box-shadow:
        0 20px 60px
        rgba(0,0,0,.30),

        inset 0 1px 0
        rgba(255,255,255,.08);

    backdrop-filter:
        blur(20px);

    -webkit-backdrop-filter:
        blur(20px);

}


/* ============================================================
   HEADER
   ============================================================ */

.header {

    position: relative;

    padding: 28px;

    border-radius: 22px;

    margin-bottom: 22px;

    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            rgba(30,41,59,.82),
            rgba(15,23,42,.58)
        );

    border:
        1px solid
        rgba(255,255,255,.13);

    box-shadow:
        0 25px 70px
        rgba(0,0,0,.35);

    backdrop-filter:
        blur(25px);

    -webkit-backdrop-filter:
        blur(25px);

}


.header::before {

    content: "";

    position: absolute;

    width: 180px;
    height: 180px;

    right: -60px;
    top: -70px;

    border-radius: 50%;

    background:
        rgba(59,130,246,.20);

    filter: blur(8px);

}


.header::after {

    content: "";

    position: absolute;

    width: 130px;
    height: 130px;

    left: -70px;
    bottom: -70px;

    border-radius: 50%;

    background:
        rgba(139,92,246,.16);

    filter: blur(8px);

}


.header h1 {

    position: relative;

    z-index: 2;

    color: #fff;

    font-size: clamp(24px, 4vw, 34px);

    margin-top: 15px;

    margin-bottom: 8px;

}


.header p {

    position: relative;

    z-index: 2;

    color: #94a3b8;

    font-size: 14px;

}


/* ============================================================
   LINK DATA JARINGAN
   ============================================================ */

.back-link {

    position: relative;

    z-index: 3;

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding:
        8px 13px;

    border-radius: 10px;

    color: #93c5fd;

    text-decoration: none;

    font-size: 14px;

    font-weight: 700;

    background:
        rgba(59,130,246,.10);

    border:
        1px solid
        rgba(96,165,250,.20);

    transition:
        .25s ease;

}


.back-link:hover {

    color: #fff;

    background:
        rgba(59,130,246,.22);

    border-color:
        rgba(96,165,250,.45);

    transform:
        translateX(-3px);

    box-shadow:
        0 8px 25px
        rgba(37,99,235,.20);

}


/* ============================================================
   ALERT
   ============================================================ */

.alert {

    padding: 15px 18px;

    border-radius: 14px;

    margin-bottom: 20px;

    font-weight: 700;

    backdrop-filter: blur(15px);

    animation:
        slideDown .35s ease;

}


@keyframes slideDown {

    from {
        opacity: 0;
        transform:
            translateY(-10px);
    }

    to {
        opacity: 1;
        transform:
            translateY(0);
    }

}


.alert.success {

    background:
        rgba(6,78,59,.48);

    border:
        1px solid
        rgba(16,185,129,.35);

    color: #a7f3d0;

}


.alert.error {

    background:
        rgba(127,29,29,.45);

    border:
        1px solid
        rgba(239,68,68,.40);

    color: #fecaca;

}


/* ============================================================
   STATISTIK
   ============================================================ */

.stats {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 16px;

    margin-bottom: 22px;

}


.stat-card {

    position: relative;

    overflow: hidden;

    padding: 22px;

    min-height: 125px;

    border-radius: 18px;

    background:
        linear-gradient(
            135deg,
            rgba(30,41,59,.72),
            rgba(15,23,42,.48)
        );

    border:
        1px solid
        rgba(255,255,255,.10);

    box-shadow:
        0 15px 40px
        rgba(0,0,0,.20);

    backdrop-filter:
        blur(20px);

    transition:
        transform .25s ease,
        border-color .25s ease;

}


.stat-card:hover {

    transform:
        translateY(-4px);

    border-color:
        rgba(96,165,250,.30);

}


.stat-card::after {

    content: "";

    position: absolute;

    right: -30px;
    bottom: -50px;

    width: 130px;
    height: 130px;

    border-radius: 50%;

    background:
        rgba(59,130,246,.08);

}


.stat-title {

    color: #94a3b8;

    font-size: 13px;

    margin-bottom: 9px;

}


.stat-number {

    font-size: 34px;

    font-weight: 800;

    color: #fff;

}


/* ============================================================
   CARD
   ============================================================ */

.form-card,
.search-card,
.table-card {

    background:
        linear-gradient(
            135deg,
            rgba(30,41,59,.72),
            rgba(15,23,42,.50)
        );

    border:
        1px solid
        rgba(255,255,255,.11);

    box-shadow:
        0 20px 55px
        rgba(0,0,0,.24);

    backdrop-filter:
        blur(20px);

    -webkit-backdrop-filter:
        blur(20px);

}


/* ============================================================
   FORM
   ============================================================ */

.form-card {

    padding: 26px;

    border-radius: 20px;

    margin-bottom: 22px;

}


.form-title {

    font-size: 20px;

    font-weight: 800;

    color: #fff;

    margin-bottom: 22px;

}


.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 17px;

}


.form-group {

    display: flex;

    flex-direction: column;

}


.form-group.full {

    grid-column:
        1 / -1;

}


label {

    font-size: 13px;

    font-weight: 700;

    margin-bottom: 8px;

    color: #cbd5e1;

}


input,
select,
textarea {

    width: 100%;

    padding:
        12px 14px;

    border-radius: 11px;

    border:
        1px solid
        rgba(148,163,184,.28);

    background:
        rgba(2,6,23,.52);

    color: #fff;

    outline: none;

    font-size: 14px;

    transition:
        .2s ease;

}


input::placeholder,
textarea::placeholder {

    color: #64748b;

}


input:hover,
select:hover,
textarea:hover {

    border-color:
        rgba(148,163,184,.45);

}


input:focus,
select:focus,
textarea:focus {

    border-color:
        rgba(59,130,246,.80);

    background:
        rgba(2,6,23,.68);

    box-shadow:
        0 0 0 3px
        rgba(59,130,246,.12);

}


select option {

    background: #0f172a;

    color: #fff;

}


textarea {

    min-height: 105px;

    resize: vertical;

}


/* ============================================================
   BUTTON
   ============================================================ */

.button-area {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;

    margin-top: 22px;

}


button,
.btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    min-height: 43px;

    padding:
        10px 17px;

    border-radius: 11px;

    border: 1px solid transparent;

    cursor: pointer;

    text-decoration: none;

    font-weight: 700;

    font-size: 13px;

    transition:
        .22s ease;

}


button:hover,
.btn:hover {

    transform:
        translateY(-2px);

}


.btn-primary {

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #3b82f6
        );

    color: #fff;

    box-shadow:
        0 8px 22px
        rgba(37,99,235,.25);

}


.btn-primary:hover {

    box-shadow:
        0 12px 28px
        rgba(37,99,235,.38);

}


.btn-warning {

    background:
        linear-gradient(
            135deg,
            #d97706,
            #f59e0b
        );

    color: #fff;

}


.btn-danger {

    background:
        linear-gradient(
            135deg,
            #dc2626,
            #ef4444
        );

    color: #fff;

}


.btn-secondary {

    background:
        rgba(71,85,105,.55);

    color: #fff;

    border-color:
        rgba(148,163,184,.18);

}


.btn-secondary:hover {

    background:
        rgba(71,85,105,.80);

}


/* ============================================================
   SEARCH
   ============================================================ */

.search-card {

    padding: 18px;

    border-radius: 18px;

    margin-bottom: 22px;

}


.search-form {

    display: flex;

    gap: 10px;

}


.search-form input {

    flex: 1;

}


/* ============================================================
   TABLE CARD
   ============================================================ */

.table-card {

    border-radius: 20px;

    overflow: hidden;

}


.table-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 20px 22px;

    border-bottom:
        1px solid
        rgba(255,255,255,.09);

    font-size: 19px;

    font-weight: 800;

    color: #fff;

}


.table-wrapper {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse:
        separate;

    border-spacing: 0;

    min-width: 1450px;

}


th,
td {

    padding:
        13px 14px;

    text-align: left;

    border-bottom:
        1px solid
        rgba(148,163,184,.10);

    vertical-align: middle;

}


th {

    background:
        rgba(2,6,23,.62);

    color: #93c5fd;

    font-size: 12px;

    white-space: nowrap;

    position: sticky;

    top: 0;

    z-index: 2;

}


td {

    font-size: 13px;

    color: #cbd5e1;

}


tbody tr {

    transition:
        .18s ease;

}


tbody tr:hover td {

    background:
        rgba(59,130,246,.055);

}


tbody tr:last-child td {

    border-bottom: none;

}


/* ============================================================
   STATUS
   ============================================================ */

.status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding:
        6px 10px;

    border-radius: 30px;

    font-size: 11px;

    font-weight: 800;

    white-space: nowrap;

}


.status::before {

    content: "";

    width: 7px;

    height: 7px;

    border-radius: 50%;

}


.status-aktif {

    background:
        rgba(16,185,129,.12);

    border:
        1px solid
        rgba(16,185,129,.20);

    color: #6ee7b7;

}


.status-aktif::before {

    background: #34d399;

    box-shadow:
        0 0 8px
        #34d399;

}


.status-tidak {

    background:
        rgba(239,68,68,.12);

    border:
        1px solid
        rgba(239,68,68,.20);

    color: #fca5a5;

}


.status-tidak::before {

    background: #f87171;

}


/* ============================================================
   ACTION
   ============================================================ */

.action {

    display: flex;

    gap: 7px;

    white-space: nowrap;

}


.action a {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding:
        7px 10px;

    border-radius: 8px;

    text-decoration: none;

    font-size: 11px;

    font-weight: 800;

    transition:
        .2s ease;

}


.action a:hover {

    transform:
        translateY(-2px);

}


.edit {

    background:
        rgba(217,119,6,.16);

    color: #fbbf24;

    border:
        1px solid
        rgba(245,158,11,.20);

}


.delete {

    background:
        rgba(220,38,38,.16);

    color: #fca5a5;

    border:
        1px solid
        rgba(239,68,68,.20);

}


/* ============================================================
   EMPTY
   ============================================================ */

.empty {

    text-align: center;

    padding: 55px 30px;

    color: #64748b;

}


.empty strong {

    color: #94a3b8;

}


/* ============================================================
   MOBILE CARD EFFECT
   ============================================================ */

@media (max-width: 900px) {

    .stats {

        grid-template-columns:
            1fr;

    }

    .form-grid {

        grid-template-columns:
            1fr;

    }

    .form-group.full {

        grid-column: auto;

    }

}


/* ============================================================
   TABLET / MOBILE
   ============================================================ */

@media (max-width: 600px) {

    .container {

        width: 94%;

        margin:
            14px auto
            30px;

    }


    .header {

        padding: 21px;

        border-radius: 18px;

    }


    .header h1 {

        font-size: 23px;

    }


    .header p {

        font-size: 13px;

    }


    .form-card {

        padding: 19px;

        border-radius: 17px;

    }


    .stats {

        gap: 11px;

    }


    .stat-card {

        min-height: 105px;

        padding: 18px;

    }


    .stat-number {

        font-size: 28px;

    }


    .search-form {

        flex-direction: column;

    }


    .search-form .btn {

        width: 100%;

    }


    .button-area {

        flex-direction: column;

    }


    .button-area .btn,
    .button-area button {

        width: 100%;

    }


    .table-header {

        font-size: 17px;

        padding: 17px;

    }

}


/* ============================================================
   SMALL ANIMATION
   ============================================================ */

.form-card,
.search-card,
.table-card,
.stat-card {

    animation:
        fadeUp .45s ease both;

}


@keyframes fadeUp {

    from {

        opacity: 0;

        transform:
            translateY(10px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}

</style>

</head>


<body>


<div class="container">


    <!-- =====================================================
         HEADER
         ===================================================== -->

    <div class="header">

        <a
            href="index.php"
            class="back-link"
        >
            ← Kembali
        </a>

        <h1>
            📡 Data WiFi / Access Point
        </h1>

        <p>
            Sistem Inventaris Digital • Manajemen Access Point
        </p>

    </div>


    <!-- =====================================================
         ALERT
         ===================================================== -->

    <?php if ($message !== ''): ?>

        <div class="alert <?= e($messageType) ?>">

            <?= e($message) ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         STATISTIK
         ===================================================== -->

    <div class="stats">

        <div class="stat-card">

            <div class="stat-title">
                Total Access Point
            </div>

            <div class="stat-number">
                <?= $totalData ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Access Point Aktif
            </div>

            <div class="stat-number">
                <?= $totalAktif ?>
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Tidak Aktif
            </div>

            <div class="stat-number">
                <?= $totalTidakAktif ?>
            </div>

        </div>

    </div>


    <!-- =====================================================
         FORM
         ===================================================== -->

    <div class="form-card">

        <div class="form-title">

            <?= $editData
                ? '✏️ Edit Data WiFi'
                : '➕ Tambah Data WiFi'
            ?>

        </div>


        <form method="POST">

            <?php if ($editData): ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= e($editData['id']) ?>"
                >

            <?php endif; ?>


            <div class="form-grid">


                <!-- GEDUNG -->

                <div class="form-group">

                    <label for="gedung">
                        Gedung *
                    </label>

                    <select
                        name="gedung"
                        id="gedung"
                        required
                    >

                        <option value="">
                            -- Pilih Gedung --
                        </option>

                        <?php foreach ($gedungValid as $gedungOption): ?>

                            <option
                                value="<?= e($gedungOption) ?>"
                                <?= (
                                    isset($editData['gedung'])
                                    && $editData['gedung'] === $gedungOption
                                ) ? 'selected' : '' ?>
                            >
                                <?= e($gedungOption) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- LANTAI -->

                <div class="form-group">

                    <label for="lantai">
                        Lantai *
                    </label>

                    <select
                        name="lantai"
                        id="lantai"
                        required
                    >

                        <option value="">
                            -- Pilih Lantai --
                        </option>

                        <?php foreach ($lantaiValid as $lantaiOption): ?>

                            <option
                                value="<?= e($lantaiOption) ?>"
                                <?= (
                                    isset($editData['lantai'])
                                    && $editData['lantai'] === $lantaiOption
                                ) ? 'selected' : '' ?>
                            >
                                <?= e($lantaiOption) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- PORT -->

                <div class="form-group">

                    <label for="port">
                        Port
                    </label>

                    <input
                        type="text"
                        name="port"
                        id="port"
                        placeholder="Contoh: Port 01"
                        value="<?= e($editData['port'] ?? '') ?>"
                    >

                </div>


                <!-- IP -->

                <div class="form-group">

                    <label for="ip">
                        IP Address
                    </label>

                    <input
                        type="text"
                        name="ip"
                        id="ip"
                        placeholder="Contoh: 192.168.1.10"
                        value="<?= e($editData['ip'] ?? '') ?>"
                    >

                </div>


                <!-- SSID 1 -->

                <div class="form-group">

                    <label for="ssid1">
                        SSID 1
                    </label>

                    <input
                        type="text"
                        name="ssid1"
                        id="ssid1"
                        placeholder="SSID 1"
                        value="<?= e($editData['ssid1'] ?? '') ?>"
                    >

                </div>


                <!-- SSID 2 -->

                <div class="form-group">

                    <label for="ssid2">
                        SSID 2
                    </label>

                    <input
                        type="text"
                        name="ssid2"
                        id="ssid2"
                        placeholder="SSID 2"
                        value="<?= e($editData['ssid2'] ?? '') ?>"
                    >

                </div>


                <!-- SSID 3 -->

                <div class="form-group">

                    <label for="ssid3">
                        SSID 3
                    </label>

                    <input
                        type="text"
                        name="ssid3"
                        id="ssid3"
                        placeholder="SSID 3"
                        value="<?= e($editData['ssid3'] ?? '') ?>"
                    >

                </div>


                <!-- SSID 4 -->

                <div class="form-group">

                    <label for="ssid4">
                        SSID 4
                    </label>

                    <input
                        type="text"
                        name="ssid4"
                        id="ssid4"
                        placeholder="SSID 4"
                        value="<?= e($editData['ssid4'] ?? '') ?>"
                    >

                </div>


                <!-- LOKASI WIFI -->

                <div class="form-group">

                    <label for="lokasi_wifi">
                        Lokasi WiFi
                    </label>

                    <input
                        type="text"
                        name="lokasi_wifi"
                        id="lokasi_wifi"
                        placeholder="Contoh: Ruang Meeting"
                        value="<?= e($editData['lokasi_wifi'] ?? '') ?>"
                    >

                </div>


                <!-- KONEKSI SWITCH -->

                <div class="form-group">

                    <label for="koneksi_switch">
                        Koneksi Switch
                    </label>

                    <input
                        type="text"
                        name="koneksi_switch"
                        id="koneksi_switch"
                        placeholder="Contoh: SW-KARTIKA-01"
                        value="<?= e($editData['koneksi_switch'] ?? '') ?>"
                    >

                </div>


                <!-- TIPE AP -->

                <div class="form-group">

                    <label for="tipe_access_point">
                        Tipe Access Point
                    </label>

                    <input
                        type="text"
                        name="tipe_access_point"
                        id="tipe_access_point"
                        placeholder="Contoh: Cisco / Aruba / TP-Link"
                        value="<?= e($editData['tipe_access_point'] ?? '') ?>"
                    >

                </div>


                <!-- STATUS -->

                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                    >

                        <option
                            value="Aktif"
                            <?= (
                                ($editData['status'] ?? 'Aktif')
                                === 'Aktif'
                            ) ? 'selected' : '' ?>
                        >
                            Aktif
                        </option>

                        <option
                            value="Tidak Aktif"
                            <?= (
                                ($editData['status'] ?? '')
                                === 'Tidak Aktif'
                            ) ? 'selected' : '' ?>
                        >
                            Tidak Aktif
                        </option>

                    </select>

                </div>


                <!-- KETERANGAN -->

                <div class="form-group full">

                    <label for="keterangan">
                        Keterangan
                    </label>

                    <textarea
                        name="keterangan"
                        id="keterangan"
                        placeholder="Masukkan keterangan..."
                    ><?= e($editData['keterangan'] ?? '') ?></textarea>

                </div>

            </div>


            <!-- BUTTON -->

            <div class="button-area">

                <?php if ($editData): ?>

                    <button
                        type="submit"
                        name="update"
                        class="btn btn-warning"
                    >
                        💾 Update Data
                    </button>

                    <a
                        href="wifi.php"
                        class="btn btn-secondary"
                    >
                        ✖ Batal
                    </a>

                <?php else: ?>

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-primary"
                    >
                        💾 Simpan Data
                    </button>

                <?php endif; ?>

            </div>

        </form>

    </div>


    <!-- =====================================================
         SEARCH
         ===================================================== -->

    <div class="search-card">

        <form
            method="GET"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                placeholder="🔍 Cari gedung, lantai, IP, SSID, lokasi, switch..."
                value="<?= e($search) ?>"
            >

            <button
                type="submit"
                class="btn btn-primary"
            >
                🔍 Cari
            </button>

            <?php if ($search !== ''): ?>

                <a
                    href="wifi.php"
                    class="btn btn-secondary"
                >
                    ↻ Reset
                </a>

            <?php endif; ?>

        </form>

    </div>


    <!-- =====================================================
         TABEL
         ===================================================== -->

    <div class="table-card">

        <div class="table-header">

            <span>
                📋 Daftar Data WiFi / Access Point
            </span>

        </div>


        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>

                        <th>No</th>

                        <th>Gedung</th>

                        <th>Lantai</th>

                        <th>Port</th>

                        <th>IP Address</th>

                        <th>SSID 1</th>

                        <th>SSID 2</th>

                        <th>SSID 3</th>

                        <th>SSID 4</th>

                        <th>Lokasi WiFi</th>

                        <th>Koneksi Switch</th>

                        <th>Tipe Access Point</th>

                        <th>Status</th>

                        <th>Keterangan</th>

                        <th>Aksi</th>

                    </tr>

                </thead>


                <tbody>

                <?php

                $no = 1;

                if ($result && $result->num_rows > 0):

                    while ($row = $result->fetch_assoc()):

                ?>

                    <tr>

                        <td>
                            <?= $no++ ?>
                        </td>


                        <td>

                            <strong style="color:#fff;">
                                <?= e($row['gedung']) ?>
                            </strong>

                        </td>


                        <td>
                            <?= e($row['lantai']) ?>
                        </td>


                        <td>
                            <?= e($row['port']) ?>
                        </td>


                        <td>
                            <?= e($row['ip']) ?>
                        </td>


                        <td>
                            <?= e($row['ssid1']) ?>
                        </td>


                        <td>
                            <?= e($row['ssid2']) ?>
                        </td>


                        <td>
                            <?= e($row['ssid3']) ?>
                        </td>


                        <td>
                            <?= e($row['ssid4']) ?>
                        </td>


                        <td>
                            <?= e($row['lokasi_wifi']) ?>
                        </td>


                        <td>
                            <?= e($row['koneksi_switch']) ?>
                        </td>


                        <td>
                            <?= e($row['tipe_access_point']) ?>
                        </td>


                        <td>

                            <?php if ($row['status'] === 'Aktif'): ?>

                                <span class="status status-aktif">
                                    Aktif
                                </span>

                            <?php else: ?>

                                <span class="status status-tidak">
                                    <?= e($row['status']) ?>
                                </span>

                            <?php endif; ?>

                        </td>


                        <td>
                            <?= e($row['keterangan']) ?>
                        </td>


                        <td>

                            <div class="action">

                                <a
                                    href="wifi.php?edit=<?= (int)$row['id'] ?>"
                                    class="edit"
                                >
                                    ✏️ Edit
                                </a>


                                <a
                                    href="wifi.php?hapus=<?= (int)$row['id'] ?>"
                                    class="delete"
                                    onclick="return confirm('Yakin ingin menghapus data WiFi ini?');"
                                >
                                    🗑 Hapus
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php

                    endwhile;

                else:

                ?>

                    <tr>

                        <td
                            colspan="15"
                            class="empty"
                        >

                            <?php if ($search !== ''): ?>

                                Data dengan kata pencarian
                                <strong>
                                    "<?= e($search) ?>"
                                </strong>
                                tidak ditemukan.

                            <?php else: ?>

                                Belum ada data WiFi.

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


</div>


</body>

</html>

<?php

$conn->close();

?>
```
