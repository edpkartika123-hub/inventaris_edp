<?php
include 'koneksi.php';

date_default_timezone_set('Asia/Jakarta');

/* ============================================================
   MAPPING: Tabel Disposal & Inventaris per Gedung
   ============================================================ */
$disposalMap = [
    'kartika1' => ['nama' => 'Kartika 1', 'table' => 'disposal_kartika1', 'inv_table' => 'inventaris_kartika1', 'has_tipe' => true],
    'kartika2' => ['nama' => 'Kartika 2', 'table' => 'disposal_kartika2', 'inv_table' => 'inventaris_kartika2', 'has_tipe' => false],
    'kartika3' => ['nama' => 'Kartika 3', 'table' => 'disposal_kartika3', 'inv_table' => 'inventaris_kartika3', 'has_tipe' => false],
    'cvc'      => ['nama' => 'CVC',       'table' => 'disposal_cvc',     'inv_table' => 'inventaris_cvc',     'has_tipe' => false],
    'cicu'     => ['nama' => 'CICU',      'table' => 'disposal_cicu',    'inv_table' => 'inventaris_cicu',    'has_tipe' => false]
];

$pcFields = [
    'nama_pc','nama_user','manufactur','processor_type','windows_version',
    'ram_size','ram_hardisk','monitor_model','monitor_size','monitor_vga',
    'tahun_pengadaan','id_personil_edp'
];

function normalizeTipe($tipe) {
    $map = [
        'pc' => 'PC',
        'printer1' => 'Printer 1',
        'printer2' => 'Printer 2',
        'semua' => 'PC + Semua Printer',
        'PC' => 'PC',
        'Printer 1' => 'Printer 1',
        'Printer 2' => 'Printer 2',
        'PC + Semua Printer' => 'PC + Semua Printer'
    ];
    return $map[$tipe] ?? $tipe;
}

function detectTipeFromData($row) {
    $hasPC = !empty($row['nama_pc']);
    $hasP1 = !empty($row['printer_1']);
    $hasP2 = !empty($row['printer_2']);
    if ($hasPC && ($hasP1 || $hasP2)) return 'PC + Semua Printer';
    if ($hasPC) return 'PC';
    if ($hasP1) return 'Printer 1';
    if ($hasP2) return 'Printer 2';
    return 'PC';
}

function buildSelectCols($cfg) {
    $cols = "id, id_asal";
    if ($cfg['has_tipe']) {
        $cols .= ", tipe_disposal";
    } else {
        $cols .= ", CASE 
            WHEN nama_pc != '' AND (printer_1 != '' OR printer_2 != '') THEN 'semua'
            WHEN nama_pc != '' THEN 'pc'
            WHEN printer_1 != '' THEN 'printer1'
            WHEN printer_2 != '' THEN 'printer2'
            ELSE 'pc'
        END as tipe_disposal";
    }
    $cols .= ", gedung, lantai, ruangan, nama_pc, nama_user, manufactur, processor_type, windows_version,
               ram_size, ram_hardisk, monitor_model, monitor_size, monitor_vga, printer_1, printer_2,
               tahun_pengadaan, id_personil_edp, alasan_disposal";
    return $cols;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    $items = $_POST['bulk_items'] ?? [];
    if (!empty($items)) {
        foreach ($items as $item) {
            $parts = explode('|', $item);
            if (count($parts) === 2) {
                $sourceKey = preg_replace('/[^a-zA-Z0-9_]/', '', $parts[0]);
                $id = intval($parts[1]);
                if (isset($disposalMap[$sourceKey]) && $id > 0) {
                    $tableSafe = preg_replace('/[^a-zA-Z0-9_]/', '', $disposalMap[$sourceKey]['table']);
                    mysqli_query($conn, "DELETE FROM `$tableSafe` WHERE id = $id");
                }
            }
        }
    }
    $redir = 'disposal.php';
    if (isset($_GET['sumber'])) $redir .= '?sumber=' . urlencode($_GET['sumber']);
    header('Location: ' . $redir);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_disposal'])) {
    $sourceKey = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['source_key'] ?? '');
    $disposalId = intval($_POST['disposal_id'] ?? 0);

    if (!isset($disposalMap[$sourceKey])) die('Sumber tidak valid.');
    if ($disposalId <= 0) die('ID disposal tidak valid.');

    $dispCfg = $disposalMap[$sourceKey];
    $dispTable = preg_replace('/[^a-zA-Z0-9_]/', '', $dispCfg['table']);
    $invTable  = preg_replace('/[^a-zA-Z0-9_]/', '', $dispCfg['inv_table']);
    $namaSumber = $dispCfg['nama'];

    $q = mysqli_query($conn, "SELECT * FROM `$dispTable` WHERE id = $disposalId LIMIT 1");
    if (!$q) die('Gagal membaca data disposal: ' . mysqli_error($conn));
    $d = mysqli_fetch_assoc($q);
    if (!$d) die('Data disposal tidak ditemukan.');

    $idAsal = intval($d['id_asal']);
    if ($dispCfg['has_tipe'] && !empty($d['tipe_disposal'])) {
        $tipe = normalizeTipe(trim($d['tipe_disposal']));
    } else {
        $tipe = detectTipeFromData($d);
    }

    if ($idAsal <= 0) die('ID asal inventaris tidak valid.');

    $existingQ = mysqli_query($conn, "SELECT * FROM `$invTable` WHERE id = $idAsal LIMIT 1");
    if (!$existingQ) die('Gagal mengecek inventaris: ' . mysqli_error($conn));
    $existing = mysqli_fetch_assoc($existingQ);

    mysqli_begin_transaction($conn);
    try {
        if ($tipe === 'PC') {
            if (!$existing) throw new Exception('Data inventaris asal sudah tidak ada. Restore PC tidak dapat dilakukan.');
            $sets = [];
            foreach ($pcFields as $f) {
                if (array_key_exists($f, $d)) {
                    $sets[] = "`$f` = '" . mysqli_real_escape_string($conn, $d[$f] ?? '') . "'";
                }
            }
            if (empty($sets)) throw new Exception('Tidak ada field PC yang dapat direstore.');
            $sql = "UPDATE `$invTable` SET " . implode(',', $sets) . " WHERE id = $idAsal LIMIT 1";
            if (!mysqli_query($conn, $sql)) throw new Exception('Gagal restore PC: ' . mysqli_error($conn));
        } elseif ($tipe === 'Printer 1') {
            if (!$existing) throw new Exception('Data inventaris asal sudah tidak ada. Restore Printer 1 tidak dapat dilakukan.');
            $p1 = mysqli_real_escape_string($conn, $d['printer_1'] ?? '');
            if ($p1 === '') throw new Exception('Data Printer 1 pada disposal kosong.');
            if (!mysqli_query($conn, "UPDATE `$invTable` SET printer_1 = '$p1' WHERE id = $idAsal LIMIT 1"))
                throw new Exception('Gagal restore Printer 1: ' . mysqli_error($conn));
        } elseif ($tipe === 'Printer 2') {
            if (!$existing) throw new Exception('Data inventaris asal sudah tidak ada. Restore Printer 2 tidak dapat dilakukan.');
            $p2 = mysqli_real_escape_string($conn, $d['printer_2'] ?? '');
            if ($p2 === '') throw new Exception('Data Printer 2 pada disposal kosong.');
            if (!mysqli_query($conn, "UPDATE `$invTable` SET printer_2 = '$p2' WHERE id = $idAsal LIMIT 1"))
                throw new Exception('Gagal restore Printer 2: ' . mysqli_error($conn));
        } elseif ($tipe === 'PC + Semua Printer') {
            if ($existing) throw new Exception('ID inventaris #' . $idAsal . ' sudah digunakan kembali. Restore dibatalkan.');
            $insertFields = ['id','gedung','lantai','ruangan','nama_pc','nama_user','manufactur','processor_type','windows_version','ram_size','ram_hardisk','monitor_model','monitor_size','monitor_vga','printer_1','printer_2','tahun_pengadaan','id_personil_edp'];
            $insertValues = [$idAsal];
            foreach (array_slice($insertFields, 1) as $f) {
                $insertValues[] = "'" . mysqli_real_escape_string($conn, $d[$f] ?? '') . "'";
            }
            $sql = "INSERT INTO `$invTable` (" . implode(',', array_map(fn($f)=>"`$f`", $insertFields)) . ") VALUES (" . implode(',', $insertValues) . ")";
            if (!mysqli_query($conn, $sql)) throw new Exception('Gagal mengembalikan data inventaris: ' . mysqli_error($conn));
        } else {
            throw new Exception('Tipe disposal "' . $tipe . '" tidak dikenali.');
        }
        if (!mysqli_query($conn, "DELETE FROM `$dispTable` WHERE id = $disposalId LIMIT 1"))
            throw new Exception('Data berhasil direstore tetapi gagal menghapus record disposal: ' . mysqli_error($conn));
        mysqli_commit($conn);
        header('Location: disposal.php?restore=success&sumber=' . urlencode($namaSumber));
        exit;
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        die('<div style="font-family:Arial;max-width:700px;margin:60px auto;padding:25px;border-radius:15px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;"><h2>Restore dibatalkan</h2><p>' . htmlspecialchars($e->getMessage()) . '</p><a href="disposal.php" style="display:inline-block;margin-top:15px;padding:10px 16px;background:#991b1b;color:#fff;text-decoration:none;border-radius:8px;">Kembali ke Disposal</a></div>');
    }
}

