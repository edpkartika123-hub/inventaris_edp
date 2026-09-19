<?php

/**

- Aplikasi Inventaris - Halaman Data Inventaris
- Menu: Kartika 1, Kartika 2, Kartika 3, CVC, CICU, Perbaikan, Disposal
- Ultra Dynamic & Professional Responsive Design

  */

  session_start();

// ===== KONEKSI DB & AMBIL SEMUA DATA INVENTARIS (UNTUK SCAN QR) =====

include 'koneksi.php';

$scan_units = [

'kartika1' => ['label' => 'Kartika 1', 'table' => 'inventaris_kartika1', 'url' => 'kartika1.php', 'color' => '#06b6d4'],

'kartika2' => ['label' => 'Kartika 2', 'table' => 'inventaris_kartika2', 'url' => 'kartika2.php', 'color' => '#8b5cf6'],

'kartika3' => ['label' => 'Kartika 3', 'table' => 'inventaris_kartika3', 'url' => 'kartika3.php', 'color' => '#10b981'],

'cvc'      => ['label' => 'CVC',       'table' => 'inventaris_cvc',      'url' => 'cvc.php',      'color' => '#f59e0b'],

'cicu'     => ['label' => 'CICU',      'table' => 'inventaris_cicu',     'url' => 'cicu.php',     'color' => '#f97316'],

];

$all_inventaris = [];

foreach ($scan_units as $uid => $meta) {

$tbl = mysqli_real_escape_string($conn, $meta['table']);

$cek = mysqli_query($conn, "SHOW TABLES LIKE '$tbl'");

if (!$cek || mysqli_num_rows($cek) === 0) continue;

$q = mysqli_query($conn, "SELECT * FROM `$tbl` ORDER BY id DESC");

if (!$q) continue;

while ($r = mysqli_fetch_assoc($q)) {

$r['_unit']  = $meta['label'];

$r['_unit_url']   = $meta['url'];

$r['_unit_color'] = $meta['color'];

$all_inventaris[] = $r;

}

}

$user = [

'nama' => 'Administrator',

'email' => '[admin@inventaris.id](mailto:admin@inventaris.id)',

'role' => 'Super Admin'

];

