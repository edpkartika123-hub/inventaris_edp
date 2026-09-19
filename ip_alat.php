<?php
/**
 * Aplikasi Inventaris - Halaman IP Alat (ip_alat.php)
 * CRUD Manajemen IP Address Alat
 * Struktur database: lihat database_ip_alat.sql
 */
session_start();

$user = [
    'nama' => 'Administrator',
    'email' => 'admin@inventaris.id',
    'role' => 'Super Admin'
];

// ================= KONFIGURASI DATABASE =================
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'db_inventaris_digital';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$db_error = ($mysqli->connect_errno) ? $mysqli->connect_error : null;

if (!$db_error) {
    $mysqli->set_charset('utf8mb4');
}

// ================= HELPER =================
function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }

function is_valid_ip($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
}

function is_valid_mac($mac) {
    return preg_match('/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/', $mac);
}

// ================= AKSI (POST) =================
$flash = null; // ['type' => 'success'|'error', 'msg' => '...']

if (!$db_error && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah' || $aksi === 'edit') {
        $id         = (int)($_POST['id'] ?? 0);
        $kode_alat  = trim($_POST['kode_alat'] ?? '');
        $nama_alat  = trim($_POST['nama_alat'] ?? '');
        $kategori   = trim($_POST['kategori'] ?? 'Lainnya');
        $ip_address = trim($_POST['ip_address'] ?? '');
        $subnet     = trim($_POST['subnet_mask'] ?? '255.255.255.0');
        $gateway    = trim($_POST['gateway'] ?? '');
        $mac        = trim($_POST['mac_address'] ?? '');
        $unit       = trim($_POST['unit'] ?? '');
        $ruangan    = trim($_POST['ruangan'] ?? '');
        $status     = trim($_POST['status'] ?? 'Aktif');
        $ket        = trim($_POST['keterangan'] ?? '');

        // Validasi
        $err = [];
        if ($kode_alat === '' || $nama_alat === '') $err[] = 'Kode dan Nama Alat wajib diisi.';
        if (!is_valid_ip($ip_address))               $err[] = 'Format IP Address tidak valid.';
        if (!is_valid_ip($subnet))                   $err[] = 'Format Subnet Mask tidak valid.';
        if ($gateway !== '' && !is_valid_ip($gateway)) $err[] = 'Format Gateway tidak valid.';
        if ($mac !== '' && !is_valid_mac($mac))      $err[] = 'Format MAC Address tidak valid (contoh: AA:BB:CC:DD:EE:FF).';

        if (empty($err)) {
            if ($aksi === 'tambah') {
                $stmt = $mysqli->prepare(
                    "INSERT INTO ip_alat (kode_alat, nama_alat, kategori, ip_address, subnet_mask, gateway, mac_address, unit, ruangan, status, keterangan)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?)"
                );
                $stmt->bind_param('sssssssssss', $kode_alat, $nama_alat, $kategori, $ip_address, $subnet, $gateway, $mac, $unit, $ruangan, $status, $ket);
            } else {
                $stmt = $mysqli->prepare(
                    "UPDATE ip_alat SET kode_alat=?, nama_alat=?, kategori=?, ip_address=?, subnet_mask=?, gateway=?, mac_address=?, unit=?, ruangan=?, status=?, keterangan=? WHERE id=?"
                );
                $stmt->bind_param('sssssssssssi', $kode_alat, $nama_alat, $kategori, $ip_address, $subnet, $gateway, $mac, $unit, $ruangan, $status, $ket, $id);
            }

            if ($stmt->execute()) {
                $flash = ['type' => 'success', 'msg' => $aksi === 'tambah' ? 'Data IP Alat berhasil ditambahkan.' : 'Data IP Alat berhasil diperbarui.'];
            } else {
                $flash = ['type' => 'error', 'msg' => 'Gagal menyimpan data: ' . ($stmt->error ?: 'Kemungkinan IP Address sudah terdaftar.')];
            }
            $stmt->close();
        } else {
            $flash = ['type' => 'error', 'msg' => implode(' ', $err)];
        }
    } elseif ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $mysqli->prepare("DELETE FROM ip_alat WHERE id=?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $flash = ['type' => 'success', 'msg' => 'Data IP Alat berhasil dihapus.'];
        } else {
            $flash = ['type' => 'error', 'msg' => 'Data tidak ditemukan atau gagal dihapus.'];
        }
        $stmt->close();
    }
}

// ================= AMBIL DATA =================
$q        = trim($_GET['q'] ?? '');
$f_unit   = trim($_GET['unit'] ?? '');
$f_status = trim($_GET['status'] ?? '');

$units_list = ['Jaringan Kartika 1', 'Jaringan Kartika 2', 'Jaringan Kartika 3', 'Jaringan CVC', 'Jaringan CICU', 'Jaringan RSPAD'];

