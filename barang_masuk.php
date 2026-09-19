<?php
/**
 * Tanda Terima Barang Masuk
 * Sistem Inventaris Digital
 * Database: db_inventaris_digital
 */
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_inventaris_digital';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Koneksi database gagal: ' . htmlspecialchars($conn->connect_error));
}
$conn->set_charset('utf8mb4');

// Membuat tabel otomatis jika belum ada.
$conn->query("CREATE TABLE IF NOT EXISTS barang_masuk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_surat VARCHAR(100) NOT NULL,
    tanggal DATE NOT NULL,
    file_dokumen VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nomor_surat (nomor_surat),
    INDEX idx_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS barang_masuk_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barang_masuk_id INT NOT NULL,
    nama_barang VARCHAR(255) NOT NULL,
    spesifikasi_teknik TEXT NULL,
    qty DECIMAL(15,2) NOT NULL DEFAULT 0,
    keterangan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_barang_masuk_id (barang_masuk_id),
    CONSTRAINT fk_barang_masuk_detail FOREIGN KEY (barang_masuk_id) REFERENCES barang_masuk(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'simpan') {
    $nomorSurat = trim($_POST['nomor_surat'] ?? '');
    $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
    $nama = $_POST['nama_barang'] ?? [];

    // Upload lampiran PDF/JPG/JPEG.
    $uploadedFile = $_FILES['file_dokumen'] ?? null;
    $namaFileDokumen = null;
    $fileBaruPath = null;
    $uploadDir = __DIR__ . '/uploads/barang_masuk/';

    if ($uploadedFile && ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            $message = 'Upload dokumen gagal. Kode error: ' . (int)$uploadedFile['error'];
            $messageType = 'error';
        } else {
            $allowedExt = ['pdf', 'jpg', 'jpeg'];
            $allowedMime = ['application/pdf', 'image/jpeg'];

            $ext = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($uploadedFile['tmp_name']);

            if (!in_array($ext, $allowedExt, true) || !in_array($mime, $allowedMime, true)) {
                $message = 'File harus berformat PDF atau JPG/JPEG.';
                $messageType = 'error';
            } elseif ($uploadedFile['size'] > 10 * 1024 * 1024) {
                $message = 'Ukuran file maksimal 10 MB.';
                $messageType = 'error';
            } else {
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                    $message = 'Folder upload tidak dapat dibuat.';
                    $messageType = 'error';
                } else {
                    $namaFileDokumen = 'TTBM_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                    $fileBaruPath = $uploadDir . $namaFileDokumen;

                    if (!move_uploaded_file($uploadedFile['tmp_name'], $fileBaruPath)) {
                        $message = 'Gagal menyimpan file dokumen.';
                        $messageType = 'error';
                        $namaFileDokumen = null;
                        $fileBaruPath = null;
                    }
                }
            }
        }
    }
    $spesifikasi = $_POST['spesifikasi_teknik'] ?? [];
    $qty = $_POST['qty'] ?? [];
    $keterangan = $_POST['keterangan'] ?? [];

    if ($messageType === 'error' && $message !== '') {
        // Error validasi upload ditampilkan dan proses simpan dihentikan.
    } elseif ($nomorSurat === '') {
        $message = 'Nomor surat wajib diisi.';
        $messageType = 'error';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $message = 'Tanggal tidak valid.';
        $messageType = 'error';
    } else {
        $items = [];
        $max = max(count($nama), count($spesifikasi), count($qty), count($keterangan));
        for ($i = 0; $i < $max; $i++) {
            $n = trim($nama[$i] ?? '');
            $s = trim($spesifikasi[$i] ?? '');
            $q = trim($qty[$i] ?? '');
            $k = trim($keterangan[$i] ?? '');
            if ($n !== '' || $s !== '' || $q !== '' || $k !== '') {
                $qNum = is_numeric($q) ? (float)$q : 0;
                $items[] = [$n, $s, $qNum, $k];
            }
        }

        if (!$items) {
            $message = 'Minimal satu barang harus diisi.';
            $messageType = 'error';
        } else {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare('INSERT INTO barang_masuk (nomor_surat, tanggal, file_dokumen) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $nomorSurat, $tanggal, $namaFileDokumen);
                $stmt->execute();
                $headerId = $conn->insert_id;
                $stmt->close();

                $detail = $conn->prepare('INSERT INTO barang_masuk_detail (barang_masuk_id, nama_barang, spesifikasi_teknik, qty, keterangan) VALUES (?, ?, ?, ?, ?)');
                foreach ($items as $item) {
                    [$n, $s, $q, $k] = $item;
                    $detail->bind_param('issds', $headerId, $n, $s, $q, $k);
                    $detail->execute();
                }
                $detail->close();
                $conn->commit();
                $message = 'Tanda terima barang masuk berhasil disimpan.';
            } catch (Throwable $ex) {
                $conn->rollback();
                if ($fileBaruPath && is_file($fileBaruPath)) {
                    @unlink($fileBaruPath);
                }
                $message = 'Gagal menyimpan data: ' . $ex->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Hapus satu tanda terima beserta detailnya.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmtFile = $conn->prepare('SELECT file_dokumen FROM barang_masuk WHERE id = ?');
        $stmtFile->bind_param('i', $id);
        $stmtFile->execute();
        $resFile = $stmtFile->get_result();
        $fileHapus = $resFile->fetch_assoc()['file_dokumen'] ?? null;
        $stmtFile->close();

        $stmt = $conn->prepare('DELETE FROM barang_masuk WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $message = $stmt->affected_rows ? 'Data tanda terima berhasil dihapus.' : 'Data tidak ditemukan.';
        $messageType = $stmt->affected_rows ? 'success' : 'error';
        $stmt->close();

        if ($fileHapus && $messageType === 'success') {
            $pathHapus = __DIR__ . '/uploads/barang_masuk/' . basename($fileHapus);
            if (is_file($pathHapus)) @unlink($pathHapus);
        }
    }
}

$riwayat = [];
$result = $conn->query("SELECT bm.id, bm.nomor_surat, bm.tanggal, bm.file_dokumen, bm.created_at,
    COUNT(bd.id) AS jumlah_jenis,
    COALESCE(SUM(bd.qty),0) AS total_qty
    FROM barang_masuk bm
    LEFT JOIN barang_masuk_detail bd ON bd.barang_masuk_id = bm.id
    GROUP BY bm.id
    ORDER BY bm.tanggal DESC, bm.id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) $riwayat[] = $row;
    $result->free();
}

$today = date('Y-m-d');

$detailRiwayat = [];
$detailHeader = null;
$lihatId = (int)($_GET['lihat'] ?? 0);

if ($lihatId > 0) {
    $stmt = $conn->prepare("SELECT id, nomor_surat, tanggal, file_dokumen FROM barang_masuk WHERE id = ?");
    $stmt->bind_param('i', $lihatId);
    $stmt->execute();
    $res = $stmt->get_result();
    $detailHeader = $res->fetch_assoc();
    $stmt->close();

    if ($detailHeader) {
        $stmt = $conn->prepare("SELECT id, nama_barang, spesifikasi_teknik, qty, keterangan
            FROM barang_masuk_detail
            WHERE barang_masuk_id = ?
            ORDER BY id ASC");
        $stmt->bind_param('i', $lihatId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $detailRiwayat[] = $row;
        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tanda Terima Barang Masuk - Inventaris Digital</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#07111f;--card:#0d1a2b;--card2:#111f32;--line:rgba(255,255,255,.09);--text:#f8fafc;--muted:#94a3b8;--primary:#10b981;--primary2:#059669;--danger:#ef4444;--blue:#3b82f6;--shadow:0 20px 60px rgba(0,0,0,.35)}
*{box-sizing:border-box}body{margin:0;font-family:'Plus Jakarta Sans',sans-serif;background:radial-gradient(circle at 10% 0%,rgba(16,185,129,.13),transparent 30%),radial-gradient(circle at 90% 20%,rgba(59,130,246,.12),transparent 30%),var(--bg);color:var(--text);min-height:100vh}.container{max-width:1450px;margin:auto;padding:28px}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px}.brand{display:flex;align-items:center;gap:14px}.brand-icon{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,var(--primary),var(--primary2));display:grid;place-items:center;font-size:22px;box-shadow:0 10px 30px rgba(16,185,129,.25)}h1{margin:0;font-size:26px}.subtitle{color:var(--muted);font-size:13px;margin-top:4px}.back{color:#fff;text-decoration:none;padding:11px 16px;border:1px solid var(--line);border-radius:12px;background:rgba(255,255,255,.04)}
.card{background:linear-gradient(145deg,rgba(17,31,50,.94),rgba(9,21,35,.94));border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow);padding:24px;margin-bottom:24px}.card-title{display:flex;align-items:center;gap:12px;font-size:18px;font-weight:800;margin-bottom:20px}.card-title i{color:#34d399}
.alert{padding:14px 16px;border-radius:12px;margin-bottom:18px;font-size:14px}.alert.success{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#a7f3d0}.alert.error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fecaca}
.form-grid{display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:22px}.field label{display:block;font-size:12px;color:var(--muted);font-weight:700;margin-bottom:8px}.field input,.field textarea{width:100%;background:#081524;color:#fff;border:1px solid var(--line);border-radius:11px;padding:12px 13px;outline:none;font:inherit}.field input:focus,.field textarea:focus{border-color:rgba(16,185,129,.7);box-shadow:0 0 0 3px rgba(16,185,129,.08)}
.table-wrap{overflow:auto;border:1px solid var(--line);border-radius:14px}.items{width:100%;border-collapse:collapse;min-width:1000px}.items th{background:#13243a;color:#cbd5e1;text-align:left;padding:12px 10px;font-size:12px;position:sticky;top:0}.items td{padding:8px;border-top:1px solid var(--line);vertical-align:top}.items input,.items textarea{width:100%;background:#071321;color:#fff;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:9px;font:inherit;font-size:13px}.items textarea{min-height:42px;resize:vertical}.no{width:50px;text-align:center;color:#64748b}.qty{width:110px}.act{width:55px;text-align:center}.remove{border:0;background:rgba(239,68,68,.12);color:#fca5a5;border-radius:8px;width:36px;height:36px;cursor:pointer}.remove:hover{background:rgba(239,68,68,.25)}
.actions{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-top:18px}.btn{border:0;border-radius:11px;padding:12px 17px;color:#fff;font-weight:700;cursor:pointer;font-family:inherit}.btn-add{background:rgba(59,130,246,.15);color:#93c5fd;border:1px solid rgba(59,130,246,.25)}.btn-save{background:linear-gradient(135deg,var(--primary),var(--primary2));box-shadow:0 8px 24px rgba(16,185,129,.2)}
.history{width:100%;border-collapse:collapse}.history th,.history td{padding:13px 12px;border-bottom:1px solid var(--line);text-align:left;font-size:13px}.history th{color:var(--muted);font-size:11px;text-transform:uppercase}.mini-form{display:inline}.btn-delete{background:rgba(239,68,68,.12);color:#fca5a5;padding:7px 10px;font-size:12px}.empty{text-align:center;color:var(--muted);padding:28px}.note{font-size:12px;color:var(--muted)}

.detail-link{color:#6ee7b7;text-decoration:none;font-weight:800;display:inline-flex;align-items:center;gap:7px;padding:6px 9px;border-radius:8px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.18)}
.detail-link:hover{background:rgba(16,185,129,.16);border-color:rgba(16,185,129,.35);transform:translateY(-1px)}
.detail-card{scroll-margin-top:20px}
.detail-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:18px}
.detail-meta{display:flex;gap:22px;flex-wrap:wrap;color:var(--muted);font-size:13px}
.detail-meta strong{color:#cbd5e1}
.close-detail{color:#fff;text-decoration:none;padding:9px 13px;border:1px solid var(--line);border-radius:10px;background:rgba(255,255,255,.04);white-space:nowrap}
.close-detail:hover{background:rgba(255,255,255,.08)}
.detail-table td:nth-child(3),.detail-table td:nth-child(5){white-space:normal;min-width:220px;line-height:1.55}
@media(max-width:800px){.container{padding:16px}.topbar{align-items:flex-start;flex-direction:column}.form-grid{grid-template-columns:1fr}.actions{align-items:stretch;flex-direction:column}.actions>*{width:100%}.btn{width:100%}}
</style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <div class="brand"><div class="brand-icon"><i class="fas fa-file-signature"></i></div><div><h1>Tanda Terima Barang Masuk</h1><div class="subtitle">Pencatatan penerimaan barang dan spesifikasi teknik</div></div></div>
        <a class="back" href="index.php"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <?php if ($message): ?><div class="alert <?= e($messageType) ?>"><i class="fas <?= $messageType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= e($message) ?></div><?php endif; ?>

    <form method="post" class="card" autocomplete="off" enctype="multipart/form-data">
        <input type="hidden" name="action" value="simpan">
        <div class="card-title"><i class="fas fa-clipboard-list"></i> Informasi Surat / Penerimaan</div>
        <div class="form-grid">
            <div class="field"><label>Nomor Surat *</label><input type="text" name="nomor_surat" placeholder="Contoh: 012/PO/IX/2026" required></div>
            <div class="field"><label>Tanggal Barang Masuk *</label><input type="date" name="tanggal" value="<?= e($today) ?>" required></div>
        </div>
        <div class="field" style="margin-bottom:22px;">
            <label>Dokumen Tanda Terima (PDF / JPG) — Maks. 10 MB</label>
            <input type="file" name="file_dokumen" accept=".pdf,.jpg,.jpeg,application/pdf,image/jpeg">
            <div class="note" style="margin-top:7px;">Lampiran surat/tanda terima dapat berupa PDF atau JPG/JPEG.</div>
        </div>

        <div class="card-title"><i class="fas fa-boxes-stacked"></i> Spesifikasi Teknik Barang</div>
        <div class="table-wrap">
            <table class="items" id="itemsTable">
                <thead><tr><th class="no">No.</th><th>Nama Barang *</th><th>Spesifikasi Teknik</th><th class="qty">Qty</th><th>Keterangan</th><th class="act">Aksi</th></tr></thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
        <div class="actions"><button type="button" class="btn btn-add" onclick="addRow()"><i class="fas fa-plus"></i> Tambah Baris</button><div><span class="note" id="rowInfo">20 baris disiapkan</span> &nbsp; <button class="btn btn-save" type="submit"><i class="fas fa-floppy-disk"></i> Simpan Tanda Terima</button></div></div>
    </form>

    <section class="card">
        <div class="card-title"><i class="fas fa-clock-rotate-left"></i> Riwayat Tanda Terima</div>
        <?php if (!$riwayat): ?><div class="empty">Belum ada data tanda terima barang masuk.</div>
        <?php else: ?><div class="table-wrap"><table class="history"><thead><tr><th>No</th><th>Nomor Surat</th><th>Tanggal</th><th>Dokumen</th><th>Jenis Barang</th><th>Total Qty</th><th>Aksi</th></tr></thead><tbody>
        <?php foreach ($riwayat as $i => $r): ?><tr><td><?= $i+1 ?></td><td><strong><?= e($r['nomor_surat']) ?></strong></td><td><?= e(date('d-m-Y', strtotime($r['tanggal']))) ?></td><td>
<?php if (!empty($r['file_dokumen'])): ?>
<a class="detail-link" href="uploads/barang_masuk/<?= e(rawurlencode(basename($r['file_dokumen']))) ?>" target="_blank" rel="noopener" title="Buka dokumen"><i class="fas fa-file-arrow-up"></i> Lihat</a>
<?php else: ?><span class="note">Tidak ada</span><?php endif; ?>
</td><td><a class="detail-link" href="?lihat=<?= e($r['id']) ?>#detailRiwayat" title="Lihat semua item"><?= e($r['jumlah_jenis']) ?> item <i class="fas fa-eye"></i></a></td><td><?= e($r['total_qty']) ?></td><td><form class="mini-form" method="post" onsubmit="return confirm('Hapus tanda terima ini?')"><input type="hidden" name="action" value="hapus"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="btn btn-delete" type="submit"><i class="fas fa-trash"></i> Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    </section>
</div>

<?php if ($detailHeader): ?>
<section class="card detail-card" id="detailRiwayat">
    <div class="detail-head">
        <div>
            <div class="card-title" style="margin-bottom:6px;"><i class="fas fa-list-check"></i> Detail Tanda Terima</div>
            <div class="detail-meta">
                <span><strong>Nomor Surat:</strong> <?= e($detailHeader['nomor_surat']) ?></span>
                <span><strong>Tanggal:</strong> <?= e(date('d-m-Y', strtotime($detailHeader['tanggal']))) ?></span>
                <?php if (!empty($detailHeader['file_dokumen'])): ?>
                <span><a class="detail-link" href="uploads/barang_masuk/<?= e(rawurlencode(basename($detailHeader['file_dokumen']))) ?>" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i> Buka Dokumen</a></span>
                <?php endif; ?>
            </div>
        </div>
        <a class="close-detail" href="<?= e($_SERVER['PHP_SELF']) ?>#detailRiwayat"><i class="fas fa-times"></i> Tutup</a>
    </div>

    <div class="table-wrap">
        <table class="history detail-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Spesifikasi Teknik</th>
                    <th>Qty</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$detailRiwayat): ?>
                <tr><td colspan="5" class="empty">Tidak ada detail barang.</td></tr>
            <?php else: ?>
                <?php foreach ($detailRiwayat as $j => $d): ?>
                <tr>
                    <td><?= $j + 1 ?></td>
                    <td><strong><?= e($d['nama_barang']) ?></strong></td>
                    <td><?= nl2br(e($d['spesifikasi_teknik'])) ?></td>
                    <td><?= e(rtrim(rtrim(number_format((float)$d['qty'], 2, '.', ''), '0'), '.')) ?></td>
                    <td><?= nl2br(e($d['keterangan'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<script>
let rowCount=0;
function addRow(n='',s='',q='',k=''){
 const tbody=document.getElementById('itemsBody'); rowCount++;
 const tr=document.createElement('tr');
 tr.innerHTML=`<td class="no">${rowCount}</td><td><input type="text" name="nama_barang[]" value="${esc(n)}" placeholder="Nama barang" ${n?'':'required'}></td><td><textarea name="spesifikasi_teknik[]" placeholder="Merk, tipe, ukuran, bahan, kapasitas, model, dll.">${esc(s)}</textarea></td><td><input class="qty" type="number" step="0.01" min="0" name="qty[]" value="${esc(q)}" placeholder="0"></td><td><textarea name="keterangan[]" placeholder="Kondisi / catatan penerimaan">${esc(k)}</textarea></td><td class="act"><button type="button" class="remove" onclick="removeRow(this)" title="Hapus baris"><i class="fas fa-trash"></i></button></td>`;
 tbody.appendChild(tr); updateRows();
}
function esc(v){return String(v??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;')}
function removeRow(btn){btn.closest('tr').remove(); renumber();}
function renumber(){[...document.querySelectorAll('#itemsBody tr')].forEach((tr,i)=>tr.querySelector('.no').textContent=i+1); rowCount=document.querySelectorAll('#itemsBody tr').length; updateRows();}
function updateRows(){document.getElementById('rowInfo').textContent=document.querySelectorAll('#itemsBody tr').length+' baris';}
for(let i=0;i<1;i++) addRow();
</script>
</body>
</html>