if (isset($_GET['hapus']) && isset($_GET['src'])) {
    $sourceKey = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['src']);
    $id = intval($_GET['hapus']);
    if (isset($disposalMap[$sourceKey]) && $id > 0) {
        $tableSafe = preg_replace('/[^a-zA-Z0-9_]/', '', $disposalMap[$sourceKey]['table']);
        mysqli_query($conn, "DELETE FROM `$tableSafe` WHERE id = $id");
    }
    header('Location: disposal.php');
    exit;
}

$filterSumber = $_GET['sumber'] ?? '';
$perPage = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$sortableCols = ['id','sumber','tipe_disposal','gedung','lantai','ruangan','nama_pc','nama_user','tahun_pengadaan'];
$sortBy = in_array($_GET['sort'] ?? '', $sortableCols) ? $_GET['sort'] : 'id';
$sortOrder = ($_GET['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

$unionParts = [];
$countParts = [];
$statsParts = [];
$chartSumberParts = [];
$chartTipeParts = [];

foreach ($disposalMap as $key => $cfg) {
    $tableSafe = preg_replace('/[^a-zA-Z0-9_]/', '', $cfg['table']);
    $selectCols = buildSelectCols($cfg);

    $unionParts[] = "(SELECT $selectCols, '{$cfg['nama']}' as sumber, '$key' as source_key FROM `$tableSafe`)";
    $countParts[] = "(SELECT COUNT(*) as cnt FROM `$tableSafe`)";

    if ($cfg['has_tipe']) {
        $statsParts[] = "(SELECT tipe_disposal, COUNT(*) as jumlah FROM `$tableSafe` GROUP BY tipe_disposal)";
        $chartTipeParts[] = "(SELECT tipe_disposal, COUNT(*) as jumlah FROM `$tableSafe` GROUP BY tipe_disposal)";
    } else {
        $statsParts[] = "(SELECT 
            CASE 
                WHEN nama_pc != '' AND (printer_1 != '' OR printer_2 != '') THEN 'semua'
                WHEN nama_pc != '' THEN 'pc'
                WHEN printer_1 != '' THEN 'printer1'
                WHEN printer_2 != '' THEN 'printer2'
                ELSE 'pc'
            END as tipe_disposal, 
            COUNT(*) as jumlah 
            FROM `$tableSafe` GROUP BY tipe_disposal)";
        $chartTipeParts[] = "(SELECT 
            CASE 
                WHEN nama_pc != '' AND (printer_1 != '' OR printer_2 != '') THEN 'semua'
                WHEN nama_pc != '' THEN 'pc'
                WHEN printer_1 != '' THEN 'printer1'
                WHEN printer_2 != '' THEN 'printer2'
                ELSE 'pc'
            END as tipe_disposal, 
            COUNT(*) as jumlah 
            FROM `$tableSafe` GROUP BY tipe_disposal)";
    }
    $chartSumberParts[] = "(SELECT '{$cfg['nama']}' as sumber, COUNT(*) as jumlah FROM `$tableSafe`)";
}

$totalRows = 0;
if (!empty($countParts)) {
    $countSql = "SELECT SUM(cnt) as total FROM (" . implode(" UNION ALL ", $countParts) . ") t";
    $countRes = mysqli_query($conn, $countSql);
    $totalRows = ($countRes) ? (int)mysqli_fetch_assoc($countRes)['total'] : 0;
}

$whereClause = '';
if ($filterSumber !== '') {
    $whereClause = " WHERE sumber = '" . mysqli_real_escape_string($conn, $filterSumber) . "'";
}

