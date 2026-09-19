<?php
// ============================================
// data_kegiatan.php
// Tampilan data kegiatan + Dashboard Grafik
// ============================================

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_inventaris_digital';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

// Hapus data
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM kegiatan WHERE id = $id");
    header('Location: data_kegiatan.php');
    exit;
}

// Filter
$where = [];
$params = [];
$types = '';

if (!empty($_GET['cari_nama'])) {
    $where[] = "nama LIKE ?";
    $params[] = '%' . $_GET['cari_nama'] . '%';
    $types .= 's';
}
if (!empty($_GET['cari_lokasi'])) {
    $where[] = "lokasi LIKE ?";
    $params[] = '%' . $_GET['cari_lokasi'] . '%';
    $types .= 's';
}
if (!empty($_GET['cari_bulan'])) {
    $where[] = "bulan LIKE ?";
    $params[] = '%' . $_GET['cari_bulan'] . '%';
    $types .= 's';
}

$sql = "SELECT * FROM kegiatan";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY tanggal DESC, jam DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ============================================
// DATA AGREGAT UNTUK GRAFIK
// ============================================

// 1. Kegiatan per bulan
$chartBulan = [];
$resBulan = $conn->query("SELECT bulan, COUNT(*) as jumlah FROM kegiatan GROUP BY bulan ORDER BY FIELD(bulan, 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember')");
while ($r = $resBulan->fetch_assoc()) {
    $chartBulan[] = $r;
}

// 2. Kegiatan per lokasi (top 8)
$chartLokasi = [];
$resLokasi = $conn->query("SELECT lokasi, COUNT(*) as jumlah FROM kegiatan GROUP BY lokasi ORDER BY jumlah DESC LIMIT 8");
while ($r = $resLokasi->fetch_assoc()) {
    $chartLokasi[] = $r;
}

// 3. Jenis kerusakan (top 6 yang tidak kosong)
$chartKerusakan = [];
$resKerusakan = $conn->query("SELECT jenis_kerusakan, COUNT(*) as jumlah FROM kegiatan WHERE jenis_kerusakan IS NOT NULL AND jenis_kerusakan != '' GROUP BY jenis_kerusakan ORDER BY jumlah DESC LIMIT 6");
while ($r = $resKerusakan->fetch_assoc()) {
    $chartKerusakan[] = $r;
}

// 4. Kegiatan per User (top 10)
$chartUser = [];
$resUser = $conn->query("SELECT nama, COUNT(*) as jumlah FROM kegiatan GROUP BY nama ORDER BY jumlah DESC LIMIT 10");
while ($r = $resUser->fetch_assoc()) {
    $chartUser[] = $r;
}

// 5. Statistik
$totalKegiatan = $conn->query("SELECT COUNT(*) as total FROM kegiatan")->fetch_assoc()['total'];
$totalLokasi = $conn->query("SELECT COUNT(DISTINCT lokasi) as total FROM kegiatan")->fetch_assoc()['total'];
$totalBulan = $conn->query("SELECT COUNT(DISTINCT bulan) as total FROM kegiatan")->fetch_assoc()['total'];
$totalDenganFoto = $conn->query("SELECT COUNT(*) as total FROM kegiatan WHERE foto IS NOT NULL AND foto != ''")->fetch_assoc()['total'];
$totalUser = $conn->query("SELECT COUNT(DISTINCT nama) as total FROM kegiatan")->fetch_assoc()['total'];

