<?php
/**
 * Jaringan CICU - Data IP & Perangkat Jaringan
 * Enhanced Dynamic Theme v2.0
 * Tabel: jaringan_cicu (mirror dari inventaris_cicu + ip_address)
 */

include 'koneksi.php';

// Buat tabel jaringan_cicu otomatis jika belum ada
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS jaringan_cicu (
    id INT(11) NOT NULL AUTO_INCREMENT,
    id_asal INT(11) DEFAULT NULL COMMENT 'ID referensi dari inventaris_cicu',
    gedung VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    lantai VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    ruangan VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    nama_pc VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    nama_user VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    manufactur VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    processor_type VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    windows_version VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    ram_size VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    ram_hardisk VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    monitor_model VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    monitor_size VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    monitor_vga VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    printer_1 VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    printer_2 VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    tahun_pengadaan VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    id_personil_edp VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
    ip_address VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Kolom tambahan untuk jaringan',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY id_asal (id_asal),
    KEY ruangan (ruangan),
    KEY nama_pc (nama_pc),
    KEY ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Update IP Address di tabel jaringan_cicu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ip_id'])) {
    $id = intval($_POST['update_ip_id']);
    $ip_address = mysqli_real_escape_string($conn, $_POST['ip_address'] ?? '');
    $sql = "UPDATE jaringan_cicu SET ip_address='$ip_address' WHERE id=$id";
    mysqli_query($conn, $sql);
    header('Location: jaringan_cicu.php?success=ip_updated');
    exit;
}