$totalPages = max(1, ceil($totalRows / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$dataRows = [];
if (!empty($unionParts)) {
    $unionSql = "SELECT * FROM (" . implode(" UNION ALL ", $unionParts) . ") combined" . $whereClause . " ORDER BY $sortBy $sortOrder LIMIT $perPage OFFSET $offset";
    $result = mysqli_query($conn, $unionSql);
    if (!$result) die('Gagal mengambil data disposal: ' . mysqli_error($conn));
    while ($r = mysqli_fetch_assoc($result)) $dataRows[] = $r;
}

$stats = [];
if (!empty($statsParts)) {
    $statsSql = "SELECT tipe_disposal, SUM(jumlah) as jumlah FROM (" . implode(" UNION ALL ", $statsParts) . ") t GROUP BY tipe_disposal";
    $statsResult = mysqli_query($conn, $statsSql);
    if ($statsResult) while ($s = mysqli_fetch_assoc($statsResult)) {
        $nt = normalizeTipe($s['tipe_disposal']);
        $stats[$nt] = ($stats[$nt] ?? 0) + (int)$s['jumlah'];
    }
}

$chartSumber = [];
if (!empty($chartSumberParts)) {
    $chartSumberSql = "SELECT sumber, SUM(jumlah) as jumlah FROM (" . implode(" UNION ALL ", $chartSumberParts) . ") t GROUP BY sumber ORDER BY jumlah DESC";
    $chartSumberRes = mysqli_query($conn, $chartSumberSql);
    if ($chartSumberRes) while ($r = mysqli_fetch_assoc($chartSumberRes)) $chartSumber[] = $r;
}

$chartTipe = [];
if (!empty($chartTipeParts)) {
    $chartTipeSql = "SELECT tipe_disposal, SUM(jumlah) as jumlah FROM (" . implode(" UNION ALL ", $chartTipeParts) . ") t GROUP BY tipe_disposal ORDER BY jumlah DESC";
    $chartTipeRes = mysqli_query($conn, $chartTipeSql);
    if ($chartTipeRes) while ($r = mysqli_fetch_assoc($chartTipeRes)) $chartTipe[] = $r;
}

function buildSortUrl($col, $currSort, $currOrder, $filterSumber) {
    $newOrder = ($currSort === $col && $currOrder === 'asc') ? 'desc' : 'asc';
    $url = 'disposal.php?sort=' . $col . '&order=' . $newOrder;
    if ($filterSumber !== '') $url .= '&sumber=' . urlencode($filterSumber);
    return $url;
}
function sortIcon($col, $currSort, $currOrder) {
    if ($currSort !== $col) return '<i class="fas fa-sort" style="opacity:0.3;margin-left:6px;font-size:10px;"></i>';
    return $currOrder === 'asc' 
        ? '<i class="fas fa-sort-up" style="color:var(--accent-cyan);margin-left:6px;font-size:10px;"></i>'
        : '<i class="fas fa-sort-down" style="color:var(--accent-cyan);margin-left:6px;font-size:10px;"></i>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Disposal Inventaris | Digital Asset Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --bg-primary: #0a0e1a; --bg-secondary: #111827; --bg-card: rgba(17,24,39,0.7);
            --bg-glass: rgba(255,255,255,0.03); --border-glass: rgba(255,255,255,0.08);
            --accent-cyan: #06b6d4; --accent-blue: #3b82f6; --accent-purple: #8b5cf6;
            --accent-pink: #ec4899; --accent-emerald: #10b981; --accent-amber: #f59e0b; --accent-rose: #f43f5e;
            --text-primary: #f1f5f9; --text-secondary: #94a3b8; --text-muted: #64748b;
            --shadow-glow: 0 0 40px rgba(6,182,212,0.1); --chart-grid: rgba(255,255,255,0.03);
            --toast-bg: rgba(17,24,39,0.95); --toast-border: rgba(255,255,255,0.1);
            --drag-ghost-bg: rgba(6,182,212,0.2); --drag-ghost-border: var(--accent-cyan);
        }
        body.light-mode {
            --bg-primary: #f8fafc; --bg-secondary: #ffffff; --bg-card: rgba(255,255,255,0.9);
            --bg-glass: rgba(0,0,0,0.03); --border-glass: rgba(0,0,0,0.08);
            --text-primary: #0f172a; --text-secondary: #475569; --text-muted: #94a3b8;
            --shadow-glow: 0 0 40px rgba(6,182,212,0.15); --chart-grid: rgba(0,0,0,0.05);
            --toast-bg: rgba(255,255,255,0.95); --toast-border: rgba(0,0,0,0.1);
            --drag-ghost-bg: rgba(6,182,212,0.1); --drag-ghost-border: var(--accent-cyan);
        }
        body.light-mode .bg-grid { background-image: linear-gradient(rgba(6,182,212,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(6,182,212,0.05) 1px, transparent 1px); }
        body.light-mode .orb { opacity: 0.08; }
        body.light-mode th { background: rgba(241,245,249,0.95); color: #475569; border-bottom: 1px solid rgba(0,0,0,0.08); }
        body.light-mode th:hover { background: rgba(226,232,240,1); color: var(--accent-cyan); }
        body.light-mode td { border-bottom: 1px solid rgba(0,0,0,0.04); color: #475569; }
        body.light-mode tbody tr:hover { background: rgba(6,182,212,0.03); }
        body.light-mode tbody tr.selected { background: rgba(6,182,212,0.08); }
        body.light-mode .empty-state i { opacity: 0.15; }
        body.light-mode .alert-success { background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(16,185,129,0.03)); }
        body.light-mode .bulk-bar { background: linear-gradient(135deg, rgba(244,63,95,0.05), rgba(244,63,95,0.02)); }
        body.light-mode .page-btn.disabled { background: rgba(0,0,0,0.03); }
        body.light-mode .modal-overlay { background: rgba(0,0,0,0.4); }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-primary); min-height: 100vh; color: var(--text-primary); overflow-x: hidden; transition: background 0.4s ease, color 0.4s ease; }
        .bg-grid { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; background-image: linear-gradient(rgba(6,182,212,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(6,182,212,0.03) 1px, transparent 1px); background-size: 60px 60px; animation: gridMove 20s linear infinite; }
        @keyframes gridMove { 0%{transform:translate(0,0);} 100%{transform:translate(60px,60px);} }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.15; pointer-events: none; z-index: 0; animation: orbFloat 15s ease-in-out infinite; }
        .orb-1 { width: 400px; height: 400px; background: var(--accent-cyan); top: -100px; right: -100px; }
        .orb-2 { width: 300px; height: 300px; background: var(--accent-purple); bottom: -50px; left: -50px; animation-delay: -5s; }
        .orb-3 { width: 250px; height: 250px; background: var(--accent-blue); top: 50%; left: 50%; animation-delay: -10s; }
        @keyframes orbFloat { 0%,100%{transform:translate(0,0) scale(1);} 33%{transform:translate(30px,-30px) scale(1.1);} 66%{transform:translate(-20px,20px) scale(0.9);} }
        .container { position: relative; z-index: 1; max-width: 1700px; margin: 0 auto; padding: 30px 24px; }
        .theme-toggle { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 12px; background: var(--bg-glass); border: 1px solid var(--border-glass); color: var(--text-secondary); font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'Inter', sans-serif; }
        .theme-toggle:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.15); color: var(--text-primary); transform: translateY(-2px); }
        .toast-container { position: fixed; top: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 12px; pointer-events: none; }
        .toast { background: var(--toast-bg); border: 1px solid var(--toast-border); backdrop-filter: blur(20px); border-radius: 14px; padding: 16px 20px; min-width: 320px; max-width: 420px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); display: flex; align-items: flex-start; gap: 12px; pointer-events: all; transform: translateX(120%); animation: toastSlideIn 0.4s ease forwards; position: relative; overflow: hidden; }
        .toast.toast-exit { animation: toastSlideOut 0.4s ease forwards; }
        @keyframes toastSlideIn { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toastSlideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(120%); opacity: 0; } }
        .toast-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .toast-icon.success { background: rgba(16,185,129,0.15); color: var(--accent-emerald); }
        .toast-icon.error { background: rgba(244,63,95,0.15); color: var(--accent-rose); }
        .toast-icon.warning { background: rgba(245,158,11,0.15); color: var(--accent-amber); }
        .toast-icon.info { background: rgba(6,182,212,0.15); color: var(--accent-cyan); }
        .toast-content { flex: 1; }
        .toast-title { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .toast-message { font-size: 13px; color: var(--text-secondary); line-height: 1.5; }
        .toast-close { width: 28px; height: 28px; border-radius: 8px; background: transparent; border: none; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; font-size: 12px; }
        .toast-close:hover { background: var(--bg-glass); color: var(--text-primary); }
        .toast-progress { position: absolute; bottom: 0; left: 0; height: 3px; background: linear-gradient(90deg, var(--accent-cyan), var(--accent-blue)); border-radius: 0 0 0 14px; animation: toastProgress 5s linear forwards; }
        @keyframes toastProgress { from { width: 100%; } to { width: 0%; } }
        th.dragging { opacity: 0.5; background: var(--drag-ghost-bg) !important; border: 2px dashed var(--drag-ghost-border); }
        th.drag-over { border-left: 3px solid var(--accent-cyan); background: var(--drag-ghost-bg) !important; }
        .drag-handle { cursor: grab; margin-right: 6px; opacity: 0.3; transition: opacity 0.2s; }
        .drag-handle:hover { opacity: 0.8; }
        th:active .drag-handle { cursor: grabbing; }
        .header-section { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
        .header-left { flex: 1; min-width: 300px; }
        .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-muted); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .breadcrumb i { font-size: 10px; }
        .header-title { font-size: 36px; font-weight: 800; background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent-cyan) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 8px; letter-spacing: -0.5px; }
        .header-subtitle { color: var(--text-secondary); font-size: 15px; max-width: 600px; line-height: 1.6; }
        .header-actions { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .live-indicator { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: var(--accent-emerald); }
        .live-indicator::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--accent-emerald); animation: pulse 2s ease-in-out infinite; box-shadow: 0 0 8px rgba(16,185,129,0.5); }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.5;} }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: var(--bg-glass); border: 1px solid var(--border-glass); border-radius: 12px; color: var(--text-secondary); text-decoration: none; font-size: 14px; font-weight: 500; transition: all 0.3s ease; backdrop-filter: blur(10px); }
        .btn-back:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.15); color: var(--text-primary); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
        .alert-success { display: flex; align-items: center; gap: 12px; padding: 16px 20px; background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(16,185,129,0.05)); border: 1px solid rgba(16,185,129,0.2); border-radius: 14px; margin-bottom: 25px; animation: slideDown 0.5s ease; backdrop-filter: blur(10px); }
        @keyframes slideDown { from{opacity:0;transform:translateY(-20px);} to{opacity:1;transform:translateY(0);} }
        .alert-success i { color: var(--accent-emerald); font-size: 20px; }
        .alert-success span { color: #a7f3d0; font-weight: 500; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 25px; }
        .stat-card { background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 16px; padding: 20px; position: relative; overflow: hidden; transition: all 0.3s ease; backdrop-filter: blur(20px); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: linear-gradient(90deg, var(--accent-cyan), var(--accent-blue)); opacity: 0; transition: opacity 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); border-color: rgba(255,255,255,0.15); box-shadow: var(--shadow-glow); }
        .stat-card:hover::before { opacity: 1; }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 14px; }
        .stat-icon.cyan { background: rgba(6,182,212,0.15); color: var(--accent-cyan); }
        .stat-icon.blue { background: rgba(59,130,246,0.15); color: var(--accent-blue); }
        .stat-icon.purple { background: rgba(139,92,246,0.15); color: var(--accent-purple); }
        .stat-icon.pink { background: rgba(236,72,153,0.15); color: var(--accent-pink); }
        .stat-icon.emerald { background: rgba(16,185,129,0.15); color: var(--accent-emerald); }
        .stat-icon.amber { background: rgba(245,158,11,0.15); color: var(--accent-amber); }
        .stat-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .stat-value { font-size: 28px; font-weight: 800; color: var(--text-primary); line-height: 1; }
        .charts-section { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .chart-card { background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 16px; padding: 20px; backdrop-filter: blur(20px); transition: all 0.3s ease; }
        .chart-card:hover { border-color: rgba(255,255,255,0.12); }
        .chart-header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; font-size: 14px; font-weight: 700; color: var(--text-secondary); }
        .chart-header i { color: var(--accent-cyan); }
        .chart-container { position: relative; height: 220px; }
        @media (max-width: 1200px) { .charts-section { grid-template-columns: 1fr; } }
        .filter-section { background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 16px; padding: 20px; margin-bottom: 25px; backdrop-filter: blur(20px); }
        .filter-header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; font-size: 14px; font-weight: 600; color: var(--text-secondary); }
        .filter-header i { color: var(--accent-cyan); }
        .filter-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .filter-chip { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; background: var(--bg-glass); border: 1px solid var(--border-glass); color: var(--text-secondary); text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.3s ease; cursor: pointer; }
        .filter-chip:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.15); color: var(--text-primary); transform: translateY(-1px); }
        .filter-chip.active { background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue)); border-color: transparent; color: #fff; box-shadow: 0 4px 15px rgba(6,182,212,0.3); }
        .table-section { background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 16px; overflow: hidden; backdrop-filter: blur(20px); box-shadow: 0 4px 30px rgba(0,0,0,0.2); }
        .table-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-glass); flex-wrap: wrap; gap: 15px; }
        .table-title { display: flex; align-items: center; gap: 10px; font-size: 16px; font-weight: 700; }
        .table-title i { color: var(--accent-cyan); }
        .table-meta { font-size: 13px; color: var(--text-muted); font-weight: 400; margin-left: 8px; }
        .table-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .search-box { position: relative; }
        .search-box input { padding: 10px 16px 10px 40px; background: var(--bg-glass); border: 1px solid var(--border-glass); border-radius: 10px; color: var(--text-primary); font-size: 13px; width: 220px; outline: none; transition: all 0.3s ease; font-family: 'Inter', sans-serif; }
        .search-box input:focus { border-color: var(--accent-cyan); box-shadow: 0 0 0 3px rgba(6,182,212,0.1); }
        .search-box input::placeholder { color: var(--text-muted); }
        .search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 13px; }
        .btn-export { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 10px; border: 1px solid var(--border-glass); background: var(--bg-glass); color: var(--text-secondary); font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'Inter', sans-serif; }
        .btn-export:hover { transform: translateY(-2px); border-color: rgba(255,255,255,0.2); color: var(--text-primary); }
        .btn-export.excel:hover { background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.3); color: var(--accent-emerald); }
        .btn-export.pdf:hover { background: rgba(244,63,95,0.1); border-color: rgba(244,63,95,0.3); color: var(--accent-rose); }
        .bulk-bar { display: none; align-items: center; justify-content: space-between; padding: 12px 24px; background: linear-gradient(135deg, rgba(244,63,95,0.08), rgba(244,63,95,0.03)); border-bottom: 1px solid rgba(244,63,95,0.15); animation: slideDown 0.3s ease; }
        .bulk-bar.active { display: flex; }
        .bulk-info { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #fda4af; font-weight: 600; }
        .bulk-info i { color: var(--accent-rose); }
        .btn-bulk-delete { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; background: linear-gradient(135deg, var(--accent-rose), #e11d48); color: #fff; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'Inter', sans-serif; }
        .btn-bulk-delete:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(244,63,95,0.3); }
        .btn-bulk-cancel { padding: 8px 16px; border-radius: 8px; background: transparent; border: 1px solid var(--border-glass); color: var(--text-secondary); font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'Inter', sans-serif; margin-right: 8px; }
        .btn-bulk-cancel:hover { background: var(--bg-glass); color: var(--text-primary); }
        .table-wrapper { overflow-x: auto; max-height: 65vh; overflow-y: auto; }
        .table-wrapper::-webkit-scrollbar { width: 8px; height: 8px; }
        .table-wrapper::-webkit-scrollbar-track { background: transparent; }
        .table-wrapper::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
        .table-wrapper::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
        table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 12.5px; }
        thead { position: sticky; top: 0; z-index: 10; }
        th { background: rgba(15,23,42,0.95); padding: 14px 12px; text-align: left; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; border-bottom: 1px solid var(--border-glass); white-space: nowrap; backdrop-filter: blur(10px); cursor: pointer; transition: all 0.2s ease; user-select: none; }
        th:hover { color: var(--accent-cyan); background: rgba(15,23,42,1); }
        th:first-child { padding-left: 24px; }
        th:last-child { padding-right: 24px; }
        td { padding: 14px 12px; border-bottom: 1px solid rgba(255,255,255,0.03); color: var(--text-secondary); vertical-align: middle; transition: all 0.2s ease; cursor: pointer; }
        td:first-child { padding-left: 24px; }
        td:last-child { padding-right: 24px; }
        tbody tr { transition: all 0.2s ease; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }
        tbody tr:hover td { color: var(--text-primary); }
        tbody tr.selected { background: rgba(6,182,212,0.06); }
        tbody tr.selected td { color: var(--text-primary); }
        .custom-checkbox { width: 18px; height: 18px; border: 2px solid var(--border-glass); border-radius: 5px; background: var(--bg-glass); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; position: relative; }
        .custom-checkbox:hover { border-color: var(--accent-cyan); }
        .custom-checkbox.checked { background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue)); border-color: transparent; }
        .custom-checkbox.checked::after { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: 10px; color: #fff; }
        .custom-checkbox input { position: absolute; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; white-space: nowrap; }
        .badge-source { background: rgba(59,130,246,0.12); color: #93c5fd; border: 1px solid rgba(59,130,246,0.2); }
        .badge-pc { background: rgba(139,92,246,0.12); color: #c4b5fd; border: 1px solid rgba(139,92,246,0.2); }
        .badge-printer { background: rgba(245,158,11,0.12); color: #fcd34d; border: 1px solid rgba(245,158,11,0.2); }
        .badge-all { background: rgba(244,63,95,0.12); color: #fda4af; border: 1px solid rgba(244,63,95,0.2); }
        .badge-empty { background: rgba(100,116,139,0.12); color: var(--text-muted); border: 1px solid rgba(100,116,139,0.2); }
        .action-group { display: flex; gap: 6px; white-space: nowrap; }
        .btn-action { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 14px; border-radius: 8px; border: none; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'Inter', sans-serif; text-decoration: none; }
        .btn-restore { background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(16,185,129,0.05)); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
        .btn-restore:hover { background: linear-gradient(135deg, rgba(16,185,129,0.25), rgba(16,185,129,0.15)); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(16,185,129,0.2); }
        .btn-delete { background: linear-gradient(135deg, rgba(244,63,95,0.15), rgba(244,63,95,0.05)); color: #fb7185; border: 1px solid rgba(244,63,95,0.2); width: 36px; height: 36px; padding: 0; }
        .btn-delete:hover { background: linear-gradient(135deg, rgba(244,63,95,0.25), rgba(244,63,95,0.15)); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(244,63,95,0.2); }
        .btn-detail { background: linear-gradient(135deg, rgba(6,182,212,0.15), rgba(6,182,212,0.05)); color: #67e8f9; border: 1px solid rgba(6,182,212,0.2); width: 36px; height: 36px; padding: 0; }
        .btn-detail:hover { background: linear-gradient(135deg, rgba(6,182,212,0.25), rgba(6,182,212,0.15)); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(6,182,212,0.2); }
        .pagination-bar { display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-top: 1px solid var(--border-glass); flex-wrap: wrap; gap: 12px; }
        .pagination-info { font-size: 13px; color: var(--text-muted); }
        .pagination-controls { display: flex; gap: 6px; align-items: center; }
        .page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 8px; background: var(--bg-glass); border: 1px solid var(--border-glass); color: var(--text-secondary); text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.3s ease; }
        .page-btn:hover { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.15); color: var(--text-primary); }
        .page-btn.active { background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue)); border-color: transparent; color: #fff; box-shadow: 0 4px 12px rgba(6,182,212,0.3); }
        .page-btn.disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }
        .page-btn i { font-size: 11px; }
        .empty-state { text-align: center; padding: 80px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 56px; margin-bottom: 20px; opacity: 0.3; display: block; }
        .empty-state h3 { font-size: 18px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; }
        .empty-state p { font-size: 14px; }
        .cell-primary { font-weight: 600; color: var(--text-primary); }
        .cell-secondary { color: var(--text-secondary); }
        .cell-muted { color: var(--text-muted); font-size: 11px; }
        .cell-alasan { max-width: 200px; line-height: 1.5; }
        .row-num { font-weight: 700; color: var(--accent-cyan); font-size: 13px; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 20px; opacity: 0; transition: opacity 0.3s ease; }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-container { background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 20px; width: 100%; max-width: 700px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,0.5); transform: scale(0.95) translateY(20px); transition: all 0.3s ease; }
        .modal-overlay.active .modal-container { transform: scale(1) translateY(0); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 24px 28px; border-bottom: 1px solid var(--border-glass); }
        .modal-header h2 { font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .modal-header h2 i { color: var(--accent-cyan); }
        .modal-close { width: 36px; height: 36px; border-radius: 10px; background: var(--bg-glass); border: 1px solid var(--border-glass); color: var(--text-secondary); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; font-size: 16px; }
        .modal-close:hover { background: rgba(244,63,95,0.15); border-color: rgba(244,63,95,0.3); color: var(--accent-rose); }
        .modal-body { padding: 24px 28px; }
        .detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .detail-item { background: var(--bg-glass); border: 1px solid var(--border-glass); border-radius: 12px; padding: 16px; transition: all 0.3s ease; }
        .detail-item:hover { border-color: rgba(255,255,255,0.12); transform: translateY(-2px); }
        .detail-item.full-width { grid-column: 1 / -1; }
        .detail-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
        .detail-label i { font-size: 10px; color: var(--accent-cyan); }
        .detail-value { font-size: 14px; font-weight: 600; color: var(--text-primary); word-break: break-word; }
        .detail-value.empty { color: var(--text-muted); font-weight: 400; font-style: italic; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 28px 24px; }
        .btn-modal-close { padding: 10px 20px; border-radius: 10px; background: var(--bg-glass); border: 1px solid var(--border-glass); color: var(--text-secondary); font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'Inter', sans-serif; }
        .btn-modal-close:hover { background: rgba(255,255,255,0.06); color: var(--text-primary); }
        @media (max-width: 768px) {
            .container { padding: 20px 16px; }
            .header-title { font-size: 28px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .search-box input { width: 100%; }
            .table-header { flex-direction: column; align-items: flex-start; }
            th, td { padding: 10px 8px; font-size: 11px; }
            .action-group { flex-direction: column; }
            .detail-grid { grid-template-columns: 1fr; }
            .pagination-bar { flex-direction: column; align-items: center; }
            .charts-section { grid-template-columns: 1fr; }
            .bulk-bar { flex-direction: column; gap: 10px; text-align: center; }
            .toast { min-width: auto; max-width: 90vw; }
            .toast-container { right: 12px; left: 12px; }
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="toast-container" id="toastContainer"></div>
    <div class="container">
        <div class="header-section">
            <div class="header-left">
                <div class="breadcrumb">
                    <span>Dashboard</span><i class="fas fa-chevron-right"></i>
                    <span>Asset Management</span><i class="fas fa-chevron-right"></i>
                    <span style="color: var(--accent-cyan);">Disposal</span>
                </div>
                <h1 class="header-title">
                    <i class="fas fa-recycle" style="margin-right: 12px; color: var(--accent-cyan);"></i>
                    Data Disposal Inventaris
                </h1>
                <p class="header-subtitle">
                    Manajemen data perangkat yang telah dipindahkan dari inventaris aktif.
                    Pantau riwayat disposal, lakukan restore, atau hapus permanen dengan kontrol penuh.
                </p>
            </div>
            <div class="header-actions">
                <div class="live-indicator">System Active</div>
                <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon" id="themeIcon"></i>
                    <span id="themeText">Dark</span>
                </button>
                <a href="inventaris.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Inventaris
                </a>
            </div>
        </div>

        <?php if (isset($_GET['restore']) && $_GET['restore'] === 'success'): ?>
        <div class="alert-success" id="restoreAlert">
            <i class="fas fa-circle-check"></i>
            <span>Data berhasil dikembalikan ke inventaris <?= htmlspecialchars($_GET['sumber'] ?? ''); ?>.</span>
        </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon cyan"><i class="fas fa-database"></i></div>
                <div class="stat-label">Total Disposal</div>
                <div class="stat-value"><?= number_format($totalRows); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-desktop"></i></div>
                <div class="stat-label">PC Only</div>
                <div class="stat-value"><?= number_format($stats['PC'] ?? 0); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fas fa-print"></i></div>
                <div class="stat-label">Printer</div>
                <div class="stat-value"><?= number_format(($stats['Printer 1'] ?? 0) + ($stats['Printer 2'] ?? 0)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pink"><i class="fas fa-layer-group"></i></div>
                <div class="stat-label">PC + Printer</div>
                <div class="stat-value"><?= number_format($stats['PC + Semua Printer'] ?? 0); ?></div>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header"><i class="fas fa-chart-pie"></i> Distribusi per Lokasi</div>
                <div class="chart-container"><canvas id="chartSumber"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-header"><i class="fas fa-chart-bar"></i> Distribusi per Tipe</div>
                <div class="chart-container"><canvas id="chartTipe"></canvas></div>
            </div>
            <div class="chart-card">
                <div class="chart-header"><i class="fas fa-chart-column"></i> Ringkasan per Lokasi</div>
                <div class="chart-container"><canvas id="chartLokasi"></canvas></div>
            </div>
        </div>

        <div class="filter-section">
            <div class="filter-header"><i class="fas fa-filter"></i><span>Filter Lokasi</span></div>
            <div class="filter-grid">
                <?php
                $filters = [
                    ['val'=>'', 'label'=>'Semua Lokasi', 'icon'=>'fa-globe'],
                    ['val'=>'Kartika 1', 'label'=>'Kartika 1', 'icon'=>'fa-building'],
                    ['val'=>'Kartika 2', 'label'=>'Kartika 2', 'icon'=>'fa-building'],
                    ['val'=>'Kartika 3', 'label'=>'Kartika 3', 'icon'=>'fa-building'],
                    ['val'=>'CVC', 'label'=>'CVC', 'icon'=>'fa-hospital'],
                    ['val'=>'CICU', 'label'=>'CICU', 'icon'=>'fa-hospital'],
                ];
                foreach ($filters as $f):
                    $isActive = $filterSumber === $f['val'];
                    $href = $f['val'] === '' ? 'disposal.php' : 'disposal.php?sumber=' . urlencode($f['val']);
                ?>
                <a href="<?= $href; ?>" class="filter-chip <?= $isActive ? 'active' : '' ?>">
                    <i class="fas <?= $f['icon']; ?>"></i> <?= $f['label']; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="table-section">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-table-list"></i>
                    Riwayat Disposal
                    <span class="table-meta">&mdash; <?= number_format($totalRows); ?> record ditemukan</span>
                </div>
                <div class="table-actions">
                    <button class="btn-export excel" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button class="btn-export pdf" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="tableSearch" placeholder="Cari data..." onkeyup="filterTable()">
                    </div>
                </div>
            </div>

            <div class="bulk-bar" id="bulkBar">
                <div class="bulk-info">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span id="bulkCount">0 data terpilih</span>
                </div>
                <div>
                    <button class="btn-bulk-cancel" onclick="clearSelection()">Batal</button>
                    <button class="btn-bulk-delete" onclick="confirmBulkDelete()">
                        <i class="fas fa-trash"></i> Hapus Terpilih
                    </button>
                </div>
            </div>

            <form id="bulkForm" method="POST" action="disposal.php<?= $filterSumber !== '' ? '?sumber=' . urlencode($filterSumber) : ''; ?>">
                <input type="hidden" name="bulk_delete" value="1">
                <div class="table-wrapper">
                    <table id="disposalTable">
                        <thead>
                            <tr id="tableHeaderRow">
                                <th style="width:40px;cursor:default;" data-col="checkbox" onclick="event.stopPropagation()">
                                    <div class="custom-checkbox" id="selectAllBox" onclick="toggleSelectAll()">
                                        <input type="checkbox" id="selectAllInput">
                                    </div>
                                </th>
                                <th draggable="true" data-col="no" onclick="window.location.href='<?= buildSortUrl('id', $sortBy, $sortOrder, $filterSumber); ?>'">
                                    <i class="fas fa-grip-vertical drag-handle"></i>No <?= sortIcon('id', $sortBy, $sortOrder); ?>
                                </th>
                                <th draggable="true" data-col="sumber" onclick="window.location.href='<?= buildSortUrl('sumber', $sortBy, $sortOrder, $filterSumber); ?>'">
                                    <i class="fas fa-grip-vertical drag-handle"></i>Sumber <?= sortIcon('sumber', $sortBy, $sortOrder); ?>
                                </th>
                                <th draggable="true" data-col="tipe" onclick="window.location.href='<?= buildSortUrl('tipe_disposal', $sortBy, $sortOrder, $filterSumber); ?>'">
                                    <i class="fas fa-grip-vertical drag-handle"></i>Tipe <?= sortIcon('tipe_disposal', $sortBy, $sortOrder); ?>
                                </th>
                                <th draggable="true" data-col="lokasi" onclick="window.location.href='<?= buildSortUrl('ruangan', $sortBy, $sortOrder, $filterSumber); ?>'">
                                    <i class="fas fa-grip-vertical drag-handle"></i>Lokasi <?= sortIcon('ruangan', $sortBy, $sortOrder); ?>
                                </th>
                                <th draggable="true" data-col="nama_pc" onclick="window.location.href='<?= buildSortUrl('nama_pc', $sortBy, $sortOrder, $filterSumber); ?>'">
                                    <i class="fas fa-grip-vertical drag-handle"></i>Nama PC <?= sortIcon('nama_pc', $sortBy, $sortOrder); ?>
                                </th>
                                <th draggable="true" data-col="user" onclick="window.location.href='<?= buildSortUrl('nama_user', $sortBy, $sortOrder, $filterSumber); ?>'">
                                    <i class="fas fa-grip-vertical drag-handle"></i>User <?= sortIcon('nama_user', $sortBy, $sortOrder); ?>
                                </th>
                                <th draggable="true" data-col="spesifikasi">
                                    <i class="fas fa-grip-vertical drag-handle"></i>Spesifikasi
                                </th>
                                <th draggable="true" data-col="printer">
                                    <i class="fas fa-grip-vertical drag-handle"></i>Printer
                                </th>
                                <th draggable="true" data-col="tahun" onclick="window.location.href='<?= buildSortUrl('tahun_pengadaan', $sortBy, $sortOrder, $filterSumber); ?>'">
                                    <i class="fas fa-grip-vertical drag-handle"></i>Tahun <?= sortIcon('tahun_pengadaan', $sortBy, $sortOrder); ?>
                                </th>
                                <th draggable="true" data-col="edp">
                                    <i class="fas fa-grip-vertical drag-handle"></i>EDP
                                </th>
                                <th draggable="true" data-col="alasan">
                                    <i class="fas fa-grip-vertical drag-handle"></i>Alasan
                                </th>
                                <th style="cursor:default;" data-col="aksi" onclick="event.stopPropagation()">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = $offset + 1;
                            if (!empty($dataRows)):
                                foreach ($dataRows as $row):
                                    // Normalisasi tipe: gunakan tipe_disposal jika ada, jika tidak deteksi dari data
                                    if (!empty($row['tipe_disposal'])) {
                                        $tipe = normalizeTipe($row['tipe_disposal']);
                                    } else {
                                        $tipe = detectTipeFromData($row);
                                    }

                                    if ($tipe === 'PC') { $badgeClass = 'badge-pc'; $badgeIcon = 'fa-desktop'; }
                                    elseif (strpos($tipe, 'Printer') !== false) { $badgeClass = 'badge-printer'; $badgeIcon = 'fa-print'; }
                                    elseif ($tipe === 'PC + Semua Printer') { $badgeClass = 'badge-all'; $badgeIcon = 'fa-layer-group'; }
                                    else { $badgeClass = 'badge-empty'; $badgeIcon = 'fa-question'; }

                                    $spec = [];
                                    if (!empty($row['manufactur'])) $spec[] = $row['manufactur'];
                                    if (!empty($row['processor_type'])) $spec[] = $row['processor_type'];
                                    if (!empty($row['ram_size'])) $spec[] = $row['ram_size'];
                                    $specText = !empty($spec) ? implode(' &bull; ', $spec) : '<span class="cell-muted">-</span>';

                                    $printerInfo = [];
                                    if (!empty($row['printer_1'])) $printerInfo[] = 'P1: ' . $row['printer_1'];
                                    if (!empty($row['printer_2'])) $printerInfo[] = 'P2: ' . $row['printer_2'];
                                    $printerText = !empty($printerInfo) ? implode('<br>', $printerInfo) : '<span class="cell-muted">-</span>';

                                    $modalData = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                    $rowId = (int)$row['id'];
                                    $sourceKey = htmlspecialchars($row['source_key']);
                                    $sumberName = htmlspecialchars($row['sumber']);
                            ?>
                            <tr id="row-<?= $sourceKey; ?>-<?= $rowId; ?>" onclick="openModal('<?= $modalData; ?>')">
                                <td onclick="event.stopPropagation();" data-col="checkbox">
                                    <div class="custom-checkbox" id="cb-<?= $sourceKey; ?>-<?= $rowId; ?>" onclick="toggleRow('<?= $sourceKey; ?>', <?= $rowId; ?>, event)">
                                        <input type="checkbox" name="bulk_items[]" value="<?= $sourceKey; ?>|<?= $rowId; ?>" class="row-checkbox" data-source="<?= $sourceKey; ?>" data-id="<?= $rowId; ?>">
                                    </div>
                                </td>
                                <td data-col="no"><span class="row-num"><?= $no++; ?></span></td>
                                <td data-col="sumber"><span class="badge badge-source"><i class="fas fa-building"></i> <?= $sumberName; ?></span></td>
                                <td data-col="tipe"><span class="badge <?= $badgeClass; ?>"><i class="fas <?= $badgeIcon; ?>"></i> <?= htmlspecialchars($tipe); ?></span></td>
                                <td data-col="lokasi">
                                    <div class="cell-primary"><?= htmlspecialchars($row['ruangan']); ?></div>
                                    <div class="cell-muted"><?= htmlspecialchars($row['gedung']); ?>, Lantai <?= htmlspecialchars($row['lantai']); ?></div>
                                </td>
                                <td data-col="nama_pc" class="cell-primary"><?= !empty($row['nama_pc']) ? htmlspecialchars($row['nama_pc']) : '<span class="cell-muted">-</span>'; ?></td>
                                <td data-col="user" class="cell-secondary"><?= !empty($row['nama_user']) ? htmlspecialchars($row['nama_user']) : '<span class="cell-muted">-</span>'; ?></td>
                                <td data-col="spesifikasi" class="cell-secondary"><?= $specText; ?></td>
                                <td data-col="printer" class="cell-secondary"><?= $printerText; ?></td>
                                <td data-col="tahun" class="cell-secondary"><?= !empty($row['tahun_pengadaan']) ? htmlspecialchars($row['tahun_pengadaan']) : '<span class="cell-muted">-</span>'; ?></td>
                                <td data-col="edp" class="cell-secondary"><?= !empty($row['id_personil_edp']) ? htmlspecialchars($row['id_personil_edp']) : '<span class="cell-muted">-</span>'; ?></td>
                                <td data-col="alasan" class="cell-alasan"><?= nl2br(htmlspecialchars($row['alasan_disposal'])); ?></td>
                                <td data-col="aksi" onclick="event.stopPropagation();">
                                    <div class="action-group">
                                        <button class="btn-action btn-detail" onclick="openModal('<?= $modalData; ?>')" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" action="disposal.php" style="display:inline;" onsubmit="return confirm('Kembalikan data ini ke inventaris <?= $sumberName; ?>?');">
                                            <input type="hidden" name="restore_disposal" value="1">
                                            <input type="hidden" name="source_key" value="<?= $sourceKey; ?>">
                                            <input type="hidden" name="disposal_id" value="<?= $rowId; ?>">
                                            <button type="submit" class="btn-action btn-restore" title="Restore ke Inventaris">
                                                <i class="fas fa-rotate-left"></i><span>Restore</span>
                                            </button>
                                        </form>
                                        <a href="disposal.php?hapus=<?= $rowId; ?>&src=<?= $sourceKey; ?>" class="btn-action btn-delete" onclick="return confirm('Hapus permanen data disposal ini?')" title="Hapus Permanen">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="13">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h3>Belum ada data disposal</h3>
                                        <p>Data perangkat yang dipindahkan dari inventaris akan muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>

            <?php if ($totalPages > 1): ?>
            <div class="pagination-bar">
                <div class="pagination-info">
                    Menampilkan <?= number_format($offset + 1); ?> - <?= number_format(min($offset + $perPage, $totalRows)); ?> dari <?= number_format($totalRows); ?> data
                </div>
                <div class="pagination-controls">
                    <?php
                    $baseParams = [];
                    if ($filterSumber !== '') $baseParams[] = 'sumber=' . urlencode($filterSumber);
                    if ($sortBy !== 'id') $baseParams[] = 'sort=' . $sortBy;
                    if ($sortOrder !== 'desc') $baseParams[] = 'order=' . $sortOrder;
                    $baseUrl = 'disposal.php' . (!empty($baseParams) ? '?' . implode('&', $baseParams) . '&' : '?');

                    $prevDisabled = $page <= 1 ? 'disabled' : '';
                    $prevUrl = $baseUrl . 'page=' . ($page - 1);
                    echo '<a href="' . $prevUrl . '" class="page-btn ' . $prevDisabled . '"><i class="fas fa-chevron-left"></i></a>';

                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    if ($startPage > 1) {
                        echo '<a href="' . $baseUrl . 'page=1" class="page-btn">1</a>';
                        if ($startPage > 2) echo '<span class="page-btn disabled">...</span>';
                    }
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $active = $i === $page ? 'active' : '';
                        echo '<a href="' . $baseUrl . 'page=' . $i . '" class="page-btn ' . $active . '">' . $i . '</a>';
                    }
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) echo '<span class="page-btn disabled">...</span>';
                        echo '<a href="' . $baseUrl . 'page=' . $totalPages . '" class="page-btn">' . $totalPages . '</a>';
                    }

                    $nextDisabled = $page >= $totalPages ? 'disabled' : '';
                    $nextUrl = $baseUrl . 'page=' . ($page + 1);
                    echo '<a href="' . $nextUrl . '" class="page-btn ' . $nextDisabled . '"><i class="fas fa-chevron-right"></i></a>';
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal-overlay" id="detailModal" onclick="closeModal(event)">
        <div class="modal-container" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2><i class="fas fa-circle-info"></i> Detail Disposal</h2>
                <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer">
                <button class="btn-modal-close" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>
<script>
    // ==================== THEME TOGGLE ====================
    function initTheme() {
        const saved = localStorage.getItem('disposal_theme');
        if (saved === 'light') {
            document.body.classList.add('light-mode');
            updateThemeUI(true);
        }
    }
    function toggleTheme() {
        const isLight = document.body.classList.toggle('light-mode');
        localStorage.setItem('disposal_theme', isLight ? 'light' : 'dark');
        updateThemeUI(isLight);
        showToast(isLight ? 'Light mode diaktifkan' : 'Dark mode diaktifkan', 'info');
        updateChartTheme();
    }
    function updateThemeUI(isLight) {
        const icon = document.getElementById('themeIcon');
        const text = document.getElementById('themeText');
        if (isLight) { icon.className = 'fas fa-sun'; text.textContent = 'Light'; }
        else { icon.className = 'fas fa-moon'; text.textContent = 'Dark'; }
    }

    // ==================== TOAST NOTIFICATIONS ====================
    function showToast(message, type = 'info', title = '') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast';
        const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
        const titles = { success: 'Berhasil', error: 'Error', warning: 'Peringatan', info: 'Informasi' };
        toast.innerHTML = `
            <div class="toast-icon ${type}"><i class="fas ${icons[type]}"></i></div>
            <div class="toast-content">
                <div class="toast-title">${title || titles[type]}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="dismissToast(this.parentElement)"><i class="fas fa-xmark"></i></button>
            <div class="toast-progress"></div>
        `;
        container.appendChild(toast);
        setTimeout(() => { if (toast.parentElement) dismissToast(toast); }, 5000);
    }
    function dismissToast(toast) {
        toast.classList.add('toast-exit');
        setTimeout(() => { if (toast.parentElement) toast.parentElement.removeChild(toast); }, 400);
    }

    // ==================== DRAG & DROP COLUMN REORDER ====================
    let draggedCol = null;
    let colOrder = JSON.parse(localStorage.getItem('disposal_col_order')) || ['checkbox','no','sumber','tipe','lokasi','nama_pc','user','spesifikasi','printer','tahun','edp','alasan','aksi'];

    function initDragDrop() {
        applyColumnOrder();
        const headers = document.querySelectorAll('th[draggable="true"]');
        headers.forEach(th => {
            th.addEventListener('dragstart', handleDragStart);
            th.addEventListener('dragover', handleDragOver);
            th.addEventListener('drop', handleDrop);
            th.addEventListener('dragend', handleDragEnd);
            th.addEventListener('dragenter', handleDragEnter);
            th.addEventListener('dragleave', handleDragLeave);
        });
    }
    function handleDragStart(e) {
        draggedCol = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', this.dataset.col);
    }
    function handleDragOver(e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; }
    function handleDragEnter(e) { if (this !== draggedCol) this.classList.add('drag-over'); }
    function handleDragLeave(e) { this.classList.remove('drag-over'); }
    function handleDrop(e) {
        e.preventDefault();
        if (this === draggedCol) return;
        reorderColumns(draggedCol.dataset.col, this.dataset.col);
    }
    function handleDragEnd(e) {
        this.classList.remove('dragging');
        document.querySelectorAll('th').forEach(th => th.classList.remove('drag-over'));
    }
    function reorderColumns(fromCol, toCol) {
        const fromIndex = colOrder.indexOf(fromCol);
        const toIndex = colOrder.indexOf(toCol);
        if (fromIndex === -1 || toIndex === -1) return;
        colOrder.splice(fromIndex, 1);
        colOrder.splice(toIndex, 0, fromCol);
        localStorage.setItem('disposal_col_order', JSON.stringify(colOrder));
        applyColumnOrder();
        showToast('Urutan kolom berhasil diubah', 'success');
    }
    function applyColumnOrder() {
        const table = document.getElementById('disposalTable');
        const headerRow = document.getElementById('tableHeaderRow');
        const tbody = table.querySelector('tbody');
        const headers = Array.from(headerRow.querySelectorAll('th'));
        const headerMap = {};
        headers.forEach(th => { headerMap[th.dataset.col] = th; });
        colOrder.forEach(col => { if (headerMap[col]) headerRow.appendChild(headerMap[col]); });
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            const cellMap = {};
            cells.forEach(td => { cellMap[td.dataset.col] = td; });
            colOrder.forEach(col => { if (cellMap[col]) row.appendChild(cellMap[col]); });
        });
    }

    // ==================== TABLE SEARCH ====================
    function filterTable() {
        const input = document.getElementById('tableSearch');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('disposalTable');
        const tr = table.getElementsByTagName('tr');
        for (let i = 1; i < tr.length; i++) {
            const td = tr[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < td.length; j++) {
                if (td[j]) {
                    const txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) { found = true; break; }
                }
            }
            tr[i].style.display = found ? '' : 'none';
        }
    }

    // ==================== MODAL ====================
    function openModal(jsonData) {
        const data = JSON.parse(jsonData);
        const modal = document.getElementById('detailModal');
        const body = document.getElementById('modalBody');
        const fields = [
            {key:'sumber', label:'Sumber Inventaris', icon:'fa-building'},
            {key:'tipe_disposal', label:'Tipe Disposal', icon:'fa-tag'},
            {key:'source_key', label:'Source Key', icon:'fa-key'},
            {key:'gedung', label:'Gedung', icon:'fa-building'},
            {key:'lantai', label:'Lantai', icon:'fa-layer-group'},
            {key:'ruangan', label:'Ruangan', icon:'fa-door-open'},
            {key:'nama_pc', label:'Nama PC', icon:'fa-desktop'},
            {key:'nama_user', label:'Nama User', icon:'fa-user'},
            {key:'manufactur', label:'Manufactur', icon:'fa-industry'},
            {key:'processor_type', label:'Processor', icon:'fa-microchip'},
            {key:'windows_version', label:'Windows Version', icon:'fa-windows'},
            {key:'ram_size', label:'RAM', icon:'fa-memory'},
            {key:'ram_hardisk', label:'Hardisk', icon:'fa-hdd'},
            {key:'monitor_model', label:'Monitor Model', icon:'fa-tv'},
            {key:'monitor_size', label:'Monitor Size', icon:'fa-expand'},
            {key:'monitor_vga', label:'VGA', icon:'fa-display'},
            {key:'printer_1', label:'Printer 1', icon:'fa-print'},
            {key:'printer_2', label:'Printer 2', icon:'fa-print'},
            {key:'tahun_pengadaan', label:'Tahun Pengadaan', icon:'fa-calendar-check'},
            {key:'id_personil_edp', label:'ID Personil EDP', icon:'fa-id-card'},
            {key:'alasan_disposal', label:'Alasan Disposal', icon:'fa-circle-exclamation', full:true},
        ];
        let html = '<div class="detail-grid">';
        fields.forEach(f => {
            const val = data[f.key] || '';
            const emptyClass = val ? '' : 'empty';
            const displayText = val || 'Tidak ada data';
            const fullClass = f.full ? 'full-width' : '';
            html += `<div class="detail-item ${fullClass}"><div class="detail-label"><i class="fas ${f.icon}"></i> ${f.label}</div><div class="detail-value ${emptyClass}">${displayText}</div></div>`;
        });
        html += '</div>';
        body.innerHTML = html;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(event) {
        if (event && event.target !== document.getElementById('detailModal')) return;
        document.getElementById('detailModal').classList.remove('active');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

    // ==================== EXPORT ====================
    function exportToExcel() {
        const table = document.getElementById('disposalTable');
        const wb = XLSX.utils.table_to_book(table, {sheet: "Disposal Data"});
        XLSX.writeFile(wb, 'Data_Disposal_Inventaris_<?= date('Ymd_His'); ?>.xlsx');
        showToast('File Excel berhasil diunduh', 'success');
    }
    async function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({orientation: 'landscape', unit: 'mm', format: 'a4'});
        doc.setFontSize(16);
        doc.text('Data Disposal Inventaris', 14, 20);
        doc.setFontSize(10);
        doc.setTextColor(100);
        doc.text('Export Date: <?= date('d F Y H:i:s'); ?>', 14, 28);
        const table = document.getElementById('disposalTable');
        doc.autoTable({
            html: table, startY: 35, styles: {fontSize: 8, cellPadding: 2},
            headStyles: {fillColor: [6, 182, 212], textColor: 255, fontStyle: 'bold'},
            alternateRowStyles: {fillColor: [245, 245, 245]}, margin: {top: 35}
        });
        doc.save('Data_Disposal_Inventaris_<?= date('Ymd_His'); ?>.pdf');
        showToast('File PDF berhasil diunduh', 'success');
    }

    // ==================== BULK DELETE ====================
    let selectedItems = new Set();
    function toggleRow(sourceKey, id, event) {
        event.stopPropagation();
        const itemKey = sourceKey + '|' + id;
        const checkbox = document.querySelector(`input[data-source="${sourceKey}"][data-id="${id}"]`);
        const row = document.getElementById(`row-${sourceKey}-${id}`);
        const cbDiv = document.getElementById(`cb-${sourceKey}-${id}`);
        if (selectedItems.has(itemKey)) {
            selectedItems.delete(itemKey);
            if (checkbox) checkbox.checked = false;
            if (row) row.classList.remove('selected');
            if (cbDiv) cbDiv.classList.remove('checked');
        } else {
            selectedItems.add(itemKey);
            if (checkbox) checkbox.checked = true;
            if (row) row.classList.add('selected');
            if (cbDiv) cbDiv.classList.add('checked');
        }
        updateBulkBar();
    }
    function toggleSelectAll() {
        event.stopPropagation();
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const selectAllBox = document.getElementById('selectAllBox');
        const allSelected = selectedItems.size === checkboxes.length && checkboxes.length > 0;
        checkboxes.forEach(cb => {
            const sourceKey = cb.dataset.source;
            const id = parseInt(cb.dataset.id);
            const itemKey = sourceKey + '|' + id;
            const row = document.getElementById(`row-${sourceKey}-${id}`);
            const cbDiv = document.getElementById(`cb-${sourceKey}-${id}`);
            if (allSelected) {
                selectedItems.delete(itemKey);
                cb.checked = false;
                if (row) row.classList.remove('selected');
                if (cbDiv) cbDiv.classList.remove('checked');
            } else {
                selectedItems.add(itemKey);
                cb.checked = true;
                if (row) row.classList.add('selected');
                if (cbDiv) cbDiv.classList.add('checked');
            }
        });
        if (allSelected) selectAllBox.classList.remove('checked');
        else selectAllBox.classList.add('checked');
        updateBulkBar();
    }
    function updateBulkBar() {
        const bar = document.getElementById('bulkBar');
        const count = document.getElementById('bulkCount');
        if (selectedItems.size > 0) {
            bar.classList.add('active');
            count.textContent = selectedItems.size + ' data terpilih';
        } else {
            bar.classList.remove('active');
            document.getElementById('selectAllBox').classList.remove('checked');
        }
    }
    function clearSelection() {
        selectedItems.clear();
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = false;
            const sourceKey = cb.dataset.source;
            const id = cb.dataset.id;
            const row = document.getElementById(`row-${sourceKey}-${id}`);
            const cbDiv = document.getElementById(`cb-${sourceKey}-${id}`);
            if (row) row.classList.remove('selected');
            if (cbDiv) cbDiv.classList.remove('checked');
        });
        document.getElementById('selectAllBox').classList.remove('checked');
        updateBulkBar();
    }
    function confirmBulkDelete() {
        if (selectedItems.size === 0) return;
        const msg = 'Anda yakin ingin menghapus ' + selectedItems.size + ' data disposal secara permanen? Tindakan ini tidak dapat dibatalkan.';
        if (confirm(msg)) document.getElementById('bulkForm').submit();
    }

    // ==================== CHARTS ====================
    let chartSumberInst, chartTipeInst, chartLokasiInst;
    function initCharts() {
        Chart.defaults.color = document.body.classList.contains('light-mode') ? '#475569' : '#94a3b8';
        Chart.defaults.borderColor = document.body.classList.contains('light-mode') ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.05)';
        Chart.defaults.font.family = 'Inter';

        const sumberCtx = document.getElementById('chartSumber').getContext('2d');
        chartSumberInst = new Chart(sumberCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($chartSumber, 'sumber')); ?>,
                datasets: [{
                    data: <?= json_encode(array_map('intval', array_column($chartSumber, 'jumlah'))); ?>,
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b'],
                    borderColor: 'transparent', hoverOffset: 8
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 12, padding: 15, font: {size: 11} } } }, cutout: '65%' }
        });

        const tipeCtx = document.getElementById('chartTipe').getContext('2d');
        chartTipeInst = new Chart(tipeCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(function($r){ return normalizeTipe($r['tipe_disposal']); }, $chartTipe)); ?>,
                datasets: [{
                    label: 'Jumlah',
                    data: <?= json_encode(array_map('intval', array_column($chartTipe, 'jumlah'))); ?>,
                    backgroundColor: ['#8b5cf6', '#f59e0b', '#f43f5e', '#06b6d4'],
                    borderRadius: 8, borderSkipped: false
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: document.body.classList.contains('light-mode') ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.03)' } }, x: { grid: { display: false } } } }
        });

        const lokasiCtx = document.getElementById('chartLokasi').getContext('2d');
        chartLokasiInst = new Chart(lokasiCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($chartSumber, 'sumber')); ?>,
                datasets: [{
                    label: 'Total Disposal',
                    data: <?= json_encode(array_map('intval', array_column($chartSumber, 'jumlah'))); ?>,
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b'],
                    borderRadius: 6, borderSkipped: false
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: document.body.classList.contains('light-mode') ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.03)' } }, x: { grid: { display: false } } } }
        });
    }
    function updateChartTheme() {
        const isLight = document.body.classList.contains('light-mode');
        const color = isLight ? '#475569' : '#94a3b8';
        const gridColor = isLight ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.03)';
        [chartSumberInst, chartTipeInst, chartLokasiInst].forEach(chart => {
            if (!chart) return;
            chart.options.plugins.legend.labels.color = color;
            if (chart.options.scales.y) chart.options.scales.y.grid.color = gridColor;
            if (chart.options.scales.x) chart.options.scales.x.ticks.color = color;
            if (chart.options.scales.y) chart.options.scales.y.ticks.color = color;
            chart.update();
        });
    }

    // ==================== ENTRANCE ANIMATIONS ====================
    document.addEventListener('DOMContentLoaded', function() {
        initTheme();
        initDragDrop();
        initCharts();

        <?php if (isset($_GET['restore']) && $_GET['restore'] === 'success'): ?>
        setTimeout(() => {
            showToast('Data berhasil dikembalikan ke inventaris', 'success', 'Restore Berhasil');
            const alert = document.getElementById('restoreAlert');
            if (alert) alert.style.display = 'none';
        }, 500);
        <?php endif; ?>

        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.opacity = '0'; row.style.transform = 'translateY(10px)';
            setTimeout(() => { row.style.transition = 'all 0.4s ease'; row.style.opacity = '1'; row.style.transform = 'translateY(0)'; }, index * 50);
        });
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0'; card.style.transform = 'translateY(20px)';
            setTimeout(() => { card.style.transition = 'all 0.5s ease'; card.style.opacity = '1'; card.style.transform = 'translateY(0)'; }, index * 100);
        });
        const charts = document.querySelectorAll('.chart-card');
        charts.forEach((card, index) => {
            card.style.opacity = '0'; card.style.transform = 'translateY(20px)';
            setTimeout(() => { card.style.transition = 'all 0.5s ease'; card.style.opacity = '1'; card.style.transform = 'translateY(0)'; }, 400 + index * 100);
        });
    });
</script>
</body>
</html>