$units = [

[

'id' => 'kartika1',

'title' => 'Kartika 1',

'desc' => 'Kelola data inventaris ruangan Kartika 1 secara detail dan terstruktur.',

'icon' => 'fa-bed-pulse',

'url' => 'kartika1.php',

'color' => 'cyan',

'gradient' => ['#06b6d4', '#0891b2']

],

[

'id' => 'kartika2',

'title' => 'Kartika 2',

'desc' => 'Kelola data inventaris ruangan Kartika 2 secara detail dan terstruktur.',

'icon' => 'fa-user-injured',

'url' => 'kartika2.php',

'color' => 'violet',

'gradient' => ['#8b5cf6', '#7c3aed']

],

[

'id' => 'kartika3',

'title' => 'Kartika 3',

'desc' => 'Kelola data inventaris ruangan Kartika 3 secara detail dan terstruktur.',

'icon' => 'fa-hospital',

'url' => 'kartika3.php',

'color' => 'emerald',

'gradient' => ['#10b981', '#059669']

],

[

'id' => 'cvc',

'title' => 'CVC',

'desc' => 'Kelola data inventaris ruangan CVC secara detail dan terstruktur.',

'icon' => 'fa-heart-pulse',

'url' => 'cvc.php',

'color' => 'amber',

'gradient' => ['#f59e0b', '#d97706']

],

[

'id' => 'cicu',

'title' => 'CICU',

'desc' => 'Kelola data inventaris ruangan CICU secara detail dan terstruktur.',

'icon' => 'fa-kit-medical',

'url' => 'cicu.php',

'color' => 'orange',

'gradient' => ['#f97316', '#ea580c']

],

[

'id' => 'gudang',

'title' => 'Perbaikan',

'desc' => 'Lihat dan kelola barang-barang yang dipindahkan ke gudang.',

'icon' => 'fa-warehouse',

'url' => 'gudang.php',

'color' => 'slate',

'gradient' => ['#475569', '#334155']

],

[

'id' => 'disposal',

'title' => 'Disposal',

'desc' => 'Lihat riwayat barang yang telah didisposal dari inventaris.',

'icon' => 'fa-trash-can',

'url' => 'disposal.php',

'color' => 'rose',

'gradient' => ['#e11d48', '#be123c']

]

];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="description" content="Inventaris Digital - Manajemen inventaris dan scanner QR/barcode">
    <title>Inventaris Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --accent: #06b6d4;
        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --dark: #0f172a;
        --dark-card: #1e293b;
        --dark-surface: #334155;
        --text-primary: #f8fafc;
        --text-secondary: #cbd5e1;
        --text-muted: #94a3b8;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
        --shadow-glow: 0 0 40px rgba(99, 102, 241, 0.15);
        
        --radius-sm: 12px;
        --radius: 20px;
        --radius-lg: 28px;
        --radius-xl: 36px;
        
        --transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
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

    /* ========== ANIMATED MESH BACKGROUND ========== */
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
    .bg-orb:nth-child(1) {
        width: 400px; height: 400px;
        background: rgba(99, 102, 241, 0.3);
        top: -10%; left: -5%; animation-delay: 0s;
    }
    .bg-orb:nth-child(2) {
        width: 300px; height: 300px;
        background: rgba(236, 72, 153, 0.2);
        top: 40%; right: -5%; animation-delay: -7s;
    }
    .bg-orb:nth-child(3) {
        width: 350px; height: 350px;
        background: rgba(6, 182, 212, 0.2);
        bottom: -10%; left: 30%; animation-delay: -14s;
    }
    @keyframes orb-float {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -30px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
    }

    .bg-grid {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-image: 
            linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
        background-size: 60px 60px;
        z-index: 0; pointer-events: none;
        mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse at center, black 40%, transparent 80%);
    }

    /* ========== LOADING SCREEN ========== */
    .loading-screen {
        position: fixed; inset: 0;
        background: var(--dark); z-index: 9999;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        transition: opacity 0.6s ease, visibility 0.6s ease;
    }
    .loading-screen.hidden { opacity: 0; visibility: hidden; pointer-events: none; }

    .loader-ring { position: relative; width: 80px; height: 80px; }
    .loader-ring::before, .loader-ring::after {
        content: ''; position: absolute; inset: 0; border-radius: 50%; border: 3px solid transparent;
    }
    .loader-ring::before {
        border-top-color: var(--primary); border-right-color: rgba(99, 102, 241, 0.3);
        animation: spin 1.2s linear infinite;
    }
    .loader-ring::after {
        border-bottom-color: var(--accent); border-left-color: rgba(6, 182, 212, 0.3);
        animation: spin 1.8s linear infinite reverse; inset: 8px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .loading-brand { margin-top: 28px; display: flex; align-items: center; gap: 12px; }
    .loading-brand-icon {
        width: 32px; height: 32px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        border-radius: 10px; display: flex; align-items: center; justify-content: center;
        color: white; font-size: 14px;
        animation: pulse-brand 2s ease-in-out infinite;
    }
    @keyframes pulse-brand {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }
    .loading-brand-text { font-size: 18px; font-weight: 700; color: white; letter-spacing: -0.5px; }
    .loading-brand-text span { font-weight: 400; color: var(--text-secondary); }

    .loading-progress { margin-top: 24px; width: 200px; height: 3px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; }
    .loading-progress-bar { height: 100%; width: 0%; background: linear-gradient(90deg, var(--primary), var(--accent)); border-radius: 3px; animation: load-progress 1.5s ease-out forwards; }
    @keyframes load-progress { 0% { width: 0%; } 100% { width: 100%; } }

    /* ========== HEADER ========== */
    .header { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; transition: var(--transition); }
    .header.scrolled {
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }
    .header-inner { max-width: 1440px; margin: 0 auto; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; }
    .header-left { display: flex; align-items: center; gap: 20px; }

    .menu-btn {
        width: 44px; height: 44px; border-radius: 14px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white; font-size: 16px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: var(--transition-fast); position: relative; overflow: hidden;
    }
    .menu-btn:hover { background: rgba(255, 255, 255, 0.12); transform: scale(1.05); border-color: rgba(255, 255, 255, 0.2); }
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
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        position: relative; overflow: hidden;
    }
    .logo-icon::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.2) 100%); }
    .logo-icon i { color: white; font-size: 18px; position: relative; z-index: 1; }
    .logo-text { font-size: 20px; font-weight: 800; color: white; letter-spacing: -0.5px; line-height: 1; }
    .logo-text span { font-weight: 500; color: var(--text-secondary); }

    .header-right { display: flex; align-items: center; gap: 10px; }
    .header-btn {
        width: 44px; height: 44px; border-radius: 14px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: white; font-size: 15px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: var(--transition-fast); position: relative;
    }
    .header-btn:hover { background: rgba(255, 255, 255, 0.12); transform: translateY(-2px); border-color: rgba(255, 255, 255, 0.2); }
    .header-btn:active { transform: translateY(0) scale(0.95); }
    .header-btn .badge {
        position: absolute; top: 8px; right: 8px;
        width: 8px; height: 8px; background: var(--danger);
        border-radius: 50%; border: 2px solid var(--dark);
        animation: badge-pulse 2s ease-in-out infinite;
    }
    @keyframes badge-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        50% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
    }

    /* ========== BREADCRUMB ========== */
    .breadcrumb-bar { position: relative; z-index: 1; padding-top: 100px; padding-bottom: 16px; }
    .breadcrumb-inner { max-width: 1440px; margin: 0 auto; padding: 0 32px; }
    .breadcrumb {
        display: flex; align-items: center; gap: 12px;
        font-size: 13px; font-weight: 600;
    }
    .breadcrumb a {
        color: var(--text-muted); text-decoration: none;
        transition: var(--transition-fast); display: flex; align-items: center; gap: 6px;
    }
    .breadcrumb a:hover { color: white; }
    .breadcrumb a i { font-size: 12px; }
    .breadcrumb-sep { color: var(--text-muted); font-size: 10px; }
    .breadcrumb-current { color: white; font-weight: 700; }

    /* ========== SIDEBAR ========== */
    .sidebar-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px); z-index: 2000; opacity: 0; visibility: hidden; transition: var(--transition); }
    .sidebar-overlay.active { opacity: 1; visibility: visible; }
    .sidebar {
        position: fixed; top: 0; left: 0; width: 320px; height: 100%;
        background: linear-gradient(180deg, rgba(30, 41, 59, 0.98) 0%, rgba(15, 23, 42, 0.98) 100%);
        backdrop-filter: blur(30px); z-index: 2001;
        transform: translateX(-100%);
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-y: auto; border-right: 1px solid rgba(255, 255, 255, 0.06);
        display: flex; flex-direction: column;
    }
    .sidebar.active { transform: translateX(0); }
    .sidebar-header {
        padding: 32px 24px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.1));
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        position: relative; overflow: hidden;
    }
    .sidebar-header::before { content: ''; position: absolute; top: -50%; right: -30%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(99, 102, 241, 0.15), transparent 70%); }
    .sidebar-close { position: absolute; top: 16px; right: 16px; width: 32px; height: 32px; border-radius: 10px; background: rgba(255, 255, 255, 0.1); border: none; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-fast); font-size: 14px; }
    .sidebar-close:hover { background: rgba(255, 255, 255, 0.2); transform: rotate(90deg); }
    .sidebar-user { display: flex; align-items: center; gap: 16px; position: relative; z-index: 1; }
    .sidebar-avatar {
        width: 56px; height: 56px; border-radius: 18px;
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
        position: relative; overflow: hidden;
    }
    .sidebar-avatar::after { content: ''; position: absolute; bottom: 0; right: 0; width: 14px; height: 14px; background: var(--success); border-radius: 50%; border: 3px solid var(--dark-card); }
    .sidebar-user-info h3 { color: white; font-size: 16px; font-weight: 700; margin-bottom: 2px; }
    .sidebar-user-info p { color: var(--text-secondary); font-size: 12px; font-weight: 500; }
    .sidebar-user-info .role-badge {
        display: inline-flex; align-items: center; gap: 4px;
        margin-top: 6px; padding: 3px 10px;
        background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.2);
        border-radius: 20px; font-size: 10px; font-weight: 600;
        color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px;
    }
    .sidebar-menu { padding: 24px 16px; flex: 1; }
    .sidebar-section { margin-bottom: 28px; }
    .sidebar-section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-muted); padding: 0 12px; margin-bottom: 10px; }
    .sidebar-item {
        display: flex; align-items: center; gap: 14px;
        padding: 14px 14px; border-radius: 14px;
        color: var(--text-secondary); text-decoration: none;
        font-size: 14px; font-weight: 600;
        transition: var(--transition-fast); margin-bottom: 4px;
        position: relative; overflow: hidden;
    }
    .sidebar-item::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 0; background: linear-gradient(180deg, var(--primary), var(--accent)); border-radius: 0 3px 3px 0; transition: var(--transition-fast); }
    .sidebar-item:hover { background: rgba(99, 102, 241, 0.08); color: var(--text-primary); }
    .sidebar-item.active { background: rgba(99, 102, 241, 0.12); color: var(--primary); }
    .sidebar-item.active::before { height: 60%; }
    .sidebar-item i { width: 24px; text-align: center; font-size: 16px; transition: var(--transition-fast); }
    .sidebar-item:hover i { transform: scale(1.15); }
    .sidebar-footer { padding: 20px 24px; border-top: 1px solid rgba(255, 255, 255, 0.06); }
    .sidebar-logout {
        display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 14px;
        color: var(--danger); text-decoration: none; font-size: 14px; font-weight: 600;
        transition: var(--transition-fast); background: rgba(239, 68, 68, 0.05);
        border: 1px solid rgba(239, 68, 68, 0.1);
    }
    .sidebar-logout:hover { background: rgba(239, 68, 68, 0.1); transform: translateX(4px); }

    /* ========== MAIN CONTENT ========== */
    .main-content { position: relative; z-index: 1; padding-bottom: 40px; }
    .container { max-width: 1440px; margin: 0 auto; padding: 0 32px; }

    /* ========== PAGE HEADER ========== */
    .page-header { margin-bottom: 40px; animation: fadeInUp 0.8s ease-out; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideInRight { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

    .page-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 8px 18px; border-radius: 50px;
        color: var(--text-secondary); font-size: 13px; font-weight: 600;
        margin-bottom: 20px; backdrop-filter: blur(10px);
        animation: slideInRight 0.6s ease-out;
    }
    .page-badge i { color: #fbbf24; }
    .page-title {
        font-size: clamp(28px, 5vw, 44px); font-weight: 800;
        color: white; line-height: 1.15; margin-bottom: 16px; letter-spacing: -1.5px;
    }
    .page-title .gradient-text {
        background: linear-gradient(135deg, #fbbf24, #f59e0b, #ec4899);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .page-subtitle { font-size: clamp(14px, 2vw, 16px); color: var(--text-secondary); font-weight: 500; max-width: 600px; line-height: 1.7; }

    /* ========== MENU GRID (7 ITEMS) ========== */
    .menu-section { animation: fadeInUp 0.8s ease-out 0.2s both; }
    .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
    .section-title { font-size: 20px; font-weight: 800; color: white; display: flex; align-items: center; gap: 12px; letter-spacing: -0.5px; }
    .section-title i { color: #fbbf24; font-size: 18px; }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    .menu-card {
        position: relative;
        background: rgba(255, 255, 255, 0.04);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: var(--radius-lg);
        overflow: hidden;
        text-decoration: none;
        display: block;
        transition: var(--transition-bounce);
        cursor: pointer;
        opacity: 0;
        animation: cardEnter 0.6s ease-out forwards;
    }
    .menu-card:nth-child(1) { animation-delay: 0.25s; }
    .menu-card:nth-child(2) { animation-delay: 0.35s; }
    .menu-card:nth-child(3) { animation-delay: 0.45s; }
    .menu-card:nth-child(4) { animation-delay: 0.55s; }
    .menu-card:nth-child(5) { animation-delay: 0.65s; }
    .menu-card:nth-child(6) { animation-delay: 0.75s; }
    .menu-card:nth-child(7) { animation-delay: 0.85s; }

    @keyframes cardEnter {
        from { opacity: 0; transform: translateY(30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .menu-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        opacity: 0; transition: var(--transition); z-index: 2;
    }
    .menu-card:hover::before { opacity: 1; }
    .menu-card:hover {
        transform: translateY(-10px) scale(1.02);
        border-color: rgba(255, 255, 255, 0.15);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
    }

    .card-image {
        height: 180px; display: flex; align-items: center; justify-content: center;
        position: relative; overflow: hidden;
    }
    .card-image::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 100%;
        background: linear-gradient(to top, rgba(15, 23, 42, 0.9) 0%, transparent 60%);
        z-index: 1;
    }
    .card-bg-pattern {
        position: absolute; inset: 0; opacity: 0.3;
        background-image: 
            radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255,255,255,0.08) 0%, transparent 40%);
    }
    .card-bg-circle {
        position: absolute; width: 140px; height: 140px; border-radius: 50%;
        opacity: 0.6; transition: var(--transition-slow); filter: blur(40px);
    }
    .menu-card:hover .card-bg-circle { transform: scale(1.3) rotate(15deg); opacity: 0.8; }

    .card-icon-wrapper {
        position: relative; z-index: 2;
        width: 80px; height: 80px; border-radius: 26px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; color: white;
        box-shadow: 0 12px 40px rgba(0,0,0,0.3);
        transition: var(--transition-bounce); overflow: hidden;
    }
    .card-icon-wrapper::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.2) 100%); }
    .menu-card:hover .card-icon-wrapper { transform: scale(1.1) rotate(-5deg) translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.4); }
    .card-icon-wrapper i { position: relative; z-index: 1; }

    .card-content { padding: 24px; position: relative; z-index: 2; }
    .card-title { font-size: 17px; font-weight: 800; color: white; margin-bottom: 8px; letter-spacing: -0.3px; text-align: center; }
    .card-desc {
        font-size: 13px; color: var(--text-muted); line-height: 1.7;
        margin-bottom: 20px; text-align: center;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .card-footer { display: flex; align-items: center; justify-content: center; }
    .card-arrow {
        width: 44px; height: 44px; border-radius: 14px;
        background: rgba(255, 255, 255, 0.06);
        display: flex; align-items: center; justify-content: center;
        color: var(--text-secondary); font-size: 14px;
        transition: var(--transition-bounce);
        border: 1px solid rgba(255, 255, 255, 0.06);
    }
    .menu-card:hover .card-arrow { background: white; color: var(--dark); transform: rotate(-45deg); border-color: white; }

    /* Card Color Themes */
    .card-kartika1::before { background: linear-gradient(90deg, #06b6d4, #0891b2); }
    .card-kartika1 .card-bg-circle { background: linear-gradient(135deg, #06b6d4, #0891b2); }
    .card-kartika1 .card-icon-wrapper { background: linear-gradient(135deg, #22d3ee, #06b6d4); }

    .card-kartika2::before { background: linear-gradient(90deg, #8b5cf6, #7c3aed); }
    .card-kartika2 .card-bg-circle { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }
    .card-kartika2 .card-icon-wrapper { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }

    .card-kartika3::before { background: linear-gradient(90deg, #10b981, #059669); }
    .card-kartika3 .card-bg-circle { background: linear-gradient(135deg, #34d399, #10b981); }
    .card-kartika3 .card-icon-wrapper { background: linear-gradient(135deg, #34d399, #10b981); }

    .card-cvc::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .card-cvc .card-bg-circle { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
    .card-cvc .card-icon-wrapper { background: linear-gradient(135deg, #fbbf24, #f59e0b); }

    .card-cicu::before { background: linear-gradient(90deg, #f97316, #ea580c); }
    .card-cicu .card-bg-circle { background: linear-gradient(135deg, #fb923c, #f97316); }
    .card-cicu .card-icon-wrapper { background: linear-gradient(135deg, #fb923c, #f97316); }

    .card-gudang::before { background: linear-gradient(90deg, #475569, #334155); }
    .card-gudang .card-bg-circle { background: linear-gradient(135deg, #64748b, #475569); }
    .card-gudang .card-icon-wrapper { background: linear-gradient(135deg, #94a3b8, #64748b); }

    .card-disposal::before { background: linear-gradient(90deg, #e11d48, #be123c); }
    .card-disposal .card-bg-circle { background: linear-gradient(135deg, #fb7185, #e11d48); }
    .card-disposal .card-icon-wrapper { background: linear-gradient(135deg, #fb7185, #e11d48); }

    /* ========== FOOTER ========== */
    .footer { position: relative; z-index: 1; text-align: center; padding: 40px 24px; color: var(--text-muted); font-size: 13px; font-weight: 600; }
    .footer a { color: var(--text-secondary); text-decoration: none; transition: var(--transition-fast); }
    .footer a:hover { color: white; }

    /* ========== SCROLLBAR ========== */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }


    /* ========== LIGHT MODE ========== */
    body.light-mode {
        --primary: #4f46e5;
        --primary-dark: #4338ca;
        --dark: #f1f5f9;
        --dark-card: #ffffff;
        --dark-surface: #e2e8f0;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #64748b;
        background: #f8fafc;
        color: var(--text-primary);
    }

    body.light-mode .bg-mesh {
        background:
            radial-gradient(ellipse at 20% 20%, rgba(99, 102, 241, 0.12) 0%, transparent 50%),
            radial-gradient(ellipse at 80% 80%, rgba(236, 72, 153, 0.08) 0%, transparent 50%),
            radial-gradient(ellipse at 50% 50%, rgba(6, 182, 212, 0.06) 0%, transparent 60%),
            linear-gradient(180deg, #f8fafc 0%, #eef2ff 50%, #f8fafc 100%);
    }
    body.light-mode .bg-orb { opacity: 0.25; }
    body.light-mode .bg-grid {
        background-image:
            linear-gradient(rgba(15,23,42,0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15,23,42,0.04) 1px, transparent 1px);
    }

    body.light-mode .loading-screen { background: #f8fafc; }
    body.light-mode .loading-brand-text { color: #0f172a; }
    body.light-mode .loading-brand-text span { color: var(--text-secondary); }
    body.light-mode .loading-progress { background: rgba(15,23,42,0.1); }

    body.light-mode .header.scrolled {
        background: rgba(255, 255, 255, 0.85);
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }
    body.light-mode .menu-btn,
    body.light-mode .header-btn {
        background: rgba(15, 23, 42, 0.04);
        border: 1px solid rgba(15, 23, 42, 0.08);
        color: #0f172a;
    }
    body.light-mode .menu-btn:hover,
    body.light-mode .header-btn:hover {
        background: rgba(15, 23, 42, 0.08);
        border-color: rgba(15, 23, 42, 0.15);
    }
    body.light-mode .header-btn .badge { border-color: #fff; }
    body.light-mode .logo-text { color: #0f172a; }
    body.light-mode .logo-text span { color: var(--text-secondary); }

    body.light-mode .breadcrumb a { color: var(--text-muted); }
    body.light-mode .breadcrumb a:hover { color: #0f172a; }
    body.light-mode .breadcrumb-sep { color: var(--text-muted); }
    body.light-mode .breadcrumb-current { color: #0f172a; }

    body.light-mode .sidebar {
        background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
        border-right: 1px solid rgba(15, 23, 42, 0.06);
    }
    body.light-mode .sidebar-header {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.05));
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }
    body.light-mode .sidebar-close { background: rgba(15,23,42,0.06); color: #0f172a; }
    body.light-mode .sidebar-close:hover { background: rgba(15,23,42,0.1); }
    body.light-mode .sidebar-user-info h3 { color: #0f172a; }
    body.light-mode .sidebar-user-info p { color: var(--text-secondary); }
    body.light-mode .sidebar-avatar::after { border-color: #fff; }
    body.light-mode .sidebar-item { color: var(--text-secondary); }
    body.light-mode .sidebar-item:hover { background: rgba(99, 102, 241, 0.08); color: #0f172a; }
    body.light-mode .sidebar-item.active { background: rgba(99, 102, 241, 0.12); color: var(--primary); }
    body.light-mode .sidebar-footer { border-top: 1px solid rgba(15,23,42,0.06); }

    body.light-mode .page-badge {
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(15, 23, 42, 0.08);
        color: var(--text-secondary);
    }
    body.light-mode .page-title { color: #0f172a; }
    body.light-mode .page-subtitle { color: var(--text-secondary); }
    body.light-mode .section-title { color: #0f172a; }

    body.light-mode .menu-card {
        background: rgba(255, 255, 255, 0.75);
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
    }
    body.light-mode .menu-card:hover {
        border-color: rgba(15, 23, 42, 0.12);
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15);
    }
    body.light-mode .card-image::after {
        background: linear-gradient(to top, rgba(248, 250, 252, 0.95) 0%, transparent 60%);
    }
    body.light-mode .card-title { color: #0f172a; }
    body.light-mode .card-desc { color: var(--text-muted); }
    body.light-mode .card-arrow {
        background: rgba(15, 23, 42, 0.05);
        color: var(--text-secondary);
        border: 1px solid rgba(15, 23, 42, 0.06);
    }
    body.light-mode .menu-card:hover .card-arrow { background: #0f172a; color: #fff; border-color: #0f172a; }

    body.light-mode .footer { color: var(--text-muted); }
    body.light-mode .footer a { color: var(--text-secondary); }
    body.light-mode .footer a:hover { color: #0f172a; }

    body.light-mode ::-webkit-scrollbar-thumb { background: rgba(15,23,42,0.15); }
    body.light-mode ::-webkit-scrollbar-thumb:hover { background: rgba(15,23,42,0.25); }

    body.light-mode #toast {
        background: rgba(255, 255, 255, 0.95);
        color: #0f172a;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 20px 40px rgba(15,23,42,0.15);
    }

    #themeIcon, #sidebarThemeIcon { transition: transform 0.4s ease; }
    body.light-mode #themeIcon, body.light-mode #sidebarThemeIcon { transform: rotate(360deg); }


    /* ========== SCAN QR BARCODE ========== */
    .scan-section { margin-bottom: 40px; animation: fadeInUp 0.8s ease-out 0.1s both; }
    .scan-card {
        display: flex; align-items: center; gap: 22px;
        background: rgba(255, 255, 255, 0.04);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: var(--radius-lg);
        padding: 26px 28px;
        position: relative; overflow: hidden;
        transition: var(--transition);
    }
    .scan-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, #06b6d4, #8b5cf6, #f59e0b);
    }
    .scan-card:hover { border-color: rgba(255, 255, 255, 0.15); box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
    .scan-card-icon {
        width: 64px; height: 64px; min-width: 64px; border-radius: 20px;
        background: linear-gradient(135deg, #06b6d4, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; color: white;
        box-shadow: 0 10px 30px rgba(6, 182, 212, 0.35);
        animation: scan-pulse 2.5s ease-in-out infinite;
    }
    @keyframes scan-pulse {
        0%, 100% { box-shadow: 0 10px 30px rgba(6, 182, 212, 0.35); }
        50% { box-shadow: 0 10px 40px rgba(139, 92, 246, 0.5); }
    }
    .scan-card-body { flex: 1; }
    .scan-card-title { font-size: 17px; font-weight: 800; color: white; margin-bottom: 6px; letter-spacing: -0.3px; }
    .scan-card-desc { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; line-height: 1.7; }
    .scan-card-desc strong { color: var(--text-secondary); }
    .scan-input-row { display: flex; gap: 12px; }
    .scan-input {
        flex: 1; padding: 14px 18px; border-radius: 14px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: white; font-size: 15px; font-weight: 600;
        font-family: 'Plus Jakarta Sans', sans-serif;
        outline: none; transition: var(--transition-fast);
        letter-spacing: 0.5px;
    }
    .scan-input::placeholder { color: var(--text-muted); font-weight: 500; }
    .scan-input:focus { border-color: #06b6d4; box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.15); background: rgba(6, 182, 212, 0.06); }
    .scan-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 14px 24px; border-radius: 14px; border: none;
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        color: white; font-size: 14px; font-weight: 700;
        font-family: 'Plus Jakarta Sans', sans-serif;
        cursor: pointer; transition: var(--transition-fast);
        text-decoration: none; white-space: nowrap;
    }
    .scan-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); }
    .scan-btn:active { transform: translateY(0); }
    .scan-btn.ghost { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); }
    .scan-btn.ghost:hover { background: rgba(255,255,255,0.14); box-shadow: none; }

    /* Modal hasil scan */
    .scan-modal-overlay {
        position: fixed; inset: 0; z-index: 5000;
        background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(12px);
        display: flex; align-items: center; justify-content: center;
        padding: 20px; opacity: 0; visibility: hidden; transition: var(--transition);
    }
    .scan-modal-overlay.active { opacity: 1; visibility: visible; }
    .scan-modal {
        width: 100%; max-width: 720px; max-height: 88vh; overflow-y: auto;
        background: linear-gradient(180deg, var(--dark-card) 0%, var(--dark) 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-lg);
        transform: translateY(30px) scale(0.96); transition: var(--transition-bounce);
        box-shadow: var(--shadow-xl);
    }
    .scan-modal-overlay.active .scan-modal { transform: translateY(0) scale(1); }
    .scan-modal-header {
        padding: 22px 26px; border-bottom: 1px solid rgba(255,255,255,0.08);
        display: flex; align-items: center; justify-content: space-between;
    }
    .scan-modal-header::before { content:''; display:block; }
    .scan-modal-title { font-size: 17px; font-weight: 800; color: white; display: flex; align-items: center; gap: 12px; }
    .scan-modal-title i { color: #06b6d4; font-size: 20px; }
    .scan-modal-close {
        width: 36px; height: 36px; border-radius: 12px; border: none;
        background: rgba(255,255,255,0.08); color: var(--text-secondary);
        cursor: pointer; font-size: 15px; display: flex; align-items: center; justify-content: center;
        transition: var(--transition-fast);
    }
    .scan-modal-close:hover { background: rgba(239,68,68,0.2); color: var(--danger); transform: rotate(90deg); }
    .scan-modal-body { padding: 26px; }
    .scan-modal-footer {
        padding: 18px 26px; border-top: 1px solid rgba(255,255,255,0.08);
        display: flex; justify-content: flex-end; gap: 12px;
    }
    .scan-result-head {
        display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
        padding: 16px 18px; border-radius: 16px; margin-bottom: 20px;
        background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .scan-result-head i { font-size: 24px; color: var(--success); }
    .scan-result-head .t1 { font-size: 16px; font-weight: 800; color: white; }
    .scan-result-head .t2 { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .unit-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 14px; border-radius: 50px;
        font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
        color: white; margin-left: auto;
    }
    .scan-detail-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .scan-detail-item {
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);
        border-radius: 14px; padding: 12px 14px;
    }
    .scan-detail-item .lbl {
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;
        color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-bottom: 4px;
    }
    .scan-detail-item .lbl i { color: #06b6d4; font-size: 11px; }
    .scan-detail-item .val { font-size: 14px; font-weight: 700; color: white; word-break: break-word; }
    .scan-qr-box {
        margin-top: 20px; text-align: center; padding: 20px;
        background: white; border-radius: 16px; display: inline-block; width: 100%;
    }
    .scan-qr-box img, .scan-qr-box canvas, .scan-qr-box table { margin: 0 auto; }
    .scan-qr-title { font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 12px; display:flex; align-items:center; justify-content:center; gap:8px; }
    .scan-qr-title i { color:#4f46e5; }
    #scanQRCode { min-height: 230px; display:flex; align-items:center; justify-content:center; padding:10px; }
    #scanQRCode img, #scanQRCode canvas { width:280px !important; height:280px !important; max-width:100%; image-rendering:auto; }
    .qr-download-row { display:flex; justify-content:center; gap:10px; flex-wrap:wrap; margin-top:14px; }
    .qr-download-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:11px 16px; border:0; border-radius:12px; background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; font:700 12px 'Plus Jakarta Sans',sans-serif; cursor:pointer; text-decoration:none; }
    .qr-download-btn:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(79,70,229,.28); }
    .qr-code-value { margin-top:10px; font:600 11px 'Plus Jakarta Sans',sans-serif; color:#64748b; word-break:break-all; }
    .qr-source-title { margin: 0 auto 12px; max-width: 520px; padding: 10px 12px; border-radius: 10px; background:#f1f5f9; color:#475569; font:600 11px 'Plus Jakarta Sans',sans-serif; word-break:break-all; text-align:center; }
    .qr-source-title span { color:#64748b; }
    .qr-source-title strong { color:#0f172a; }
    body.light-mode .scan-qr-box { background:#fff; }
    body.light-mode .qr-source-title { background:#f1f5f9; color:#475569; }
    body.light-mode .qr-source-title strong { color:#0f172a; }


    body.light-mode .scan-card { background: rgba(255,255,255,0.8); border-color: rgba(15,23,42,0.08); box-shadow: 0 4px 20px rgba(15,23,42,0.06); }
    body.light-mode .scan-card-title { color: #0f172a; }
    body.light-mode .scan-card-desc { color: var(--text-muted); }
    body.light-mode .scan-card-desc strong { color: var(--text-secondary); }
    body.light-mode .scan-input { background: rgba(15,23,42,0.04); border-color: rgba(15,23,42,0.12); color: #0f172a; }
    body.light-mode .scan-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(99,102,241,0.12); background: rgba(99,102,241,0.05); }
    body.light-mode .scan-btn.ghost { background: rgba(15,23,42,0.05); border-color: rgba(15,23,42,0.1); color: #0f172a; }
    body.light-mode .scan-modal { background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); border-color: rgba(15,23,42,0.08); }
    body.light-mode .scan-modal-title { color: #0f172a; }
    body.light-mode .scan-modal-header, body.light-mode .scan-modal-footer { border-color: rgba(15,23,42,0.07); }
    body.light-mode .scan-modal-close { background: rgba(15,23,42,0.06); color: var(--text-secondary); }
    body.light-mode .scan-result-head { background: rgba(16,185,129,0.07); border-color: rgba(16,185,129,0.25); }
    body.light-mode .scan-result-head .t1 { color: #0f172a; }
    body.light-mode .scan-detail-item { background: rgba(15,23,42,0.03); border-color: rgba(15,23,42,0.07); }
    body.light-mode .scan-detail-item .val { color: #0f172a; }

    /* ========== CAMERA QR / BARCODE SCANNER ========== */
    .camera-btn {
        background: linear-gradient(135deg, #06b6d4, #8b5cf6);
        min-width: 190px;
        justify-content: center;
    }

    .camera-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 6000;
        background: rgba(2, 6, 23, 0.82);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        opacity: 0;
        visibility: hidden;
        transition: var(--transition);
    }

    .camera-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .camera-modal {
        width: 100%;
        max-width: 620px;
        max-height: 94vh;
        overflow-y: auto;
        background: linear-gradient(180deg, var(--dark-card) 0%, var(--dark) 100%);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: var(--radius-lg);
        box-shadow: 0 30px 80px rgba(0,0,0,0.55);
        transform: translateY(30px) scale(0.96);
        transition: var(--transition-bounce);
    }

    .camera-modal-overlay.active .camera-modal {
        transform: translateY(0) scale(1);
    }

    .camera-modal-header {
        padding: 18px 22px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .camera-modal-title {
        color: white;
        font-size: 16px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .camera-modal-title i { color: #06b6d4; font-size: 20px; }

    .camera-modal-body { padding: 18px; }

    .camera-status {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 11px 14px;
        border-radius: 12px;
        background: rgba(6,182,212,0.08);
        border: 1px solid rgba(6,182,212,0.18);
        color: var(--text-secondary);
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 14px;
    }

    .camera-status-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #06b6d4;
        box-shadow: 0 0 0 5px rgba(6,182,212,0.12);
        animation: camera-dot-pulse 1.5s ease-in-out infinite;
        flex: 0 0 auto;
    }

    @keyframes camera-dot-pulse {
        0%,100% { opacity: 1; transform: scale(1); }
        50% { opacity: .45; transform: scale(.8); }
    }

    .camera-reader-wrap {
        position: relative;
        width: 100%;
        max-width: 520px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 20px;
        background: #020617;
        border: 1px solid rgba(255,255,255,0.1);
        min-height: 300px;
    }

    #qrReader {
        width: 100%;
    }

    #qrReader video {
        width: 100% !important;
        height: auto !important;
        min-height: 300px;
        object-fit: cover;
        display: block;
        border-radius: 20px;
    }

    #qrReader__dashboard {
        padding: 10px !important;
        background: rgba(2,6,23,0.92) !important;
        color: white !important;
        border: 0 !important;
    }

    #qrReader__dashboard_section_csr,
    #qrReader__dashboard_section_swaplink {
        color: white !important;
        font-size: 12px !important;
    }

    #qrReader__scan_region {
        border: 0 !important;
        min-height: 300px;
    }

    #qrReader__scan_region img {
        display: none !important;
    }

    #qrReader__camera_permission_button,
    #qrReader__dashboard_section_csr button {
        border: 0 !important;
        border-radius: 10px !important;
        padding: 9px 14px !important;
        background: linear-gradient(135deg, #06b6d4, #8b5cf6) !important;
        color: #fff !important;
        font-weight: 700 !important;
        cursor: pointer !important;
    }

    .scan-frame {
        position: absolute;
        width: 68%;
        height: 48%;
        max-width: 360px;
        max-height: 210px;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
        border-radius: 18px;
        box-shadow: 0 0 0 9999px rgba(0,0,0,0.18);
    }

    .scan-frame .corner {
        position: absolute;
        width: 32px;
        height: 32px;
        border-color: #22d3ee;
        border-style: solid;
        filter: drop-shadow(0 0 6px rgba(34,211,238,0.8));
    }

    .scan-frame .tl { left: 0; top: 0; border-width: 4px 0 0 4px; border-radius: 12px 0 0 0; }
    .scan-frame .tr { right: 0; top: 0; border-width: 4px 4px 0 0; border-radius: 0 12px 0 0; }
    .scan-frame .bl { left: 0; bottom: 0; border-width: 0 0 4px 4px; border-radius: 0 0 0 12px; }
    .scan-frame .br { right: 0; bottom: 0; border-width: 0 4px 4px 0; border-radius: 0 0 12px 0; }

    .scan-line {
        position: absolute;
        left: 5%;
        right: 5%;
        top: 50%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #22d3ee, transparent);
        box-shadow: 0 0 12px rgba(34,211,238,0.8);
        animation: scanner-line 2s ease-in-out infinite;
    }

    @keyframes scanner-line {
        0%,100% { transform: translateY(-70px); opacity: .35; }
        50% { transform: translateY(70px); opacity: 1; }
    }

    .camera-help {
        display: flex;
        gap: 9px;
        align-items: flex-start;
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 12px;
        background: rgba(255,255,255,0.04);
        color: var(--text-muted);
        font-size: 11px;
        line-height: 1.6;
    }

    .camera-help i { color: #06b6d4; margin-top: 2px; }

    .camera-footer { justify-content: space-between; }

    body.light-mode .camera-modal {
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-color: rgba(15,23,42,0.08);
    }

    body.light-mode .camera-modal-header { border-color: rgba(15,23,42,0.07); }
    body.light-mode .camera-modal-title { color: #0f172a; }
    body.light-mode .camera-status {
        background: rgba(6,182,212,0.06);
        border-color: rgba(6,182,212,0.16);
    }
    body.light-mode .camera-help { background: rgba(15,23,42,0.04); }

    @media (max-width: 767px) {
        .camera-modal-overlay { padding: 8px; align-items: flex-end; }
        .camera-modal { max-height: 96vh; border-radius: 24px 24px 0 0; }
        .camera-modal-body { padding: 12px; }
        .camera-reader-wrap, #qrReader__scan_region, #qrReader video { min-height: 260px; }
        .scan-frame { width: 76%; height: 44%; }
        .scan-input-row .camera-btn { width: 100%; }
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 1399px) {
        .menu-grid { grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .card-image { height: 160px; }
        .card-icon-wrapper { width: 72px; height: 72px; font-size: 28px; border-radius: 22px; }
    }

    @media (max-width: 1199px) {
        .menu-grid { grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .card-image { height: 170px; }
        .card-icon-wrapper { width: 76px; height: 76px; font-size: 30px; }
    }

    @media (max-width: 991px) {
        .header-inner { padding: 14px 24px; }
        .container { padding: 0 24px; }
        .breadcrumb-bar { padding-top: 90px; }
        .menu-grid { grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .card-image { height: 150px; }
        .card-icon-wrapper { width: 68px; height: 68px; font-size: 26px; border-radius: 20px; }
        .card-content { padding: 20px; }
        .card-title { font-size: 15px; }
        .card-desc { font-size: 12px; }
        .page-title { font-size: 32px; }
    }

    @media (max-width: 767px) {
        .scan-card { flex-direction: column; align-items: flex-start; padding: 20px; }
        .scan-input-row { flex-direction: column; width: 100%; }
        .scan-btn { justify-content: center; }
        .scan-detail-grid { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 575px) {
        .scan-detail-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 767px) {
        .container { padding: 0 16px; }
        .header-inner { padding: 12px 16px; }
        .breadcrumb-inner { padding: 0 16px; }
        .breadcrumb-bar { padding-top: 80px; }
        .logo-text { font-size: 16px; }
        .menu-btn, .header-btn { width: 40px; height: 40px; border-radius: 12px; }
        .menu-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .card-image { height: 140px; }
        .card-bg-circle { width: 100px; height: 100px; }
        .card-icon-wrapper { width: 60px; height: 60px; font-size: 24px; border-radius: 18px; }
        .card-content { padding: 18px; }
        .card-title { font-size: 14px; margin-bottom: 4px; }
        .card-desc { font-size: 11px; margin-bottom: 14px; }
        .card-arrow { width: 36px; height: 36px; border-radius: 12px; }
        .page-title { font-size: 26px; }
        .sidebar { width: 300px; }
    }

    @media (max-width: 575px) {
        .breadcrumb-bar { padding-top: 75px; padding-bottom: 12px; }
        .page-header { margin-bottom: 28px; }
        .page-badge { font-size: 11px; padding: 6px 14px; }
        .page-title { font-size: 24px; letter-spacing: -1px; }
        .page-subtitle { font-size: 13px; }
        .section-title { font-size: 16px; }
        .menu-grid { grid-template-columns: 1fr; gap: 12px; }
        .menu-card {
            display: flex; align-items: center; padding: 14px;
        }
        .card-image {
            width: 64px; height: 64px; min-width: 64px;
            margin-right: 14px; border-radius: 16px;
        }
        .card-bg-circle { width: 50px; height: 50px; filter: blur(20px); }
        .card-icon-wrapper {
            width: 40px; height: 40px; border-radius: 12px; font-size: 18px;
        }
        .card-content { padding: 0; flex: 1; text-align: left; }
        .card-title { font-size: 14px; text-align: left; margin-bottom: 4px; }
        .card-desc { text-align: left; margin-bottom: 10px; -webkit-line-clamp: 1; }
        .card-footer { justify-content: flex-start; }
        .card-arrow { width: 32px; height: 32px; border-radius: 10px; }
        .footer { padding: 24px 16px; font-size: 11px; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; }
    }

    [data-tooltip] { position: relative; }
    [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); padding: 6px 12px;
        background: var(--dark-card); color: white; font-size: 12px; font-weight: 600;
        border-radius: 8px; white-space: nowrap; opacity: 0; visibility: hidden;
        transition: var(--transition-fast); border: 1px solid rgba(255,255,255,0.1);
        pointer-events: none;
    }
    [data-tooltip]:hover::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
</style>



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
                <h3><?php echo htmlspecialchars($user['nama']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <span class="role-badge"><i class="fas fa-shield-halved" style="font-size:8px"></i> <?php echo htmlspecialchars($user['role']); ?></span>
            </div>
        </div>
    </div>
    <nav class="sidebar-menu">
        <div class="sidebar-section">
            <div class="sidebar-section-title">Menu Utama</div>
            <a href="index.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-house"></i> Beranda</a>
            <a href="inventaris.php" class="sidebar-item active" onclick="toggleSidebar()"><i class="fas fa-warehouse"></i> Data Inventaris</a>
            <a href="kegiatan.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-list-check"></i> Data Kegiatan</a>
            <a href="jaringan.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-diagram-project"></i> Data Jaringan</a>
            <a href="laporan.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-chart-line"></i> Data Laporan</a>
        </div>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Pengaturan</div>
            <a href="#" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-user-gear"></i> Profil</a>
            <a href="#" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-sliders"></i> Pengaturan</a>
            <a href="#" class="sidebar-item" onclick="event.preventDefault(); toggleTheme()"><i class="fas fa-moon" id="sidebarThemeIcon"></i> <span id="sidebarThemeText">Mode Terang</span></a>
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
            <button class="header-btn" id="themeToggle" data-tooltip="Mode Terang / Gelap" onclick="toggleTheme()"><i class="fas fa-moon" id="themeIcon"></i></button>
            <button class="header-btn" data-tooltip="Cari" onclick="showToast('Fitur pencarian akan segera hadir!')"><i class="fas fa-magnifying-glass"></i></button>
            <button class="header-btn" data-tooltip="Refresh" onclick="location.reload()"><i class="fas fa-rotate"></i></button>
            <button class="header-btn" data-tooltip="Notifikasi" onclick="showToast('Tidak ada notifikasi baru')"><i class="fas fa-bell"></i><span class="badge"></span></button>
        </div>
    </div>
</header>

<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <div class="breadcrumb-inner">
        <div class="breadcrumb">
            <a href="index.php"><i class="fas fa-house"></i> Beranda</a>
            <i class="fas fa-chevron-right breadcrumb-sep"></i>
            <span class="breadcrumb-current">Data Inventaris</span>
        </div>
    </div>
</div>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <section class="page-header">
            <div class="page-badge"><i class="fas fa-warehouse"></i> Modul Inventaris</div>
            <h1 class="page-title">Pilih <span class="gradient-text">Unit / Ruangan</span></h1>
            <p class="page-subtitle">Silakan pilih unit, ruangan, gudang, atau disposal untuk mengelola data inventaris masing-masing area.</p>
        </section>

        <!-- SCANNER KAMERA QR / BARCODE -->
        <section class="scan-section">
            <div class="scan-card">
                <div class="scan-card-icon"><i class="fas fa-camera"></i></div>
                <div class="scan-card-body">
                    <h3 class="scan-card-title">Scanner Kamera &mdash; Cari Inventaris</h3>
                    <p class="scan-card-desc">
                        Tekan <strong>Scan dengan Kamera</strong>, arahkan kamera HP ke QR/Barcode inventaris.
                        Setelah kode terbaca, sistem akan <strong>otomatis mencari data di semua unit</strong>
                        (Kartika 1, Kartika 2, Kartika 3, CVC, CICU).
                    </p>
                    <div class="scan-input-row">
                        <button type="button" class="scan-btn camera-btn" onclick="openCameraScanner()">
                            <i class="fas fa-camera"></i> Scan dengan Kamera
                        </button>
                        <input type="text" id="scanInput" class="scan-input" placeholder="Atau masukkan kode manual..." autocomplete="off">
                        <button type="button" class="scan-btn" onclick="manualScan()"><i class="fas fa-magnifying-glass"></i> Cari</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal Scanner Kamera -->
        <div class="camera-modal-overlay" id="cameraModal" onclick="if(event.target===this) closeCameraScanner()">
            <div class="camera-modal">
                <div class="camera-modal-header">
                    <div class="camera-modal-title">
                        <i class="fas fa-camera"></i>
                        <span>Scanner Kamera</span>
                    </div>
                    <button type="button" class="scan-modal-close" onclick="closeCameraScanner()" aria-label="Tutup">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <div class="camera-modal-body">
                    <div class="camera-status" id="cameraStatus">
                        <span class="camera-status-dot"></span>
                        <span>Arahkan kamera ke QR / Barcode inventaris</span>
                    </div>

                    <div class="camera-reader-wrap">
                        <div id="qrReader"></div>
                        <div class="scan-frame">
                            <span class="corner tl"></span>
                            <span class="corner tr"></span>
                            <span class="corner bl"></span>
                            <span class="corner br"></span>
                            <span class="scan-line"></span>
                        </div>
                    </div>

                    <div class="camera-help">
                        <i class="fas fa-circle-info"></i>
                        <span>Gunakan kamera belakang HP. Pastikan QR/Barcode berada di dalam kotak dan pencahayaan cukup.</span>
                    </div>
                </div>

                <div class="scan-modal-footer camera-footer">
                    <button type="button" class="scan-btn ghost" onclick="closeCameraScanner()">
                        <i class="fas fa-stop"></i> Tutup Scanner
                    </button>
                </div>
            </div>
        </div>

        <section class="menu-section">
            <div class="section-header">
                <!--<h2 class="section-title"><i class="fas fa-grid-2"></i> Daftar Unit / Ruangan</h2>-->
            </div>

            <div class="menu-grid">
                <?php foreach ($units as $unit): ?>
                <a href="<?php echo $unit['url']; ?>" class="menu-card card-<?php echo $unit['id']; ?>">
                    <div class="card-image">
                        <div class="card-bg-pattern"></div>
                        <div class="card-bg-circle"></div>
                        <div class="card-icon-wrapper">
                            <i class="fas <?php echo $unit['icon']; ?>"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title"><?php echo $unit['title']; ?></h3>
                        <p class="card-desc"><?php echo $unit['desc']; ?></p>
                        <div class="card-footer">
                            <div class="card-arrow"><i class="fas fa-arrow-right"></i></div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<!-- Modal Hasil Scan QR -->
<div class="scan-modal-overlay" id="scanModal" onclick="if(event.target===this) closeScanModal()">
    <div class="scan-modal">
        <div class="scan-modal-header">
            <div class="scan-modal-title"><i class="fas fa-qrcode"></i> Hasil Scan QR Barcode</div>
            <button class="scan-modal-close" onclick="closeScanModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="scan-modal-body" id="scanModalBody"></div>
        <div class="scan-modal-footer">
            <button class="scan-btn ghost" onclick="closeScanModal()"><i class="fas fa-xmark"></i> Tutup</button>
            <a href="#" id="scanGoToUnit" class="scan-btn" style="display:none;"><i class="fas fa-arrow-up-right-from-square"></i> Buka Halaman Unit</a>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <p>&copy; <?php echo date('Y'); ?> Inventaris Digital. Dibangun dengan <i class="fas fa-heart" style="color:#f43f5e"></i> untuk Indonesia.</p>
</footer>

<!-- Toast Notification -->
<div id="toast" style="position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px); background: rgba(30, 41, 59, 0.95); backdrop-filter: blur(20px); color: white; padding: 14px 28px; border-radius: 16px; font-size: 14px; font-weight: 600; z-index: 9998; opacity: 0; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 40px rgba(0,0,0,0.3); pointer-events: none; white-space: nowrap;">
    <i class="fas fa-circle-info" style="margin-right:8px;color:var(--accent)"></i>
    <span id="toastMessage"></span>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" type="text/javascript"></script>  
<script>  
    // Loading Screen  
    window.addEventListener('load', function() {  
        setTimeout(() => {  
            var ls = document.getElementById('loadingScreen');  
            if (ls) ls.classList.add('hidden');  
        }, 1800);  
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
            const sidebar = document.getElementById('sidebar');  
            if (sidebar.classList.contains('active')) toggleSidebar();  
        }  
    });  
  
    // Header Scroll  
    window.addEventListener('scroll', function() {  
        const header = document.getElementById('header');  
        if (window.scrollY > 30) header.classList.add('scrolled');  
        else header.classList.remove('scrolled');  
    });  
  
    // Toast  
    function showToast(message) {  
        const toast = document.getElementById('toast');  
        const toastMessage = document.getElementById('toastMessage');  
        toastMessage.textContent = message;  
        toast.style.opacity = '1';  
        toast.style.transform = 'translateX(-50%) translateY(0)';  
        setTimeout(() => {  
            toast.style.opacity = '0';  
            toast.style.transform = 'translateX(-50%) translateY(100px)';  
        }, 3000);  
    }  
  
    // 3D Tilt Effect (Desktop)  
    if (window.matchMedia('(hover: hover)').matches) {  
        document.querySelectorAll('.menu-card').forEach(card => {  
            card.addEventListener('mousemove', function(e) {  
                const rect = this.getBoundingClientRect();  
                const x = e.clientX - rect.left;  
                const y = e.clientY - rect.top;  
                const centerX = rect.width / 2;  
                const centerY = rect.height / 2;  
                const rotateX = (y - centerY) / 20;  
                const rotateY = (centerX - x) / 20;  
                this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px) scale(1.02)`;  
            });  
            card.addEventListener('mouseleave', function() {  
                this.style.transform = '';  
            });  
        });  
    }  
  
    // Touch Feedback  
    document.querySelectorAll('.menu-card, .header-btn, .menu-btn').forEach(el => {  
        el.addEventListener('touchstart', function() { this.style.transform += ' scale(0.97)'; }, { passive: true });  
        el.addEventListener('touchend', function() { this.style.transform = this.style.transform.replace(' scale(0.97)', ''); }, { passive: true });  
    });  
  
    // Parallax Background  
    let ticking = false;  
    window.addEventListener('scroll', function() {  
        if (!ticking) {  
            requestAnimationFrame(function() {  
                const scrolled = window.scrollY;  
                document.querySelectorAll('.bg-orb').forEach((orb, index) => {  
                    const speed = 0.1 + (index * 0.05);  
                    orb.style.transform = `translateY(${scrolled * speed}px)`;  
                });  
                ticking = false;  
            });  
            ticking = true;  
        }  
    });  
  
    // ===== SCANNER KAMERA QR / BARCODE - CARI DI SEMUA INVENTARIS =====  
    var allInventaris = <?php echo json_encode($all_inventaris, JSON_UNESCAPED_UNICODE); ?>;  
    var html5QrScanner = null;  
    var cameraScannerRunning = false;  
    var scanLocked = false;  
  
    function escHtml(s) {  
        return String(s == null ? '' : s)  
            .replace(/&/g,'&amp;')  
            .replace(/</g,'&lt;')  
            .replace(/>/g,'&gt;')  
            .replace(/"/g,'&quot;')  
            .replace(/'/g,'&#039;');  
    }  
  
    // Parse hasil scan: "NamaPC|NamaUser"  
    // =========================================================
    // SISTEM IDENTITAS QR / BARCODE INVENTARIS
    // Format kode utama:
    //     Nama PC|Nama User
    //
    // QR yang ditampilkan setelah scan SELALU dibuat dari
    // nilai nama_pc + "|" + nama_user yang berasal dari database.
    // =========================================================

    function normalizeScanText(value) {
        return String(value == null ? '' : value)
            .replace(/\r?\n/g, ' ')
            .replace(/\t/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function normalizeCompare(value) {
        return normalizeScanText(value).toLowerCase();
    }

    function parseScanValue(val) {
        val = normalizeScanText(val);
        if (!val) return null;

        // Format utama: NamaPC|NamaUser
        var idx = val.indexOf('|');

        if (idx !== -1) {
            return {
                nama_pc: normalizeScanText(val.substring(0, idx)),
                nama_user: normalizeScanText(val.substring(idx + 1)),
                raw: val
            };
        }

        // Jika barcode hanya berisi satu entitas:
        // akan dicari ke nama_pc DAN nama_user.
        return {
            nama_pc: '',
            nama_user: '',
            raw: val
        };
    }

    function getInventarisQrValue(data) {
        var pc   = normalizeScanText(data && data.nama_pc);
        var user = normalizeScanText(data && data.nama_user);

        // Jangan membuat QR kosong atau hanya "|".
        if (!pc && !user) return '';

        // Format ini harus konsisten dengan pencarian scanner.
        if (pc && user) return pc + '|' + user;
        if (pc) return pc;
        return user;
    }

    // Cari di SEMUA tabel inventaris yang dikirim dari PHP.
    // Prioritas:
    // 1. exact Nama PC + Nama User
    // 2. exact Nama PC
    // 3. exact Nama User
    // 4. pencarian sebagian.
    function findInventaris(val) {
        var parsed = parseScanValue(val);
        if (!parsed) return null;

        var pc  = normalizeCompare(parsed.nama_pc);
        var usr = normalizeCompare(parsed.nama_user);
        var raw = normalizeCompare(parsed.raw);

        // 1. Exact pasangan Nama PC + Nama User
        if (pc && usr) {
            var exactPair = allInventaris.find(function(it) {
                return normalizeCompare(it.nama_pc) === pc &&
                       normalizeCompare(it.nama_user) === usr;
            });

            if (exactPair) return exactPair;
        }

        // 2. Exact Nama PC
        if (raw) {
            var exactPc = allInventaris.find(function(it) {
                return normalizeCompare(it.nama_pc) === raw;
            });

            if (exactPc) return exactPc;

            // 3. Exact Nama User
            var exactUser = allInventaris.find(function(it) {
                return normalizeCompare(it.nama_user) === raw;
            });

            if (exactUser) return exactUser;
        }

        // 4. Pencarian sebagian
        var loose = allInventaris.filter(function(it) {
            var itemPc  = normalizeCompare(it.nama_pc);
            var itemUsr = normalizeCompare(it.nama_user);

            if (pc && usr) {
                return itemPc.indexOf(pc) !== -1 &&
                       itemUsr.indexOf(usr) !== -1;
            }

            return (raw && (
                itemPc.indexOf(raw) !== -1 ||
                itemUsr.indexOf(raw) !== -1
            ));
        });

        return loose.length ? loose[0] : null;
    }

    function getDisplayFields(data) {
        var hiddenKeys = {
            id: true,
            _unit: true,
            _unit_url: true,
            _unit_color: true
        };

        var preferredOrder = [
            'gedung',
            'lantai',
            'ruangan',
            'nama_pc',
            'nama_user',
            'manufactur',
            'processor_type',
            'windows_version',
            'ram_size',
            'ram_hardisk',
            'monitor_model',
            'monitor_size',
            'monitor_vga',
            'printer_1',
            'printer_2',
            'tahun_pengadaan',
            'id_personil_edp'
        ];

        var labels = {
            gedung: 'Gedung',
            lantai: 'Lantai',
            ruangan: 'Ruangan',
            nama_pc: 'Nama PC',
            nama_user: 'Nama User',
            manufactur: 'Manufactur',
            processor_type: 'Processor',
            windows_version: 'Windows',
            ram_size: 'RAM',
            ram_hardisk: 'Hardisk',
            monitor_model: 'Monitor',
            monitor_size: 'Size Monitor',
            monitor_vga: 'VGA',
            printer_1: 'Printer 1',
            printer_2: 'Printer 2',
            tahun_pengadaan: 'Tahun Pengadaan',
            id_personil_edp: 'ID Personil EDP'
        };

        var icons = {
            gedung: 'fa-building',
            lantai: 'fa-layer-group',
            ruangan: 'fa-door-open',
            nama_pc: 'fa-desktop',
            nama_user: 'fa-user',
            manufactur: 'fa-industry',
            processor_type: 'fa-microchip',
            windows_version: 'fa-windows',
            ram_size: 'fa-memory',
            ram_hardisk: 'fa-hard-drive',
            monitor_model: 'fa-display',
            monitor_size: 'fa-ruler',
            monitor_vga: 'fa-plug',
            printer_1: 'fa-print',
            printer_2: 'fa-print',
            tahun_pengadaan: 'fa-calendar-days',
            id_personil_edp: 'fa-id-badge'
        };

        var fields = [];
        var used = {};

        // Kolom utama ditampilkan lebih dahulu.
        preferredOrder.forEach(function(key) {
            if (
                Object.prototype.hasOwnProperty.call(data, key) &&
                !hiddenKeys[key]
            ) {
                fields.push([
                    labels[key] || key,
                    icons[key] || 'fa-database',
                    data[key]
                ]);
                used[key] = true;
            }
        });

        // Semua kolom database lain juga ditampilkan.
        Object.keys(data).forEach(function(key) {
            if (hiddenKeys[key] || used[key]) return;

            var label = key
                .replace(/_/g, ' ')
                .replace(/\b\w/g, function(c) {
                    return c.toUpperCase();
                });

            fields.push([
                label,
                icons[key] || 'fa-database',
                data[key]
            ]);
        });

        return fields;
    }

    function renderScanResult(data) {
        window.lastScannedInventaris = data;

        var fields = getDisplayFields(data);
        var qrValue = getInventarisQrValue(data);

        var html =
            '<div class="scan-result-head">' +
                '<i class="fas fa-circle-check"></i>' +
                '<div>' +
                    '<div class="t1">' +
                        escHtml(data.nama_pc || '-') +
                        ' &mdash; ' +
                        escHtml(data.nama_user || '-') +
                    '</div>' +
                    '<div class="t2">Data ditemukan otomatis dari database inventaris</div>' +
                '</div>' +
                '<span class="unit-badge" style="background:' +
                    escHtml(data._unit_color || '#475569') +
                    ';">' +
                    '<i class="fas fa-warehouse" style="font-size:9px;"></i> ' +
                    escHtml(data._unit || '-') +
                '</span>' +
            '</div>' +
            '<div class="scan-detail-grid">';

        fields.forEach(function(f) {
            html +=
                '<div class="scan-detail-item">' +
                    '<div class="lbl">' +
                        '<i class="fas ' + escHtml(f[1]) + '"></i> ' +
                        escHtml(f[0]) +
                    '</div>' +
                    '<div class="val">' +
                        escHtml(f[2] == null || f[2] === '' ? '-' : f[2]) +
                    '</div>' +
                '</div>';
        });

        html +=
            '</div>' +
            '<div class="scan-qr-box">' +
                '<div class="scan-qr-title">' +
                    '<i class="fas fa-qrcode"></i> QR / Barcode Inventaris' +
                '</div>' +
                '<div class="qr-source-title">' +
                    '<span>Identitas QR:</span> ' +
                    '<strong>' + escHtml(qrValue || '-') + '</strong>' +
                '</div>' +
                '<div id="scanQRCode"></div>' +
                '<div class="qr-download-row">' +
                    '<button type="button" class="qr-download-btn" onclick="downloadScanQRCode()">' +
                        '<i class="fas fa-download"></i> Download QR' +
                    '</button>' +
                    '<button type="button" class="qr-download-btn" ' +
                        'onclick="printScanQRCode()" ' +
                        'style="background:linear-gradient(135deg,#0f766e,#059669);">' +
                        '<i class="fas fa-print"></i> Cetak' +
                    '</button>' +
                '</div>' +
            '</div>';

        document.getElementById('scanModalBody').innerHTML = html;

        var goBtn = document.getElementById('scanGoToUnit');

        if (data._unit_url) {
            goBtn.href = data._unit_url;
            goBtn.style.display = 'inline-flex';
        } else {
            goBtn.style.display = 'none';
        }

        openScanModal();

        // Generate QR setelah #scanQRCode sudah masuk DOM.
        setTimeout(function() {
            generateScanQRCode(qrValue);
        }, 80);
    }

    function generateScanQRCode(qrValue) {
        var host = document.getElementById('scanQRCode');

        if (!host) return;

        host.innerHTML = '';

        if (!qrValue) {
            host.innerHTML =
                '<div style="color:#ef4444;font:700 12px Plus Jakarta Sans,sans-serif;">' +
                'Nama PC / Nama User kosong. QR tidak dapat dibuat.' +
                '</div>';
            return;
        }

        if (typeof QRCode === 'undefined') {
            host.innerHTML =
                '<div style="color:#ef4444;font:700 12px Plus Jakarta Sans,sans-serif;">' +
                'Library QR Code belum termuat. Periksa koneksi internet.' +
                '</div>';
            return;
        }

        try {
            new QRCode(host, {
                text: qrValue,
                width: 280,
                height: 280,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch (error) {
            console.error('QR generation error:', error);
            host.innerHTML =
                '<div style="color:#ef4444;font:700 12px Plus Jakarta Sans,sans-serif;">' +
                'QR gagal dibuat.' +
                '</div>';
        }
    }

    function getGeneratedQRCodeDataUrl() {
        var host = document.getElementById('scanQRCode');

        if (!host) return null;

        var canvas = host.querySelector('canvas');

        if (canvas) {
            try {
                return canvas.toDataURL('image/png');
            } catch (e) {
                console.warn('Canvas QR:', e);
            }
        }

        var img = host.querySelector('img');

        if (img && img.src) {
            return img.src;
        }

        return null;
    }

    function makeQrFileName(data) {
        data = data || window.lastScannedInventaris || {};

        var pc = normalizeScanText(data.nama_pc || 'PC');
        var user = normalizeScanText(data.nama_user || 'User');

        var name = pc + '_' + user;

        return name
            .replace(/[\\/:*?"<>|]+/g, '_')
            .replace(/\s+/g, '_')
            .substring(0, 100) || 'barcode-inventaris';
    }

    function downloadScanQRCode() {
        var dataUrl = getGeneratedQRCodeDataUrl();

        if (!dataUrl) {
            showToast('QR belum siap. Tunggu sebentar lalu coba lagi.');
            return;
        }

        var data = window.lastScannedInventaris || {};
        var fileName = makeQrFileName(data) + '.png';

        var a = document.createElement('a');
        a.href = dataUrl;
        a.download = fileName;
        a.style.display = 'none';

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        showToast('QR berhasil didownload: ' + fileName);
    }

    function printScanQRCode() {
        var dataUrl = getGeneratedQRCodeDataUrl();

        if (!dataUrl) {
            showToast('QR belum siap. Tunggu sebentar lalu coba lagi.');
            return;
        }

        var data = window.lastScannedInventaris || {};

        var w = window.open('', '_blank', 'width=600,height=760');

        if (!w) {
            showToast('Popup diblokir browser. Izinkan popup untuk mencetak.');
            return;
        }

        w.document.write(
            '<!doctype html>' +
            '<html>' +
            '<head>' +
                '<meta charset="UTF-8">' +
                '<title>QR Inventaris</title>' +
                '<style>' +
                    'body{font-family:Arial,sans-serif;text-align:center;padding:30px;color:#111}' +
                    'img{width:320px;height:320px;image-rendering:auto}' +
                    '.title{font-size:22px;font-weight:700;margin:15px 0 5px}' +
                    '.sub{font-size:14px;color:#555;margin-bottom:8px}' +
                    '.code{font-size:13px;color:#333;margin-bottom:20px;word-break:break-all}' +
                '</style>' +
            '</head>' +
            '<body>' +
                '<div class="title">' + escHtml(data.nama_pc || 'Inventaris') + '</div>' +
                '<div class="sub">' +
                    escHtml(data.nama_user || '') +
                    ' &bull; ' +
                    escHtml(data._unit || '') +
                '</div>' +
                '<div class="code">' +
                    escHtml(getInventarisQrValue(data)) +
                '</div>' +
                '<img src="' + dataUrl + '" alt="QR Inventaris">' +
                '<script>window.onload=function(){window.print();}<\/script>' +
            '</body>' +
            '</html>'
        );

        w.document.close();
    }

    function showNotFoundResult(val) {  
        var body = document.getElementById('scanModalBody');  
        body.innerHTML =  
            '<div class="scan-result-head" style="background:rgba(239,68,68,0.08);border-color:rgba(239,68,68,0.3);">'  
            + '<i class="fas fa-circle-xmark" style="color:var(--danger);"></i><div>'  
            + '<div class="t1">Data Tidak Ditemukan</div>'  
            + '<div class="t2">Kode "' + escHtml(val) + '" tidak cocok dengan Nama PC / Nama User di unit manapun. Total data terindeks: ' + allInventaris.length + '.</div></div></div>';  
  
        document.getElementById('scanGoToUnit').style.display = 'none';  
        openScanModal();  
    }  
  
    // Hasil scanner kamera masuk ke sini.  
    function handleCameraScan(decodedText) {  
        if (scanLocked) return;  
  
        var value = normalizeScanText(decodedText);  
        if (!value) return;  
  
        scanLocked = true;  
  
        // Hentikan kamera terlebih dahulu agar tidak membaca kode yang sama berulang-ulang.  
        stopCameraScanner().finally(function() {  
            var found = findInventaris(value);  
  
            if (found) {  
                renderScanResult(found);  
                showToast('QR terbaca. Data ditemukan di ' + (found._unit || 'inventaris'));  
            } else {  
                showToast('QR terbaca, tetapi data tidak ditemukan di semua unit');  
                showNotFoundResult(value);  
            }  
  
            setTimeout(function() {  
                scanLocked = false;  
            }, 500);  
        });  
    }  
  
    // ===== SCANNER KAMERA MOBILE - VERSI LEBIH STABIL ===== 
    async function openCameraScanner() { 
        var modal = document.getElementById('cameraModal'); 
        var status = document.getElementById('cameraStatus'); 
        var qrHost = document.getElementById('qrReader'); 
 
        if (!modal || !status || !qrHost) { 
            showToast('Komponen scanner tidak ditemukan.'); 
            return; 
        } 
 
        if (typeof Html5Qrcode === 'undefined') { 
            status.innerHTML = 
                '<span style="color:#fb7185;">●</span>' + 
                '<span>Library scanner belum termuat. Pastikan internet aktif.</span>'; 
            showToast('Library scanner belum termuat.'); 
            return; 
        } 
 
        // Kamera browser hanya dapat digunakan pada secure context: 
        // HTTPS atau localhost. HTTP dengan IP LAN pada HP biasanya diblokir browser. 
        if (!window.isSecureContext && 
            location.hostname !== 'localhost' && 
            location.hostname !== '127.0.0.1') { 
 
            modal.classList.add('active'); 
            document.body.style.overflow = 'hidden'; 
 
            status.innerHTML = 
                '<span style="color:#fb7185;">●</span>' + 
                '<span>Kamera HP membutuhkan HTTPS. Buka aplikasi menggunakan HTTPS.</span>'; 
 
            showToast('Kamera HP memerlukan HTTPS atau localhost.'); 
            return; 
        } 
 
        modal.classList.add('active'); 
        document.body.style.overflow = 'hidden'; 
        scanLocked = false; 
 
        status.innerHTML = 
            '<span class="camera-status-dot"></span>' + 
            '<span>Menyiapkan kamera belakang...</span>'; 
 
        // Pastikan scanner lama benar-benar dihentikan. 
        await stopCameraScanner(); 
 
        try { 
            qrHost.innerHTML = ''; 
 
            html5QrScanner = new Html5Qrcode('qrReader'); 
 
            var formats = [ 
                Html5QrcodeSupportedFormats.QR_CODE, 
                Html5QrcodeSupportedFormats.CODE_128, 
                Html5QrcodeSupportedFormats.CODE_39, 
                Html5QrcodeSupportedFormats.CODE_93, 
                Html5QrcodeSupportedFormats.EAN_13, 
                Html5QrcodeSupportedFormats.EAN_8, 
                Html5QrcodeSupportedFormats.UPC_A, 
                Html5QrcodeSupportedFormats.UPC_E 
            ]; 
 
            var config = { 
                fps: 10, 
                rememberLastUsedCamera: true, 
                disableFlip: false, 
                formatsToSupport: formats, 
                experimentalFeatures: { 
                    useBarCodeDetectorIfSupported: true 
                }, 
                qrbox: function(viewfinderWidth, viewfinderHeight) { 
                    var minEdge = Math.min(viewfinderWidth, viewfinderHeight); 
                    var width = Math.floor(minEdge * 0.72); 
                    var height = Math.floor(minEdge * 0.50); 
 
                    width = Math.max(180, Math.min(width, 360)); 
                    height = Math.max(120, Math.min(height, 240)); 
 
                    return { width: width, height: height }; 
                } 
            }; 
 
            /* 
             * Jangan menggunakan: 
             * { facingMode: { exact: 'environment' } } 
             * 
             * Pada sebagian HP/browser, constraint "exact" membuat kamera 
             * langsung gagal dibuka. Kita ambil daftar kamera terlebih dahulu, 
             * lalu pilih kamera belakang berdasarkan label. 
             */ 
            var cameras = await Html5Qrcode.getCameras(); 
 
            if (!cameras || !cameras.length) { 
                throw new Error( 
                    'Kamera tidak ditemukan. Pastikan izin kamera diberikan.' 
                ); 
            } 
 
            var backCamera = cameras.find(function(camera) { 
                var label = (camera.label || '').toLowerCase(); 
 
                return ( 
                    label.indexOf('back') !== -1 || 
                    label.indexOf('rear') !== -1 || 
                    label.indexOf('environment') !== -1 || 
                    label.indexOf('belakang') !== -1 || 
                    label.indexOf('world') !== -1 
                ); 
            }); 
 
            var selectedCamera = backCamera || cameras[cameras.length - 1]; 
 
            status.innerHTML = 
                '<span class="camera-status-dot"></span>' + 
                '<span>Membuka kamera ' + 
                escHtml(selectedCamera.label || 'belakang') + 
                '...</span>'; 
 
            await html5QrScanner.start( 
                selectedCamera.id, 
                config, 
                function(decodedText) { 
                    handleCameraScan(decodedText); 
                }, 
                function() { 
                    // Callback error per-frame sengaja diabaikan. 
                } 
            ); 
 
            cameraScannerRunning = true; 
 
            status.innerHTML = 
                '<span class="camera-status-dot"></span>' + 
                '<span>Kamera aktif. Arahkan QR / Barcode ke dalam kotak.</span>'; 
 
        } catch (error) { 
            console.error('Camera scanner error:', error); 
 
            cameraScannerRunning = false; 
 
            // Bersihkan instance agar tombol Scan dapat dicoba lagi. 
            try { 
                if (html5QrScanner) { 
                    await html5QrScanner.clear(); 
                } 
            } catch (clearError) { 
                console.warn('Scanner clear after error:', clearError); 
            } 
 
            html5QrScanner = null; 
            qrHost.innerHTML = ''; 
 
            var message = 
                'Kamera tidak dapat dibuka. ' + 
                'Pastikan izin kamera diberikan dan halaman menggunakan HTTPS.'; 
 
            var errText = String( 
                (error && (error.message || error.name)) || error || '' 
            ).toLowerCase(); 
 
            if ( 
                errText.indexOf('permission') !== -1 || 
                errText.indexOf('notallowed') !== -1 || 
                errText.indexOf('denied') !== -1 
            ) { 
                message = 
                    'Akses kamera ditolak. Izinkan kamera untuk browser ini, ' + 
                    'lalu tekan Scan dengan Kamera lagi.'; 
            } else if ( 
                errText.indexOf('notfound') !== -1 || 
                errText.indexOf('no camera') !== -1 
            ) { 
                message = 
                    'Kamera tidak ditemukan. Pastikan kamera HP tidak sedang ' + 
                    'digunakan aplikasi lain.'; 
            } else if ( 
                errText.indexOf('secure') !== -1 || 
                errText.indexOf('https') !== -1 
            ) { 
                message = 
                    'Kamera HP membutuhkan HTTPS. HTTP melalui alamat IP jaringan ' + 
                    'tidak dapat digunakan untuk kamera browser.'; 
            } 
 
            status.innerHTML = 
                '<span style="color:#fb7185;">●</span>' + 
                '<span>' + escHtml(message) + '</span>'; 
 
            showToast(message); 
        } 
    } 
 
    async function stopCameraScanner() {  
        if (!html5QrScanner) {  
            cameraScannerRunning = false;  
            return;  
        }  
  
        try {  
            if (cameraScannerRunning) {  
                await html5QrScanner.stop();  
            }  
        } catch (e) {  
            console.warn('Scanner stop:', e);  
        }  
  
        try {  
            await html5QrScanner.clear();  
        } catch (e) {  
            console.warn('Scanner clear:', e);  
        }  
  
        html5QrScanner = null;  
        var qrHost3 = document.getElementById('qrReader');  
        if (qrHost3) qrHost3.innerHTML = '';  
        cameraScannerRunning = false;  
    }  
  
    async function closeCameraScanner() {  
        await stopCameraScanner();  
  
        var modal = document.getElementById('cameraModal');  
        modal.classList.remove('active');  
  
        document.body.style.overflow = '';  
        scanLocked = false;  
    }  
  
    function manualScan() {  
        var input = document.getElementById('scanInput');  
        var val = normalizeScanText(input.value);  
  
        if (!val) {  
            showToast('Silakan scan dengan kamera atau masukkan kode terlebih dahulu');  
            return;  
        }  
  
        var found = findInventaris(val);  
  
        if (found) {  
            renderScanResult(found);  
        } else {  
            showToast('Data tidak ditemukan di semua inventaris: ' + val);  
            showNotFoundResult(val);  
        }  
  
        input.value = '';  
        input.focus();  
    }  
  
    function openScanModal() {  
        document.getElementById('scanModal').classList.add('active');  
        document.body.style.overflow = 'hidden';  
    }  
  
    function closeScanModal() {  
        document.getElementById('scanModal').classList.remove('active');  
        document.body.style.overflow = '';  
    }  
  
    // Enter tetap bisa dipakai jika user ingin mencari kode secara manual.  
    (function() {  
        var si = document.getElementById('scanInput');  
        if (!si) return;  
  
        si.addEventListener('keydown', function(e) {  
            if (e.key === 'Enter') {  
                e.preventDefault();  
                manualScan();  
            }  
        });  
    })();  
  
    document.addEventListener('keydown', function(e) {  
        if (e.key === 'Escape') {  
            closeCameraScanner();  
            closeScanModal();  
        }  
    });  
  
    // ========== THEME TOGGLE (DARK / LIGHT) ==========  
    function toggleTheme() {  
        const isLight = document.body.classList.toggle('light-mode');  
        localStorage.setItem('inventaris-theme', isLight ? 'light' : 'dark');  
        updateThemeUI(isLight);  
        showToast(isLight ? 'Mode terang aktif' : 'Mode gelap aktif');  
    }  
  
    function updateThemeUI(isLight) {  
        const icon = document.getElementById('themeIcon');  
        if (icon) icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';  
        const sIcon = document.getElementById('sidebarThemeIcon');  
        if (sIcon) sIcon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';  
        const sText = document.getElementById('sidebarThemeText');  
        if (sText) sText.textContent = isLight ? 'Mode Gelap' : 'Mode Terang';  
    }  
  
    (function initTheme() {  
        const saved = localStorage.getItem('inventaris-theme');  
        const isLight = saved === 'light';  
        if (isLight) document.body.classList.add('light-mode');  
        updateThemeUI(isLight);  
    })();  
</script>  
</body>  
</html> 
