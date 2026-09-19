<?php
/**
 * Kartika 2 - Data Inventaris Hardware & PC EDP
 * Excel-Like Table v4.0 - Freeze Panes + Responsive Scroll
 */

include 'koneksi.php';

function extractYear($dateStr) {
    if (empty($dateStr)) return '';
    if (preg_match('/^\d{4}$/', $dateStr)) return $dateStr;
    $timestamp = strtotime($dateStr);
    return $timestamp ? date('Y', $timestamp) : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id']) && !isset($_POST['ganti_id']) && !isset($_POST['disposal_id'])) {
    $gedung = mysqli_real_escape_string($conn, $_POST['gedung']);
    $lantai = mysqli_real_escape_string($conn, $_POST['lantai']);
    $ruangan = mysqli_real_escape_string($conn, $_POST['ruangan']);
    $nama_pc = mysqli_real_escape_string($conn, $_POST['nama_pc']);
    $nama_user = mysqli_real_escape_string($conn, $_POST['nama_user']);
    $manufactur = mysqli_real_escape_string($conn, $_POST['manufactur']);
    $processor_type = mysqli_real_escape_string($conn, $_POST['processor_type']);
    $windows_version = mysqli_real_escape_string($conn, $_POST['windows_version']);
    $ram_size = mysqli_real_escape_string($conn, $_POST['ram_size']);
    $ram_hardisk = mysqli_real_escape_string($conn, $_POST['ram_hardisk']);
    $monitor_model = mysqli_real_escape_string($conn, $_POST['monitor_model']);
    $monitor_size = mysqli_real_escape_string($conn, $_POST['monitor_size']);
    $monitor_vga = mysqli_real_escape_string($conn, $_POST['monitor_vga']);
    $printer_1 = mysqli_real_escape_string($conn, $_POST['printer_1']);
    $printer_2 = mysqli_real_escape_string($conn, $_POST['printer_2']);
    $tahun_pengadaan = extractYear(mysqli_real_escape_string($conn, $_POST['tahun_pengadaan']));
    $id_personil_edp = mysqli_real_escape_string($conn, $_POST['id_personil_edp']);

    $sql = "INSERT INTO inventaris_kartika2 (gedung, lantai, ruangan, nama_pc, nama_user, manufactur, processor_type, windows_version, ram_size, ram_hardisk, monitor_model, monitor_size, monitor_vga, printer_1, printer_2, tahun_pengadaan, id_personil_edp) VALUES ('$gedung', '$lantai', '$ruangan', '$nama_pc', '$nama_user', '$manufactur', '$processor_type', '$windows_version', '$ram_size', '$ram_hardisk', '$monitor_model', '$monitor_size', '$monitor_vga', '$printer_1', '$printer_2', '$tahun_pengadaan', '$id_personil_edp')";
    mysqli_query($conn, $sql);
    header('Location: kartika2.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $gedung = mysqli_real_escape_string($conn, $_POST['gedung']);
    $lantai = mysqli_real_escape_string($conn, $_POST['lantai']);
    $ruangan = mysqli_real_escape_string($conn, $_POST['ruangan']);
    $nama_pc = mysqli_real_escape_string($conn, $_POST['nama_pc']);
    $nama_user = mysqli_real_escape_string($conn, $_POST['nama_user']);
    $manufactur = mysqli_real_escape_string($conn, $_POST['manufactur']);
    $processor_type = mysqli_real_escape_string($conn, $_POST['processor_type']);
    $windows_version = mysqli_real_escape_string($conn, $_POST['windows_version']);
    $ram_size = mysqli_real_escape_string($conn, $_POST['ram_size']);
    $ram_hardisk = mysqli_real_escape_string($conn, $_POST['ram_hardisk']);
    $monitor_model = mysqli_real_escape_string($conn, $_POST['monitor_model']);
    $monitor_size = mysqli_real_escape_string($conn, $_POST['monitor_size']);
    $monitor_vga = mysqli_real_escape_string($conn, $_POST['monitor_vga']);
    $printer_1 = mysqli_real_escape_string($conn, $_POST['printer_1']);
    $printer_2 = mysqli_real_escape_string($conn, $_POST['printer_2']);
    $tahun_pengadaan = extractYear(mysqli_real_escape_string($conn, $_POST['tahun_pengadaan']));
    $id_personil_edp = mysqli_real_escape_string($conn, $_POST['id_personil_edp']);

    $sql = "UPDATE inventaris_kartika2 SET gedung='$gedung', lantai='$lantai', ruangan='$ruangan', nama_pc='$nama_pc', nama_user='$nama_user', manufactur='$manufactur', processor_type='$processor_type', windows_version='$windows_version', ram_size='$ram_size', ram_hardisk='$ram_hardisk', monitor_model='$monitor_model', monitor_size='$monitor_size', monitor_vga='$monitor_vga', printer_1='$printer_1', printer_2='$printer_2', tahun_pengadaan='$tahun_pengadaan', id_personil_edp='$id_personil_edp' WHERE id=$id";
    mysqli_query($conn, $sql);
    header('Location: kartika2.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_id'])) {
    $id = intval($_POST['ganti_id']);
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan_ganti'] ?? '');
    $status_gudang = mysqli_real_escape_string($conn, $_POST['status_gudang'] ?? 'Stok');
    $tipe_ganti = $_POST['tipe_ganti'] ?? '';

    $tipe_valid = ['pc', 'printer1', 'printer2', 'semua'];
    if (!in_array($tipe_ganti, $tipe_valid, true)) die('Jenis barang tidak valid.');
    if (empty($alasan)) die('Alasan wajib diisi.');

    $query_data = mysqli_query($conn, "SELECT * FROM inventaris_kartika2 WHERE id = $id LIMIT 1");
    $data = mysqli_fetch_assoc($query_data);
    if (!$data) die('Data tidak ditemukan.');

    $gedung = mysqli_real_escape_string($conn, $data['gedung'] ?? '');
    $lantai = mysqli_real_escape_string($conn, $data['lantai'] ?? '');
    $ruangan = mysqli_real_escape_string($conn, $data['ruangan'] ?? '');
    $tahun_pengadaan = mysqli_real_escape_string($conn, $data['tahun_pengadaan'] ?? '');
    $id_personil_edp = mysqli_real_escape_string($conn, $data['id_personil_edp'] ?? '');

    mysqli_begin_transaction($conn);
    try {
        if ($tipe_ganti === 'pc') {
            if (trim($data['nama_pc'] ?? '') === '') throw new Exception('Data PC kosong.');
            $nama_pc = mysqli_real_escape_string($conn, $data['nama_pc'] ?? '');
            $nama_user = mysqli_real_escape_string($conn, $data['nama_user'] ?? '');
            $manufactur = mysqli_real_escape_string($conn, $data['manufactur'] ?? '');
            $processor_type = mysqli_real_escape_string($conn, $data['processor_type'] ?? '');
            $windows_version = mysqli_real_escape_string($conn, $data['windows_version'] ?? '');
            $ram_size = mysqli_real_escape_string($conn, $data['ram_size'] ?? '');
            $ram_hardisk = mysqli_real_escape_string($conn, $data['ram_hardisk'] ?? '');
            $monitor_model = mysqli_real_escape_string($conn, $data['monitor_model'] ?? '');
            $monitor_size = mysqli_real_escape_string($conn, $data['monitor_size'] ?? '');
            $monitor_vga = mysqli_real_escape_string($conn, $data['monitor_vga'] ?? '');
            $sql_gudang = "INSERT INTO gudang_kartika2 (id_asal, tipe_ganti, gedung, lantai, ruangan, nama_pc, nama_user, manufactur, processor_type, windows_version, ram_size, ram_hardisk, monitor_model, monitor_size, monitor_vga, printer_1, printer_2, tahun_pengadaan, id_personil_edp, alasan_ganti, status_gudang) VALUES ($id, '$tipe_ganti', '$gedung', '$lantai', '$ruangan', '$nama_pc', '$nama_user', '$manufactur', '$processor_type', '$windows_version', '$ram_size', '$ram_hardisk', '$monitor_model', '$monitor_size', '$monitor_vga', '', '', '$tahun_pengadaan', '$id_personil_edp', '$alasan', '$status_gudang')";
            if (!mysqli_query($conn, $sql_gudang)) throw new Exception('Gagal pindah ke gudang: ' . mysqli_error($conn));
            $sql_update = "UPDATE inventaris_kartika2 SET nama_pc='', nama_user='', manufactur='', processor_type='', windows_version='', ram_size='', ram_hardisk='', monitor_model='', monitor_size='', monitor_vga='', tahun_pengadaan='', id_personil_edp='' WHERE id = $id";
            if (!mysqli_query($conn, $sql_update)) throw new Exception('Gagal hapus dari inventaris: ' . mysqli_error($conn));
        }
        elseif ($tipe_ganti === 'printer1') {
            if (trim($data['printer_1'] ?? '') === '') throw new Exception('Printer 1 kosong.');
            $printer_1 = mysqli_real_escape_string($conn, $data['printer_1']);
            $sql_gudang = "INSERT INTO gudang_kartika2 (id_asal, tipe_ganti, gedung, lantai, ruangan, nama_pc, nama_user, manufactur, processor_type, windows_version, ram_size, ram_hardisk, monitor_model, monitor_size, monitor_vga, printer_1, printer_2, tahun_pengadaan, id_personil_edp, alasan_ganti, status_gudang) VALUES ($id, '$tipe_ganti', '$gedung', '$lantai', '$ruangan', '', '', '', '', '', '', '', '', '', '', '$printer_1', '', '', '', '$alasan', '$status_gudang')";
            if (!mysqli_query($conn, $sql_gudang)) throw new Exception('Gagal pindah: ' . mysqli_error($conn));
            $sql_update = "UPDATE inventaris_kartika2 SET printer_1 = '' WHERE id = $id";
            if (!mysqli_query($conn, $sql_update)) throw new Exception('Gagal hapus: ' . mysqli_error($conn));
        }
        elseif ($tipe_ganti === 'printer2') {
            if (trim($data['printer_2'] ?? '') === '') throw new Exception('Printer 2 kosong.');
            $printer_2 = mysqli_real_escape_string($conn, $data['printer_2']);
            $sql_gudang = "INSERT INTO gudang_kartika2 (id_asal, tipe_ganti, gedung, lantai, ruangan, nama_pc, nama_user, manufactur, processor_type, windows_version, ram_size, ram_hardisk, monitor_model, monitor_size, monitor_vga, printer_1, printer_2, tahun_pengadaan, id_personil_edp, alasan_ganti, status_gudang) VALUES ($id, '$tipe_ganti', '$gedung', '$lantai', '$ruangan', '', '', '', '', '', '', '', '', '', '', '', '$printer_2', '', '', '$alasan', '$status_gudang')";
            if (!mysqli_query($conn, $sql_gudang)) throw new Exception('Gagal pindah: ' . mysqli_error($conn));
            $sql_update = "UPDATE inventaris_kartika2 SET printer_2 = '' WHERE id = $id";
            if (!mysqli_query($conn, $sql_update)) throw new Exception('Gagal hapus: ' . mysqli_error($conn));
        }
        elseif ($tipe_ganti === 'semua') {
            $fields = ['nama_pc','nama_user','manufactur','processor_type','windows_version','ram_size','ram_hardisk','monitor_model','monitor_size','monitor_vga','printer_1','printer_2'];
            $escaped = [];
            foreach ($fields as $field) $escaped[$field] = mysqli_real_escape_string($conn, $data[$field] ?? '');
            $sql_gudang = "INSERT INTO gudang_kartika2 (id_asal, tipe_ganti, gedung, lantai, ruangan, nama_pc, nama_user, manufactur, processor_type, windows_version, ram_size, ram_hardisk, monitor_model, monitor_size, monitor_vga, printer_1, printer_2, tahun_pengadaan, id_personil_edp, alasan_ganti, status_gudang) VALUES ($id, '$tipe_ganti', '$gedung', '$lantai', '$ruangan', '{$escaped['nama_pc']}', '{$escaped['nama_user']}', '{$escaped['manufactur']}', '{$escaped['processor_type']}', '{$escaped['windows_version']}', '{$escaped['ram_size']}', '{$escaped['ram_hardisk']}', '{$escaped['monitor_model']}', '{$escaped['monitor_size']}', '{$escaped['monitor_vga']}', '{$escaped['printer_1']}', '{$escaped['printer_2']}', '$tahun_pengadaan', '$id_personil_edp', '$alasan', '$status_gudang')";
            if (!mysqli_query($conn, $sql_gudang)) throw new Exception('Gagal pindah semua: ' . mysqli_error($conn));
            $sql_delete = "DELETE FROM inventaris_kartika2 WHERE id = $id";
            if (!mysqli_query($conn, $sql_delete)) throw new Exception('Gagal hapus: ' . mysqli_error($conn));
        }
        mysqli_commit($conn);
        header('Location: kartika2.php?success=ganti'); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        die('<div style="font-family:Inter,system-ui;padding:30px;background:#fee2e2;color:#991b1b;max-width:700px;margin:50px auto;border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,.2);"><h3><i class="fas fa-circle-xmark"></i> Gagal Memindahkan Barang</h3><p>' . htmlspecialchars($e->getMessage()) . '</p><a href="kartika2.php" style="display:inline-flex;align-items:center;gap:8px;margin-top:16px;padding:10px 20px;background:#dc2626;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;"><i class="fas fa-arrow-left"></i> Kembali</a></div>');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['disposal_id'])) {
    $id = intval($_POST['disposal_id']);
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan_disposal'] ?? '');
    $tipe_disposal = $_POST['tipe_disposal'] ?? '';

    $tipe_valid = ['pc', 'printer1', 'printer2', 'semua'];
    if (!in_array($tipe_disposal, $tipe_valid, true)) die('Jenis disposal tidak valid.');
    if (empty($alasan)) die('Alasan disposal wajib diisi.');

    $query_data = mysqli_query($conn, "SELECT * FROM inventaris_kartika2 WHERE id = $id LIMIT 1");
    $data = mysqli_fetch_assoc($query_data);
    if (!$data) die('Data tidak ditemukan.');

    $gedung = mysqli_real_escape_string($conn, $data['gedung'] ?? '');
    $lantai = mysqli_real_escape_string($conn, $data['lantai'] ?? '');
    $ruangan = mysqli_real_escape_string($conn, $data['ruangan'] ?? '');
    $tahun_pengadaan = mysqli_real_escape_string($conn, $data['tahun_pengadaan'] ?? '');
    $id_personil_edp = mysqli_real_escape_string($conn, $data['id_personil_edp'] ?? '');

    mysqli_begin_transaction($conn);
    try {
        if ($tipe_disposal === 'pc') {
            if (trim($data['nama_pc'] ?? '') === '') throw new Exception('Data PC kosong.');
            $nama_pc = mysqli_real_escape_string($conn, $data['nama_pc'] ?? '');
            $nama_user = mysqli_real_escape_string($conn, $data['nama_user'] ?? '');
            $manufactur = mysqli_real_escape_string($conn, $data['manufactur'] ?? '');
            $processor_type = mysqli_real_escape_string($conn, $data['processor_type'] ?? '');
            $windows_version = mysqli_real_escape_string($conn, $data['windows_version'] ?? '');
            $ram_size = mysqli_real_escape_string($conn, $data['ram_size'] ?? '');
            $ram_hardisk = mysqli_real_escape_string($conn, $data['ram_hardisk'] ?? '');
            $monitor_model = mysqli_real_escape_string($conn, $data['monitor_model'] ?? '');
            $monitor_size = mysqli_real_escape_string($conn, $data['monitor_size'] ?? '');
            $monitor_vga = mysqli_real_escape_string($conn, $data['monitor_vga'] ?? '');
            $sql_disposal = "INSERT INTO disposal_kartika2 (id_asal, tipe_disposal, gedung, lantai, ruangan, nama_pc, nama_user, manufactur, processor_type, windows_version, ram_size, ram_hardisk, monitor_model, monitor_size, monitor_vga, printer_1, printer_2, tahun_pengadaan, id_personil_edp, alasan_disposal) VALUES ($id, '$tipe_disposal', '$gedung', '$lantai', '$ruangan', '$nama_pc', '$nama_user', '$manufactur', '$processor_type', '$windows_version', '$ram_size', '$ram_hardisk', '$monitor_model', '$monitor_size', '$monitor_vga', '', '', '$tahun_pengadaan', '$id_personil_edp', '$alasan')";
            if (!mysqli_query($conn, $sql_disposal)) throw new Exception('Gagal disposal: ' . mysqli_error($conn));
            $sql_update = "UPDATE inventaris_kartika2 SET nama_pc='', nama_user='', manufactur='', processor_type='', windows_version='', ram_size='', ram_hardisk='', monitor_model='', monitor_size='', monitor_vga='', tahun_pengadaan='', id_personil_edp='' WHERE id = $id";
            if (!mysqli_query($conn, $sql_update)) throw new Exception('Gagal hapus: ' . mysqli_error($conn));
        }
        elseif ($tipe_disposal === 'printer1') {
            if (trim($data['printer_1'] ?? '') === '') throw new Exception('Printer 1 kosong.');
            $printer_1 = mysqli_real_escape_string($conn, $data['printer_1']);
            $sql_disposal = "INSERT INTO disposal_kartika2 (id_asal, tipe_disposal, gedung, lantai, ruangan, nama_pc, nama_user, manufactur, processor_type, windows_version, ram_size, ram_hardisk, monitor_model, monitor_size, monitor_vga, printer_1, printer_2, tahun_pengadaan, id_personil_edp, alasan_disposal) VALUES ($id, '$tipe_disposal', '$gedung', '$lantai', '$ruangan', '', '', '', '', '', '', '', '', '', '', '$printer_1', '', '', '', '$alasan')";
            if (!mysqli_query($conn, $sql_disposal)) throw new Exception('Gagal disposal: ' . mysqli_error($conn));
            $sql_update = "UPDATE inventaris_kartika2 SET printer_1 = '' WHERE id = $id";
            if (!mysqli_query($conn, $sql_update)) throw new Exception('Gagal hapus: ' . mysqli_error($conn));
        }
        elseif ($tipe_disposal === 'printer2') {
            if (trim($data['printer_2'] ?? '') === '') throw new Exception('Printer 2 kosong.');
            $printer_2 = mysqli_real_escape_string($conn, $data['printer_2']);
            $sql_disposal = "INSERT INTO disposal_kartika2 (id_asal, tipe_disposal, gedung, lantai, ruangan, nama_pc, nama_user, manufactur, processor_type, windows_version, ram_size, ram_hardisk, monitor_model, monitor_size, monitor_vga, printer_1, printer_2, tahun_pengadaan, id_personil_edp, alasan_disposal) VALUES ($id, '$tipe_disposal', '$gedung', '$lantai', '$ruangan', '', '', '', '', '', '', '', '', '', '', '', '$printer_2', '', '', '$alasan')";
            if (!mysqli_query($conn, $sql_disposal)) throw new Exception('Gagal disposal: ' . mysqli_error($conn));
            $sql_update = "UPDATE inventaris_kartika2 SET printer_2 = '' WHERE id = $id";
            if (!mysqli_query($conn, $sql_update)) throw new Exception('Gagal hapus: ' . mysqli_error($conn));
        }
        elseif ($tipe_disposal === 'semua') {
            $fields = ['nama_pc','nama_user','manufactur','processor_type','windows_version','ram_size','ram_hardisk','monitor_model','monitor_size','monitor_vga','printer_1','printer_2'];
            $escaped = [];
            foreach ($fields as $field) $escaped[$field] = mysqli_real_escape_string($conn, $data[$field] ?? '');
            $sql_disposal = "INSERT INTO disposal_kartika2 (id_asal, tipe_disposal, gedung, lantai, ruangan, nama_pc, nama_user, manufactur, processor_type, windows_version, ram_size, ram_hardisk, monitor_model, monitor_size, monitor_vga, printer_1, printer_2, tahun_pengadaan, id_personil_edp, alasan_disposal) VALUES ($id, '$tipe_disposal', '$gedung', '$lantai', '$ruangan', '{$escaped['nama_pc']}', '{$escaped['nama_user']}', '{$escaped['manufactur']}', '{$escaped['processor_type']}', '{$escaped['windows_version']}', '{$escaped['ram_size']}', '{$escaped['ram_hardisk']}', '{$escaped['monitor_model']}', '{$escaped['monitor_size']}', '{$escaped['monitor_vga']}', '{$escaped['printer_1']}', '{$escaped['printer_2']}', '$tahun_pengadaan', '$id_personil_edp', '$alasan')";
            if (!mysqli_query($conn, $sql_disposal)) throw new Exception('Gagal disposal: ' . mysqli_error($conn));
            $sql_delete = "DELETE FROM inventaris_kartika2 WHERE id = $id";
            if (!mysqli_query($conn, $sql_delete)) throw new Exception('Gagal hapus: ' . mysqli_error($conn));
        }
        mysqli_commit($conn);
        header('Location: kartika2.php?success=disposal'); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        die('<div style="font-family:Inter,system-ui;padding:30px;background:#fee2e2;color:#991b1b;max-width:700px;margin:50px auto;border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,.2);"><h3><i class="fas fa-circle-xmark"></i> Gagal Disposal</h3><p>' . htmlspecialchars($e->getMessage()) . '</p><a href="kartika2.php" style="display:inline-flex;align-items:center;gap:8px;margin-top:16px;padding:10px 20px;background:#dc2626;color:#fff;border-radius:10px;text-decoration:none;font-weight:600;"><i class="fas fa-arrow-left"></i> Kembali</a></div>');
    }
}

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM inventaris_kartika2 WHERE id=$id");
    header('Location: kartika2.php'); exit;
}
?>