// Sinkronisasi Manual: Copy data baru dari inventaris_cicu ke jaringan_cicu
if (isset($_GET['sync'])) {
    $sync_sql = "INSERT INTO jaringan_cicu (
        id_asal, gedung, lantai, ruangan, nama_pc, nama_user,
        manufactur, processor_type, windows_version, ram_size, ram_hardisk,
        monitor_model, monitor_size, monitor_vga, printer_1, printer_2,
        tahun_pengadaan, id_personil_edp, ip_address
    )
    SELECT 
        id, gedung, lantai, ruangan, nama_pc, nama_user,
        manufactur, processor_type, windows_version, ram_size, ram_hardisk,
        monitor_model, monitor_size, monitor_vga, printer_1, printer_2,
        tahun_pengadaan, id_personil_edp, NULL
    FROM inventaris_cicu
    WHERE id NOT IN (SELECT id_asal FROM jaringan_cicu WHERE id_asal IS NOT NULL)";
    mysqli_query($conn, $sync_sql);

    // Update data yang sudah ada di jaringan jika ada perubahan di inventaris
    $update_sql = "UPDATE jaringan_cicu j
    INNER JOIN inventaris_cicu i ON j.id_asal = i.id
    SET j.gedung = i.gedung, j.lantai = i.lantai, j.ruangan = i.ruangan,
        j.nama_pc = i.nama_pc, j.nama_user = i.nama_user, j.manufactur = i.manufactur,
        j.processor_type = i.processor_type, j.windows_version = i.windows_version,
        j.ram_size = i.ram_size, j.ram_hardisk = i.ram_hardisk,
        j.monitor_model = i.monitor_model, j.monitor_size = i.monitor_size,
        j.monitor_vga = i.monitor_vga, j.printer_1 = i.printer_1,
        j.printer_2 = i.printer_2, j.tahun_pengadaan = i.tahun_pengadaan,
        j.id_personil_edp = i.id_personil_edp";
    mysqli_query($conn, $update_sql);

    header('Location: jaringan_cicu.php?success=sync');
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Jaringan CICU - Data IP & Perangkat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #0b1120;
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --glass-bg: rgba(15,23,42,0.75);
            --glass-border: rgba(148,163,184,0.12);
            --glass-border-hover: rgba(148,163,184,0.22);
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
            --accent-blue: #38bdf8;
            --accent-blue-glow: rgba(56,189,248,0.25);
            --accent-cyan: #22d3ee;
            --accent-green: #34d399;
            --accent-green-glow: rgba(52,211,153,0.25);
            --accent-amber: #fbbf24;
            --accent-amber-glow: rgba(251,191,36,0.25);
            --accent-red: #f87171;
            --accent-red-glow: rgba(248,113,113,0.25);
            --accent-purple: #a78bfa;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.2);
            --shadow-md: 0 8px 24px rgba(0,0,0,0.25);
            --shadow-lg: 0 16px 48px rgba(0,0,0,0.3);
            --shadow-xl: 0 24px 64px rgba(0,0,0,0.4);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --transition-fast: 150ms cubic-bezier(0.4,0,0.2,1);
            --transition-base: 250ms cubic-bezier(0.4,0,0.2,1);
            --transition-slow: 400ms cubic-bezier(0.4,0,0.2,1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; -webkit-font-smoothing: antialiased; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-deep);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }
        body::before {
            content: "";
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(ellipse 80% 50% at 20% 0%, rgba(56,189,248,0.08), transparent),
                radial-gradient(ellipse 60% 40% at 80% 0%, rgba(167,139,250,0.06), transparent),
                radial-gradient(ellipse 50% 30% at 50% 100%, rgba(34,211,238,0.04), transparent);
        }
        body::after {
            content: "";
            position: fixed; inset: 0; z-index: 0; pointer-events: none; opacity: 0.1;
            background-image: linear-gradient(rgba(255,255,255,0.01) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.01) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInScale { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(120%); } to { opacity: 1; transform: translateX(0); } }
        @keyframes slideOutRight { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(120%); } }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.92) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        @keyframes modalOut { from { opacity: 1; transform: scale(1) translateY(0); } to { opacity: 0; transform: scale(0.92) translateY(20px); } }
        @keyframes ripple { to { transform: scale(4); opacity: 0; } }
        @keyframes toastProgress { to { width: 0%; } }
        @keyframes glowPulse { 0%,100% { box-shadow: 0 0 5px var(--accent-blue-glow); } 50% { box-shadow: 0 0 20px var(--accent-blue-glow), 0 0 40px var(--accent-blue-glow); } }

        .loading-screen { position: fixed; inset: 0; background: var(--bg-deep); z-index: 9999; display: flex; align-items: center; justify-content: center; flex-direction: column; transition: opacity 0.5s, visibility 0.5s; }
        .loading-screen.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
        .loader { width: 50px; height: 50px; border: 3px solid rgba(255,255,255,0.1); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 1s linear infinite; }
        .loading-text { color: var(--text-secondary); margin-top: 20px; font-size: 14px; font-weight: 600; letter-spacing: 2px; animation: pulse 1.5s ease-in-out infinite; }

        .toast-container { position: fixed; top: 24px; right: 24px; z-index: 10000; display: flex; flex-direction: column; gap: 12px; max-width: 400px; width: 100%; pointer-events: none; }
        .toast { position: relative; pointer-events: auto; padding: 16px 20px; border-radius: var(--radius-lg); font-size: 14px; font-weight: 600; color: #fff; box-shadow: var(--shadow-xl); transform: translateX(120%); opacity: 0; animation: slideInRight 0.45s cubic-bezier(0.22,1,0.36,1) forwards; overflow: hidden; display: flex; align-items: center; gap: 12px; }
        .toast.success { background: rgba(6,78,59,0.95); border: 1px solid #10b981; }
        .toast.error { background: rgba(127,29,29,0.95); border: 1px solid #ef4444; }
        .toast.info { background: rgba(30,58,138,0.95); border: 1px solid #3b82f6; }
        .toast-icon { font-size: 20px; flex-shrink: 0; }
        .toast-content { flex: 1; }
        .toast-title { font-weight: 800; font-size: 13px; margin-bottom: 2px; }
        .toast-message { font-weight: 500; opacity: 0.9; font-size: 13px; }
        .toast-progress { position: absolute; bottom: 0; left: 0; height: 3px; background: rgba(255,255,255,0.35); animation: toastProgress 5s linear forwards; }
        .toast-close { position: absolute; top: 8px; right: 10px; background: none; border: none; color: inherit; font-size: 20px; cursor: pointer; opacity: 0.5; transition: opacity 0.2s; padding: 0 4px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-sm); }
        .toast-close:hover { opacity: 1; background: rgba(255,255,255,0.1); }

        .header { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: rgba(15,23,42,0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid var(--glass-border); transition: var(--transition-base); }
        .header.scrolled { background: rgba(15,23,42,0.9); box-shadow: var(--shadow-lg); }
        .header-inner { max-width: 1400px; margin: 0 auto; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; }
        .header-left { display: flex; align-items: center; gap: 16px; }
        .menu-btn { width: 44px; height: 44px; border-radius: var(--radius-md); background: rgba(255,255,255,0.08); border: 1px solid var(--glass-border); color: var(--text-primary); font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-base); }
        .menu-btn:hover { background: rgba(255,255,255,0.15); border-color: var(--glass-border-hover); transform: scale(1.05); }
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo-icon { width: 40px; height: 40px; background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan)); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px var(--accent-blue-glow); animation: glowPulse 3s ease-in-out infinite; }
        .logo-icon i { color: white; font-size: 18px; }
        .logo-text { font-size: 20px; font-weight: 800; color: white; letter-spacing: -0.5px; }
        .logo-text span { font-weight: 300; opacity: 0.7; }
        .header-right { display: flex; align-items: center; gap: 10px; }
        .header-btn { width: 44px; height: 44px; border-radius: var(--radius-md); background: rgba(255,255,255,0.08); border: 1px solid var(--glass-border); color: var(--text-primary); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-base); position: relative; overflow: hidden; }
        .header-btn::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1)); opacity: 0; transition: opacity var(--transition-base); }
        .header-btn:hover::before { opacity: 1; }
        .header-btn:hover { transform: translateY(-2px); border-color: var(--glass-border-hover); box-shadow: var(--shadow-md); }
        .header-btn:active { transform: translateY(0); }
        .header-btn i { position: relative; z-index: 1; transition: transform var(--transition-fast); }
        .header-btn:hover i { transform: scale(1.1); }

        .breadcrumb-bar { position: relative; z-index: 1; padding-top: 90px; padding-bottom: 10px; animation: fadeInDown 0.6s ease; }
        .breadcrumb-inner { max-width: 1400px; margin: 0 auto; padding: 0 24px; }
        .breadcrumb { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--text-muted); flex-wrap: wrap; }
        .breadcrumb a { color: var(--text-secondary); text-decoration: none; transition: var(--transition-fast); font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .breadcrumb a:hover { color: var(--text-primary); }
        .breadcrumb i.fa-chevron-right { font-size: 10px; color: var(--text-dim); }
        .breadcrumb-current { color: var(--text-primary); font-weight: 700; }

        .sidebar-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); z-index: 2000; opacity: 0; visibility: hidden; transition: var(--transition-base); }
        .sidebar-overlay.active { opacity: 1; visibility: visible; }
        .sidebar { position: fixed; top: 0; left: -320px; width: 320px; height: 100%; background: linear-gradient(180deg, var(--bg-dark) 0%, var(--bg-card) 100%); z-index: 2001; transition: left 0.4s cubic-bezier(0.4,0,0.2,1); overflow-y: auto; box-shadow: 10px 0 40px rgba(0,0,0,0.3); }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 30px 24px; background: linear-gradient(135deg, rgba(56,189,248,0.3), rgba(34,211,238,0.2)); position: relative; overflow: hidden; }
        .sidebar-header::before { content: ''; position: absolute; top: -50%; right: -50%; width: 100%; height: 100%; background: rgba(255,255,255,0.1); border-radius: 50%; }
        .sidebar-close { position: absolute; top: 16px; right: 16px; width: 32px; height: 32px; border-radius: var(--radius-sm); background: rgba(255,255,255,0.2); border: none; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-base); }
        .sidebar-close:hover { background: rgba(255,255,255,0.3); transform: rotate(90deg); }
        .sidebar-user { display: flex; align-items: center; gap: 14px; position: relative; z-index: 1; }
        .sidebar-avatar { width: 56px; height: 56px; border-radius: var(--radius-lg); background: white; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: var(--shadow-md); }
        .sidebar-user-info h3 { color: white; font-size: 16px; font-weight: 700; }
        .sidebar-user-info p { color: rgba(255,255,255,0.7); font-size: 12px; margin-top: 2px; }
        .sidebar-menu { padding: 20px 16px; }
        .sidebar-section { margin-bottom: 24px; }
        .sidebar-section-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--text-dim); padding: 0 12px; margin-bottom: 8px; }
        .sidebar-item { display: flex; align-items: center; gap: 14px; padding: 14px 12px; border-radius: var(--radius-md); color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 600; transition: var(--transition-base); margin-bottom: 4px; position: relative; overflow: hidden; }
        .sidebar-item::before { content: ''; position: absolute; left: 0; top: 50%; width: 3px; height: 0; background: var(--accent-blue); border-radius: 0 4px 4px 0; transition: var(--transition-base); transform: translateY(-50%); }
        .sidebar-item:hover, .sidebar-item.active { background: rgba(56,189,248,0.1); color: var(--accent-blue); }
        .sidebar-item:hover::before, .sidebar-item.active::before { height: 60%; }
        .sidebar-item i { width: 24px; text-align: center; font-size: 16px; transition: transform var(--transition-fast); }
        .sidebar-item:hover i { transform: scale(1.15); }

        .main-content { position: relative; z-index: 1; padding-bottom: 60px; }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 24px; }

        .page-header { margin-bottom: 32px; animation: fadeInUp 0.8s ease-out; }
        .page-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(56,189,248,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(56,189,248,0.2); padding: 8px 16px; border-radius: 50px; color: var(--accent-blue); font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .page-badge i { animation: pulse 2s ease-in-out infinite; }
        .page-title { font-size: clamp(26px, 4vw, 38px); font-weight: 900; color: white; line-height: 1.2; margin-bottom: 10px; letter-spacing: -1px; }
        .page-title .gradient-text { background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .page-subtitle { font-size: clamp(13px, 2vw, 15px); color: var(--text-muted); font-weight: 400; max-width: 600px; line-height: 1.6; }

        .glass-card { background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: var(--radius-xl); overflow: hidden; transition: var(--transition-base); margin-bottom: 28px; animation: fadeInUp 0.8s ease-out both; position: relative; }
        .glass-card:nth-child(2) { animation-delay: 0.1s; }
        .glass-card:nth-child(3) { animation-delay: 0.2s; }
        .glass-card:hover { border-color: var(--glass-border-hover); box-shadow: var(--shadow-lg); }
        .glass-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, var(--accent-blue), var(--accent-cyan), transparent); opacity: 0.5; border-radius: var(--radius-xl) var(--radius-xl) 0 0; }
        .card-header-glass { padding: 20px 24px; border-bottom: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; position: relative; }
        .card-header-title { display: flex; align-items: center; gap: 12px; color: white; font-size: 16px; font-weight: 700; }
        .card-header-title i { width: 36px; height: 36px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan)); display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 4px 15px var(--accent-blue-glow); }
        .card-body-glass { padding: 24px; }

        .btn-glass { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: var(--radius-md); font-size: 14px; font-weight: 700; font-family: 'Inter', sans-serif; cursor: pointer; border: none; transition: var(--transition-base); text-decoration: none; position: relative; overflow: hidden; }
        .btn-glass::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1)); opacity: 0; transition: opacity var(--transition-base); }
        .btn-glass:hover::after { opacity: 1; }
        .btn-glass:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-glass:active { transform: translateY(0); }
        .btn-glass i { position: relative; z-index: 1; font-size: 14px; transition: transform var(--transition-fast); }
        .btn-glass:hover i { transform: scale(1.15); }
        .btn-primary-glass { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; box-shadow: 0 4px 15px rgba(37,99,235,0.3); }
        .btn-success-glass { background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 15px rgba(5,150,105,0.3); }
        .btn-danger-glass { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; box-shadow: 0 4px 15px rgba(220,38,38,0.3); padding: 8px 14px; font-size: 12px; border-radius: var(--radius-sm); }
        .btn-danger-glass:hover { box-shadow: 0 6px 20px rgba(220,38,38,0.5); }
        .btn-warning-glass { background: linear-gradient(135deg, #f59e0b, #d97706); color: #1e293b; box-shadow: 0 4px 15px rgba(217,119,6,0.3); padding: 8px 14px; font-size: 12px; border-radius: var(--radius-sm); text-decoration: none; border: none; cursor: pointer; }
        .btn-warning-glass:hover { box-shadow: 0 6px 20px rgba(217,119,6,0.5); }
        .btn-info-glass { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; box-shadow: 0 4px 15px rgba(2,132,199,0.3); padding: 8px 14px; font-size: 12px; border-radius: var(--radius-sm); text-decoration: none; border: none; cursor: pointer; }
        .btn-info-glass:hover { box-shadow: 0 6px 20px rgba(2,132,199,0.5); }
        .btn-back-glass { background: rgba(255,255,255,0.08); border: 1px solid var(--glass-border); color: var(--text-secondary); padding: 10px 18px; font-size: 13px; }
        .btn-back-glass:hover { background: rgba(255,255,255,0.15); color: var(--text-primary); border-color: var(--glass-border-hover); }
        .btn-action-group { display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: nowrap; }

        .action-icon-btn { position: relative; width: 42px; height: 42px; padding: 0 !important; border-radius: var(--radius-md) !important; display: inline-flex !important; align-items: center; justify-content: center; gap: 7px; overflow: hidden; white-space: nowrap; transition: all 0.3s ease; }
        .action-icon-btn i { font-size: 15px; flex-shrink: 0; position: relative; z-index: 1; }
        .action-icon-btn .action-text { max-width: 0; opacity: 0; overflow: hidden; transform: translateX(-5px); transition: all 0.3s ease; font-size: 12px; font-weight: 600; position: relative; z-index: 1; }
        .action-icon-btn:hover { width: 92px; padding: 0 13px !important; transform: translateY(-2px); }
        .action-icon-btn:hover .action-text { max-width: 70px; opacity: 1; transform: translateX(0); }
        .action-icon-btn.ip-btn:hover { width: 105px; }
        .action-icon-btn:active { transform: scale(0.95); }

        .table-wrapper { overflow-x: auto; border-radius: var(--radius-lg); }
        .data-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12px; }
        .data-table thead th { background: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(37,99,235,0.2)); backdrop-filter: blur(10px); color: white; font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 10px; text-align: center; border-bottom: 1px solid var(--glass-border); white-space: nowrap; }
        .data-table thead th:first-child { border-radius: var(--radius-md) 0 0 0; }
        .data-table thead th:last-child { border-radius: 0 var(--radius-md) 0 0; }
        .data-table tbody tr { background: rgba(255,255,255,0.02); transition: var(--transition-fast); animation: fadeInUp 0.4s ease both; }
        .data-table tbody tr:nth-child(1) { animation-delay: 0.02s; }
        .data-table tbody tr:nth-child(2) { animation-delay: 0.04s; }
        .data-table tbody tr:nth-child(3) { animation-delay: 0.06s; }
        .data-table tbody tr:nth-child(4) { animation-delay: 0.08s; }
        .data-table tbody tr:nth-child(5) { animation-delay: 0.10s; }
        .data-table tbody tr:nth-child(6) { animation-delay: 0.12s; }
        .data-table tbody tr:nth-child(7) { animation-delay: 0.14s; }
        .data-table tbody tr:nth-child(8) { animation-delay: 0.16s; }
        .data-table tbody tr:nth-child(9) { animation-delay: 0.18s; }
        .data-table tbody tr:nth-child(10) { animation-delay: 0.20s; }
        .data-table tbody tr:nth-child(even) { background: rgba(255,255,255,0.04); }
        .data-table tbody tr:hover { background: rgba(56,189,248,0.06); }
        .data-table tbody td { padding: 14px 10px; color: var(--text-secondary); border-bottom: 1px solid var(--glass-border); vertical-align: middle; text-align: center; font-size: 12px; transition: var(--transition-fast); }
        .data-table tbody tr:hover td { color: var(--text-primary); }
        .data-table tbody tr:last-child td:first-child { border-radius: 0 0 0 var(--radius-md); }
        .data-table tbody tr:last-child td:last-child { border-radius: 0 0 var(--radius-md) 0; }
        .td-center { text-align: center; }
        .td-small { font-size: 11px; color: var(--text-dim); }
        .td-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 50px; font-size: 10px; font-weight: 700; letter-spacing: 0.3px; }
        .badge-cyan { background: rgba(6,182,212,0.15); color: #67e8f9; border: 1px solid rgba(6,182,212,0.25); }
        .badge-green { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.25); }
        .badge-purple { background: rgba(99,102,241,0.15); color: #a5b4fc; border: 1px solid rgba(99,102,241,0.25); }
        .badge-pink { background: rgba(236,72,153,0.15); color: #f472b6; border: 1px solid rgba(236,72,153,0.25); }
        .badge-orange { background: rgba(249,115,22,0.15); color: #fb923c; border: 1px solid rgba(249,115,22,0.25); }
        .badge-blue { background: rgba(59,130,246,0.15); color: #93c5fd; border: 1px solid rgba(59,130,246,0.25); }
        .badge-ip { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.25); font-family: 'Courier New', monospace; font-size: 11px; }
        .badge-ip-empty { background: rgba(100,116,139,0.15); color: #94a3b8; border: 1px solid rgba(100,116,139,0.25); font-style: italic; }

        .empty-state { text-align: center; padding: 60px 20px; animation: fadeInUp 0.5s ease; }
        .empty-state i { font-size: 48px; margin-bottom: 16px; color: var(--text-dim); display: block; animation: float 3s ease-in-out infinite; }
        .empty-state h4 { color: var(--text-muted); font-size: 16px; margin-bottom: 8px; font-weight: 700; }
        .empty-state p { color: var(--text-dim); font-size: 13px; }

        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(12px); z-index: 3000; opacity: 0; visibility: hidden; transition: var(--transition-base); display: flex; align-items: center; justify-content: center; padding: 20px; }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-content { background: linear-gradient(180deg, var(--bg-card) 0%, var(--bg-dark) 100%); border: 1px solid var(--glass-border); border-radius: var(--radius-xl); width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; transform: translateY(30px) scale(0.95); transition: var(--transition-slow); box-shadow: var(--shadow-xl); position: relative; }
        .modal-overlay.active .modal-content { transform: translateY(0) scale(1); }
        .modal-header { padding: 20px 24px; border-bottom: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: space-between; position: relative; overflow: hidden; }
        .modal-header::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, var(--accent-blue), var(--accent-cyan)); }
        .modal-title { display: flex; align-items: center; gap: 12px; color: white; font-size: 18px; font-weight: 800; }
        .modal-title i { width: 36px; height: 36px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan)); box-shadow: 0 4px 15px var(--accent-blue-glow); display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .modal-close { width: 36px; height: 36px; border-radius: var(--radius-md); background: rgba(255,255,255,0.08); border: 1px solid var(--glass-border); color: var(--text-muted); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-base); }
        .modal-close:hover { background: rgba(239,68,68,0.2); border-color: rgba(239,68,68,0.4); color: var(--accent-red); transform: rotate(90deg); }
        .modal-body { padding: 24px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--glass-border); display: flex; justify-content: flex-end; gap: 12px; }

        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .form-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; display: flex; align-items: center; gap: 6px; }
        .form-label i { font-size: 12px; color: var(--accent-blue); }
        .form-input { width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-md); color: var(--text-primary); font-size: 14px; font-family: 'Inter', sans-serif; transition: var(--transition-base); outline: none; }
        .form-input::placeholder { color: var(--text-dim); opacity: 0.6; }
        .form-input:focus { background: rgba(56,189,248,0.05); border-color: rgba(56,189,248,0.4); box-shadow: 0 0 0 3px rgba(56,189,248,0.1); }

        .footer { position: relative; z-index: 1; text-align: center; padding: 30px 24px; color: var(--text-dim); font-size: 13px; font-weight: 500; }
        .footer i { color: var(--accent-red); animation: pulse 2s ease-in-out infinite; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--glass-border-hover); border-radius: 50px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-dim); }

        @media (max-width: 991px) {
            .header-inner { padding: 12px 20px; }
            .card-header-glass { padding: 16px 20px; }
            .card-body-glass { padding: 20px; }
            .data-table { font-size: 11px; }
            .data-table thead th, .data-table tbody td { padding: 12px 8px; }
            .page-title { font-size: 28px; }
        }
        @media (max-width: 767px) {
            .container { padding: 0 16px; }
            .header-inner { padding: 10px 16px; }
            .logo-text { font-size: 16px; }
            .menu-btn, .header-btn { width: 40px; height: 40px; }
            .breadcrumb-bar { padding-top: 80px; }
            .breadcrumb-inner { padding: 0 16px; }
            .card-header-glass { padding: 16px; }
            .card-body-glass { padding: 16px; }
            .page-title { font-size: 24px; }
            .btn-glass { padding: 10px 18px; font-size: 13px; }
            .data-table thead { display: none; }
            .data-table tbody tr { display: block; margin-bottom: 16px; background: var(--glass-bg); border-radius: var(--radius-lg); border: 1px solid var(--glass-border); overflow: hidden; }
            .data-table tbody td { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--glass-border); text-align: right; }
            .data-table tbody td::before { content: attr(data-label); font-weight: 700; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
            .data-table tbody tr:last-child td:first-child, .data-table tbody tr:last-child td:last-child { border-radius: 0; }
            .data-table tbody tr td:last-child { border-bottom: none; justify-content: center; padding: 16px; }
            .sidebar { width: 280px; }
            .modal-content { max-height: 85vh; }
            .modal-body { padding: 16px; }
            .action-icon-btn { width: 38px; height: 38px; }
            .action-icon-btn:hover { width: 38px; padding: 0 !important; transform: none; }
            .action-icon-btn:hover .action-text { display: none; }
            .action-icon-btn.ip-btn:hover { width: 38px; }
        }
        @media (max-width: 575px) {
            .breadcrumb-bar { padding-top: 75px; }
            .page-header { margin-bottom: 24px; }
            .page-badge { font-size: 11px; padding: 6px 12px; }
            .page-title { font-size: 20px; }
            .page-subtitle { font-size: 12px; }
            .card-header-title { font-size: 14px; }
            .card-header-title i { width: 32px; height: 32px; font-size: 14px; }
            .footer { padding: 20px 16px; font-size: 11px; }
        }
    </style>
