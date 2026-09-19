<?php
/**
 * Aplikasi Inventaris - Halaman Kegiatan v2.0
 * Inline Edit + Dynamic UI + Professional Icons
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_inventaris_digital';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

$pesan = '';
$statusMsg = '';

// --- PROSES HAPUS ---
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM kegiatan WHERE id = $id");
    header('Location: kegiatan.php');
    exit;
}

// --- PROSES UPDATE (Inline Edit) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $id              = intval($_POST['update_id']);
    $tanggal         = $_POST['edit_tanggal'] ?? '';
    $jam             = $_POST['edit_jam'] ?? '';
    $nama            = trim($_POST['edit_nama'] ?? '');
    $lokasi          = trim($_POST['edit_lokasi'] ?? '');
    $jenis_kerusakan = trim($_POST['edit_jenis_kerusakan'] ?? '');
    $keterangan      = trim($_POST['edit_keterangan'] ?? '');
    $bulan           = trim($_POST['edit_bulan'] ?? '');

    $resOld = $conn->query("SELECT foto FROM kegiatan WHERE id = $id");
    $oldFoto = $resOld->fetch_assoc()['foto'] ?? '';
    $foto = $oldFoto;

    if (!empty($_FILES['edit_foto']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileName = $_FILES['edit_foto']['name'];
        $fileTmp  = $_FILES['edit_foto']['tmp_name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($fileExt, $allowed)) {
            $newName = 'kegiatan_' . time() . '_' . uniqid() . '.' . $fileExt;
            $uploadDir = 'uploads/kegiatan/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($fileTmp, $uploadDir . $newName)) {
                if ($oldFoto && file_exists($oldFoto)) unlink($oldFoto);
                $foto = $uploadDir . $newName;
            }
        }
    }

    if ($tanggal && $jam && $nama && $lokasi && $bulan) {
        $stmt = $conn->prepare("UPDATE kegiatan SET tanggal=?, jam=?, nama=?, lokasi=?, jenis_kerusakan=?, keterangan=?, foto=?, bulan=? WHERE id=?");
        $stmt->bind_param('ssssssssi', $tanggal, $jam, $nama, $lokasi, $jenis_kerusakan, $keterangan, $foto, $bulan, $id);
        if ($stmt->execute()) {
            $pesan = 'Data kegiatan berhasil diperbarui!';
            $statusMsg = 'success';
        } else {
            $pesan = 'Gagal memperbarui: ' . $stmt->error;
            $statusMsg = 'error';
        }
        $stmt->close();
    } else {
        $pesan = 'Tanggal, Jam, Nama, Lokasi, dan Bulan wajib diisi!';
        $statusMsg = 'error';
    }
    header('Location: kegiatan.php?msg=' . urlencode($pesan) . '&status=' . $statusMsg);
    exit;
}

if (isset($_GET['msg'])) {
    $pesan = $_GET['msg'];
    $statusMsg = $_GET['status'] ?? 'success';
}

// --- PROSES SIMPAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $tanggal         = $_POST['tanggal'] ?? '';
    $jam             = $_POST['jam'] ?? '';
    $nama            = trim($_POST['nama'] ?? '');
    $lokasi          = trim($_POST['lokasi'] ?? '');
    $jenis_kerusakan = trim($_POST['jenis_kerusakan'] ?? '');
    $keterangan      = trim($_POST['keterangan'] ?? '');
    $bulan           = trim($_POST['bulan'] ?? '');

    $foto = '';
    if (!empty($_FILES['foto']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileName = $_FILES['foto']['name'];
        $fileTmp  = $_FILES['foto']['tmp_name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($fileExt, $allowed)) {
            $newName = 'kegiatan_' . time() . '_' . uniqid() . '.' . $fileExt;
            $uploadDir = 'uploads/kegiatan/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            if (move_uploaded_file($fileTmp, $uploadDir . $newName)) {
                $foto = $uploadDir . $newName;
            }
        }
    }

    if ($tanggal && $jam && $nama && $lokasi && $bulan) {
        $stmt = $conn->prepare("INSERT INTO kegiatan (tanggal, jam, nama, lokasi, jenis_kerusakan, keterangan, foto, bulan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssss', $tanggal, $jam, $nama, $lokasi, $jenis_kerusakan, $keterangan, $foto, $bulan);
        if ($stmt->execute()) {
            $pesan = 'Data kegiatan berhasil disimpan!';
            $statusMsg = 'success';
            $_POST = [];
        } else {
            $pesan = 'Gagal menyimpan: ' . $stmt->error;
            $statusMsg = 'error';
        }
        $stmt->close();
    } else {
        $pesan = 'Tanggal, Jam, Nama, Lokasi, dan Bulan wajib diisi!';
        $statusMsg = 'error';
    }
}

// --- AMBIL DATA ---
$where = []; $params = []; $types = '';
if (!empty($_GET['cari_nama']))   { $where[] = "nama LIKE ?";   $params[] = '%'.$_GET['cari_nama'].'%';   $types .= 's'; }
if (!empty($_GET['cari_lokasi'])) { $where[] = "lokasi LIKE ?"; $params[] = '%'.$_GET['cari_lokasi'].'%'; $types .= 's'; }
if (!empty($_GET['cari_bulan']))  { $where[] = "bulan LIKE ?";  $params[] = '%'.$_GET['cari_bulan'].'%';  $types .= 's'; }

$sql = "SELECT * FROM kegiatan";
if ($where) { $sql .= " WHERE ".implode(" AND ", $where); }
$sql .= " ORDER BY tanggal DESC, jam DESC";

$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();

$totalKegiatan = $result->num_rows;
$bulanIniId = date('m');
$tahunIni = date('Y');
$resBulanIni = $conn->query("SELECT COUNT(*) as jml FROM kegiatan WHERE MONTH(tanggal) = $bulanIniId AND YEAR(tanggal) = $tahunIni");
$countBulanIni = $resBulanIni->fetch_assoc()['jml'] ?? 0;

$bulanIndo = [
    '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
    '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
    '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inventaris Digital - Data Kegiatan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1; --primary-dark: #4f46e5; --secondary: #ec4899;
            --accent: #06b6d4; --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
            --dark: #0f172a; --dark-card: #1e293b; --light: #f8fafc;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --radius-sm: 8px; --radius: 16px; --radius-lg: 24px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            background-attachment: fixed; min-height: 100vh; color: var(--light);
            overflow-x: hidden; -webkit-font-smoothing: antialiased;
        }
        .bg-mesh {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none; z-index: 0; overflow: hidden;
        }
        .mesh-blob {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.35;
            animation: blob-morph 20s ease-in-out infinite;
        }
        .mesh-blob:nth-child(1) { width: 500px; height: 500px; background: linear-gradient(135deg, #6366f1, #8b5cf6); left: -10%; top: -10%; }
        .mesh-blob:nth-child(2) { width: 400px; height: 400px; background: linear-gradient(135deg, #ec4899, #f43f5e); right: -5%; top: 20%; animation-delay: -5s; }
        .mesh-blob:nth-child(3) { width: 450px; height: 450px; background: linear-gradient(135deg, #06b6d4, #3b82f6); left: 30%; bottom: -10%; animation-delay: -10s; }
        @keyframes blob-morph {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        .glass-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.3); backdrop-filter: blur(2px); z-index: 0;
        }
        .header {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding: 0; transition: var(--transition);
        }
        .header.scrolled { background: rgba(15, 23, 42, 0.9); box-shadow: var(--shadow-lg); }
        .header-inner { max-width: 1400px; margin: 0 auto; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; }
        .header-left { display: flex; align-items: center; gap: 16px; }
        .menu-btn {
            width: 44px; height: 44px; border-radius: 12px; background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15); color: white; font-size: 18px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: var(--transition);
        }
        .menu-btn:hover { background: rgba(255, 255, 255, 0.2); transform: scale(1.05); }
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo-icon {
            width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4); animation: pulse-glow 3s ease-in-out infinite;
        }
        @keyframes pulse-glow { 0%, 100% { box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4); } 50% { box-shadow: 0 4px 25px rgba(99, 102, 241, 0.7); } }
        .logo-icon i { color: white; font-size: 18px; }
        .logo-text { font-size: 20px; font-weight: 700; color: white; letter-spacing: -0.5px; }
        .logo-text span { font-weight: 300; opacity: 0.8; }
        .header-right { display: flex; align-items: center; gap: 10px; }
        .header-btn {
            width: 44px; height: 44px; border-radius: 12px; background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15); color: white; font-size: 16px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: var(--transition); position: relative; overflow: hidden;
        }
        .header-btn::before { content: ''; position: absolute; top: 50%; left: 50%; width: 0; height: 0; background: rgba(255, 255, 255, 0.2); border-radius: 50%; transform: translate(-50%, -50%); transition: width 0.4s, height 0.4s; }
        .header-btn:hover::before { width: 100%; height: 100%; }
        .header-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .header-btn i { position: relative; z-index: 1; }
        .header-btn .badge-dot { position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; background: var(--danger); border-radius: 50%; border: 2px solid var(--dark); }
        .breadcrumb-bar { position: relative; z-index: 1; padding-top: 90px; padding-bottom: 10px; }
        .breadcrumb-inner { max-width: 1400px; margin: 0 auto; padding: 0 24px; }
        .breadcrumb { display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.5); }
        .breadcrumb a { color: rgba(255,255,255,0.7); text-decoration: none; transition: var(--transition); font-weight: 500; }
        .breadcrumb a:hover { color: white; }
        .breadcrumb i { font-size: 10px; color: rgba(255,255,255,0.3); }
        .breadcrumb-current { color: white; font-weight: 600; }
        .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px); z-index: 2000; opacity: 0; visibility: hidden; transition: var(--transition); }
        .sidebar-overlay.active { opacity: 1; visibility: visible; }
        .sidebar { position: fixed; top: 0; left: -340px; width: 340px; height: 100%; background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); z-index: 2001; transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1); overflow-y: auto; box-shadow: 10px 0 40px rgba(0,0,0,0.4); border-right: 1px solid rgba(255,255,255,0.05); }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 32px 24px; background: linear-gradient(135deg, var(--primary), var(--secondary)); position: relative; overflow: hidden; }
        .sidebar-header::before { content: ''; position: absolute; top: -50%; right: -50%; width: 100%; height: 100%; background: rgba(255,255,255,0.1); border-radius: 50%; }
        .sidebar-close { position: absolute; top: 16px; right: 16px; width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.2); border: none; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition); }
        .sidebar-close:hover { background: rgba(255,255,255,0.3); transform: rotate(90deg); }
        .sidebar-user { display: flex; align-items: center; gap: 14px; position: relative; z-index: 1; }
        .sidebar-avatar { width: 56px; height: 56px; border-radius: 16px; background: white; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .sidebar-user-info h3 { color: white; font-size: 16px; font-weight: 600; }
        .sidebar-user-info p { color: rgba(255,255,255,0.7); font-size: 12px; margin-top: 2px; }
        .sidebar-menu { padding: 24px 16px; }
        .sidebar-section { margin-bottom: 28px; }
        .sidebar-section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #64748b; padding: 0 12px; margin-bottom: 10px; }
        .sidebar-item { display: flex; align-items: center; gap: 14px; padding: 14px 12px; border-radius: 12px; color: #94a3b8; text-decoration: none; font-size: 14px; font-weight: 500; transition: var(--transition); margin-bottom: 4px; position: relative; overflow: hidden; }
        .sidebar-item::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 3px; background: linear-gradient(180deg, var(--primary), var(--secondary)); border-radius: 0 4px 4px 0; transform: scaleY(0); transition: var(--transition); }
        .sidebar-item:hover, .sidebar-item.active { background: rgba(99, 102, 241, 0.1); color: #e2e8f0; }
        .sidebar-item:hover::before, .sidebar-item.active::before { transform: scaleY(1); }
        .sidebar-item.active { background: rgba(99, 102, 241, 0.15); color: white; }
        .sidebar-item i { width: 24px; text-align: center; font-size: 16px; }
        .sidebar-item .menu-arrow { margin-left: auto; font-size: 12px; opacity: 0.5; }
        .main-content { position: relative; z-index: 1; padding-bottom: 60px; }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 24px; }
        .page-header { margin-bottom: 32px; animation: fadeInUp 0.8s ease-out; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .page-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.12); padding: 8px 16px; border-radius: 50px; color: rgba(255,255,255,0.8); font-size: 13px; font-weight: 500; margin-bottom: 16px; }
        .page-badge i { color: #fbbf24; }
        .page-title { font-size: clamp(28px, 5vw, 42px); font-weight: 800; color: white; line-height: 1.2; margin-bottom: 12px; text-shadow: 0 2px 20px rgba(0,0,0,0.2); }
        .page-title .gradient-text { background: linear-gradient(135deg, #fbbf24, #f59e0b, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .page-subtitle { font-size: clamp(14px, 2vw, 16px); color: rgba(255, 255, 255, 0.55); font-weight: 400; max-width: 600px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px; animation: fadeInUp 0.8s ease-out 0.1s both; }
        .stat-card { background: rgba(255, 255, 255, 0.04); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: var(--radius); padding: 24px; transition: var(--transition); position: relative; overflow: hidden; }
        .stat-card::after { content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%); }
        .stat-card:hover { transform: translateY(-4px); border-color: rgba(255, 255, 255, 0.15); box-shadow: var(--shadow-lg); }
        .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 16px; }
        .stat-icon.purple { background: rgba(99, 102, 241, 0.15); color: #a5b4fc; }
        .stat-icon.pink { background: rgba(236, 72, 153, 0.15); color: #f9a8d4; }
        .stat-icon.cyan { background: rgba(6, 182, 212, 0.15); color: #67e8f9; }
        .stat-icon.amber { background: rgba(245, 158, 11, 0.15); color: #fcd34d; }
        .stat-value { font-size: 28px; font-weight: 800; color: white; line-height: 1; margin-bottom: 6px; }
        .stat-label { font-size: 13px; color: rgba(255,255,255,0.5); font-weight: 500; }
        .stat-change { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; margin-top: 8px; padding: 4px 8px; border-radius: 20px; }
        .stat-change.up { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; }
        .glass-card { background: rgba(255, 255, 255, 0.04); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 32px; animation: fadeInUp 0.8s ease-out; box-shadow: var(--shadow-xl); transition: var(--transition); }
        .glass-card:hover { border-color: rgba(255, 255, 255, 0.12); }
        .glass-card-header { padding: 24px 30px; border-bottom: 1px solid rgba(255, 255, 255, 0.06); display: flex; align-items: center; gap: 14px; }
        .glass-card-header i { width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, var(--accent), var(--primary)); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3); }
        .glass-card-header h2 { color: white; font-size: 1.25rem; font-weight: 700; }
        .glass-card-header p { color: rgba(255,255,255,0.5); font-size: 0.85rem; margin-top: 2px; }
        .glass-card-body { padding: 28px 30px; }
        .alert-glass { padding: 14px 18px; border-radius: 12px; margin-bottom: 22px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); animation: slideIn 0.4s ease-out; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        .alert-glass.success { background: rgba(16, 185, 129, 0.1); color: #6ee7b7; border-color: rgba(16, 185, 129, 0.2); }
        .alert-glass.error { background: rgba(239, 68, 68, 0.1); color: #fca5a5; border-color: rgba(239, 68, 68, 0.2); }
        .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
        .form-group { margin-bottom: 18px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { display: block; margin-bottom: 6px; color: rgba(255,255,255,0.7); font-weight: 500; font-size: 0.85rem; }
        .form-group label .required { color: #f87171; margin-left: 3px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 14px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; font-size: 0.9rem; color: white; transition: var(--transition); font-family: 'Poppins', sans-serif; }
        .form-group input::placeholder, .form-group textarea::placeholder { color: rgba(255,255,255,0.3); }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: rgba(99, 102, 241, 0.5); background: rgba(255, 255, 255, 0.08); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .form-group textarea { resize: vertical; min-height: 90px; }
        .file-hint { font-size: 0.78rem; color: rgba(255,255,255,0.35); margin-top: 5px; }
        .btn-glass-submit { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; padding: 13px 28px; border: none; border-radius: 12px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: var(--transition); box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3); position: relative; overflow: hidden; }
        .btn-glass-submit::after { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transition: 0.5s; }
        .btn-glass-submit:hover::after { left: 100%; }
        .btn-glass-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4); }
        .btn-glass-submit:active { transform: scale(0.98); }
        .filter-glass { padding: 20px 30px; background: rgba(255, 255, 255, 0.02); border-bottom: 1px solid rgba(255, 255, 255, 0.06); }
        .filter-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: end; }
        .filter-form input { padding: 11px 14px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; font-size: 0.88rem; color: white; width: 100%; transition: var(--transition); }
        .filter-form input::placeholder { color: rgba(255,255,255,0.3); }
        .filter-form input:focus { outline: none; border-color: rgba(99, 102, 241, 0.4); background: rgba(255, 255, 255, 0.08); }
        .btn-filter, .btn-reset-glass { padding: 11px 20px; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 0.85rem; transition: var(--transition); display: inline-flex; align-items: center; gap: 6px; }
        .btn-filter { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2); }
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3); }
        .btn-reset-glass { background: rgba(255, 255, 255, 0.06); color: rgba(255,255,255,0.7); text-decoration: none; display: inline-block; text-align: center; border: 1px solid rgba(255, 255, 255, 0.1); }
        .btn-reset-glass:hover { background: rgba(255, 255, 255, 0.1); color: white; }
        .table-wrap {
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            position: relative;
        }
        .table-wrap::-webkit-scrollbar { height: 8px; }
        .table-wrap::-webkit-scrollbar-track { background: rgba(255,255,255,0.04); border-radius: 4px; }
        .table-wrap::-webkit-scrollbar-thumb { background: linear-gradient(90deg, var(--primary), var(--secondary)); border-radius: 4px; }
        html.light .table-wrap::-webkit-scrollbar-track { background: rgba(15,23,42,0.05); }
        .scroll-hint {
            display: none; align-items: center; gap: 8px;
            padding: 10px 30px; font-size: 0.78rem; color: rgba(255,255,255,0.45);
            background: rgba(99, 102, 241, 0.08); border-bottom: 1px solid rgba(255,255,255,0.05);
            animation: slideIn 0.4s ease-out;
        }
        html.light .scroll-hint { color: rgba(15,23,42,0.5); background: rgba(99,102,241,0.06); border-bottom: 1px solid rgba(15,23,42,0.05); }
        .scroll-hint i { animation: slide-hint 1.2s ease-in-out infinite; color: var(--accent); }
        @keyframes slide-hint { 0%,100% { transform: translateX(0); } 50% { transform: translateX(6px); } }
        .table-scroll-fade {
            position: relative;
        }
        .table-scroll-fade::after {
            content: ''; position: absolute; top: 0; right: 0; width: 40px; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(15,23,42,0.35));
            pointer-events: none; transition: opacity 0.3s; border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
        }
        html.light .table-scroll-fade::after { background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9)); }
        .table-scroll-fade.scrolled-end::after { opacity: 0; }
        .data-table { width: 100%; min-width: 1050px; border-collapse: collapse; }
        .data-table th:nth-child(1), .data-table td:nth-child(1) { min-width: 50px; }
        .data-table th:nth-child(2), .data-table td:nth-child(2) { min-width: 150px; }
        .data-table th:nth-child(3), .data-table td:nth-child(3) { min-width: 140px; }
        .data-table th:nth-child(4), .data-table td:nth-child(4) { min-width: 140px; }
        .data-table th:nth-child(5), .data-table td:nth-child(5) { min-width: 170px; }
        .data-table th:nth-child(6), .data-table td:nth-child(6) { min-width: 200px; }
        .data-table th:nth-child(7), .data-table td:nth-child(7) { min-width: 80px; }
        .data-table th:nth-child(8), .data-table td:nth-child(8) { min-width: 110px; }
        .data-table th:nth-child(9), .data-table td:nth-child(9) { min-width: 100px; }
        .data-table thead { background: rgba(15, 23, 42, 0.5); }
        .data-table th { padding: 16px 14px; text-align: left; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.8px; white-space: nowrap; border-bottom: 1px solid rgba(255,255,255,0.06); color: #94a3b8; }
        .data-table td { padding: 14px 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.04); font-size: 0.87rem; color: rgba(255,255,255,0.8); vertical-align: top; }
        .data-table tbody tr { transition: var(--transition); position: relative; }
        .data-table tbody tr:hover { background: rgba(99, 102, 241, 0.05); }
        .data-table tbody tr:nth-child(even) { background: rgba(255, 255, 255, 0.015); }
        .data-table tbody tr:nth-child(even):hover { background: rgba(99, 102, 241, 0.05); }
        .badge-glass { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; backdrop-filter: blur(10px); }
        .badge-tanggal { background: rgba(16, 185, 129, 0.12); color: #6ee7b7; }
        .badge-jam { background: rgba(6, 182, 212, 0.12); color: #67e8f9; margin-top: 4px; }
        .badge-bulan { background: rgba(251, 191, 36, 0.12); color: #fcd34d; }
        .foto-thumb { width: 55px; height: 55px; object-fit: cover; border-radius: 10px; border: 2px solid rgba(251, 191, 36, 0.3); cursor: pointer; transition: var(--transition); }
        .foto-thumb:hover { transform: scale(1.1); border-color: #fbbf24; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2); }
        .no-foto { width: 55px; height: 55px; background: rgba(255,255,255,0.04); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.2); font-size: 1.1rem; }
        .text-wrap { max-width: 220px; word-wrap: break-word; line-height: 1.5; }
        .text-muted-glass { color: rgba(255,255,255,0.3); font-style: italic; }
        .aksi { display: flex; gap: 6px; }
        .aksi a, .aksi button { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 0.85rem; transition: var(--transition); backdrop-filter: blur(10px); border: none; cursor: pointer; }
        .btn-edit-glass { background: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.15); }
        .btn-edit-glass:hover { background: #f59e0b; color: var(--dark); transform: translateY(-2px); }
        .btn-hapus-glass { background: rgba(239, 68, 68, 0.12); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.15); }
        .btn-hapus-glass:hover { background: #ef4444; color: white; transform: translateY(-2px); }
        .empty-glass { text-align: center; padding: 50px; color: rgba(255,255,255,0.3); }
        .empty-glass i { font-size: 3rem; margin-bottom: 14px; display: block; color: rgba(255,255,255,0.1); }
        .footer-bar-glass { padding: 16px 28px; font-size: 0.82rem; display: flex; justify-content: space-between; align-items: center; background: rgba(15, 23, 42, 0.3); border-top: 1px solid rgba(255, 255, 255, 0.04); color: rgba(255,255,255,0.4); }
        .footer-bar-glass span { color: #a5b4fc; font-weight: 600; }
        .modal-overlay { display: none; position: fixed; z-index: 3000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-content { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius-lg); width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); transform: scale(0.9) translateY(20px); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .modal-overlay.active .modal-content { transform: scale(1) translateY(0); }
        .modal-header { padding: 24px 28px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between; }
        .modal-header h3 { color: white; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .modal-header h3 i { color: var(--warning); }
        .modal-close { width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.6); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition); font-size: 16px; }
        .modal-close:hover { background: rgba(239, 68, 68, 0.2); color: #fca5a5; transform: rotate(90deg); }
        .modal-body { padding: 24px 28px; }
        .modal-footer { padding: 20px 28px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: flex-end; gap: 10px; }
        .btn-modal-save { padding: 11px 24px; border: none; border-radius: 12px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; font-weight: 600; cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; gap: 8px; }
        .btn-modal-save:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3); }
        .btn-modal-cancel { padding: 11px 24px; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; background: transparent; color: rgba(255,255,255,0.6); font-weight: 600; cursor: pointer; transition: var(--transition); }
        .btn-modal-cancel:hover { background: rgba(255,255,255,0.05); color: white; }
        .current-foto { width: 100px; height: 100px; object-fit: cover; border-radius: 12px; border: 2px solid rgba(255,255,255,0.1); margin-bottom: 10px; }
        .modal-foto { display: none; position: fixed; z-index: 3000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); backdrop-filter: blur(10px); align-items: center; justify-content: center; }
        .modal-foto img { max-width: 90%; max-height: 90%; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        .modal-foto-close { position: absolute; top: 20px; right: 30px; color: #fff; font-size: 2.5rem; cursor: pointer; transition: var(--transition); }
        .modal-foto-close:hover { color: #f87171; transform: scale(1.1); }
        .footer { position: relative; z-index: 1; text-align: center; padding: 30px 24px; color: rgba(255, 255, 255, 0.3); font-size: 13px; font-weight: 500; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.25); }
        .loading-screen { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: var(--dark); z-index: 9999; display: flex; align-items: center; justify-content: center; flex-direction: column; transition: opacity 0.5s, visibility 0.5s; }
        .loading-screen.hidden { opacity: 0; visibility: hidden; }
        .loader { width: 50px; height: 50px; border: 3px solid rgba(255,255,255,0.1); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { color: white; margin-top: 20px; font-size: 14px; font-weight: 500; letter-spacing: 2px; animation: pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { opacity: 0.5; } 50% { opacity: 1; } }

        /* ========== LIGHT THEME ========== */
        html.light body {
            --dark: #f1f5f9;
            --dark-card: #ffffff;
            --light: #0f172a;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 50%, #e0e7ff 100%);
            color: #0f172a;
        }

        html.light .glass-overlay { background: rgba(255, 255, 255, 0.25); }

        html.light .mesh-blob { opacity: 0.2; }

        html.light .header {
            background: rgba(255, 255, 255, 0.7);
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }
        html.light .header.scrolled { background: rgba(255, 255, 255, 0.92); box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.08); }

        html.light .menu-btn,
        html.light .header-btn {
            background: rgba(15, 23, 42, 0.05);
            border: 1px solid rgba(15, 23, 42, 0.1);
            color: var(--light);
        }
        html.light .menu-btn:hover,
        html.light .header-btn:hover { background: rgba(15, 23, 42, 0.1); }
        html.light .header-btn::before { background: rgba(15, 23, 42, 0.06); }
        html.light .header-btn .badge-dot { border-color: #ffffff; }

        html.light .logo-text { color: var(--light); }
        html.light .logo-text span { opacity: 0.55; }

        html.light .breadcrumb { color: rgba(15,23,42,0.5); }
        html.light .breadcrumb a { color: rgba(15,23,42,0.65); }
        html.light .breadcrumb a:hover { color: var(--light); }
        html.light .breadcrumb i { color: rgba(15,23,42,0.25); }
        html.light .breadcrumb-current { color: var(--light); }

        html.light .sidebar {
            background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
            border-right: 1px solid rgba(15,23,42,0.08);
            box-shadow: 10px 0 40px rgba(15,23,42,0.12);
        }
        html.light .sidebar-section-title { color: #94a3b8; }
        html.light .sidebar-item { color: #64748b; }
        html.light .sidebar-item:hover, html.light .sidebar-item.active { background: rgba(99, 102, 241, 0.08); color: var(--primary); }

        html.light .page-badge {
            background: rgba(15, 23, 42, 0.05);
            border: 1px solid rgba(15, 23, 42, 0.08);
            color: rgba(15,23,42,0.7);
        }
        html.light .page-title { color: var(--light); text-shadow: none; }
        html.light .page-title .gradient-text {
            background: linear-gradient(135deg, #d97706, #f59e0b, #db2777);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        html.light .page-subtitle { color: rgba(15,23,42,0.55); }

        html.light .stat-card,
        html.light .glass-card {
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
        }
        html.light .stat-card:hover,
        html.light .glass-card:hover { border-color: rgba(15, 23, 42, 0.15); box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.12); }
        html.light .stat-card::after { background: radial-gradient(circle, rgba(15,23,42,0.03) 0%, transparent 70%); }
        html.light .stat-value { color: var(--light); }
        html.light .stat-label { color: rgba(15,23,42,0.5); }
        html.light .glass-card-header { border-bottom: 1px solid rgba(15, 23, 42, 0.06); }
        html.light .glass-card-header h2 { color: var(--light); }
        html.light .glass-card-header p { color: rgba(15,23,42,0.45); }

        html.light .form-group label { color: rgba(15,23,42,0.7); }
        html.light .form-group input,
        html.light .form-group select,
        html.light .form-group textarea,
        html.light .filter-form input {
            background: rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(15, 23, 42, 0.12);
            color: var(--light);
        }
        html.light .form-group input::placeholder,
        html.light .form-group textarea::placeholder,
        html.light .filter-form input::placeholder { color: rgba(15,23,42,0.35); }
        html.light .form-group input:focus,
        html.light .form-group select:focus,
        html.light .form-group textarea:focus,
        html.light .filter-form input:focus {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08);
        }
        html.light .file-hint { color: rgba(15,23,42,0.4); }

        html.light .filter-glass { background: rgba(15, 23, 42, 0.02); border-bottom: 1px solid rgba(15, 23, 42, 0.06); }
        html.light .btn-reset-glass {
            background: rgba(15, 23, 42, 0.05);
            color: rgba(15,23,42,0.6);
            border: 1px solid rgba(15, 23, 42, 0.1);
        }
        html.light .btn-reset-glass:hover { background: rgba(15, 23, 42, 0.1); color: var(--light); }

        html.light .data-table thead { background: rgba(15, 23, 42, 0.04); }
        html.light .data-table th { color: #64748b; border-bottom: 1px solid rgba(15,23,42,0.08); }
        html.light .data-table td { color: rgba(15,23,42,0.75); border-bottom: 1px solid rgba(15, 23, 42, 0.05); }
        html.light .data-table tbody tr:hover { background: rgba(99, 102, 241, 0.05); }
        html.light .data-table tbody tr:nth-child(even) { background: rgba(15, 23, 42, 0.015); }
        html.light .data-table tbody tr:nth-child(even):hover { background: rgba(99, 102, 241, 0.05); }
        html.light .data-table td strong { color: var(--light) !important; }
        html.light .text-muted-glass { color: rgba(15,23,42,0.35); }

        html.light .no-foto { background: rgba(15,23,42,0.05); color: rgba(15,23,42,0.2); }

        html.light .btn-edit-glass { background: rgba(245, 158, 11, 0.1); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.2); }
        html.light .btn-edit-glass:hover { background: #f59e0b; color: #ffffff; }
        html.light .btn-hapus-glass { background: rgba(239, 68, 68, 0.1); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.2); }
        html.light .btn-hapus-glass:hover { background: #ef4444; color: #ffffff; }

        html.light .empty-glass { color: rgba(15,23,42,0.35); }
        html.light .empty-glass i { color: rgba(15,23,42,0.12); }

        html.light .footer-bar-glass {
            background: rgba(15, 23, 42, 0.03);
            border-top: 1px solid rgba(15, 23, 42, 0.05);
            color: rgba(15,23,42,0.45);
        }

        html.light .modal-content {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(15,23,42,0.1);
        }
        html.light .modal-header { border-bottom: 1px solid rgba(15,23,42,0.06); }
        html.light .modal-header h3 { color: var(--light); }
        html.light .modal-close {
            background: rgba(15,23,42,0.05);
            border: 1px solid rgba(15,23,42,0.1);
            color: rgba(15,23,42,0.5);
        }
        html.light .modal-footer { border-top: 1px solid rgba(15,23,42,0.06); }
        html.light .btn-modal-cancel {
            border: 1px solid rgba(15,23,42,0.15);
            color: rgba(15,23,42,0.55);
        }
        html.light .btn-modal-cancel:hover { background: rgba(15,23,42,0.05); color: var(--light); }
        html.light .current-foto { border: 2px solid rgba(15,23,42,0.1); }

        html.light .footer { color: rgba(15, 23, 42, 0.35); }

        html.light .loading-screen { background: #f1f5f9; }
        html.light .loader { border-color: rgba(15,23,42,0.1); border-top-color: var(--primary); }
        html.light .loading-text { color: var(--light); }

        html.light ::-webkit-scrollbar-thumb { background: rgba(15,23,42,0.15); }
        html.light ::-webkit-scrollbar-thumb:hover { background: rgba(15,23,42,0.3); }

        /* Transisi tema */
        body, .header, .sidebar, .stat-card, .glass-card, .menu-btn, .header-btn,
        .sidebar-item, .form-group input, .form-group textarea, .filter-form input,
        .data-table th, .data-table td, .modal-content, .page-title, .stat-value,
        .glass-card-header h2, .loading-screen {
            transition: background-color 0.4s ease, color 0.4s ease, border-color 0.4s ease, background 0.4s ease, box-shadow 0.4s ease;
        }

        @media (max-width: 991px) {
            .form-row { grid-template-columns: 1fr; }
            .glass-card-body, .glass-card-header, .filter-glass { padding: 20px; }
            .data-table th, .data-table td { padding: 12px 10px; font-size: 0.8rem; }
            .text-wrap { max-width: 160px; }
        }
        @media (max-width: 767px) {
            .container { padding: 0 16px; }
            .header-inner { padding: 10px 16px; }
            .logo-text { font-size: 16px; }
            .menu-btn, .header-btn { width: 40px; height: 40px; }
            .breadcrumb-bar { padding-top: 80px; }
            .breadcrumb-inner { padding: 0 16px; }
            .page-title { font-size: 26px; }
            .sidebar { width: 300px; left: -300px; }
            .filter-form { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .stat-value { font-size: 22px; }
            .modal-content { width: 95%; }
        }
        @media (max-width: 575px) {
            .page-title { font-size: 22px; }
            .glass-card-header i { width: 40px; height: 40px; font-size: 16px; }
            .glass-card-header h2 { font-size: 1.05rem; }
            .stats-grid { grid-template-columns: 1fr; }
            /* Tabel TETAP berjejer ke samping, digeser horizontal (swipe) */
            .table-wrap { margin: 0 -4px; padding: 0 4px; }
            .data-table { min-width: 950px; }
            .data-table th, .data-table td { padding: 10px 8px; font-size: 0.78rem; }
            .text-wrap { max-width: 160px; }
            .aksi { justify-content: flex-start; }
            .footer-bar-glass { flex-direction: column; gap: 6px; text-align: center; }
        }
    </style>
    <script>
        // Terapkan tema tersimpan sebelum render (anti-flash)
        (function() {
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.add('light');
            }
        })();
    </script>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loader"></div>
        <div class="loading-text">MEMUAT DATA KEGIATAN...</div>
    </div>

    <!-- Background Effects -->
    <div class="bg-mesh">
        <div class="mesh-blob"></div>
        <div class="mesh-blob"></div>
        <div class="mesh-blob"></div>
    </div>
    <div class="glass-overlay"></div>

    <!-- Sidebar Drawer -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="sidebar-close" onclick="toggleSidebar()"><i class="fas fa-times"></i></button>
            <div class="sidebar-user">
                <div class="sidebar-avatar">👤</div>
                <div class="sidebar-user-info">
                    <h3>Administrator</h3>
                    <p>admin@inventaris.id</p>
                </div>
            </div>
        </div>
        <nav class="sidebar-menu">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Menu Utama</div>
                <a href="index.php" class="sidebar-item" onclick="toggleSidebar()">
                    <i class="fas fa-house"></i> Beranda
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <a href="inventaris.php" class="sidebar-item" onclick="toggleSidebar()">
                    <i class="fas fa-boxes-stacked"></i> Data Inventaris
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <a href="kegiatan.php" class="sidebar-item active" onclick="toggleSidebar()">
                    <i class="fas fa-clipboard-check"></i> Data Kegiatan
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <a href="jaringan.php" class="sidebar-item" onclick="toggleSidebar()">
                    <i class="fas fa-server"></i> Data Jaringan
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
                <a href="laporan.php" class="sidebar-item" onclick="toggleSidebar()">
                    <i class="fas fa-chart-pie"></i> Data Laporan
                    <i class="fas fa-chevron-right menu-arrow"></i>
                </a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title">Pengaturan</div>
                <a href="#" class="sidebar-item" onclick="toggleSidebar()">
                    <i class="fas fa-user-shield"></i> Profil
                </a>
                <a href="#" class="sidebar-item" onclick="toggleSidebar()">
                    <i class="fas fa-sliders"></i> Pengaturan
                </a>
                <a href="#" class="sidebar-item" onclick="toggleSidebar()">
                    <i class="fas fa-power-off"></i> Keluar
                </a>
            </div>
        </nav>
    </aside>

    <!-- Header -->
    <header class="header" id="header">
        <div class="header-inner">
            <div class="header-left">
                <button class="menu-btn" onclick="toggleSidebar()" aria-label="Menu"><i class="fas fa-bars-staggered"></i></button>
                <div class="logo">
                    <div class="logo-icon"><i class="fas fa-cubes"></i></div>
                    <span class="logo-text">Inventaris<span>Digital</span></span>
                </div>
            </div>
            <div class="header-right">
                <button class="header-btn" onclick="alert('Fitur pencarian akan segera hadir!')"><i class="fas fa-magnifying-glass"></i></button>
                <button class="header-btn" onclick="location.reload()"><i class="fas fa-rotate"></i></button>
                <button class="header-btn" id="themeToggle" title="Ganti Tema" onclick="toggleTheme()"><i class="fas fa-moon" id="themeIcon"></i></button>
                <button class="header-btn" onclick="alert('Tidak ada notifikasi baru')"><i class="fas fa-bell"></i><span class="badge-dot"></span></button>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <div class="breadcrumb-inner">
            <div class="breadcrumb">
                <a href="index.php"><i class="fas fa-house"></i> Beranda</a>
                <i class="fas fa-chevron-right"></i>
                <span class="breadcrumb-current">Data Kegiatan</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <section class="page-header">
                <div class="page-badge">
                    <i class="fas fa-clipboard-check"></i>
                    Modul Kegiatan
                </div>
                <h1 class="page-title">
                    Input & <span class="gradient-text">Data Kegiatan</span>
                </h1>
                <p class="page-subtitle">
                    Catat kegiatan perawatan dan laporan kerusakan EDP secara real-time dengan sistem inline editing.
                </p>
            </section>

            <!-- Statistik -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-clipboard-list"></i></div>
                    <div class="stat-value" id="statTotal"><?= $totalKegiatan ?></div>
                    <div class="stat-label">Total Kegiatan</div>
                    <div class="stat-change up"><i class="fas fa-arrow-trend-up"></i> Seluruh Data</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pink"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-value" id="statBulan"><?= $countBulanIni ?></div>
                    <div class="stat-label">Kegiatan Bulan Ini</div>
                    <div class="stat-change up"><i class="fas fa-check"></i> <?= date('F Y') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon cyan"><i class="fas fa-clock"></i></div>
                    <div class="stat-value">Real-time</div>
                    <div class="stat-label">Update Terakhir</div>
                    <div class="stat-change up"><i class="fas fa-bolt"></i> Live</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="fas fa-image"></i></div>
                    <div class="stat-value">100%</div>
                    <div class="stat-label">Dokumentasi</div>
                    <div class="stat-change up"><i class="fas fa-camera"></i> Lengkap</div>
                </div>
            </div>

            <!-- ===== FORM INPUT ===== -->
            <div class="glass-card" style="animation-delay: 0.1s;">
                <div class="glass-card-header">
                    <i class="fas fa-plus-circle"></i>
                    <div>
                        <h2>Input Kegiatan Baru</h2>
                        <p>Isi formulir di bawah untuk mencatat kegiatan</p>
                    </div>
                </div>
                <div class="glass-card-body">
                    <?php if ($pesan && !isset($_GET['msg'])): ?>
                        <div class="alert-glass <?= $statusMsg ?>">
                            <i class="fas fa-<?= $statusMsg === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                            <?= htmlspecialchars($pesan) ?>
                        </div>
                    <?php elseif (isset($_GET['msg'])): ?>
                        <div class="alert-glass <?= $statusMsg ?>">
                            <i class="fas fa-<?= $statusMsg === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                            <?= htmlspecialchars($_GET['msg']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" id="formInput">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tanggal <span class="required">*</span></label>
                                <input type="date" name="tanggal" value="<?= $_POST['tanggal'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Jam <span class="required">*</span></label>
                                <input type="time" name="jam" value="<?= $_POST['jam'] ?? date('H:i') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Pelapor / Teknisi <span class="required">*</span></label>
                                <input type="text" name="nama" placeholder="Contoh: Ahmad Fauzi" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Lokasi <span class="required">*</span></label>
                                <input type="text" name="lokasi" placeholder="Contoh: Ruang CICU, Lantai 2" value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>" required>
                            </div>
                            <div class="form-group full">
                                <label>Jenis Kerusakan</label>
                                <input type="text" name="jenis_kerusakan" placeholder="Contoh: Monitor tidak menyala, Printer error" value="<?= htmlspecialchars($_POST['jenis_kerusakan'] ?? '') ?>">
                            </div>
                            <div class="form-group full">
                                <label>Keterangan</label>
                                <textarea name="keterangan" placeholder="Jelaskan detail kegiatan atau kondisi kerusakan..."><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Foto Bukti</label>
                                <input type="file" name="foto" accept="image/*">
                                <div class="file-hint">Format: JPG, PNG, GIF (Maks 2MB)</div>
                            </div>
                            <div class="form-group">
                                <label>Bulan <span class="required">*</span></label>
                                <input type="text" name="bulan" placeholder="Contoh: Agustus 2026" value="<?= htmlspecialchars($_POST['bulan'] ?? '') ?>" required>
                            </div>
                        </div>
                        <button type="submit" name="simpan" class="btn-glass-submit" style="margin-top:8px;">
                            <i class="fas fa-paper-plane"></i> Simpan Data
                        </button>
                    </form>
                </div>
            </div>

            <!-- ===== TABEL DATA ===== -->
            <div class="glass-card" style="animation-delay: 0.2s;">
                <div class="glass-card-header">
                    <i class="fas fa-table" style="background: linear-gradient(135deg, #f59e0b, #ef4444); box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);"></i>
                    <div>
                        <h2>Riwayat Kegiatan</h2>
                        <p>Daftar seluruh kegiatan yang telah tercatat</p>
                    </div>
                </div>

                <div class="filter-glass">
                    <form method="GET" class="filter-form" id="filterForm">
                        <div><input type="text" name="cari_nama" placeholder="Cari nama..." value="<?= htmlspecialchars($_GET['cari_nama'] ?? '') ?>"></div>
                        <div><input type="text" name="cari_lokasi" placeholder="Cari lokasi..." value="<?= htmlspecialchars($_GET['cari_lokasi'] ?? '') ?>"></div>
                        <div><input type="text" name="cari_bulan" placeholder="Cari bulan..." value="<?= htmlspecialchars($_GET['cari_bulan'] ?? '') ?>"></div>
                        <div style="display:flex; gap:8px;">
                            <button type="submit" class="btn-filter"><i class="fas fa-magnifying-glass"></i> Cari</button>
                            <a href="kegiatan.php" class="btn-reset-glass"><i class="fas fa-rotate-left"></i> Reset</a>
                        </div>
                    </form>
                </div>

                <div class="scroll-hint" id="scrollHint"><i class="fas fa-arrows-left-right"></i> Geser ke samping untuk melihat semua kolom</div>
                <div class="table-scroll-fade" id="tableFade">
                <div class="table-wrap" id="tableWrap">
                    <table class="data-table" id="dataTable">
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
                                <tr><td colspan="9">
                                    <div class="empty-glass">
                                        <i class="fas fa-inbox"></i>
                                        <p>Belum ada data kegiatan.</p>
                                    </div>
                                </td></tr>
                            <?php else: ?>
                                <?php $no = 1; while ($row = $result->fetch_assoc()):
                                    $t = date('d', strtotime($row['tanggal'])) . ' ' .
                                         $bulanIndo[date('m', strtotime($row['tanggal']))] . ' ' .
                                         date('Y', strtotime($row['tanggal']));
                                    $fotoExists = $row['foto'] && file_exists($row['foto']);
                                ?>
                                <tr data-id="<?= $row['id'] ?>"
                                    data-tanggal="<?= $row['tanggal'] ?>"
                                    data-jam="<?= $row['jam'] ?>"
                                    data-nama="<?= htmlspecialchars($row['nama'], ENT_QUOTES) ?>"
                                    data-lokasi="<?= htmlspecialchars($row['lokasi'], ENT_QUOTES) ?>"
                                    data-jenis="<?= htmlspecialchars($row['jenis_kerusakan'] ?? '', ENT_QUOTES) ?>"
                                    data-keterangan="<?= htmlspecialchars($row['keterangan'] ?? '', ENT_QUOTES) ?>"
                                    data-bulan="<?= htmlspecialchars($row['bulan'], ENT_QUOTES) ?>"
                                    data-foto="<?= $row['foto'] ? htmlspecialchars($row['foto'], ENT_QUOTES) : '' ?>">
                                    <td data-label="No"><?= $no++ ?></td>
                                    <td data-label="Tanggal & Jam">
                                        <span class="badge-glass badge-tanggal"><i class="far fa-calendar"></i> <?= $t ?></span><br>
                                        <span class="badge-glass badge-jam"><i class="far fa-clock"></i> <?= substr($row['jam'], 0, 5) ?></span>
                                    </td>
                                    <td data-label="Nama"><strong style="color:#fff;"><?= htmlspecialchars($row['nama']) ?></strong></td>
                                    <td data-label="Lokasi"><?= htmlspecialchars($row['lokasi']) ?></td>
                                    <td data-label="Kerusakan"><?= $row['jenis_kerusakan'] ? '<span class="text-wrap">'.htmlspecialchars($row['jenis_kerusakan']).'</span>' : '<span class="text-muted-glass">-</span>' ?></td>
                                    <td data-label="Keterangan"><?= $row['keterangan'] ? '<span class="text-wrap">'.nl2br(htmlspecialchars($row['keterangan'])).'</span>' : '<span class="text-muted-glass">-</span>' ?></td>
                                    <td data-label="Foto">
                                        <?php if ($fotoExists): ?>
                                            <img src="<?= $row['foto'] ?>" class="foto-thumb" onclick="openFotoModal('<?= $row['foto'] ?>')">
                                        <?php else: ?>
                                            <div class="no-foto"><i class="far fa-image"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Bulan"><span class="badge-glass badge-bulan"><?= htmlspecialchars($row['bulan']) ?></span></td>
                                    <td data-label="Aksi">
                                        <div class="aksi">
                                            <button type="button" class="btn-edit-glass" onclick="openEditModal(this)" title="Edit"><i class="fas fa-pen"></i></button>
                                            <a href="?hapus=<?= $row['id'] ?>" class="btn-hapus-glass" title="Hapus" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                </div>
                <div class="footer-bar-glass">
                    <span>db_inventaris_digital</span>
                    <div>Total Data: <span><?= $result->num_rows ?></span> kegiatan</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Inventaris Digital. Dibangun dengan <i class="fas fa-heart" style="color:#ec4899"></i> untuk Indonesia.</p>
    </footer>

    <!-- ===== MODAL EDIT ===== -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-pen-to-square"></i> Edit Data Kegiatan</h3>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" id="formEdit">
                <input type="hidden" name="update_id" id="edit_id">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal <span class="required">*</span></label>
                            <input type="date" name="edit_tanggal" id="edit_tanggal" required>
                        </div>
                        <div class="form-group">
                            <label>Jam <span class="required">*</span></label>
                            <input type="time" name="edit_jam" id="edit_jam" required>
                        </div>
                        <div class="form-group">
                            <label>Nama <span class="required">*</span></label>
                            <input type="text" name="edit_nama" id="edit_nama" required>
                        </div>
                        <div class="form-group">
                            <label>Lokasi <span class="required">*</span></label>
                            <input type="text" name="edit_lokasi" id="edit_lokasi" required>
                        </div>
                        <div class="form-group full">
                            <label>Jenis Kerusakan</label>
                            <input type="text" name="edit_jenis_kerusakan" id="edit_jenis_kerusakan">
                        </div>
                        <div class="form-group full">
                            <label>Keterangan</label>
                            <textarea name="edit_keterangan" id="edit_keterangan" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Foto Saat Ini</label>
                            <div id="edit_foto_preview"></div>
                            <input type="file" name="edit_foto" id="edit_foto" accept="image/*">
                            <div class="file-hint">Kosongkan jika tidak ingin mengubah foto</div>
                        </div>
                        <div class="form-group">
                            <label>Bulan <span class="required">*</span></label>
                            <input type="text" name="edit_bulan" id="edit_bulan" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn-modal-save"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Preview Foto -->
    <div id="fotoModal" class="modal-foto" onclick="closeFotoModal()">
        <span class="modal-foto-close">&times;</span>
        <img id="modalImg" src="">
    </div>

    <script>
        // ========== THEME TOGGLE ==========
        function toggleTheme() {
            const isLight = document.documentElement.classList.toggle('light');
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            const icon = document.getElementById('themeIcon');
            if (icon) icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
        }

        // Set ikon sesuai tema tersimpan saat load
        (function() {
            const icon = document.getElementById('themeIcon');
            if (icon && document.documentElement.classList.contains('light')) {
                icon.className = 'fas fa-sun';
            }
        })();

        // Loading Screen
        window.addEventListener('load', function() {
            setTimeout(() => { document.getElementById('loadingScreen').classList.add('hidden'); }, 800);
        });

        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }

        // Header Scroll Effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            header.classList.toggle('scrolled', window.scrollY > 20);
        });

        // Modal Foto
        function openFotoModal(src) {
            document.getElementById('modalImg').src = src;
            document.getElementById('fotoModal').style.display = 'flex';
        }
        function closeFotoModal() {
            document.getElementById('fotoModal').style.display = 'none';
        }

        // ===== INLINE EDIT MODAL =====
        function openEditModal(btn) {
            const row = btn.closest('tr');
            const data = row.dataset;

            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_tanggal').value = data.tanggal;
            document.getElementById('edit_jam').value = data.jam;
            document.getElementById('edit_nama').value = data.nama;
            document.getElementById('edit_lokasi').value = data.lokasi;
            document.getElementById('edit_jenis_kerusakan').value = data.jenis || '';
            document.getElementById('edit_keterangan').value = data.keterangan || '';
            document.getElementById('edit_bulan').value = data.bulan;

            // Preview foto
            const previewDiv = document.getElementById('edit_foto_preview');
            if (data.foto) {
                previewDiv.innerHTML = '<img src="' + data.foto + '" class="current-foto" onclick="openFotoModal(\'' + data.foto + '\')">';
            } else {
                previewDiv.innerHTML = '<div class="no-foto" style="width:100px;height:100px;"><i class="far fa-image"></i></div>';
            }

            const modal = document.getElementById('editModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
                closeFotoModal();
            }
        });

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        // ===== Scroll horizontal tabel: hint + fade =====
        (function() {
            const wrap = document.getElementById('tableWrap');
            const hint = document.getElementById('scrollHint');
            const fade = document.getElementById('tableFade');
            if (!wrap) return;
            function updateScrollUI() {
                const canScroll = wrap.scrollWidth > wrap.clientWidth + 5;
                if (hint) hint.style.display = canScroll ? 'flex' : 'none';
                const atEnd = wrap.scrollLeft + wrap.clientWidth >= wrap.scrollWidth - 5;
                if (fade) fade.classList.toggle('scrolled-end', atEnd || !canScroll);
            }
            wrap.addEventListener('scroll', updateScrollUI);
            window.addEventListener('resize', updateScrollUI);
            window.addEventListener('load', updateScrollUI);
            updateScrollUI();
        })();

        // Touch Feedback
        document.querySelectorAll('.header-btn, .menu-btn, .btn-edit-glass, .btn-hapus-glass').forEach(el => {
            el.addEventListener('touchstart', function() { this.style.transform = 'scale(0.95)'; });
            el.addEventListener('touchend', function() { this.style.transform = ''; });
        });

        // Auto-hide alert after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-glass');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s, transform 0.5s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(-20px)';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Animated counter for stats
        function animateCounter(id, target, duration = 1000) {
            const el = document.getElementById(id);
            if (!el) return;
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    el.textContent = target;
                    clearInterval(timer);
                } else {
                    el.textContent = Math.floor(current);
                }
            }, 16);
        }

        window.addEventListener('load', () => {
            const totalEl = document.getElementById('statTotal');
            const bulanEl = document.getElementById('statBulan');
            if (totalEl) animateCounter('statTotal', parseInt(totalEl.textContent) || 0);
            if (bulanEl) animateCounter('statBulan', parseInt(bulanEl.textContent) || 0);
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>