<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" id="metaThemeColor" content="#0b1120">
    <title>Kartika 2 - Inventaris Hardware & PC EDP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        (function(){
            var saved = null;
            try { saved = localStorage.getItem('kartika2-theme'); } catch(e) {}
            var prefersLight = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches;
            var theme = saved || (prefersLight ? 'light' : 'dark');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <style>

        :root {
            --bg-deep: #0b1120;
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --glass-bg: rgba(15,23,42,0.75);
            --glass-border: rgba(148,163,184,0.12);
            --glass-border-hover: rgba(148,163,184,0.22);
            --input-bg: rgba(255,255,255,0.05);
            --row-bg-1: rgba(255,255,255,0.02);
            --row-bg-2: rgba(255,255,255,0.04);
            --row-hover: rgba(56,189,248,0.06);
            --sticky-bg: #1e293b;
            --sticky-bg-alt: #243349;
            --header-bg: linear-gradient(135deg, rgba(56,189,248,0.2), rgba(37,99,235,0.2));
            --btn-bg: rgba(255,255,255,0.08);
            --overlay-bg: rgba(0,0,0,0.6);
            --shadow-color: rgba(0,0,0,0.3);
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
            --date-scheme: dark;
            --excel-line: rgba(148,163,184,0.10);
            --excel-line-hover: rgba(148,163,184,0.18);
            --sticky-shadow: 2px 0 8px rgba(0,0,0,0.35);
        }

        [data-theme="light"] {
            --bg-deep: #eef2f7;
            --bg-dark: #ffffff;
            --bg-card: #ffffff;
            --glass-bg: rgba(255,255,255,0.82);
            --glass-border: rgba(15,23,42,0.1);
            --glass-border-hover: rgba(15,23,42,0.2);
            --input-bg: rgba(15,23,42,0.04);
            --row-bg-1: rgba(255,255,255,0.9);
            --row-bg-2: rgba(241,245,249,0.9);
            --row-hover: rgba(56,189,248,0.1);
            --sticky-bg: #ffffff;
            --sticky-bg-alt: #f1f5f9;
            --header-bg: linear-gradient(135deg, rgba(56,189,248,0.25), rgba(37,99,235,0.2));
            --btn-bg: rgba(15,23,42,0.06);
            --overlay-bg: rgba(100,116,139,0.35);
            --shadow-color: rgba(15,23,42,0.15);
            --text-primary: #0f172a;
            --text-secondary: #334155;
            --text-muted: #64748b;
            --text-dim: #94a3b8;
            --shadow-sm: 0 2px 8px rgba(15,23,42,0.08);
            --shadow-md: 0 8px 24px rgba(15,23,42,0.1);
            --shadow-lg: 0 16px 48px rgba(15,23,42,0.12);
            --shadow-xl: 0 24px 64px rgba(15,23,42,0.18);
            --date-scheme: light;
            --excel-line: rgba(15,23,42,0.08);
            --excel-line-hover: rgba(15,23,42,0.15);
            --sticky-shadow: 2px 0 8px rgba(15,23,42,0.12);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; -webkit-font-smoothing: antialiased; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-deep);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            transition: background-color 0.4s ease, color 0.4s ease;
        }
        body::before {
            content: "";
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(ellipse 80% 50% at 20% 0%, rgba(56,189,248,0.08), transparent),
                radial-gradient(ellipse 60% 40% at 80% 0%, rgba(167,139,250,0.06), transparent),
                radial-gradient(ellipse 50% 30% at 50% 100%, rgba(34,211,238,0.04), transparent);
        }
        [data-theme="light"] body::before {
            background:
                radial-gradient(ellipse 80% 50% at 20% 0%, rgba(56,189,248,0.12), transparent),
                radial-gradient(ellipse 60% 40% at 80% 0%, rgba(167,139,250,0.1), transparent),
                radial-gradient(ellipse 50% 30% at 50% 100%, rgba(34,211,238,0.08), transparent);
        }
        body::after {
            content: "";
            position: fixed; inset: 0; z-index: 0; pointer-events: none; opacity: 0.1;
            background-image: linear-gradient(rgba(255,255,255,0.01) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.01) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(120%); } to { opacity: 1; transform: translateX(0); } }
        @keyframes slideOutRight { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(120%); } }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes modalOut { from { opacity: 1; transform: scale(1) translateY(0); } to { opacity: 0; transform: scale(0.92) translateY(20px); } }
        @keyframes ripple { to { transform: scale(4); opacity: 0; } }
        @keyframes toastProgress { to { width: 0%; } }
        @keyframes glowPulse { 0%,100% { box-shadow: 0 0 5px var(--accent-blue-glow); } 50% { box-shadow: 0 0 20px var(--accent-blue-glow), 0 0 40px var(--accent-blue-glow); } }
        @keyframes scrollHint { 0%,100% { transform: translateX(0); } 50% { transform: translateX(8px); } }

        .loading-screen { position: fixed; inset: 0; background: var(--bg-deep); z-index: 9999; display: flex; align-items: center; justify-content: center; flex-direction: column; transition: opacity 0.5s, visibility 0.5s; }
        .loading-screen.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
        .loader { width: 50px; height: 50px; border: 3px solid var(--glass-border); border-top-color: var(--accent-blue); border-radius: 50%; animation: spin 1s linear infinite; }
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

        .header { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid var(--glass-border); transition: var(--transition-base), background-color 0.4s ease; }
        .header.scrolled { background: var(--glass-bg); box-shadow: var(--shadow-lg); }
        .header-inner { max-width: 1400px; margin: 0 auto; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .header-left { display: flex; align-items: center; gap: 16px; min-width: 0; }
        .menu-btn { width: 44px; height: 44px; flex-shrink: 0; border-radius: var(--radius-md); background: var(--btn-bg); border: 1px solid var(--glass-border); color: var(--text-primary); font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-base); }
        .menu-btn:hover { background: var(--btn-bg); border-color: var(--glass-border-hover); transform: scale(1.05); }
        .logo { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .logo-icon { width: 40px; height: 40px; flex-shrink: 0; background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan)); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px var(--accent-blue-glow); animation: glowPulse 3s ease-in-out infinite; }
        .logo-icon i { color: white; font-size: 18px; }
        .logo-text { font-size: 20px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px; white-space: nowrap; }
        .logo-text span { font-weight: 300; opacity: 0.7; }
        .header-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

        .theme-toggle { position: relative; overflow: hidden; }
        .theme-toggle .icon-sun, .theme-toggle .icon-moon { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; transition: transform 0.5s cubic-bezier(0.4,0,0.2,1), opacity 0.3s; }
        .theme-toggle .icon-sun { transform: translateY(0); opacity: 1; }
        .theme-toggle .icon-moon { transform: translateY(100%); opacity: 0; }
        [data-theme="light"] .theme-toggle .icon-sun { transform: translateY(-100%); opacity: 0; }
        [data-theme="light"] .theme-toggle .icon-moon { transform: translateY(0); opacity: 1; }

        .header-btn { width: 44px; height: 44px; border-radius: var(--radius-md); background: var(--btn-bg); border: 1px solid var(--glass-border); color: var(--text-primary); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-base); position: relative; overflow: hidden; }
        .header-btn::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent, rgba(255,255,255,0.15)); opacity: 0; transition: opacity var(--transition-base); }
        [data-theme="light"] .header-btn::before { background: linear-gradient(135deg, transparent, rgba(15,23,42,0.08)); }
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

        .sidebar-overlay { position: fixed; inset: 0; background: var(--overlay-bg); backdrop-filter: blur(5px); z-index: 2000; opacity: 0; visibility: hidden; transition: var(--transition-base); }
        .sidebar-overlay.active { opacity: 1; visibility: visible; }
        .sidebar { position: fixed; top: 0; left: -320px; width: 320px; height: 100%; background: var(--bg-card); z-index: 2001; transition: left 0.4s cubic-bezier(0.4,0,0.2,1), background-color 0.4s; overflow-y: auto; box-shadow: 10px 0 40px var(--shadow-color); }
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
        .page-title { font-size: clamp(26px, 4vw, 38px); font-weight: 900; color: var(--text-primary); line-height: 1.2; margin-bottom: 10px; letter-spacing: -1px; }
        .page-title .gradient-text { background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .page-subtitle { font-size: clamp(13px, 2vw, 15px); color: var(--text-muted); font-weight: 400; max-width: 600px; line-height: 1.6; }

        .glass-card { background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: var(--radius-xl); overflow: hidden; transition: var(--transition-base), background-color 0.4s; margin-bottom: 28px; animation: fadeInUp 0.8s ease-out both; }
        .glass-card:nth-child(2) { animation-delay: 0.1s; }
        .glass-card:nth-child(3) { animation-delay: 0.2s; }
        .glass-card:hover { border-color: var(--glass-border-hover); box-shadow: var(--shadow-lg); }
        .glass-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, var(--accent-blue), var(--accent-cyan), transparent); opacity: 0.5; border-radius: var(--radius-xl) var(--radius-xl) 0 0; }
        .card-header-glass { padding: 20px 24px; border-bottom: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; position: relative; }
        .card-header-title { display: flex; align-items: center; gap: 12px; color: var(--text-primary); font-size: 16px; font-weight: 700; }
        .card-header-title i { width: 36px; height: 36px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan)); display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 4px 15px var(--accent-blue-glow); }
        .card-body-glass { padding: 24px; }

        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; display: flex; align-items: center; gap: 6px; }
        .form-label i { font-size: 12px; color: var(--accent-blue); }
        .form-input { width: 100%; padding: 12px 16px; background: var(--input-bg); border: 1px solid var(--glass-border); border-radius: var(--radius-md); color: var(--text-primary); font-size: 14px; font-family: 'Inter', sans-serif; transition: var(--transition-base), background-color 0.4s; outline: none; }
        .form-input::placeholder { color: var(--text-dim); opacity: 0.6; }
        .form-input:focus { background: rgba(56,189,248,0.05); border-color: rgba(56,189,248,0.4); box-shadow: 0 0 0 3px rgba(56,189,248,0.1); }
        select.form-input { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
        select.form-input option { background: var(--bg-card); color: var(--text-primary); }
        textarea.form-input { min-height: 80px; resize: vertical; }
        input[type="date"].form-input { color-scheme: var(--date-scheme); }
        [data-theme="light"] input[type="date"].form-input::-webkit-calendar-picker-indicator { filter: none; opacity: 0.6; }
        input[type="date"].form-input::-webkit-calendar-picker-indicator { filter: invert(1); opacity: 0.6; cursor: pointer; }
        input[type="date"].form-input::-webkit-calendar-picker-indicator:hover { opacity: 1; }

        .btn-glass { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 24px; border-radius: var(--radius-md); font-size: 14px; font-weight: 700; font-family: 'Inter', sans-serif; cursor: pointer; border: none; transition: var(--transition-base); text-decoration: none; position: relative; overflow: hidden; }
        .btn-glass::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1)); opacity: 0; transition: opacity var(--transition-base); }
        .btn-glass:hover::after { opacity: 1; }
        .btn-glass:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-glass:active { transform: translateY(0); }
        .btn-glass i { position: relative; z-index: 1; font-size: 14px; transition: transform var(--transition-fast); }
        .btn-glass:hover i { transform: scale(1.15); }
        .btn-primary-glass { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; box-shadow: 0 4px 15px rgba(37,99,235,0.3); }
        .btn-success-glass { background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 15px rgba(5,150,105,0.3); }
        .btn-success-glass:hover { box-shadow: 0 8px 25px rgba(5,150,105,0.5); }
        .btn-danger-glass { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; box-shadow: 0 4px 15px rgba(220,38,38,0.3); padding: 8px 14px; font-size: 12px; border-radius: var(--radius-sm); }
        .btn-warning-glass { background: linear-gradient(135deg, #f59e0b, #d97706); color: #1e293b; box-shadow: 0 4px 15px rgba(217,119,6,0.3); padding: 8px 14px; font-size: 12px; border-radius: var(--radius-sm); text-decoration: none; border: none; cursor: pointer; }
        .btn-info-glass { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; box-shadow: 0 4px 15px rgba(2,132,199,0.3); padding: 8px 14px; font-size: 12px; border-radius: var(--radius-sm); text-decoration: none; border: none; cursor: pointer; }
        .btn-back-glass { background: var(--btn-bg); border: 1px solid var(--glass-border); color: var(--text-secondary); padding: 10px 18px; font-size: 13px; }
        .btn-back-glass:hover { background: var(--btn-bg); color: var(--text-primary); border-color: var(--glass-border-hover); }
        .btn-action-group { display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: nowrap; }

        .action-icon-btn { position: relative; width: 42px; height: 42px; padding: 0 !important; border-radius: var(--radius-md) !important; display: inline-flex !important; align-items: center; justify-content: center; gap: 7px; overflow: hidden; white-space: nowrap; transition: all 0.3s ease; }
        .action-icon-btn i { font-size: 15px; flex-shrink: 0; position: relative; z-index: 1; }
        .action-icon-btn .action-text { max-width: 0; opacity: 0; overflow: hidden; transform: translateX(-5px); transition: all 0.3s ease; font-size: 12px; font-weight: 600; position: relative; z-index: 1; }
        .action-icon-btn:hover { width: 92px; padding: 0 13px !important; transform: translateY(-2px); }
        .action-icon-btn:hover .action-text { max-width: 70px; opacity: 1; transform: translateX(0); }
        .action-icon-btn.disposal-btn:hover { width: 105px; }
        .action-icon-btn.hapus-btn:hover { width: 95px; }
        .action-icon-btn:active { transform: scale(0.95); }

        /* ============================================================
           EXCEL-LIKE TABLE - FREEZE PANES + RESPONSIVE
           ============================================================ */
        .table-excel-outer {
            position: relative;
            border-radius: var(--radius-lg);
            border: 1px solid var(--glass-border);
            overflow: hidden;
            background: var(--bg-card);
        }
        .table-excel-wrapper {
            overflow: auto;
            -webkit-overflow-scrolling: touch;
            max-height: 70vh;
            scrollbar-width: thin;
            scrollbar-color: var(--text-dim) transparent;
            position: relative;
        }
        .table-excel-wrapper::-webkit-scrollbar { width: 8px; height: 8px; }
        .table-excel-wrapper::-webkit-scrollbar-track { background: transparent; }
        .table-excel-wrapper::-webkit-scrollbar-thumb { background: var(--text-dim); border-radius: 50px; }
        .table-excel-wrapper::-webkit-scrollbar-thumb:hover { background: var(--accent-blue); }
        .table-excel-wrapper::-webkit-scrollbar-corner { background: transparent; }

        /* Shadow indicators for scroll */
        .table-shadow-left, .table-shadow-right {
            position: absolute; top: 0; bottom: 0; width: 40px; pointer-events: none;
            z-index: 10; transition: opacity 0.3s ease; opacity: 0;
        }
        .table-shadow-left {
            left: 0;
            background: linear-gradient(90deg, rgba(0,0,0,0.35), transparent);
        }
        .table-shadow-right {
            right: 0;
            background: linear-gradient(-90deg, rgba(0,0,0,0.35), transparent);
        }
        [data-theme="light"] .table-shadow-left { background: linear-gradient(90deg, rgba(15,23,42,0.12), transparent); }
        [data-theme="light"] .table-shadow-right { background: linear-gradient(-90deg, rgba(15,23,42,0.12), transparent); }
        .table-shadow-left.visible, .table-shadow-right.visible { opacity: 1; }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12px;
            min-width: 1600px;
        }

        /* === STICKY HEADER (Freeze Top Row) === */
        .data-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: var(--header-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: var(--text-primary);
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 10px;
            text-align: center;
            border-bottom: 2px solid rgba(56,189,248,0.3);
            border-right: 1px solid var(--excel-line);
            white-space: nowrap;
            transition: background-color 0.4s;
        }
        .data-table thead th:last-child { border-right: none; }

        /* === STICKY COLUMNS (Freeze First 2 Columns) === */
        .data-table thead th.col-sticky-1,
        .data-table tbody td.col-sticky-1 {
            position: sticky;
            left: 0;
            z-index: 4;
            background: var(--sticky-bg);
            box-shadow: var(--sticky-shadow);
            border-right: 1px solid var(--excel-line);
            min-width: 50px;
            max-width: 50px;
            width: 50px;
        }
        .data-table thead th.col-sticky-1 { z-index: 5; }
        .data-table tbody tr:nth-child(even) td.col-sticky-1 { background: var(--sticky-bg-alt); }

        .data-table thead th.col-sticky-2,
        .data-table tbody td.col-sticky-2 {
            position: sticky;
            left: 50px;
            z-index: 4;
            background: var(--sticky-bg);
            box-shadow: var(--sticky-shadow);
            border-right: 1px solid var(--excel-line);
            min-width: 90px;
            max-width: 90px;
            width: 90px;
        }
        .data-table thead th.col-sticky-2 { z-index: 5; }
        .data-table tbody tr:nth-child(even) td.col-sticky-2 { background: var(--sticky-bg-alt); }

        /* === BODY CELLS (Excel Grid Lines) === */
        .data-table tbody tr {
            background: var(--row-bg-1);
            transition: var(--transition-fast), background-color 0.4s;
            animation: fadeInUp 0.4s ease both;
        }
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
        .data-table tbody tr:nth-child(even) { background: var(--row-bg-2); }
        .data-table tbody tr:hover { background: var(--row-hover); }
        .data-table tbody tr:hover td { border-bottom-color: var(--excel-line-hover); }
        .data-table tbody tr:hover td.col-sticky-1,
        .data-table tbody tr:hover td.col-sticky-2 { background: var(--row-hover); }

        .data-table tbody td {
            padding: 14px 10px;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--excel-line);
            border-right: 1px solid var(--excel-line);
            vertical-align: middle;
            text-align: center;
            font-size: 12px;
            white-space: nowrap;
            transition: var(--transition-fast), background-color 0.4s, border-color 0.4s;
        }
        .data-table tbody td:last-child { border-right: none; }
        .data-table tbody tr:hover td { color: var(--text-primary); }

        .data-table tbody tr:last-child td:first-child { border-radius: 0 0 0 var(--radius-md); }
        .data-table tbody tr:last-child td:last-child { border-radius: 0 0 var(--radius-md) 0; }

        .td-center { text-align: center; }
        .td-small { font-size: 11px; color: var(--text-dim); }
        .td-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 50px;
            font-size: 10px; font-weight: 700; letter-spacing: 0.3px; white-space: nowrap;
        }
        .badge-cyan { background: rgba(6,182,212,0.15); color: #22b8d3; border: 1px solid rgba(6,182,212,0.25); }
        [data-theme="dark"] .badge-cyan { color: #67e8f9; }
        .badge-green { background: rgba(16,185,129,0.15); color: #059669; border: 1px solid rgba(16,185,129,0.25); }
        [data-theme="dark"] .badge-green { color: #34d399; }
        .badge-purple { background: rgba(99,102,241,0.15); color: #6366f1; border: 1px solid rgba(99,102,241,0.25); }
        [data-theme="dark"] .badge-purple { color: #a5b4fc; }
        .badge-pink { background: rgba(236,72,153,0.15); color: #db2777; border: 1px solid rgba(236,72,153,0.25); }
        [data-theme="dark"] .badge-pink { color: #f472b6; }
        .badge-orange { background: rgba(249,115,22,0.15); color: #ea580c; border: 1px solid rgba(249,115,22,0.25); }
        [data-theme="dark"] .badge-orange { color: #fb923c; }
        .badge-blue { background: rgba(59,130,246,0.15); color: #2563eb; border: 1px solid rgba(59,130,246,0.25); }
        [data-theme="dark"] .badge-blue { color: #93c5fd; }

        /* Column width control for Excel feel */
        .data-table th, .data-table td { min-width: 80px; }
        .data-table th:nth-child(3), .data-table td:nth-child(3) { min-width: 55px; }
        .data-table th:nth-child(4), .data-table td:nth-child(4) { min-width: 100px; }
        .data-table th:nth-child(5), .data-table td:nth-child(5) { min-width: 110px; }
        .data-table th:nth-child(6), .data-table td:nth-child(6) { min-width: 120px; }
        .data-table th:nth-child(7), .data-table td:nth-child(7) { min-width: 90px; }
        .data-table th:nth-child(8), .data-table td:nth-child(8) { min-width: 130px; }
        .data-table th:nth-child(9), .data-table td:nth-child(9) { min-width: 110px; }
        .data-table th:nth-child(10), .data-table td:nth-child(10) { min-width: 80px; }
        .data-table th:nth-child(11), .data-table td:nth-child(11) { min-width: 90px; }
        .data-table th:nth-child(12), .data-table td:nth-child(12) { min-width: 110px; }
        .data-table th:nth-child(13), .data-table td:nth-child(13) { min-width: 70px; }
        .data-table th:nth-child(14), .data-table td:nth-child(14) { min-width: 90px; }
        .data-table th:nth-child(15), .data-table td:nth-child(15) { min-width: 110px; }
        .data-table th:nth-child(16), .data-table td:nth-child(16) { min-width: 110px; }
        .data-table th:nth-child(17), .data-table td:nth-child(17) { min-width: 65px; }
        .data-table th:nth-child(18), .data-table td:nth-child(18) { min-width: 80px; }
        .data-table th:nth-child(19), .data-table td:nth-child(19) { min-width: 160px; }

        .empty-state { text-align: center; padding: 60px 20px; animation: fadeInUp 0.5s ease; }
        .empty-state i { font-size: 48px; margin-bottom: 16px; color: var(--text-dim); display: block; animation: float 3s ease-in-out infinite; }
        .empty-state h4 { color: var(--text-muted); font-size: 16px; margin-bottom: 8px; font-weight: 700; }
        .empty-state p { color: var(--text-dim); font-size: 13px; }

        .modal-overlay { position: fixed; inset: 0; background: var(--overlay-bg); backdrop-filter: blur(12px); z-index: 3000; opacity: 0; visibility: hidden; transition: var(--transition-base); display: flex; align-items: center; justify-content: center; padding: 20px; }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-content { background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-xl); width: 100%; max-width: 760px; max-height: 90vh; overflow-y: auto; transform: translateY(30px) scale(0.95); transition: var(--transition-slow), background-color 0.4s; box-shadow: var(--shadow-xl); position: relative; }
        .modal-overlay.active .modal-content { transform: translateY(0) scale(1); }
        .modal-header { padding: 20px 24px; border-bottom: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: space-between; position: relative; overflow: hidden; }
        .modal-header::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; }
        .modal-header.ganti::before { background: linear-gradient(90deg, var(--accent-blue), var(--accent-cyan)); }
        .modal-header.disposal::before { background: linear-gradient(90deg, var(--accent-red), #fca5a5); }
        .modal-header.edit::before { background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple)); }
        .modal-title { display: flex; align-items: center; gap: 12px; color: var(--text-primary); font-size: 18px; font-weight: 800; }
        .modal-title i { width: 36px; height: 36px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .modal-title i.icon-ganti { background: linear-gradient(135deg, var(--accent-blue), var(--accent-cyan)); box-shadow: 0 4px 15px var(--accent-blue-glow); }
        .modal-title i.icon-disposal { background: linear-gradient(135deg, var(--accent-red), #dc2626); box-shadow: 0 4px 15px var(--accent-red-glow); }
        .modal-title i.icon-edit { background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); box-shadow: 0 4px 15px var(--accent-blue-glow); }
        .modal-close { width: 36px; height: 36px; border-radius: var(--radius-md); background: var(--btn-bg); border: 1px solid var(--glass-border); color: var(--text-muted); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition-base); }
        .modal-close:hover { background: rgba(239,68,68,0.2); border-color: rgba(239,68,68,0.4); color: var(--accent-red); transform: rotate(90deg); }
        .modal-body { padding: 24px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--glass-border); display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap; }
        .info-box { background: var(--input-bg); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px; }
        .info-box p { color: var(--text-muted); font-size: 13px; line-height: 1.6; margin: 0; }
        .info-box strong { color: var(--text-primary); }
        .info-box i { margin-right: 6px; }

        .ganti-option, .disposal-option { display: flex; align-items: center; gap: 10px; cursor: pointer; color: var(--text-secondary); font-size: 14px; padding: 12px 16px; background: var(--input-bg); border: 1px solid var(--glass-border); border-radius: var(--radius-md); transition: var(--transition-base); font-weight: 600; }
        .ganti-option:hover { border-color: rgba(56,189,248,0.4); background: rgba(56,189,248,0.08); color: var(--text-primary); }
        .disposal-option:hover { border-color: rgba(239,68,68,0.4); background: rgba(239,68,68,0.08); color: var(--text-primary); }
        .ganti-option input, .disposal-option input { accent-color: var(--accent-blue); width: 18px; height: 18px; cursor: pointer; flex-shrink: 0; }
        .disposal-option input { accent-color: var(--accent-red); }
        .ganti-option i { color: var(--accent-blue); font-size: 16px; }
        .disposal-option i { color: var(--accent-red); font-size: 16px; }

        .footer { position: relative; z-index: 1; text-align: center; padding: 30px 24px; color: var(--text-dim); font-size: 13px; font-weight: 500; }
        .footer i { color: var(--accent-red); animation: pulse 2s ease-in-out infinite; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--glass-border-hover); border-radius: 50px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-dim); }

        /* ============================================================
           RESPONSIVE BREAKPOINTS
           ============================================================ */
        @media (min-width: 1400px) {
            .header-inner, .breadcrumb-inner, .container { padding-left: 32px; padding-right: 32px; }
            .data-table { min-width: 1700px; }
        }

        @media (max-width: 1199px) {
            .form-grid { grid-template-columns: repeat(2, 1fr); }
            .data-table { min-width: 1500px; }
            .action-icon-btn:hover { width: 42px; padding: 0 !important; transform: none; }
            .action-icon-btn:hover .action-text { display: none; }
            .action-icon-btn.disposal-btn:hover, .action-icon-btn.hapus-btn:hover { width: 42px; }
        }

        @media (max-width: 991px) {
            .header-inner { padding: 12px 20px; }
            .card-header-glass { padding: 16px 20px; }
            .card-body-glass { padding: 20px; }
            .data-table { min-width: 1400px; font-size: 11px; }
            .data-table thead th, .data-table tbody td { padding: 12px 8px; }
            .data-table thead th { font-size: 9px; }
            .page-title { font-size: 28px; }
            .modal-content { max-width: 95%; }
            .logo-text { font-size: 18px; }
            .btn-action-group { gap: 6px; }
            .action-icon-btn { width: 38px; height: 38px; }
        }

        /* Tablet & HP: tetap tabel Excel-like, hanya lebih compact */
        @media (max-width: 767px) {
            .container { padding: 0 16px; }
            .header-inner { padding: 10px 14px; gap: 6px; }
            .logo { gap: 8px; }
            .logo-icon { width: 36px; height: 36px; }
            .logo-text { font-size: 15px; }
            .menu-btn, .header-btn { width: 40px; height: 40px; }
            .header-right { gap: 6px; }
            .breadcrumb-bar { padding-top: 80px; }
            .breadcrumb-inner { padding: 0 16px; }
            .form-grid { grid-template-columns: 1fr; }
            .card-header-glass { padding: 16px; }
            .card-body-glass { padding: 16px; }
            .page-title { font-size: 24px; }
            .btn-glass { padding: 10px 18px; font-size: 13px; }
            .data-table { min-width: 1200px; font-size: 10px; }
            .data-table thead th, .data-table tbody td { padding: 10px 6px; }
            .data-table thead th { font-size: 8px; letter-spacing: 0.3px; }
            .td-badge { padding: 3px 8px; font-size: 9px; }
            .action-icon-btn { width: 34px; height: 34px; }
            .action-icon-btn i { font-size: 13px; }
            .sidebar { width: 280px; }
            .modal-content { max-height: 85vh; }
            .modal-body { padding: 16px; }
        }

        @media (max-width: 575px) {
            .breadcrumb-bar { padding-top: 75px; }
            .page-header { margin-bottom: 24px; }
            .page-badge { font-size: 11px; padding: 6px 12px; }
            .page-title { font-size: 20px; }
            .page-subtitle { font-size: 12px; }
            .card-header-title { font-size: 14px; }
            .card-header-title i { width: 32px; height: 32px; font-size: 14px; }
            .form-input { padding: 10px 14px; font-size: 13px; }
            .btn-action-group { flex-wrap: wrap; gap: 4px; }
            .modal-footer { justify-content: stretch; }
            .modal-footer .btn-glass { flex: 1; justify-content: center; }
            .footer { padding: 20px 16px; font-size: 11px; }
            .data-table { min-width: 1100px; }
            .data-table thead th, .data-table tbody td { padding: 8px 5px; }
        }
    </style>
</head>

<body>
    <div class="loading-screen" id="loadingScreen">
        <div class="loader"></div>
        <div class="loading-text">MEMUAT KARTIKA 2...</div>
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
                <a href="inventaris.php" class="sidebar-item active" onclick="toggleSidebar()"><i class="fas fa-warehouse"></i> Data Inventaris</a>
                <a href="kegiatan.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-clipboard-list"></i> Data Kegiatan</a>
                <a href="jaringan.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-network-wired"></i> Data Jaringan</a>
                <a href="laporan.php" class="sidebar-item" onclick="toggleSidebar()"><i class="fas fa-chart-pie"></i> Data Laporan</a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title">Pengaturan</div>
                <a href="#" class="sidebar-item" onclick="toggleTheme(); toggleSidebar();"><i class="fas fa-circle-half-stroke"></i> Ganti Tema</a>
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
                <button class="header-btn theme-toggle" onclick="toggleTheme()" title="Ganti Tema Gelap / Terang" aria-label="Ganti tema">
                    <span class="icon-sun"><i class="fas fa-sun"></i></span>
                    <span class="icon-moon"><i class="fas fa-moon"></i></span>
                </button>
                <button class="header-btn" onclick="showToast('Fitur pencarian akan segera hadir!', 'info')" title="Cari"><i class="fas fa-magnifying-glass"></i></button>
                <button class="header-btn" onclick="location.reload()" title="Refresh"><i class="fas fa-rotate"></i></button>
                <button class="header-btn" onclick="showToast('Tidak ada notifikasi baru', 'info')" title="Notifikasi"><i class="fas fa-bell"></i></button>
            </div>
        </div>
    </header>

    <div class="breadcrumb-bar">
        <div class="breadcrumb-inner">
            <div class="breadcrumb">
                <a href="index_modern.php"><i class="fas fa-house"></i> Beranda</a>
                <i class="fas fa-chevron-right"></i>
                <a href="inventaris.php">Data Inventaris</a>
                <i class="fas fa-chevron-right"></i>
                <span class="breadcrumb-current">Kartika 2</span>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="container">
            <section class="page-header">
                <div class="page-badge"><i class="fas fa-door-open"></i> Ruangan Kartika 2</div>
                <h1 class="page-title">Data Inventaris <span class="gradient-text">Hardware & PC EDP</span></h1>
                <p class="page-subtitle">Input dan kelola data inventaris komputer, monitor, printer, dan perangkat EDP ruangan Kartika 2.</p>
            </section>

            <!-- Form Input -->
            <div class="glass-card">
                <div class="card-header-glass">
                    <div class="card-header-title"><i class="fas fa-circle-plus"></i> Tambah Data Inventaris</div>
                    <a href="inventaris.php" class="btn-glass btn-back-glass"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
                <div class="card-body-glass">
                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-building"></i> Gedung</label>
                                <select name="gedung" class="form-input" required>
                                    <option value="" disabled selected>Pilih Gedung</option>
                                    <option value="Kartika 1">Kartika 1</option>
                                    <option value="Kartika 2">Kartika 2</option>
                                    <option value="Kartika 3">Kartika 3</option>
                                    <option value="CVC">CVC</option>
                                    <option value="CICU">CICU</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-layer-group"></i> Lantai</label>
                                <select name="lantai" class="form-input" required>
                                    <option value="" disabled selected>Pilih Lantai</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-door-open"></i> Ruangan</label>
                                <input type="text" name="ruangan" class="form-input" placeholder="Contoh: Ruang 101" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-desktop"></i> Nama PC</label>
                                <input type="text" name="nama_pc" class="form-input" placeholder="Contoh: PC-EDP-01" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-user"></i> Nama User</label>
                                <input type="text" name="nama_user" class="form-input" placeholder="Contoh: Budi Santoso" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-industry"></i> Manufactur</label>
                                <input type="text" name="manufactur" class="form-input" placeholder="Contoh: Dell, HP, Lenovo">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-microchip"></i> Processor Type</label>
                                <input type="text" name="processor_type" class="form-input" placeholder="Contoh: Intel Core i5-10400">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-windows"></i> Windows Version</label>
                                <input type="text" name="windows_version" class="form-input" placeholder="Contoh: Windows 10 Pro">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-memory"></i> RAM Size</label>
                                <input type="text" name="ram_size" class="form-input" placeholder="Contoh: 8GB DDR4">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-hard-drive"></i> Hardisk</label>
                                <input type="text" name="ram_hardisk" class="form-input" placeholder="Contoh: 512GB SSD">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-display"></i> Monitor Model</label>
                                <input type="text" name="monitor_model" class="form-input" placeholder="Contoh: Dell P2419H">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-ruler"></i> Monitor Size</label>
                                <input type="text" name="monitor_size" class="form-input" placeholder="Contoh: 24 Inch">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-plug"></i> Monitor VGA</label>
                                <input type="text" name="monitor_vga" class="form-input" placeholder="Contoh: HDMI, DisplayPort">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-print"></i> Printer 1</label>
                                <input type="text" name="printer_1" class="form-input" placeholder="Contoh: Canon PIXMA">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-print"></i> Printer 2</label>
                                <input type="text" name="printer_2" class="form-input" placeholder="Contoh: Epson L3110">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-calendar-days"></i> Tahun Pengadaan</label>
                                <input type="date" name="tahun_pengadaan" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-id-badge"></i> ID Personil EDP</label>
                                <input type="text" name="id_personil_edp" class="form-input" placeholder="Contoh: EDP-001">
                            </div>
                            <div class="form-group" style="display:flex; align-items:flex-end;">
                                <button type="submit" class="btn-glass btn-success-glass" style="width:100%;"><i class="fas fa-floppy-disk"></i> Simpan Data</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data Excel-Like -->
            <div class="glass-card">
                <div class="card-header-glass">
                    <div class="card-header-title"><i class="fas fa-table-list"></i> Daftar Inventaris Kartika 2</div>
                    <span class="td-badge badge-cyan"><i class="fas fa-database" style="margin-right:4px;"></i>
                        <?php
                        $count = mysqli_query($conn, "SELECT COUNT(*) as total FROM inventaris_kartika2");
                        $total = mysqli_fetch_assoc($count)['total'];
                        echo $total . ' Data';
                        ?>
                    </span>
                </div>
                <div class="card-body-glass">
                    <div class="table-excel-outer">
                        <div class="table-shadow-left" id="tableShadowLeft"></div>
                        <div class="table-shadow-right" id="tableShadowRight"></div>
                        <div class="table-excel-wrapper" id="tableExcelWrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th class="col-sticky-1">No</th>
                                        <th class="col-sticky-2">Gedung</th>
                                        <th>Lantai</th>
                                        <th>Ruangan</th>
                                        <th>Nama PC</th>
                                        <th>Nama User</th>
                                        <th>Manufactur</th>
                                        <th>Processor</th>
                                        <th>Windows</th>
                                        <th>RAM</th>
                                        <th>Hardisk</th>
                                        <th>Monitor</th>
                                        <th>Size</th>
                                        <th>VGA</th>
                                        <th>Printer 1</th>
                                        <th>Printer 2</th>
                                        <th>Tahun</th>
                                        <th>EDP ID</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $no = 1;
                                    $query = mysqli_query($conn, "SELECT * FROM inventaris_kartika2 ORDER BY id DESC");
                                    if (mysqli_num_rows($query) > 0) {
                                        while ($row = mysqli_fetch_assoc($query)) {
                                    ?>
                                    <tr>
                                        <td class="col-sticky-1 td-center"><span class="td-badge badge-purple"><i class="fas fa-hashtag" style="font-size:9px;"></i> <?= $no++; ?></span></td>
                                        <td class="col-sticky-2"><span class="td-badge badge-cyan"><i class="fas fa-building" style="font-size:9px;"></i> <?= htmlspecialchars($row['gedung']); ?></span></td>
                                        <td><?= htmlspecialchars($row['lantai']); ?></td>
                                        <td><span class="td-badge badge-pink"><i class="fas fa-door-open" style="font-size:9px;"></i> <?= htmlspecialchars($row['ruangan']); ?></span></td>
                                        <td><strong style="color:var(--text-primary);"><?= htmlspecialchars($row['nama_pc']); ?></strong></td>
                                        <td><i class="fas fa-user" style="color:var(--accent-green);margin-right:4px;font-size:10px;"></i><?= htmlspecialchars($row['nama_user']); ?></td>
                                        <td><?= htmlspecialchars($row['manufactur']); ?></td>
                                        <td><span class="td-small"><i class="fas fa-microchip" style="margin-right:4px;"></i><?= htmlspecialchars($row['processor_type']); ?></span></td>
                                        <td><span class="td-badge badge-green"><i class="fas fa-windows" style="font-size:9px;"></i> <?= htmlspecialchars($row['windows_version']); ?></span></td>
                                        <td><span class="td-badge badge-cyan"><?= htmlspecialchars($row['ram_size']); ?></span></td>
                                        <td><span class="td-badge badge-green"><?= htmlspecialchars($row['ram_hardisk']); ?></span></td>
                                        <td><?= htmlspecialchars($row['monitor_model']); ?></td>
                                        <td><?= htmlspecialchars($row['monitor_size']); ?></td>
                                        <td><span class="td-small"><i class="fas fa-plug" style="margin-right:4px;"></i><?= htmlspecialchars($row['monitor_vga']); ?></span></td>
                                        <td><?= $row['printer_1'] ? '<i class="fas fa-print" style="color:var(--accent-amber);margin-right:4px;font-size:10px;"></i>' . htmlspecialchars($row['printer_1']) : '-'; ?></td>
                                        <td><?= $row['printer_2'] ? '<i class="fas fa-print" style="color:var(--accent-amber);margin-right:4px;font-size:10px;"></i>' . htmlspecialchars($row['printer_2']) : '-'; ?></td>
                                        <td class="td-center"><span class="td-badge badge-orange"><i class="fas fa-calendar" style="font-size:9px;"></i> <?= htmlspecialchars($row['tahun_pengadaan']); ?></span></td>
                                        <td class="td-center"><span class="td-badge badge-blue"><?= htmlspecialchars($row['id_personil_edp']); ?></span></td>
                                        <td>
                                            <div class="btn-action-group">
                                                <button type="button" class="btn-glass btn-warning-glass action-icon-btn" onclick="openEditModal(<?= $row['id']; ?>)" title="Edit Data">
                                                    <i class="fas fa-pen-to-square"></i><span class="action-text">Edit</span>
                                                </button>
                                                <button type="button" class="btn-glass btn-info-glass action-icon-btn" onclick="openGantiModal(<?= $row['id']; ?>)" title="Perbaikan / Pindah ke Daftar Perbaikan">
                                                    <i class="fas fa-warehouse"></i><span class="action-text">Perbaikan</span>
                                                </button>
                                                <button type="button" class="btn-glass btn-danger-glass action-icon-btn disposal-btn" onclick="openDisposalModal(<?= $row['id']; ?>)" title="Disposal">
                                                    <i class="fas fa-recycle"></i><span class="action-text">Disposal</span>
                                                </button>
                                                <a href="?hapus=<?= $row['id']; ?>" class="btn-glass btn-danger-glass action-icon-btn hapus-btn" style="background: linear-gradient(135deg, #7f1d1d, #991b1b); box-shadow: 0 4px 15px rgba(153,27,27,0.3);" onclick="return confirm('Yakin ingin menghapus data ini? Data akan dihapus permanen dari inventaris.')" title="Hapus Data">
                                                    <i class="fas fa-trash"></i><span class="action-text">Hapus</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } } else { ?>
                                    <tr>
                                        <td colspan="19">
                                            <div class="empty-state">
                                                <i class="fas fa-box-open"></i>
                                                <h4>Belum Ada Data</h4>
                                                <p>Silakan tambah data inventaris menggunakan form di atas.</p>
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
        </div>
    </main>

    <!-- MODAL EDIT -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-content">
            <div class="modal-header edit">
                <div class="modal-title"><i class="fas fa-pen-to-square icon-edit"></i> Edit Data Inventaris</div>
                <button class="modal-close" onclick="closeEditModal()"><i class="fas fa-xmark"></i></button>
            </div>
            <form method="POST" action="" id="editForm">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-building"></i> Gedung</label>
                            <select name="gedung" id="edit_gedung" class="form-input" required>
                                <option value="" disabled>Pilih Gedung</option>
                                <option value="Kartika 1">Kartika 1</option>
                                <option value="Kartika 2">Kartika 2</option>
                                <option value="Kartika 3">Kartika 3</option>
                                <option value="CVC">CVC</option>
                                <option value="CICU">CICU</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-layer-group"></i> Lantai</label>
                            <select name="lantai" id="edit_lantai" class="form-input" required>
                                <option value="" disabled>Pilih Lantai</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-door-open"></i> Ruangan</label><input type="text" name="ruangan" id="edit_ruangan" class="form-input" required></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-desktop"></i> Nama PC</label><input type="text" name="nama_pc" id="edit_nama_pc" class="form-input" required></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-user"></i> Nama User</label><input type="text" name="nama_user" id="edit_nama_user" class="form-input" required></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-industry"></i> Manufactur</label><input type="text" name="manufactur" id="edit_manufactur" class="form-input"></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-microchip"></i> Processor Type</label><input type="text" name="processor_type" id="edit_processor_type" class="form-input"></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-windows"></i> Windows Version</label><input type="text" name="windows_version" id="edit_windows_version" class="form-input"></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-memory"></i> RAM Size</label><input type="text" name="ram_size" id="edit_ram_size" class="form-input"></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-hard-drive"></i> Hardisk</label><input type="text" name="ram_hardisk" id="edit_ram_hardisk" class="form-input"></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-display"></i> Monitor Model</label><input type="text" name="monitor_model" id="edit_monitor_model" class="form-input"></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-ruler"></i> Monitor Size</label><input type="text" name="monitor_size" id="edit_monitor_size" class="form-input"></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-plug"></i> Monitor VGA</label><input type="text" name="monitor_vga" id="edit_monitor_vga" class="form-input"></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-print"></i> Printer 1</label><input type="text" name="printer_1" id="edit_printer_1" class="form-input"></div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-print"></i> Printer 2</label><input type="text" name="printer_2" id="edit_printer_2" class="form-input"></div>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-calendar-days"></i> Tahun Pengadaan</label>
                            <input type="date" name="tahun_pengadaan" id="edit_tahun_pengadaan" class="form-input">
                        </div>
                        <div class="form-group"><label class="form-label"><i class="fas fa-id-badge"></i> ID Personil EDP</label><input type="text" name="id_personil_edp" id="edit_id_personil_edp" class="form-input"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-glass btn-back-glass" onclick="closeEditModal()"><i class="fas fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-glass btn-success-glass"><i class="fas fa-floppy-disk"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL GANTI -->
    <div class="modal-overlay" id="gantiModal">
        <div class="modal-content">
            <div class="modal-header ganti">
                <div class="modal-title"><i class="fas fa-warehouse icon-ganti"></i> Perbaikan Barang - Pindah ke Daftar Perbaikan</div>
                <button class="modal-close" onclick="closeGantiModal()"><i class="fas fa-xmark"></i></button>
            </div>
            <form method="POST" action="" id="gantiForm">
                <input type="hidden" name="ganti_id" id="ganti_id">
                <div class="modal-body">
                    <div class="info-box" style="border-color: rgba(56,189,248,0.3);">
                        <p><i class="fas fa-circle-info" style="color:var(--accent-blue);"></i>
                        <strong>Perhatian!</strong> Pilih barang yang akan <strong>dipindahkan ke Perbaikan</strong>. Barang lain tetap tinggal di inventaris aktif.</p>
                    </div>
                    <div class="form-grid" style="grid-template-columns: 1fr;">
                        <div class="form-group">
                            <label class="form-label">Pilih Barang <span style="color:var(--accent-red)">*</span></label>
                            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                                <label class="ganti-option"><input type="radio" name="tipe_ganti" value="pc" checked><i class="fas fa-desktop"></i> PC Saja</label>
                                <label class="ganti-option"><input type="radio" name="tipe_ganti" value="printer1"><i class="fas fa-print"></i> Printer 1</label>
                                <label class="ganti-option"><input type="radio" name="tipe_ganti" value="printer2"><i class="fas fa-print"></i> Printer 2</label>
                                <label class="ganti-option"><input type="radio" name="tipe_ganti" value="semua"><i class="fas fa-box"></i> PC + Semua Printer</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alasan Perbaikan <span style="color:var(--accent-red)">*</span></label>
                            <textarea name="alasan_ganti" id="ganti_alasan" class="form-input" placeholder="Contoh: Upgrade ke PC baru, kerusakan hardware, dll." required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status di Daftar Perbaikan</label>
                            <select name="status_gudang" class="form-input">
                                <option value="Stok">Stok (Siap dipakai kembali)</option>
                                <option value="Perbaikan">Perbaikan (Butuh perbaikan)</option>
                                <option value="Siap Pakai">Siap Pakai (Langsung bisa dipakai)</option>
                                <option value="Rusak">Rusak (Tidak bisa dipakai)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-glass btn-back-glass" onclick="closeGantiModal()"><i class="fas fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-glass btn-info-glass" onclick="return confirm('Yakin ingin memindahkan barang yang dipilih ke gudang?')"><i class="fas fa-warehouse"></i> Pindahkan ke Daftar Perbaikan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DISPOSAL -->
    <div class="modal-overlay" id="disposalModal">
        <div class="modal-content">
            <div class="modal-header disposal">
                <div class="modal-title"><i class="fas fa-recycle icon-disposal"></i> Disposal Barang</div>
                <button class="modal-close" onclick="closeDisposalModal()"><i class="fas fa-xmark"></i></button>
            </div>
            <form method="POST" action="" id="disposalForm">
                <input type="hidden" name="disposal_id" id="disposal_id">
                <div class="modal-body">
                    <div class="info-box" style="border-color: rgba(239,68,68,0.3);">
                        <p><i class="fas fa-triangle-exclamation" style="color:var(--accent-red);"></i>
                        <strong>Perhatian!</strong> Pilih barang yang akan <strong>dipindahkan ke Tabel Disposal</strong>. Barang lain tetap tinggal di inventaris aktif.</p>
                    </div>
                    <div class="form-grid" style="grid-template-columns: 1fr;">
                        <div class="form-group">
                            <label class="form-label">Pilih Barang <span style="color:var(--accent-red)">*</span></label>
                            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                                <label class="disposal-option"><input type="radio" name="tipe_disposal" value="pc" checked><i class="fas fa-desktop"></i> PC Saja</label>
                                <label class="disposal-option"><input type="radio" name="tipe_disposal" value="printer1"><i class="fas fa-print"></i> Printer 1</label>
                                <label class="disposal-option"><input type="radio" name="tipe_disposal" value="printer2"><i class="fas fa-print"></i> Printer 2</label>
                                <label class="disposal-option"><input type="radio" name="tipe_disposal" value="semua"><i class="fas fa-box"></i> PC + Semua Printer</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alasan Disposal <span style="color:var(--accent-red)">*</span></label>
                            <textarea name="alasan_disposal" id="disposal_alasan" class="form-input" placeholder="Contoh: Barang rusak total, Sudah tidak layak pakai, Obsolete, dll." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-glass btn-back-glass" onclick="closeDisposalModal()"><i class="fas fa-xmark"></i> Batal</button>
                    <button type="submit" class="btn-glass btn-danger-glass" onclick="return confirm('Yakin ingin melakukan disposal? Data yang dipilih akan dipindahkan ke tabel disposal.')"><i class="fas fa-recycle"></i> Proses Disposal</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p><i class="fas fa-copyright" style="color:var(--text-dim);animation:none;"></i> <?php echo date('Y'); ?> Inventaris Digital. Dibangun dengan <i class="fas fa-heart"></i> untuk Indonesia.</p>
    </footer>

    <script>
        var inventarisData = [
<?php
$query_js = mysqli_query($conn, "SELECT * FROM inventaris_kartika2 ORDER BY id DESC");
$js_rows = [];
while ($r = mysqli_fetch_assoc($query_js)) {
    $js_rows[] = json_encode($r);
}
echo implode(",
", $js_rows);
?>
];

        // Loading Screen
        window.addEventListener('load', function() {
            setTimeout(function() { document.getElementById('loadingScreen').classList.add('hidden'); }, 800);
            initExcelTable();
        });

        // ============ DARK / LIGHT MODE ============
        var THEME_KEY = 'kartika2-theme';
        function getTheme() { return document.documentElement.getAttribute('data-theme') || 'dark'; }
        function applyMetaTheme(theme) {
            var meta = document.getElementById('metaThemeColor');
            if (meta) meta.setAttribute('content', theme === 'light' ? '#eef2f7' : '#0b1120');
        }
        function toggleTheme() {
            var newTheme = getTheme() === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            try { localStorage.setItem(THEME_KEY, newTheme); } catch(e) {}
            applyMetaTheme(newTheme);
            showToast('Tema ' + (newTheme === 'light' ? 'Terang' : 'Gelap') + ' aktif', 'info');
        }
        (function() { applyMetaTheme(getTheme()); })();

        // Toast Notifications
        function showToast(message, type) {
            type = type || 'success';
            var container = document.getElementById('toastContainer');
            var toast = document.createElement('div');
            toast.className = 'toast ' + type;
            var icon = 'fa-circle-check', title = 'Berhasil';
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
                setTimeout(function() { if (toast.parentElement) toast.remove(); }, 400);
            }, 5000);
        }

        // Check URL params for success messages
        (function() {
            var params = new URLSearchParams(window.location.search);
            var success = params.get('success');
            if (success === 'ganti') showToast('Barang berhasil dipindahkan ke gudang!', 'success');
            if (success === 'disposal') showToast('Barang berhasil diproses disposal!', 'success');
            if (window.history.replaceState && success) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        })();

        // Sidebar
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }

        // Header scroll effect
        window.addEventListener('scroll', function() {
            var header = document.getElementById('header');
            if (window.scrollY > 20) header.classList.add('scrolled');
            else header.classList.remove('scrolled');
        }, { passive: true });

        // ============ EXCEL TABLE - SHADOW INDICATORS + STICKY ============
        function initExcelTable() {
            var wrapper = document.getElementById('tableExcelWrapper');
            var shadowLeft = document.getElementById('tableShadowLeft');
            var shadowRight = document.getElementById('tableShadowRight');
            if (!wrapper || !shadowLeft || !shadowRight) return;

            function updateShadows() {
                var scrollLeft = wrapper.scrollLeft;
                var maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
                shadowLeft.classList.toggle('visible', scrollLeft > 5);
                shadowRight.classList.toggle('visible', scrollLeft < maxScroll - 5);
            }

            wrapper.addEventListener('scroll', updateShadows, { passive: true });
            window.addEventListener('resize', updateShadows);
            // Initial check after a short delay to ensure layout is done
            setTimeout(updateShadows, 300);
            setTimeout(updateShadows, 1000);
        }

        // Modals
        function openModal(id) {
            var modal = document.getElementById(id);
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            var modal = document.getElementById(id);
            var content = modal.querySelector('.modal-content');
            content.style.animation = 'modalOut 0.25s ease forwards';
            setTimeout(function() {
                modal.classList.remove('active');
                content.style.animation = '';
                document.body.style.overflow = '';
            }, 250);
        }

        function openEditModal(id) {
            var data = inventarisData.find(function(item) { return item.id == id; });
            if (!data) return;
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_gedung').value = data.gedung || '';
            document.getElementById('edit_lantai').value = data.lantai || '';
            document.getElementById('edit_ruangan').value = data.ruangan || '';
            document.getElementById('edit_nama_pc').value = data.nama_pc || '';
            document.getElementById('edit_nama_user').value = data.nama_user || '';
            document.getElementById('edit_manufactur').value = data.manufactur || '';
            document.getElementById('edit_processor_type').value = data.processor_type || '';
            document.getElementById('edit_windows_version').value = data.windows_version || '';
            document.getElementById('edit_ram_size').value = data.ram_size || '';
            document.getElementById('edit_ram_hardisk').value = data.ram_hardisk || '';
            document.getElementById('edit_monitor_model').value = data.monitor_model || '';
            document.getElementById('edit_monitor_size').value = data.monitor_size || '';
            document.getElementById('edit_monitor_vga').value = data.monitor_vga || '';
            document.getElementById('edit_printer_1').value = data.printer_1 || '';
            document.getElementById('edit_printer_2').value = data.printer_2 || '';
            document.getElementById('edit_tahun_pengadaan').value = yearToDate(data.tahun_pengadaan);
            document.getElementById('edit_id_personil_edp').value = data.id_personil_edp || '';
            openModal('editModal');
        }
        function closeEditModal() { closeModal('editModal'); }

        function openGantiModal(id) {
            document.getElementById('ganti_id').value = id;
            document.getElementById('ganti_alasan').value = '';
            openModal('gantiModal');
        }
        function closeGantiModal() { closeModal('gantiModal'); }

        function openDisposalModal(id) {
            document.getElementById('disposal_id').value = id;
            document.getElementById('disposal_alasan').value = '';
            openModal('disposalModal');
        }
        function closeDisposalModal() { closeModal('disposalModal'); }

        // Click outside to close
        ['editModal','gantiModal','disposalModal'].forEach(function(id) {
            document.getElementById(id).addEventListener('click', function(e) {
                if (e.target === this) {
                    if (id === 'editModal') closeEditModal();
                    if (id === 'gantiModal') closeGantiModal();
                    if (id === 'disposalModal') closeDisposalModal();
                }
            });
        });

        // Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal(); closeGantiModal(); closeDisposalModal();
            }
        });

        // Year to Date helper
        function yearToDate(yearStr) {
            if (!yearStr || yearStr === '') return '';
            if (/^\d{4}-\d{2}-\d{2}$/.test(yearStr)) return yearStr;
            if (/^\d{4}$/.test(yearStr)) return yearStr + '-01-01';
            return '';
        }

        // Ripple effect
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-glass, .header-btn, .menu-btn');
            if (!btn) return;
            var ripple = document.createElement('span');
            var rect = btn.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            ripple.style.cssText = 'position:absolute;border-radius:50%;background:rgba(255,255,255,0.25);width:' + size + 'px;height:' + size + 'px;left:' + (e.clientX - rect.left - size/2) + 'px;top:' + (e.clientY - rect.top - size/2) + 'px;pointer-events:none;animation:ripple 0.6s ease-out;';
            btn.style.position = 'relative';
            btn.style.overflow = 'hidden';
            btn.appendChild(ripple);
            setTimeout(function() { ripple.remove(); }, 600);
        });
    </script>
</body>
</html>