</head>

<body>
    <div class="loading-screen" id="loadingScreen">
        <div class="loader"></div>
        <div class="loading-text">MEMUAT JARINGAN CICU...</div>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="sidebar-close" onclick="toggleSidebar()"><i class="fas fa-times"></i></button>
            <div class="sidebar-user">
                <div class="sidebar-avatar"><i class="fas fa-user-shield" style="color: var(--accent-blue);"></i></div>
                <div class="sidebar-user-info"><h3>Administrator</h3><p>admin@inventaris.id</p></div>
            </div>
        </div>
        <nav class="sidebar-menu">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Menu Utama</div>
                <a href="index_modern.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-house"></i> Beranda</a>
                <a href="inventaris.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-warehouse"></i> Data Inventaris</a>
                <a href="kegiatan.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-clipboard-list"></i> Data Kegiatan</a>
                <a href="jaringan.php" class="sidebar-item active" onclick="toggleSidebar()"><i class="fas fa-network-wired"></i> Data Jaringan</a>
                <a href="laporan.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-chart-pie"></i> Data Laporan</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title">Pengaturan</div>
                <a href="#" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-user-gear"></i> Profil</a>
                <a href="#" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-gear"></i> Pengaturan</a>
                <a href="#" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-right-from-bracket"></i> Keluar</a>
            </div>
        </nav>
    </aside>

    <header class="header" id="header">
        <div class="header-inner">
            <div class="header-left">
                <button class="menu-btn" onclick="toggleSidebar()" aria-label="Menu"><i class="fas fa-bars"></i></button>
                <div class="logo">
                    <div class="logo-icon"><i class="fas fa-cubes"></i></div>
                    <span class="logo-text">Inventaris<span>Digital</span></span>
                </div>
            </div>
            <div class="header-right">
                <button class="header-btn" onclick="showToast('Fitur pencarian akan segera hadir!', 'info')" title="Cari"><i class="fas fa-magnifying-glass"></i></button>
                <button class="header-btn" onclick="location.reload()" title="Refresh"><i class="fas fa-rotate"></i></button>
                <button class="header-btn" onclick="showToast('Tidak ada notifikasi baru', 'info')" title="Notifikasi"><i class="fas fa-bell"></i></button>
            </div>
        </div>
    </header>

    <div class="breadcrumb-bar">
        <div class="breadcrumb-inner">
            <div class="breadcrumb">
                <a href="index.php"><i class="fas fa-house"></i> Beranda</a>
                <i class="fas fa-chevron-right"></i>
                <a href="jaringan.php">Data Jaringan</a>
                <i class="fas fa-chevron-right"></i>
                <span class="breadcrumb-current">Jaringan CICU</span>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-badge"><i class="fas fa-network-wired"></i> Jaringan CICU</div>
                <h1 class="page-title">Data Perangkat <span class="gradient-text">Jaringan & IP Address</span></h1>
                <p class="page-subtitle">Kelola alamat IP perangkat jaringan ruangan CICU. Data perangkat diambil dari tabel <strong>jaringan_cicu</strong> yang tersinkron dengan inventaris.</p>
            </section>

            <!-- Tabel Data Jaringan -->
            <div class="glass-card">
                <div class="card-header-glass">
                    <div class="card-header-title"><i class="fas fa-server"></i> Daftar Perangkat & IP Address</div>
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <a href="?sync=1" class="btn-glass btn-primary-glass" style="padding:10px 16px; font-size:13px;" onclick="return confirm('Sinkronisasi data dari inventaris_cicu ke jaringan_cicu? Data yang sudah ada akan diperbarui.')" title="Sinkronisasi Data dari Inventaris">
                            <i class="fas fa-rotate"></i> Sinkron Data
                        </a>
                        <span class="td-badge badge-cyan"><i class="fas fa-database" style="margin-right:4px;"></i> 
                            <?php 
                            $count = mysqli_query($conn, "SELECT COUNT(*) as total FROM jaringan_cicu");
                            $total = ($count) ? (int)mysqli_fetch_assoc($count)['total'] : 0;
                            echo $total . ' Perangkat'; 
                            ?>
                        </span>
                        <a href="jaringan.php" class="btn-glass btn-back-glass"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </div>
                </div>
                <div class="card-body-glass">
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Gedung</th>
                                    <th>Lantai</th>
                                    <th>Ruangan</th>
                                    <th>Nama PC</th>
                                    <th>Nama User</th>
                                    <th>Processor</th>
                                    <th>RAM</th>
                                    <th>Windows</th>
                                    <th>Printer 1</th>
                                    <th>Printer 2</th>
                                    <th>IP Address</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM jaringan_cicu ORDER BY id DESC");
                                if ($query && mysqli_num_rows($query) > 0) {
                                    while ($row = mysqli_fetch_assoc($query)) {
                                        $ip = !empty($row['ip_address']) ? htmlspecialchars($row['ip_address']) : '';
                                ?>
                                <tr>
                                    <td data-label="No" class="td-center"><span class="td-badge badge-purple"><i class="fas fa-hashtag" style="font-size:9px;"></i> <?= $no++; ?></span></td>
                                    <td data-label="Gedung"><span class="td-badge badge-cyan"><i class="fas fa-building" style="font-size:9px;"></i> <?= htmlspecialchars($row['gedung']); ?></span></td>
                                    <td data-label="Lantai"><?= htmlspecialchars($row['lantai']); ?></td>
                                    <td data-label="Ruangan"><span class="td-badge badge-pink"><i class="fas fa-door-open" style="font-size:9px;"></i> <?= htmlspecialchars($row['ruangan']); ?></span></td>
                                    <td data-label="Nama PC"><strong style="color:var(--text-primary);"><?= htmlspecialchars($row['nama_pc']); ?></strong></td>
                                    <td data-label="Nama User"><i class="fas fa-user" style="color:var(--accent-green);margin-right:4px;font-size:10px;"></i><?= htmlspecialchars($row['nama_user']); ?></td>
                                    <td data-label="Processor"><span class="td-small"><i class="fas fa-microchip" style="margin-right:4px;"></i><?= htmlspecialchars($row['processor_type']); ?></span></td>
                                    <td data-label="RAM"><span class="td-badge badge-cyan"><?= htmlspecialchars($row['ram_size']); ?></span></td>
                                    <td data-label="Windows"><span class="td-badge badge-green"><i class="fas fa-windows" style="font-size:9px;"></i> <?= htmlspecialchars($row['windows_version']); ?></span></td>
                                    <td data-label="Printer 1"><?= $row['printer_1'] ? '<i class="fas fa-print" style="color:var(--accent-amber);margin-right:4px;font-size:10px;"></i>' . htmlspecialchars($row['printer_1']) : '-'; ?></td>
                                    <td data-label="Printer 2"><?= $row['printer_2'] ? '<i class="fas fa-print" style="color:var(--accent-amber);margin-right:4px;font-size:10px;"></i>' . htmlspecialchars($row['printer_2']) : '-'; ?></td>
                                    <td data-label="IP Address">
                                        <?php if ($ip): ?>
                                            <span class="td-badge badge-ip"><i class="fas fa-globe" style="font-size:9px;"></i> <?= $ip; ?></span>
                                        <?php else: ?>
                                            <span class="td-badge badge-ip-empty"><i class="fas fa-globe" style="font-size:9px;"></i> Belum diatur</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="btn-action-group">
                                            <button type="button" class="btn-glass btn-info-glass action-icon-btn ip-btn" onclick="openIpModal(<?= $row['id']; ?>, '<?= $ip; ?>', '<?= htmlspecialchars($row['nama_pc']); ?>')" title="Atur IP Address">
                                                <i class="fas fa-globe"></i><span class="action-text">Set IP</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php } } else { ?>
                                <tr>
                                    <td colspan="13">
                                        <div class="empty-state">
                                            <i class="fas fa-network-wired"></i>
                                            <h4>Belum Ada Data Perangkat</h4>
                                            <p>Data perangkat diambil dari tabel <strong>jaringan_cicu</strong>. Klik tombol <strong>Sinkron Data</strong> untuk mengisi dari inventaris, atau pastikan trigger database sudah aktif.</p>
                                            <a href="?sync=1" class="btn-glass btn-primary-glass" style="margin-top:20px;" onclick="return confirm('Sinkronisasi data sekarang?')">
                                                <i class="fas fa-rotate"></i> Sinkronisasi Sekarang
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL SET IP ADDRESS -->
    <div class="modal-overlay" id="ipModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title"><i class="fas fa-globe"></i> Atur IP Address</div>
                <button class="modal-close" onclick="closeIpModal()"><i class="fas fa-xmark"></i></button>
            </div>
            <form method="POST" action="" id="ipForm">
                <input type="hidden" name="update_ip_id" id="update_ip_id">
                <div class="modal-body">
                    <div class="info-box" style="background: rgba(255,255,255,0.04); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px;">
                        <p style="color: var(--text-muted); font-size: 13px; line-height: 1.6; margin: 0;">
                            <i class="fas fa-desktop" style="color:var(--accent-blue); margin-right:6px;"></i>
                            <strong style="color: var(--text-primary);">Perangkat:</strong> <span id="ip_pc_name">-</span>
                        </p>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-globe"></i> IP Address</label>
                        <input type="text" name="ip_address" id="ip_address_input" class="form-input" placeholder="Contoh: 192.168.1.100" pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Format IP Address tidak valid. Contoh: 192.168.1.1">
                        <small style="color: var(--text-dim); font-size: 11px; margin-top: 4px;">Kosongkan jika belum memiliki IP Address.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-glass btn-back-glass" onclick="closeIpModal()"><i class="fas fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-glass btn-success-glass"><i class="fas fa-floppy-disk"></i> Simpan IP</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p><i class="fas fa-copyright" style="color:var(--text-dim);animation:none;"></i> <?php echo date('Y'); ?> Inventaris Digital. Dibangun dengan <i class="fas fa-heart"></i> untuk Indonesia.</p>
    </footer>

    <script>
        // Loading Screen
        window.addEventListener('load', function() {
            setTimeout(() => { document.getElementById('loadingScreen').classList.add('hidden'); }, 800);
        });

        // Toast Notifications
        function showToast(message, type) {
            type = type || 'success';
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            let icon = 'fa-circle-check', title = 'Berhasil';
            if (type === 'error') { icon = 'fa-circle-xmark'; title = 'Gagal'; }
            if (type === 'info') { icon = 'fa-circle-info'; title = 'Info'; }
            toast.innerHTML = 
                '<div class="toast-icon"><i class="fas ' + icon + '"></i></div>' +
                '<div class="toast-content"><div class="toast-title">' + title + '</div><div class="toast-message">' + message + '</div></div>' +
                '<button class="toast-close" onclick="this.parentElement.remove()">&times;</button>' +
                '<div class="toast-progress"></div>';
            container.appendChild(toast);
            setTimeout(function() {
                toast.style.animation = 'slideOutRight 0.35s ease forwards';
                setTimeout(function() { if(toast.parentElement) toast.remove(); }, 400);
            }, 5000);
        }

        // Check URL params for success messages
        (function() {
            const params = new URLSearchParams(window.location.search);
            const success = params.get('success');
            if (success === 'ip_updated') showToast('IP Address berhasil diperbarui!', 'success');
            if (success === 'sync') showToast('Sinkronisasi data berhasil!', 'success');
            if (window.history.replaceState && success) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        })();

        // Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 20) header.classList.add('scrolled');
            else header.classList.remove('scrolled');
        });

        // Modal IP
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            const modal = document.getElementById(id);
            const content = modal.querySelector('.modal-content');
            content.style.animation = 'modalOut 0.25s ease forwards';
            setTimeout(function() {
                modal.classList.remove('active');
                content.style.animation = '';
                document.body.style.overflow = '';
            }, 250);
        }

        function openIpModal(id, currentIp, pcName) {
            document.getElementById('update_ip_id').value = id;
            document.getElementById('ip_address_input').value = currentIp;
            document.getElementById('ip_pc_name').textContent = pcName || '-';
            openModal('ipModal');
            setTimeout(() => document.getElementById('ip_address_input').focus(), 300);
        }
        function closeIpModal() { closeModal('ipModal'); }

        // Click outside to close
        document.getElementById('ipModal').addEventListener('click', function(e) {
            if (e.target === this) closeIpModal();
        });

        // Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeIpModal();
        });

        // Ripple effect
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-glass, .header-btn, .menu-btn');
            if (!btn) return;
            const ripple = document.createElement('span');
            const rect = btn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.cssText = 'position:absolute;border-radius:50%;background:rgba(255,255,255,0.25);width:' + size + 'px;height:' + size + 'px;left:' + (e.clientX - rect.left - size/2) + 'px;top:' + (e.clientY - rect.top - size/2) + 'px;pointer-events:none;animation:ripple 0.6s ease-out;';
            btn.style.position = 'relative';
            btn.style.overflow = 'hidden';
            btn.appendChild(ripple);
            setTimeout(function() { ripple.remove(); }, 600);
        });
    </script>
</body>
</html>