// Array nama bulan Indonesia
$bulanIndo = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kegiatan - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --hijau-tua: #1e3a2e;
            --hijau-muda: #2d5a45;
            --hijau-lebih-muda: #3d7a5f;
            --emas: #d4af37;
            --emas-muda: #f0d878;
            --putih: #ffffff;
            --abu: #f4f6f5;
            --glass-bg: rgba(255,255,255,0.08);
            --glass-border: rgba(212,175,55,0.15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            background: linear-gradient(135deg, #0f1f17 0%, var(--hijau-tua) 40%, var(--hijau-muda) 100%);
            min-height: 100vh;
            padding: 30px 15px;
        }
        .container {
            max-width: 1300px;
            margin: 0 auto;
        }

        /* ========== HEADER ========== */
        .header-card {
            background: linear-gradient(135deg, var(--putih) 0%, #faf8f0 100%);
            border-radius: 20px;
            padding: 32px 36px;
            margin-bottom: 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            border: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }
        .header-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--emas), var(--emas-muda), var(--emas));
        }
        .header-card h1 {
            color: var(--hijau-tua);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-card h1 i {
            color: var(--emas);
            font-size: 1.4rem;
            animation: floatIcon 3s ease-in-out infinite;
        }
        .header-sub {
            color: #777;
            font-size: 0.9rem;
            margin-top: 6px;
        }
        .btn-tambah {
            background: linear-gradient(135deg, var(--emas), #c9a02c);
            color: var(--hijau-tua);
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(212,175,55,0.35);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
            cursor: pointer;
        }
        .btn-tambah:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(212,175,55,0.5);
        }

        /* ========== STATS CARDS ========== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(250,248,240,0.95));
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border: 1px solid rgba(212,175,55,0.1);
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            position: relative;
            overflow: hidden;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%; right: -50%;
            width: 100%; height: 100%;
            background: radial-gradient(circle, rgba(212,175,55,0.08), transparent 70%);
            pointer-events: none;
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.2);
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 12px;
        }
        .stat-icon.blue { background: linear-gradient(135deg, #e3f2fd, #bbdefb); color: #1565c0; }
        .stat-icon.green { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); color: #2e7d32; }
        .stat-icon.orange { background: linear-gradient(135deg, #fff3e0, #ffe0b2); color: #e65100; }
        .stat-icon.purple { background: linear-gradient(135deg, #f3e5f5, #e1bee7); color: #6a1b9a; }
        .stat-icon.teal { background: linear-gradient(135deg, #e0f2f1, #b2dfdb); color: #00695c; }
        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--hijau-tua);
            line-height: 1;
            margin-bottom: 6px;
        }
        .stat-label {
            font-size: 0.82rem;
            color: #888;
            font-weight: 600;
        }
        .stat-trend {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            margin-top: 8px;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        .trend-up { background: #e8f5e9; color: #2e7d32; }

        /* ========== CHARTS SECTION ========== */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .charts-section-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }
        .chart-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(250,248,240,0.95));
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border: 1px solid rgba(212,175,55,0.1);
            transition: all 0.4s ease;
        }
        .chart-card:hover {
            box-shadow: 0 14px 40px rgba(0,0,0,0.2);
        }
        .chart-card.full-width {
            grid-column: 1 / -1;
        }
        .chart-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .chart-header i {
            width: 40px; height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--hijau-tua), var(--hijau-muda));
            color: var(--emas);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .chart-header h3 {
            color: var(--hijau-tua);
            font-size: 1.1rem;
            font-weight: 700;
        }
        .chart-header span {
            color: #999;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .chart-body {
            position: relative;
            height: 280px;
        }
        .chart-body.small {
            height: 240px;
        }

        /* ========== FILTER ========== */
        .filter-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(250,248,240,0.95));
            border-radius: 18px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border: 1px solid rgba(212,175,55,0.1);
        }
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            align-items: end;
        }
        .filter-form input {
            padding: 13px 16px;
            border: 2px solid #e8e4d8;
            border-radius: 12px;
            font-size: 0.9rem;
            width: 100%;
            background: #faf8f0;
            transition: all 0.3s;
        }
        .filter-form input:focus {
            outline: none;
            border-color: var(--emas);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(212,175,55,0.15);
        }
        .btn-cari, .btn-reset {
            padding: 13px 22px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-cari {
            background: linear-gradient(135deg, var(--hijau-tua), var(--hijau-muda));
            color: var(--emas);
        }
        .btn-cari:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(30,58,46,0.3);
        }
        .btn-reset {
            background: #eee8d8;
            color: #665;
            text-decoration: none;
        }
        .btn-reset:hover {
            background: #e0d8c8;
            transform: translateY(-2px);
        }

        /* ========== TABLE ========== */
        .table-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(250,248,240,0.95));
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
            border: 1px solid rgba(212,175,55,0.1);
        }
        .table-wrap {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: linear-gradient(135deg, var(--hijau-tua), var(--hijau-muda));
            color: var(--emas);
        }
        th {
            padding: 18px 14px;
            text-align: left;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            white-space: nowrap;
        }
        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            font-size: 0.88rem;
            color: #333;
            vertical-align: top;
        }
        tbody tr {
            transition: all 0.2s;
            animation: fadeInUp 0.5s ease both;
        }
        tbody tr:hover {
            background: #f8f6f0;
            transform: scale(1.002);
        }
        tbody tr:nth-child(even) { background: #fcfbfa; }
        tbody tr:nth-child(even):hover { background: #f4f2ea; }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .badge-tanggal { background: #e8f5e9; color: #2e7d32; }
        .badge-jam { background: #e3f2fd; color: #1565c0; }
        .badge-bulan { background: #fff8e1; color: #e65100; }

        .foto-thumb {
            width: 56px; height: 56px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid var(--emas);
            cursor: pointer;
            transition: all 0.3s;
        }
        .foto-thumb:hover {
            transform: scale(1.15);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        .no-foto {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #eee, #ddd);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #bbb;
            font-size: 1.2rem;
        }

        .text-wrap {
            max-width: 260px;
            word-wrap: break-word;
            line-height: 1.5;
        }
        .text-muted { color: #aaa; font-style: italic; }

        .aksi {
            display: flex;
            gap: 8px;
        }
        .aksi a {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        .btn-edit { background: #fff3e0; color: #e65100; }
        .btn-edit:hover { background: #e65100; color: #fff; transform: translateY(-2px); }
        .btn-hapus { background: #ffebee; color: #c62828; }
        .btn-hapus:hover { background: #c62828; color: #fff; transform: translateY(-2px); }

        .empty {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        .empty i { font-size: 3.5rem; color: #ddd; margin-bottom: 16px; display: block; animation: floatIcon 3s ease-in-out infinite; }

        .footer-bar {
            background: linear-gradient(135deg, var(--hijau-tua), #152b22);
            color: rgba(255,255,255,0.6);
            padding: 18px 28px;
            font-size: 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .footer-bar span { color: var(--emas); font-weight: 700; }

        /* ========== MODAL ========== */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }
        .modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 16px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.6);
            animation: zoomIn 0.3s ease;
        }
        .modal-close {
            position: absolute;
            top: 24px; right: 36px;
            color: #fff;
            font-size: 2.8rem;
            cursor: pointer;
            transition: all 0.3s;
            width: 50px; height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .modal-close:hover {
            background: rgba(255,255,255,0.1);
            transform: rotate(90deg);
        }

        /* ========== ANIMATIONS ========== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.85); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes floatIcon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .animate-in {
            animation: fadeInUp 0.6s ease both;
        }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 992px) {
            .charts-section { grid-template-columns: 1fr; }
            .charts-section-2 { grid-template-columns: 1fr; }
            .chart-card.full-width { grid-column: 1; }
        }
        @media (max-width: 768px) {
            .header-card { text-align: center; justify-content: center; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            th, td { padding: 10px 8px; font-size: 0.8rem; }
            .text-wrap { max-width: 150px; }
            .chart-body { height: 220px; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .header-card h1 { font-size: 1.3rem; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- HEADER -->
    <div class="header-card animate-in">
        <div>
            <h1><i class="fas fa-chart-line"></i> Dashboard Kegiatan</h1>
            <p class="header-sub">Pantau dan kelola seluruh data kegiatan perbaikan & pemeliharaan</p>
        </div>
        <a href="input_kegiatan.php" class="btn-tambah"><i class="fas fa-plus"></i> Tambah Kegiatan</a>
    </div>

    <!-- STATS CARDS -->
    <div class="stats-grid">
        <div class="stat-card animate-in delay-1">
            <div class="stat-icon blue"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-value" id="statTotal"><?= $totalKegiatan ?></div>
            <div class="stat-label">Total Kegiatan</div>
            <div class="stat-trend trend-up"><i class="fas fa-arrow-trend-up"></i> Semua data</div>
        </div>
        <div class="stat-card animate-in delay-2">
            <div class="stat-icon green"><i class="fas fa-map-marker-alt"></i></div>
            <div class="stat-value" id="statLokasi"><?= $totalLokasi ?></div>
            <div class="stat-label">Lokasi Terdata</div>
            <div class="stat-trend trend-up"><i class="fas fa-location-dot"></i> Titik lokasi</div>
        </div>
        <div class="stat-card animate-in delay-3">
            <div class="stat-icon orange"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-value" id="statBulan"><?= $totalBulan ?></div>
            <div class="stat-label">Bulan Aktif</div>
            <div class="stat-trend trend-up"><i class="fas fa-clock"></i> Periode</div>
        </div>
        <div class="stat-card animate-in delay-4">
            <div class="stat-icon purple"><i class="fas fa-camera"></i></div>
            <div class="stat-value" id="statFoto"><?= $totalDenganFoto ?></div>
            <div class="stat-label">Dengan Foto</div>
            <div class="stat-trend trend-up"><i class="fas fa-image"></i> Dokumentasi</div>
        </div>
        <div class="stat-card animate-in delay-5">
            <div class="stat-icon teal"><i class="fas fa-users"></i></div>
            <div class="stat-value" id="statUser"><?= $totalUser ?></div>
            <div class="stat-label">User/Pekerja</div>
            <div class="stat-trend trend-up"><i class="fas fa-user-check"></i> Petugas</div>
        </div>
    </div>

    <!-- CHARTS BARIS 1 -->
    <div class="charts-section">
        <!-- Chart 1: Kegiatan per Bulan -->
        <div class="chart-card animate-in delay-2">
            <div class="chart-header">
                <i class="fas fa-chart-column"></i>
                <div>
                    <h3>Kegiatan per Bulan</h3>
                    <span>Distribusi jumlah kegiatan tiap bulan</span>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="chartBulan"></canvas>
            </div>
        </div>

        <!-- Chart 2: Jenis Kerusakan -->
        <div class="chart-card animate-in delay-3">
            <div class="chart-header">
                <i class="fas fa-chart-pie"></i>
                <div>
                    <h3>Jenis Kerusakan</h3>
                    <span>Top kategori kerusakan</span>
                </div>
            </div>
            <div class="chart-body small">
                <canvas id="chartKerusakan"></canvas>
            </div>
        </div>
    </div>

    <!-- CHARTS BARIS 2 -->
    <div class="charts-section-2">
        <!-- Chart 3: Kegiatan per Lokasi -->
        <div class="chart-card animate-in delay-4">
            <div class="chart-header">
                <i class="fas fa-map"></i>
                <div>
                    <h3>Kegiatan per Lokasi</h3>
                    <span>Top 8 lokasi dengan kegiatan terbanyak</span>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="chartLokasi"></canvas>
            </div>
        </div>

        <!-- Chart 4: Kegiatan per User -->
        <div class="chart-card animate-in delay-5">
            <div class="chart-header">
                <i class="fas fa-user-tag"></i>
                <div>
                    <h3>Kegiatan per User</h3>
                    <span>Top 10 user dengan kegiatan terbanyak</span>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="chartUser"></canvas>
            </div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="filter-card animate-in">
        <form method="GET" class="filter-form">
            <div>
                <input type="text" name="cari_nama" placeholder="Cari nama..." value="<?= htmlspecialchars($_GET['cari_nama'] ?? '') ?>">
            </div>
            <div>
                <input type="text" name="cari_lokasi" placeholder="Cari lokasi..." value="<?= htmlspecialchars($_GET['cari_lokasi'] ?? '') ?>">
            </div>
            <div>
                <input type="text" name="cari_bulan" placeholder="Cari bulan..." value="<?= htmlspecialchars($_GET['cari_bulan'] ?? '') ?>">
            </div>
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-cari"><i class="fas fa-search"></i> Cari</button>
                <a href="data_kegiatan.php" class="btn-reset"><i class="fas fa-undo"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table-card animate-in">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal & Jam</th>
                        <th>Nama</th>
                        <th>Lokasi</th>
                        <th>Jenis Kerusakan</th>
                        <th>Keterangan</th>
                        <th>Foto</th>
                        <th>Bulan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows === 0): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty">
                                    <i class="fas fa-inbox"></i>
                                    <p>Belum ada data kegiatan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; while ($row = $result->fetch_assoc()):
                            $t = date('d', strtotime($row['tanggal'])) . ' ' .
                                 $bulanIndo[date('m', strtotime($row['tanggal']))] . ' ' .
                                 date('Y', strtotime($row['tanggal']));
                        ?>
                        <tr>
                            <td><span class="badge" style="background:#f0f0f0;color:#555;">#<?= $no++ ?></span></td>
                            <td>
                                <span class="badge badge-tanggal"><i class="far fa-calendar"></i> <?= $t ?></span><br>
                                <span class="badge badge-jam" style="margin-top:6px;display:inline-block;"><i class="far fa-clock"></i> <?= substr($row['jam'], 0, 5) ?></span>
                            </td>
                            <td><strong style="color:var(--hijau-tua);"><?= htmlspecialchars($row['nama']) ?></strong></td>
                            <td><span class="badge" style="background:#e8f5e9;color:#2e7d32;"><i class="fas fa-map-marker-alt" style="font-size:0.7em;"></i> <?= htmlspecialchars($row['lokasi']) ?></span></td>
                            <td>
                                <?php if ($row['jenis_kerusakan']): ?>
                                    <span class="text-wrap"><?= htmlspecialchars($row['jenis_kerusakan']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['keterangan']): ?>
                                    <span class="text-wrap"><?= nl2br(htmlspecialchars($row['keterangan'])) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['foto'] && file_exists($row['foto'])): ?>
                                    <img src="<?= $row['foto'] ?>" class="foto-thumb" onclick="openModal('<?= $row['foto'] ?>')">
                                <?php else: ?>
                                    <div class="no-foto"><i class="far fa-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-bulan"><?= htmlspecialchars($row['bulan']) ?></span></td>
                            <td>
                                <div class="aksi">
                                    <a href="edit_kegiatan.php?id=<?= $row['id'] ?>" class="btn-edit" title="Edit"><i class="fas fa-pen"></i></a>
                                    <a href="?hapus=<?= $row['id'] ?>" class="btn-hapus" title="Hapus" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="footer-bar">
            <span><i class="fas fa-database"></i> db_inventaris_digital</span>
            <div>Total Data Tabel: <span><?= $result->num_rows ?></span> kegiatan</div>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW FOTO -->
<div id="fotoModal" class="modal" onclick="closeModal()">
    <span class="modal-close">&times;</span>
    <img id="modalImg" src="">
</div>

<script>
// ========== CHART CONFIGURATIONS ==========

// Warna tema hijau-emas
const colors = {
    emas: '#d4af37',
    emasMuda: '#f0d878',
    hijauTua: '#1e3a2e',
    hijauMuda: '#2d5a45',
    hijauLebihMuda: '#3d7a5f',
    palette: ['#d4af37', '#1e3a2e', '#2d5a45', '#c9a02c', '#3d7a5f', '#e8c84b', '#4a8c6f', '#b8941f', '#5a9e7f', '#a08020']
};

// Chart 1: Kegiatan per Bulan (Bar)
const ctxBulan = document.getElementById('chartBulan').getContext('2d');
new Chart(ctxBulan, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($chartBulan, 'bulan')) ?>,
        datasets: [{
            label: 'Jumlah Kegiatan',
            data: <?= json_encode(array_map('intval', array_column($chartBulan, 'jumlah'))) ?>,
            backgroundColor: colors.palette,
            borderRadius: 10,
            borderSkipped: false,
            barThickness: 28,
            hoverBackgroundColor: colors.emasMuda
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 1500, easing: 'easeOutQuart' },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: colors.hijauTua,
                titleColor: colors.emas,
                bodyColor: '#fff',
                cornerRadius: 10,
                padding: 12,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return context.parsed.y + ' kegiatan';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                ticks: { color: '#888', font: { weight: '600' } }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#666', font: { weight: '600' } }
            }
        }
    }
});

// Chart 2: Jenis Kerusakan (Doughnut)
const ctxKerusakan = document.getElementById('chartKerusakan').getContext('2d');
new Chart(ctxKerusakan, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($chartKerusakan, 'jenis_kerusakan')) ?>,
        datasets: [{
            data: <?= json_encode(array_map('intval', array_column($chartKerusakan, 'jumlah'))) ?>,
            backgroundColor: colors.palette,
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 12
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        animation: { animateScale: true, animateRotate: true, duration: 1500 },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    color: '#555',
                    font: { size: 11, weight: '600' }
                }
            },
            tooltip: {
                backgroundColor: colors.hijauTua,
                titleColor: colors.emas,
                bodyColor: '#fff',
                cornerRadius: 10,
                padding: 12,
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a,b) => a+b, 0);
                        const pct = ((context.parsed / total) * 100).toFixed(1);
                        return ' ' + context.parsed + ' kegiatan (' + pct + '%)';
                    }
                }
            }
        }
    }
});

// Chart 3: Kegiatan per Lokasi (Horizontal Bar)
const ctxLokasi = document.getElementById('chartLokasi').getContext('2d');
new Chart(ctxLokasi, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($chartLokasi, 'lokasi')) ?>,
        datasets: [{
            label: 'Jumlah Kegiatan',
            data: <?= json_encode(array_map('intval', array_column($chartLokasi, 'jumlah'))) ?>,
            backgroundColor: function(context) {
                const val = context.raw;
                const max = Math.max(...context.chart.data.datasets[0].data);
                const opacity = 0.4 + (val / max) * 0.6;
                return 'rgba(30, 58, 46, ' + opacity + ')';
            },
            borderRadius: 8,
            borderSkipped: false,
            barThickness: 22
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 1500, easing: 'easeOutQuart' },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: colors.hijauTua,
                titleColor: colors.emas,
                bodyColor: '#fff',
                cornerRadius: 10,
                padding: 12,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return context.parsed.x + ' kegiatan di lokasi ini';
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                ticks: { color: '#888', font: { weight: '600' } }
            },
            y: {
                grid: { display: false },
                ticks: { color: '#444', font: { weight: '700', size: 12 } }
            }
        }
    }
});

// Chart 4: Kegiatan per User (Bar Chart)
const ctxUser = document.getElementById('chartUser').getContext('2d');
new Chart(ctxUser, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($chartUser, 'nama')) ?>,
        datasets: [{
            label: 'Jumlah Kegiatan',
            data: <?= json_encode(array_map('intval', array_column($chartUser, 'jumlah'))) ?>,
            backgroundColor: function(context) {
                const val = context.raw;
                const max = Math.max(...context.chart.data.datasets[0].data);
                const opacity = 0.35 + (val / max) * 0.65;
                return 'rgba(212, 175, 55, ' + opacity + ')';
            },
            borderColor: colors.emas,
            borderWidth: 1,
            borderRadius: 10,
            borderSkipped: false,
            barThickness: 24,
            hoverBackgroundColor: colors.emasMuda
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 1500, easing: 'easeOutQuart' },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: colors.hijauTua,
                titleColor: colors.emas,
                bodyColor: '#fff',
                cornerRadius: 10,
                padding: 12,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return context.parsed.y + ' kegiatan dikerjakan';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                ticks: { color: '#888', font: { weight: '600' } }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#555', font: { weight: '600', size: 11 } }
            }
        }
    }
});

// ========== UTILITIES ==========
function openModal(src) {
    document.getElementById('modalImg').src = src;
    document.getElementById('fotoModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('fotoModal').style.display = 'none';
}

// Animasi angka statistik
function animateValue(id, start, end, duration) {
    const obj = document.getElementById(id);
    if (!obj) return;
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

window.addEventListener('load', () => {
    animateValue('statTotal', 0, <?= $totalKegiatan ?>, 1200);
    animateValue('statLokasi', 0, <?= $totalLokasi ?>, 1200);
    animateValue('statBulan', 0, <?= $totalBulan ?>, 1200);
    animateValue('statFoto', 0, <?= $totalDenganFoto ?>, 1200);
    animateValue('statUser', 0, <?= $totalUser ?>, 1200);
});
</script>

</body>
</html>
<?php $conn->close(); ?>