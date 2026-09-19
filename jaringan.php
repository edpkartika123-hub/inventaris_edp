<?php
/**
 * Aplikasi Inventaris - Halaman Data Jaringan
 * Menu: Jaringan Kartika 1, Kartika 2, Kartika 3, CVC, CICU
 * Ultra Dynamic & Professional Responsive Design
 */
session_start();

$user = [
    'nama' => 'Administrator',
    'email' => 'admin@inventaris.id',
    'role' => 'Super Admin'
];

$units = [
    [
        'id' => 'jaringan_kartika1',
        'title' => 'Jaringan Kartika 1',
        'desc' => 'Monitoring dan kelola infrastruktur jaringan ruangan Kartika 1 secara real-time.',
        'icon' => 'fa-network-wired',
        'url' => 'jaringan_kartika1.php',
        'color' => 'cyan',
        'gradient' => ['#06b6d4', '#0891b2']
    ],
    [
        'id' => 'jaringan_kartika2',
        'title' => 'Jaringan Kartika 2',
        'desc' => 'Monitoring dan kelola infrastruktur jaringan ruangan Kartika 2 secara real-time.',
        'icon' => 'fa-wifi',
        'url' => 'jaringan_kartika2.php',
        'color' => 'violet',
        'gradient' => ['#8b5cf6', '#7c3aed']
    ],
    [
        'id' => 'jaringan_kartika3',
        'title' => 'Jaringan Kartika 3',
        'desc' => 'Monitoring dan kelola infrastruktur jaringan ruangan Kartika 3 secara real-time.',
        'icon' => 'fa-server',
        'url' => 'jaringan_kartika3.php',
        'color' => 'emerald',
        'gradient' => ['#10b981', '#059669']
    ],
    [
        'id' => 'jaringan_cvc',
        'title' => 'Jaringan CVC',
        'desc' => 'Monitoring dan kelola infrastruktur jaringan ruangan CVC secara real-time.',
        'icon' => 'fa-router',
        'url' => 'jaringan_cvc.php',
        'color' => 'amber',
        'gradient' => ['#f59e0b', '#d97706']
    ],
    [
        'id' => 'jaringan_cicu',
        'title' => 'Jaringan CICU',
        'desc' => 'Monitoring dan kelola infrastruktur jaringan ruangan CICU secara real-time.',
        'icon' => 'fa-ethernet',
        'url' => 'jaringan_cicu.php',
        'color' => 'rose',
        'gradient' => ['#e11d48', '#be123c']
    ],
    [
        'id' => 'ip_alat',
        'title' => 'IP Alat',
        'desc' => 'Kelola alamat IP perangkat, nama alat, lokasi, dan status koneksi jaringan.',
        'icon' => 'fa-network-wired',
        'url' => 'ip_alat.php',
        'color' => 'blue',
        'gradient' => ['#3b82f6', '#2563eb']
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Inventaris Digital - Data Jaringan</title>
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

        /* ========== MENU GRID (6 ITEMS) ========== */
        .menu-section { animation: fadeInUp 0.8s ease-out 0.2s both; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .section-title { font-size: 20px; font-weight: 800; color: white; display: flex; align-items: center; gap: 12px; letter-spacing: -0.5px; }
        .section-title i { color: #fbbf24; font-size: 18px; }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
            height: 200px; display: flex; align-items: center; justify-content: center;
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
            position: absolute; width: 160px; height: 160px; border-radius: 50%;
            opacity: 0.6; transition: var(--transition-slow); filter: blur(40px);
        }
        .menu-card:hover .card-bg-circle { transform: scale(1.3) rotate(15deg); opacity: 0.8; }

        .card-icon-wrapper {
            position: relative; z-index: 2;
            width: 88px; height: 88px; border-radius: 28px;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; color: white;
            box-shadow: 0 12px 40px rgba(0,0,0,0.3);
            transition: var(--transition-bounce); overflow: hidden;
        }
        .card-icon-wrapper::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.2) 100%); }
        .menu-card:hover .card-icon-wrapper { transform: scale(1.1) rotate(-5deg) translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.4); }
        .card-icon-wrapper i { position: relative; z-index: 1; }

        .card-content { padding: 24px; position: relative; z-index: 2; }
        .card-title { font-size: 18px; font-weight: 800; color: white; margin-bottom: 8px; letter-spacing: -0.3px; text-align: center; }
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
        .card-jaringan_kartika1::before { background: linear-gradient(90deg, #06b6d4, #0891b2); }
        .card-jaringan_kartika1 .card-bg-circle { background: linear-gradient(135deg, #22d3ee, #06b6d4); }
        .card-jaringan_kartika1 .card-icon-wrapper { background: linear-gradient(135deg, #22d3ee, #06b6d4); }

        .card-jaringan_kartika2::before { background: linear-gradient(90deg, #8b5cf6, #7c3aed); }
        .card-jaringan_kartika2 .card-bg-circle { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }
        .card-jaringan_kartika2 .card-icon-wrapper { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }

        .card-jaringan_kartika3::before { background: linear-gradient(90deg, #10b981, #059669); }
        .card-jaringan_kartika3 .card-bg-circle { background: linear-gradient(135deg, #34d399, #10b981); }
        .card-jaringan_kartika3 .card-icon-wrapper { background: linear-gradient(135deg, #34d399, #10b981); }

        .card-jaringan_cvc::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
        .card-jaringan_cvc .card-bg-circle { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
        .card-jaringan_cvc .card-icon-wrapper { background: linear-gradient(135deg, #fbbf24, #f59e0b); }

        .card-jaringan_cicu::before { background: linear-gradient(90deg, #e11d48, #be123c); }
        .card-jaringan_cicu .card-bg-circle { background: linear-gradient(135deg, #fb7185, #e11d48); }
        .card-jaringan_cicu .card-icon-wrapper { background: linear-gradient(135deg, #fb7185, #e11d48); }

        .card-ip_alat::before { background: linear-gradient(90deg, #3b82f6, #2563eb); }
        .card-ip_alat .card-bg-circle { background: linear-gradient(135deg, #60a5fa, #3b82f6); }
        .card-ip_alat .card-icon-wrapper { background: linear-gradient(135deg, #60a5fa, #2563eb); }

        /* ========== FOOTER ========== */
        .footer { position: relative; z-index: 1; text-align: center; padding: 40px 24px; color: var(--text-muted); font-size: 13px; font-weight: 600; }
        .footer a { color: var(--text-secondary); text-decoration: none; transition: var(--transition-fast); }
        .footer a:hover { color: white; }

        /* ========== SCROLLBAR ========== */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1199px) {
            .menu-grid { grid-template-columns: repeat(3, 1fr); gap: 20px; }
            .card-image { height: 180px; }
            .card-icon-wrapper { width: 80px; height: 80px; font-size: 32px; border-radius: 24px; }
        }

        @media (max-width: 991px) {
            .header-inner { padding: 14px 24px; }
            .container { padding: 0 24px; }
            .breadcrumb-bar { padding-top: 90px; }
            .menu-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
            .card-image { height: 180px; }
            .card-icon-wrapper { width: 76px; height: 76px; font-size: 30px; border-radius: 22px; }
            .card-content { padding: 20px; }
            .card-title { font-size: 16px; }
            .card-desc { font-size: 12px; }
            .page-title { font-size: 32px; }
        }

        @media (max-width: 767px) {
            .container { padding: 0 16px; }
            .header-inner { padding: 12px 16px; }
            .breadcrumb-inner { padding: 0 16px; }
            .breadcrumb-bar { padding-top: 80px; }
            .logo-text { font-size: 16px; }
            .menu-btn, .header-btn { width: 40px; height: 40px; border-radius: 12px; }
            .menu-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
            .card-image { height: 150px; }
            .card-bg-circle { width: 120px; height: 120px; }
            .card-icon-wrapper { width: 64px; height: 64px; font-size: 26px; border-radius: 20px; }
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
            .menu-grid { grid-template-columns: 1fr; gap: 14px; }
            .menu-card {
                display: flex; align-items: center; padding: 16px;
            }
            .card-image {
                width: 68px; height: 68px; min-width: 68px;
                margin-right: 16px; border-radius: 18px;
            }
            .card-bg-circle { width: 50px; height: 50px; filter: blur(20px); }
            .card-icon-wrapper {
                width: 40px; height: 40px; border-radius: 12px; font-size: 18px;
            }
            .card-content { padding: 0; flex: 1; text-align: left; }
            .card-title { font-size: 15px; text-align: left; margin-bottom: 4px; }
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
                <a href="inventaris.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-warehouse"></i> Data Inventaris</a>
                <a href="kegiatan.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-list-check"></i> Data Kegiatan</a>
                <a href="jaringan.php" class="sidebar-item active" onclick="toggleSidebar()"><i class="fas fa-diagram-project"></i> Data Jaringan</a>
                <a href="ip_alat.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-network-wired"></i> IP Alat</a>
                <a href="laporan.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-chart-line"></i> Data Laporan</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title">Pengaturan</div>
                <a href="#" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-user-gear"></i> Profil</a>
                <a href="#" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-sliders"></i> Pengaturan</a>
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
                <span class="breadcrumb-current">Data Jaringan</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-badge"><i class="fas fa-diagram-project"></i> Modul Jaringan</div>
                <h1 class="page-title">Pilih <span class="gradient-text">Unit Jaringan</span></h1>
                <p class="page-subtitle">Silakan pilih unit atau ruangan untuk monitoring dan mengelola infrastruktur jaringan masing-masing area.</p>
            </section>

            <section class="menu-section">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-grid-2"></i> Daftar Unit Jaringan</h2>
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

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Inventaris Digital. Dibangun dengan <i class="fas fa-heart" style="color:#f43f5e"></i> untuk Indonesia.</p>
    </footer>

    <!-- Toast Notification -->
    <div id="toast" style="position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px); background: rgba(30, 41, 59, 0.95); backdrop-filter: blur(20px); color: white; padding: 14px 28px; border-radius: 16px; font-size: 14px; font-weight: 600; z-index: 9998; opacity: 0; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 40px rgba(0,0,0,0.3); pointer-events: none; white-space: nowrap;">
        <i class="fas fa-circle-info" style="margin-right:8px;color:var(--accent)"></i>
        <span id="toastMessage"></span>
    </div>

    <script>
        // Loading Screen
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('loadingScreen').classList.add('hidden');
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
    </script>
</body>
</html>