if (!$db_error) {
    $where = [];
    $types = '';
    $params = [];

    if ($q !== '') {
        $where[] = "(kode_alat LIKE ? OR nama_alat LIKE ? OR ip_address LIKE ? OR mac_address LIKE ?)";
        $like = "%$q%";
        $types .= 'ssss';
        array_push($params, $like, $like, $like, $like);
    }
    if ($f_unit !== '')   { $where[] = "unit = ?";   $types .= 's'; $params[] = $f_unit; }
    if ($f_status !== '') { $where[] = "status = ?"; $types .= 's'; $params[] = $f_status; }

    $sql = "SELECT * FROM ip_alat" . (count($where) ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY id DESC";
    $stmt = $mysqli->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Statistik
    $stats = ['total' => 0, 'aktif' => 0, 'nonaktif' => 0, 'maintenance' => 0];
    $res = $mysqli->query("SELECT status, COUNT(*) AS jml FROM ip_alat GROUP BY status");
    while ($r = $res->fetch_assoc()) {
        $stats['total'] += (int)$r['jml'];
        $key = strtolower($r['status']);
        if (isset($stats[$key])) $stats[$key] = (int)$r['jml'];
    }
} else {
    // Mode demo jika database belum tersedia
    $rows = [];
    $stats = ['total' => 0, 'aktif' => 0, 'nonaktif' => 0, 'maintenance' => 0];
}

// ================= MENU AKTIF =================
$current_page = 'ip_alat.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inventaris Digital - IP Alat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --dark-card: #1e293b;
            --dark-surface: #334155;
            --light: #f8fafc;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;

            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);

            --radius-sm: 12px;
            --radius: 20px;
            --radius-lg: 28px;

            --transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--dark);
            min-height: 100vh;
            color: var(--text-primary);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ========== BACKGROUND ========== */
        .bg-mesh {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 0; overflow: hidden;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(236, 72, 153, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(6, 182, 212, 0.08) 0%, transparent 60%),
                linear-gradient(180deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        }
        .bg-orb {
            position: absolute; border-radius: 50%;
            filter: blur(80px); opacity: 0.4;
            animation: orb-float 20s ease-in-out infinite;
        }
        .bg-orb:nth-child(1) { width: 400px; height: 400px; background: rgba(99,102,241,0.3); top: -10%; left: -5%; }
        .bg-orb:nth-child(2) { width: 300px; height: 300px; background: rgba(236,72,153,0.2); top: 40%; right: -5%; animation-delay: -7s; }
        .bg-orb:nth-child(3) { width: 350px; height: 350px; background: rgba(6,182,212,0.2); bottom: -10%; left: 30%; animation-delay: -14s; }
        @keyframes orb-float {
            0%, 100% { transform: translate(0,0) scale(1); }
            33% { transform: translate(30px,-30px) scale(1.1); }
            66% { transform: translate(-20px,20px) scale(0.9); }
        }
        .bg-grid {
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            z-index: 0; pointer-events: none;
            mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
        }

        /* ========== LOADING ========== */
        .loading-screen {
            position: fixed; inset: 0; background: var(--dark); z-index: 9999;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        .loading-screen.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
        .loader-ring { position: relative; width: 80px; height: 80px; }
        .loader-ring::before, .loader-ring::after {
            content: ''; position: absolute; inset: 0; border-radius: 50%; border: 3px solid transparent;
        }
        .loader-ring::before { border-top-color: var(--primary); border-right-color: rgba(99,102,241,0.3); animation: spin 1.2s linear infinite; }
        .loader-ring::after { border-bottom-color: var(--accent); border-left-color: rgba(6,182,212,0.3); animation: spin 1.8s linear infinite reverse; inset: 8px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-brand { margin-top: 28px; display: flex; align-items: center; gap: 12px; }
        .loading-brand-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            color: white; font-size: 14px;
        }
        .loading-brand-text { font-size: 18px; font-weight: 700; color: white; letter-spacing: -0.5px; }
        .loading-brand-text span { font-weight: 400; color: var(--text-secondary); }
        .loading-progress { margin-top: 24px; width: 200px; height: 3px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; }
        .loading-progress-bar { height: 100%; width: 0%; background: linear-gradient(90deg, var(--primary), var(--accent)); border-radius: 3px; animation: load-progress 1.5s ease-out forwards; }
        @keyframes load-progress { 0% { width: 0%; } 100% { width: 100%; } }

        /* ========== HEADER ========== */
        .header { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; transition: var(--transition); }
        .header.scrolled {
            background: rgba(15,23,42,0.8);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .header-inner { max-width: 1440px; margin: 0 auto; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; }
        .header-left { display: flex; align-items: center; gap: 20px; }

        .menu-btn {
            width: 44px; height: 44px; border-radius: 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            color: white; font-size: 16px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: var(--transition-fast);
        }
        .menu-btn:hover { background: rgba(255,255,255,0.12); transform: scale(1.05); }
        .menu-btn:active { transform: scale(0.95); }
        .menu-btn .menu-line { display: block; width: 18px; height: 2px; background: currentColor; border-radius: 2px; transition: var(--transition-fast); position: relative; }
        .menu-btn .menu-line::before, .menu-btn .menu-line::after { content: ''; position: absolute; left: 0; width: 18px; height: 2px; background: currentColor; border-radius: 2px; transition: var(--transition-fast); }
        .menu-btn .menu-line::before { top: -6px; }
        .menu-btn .menu-line::after { top: 6px; }
        .menu-btn.active .menu-line { background: transparent; }
        .menu-btn.active .menu-line::before { top: 0; transform: rotate(45deg); }
        .menu-btn.active .menu-line::after { top: 0; transform: rotate(-45deg); }

        .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 20px rgba(99,102,241,0.4);
            position: relative; overflow: hidden;
        }
        .logo-icon::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.2) 100%); }
        .logo-icon i { color: white; font-size: 18px; position: relative; z-index: 1; }
        .logo-text { font-size: 20px; font-weight: 800; color: white; letter-spacing: -0.5px; line-height: 1; }
        .logo-text span { font-weight: 500; color: var(--text-secondary); }

        .header-right { display: flex; align-items: center; gap: 10px; }
        .header-btn {
            width: 44px; height: 44px; border-radius: 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            color: white; font-size: 15px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: var(--transition-fast); position: relative;
        }
        .header-btn:hover { background: rgba(255,255,255,0.12); transform: translateY(-2px); }

        /* ========== SIDEBAR ========== */
        .sidebar-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 2000; opacity: 0; visibility: hidden; transition: var(--transition); }
        .sidebar-overlay.active { opacity: 1; visibility: visible; }
        .sidebar {
            position: fixed; top: 0; left: 0; width: 320px; height: 100%;
            background: linear-gradient(180deg, rgba(30,41,59,0.98) 0%, rgba(15,23,42,0.98) 100%);
            backdrop-filter: blur(30px); z-index: 2001;
            transform: translateX(-100%);
            transition: transform 0.5s cubic-bezier(0.4,0,0.2,1);
            overflow-y: auto; border-right: 1px solid rgba(255,255,255,0.06);
            display: flex; flex-direction: column;
        }
        .sidebar.active { transform: translateX(0); }
        .sidebar-header {
            padding: 32px 24px;
            background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.1));
            border-bottom: 1px solid rgba(255,255,255,0.06);
            position: relative; overflow: hidden;
        }
        .sidebar-close { position: absolute; top: 16px; right: 16px; width: 32px; height: 32px; border-radius: 10px; background: rgba(255,255,255,0.1); border: none; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-fast); font-size: 14px; }
        .sidebar-close:hover { background: rgba(255,255,255,0.2); transform: rotate(90deg); }
        .sidebar-user { display: flex; align-items: center; gap: 16px; }
        .sidebar-avatar {
            width: 56px; height: 56px; border-radius: 18px;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; box-shadow: 0 4px 20px rgba(99,102,241,0.3);
            position: relative;
        }
        .sidebar-avatar::after { content: ''; position: absolute; bottom: 0; right: 0; width: 14px; height: 14px; background: var(--success); border-radius: 50%; border: 3px solid var(--dark-card); }
        .sidebar-user-info h3 { color: white; font-size: 16px; font-weight: 700; margin-bottom: 2px; }
        .sidebar-user-info p { color: var(--text-secondary); font-size: 12px; font-weight: 500; }
        .sidebar-user-info .role-badge {
            display: inline-flex; align-items: center; gap: 4px;
            margin-top: 6px; padding: 3px 10px;
            background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.2);
            border-radius: 20px; font-size: 10px; font-weight: 600;
            color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px;
        }
        .sidebar-menu { padding: 24px 16px; flex: 1; }
        .sidebar-section { margin-bottom: 28px; }
        .sidebar-section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-muted); padding: 0 12px; margin-bottom: 10px; }
        .sidebar-item {
            display: flex; align-items: center; gap: 14px;
            padding: 14px; border-radius: 14px;
            color: var(--text-secondary); text-decoration: none;
            font-size: 14px; font-weight: 600;
            transition: var(--transition-fast); margin-bottom: 4px;
            position: relative; overflow: hidden;
        }
        .sidebar-item::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 0; background: linear-gradient(180deg, var(--primary), var(--accent)); border-radius: 0 3px 3px 0; transition: var(--transition-fast); }
        .sidebar-item:hover { background: rgba(99,102,241,0.08); color: var(--text-primary); }
        .sidebar-item.active { background: rgba(99,102,241,0.12); color: var(--primary); }
        .sidebar-item.active::before { height: 60%; }
        .sidebar-item i { width: 24px; text-align: center; font-size: 16px; }
        .sidebar-footer { padding: 20px 24px; border-top: 1px solid rgba(255,255,255,0.06); }
        .sidebar-logout {
            display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 14px;
            color: var(--danger); text-decoration: none; font-size: 14px; font-weight: 600;
            transition: var(--transition-fast); background: rgba(239,68,68,0.05);
            border: 1px solid rgba(239,68,68,0.1);
        }
        .sidebar-logout:hover { background: rgba(239,68,68,0.1); transform: translateX(4px); }

        /* ========== BREADCRUMB ========== */
        .breadcrumb-bar { position: relative; z-index: 1; padding-top: 100px; padding-bottom: 16px; }
        .breadcrumb-inner { max-width: 1440px; margin: 0 auto; padding: 0 32px; }
        .breadcrumb { display: flex; align-items: center; gap: 12px; font-size: 13px; font-weight: 600; flex-wrap: wrap; }
        .breadcrumb a { color: var(--text-muted); text-decoration: none; transition: var(--transition-fast); display: flex; align-items: center; gap: 6px; }
        .breadcrumb a:hover { color: white; }
        .breadcrumb-sep { color: var(--text-muted); font-size: 10px; }
        .breadcrumb-current { color: white; font-weight: 700; }

        /* ========== MAIN ========== */
        .main-content { position: relative; z-index: 1; padding-bottom: 40px; }
        .container { max-width: 1440px; margin: 0 auto; padding: 0 32px; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

        .page-header { margin-bottom: 32px; animation: fadeInUp 0.8s ease-out; }
        .page-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
            padding: 8px 18px; border-radius: 50px;
            color: var(--text-secondary); font-size: 13px; font-weight: 600;
            margin-bottom: 20px; backdrop-filter: blur(10px);
            animation: slideInRight 0.6s ease-out;
        }
        .page-badge i { color: #fbbf24; }
        .page-title { font-size: clamp(28px, 5vw, 44px); font-weight: 800; color: white; line-height: 1.15; margin-bottom: 12px; letter-spacing: -1.5px; }
        .page-title .gradient-text {
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #ec4899);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .page-subtitle { font-size: clamp(14px, 2vw, 16px); color: var(--text-secondary); font-weight: 500; max-width: 600px; line-height: 1.7; }

        /* ========== STATS ========== */
        .stats-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;
            margin-bottom: 32px; animation: fadeInUp 0.8s ease-out 0.1s both;
        }
        .stat-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius);
            padding: 24px;
            display: flex; align-items: center; gap: 18px;
            transition: var(--transition);
            position: relative; overflow: hidden;
        }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; opacity: 0; transition: var(--transition); }
        .stat-card:hover { transform: translateY(-4px); border-color: rgba(255,255,255,0.12); }
        .stat-card:hover::before { opacity: 1; }
        .stat-card.total::before { background: linear-gradient(90deg, var(--primary), #8b5cf6); }
        .stat-card.aktif::before { background: linear-gradient(90deg, #10b981, #059669); }
        .stat-card.nonaktif::before { background: linear-gradient(90deg, #64748b, #475569); }
        .stat-card.maintenance::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
        .stat-icon {
            width: 56px; height: 56px; border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: white; flex-shrink: 0;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }
        .stat-card.total .stat-icon { background: linear-gradient(135deg, #818cf8, #6366f1); }
        .stat-card.aktif .stat-icon { background: linear-gradient(135deg, #34d399, #10b981); }
        .stat-card.nonaktif .stat-icon { background: linear-gradient(135deg, #94a3b8, #64748b); }
        .stat-card.maintenance .stat-icon { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
        .stat-info h3 { font-size: 28px; font-weight: 800; color: white; line-height: 1; margin-bottom: 4px; letter-spacing: -1px; }
        .stat-info p { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }

        /* ========== PANEL / TABLE ========== */
        .panel {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius-lg);
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }
        .panel-header {
            padding: 24px 28px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; flex-wrap: wrap;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .panel-title { font-size: 18px; font-weight: 800; color: white; display: flex; align-items: center; gap: 12px; letter-spacing: -0.3px; }
        .panel-title i { color: #fbbf24; }

        .toolbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .search-box { position: relative; }
        .search-box i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px; pointer-events: none; }
        .search-input, .filter-select {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px; color: white;
            font-family: inherit; font-size: 13px; font-weight: 600;
            padding: 12px 16px; outline: none;
            transition: var(--transition-fast);
        }
        .search-input { padding-left: 42px; width: 240px; }
        .search-input:focus, .filter-select:focus { border-color: var(--primary); background: rgba(99,102,241,0.08); box-shadow: 0 0 0 4px rgba(99,102,241,0.12); }
        .search-input::placeholder { color: var(--text-muted); }
        .filter-select { cursor: pointer; appearance: none; padding-right: 40px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2394a3b8' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 16px center;
        }
        .filter-select option { background: var(--dark-card); color: white; }

        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 22px; border-radius: 14px;
            font-family: inherit; font-size: 13px; font-weight: 700;
            border: none; cursor: pointer; text-decoration: none;
            transition: var(--transition-bounce); position: relative; overflow: hidden;
        }
        .btn-primary { background: linear-gradient(135deg, var(--primary), #8b5cf6); color: white; box-shadow: 0 8px 24px rgba(99,102,241,0.35); }
        .btn-primary:hover { transform: translateY(-2px) scale(1.03); box-shadow: 0 12px 32px rgba(99,102,241,0.5); }
        .btn-primary:active { transform: scale(0.97); }
        .btn-danger { background: linear-gradient(135deg, #f43f5e, #e11d48); color: white; box-shadow: 0 8px 24px rgba(244,63,94,0.3); }
        .btn-danger:hover { transform: translateY(-2px) scale(1.03); }
        .btn-ghost { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: white; }
        .btn-ghost:hover { background: rgba(255,255,255,0.12); }

        /* Table */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1000px; }
        thead th {
            text-align: left; padding: 16px 20px;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px;
            color: var(--text-muted);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.02);
            white-space: nowrap;
        }
        tbody td { padding: 18px 20px; font-size: 14px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
        tbody tr { transition: var(--transition-fast); animation: rowIn 0.4s ease-out both; }
        tbody tr:hover { background: rgba(99,102,241,0.05); }
        tbody tr:last-child td { border-bottom: none; }
        @keyframes rowIn { from { opacity: 0; transform: translateX(-12px); } to { opacity: 1; transform: translateX(0); } }

        .td-alat { display: flex; align-items: center; gap: 14px; }
        .td-alat-icon {
            width: 42px; height: 42px; border-radius: 14px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: white;
        }
        .td-alat-name { font-weight: 700; color: white; font-size: 14px; margin-bottom: 2px; }
        .td-alat-code { font-size: 11px; color: var(--text-muted); font-weight: 600; }
        .ip-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(6,182,212,0.12); border: 1px solid rgba(6,182,212,0.25);
            color: #67e8f9; padding: 6px 12px; border-radius: 10px;
            font-family: 'Courier New', monospace; font-size: 13px; font-weight: 700;
            white-space: nowrap;
        }
        .mac-text { font-family: 'Courier New', monospace; font-size: 12px; color: var(--text-secondary); font-weight: 600; }
        .unit-tag { display: inline-block; padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; white-space: nowrap; }

        .status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: 50px;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .status-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; }
        .status-aktif { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.25); }
        .status-aktif::before { background: #34d399; box-shadow: 0 0 8px #34d399; animation: dot-pulse 2s infinite; }
        .status-nonaktif { background: rgba(100,116,139,0.12); color: #94a3b8; border: 1px solid rgba(100,116,139,0.25); }
        .status-nonaktif::before { background: #94a3b8; }
        .status-maintenance { background: rgba(245,158,11,0.12); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25); }
        .status-maintenance::before { background: #fbbf24; animation: dot-pulse 1s infinite; }
        @keyframes dot-pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

        .action-btns { display: flex; gap: 8px; }
        .action-btn {
            width: 36px; height: 36px; border-radius: 12px; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; transition: var(--transition-bounce);
        }
        .btn-edit { background: rgba(99,102,241,0.12); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.2); }
        .btn-edit:hover { background: rgba(99,102,241,0.25); transform: translateY(-2px) scale(1.08); }
        .btn-delete { background: rgba(239,68,68,0.12); color: #fca5a5; border: 1px solid rgba(239,68,68,0.2); }
        .btn-delete:hover { background: rgba(239,68,68,0.25); transform: translateY(-2px) scale(1.08); }

        .empty-state { text-align: center; padding: 70px 24px; }
        .empty-state i { font-size: 52px; color: var(--text-muted); opacity: 0.4; margin-bottom: 18px; }
        .empty-state h3 { color: white; font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .empty-state p { color: var(--text-muted); font-size: 13px; }

        .panel-footer { padding: 18px 28px; border-top: 1px solid rgba(255,255,255,0.06); font-size: 12px; color: var(--text-muted); font-weight: 600; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }

        /* ========== FLASH / ALERT ========== */
        .flash {
            display: flex; align-items: center; gap: 12px;
            padding: 16px 22px; border-radius: var(--radius-sm);
            font-size: 13px; font-weight: 600; margin-bottom: 24px;
            animation: slideInRight 0.5s ease-out;
        }
        .flash-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #34d399; }
        .flash-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
        .flash-warning { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); color: #fbbf24; }

        /* ========== MODAL ========== */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 3000;
            background: rgba(0,0,0,0.7); backdrop-filter: blur(10px);
            display: flex; align-items: center; justify-content: center;
            padding: 20px; opacity: 0; visibility: hidden; transition: var(--transition);
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal {
            width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-lg);
            transform: scale(0.9) translateY(30px); transition: var(--transition-bounce);
            box-shadow: var(--shadow-xl);
        }
        .modal-overlay.active .modal { transform: scale(1) translateY(0); }
        .modal-header {
            padding: 26px 30px; display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            background: linear-gradient(135deg, rgba(99,102,241,0.15), transparent);
            position: sticky; top: 0; z-index: 2;
        }
        .modal-title { font-size: 18px; font-weight: 800; color: white; display: flex; align-items: center; gap: 12px; }
        .modal-title i { color: #fbbf24; }
        .modal-close { width: 36px; height: 36px; border-radius: 12px; background: rgba(255,255,255,0.08); border: none; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-fast); font-size: 14px; }
        .modal-close:hover { background: rgba(239,68,68,0.2); transform: rotate(90deg); }
        .modal-body { padding: 28px 30px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-label { font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.8px; }
        .form-label .req { color: #f43f5e; }
        .form-input, .form-select, .form-textarea {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px; color: white;
            font-family: inherit; font-size: 14px; font-weight: 600;
            padding: 13px 16px; outline: none; width: 100%;
            transition: var(--transition-fast);
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--primary); background: rgba(99,102,241,0.08);
            box-shadow: 0 0 0 4px rgba(99,102,241,0.12);
        }
        .form-input::placeholder, .form-textarea::placeholder { color: var(--text-muted); font-weight: 500; }
        .form-select { cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2394a3b8' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 16px center;
        }
        .form-select option { background: var(--dark-card); }
        .form-textarea { resize: vertical; min-height: 80px; }
        .modal-footer { padding: 22px 30px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: flex-end; gap: 12px; position: sticky; bottom: 0; background: rgba(15,23,42,0.95); backdrop-filter: blur(10px); }

        .modal.modal-sm { max-width: 440px; }
        .delete-icon-box {
            width: 72px; height: 72px; border-radius: 24px; margin: 0 auto 20px;
            background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; color: #f87171;
        }
        .delete-text { text-align: center; color: var(--text-secondary); font-size: 14px; line-height: 1.7; margin-bottom: 8px; }
        .delete-text strong { color: white; }

        /* ========== FOOTER ========== */
        .footer { position: relative; z-index: 1; text-align: center; padding: 40px 24px; color: var(--text-muted); font-size: 13px; font-weight: 600; }

        /* ========== SCROLLBAR ========== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

        [data-tooltip] { position: relative; }
        [data-tooltip]::after {
            content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
            transform: translateX(-50%) translateY(4px); padding: 6px 12px;
            background: var(--dark-card); color: white; font-size: 12px; font-weight: 600;
            border-radius: 8px; white-space: nowrap; opacity: 0; visibility: hidden;
            transition: var(--transition-fast); border: 1px solid rgba(255,255,255,0.1);
            pointer-events: none; z-index: 10;
        }
        [data-tooltip]:hover::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1199px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 991px) {
            .header-inner { padding: 14px 24px; }
            .container, .breadcrumb-inner { padding: 0 24px; }
            .breadcrumb-bar { padding-top: 90px; }
            .form-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 767px) {
            .container, .breadcrumb-inner { padding: 0 16px; }
            .header-inner { padding: 12px 16px; }
            .breadcrumb-bar { padding-top: 80px; }
            .logo-text { font-size: 16px; }
            .menu-btn, .header-btn { width: 40px; height: 40px; border-radius: 12px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .stat-card { padding: 16px; gap: 12px; }
            .stat-icon { width: 44px; height: 44px; border-radius: 14px; font-size: 18px; }
            .stat-info h3 { font-size: 22px; }
            .panel-header { padding: 18px 16px; }
            .search-input { width: 100%; }
            .search-box { width: 100%; }
            .toolbar { width: 100%; }
            .btn span.btn-text { display: none; }
            .btn { padding: 12px 16px; }
            .sidebar { width: 300px; }
            .modal-body { padding: 20px 18px; }
            .modal-header, .modal-footer { padding-left: 18px; padding-right: 18px; }
        }
        @media (max-width: 575px) {
            .breadcrumb-bar { padding-top: 75px; }
            .page-title { font-size: 24px; }
            .page-badge { font-size: 11px; padding: 6px 14px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loader-ring"></div>
        <div class="loading-brand">
            <div class="loading-brand-icon"><i class="fas fa-cubes"></i></div>
            <div class="loading-brand-text">Inventaris<span>Digital</span></div>
        </div>
        <div class="loading-progress"><div class="loading-progress-bar"></div></div>
    </div>

    <!-- Background -->
    <div class="bg-mesh">
        <div class="bg-orb"></div>
        <div class="bg-orb"></div>
        <div class="bg-orb"></div>
    </div>
    <div class="bg-grid"></div>

    <!-- Sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="sidebar-close" onclick="toggleSidebar()"><i class="fas fa-xmark"></i></button>
            <div class="sidebar-user">
                <div class="sidebar-avatar">👤</div>
                <div class="sidebar-user-info">
                    <h3><?php echo e($user['nama']); ?></h3>
                    <p><?php echo e($user['email']); ?></p>
                    <span class="role-badge"><i class="fas fa-shield-halved" style="font-size:8px"></i> <?php echo e($user['role']); ?></span>
                </div>
            </div>
        </div>
        <nav class="sidebar-menu">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Menu Utama</div>
                <a href="index.php" class="sidebar-item"><i class="fas fa-house"></i> Beranda</a>
                <a href="inventaris.php" class="sidebar-item"><i class="fas fa-warehouse"></i> Data Inventaris</a>
                <a href="kegiatan.php" class="sidebar-item"><i class="fas fa-list-check"></i> Data Kegiatan</a>
                <a href="jaringan.php" class="sidebar-item"><i class="fas fa-diagram-project"></i> Data Jaringan</a>
                <a href="ip_alat.php" class="sidebar-item active"><i class="fas fa-network-wired"></i> IP Alat</a>
                <a href="laporan.php" class="sidebar-item"><i class="fas fa-chart-line"></i> Data Laporan</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title">Pengaturan</div>
                <a href="#" class="sidebar-item"><i class="fas fa-user-gear"></i> Profil</a>
                <a href="#" class="sidebar-item"><i class="fas fa-sliders"></i> Pengaturan</a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <a href="#" class="sidebar-logout" onclick="return confirm('Yakin ingin keluar?')">
                <i class="fas fa-arrow-right-from-bracket"></i> Keluar
            </a>
        </div>
    </aside>

    <!-- Header -->
    <header class="header" id="header">
        <div class="header-inner">
            <div class="header-left">
                <button class="menu-btn" id="menuBtn" onclick="toggleSidebar()" aria-label="Menu">
                    <span class="menu-line"></span>
                </button>
                <a href="index.php" class="logo">
                    <div class="logo-icon"><i class="fas fa-cubes"></i></div>
                    <span class="logo-text">Inventaris<span>Digital</span></span>
                </a>
            </div>
            <div class="header-right">
                <button class="header-btn" data-tooltip="Refresh" onclick="location.reload()"><i class="fas fa-rotate"></i></button>
                <button class="header-btn" data-tooltip="Tambah IP" onclick="openModal()"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <div class="breadcrumb-inner">
            <div class="breadcrumb">
                <a href="index.php"><i class="fas fa-house"></i> Beranda</a>
                <i class="fas fa-chevron-right breadcrumb-sep"></i>
                <a href="jaringan.php">Data Jaringan</a>
                <i class="fas fa-chevron-right breadcrumb-sep"></i>
                <span class="breadcrumb-current">IP Alat</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-badge"><i class="fas fa-network-wired"></i> Modul Jaringan</div>
                <h1 class="page-title">Manajemen <span class="gradient-text">IP Alat</span></h1>
                <p class="page-subtitle">Kelola dan pantau alokasi alamat IP seluruh perangkat jaringan dalam satu dashboard terpadu.</p>
            </section>

            <?php if ($flash): ?>
            <div class="flash flash-<?php echo e($flash['type']); ?>" id="flashMsg">
                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'circle-check' : 'circle-exclamation'; ?>"></i>
                <span><?php echo e($flash['msg']); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($db_error): ?>
            <div class="flash flash-warning">
                <i class="fas fa-triangle-exclamation"></i>
                <span>Koneksi database gagal: <?php echo e($db_error); ?> — Pastikan database <strong>inventaris_db</strong> sudah diimpor dari file <strong>database_ip_alat.sql</strong> dan konfigurasi koneksi di <strong>ip_alat.php</strong> sudah benar.</span>
            </div>
            <?php endif; ?>

            <!-- Statistik -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <div class="stat-icon"><i class="fas fa-network-wired"></i></div>
                    <div class="stat-info"><h3><?php echo $stats['total']; ?></h3><p>Total Alat</p></div>
                </div>
                <div class="stat-card aktif">
                    <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
                    <div class="stat-info"><h3><?php echo $stats['aktif']; ?></h3><p>Aktif</p></div>
                </div>
                <div class="stat-card nonaktif">
                    <div class="stat-icon"><i class="fas fa-circle-xmark"></i></div>
                    <div class="stat-info"><h3><?php echo $stats['nonaktif']; ?></h3><p>Nonaktif</p></div>
                </div>
                <div class="stat-card maintenance">
                    <div class="stat-icon"><i class="fas fa-screwdriver-wrench"></i></div>
                    <div class="stat-info"><h3><?php echo $stats['maintenance']; ?></h3><p>Maintenance</p></div>
                </div>
            </div>

            <!-- Panel Tabel -->
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title"><i class="fas fa-table-list"></i> Daftar IP Alat</h2>
                    <div class="toolbar">
                        <form method="GET" action="ip_alat.php" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                            <div class="search-box">
                                <i class="fas fa-magnifying-glass"></i>
                                <input type="text" name="q" class="search-input" placeholder="Cari kode, nama, IP, MAC..." value="<?php echo e($q); ?>">
                            </div>
                            <select name="unit" class="filter-select" onchange="this.form.submit()">
                                <option value="">Semua Unit</option>
                                <?php foreach ($units_list as $u): ?>
                                <option value="<?php echo e($u); ?>" <?php echo $f_unit === $u ? 'selected' : ''; ?>><?php echo e($u); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="status" class="filter-select" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="Aktif" <?php echo $f_status === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="Nonaktif" <?php echo $f_status === 'Nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                <option value="Maintenance" <?php echo $f_status === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                            <button type="submit" class="btn btn-ghost"><i class="fas fa-filter"></i> <span class="btn-text">Filter</span></button>
                        </form>
                        <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> <span class="btn-text">Tambah IP Alat</span></button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Alat</th>
                                <th>Kategori</th>
                                <th>IP Address</th>
                                <th>Gateway</th>
                                <th>MAC Address</th>
                                <th>Unit / Ruangan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($rows) > 0): ?>
                                <?php
                                $kategori_icon = [
                                    'Router' => ['fa-router', '#06b6d4', '#22d3ee'],
                                    'Switch' => ['fa-ethernet', '#8b5cf6', '#a78bfa'],
                                    'Access Point' => ['fa-wifi', '#10b981', '#34d399'],
                                    'Server' => ['fa-server', '#f59e0b', '#fbbf24'],
                                    'PC' => ['fa-desktop', '#6366f1', '#818cf8'],
                                    'Printer' => ['fa-print', '#ec4899', '#f472b6'],
                                    'CCTV' => ['fa-video', '#e11d48', '#fb7185'],
                                    'Lainnya' => ['fa-microchip', '#64748b', '#94a3b8'],
                                ];
                                $unit_colors = [
                                    'Jaringan Kartika 1' => ['rgba(6,182,212,0.12)', '#67e8f9'],
                                    'Jaringan Kartika 2' => ['rgba(139,92,246,0.12)', '#c4b5fd'],
                                    'Jaringan Kartika 3' => ['rgba(16,185,129,0.12)', '#6ee7b7'],
                                    'Jaringan CVC' => ['rgba(245,158,11,0.12)', '#fcd34d'],
                                    'Jaringan CICU' => ['rgba(225,29,72,0.12)', '#fda4af'],
									'Jaringan RSPAD' => ['rgba(225,29,72,0.12)', '#ef4444'],
                                ];
                                $no = 1;
                                foreach ($rows as $row):
                                    $kat = $row['kategori'] ?? 'Lainnya';
                                    [$icon, $c1, $c2] = $kategori_icon[$kat] ?? $kategori_icon['Lainnya'];
                                    [$ubg, $utx] = $unit_colors[$row['unit']] ?? ['rgba(255,255,255,0.08)', '#cbd5e1'];
                                    $status_key = strtolower($row['status']);
                                ?>
                                <tr>
                                    <td style="color:var(--text-muted);font-weight:700;"><?php echo $no++; ?></td>
                                    <td>
                                        <div class="td-alat">
                                            <div class="td-alat-icon" style="background:linear-gradient(135deg,<?php echo $c2; ?>,<?php echo $c1; ?>);">
                                                <i class="fas <?php echo $icon; ?>"></i>
                                            </div>
                                            <div>
                                                <div class="td-alat-name"><?php echo e($row['nama_alat']); ?></div>
                                                <div class="td-alat-code"><?php echo e($row['kode_alat']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="unit-tag" style="background:rgba(255,255,255,0.07);color:var(--text-secondary);"><?php echo e($kat); ?></span></td>
                                    <td><span class="ip-badge"><i class="fas fa-globe" style="font-size:10px"></i> <?php echo e($row['ip_address']); ?></span></td>
                                    <td class="mac-text"><?php echo e($row['gateway'] ?: '—'); ?></td>
                                    <td class="mac-text"><?php echo e($row['mac_address'] ?: '—'); ?></td>
                                    <td>
                                        <span class="unit-tag" style="background:<?php echo $ubg; ?>;color:<?php echo $utx; ?>;"><?php echo e($row['unit'] ?: '—'); ?></span>
                                        <?php if (!empty($row['ruangan'])): ?>
                                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;"><?php echo e($row['ruangan']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="status-badge status-<?php echo e($status_key); ?>"><?php echo e($row['status']); ?></span></td>
                                    <td>
                                        <div class="action-btns">
                                            <button class="action-btn btn-edit" data-tooltip="Edit" onclick='openEdit(<?php echo json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><i class="fas fa-pen"></i></button>
                                            <button class="action-btn btn-delete" data-tooltip="Hapus" onclick="openDelete(<?php echo (int)$row['id']; ?>, '<?php echo e($row['nama_alat']); ?>')"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <i class="fas fa-network-wired"></i>
                                            <h3>Belum Ada Data IP Alat</h3>
                                            <p><?php echo $db_error ? 'Hubungkan database terlebih dahulu untuk mulai mengelola data.' : 'Klik tombol "Tambah IP Alat" untuk menambahkan data pertama Anda.'; ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="panel-footer">
                    <span>Menampilkan <?php echo count($rows); ?> data</span>
                    <span><i class="fas fa-database" style="margin-right:6px"></i> inventaris_db &rsaquo; ip_alat</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Inventaris Digital. Dibangun dengan <i class="fas fa-heart" style="color:#f43f5e"></i> untuk Indonesia.</p>
    </footer>

    <!-- ========== MODAL TAMBAH / EDIT ========== -->
    <div class="modal-overlay" id="modalForm">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-network-wired"></i> <span id="modalTitle">Tambah IP Alat</span></h3>
                <button class="modal-close" onclick="closeModalForm()"><i class="fas fa-xmark"></i></button>
            </div>
            <form method="POST" action="ip_alat.php" id="formAlat">
                <input type="hidden" name="aksi" id="formAksi" value="tambah">
                <input type="hidden" name="id" id="formId" value="">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Kode Alat <span class="req">*</span></label>
                            <input type="text" name="kode_alat" id="f_kode" class="form-input" placeholder="cth: JRG-K1-RTR01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Alat <span class="req">*</span></label>
                            <input type="text" name="nama_alat" id="f_nama" class="form-input" placeholder="cth: Router Utama Kartika 1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kategori <span class="req">*</span></label>
                            <select name="kategori" id="f_kategori" class="form-select">
                                <option value="Router">Router</option>
                                <option value="Switch">Switch</option>
                                <option value="Access Point">Access Point</option>
                                <option value="Server">Server</option>
                                <option value="PC">PC</option>
                                <option value="Printer">Printer</option>
                                <option value="CCTV">CCTV</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status <span class="req">*</span></label>
                            <select name="status" id="f_status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">IP Address <span class="req">*</span></label>
                            <input type="text" name="ip_address" id="f_ip" class="form-input" placeholder="192.168.1.1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subnet Mask <span class="req">*</span></label>
                            <input type="text" name="subnet_mask" id="f_subnet" class="form-input" value="255.255.255.0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Gateway</label>
                            <input type="text" name="gateway" id="f_gateway" class="form-input" placeholder="192.168.1.1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">MAC Address</label>
                            <input type="text" name="mac_address" id="f_mac" class="form-input" placeholder="AA:BB:CC:DD:EE:FF">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit Jaringan</label>
                            <select name="unit" id="f_unit" class="form-select">
                                <option value="">— Pilih Unit —</option>
                                <?php foreach ($units_list as $u): ?>
                                <option value="<?php echo e($u); ?>"><?php echo e($u); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ruangan / Lokasi</label>
                            <input type="text" name="ruangan" id="f_ruangan" class="form-input" placeholder="cth: Ruang Kartika 1">
                        </div>
                        <div class="form-group full">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="f_ket" class="form-textarea" placeholder="Catatan tambahan (opsional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="closeModalForm()"><i class="fas fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> <span id="btnSubmitText">Simpan Data</span></button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========== MODAL HAPUS ========== -->
    <div class="modal-overlay" id="modalDelete">
        <div class="modal modal-sm">
            <div class="modal-body" style="padding-top:36px;">
                <div class="delete-icon-box"><i class="fas fa-trash-can"></i></div>
                <h3 style="text-align:center;color:white;font-size:18px;font-weight:800;margin-bottom:10px;">Hapus Data IP Alat?</h3>
                <p class="delete-text">Anda akan menghapus data <strong id="deleteName"></strong>. Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <form method="POST" action="ip_alat.php" id="formDelete">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="id" id="deleteId" value="">
                <div class="modal-footer" style="justify-content:center;">
                    <button type="button" class="btn btn-ghost" onclick="closeModalDelete()"><i class="fas fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" style="position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px); background: rgba(30,41,59,0.95); backdrop-filter: blur(20px); color: white; padding: 14px 28px; border-radius: 16px; font-size: 14px; font-weight: 600; z-index: 9998; opacity: 0; transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 40px rgba(0,0,0,0.3); pointer-events: none; white-space: nowrap;">
        <i class="fas fa-circle-info" style="margin-right:8px;color:var(--accent)"></i>
        <span id="toastMessage"></span>
    </div>

    <script>
        // Loading Screen
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('loadingScreen').classList.add('hidden');
            }, 1500);
        });

        // Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const menuBtn = document.getElementById('menuBtn');
            const isActive = sidebar.classList.toggle('active');
            overlay.classList.toggle('active', isActive);
            menuBtn.classList.toggle('active', isActive);
            document.body.style.overflow = isActive ? 'hidden' : '';
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (document.getElementById('modalForm').classList.contains('active')) closeModalForm();
                else if (document.getElementById('modalDelete').classList.contains('active')) closeModalDelete();
                else if (document.getElementById('sidebar').classList.contains('active')) toggleSidebar();
            }
        });

        // Header Scroll
        window.addEventListener('scroll', function() {
            document.getElementById('header').classList.toggle('scrolled', window.scrollY > 30);
        });

        // Flash auto-hide
        const flash = document.getElementById('flashMsg');
        if (flash) setTimeout(() => { flash.style.transition = 'all .5s'; flash.style.opacity = '0'; flash.style.transform = 'translateX(20px)'; setTimeout(() => flash.remove(), 500); }, 5000);

        // Modal Form (Tambah / Edit)
        function openModal(data) {
            const form = document.getElementById('formAlat');
            form.reset();
            document.getElementById('formAksi').value = 'tambah';
            document.getElementById('formId').value = '';
            document.getElementById('modalTitle').textContent = 'Tambah IP Alat';
            document.getElementById('btnSubmitText').textContent = 'Simpan Data';
            document.getElementById('f_subnet').value = '255.255.255.0';

            if (data) {
                document.getElementById('formAksi').value = 'edit';
                document.getElementById('formId').value = data.id || '';
                document.getElementById('modalTitle').textContent = 'Edit IP Alat';
                document.getElementById('btnSubmitText').textContent = 'Update Data';
                document.getElementById('f_kode').value = data.kode_alat || '';
                document.getElementById('f_nama').value = data.nama_alat || '';
                document.getElementById('f_kategori').value = data.kategori || 'Lainnya';
                document.getElementById('f_status').value = data.status || 'Aktif';
                document.getElementById('f_ip').value = data.ip_address || '';
                document.getElementById('f_subnet').value = data.subnet_mask || '255.255.255.0';
                document.getElementById('f_gateway').value = data.gateway || '';
                document.getElementById('f_mac').value = data.mac_address || '';
                document.getElementById('f_unit').value = data.unit || '';
                document.getElementById('f_ruangan').value = data.ruangan || '';
                document.getElementById('f_ket').value = data.keterangan || '';
            }
            document.getElementById('modalForm').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function openEdit(data) { openModal(data); }
        function closeModalForm() {
            document.getElementById('modalForm').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Modal Delete
        function openDelete(id, name) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteName').textContent = name;
            document.getElementById('modalDelete').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeModalDelete() {
            document.getElementById('modalDelete').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Validasi client-side dasar
        document.getElementById('formAlat').addEventListener('submit', function(e) {
            const ip = document.getElementById('f_ip').value.trim();
            const subnet = document.getElementById('f_subnet').value.trim();
            const gateway = document.getElementById('f_gateway').value.trim();
            const mac = document.getElementById('f_mac').value.trim();
            const ipRegex = /^(\d{1,3}\.){3}\d{1,3}$/;
            if (!ipRegex.test(ip)) { e.preventDefault(); showToast('Format IP Address tidak valid'); return; }
            if (!ipRegex.test(subnet)) { e.preventDefault(); showToast('Format Subnet Mask tidak valid'); return; }
            if (gateway && !ipRegex.test(gateway)) { e.preventDefault(); showToast('Format Gateway tidak valid'); return; }
            if (mac && !/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/.test(mac)) { e.preventDefault(); showToast('Format MAC Address tidak valid'); return; }
        });

        // Toast
        function showToast(message) {
            const toast = document.getElementById('toast');
            document.getElementById('toastMessage').textContent = message;
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(-50%) translateY(0)';
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(100px)';
            }, 3000);
        }
    </script>
</body>
</html>