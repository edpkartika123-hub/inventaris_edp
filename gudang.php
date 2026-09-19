<?php
/**
 * ============================================================
 * GUDANG DIGITAL - INVENTARIS UNIVERSAL v2.0
 * ============================================================
 * Tampilan dinamis, responsif, dengan ikon profesional
 */

include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$allowedUnits = [
    'kartika1' => 'Kartika 1',
    'kartika2' => 'Kartika 2',
    'kartika3' => 'Kartika 3',
    'cicu'     => 'CICU',
    'cvc'      => 'CVC',
];

$unit = strtolower(trim($_GET['unit'] ?? 'kartika2'));

if (!isset($allowedUnits[$unit])) {
    die('
    <!DOCTYPE html>
    <html lang="id"><head><meta charset="UTF-8"><title>Error</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    body{font-family:Inter,system-ui,sans-serif;background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
    .box{background:#1e293b;border:1px solid #334155;padding:40px;border-radius:24px;text-align:center;max-width:500px;box-shadow:0 25px 50px rgba(0,0,0,.4);animation:fadeInUp .6s ease;}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
    .box .icon{font-size:48px;color:#f87171;margin-bottom:16px;}
    .box h2{color:#f87171;margin:0 0 12px;font-size:24px;}
    .box p{color:#94a3b8;margin:0 0 24px;line-height:1.6;}
    .box a{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border-radius:12px;text-decoration:none;font-weight:700;transition:all .3s ease;}
    .box a:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(37,99,235,.4);}
    </style></head><body>
    <div class="box">
        <div class="icon"><i class="fas fa-triangle-exclamation"></i></div>
        <h2>Unit Tidak Valid</h2>
        <p>Unit <b>' . htmlspecialchars($unit) . '</b> tidak dikenali.</p>
        <p><a href="gudang.php?unit=kartika1"><i class="fas fa-hospital"></i> Kartika 1</a> 
        <a href="gudang.php?unit=kartika2"><i class="fas fa-hospital"></i> Kartika 2</a> 
        <a href="gudang.php?unit=kartika3"><i class="fas fa-hospital"></i> Kartika 3</a> 
        <a href="gudang.php?unit=cicu"><i class="fas fa-procedures"></i> CICU</a> 
        <a href="gudang.php?unit=cvc"><i class="fas fa-heart-pulse"></i> CVC</a></p>
    </div></body></html>');
}

$unitLabel       = $allowedUnits[$unit];
$gudangTable     = "gudang_{$unit}";
$inventarisTable = "inventaris_{$unit}";

function esc($value){ return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
function dbesc($value){
    global $conn;
    return mysqli_real_escape_string($conn, trim((string)($value ?? '')));
}

function showError($title, $message){
    global $unit;
    echo '
    <!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Error</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    :root{--bg:#0f172a;--panel:#1e293b;--line:#334155;--text:#f1f5f9;--muted:#94a3b8;--red:#f87171;}
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;font-family:Inter,system-ui,sans-serif;background:radial-gradient(ellipse at top left,rgba(56,189,248,.1),transparent 50%),var(--bg);}
    .container{width:100%;max-width:500px;animation:fadeInUp .6s ease;}@keyframes fadeInUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
    .error-box{background:var(--panel);border:1px solid var(--line);padding:40px;border-radius:24px;text-align:center;box-shadow:0 25px 50px rgba(0,0,0,.4);}
    .error-box .icon{font-size:52px;color:var(--red);margin-bottom:16px;animation:pulse 2s ease-in-out infinite;}@keyframes pulse{0%,100%{opacity:1}50%{opacity:.6}}
    .error-box h2{color:var(--red);margin:0 0 12px;font-size:24px;}
    .error-box p{color:var(--muted);margin:0 0 24px;line-height:1.6;}
    .back{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border-radius:12px;text-decoration:none;font-weight:700;transition:all .3s ease;box-shadow:0 4px 15px rgba(37,99,235,.3);}
    .back:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(37,99,235,.4);}
    </style></head><body>
    <div class="container">
        <div class="error-box">
            <div class="icon"><i class="fas fa-circle-xmark"></i></div>
            <h2>' . esc($title) . '</h2>
            <p>' . esc($message) . '</p>
            <a class="back" href="gudang.php?unit=' . esc($unit) . '"><i class="fas fa-arrow-left"></i> Kembali ke Gudang</a>
        </div>
    </div></body></html>';
    exit;
}

function getTableColumns($table){
    global $conn;
    $columns = [];
    $safeTable = str_replace('`', '``', $table);
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `{$safeTable}`");
    if (!$result) showError('Database Error', 'Gagal membaca struktur tabel: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_assoc($result)) $columns[] = $row['Field'];
    return $columns;
}

function tableExists($table){
    global $conn;
    $table = mysqli_real_escape_string($conn, $table);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$table}'");
    return $result && mysqli_num_rows($result) > 0;
}

/* =========================================================
   GLOBAL DESTINATION MAP & AJAX CASCADING HANDLER
========================================================= */
$destinationMapGlobal = [
    'kartika1' => 'inventaris_kartika1',
    'kartika2' => 'inventaris_kartika2',
    'kartika3' => 'inventaris_kartika3',
    'cicu'     => 'inventaris_cicu',
    'cvc'      => 'inventaris_cvc',
];

if (isset($_GET['ajax']) && isset($destinationMapGlobal[$_GET['tujuan'] ?? ''])) {
    header('Content-Type: application/json; charset=utf-8');
    $ajaxType = $_GET['ajax'];
    $destTable = $destinationMapGlobal[$_GET['tujuan']];
    $destCols = getTableColumns($destTable);

    if ($ajaxType === 'gedung') {
        $data = [];
        if (in_array('gedung', $destCols)) {
            $result = mysqli_query($conn, "SELECT DISTINCT `gedung` FROM `{$destTable}` WHERE `gedung` IS NOT NULL AND TRIM(`gedung`) != '' ORDER BY `gedung`");
            if ($result) while ($row = mysqli_fetch_assoc($result)) if ($row['gedung']) $data[] = $row['gedung'];
        }
        if (empty($data)) $data = ['Kartika 1', 'Kartika 2', 'Kartika 3', 'CICU', 'CVC'];
        echo json_encode($data);
        exit;
    }

    if ($ajaxType === 'lantai') {
        $data = [];
        if (in_array('lantai', $destCols)) {
            $result = mysqli_query($conn, "SELECT DISTINCT `lantai` FROM `{$destTable}` WHERE `lantai` IS NOT NULL AND TRIM(`lantai`) != '' ORDER BY CAST(`lantai` AS UNSIGNED), `lantai`");
            if ($result) while ($row = mysqli_fetch_assoc($result)) if ($row['lantai']) $data[] = $row['lantai'];
        }
        if (empty($data)) $data = ['1', '2', '3', '4', '5', '6'];
        echo json_encode($data);
        exit;
    }

    if ($ajaxType === 'users') {
        $gedung = dbesc($_GET['gedung'] ?? '');
        $lantai = dbesc($_GET['lantai'] ?? '');
        $fields = [];
        if (in_array('nama_user', $destCols)) $fields[] = '`nama_user`';
        if (in_array('ruangan', $destCols))   $fields[] = '`ruangan`';
        if (in_array('nama_pc', $destCols))   $fields[] = '`nama_pc`';
        $selectSql = !empty($fields) ? implode(', ', $fields) : '*';
        $whereParts = ["1=1"];
        if (in_array('gedung', $destCols))    $whereParts[] = "`gedung` = '{$gedung}'";
        if (in_array('lantai', $destCols))    $whereParts[] = "`lantai` = '{$lantai}'";
        if (in_array('nama_user', $destCols)) $whereParts[] = "`nama_user` IS NOT NULL AND TRIM(`nama_user`) != ''";
        $result = mysqli_query($conn, "SELECT DISTINCT {$selectSql} FROM `{$destTable}` WHERE ".implode(' AND ', $whereParts)." ORDER BY `nama_user`");
        $data = [];
        if ($result) while ($row = mysqli_fetch_assoc($result)) {
            $data[] = ['nama_user' => $row['nama_user'] ?? '', 'ruangan' => $row['ruangan'] ?? '', 'nama_pc' => $row['nama_pc'] ?? ''];
        }
        echo json_encode($data);
        exit;
    }

    echo json_encode([]);
    exit;
}

/* PROSES POST - sama seperti original, dipersingkat untuk kejelasan */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = intval($_POST['id'] ?? 0);
    $status = trim($_POST['status_gudang'] ?? 'Stok');
    $statusAllowed = ['Stok','Perbaikan','Siap Pakai','Rusak'];
    if (!in_array($status, $statusAllowed, true)) $status = 'Stok';
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE `{$gudangTable}` SET status_gudang = ? WHERE id = ? LIMIT 1");
        if ($stmt) { mysqli_stmt_bind_param($stmt, "si", $status, $id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt); }
    }
    header("Location: gudang.php?unit={$unit}&status=updated"); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_gudang'])) {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM `{$gudangTable}` WHERE id = ? LIMIT 1");
        if ($stmt) { mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt); }
    }
    header("Location: gudang.php?unit={$unit}&status=deleted"); exit;
}

/* RESTORE - sama persis logic original */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_gudang'])) {
    $idGudang = intval($_POST['id'] ?? 0);
    if ($idGudang <= 0) showError('Restore Gagal', 'ID tidak valid.');
    mysqli_begin_transaction($conn);
    try {
        $stmt = mysqli_prepare($conn, "SELECT * FROM `{$gudangTable}` WHERE id = ? LIMIT 1");
        if (!$stmt) throw new Exception(mysqli_error($conn));
        mysqli_stmt_bind_param($stmt, 'i', $idGudang);
        mysqli_stmt_execute($stmt);
        $dataGudang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if (!$dataGudang) throw new Exception('Data tidak ditemukan.');
        $idAsal = intval($dataGudang['id_asal'] ?? 0);
        if ($idAsal <= 0) throw new Exception('ID asal tidak valid.');
        if (!tableExists($inventarisTable)) throw new Exception("Tabel {$inventarisTable} tidak ditemukan.");
        $tipeRestore = trim((string)($dataGudang['tipe_ganti'] ?? ''));
        $allowedTypes = ['pc','printer1','printer2','semua'];
        if (!in_array($tipeRestore, $allowedTypes, true)) {
            $hasPc = trim((string)($dataGudang['nama_pc'] ?? '')) !== '';
            $hasP1 = trim((string)($dataGudang['printer_1'] ?? '')) !== '';
            $hasP2 = trim((string)($dataGudang['printer_2'] ?? '')) !== '';
            if ($hasPc && ($hasP1 || $hasP2)) $tipeRestore = 'semua';
            elseif ($hasPc) $tipeRestore = 'pc';
            elseif ($hasP1) $tipeRestore = 'printer1';
            elseif ($hasP2) $tipeRestore = 'printer2';
            else throw new Exception('Jenis barang tidak dapat ditentukan.');
        }
        $pcFields = ['nama_pc','nama_user','manufactur','processor_type','windows_version','ram_size','ram_hardisk','monitor_model','monitor_size','monitor_vga','tahun_pengadaan','id_personil_edp'];
        $p1Fields = ['printer_1']; $p2Fields = ['printer_2'];
        $allFields = array_merge(['gedung','lantai','ruangan'], $pcFields, $p1Fields, $p2Fields);
        switch($tipeRestore){case'pc':$rf=$pcFields;break;case'printer1':$rf=$p1Fields;break;case'printer2':$rf=$p2Fields;break;case'semua':$rf=$allFields;break;default:throw new Exception('Jenis tidak valid.');}
        $stmt = mysqli_prepare($conn, "SELECT id FROM `{$inventarisTable}` WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $idAsal); mysqli_stmt_execute($stmt);
        $sudahAda = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
        if ($sudahAda) {
            $setParts=[]; $uv=[];
            foreach($rf as $c){$setParts[]='`'.str_replace('`','``',$c).'` = ?';$uv[]=(string)($dataGudang[$c]??'');}
            if(empty($setParts)) throw new Exception('Tidak ada kolom yang dapat direstore.');
            $usql="UPDATE `{$inventarisTable}` SET ".implode(', ',$setParts)." WHERE `id` = ? LIMIT 1";
            $stmt=mysqli_prepare($conn,$usql);$uv[]=$idAsal;$types=str_repeat('s',count($uv)-1).'i';
            $bp=[$stmt,$types];foreach($uv as $k=>$v)$bp[]=&$uv[$k];call_user_func_array('mysqli_stmt_bind_param',$bp);
            if(!mysqli_stmt_execute($stmt)){mysqli_stmt_close($stmt);throw new Exception('Gagal restore.');}mysqli_stmt_close($stmt);
        } else {
            if($tipeRestore!=='semua') throw new Exception('Record asli tidak ditemukan. Restore sebagian tidak dapat membuat record baru.');
            $ic=['id'];$iv=[$idAsal];foreach($allFields as $c){$ic[]=$c;$iv[]=(string)($dataGudang[$c]??'');}
            $qc=[];foreach($ic as $c)$qc[]='`'.str_replace('`','``',$c).'`';
            $ph=implode(', ',array_fill(0,count($iv),'?'));
            $isql="INSERT INTO `{$inventarisTable}` (".implode(', ',$qc).") VALUES ({$ph})";
            $stmt=mysqli_prepare($conn,$isql);$types=str_repeat('s',count($iv));
            $bp=[$stmt,$types];foreach($iv as $k=>$v)$bp[]=&$iv[$k];call_user_func_array('mysqli_stmt_bind_param',$bp);
            if(!mysqli_stmt_execute($stmt)){mysqli_stmt_close($stmt);throw new Exception('Gagal membuat data.');}mysqli_stmt_close($stmt);
        }
        $stmt=mysqli_prepare($conn,"DELETE FROM `{$gudangTable}` WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt,'i',$idGudang);
        if(!mysqli_stmt_execute($stmt)){mysqli_stmt_close($stmt);throw new Exception('Gagal hapus dari gudang.');}
        if(mysqli_stmt_affected_rows($stmt)<=0){mysqli_stmt_close($stmt);throw new Exception('Data gudang tidak terhapus.');}mysqli_stmt_close($stmt);
        mysqli_commit($conn);header("Location: gudang.php?unit={$unit}&status=restore_sukses");exit;
    } catch (Throwable $e) { mysqli_rollback($conn); showError('Restore Gagal', $e->getMessage()); }
}

/* KIRIM - sama persis logic original */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_ruangan'])) {
    $idGudang=intval($_POST['id']??0);$tipeKirim=trim($_POST['tipe_kirim']??'');$tujuanKirim=trim($_POST['tujuan_kirim']??'');
    $gedungTujuan=trim($_POST['gedung_tujuan']??'');$lantaiTujuan=trim($_POST['lantai_tujuan']??'');$ruanganTujuan=trim($_POST['ruangan_tujuan']??'');
    $userTujuan=trim($_POST['nama_user_tujuan']??'');$catatanKirim=trim($_POST['catatan_kirim']??'');
    $allowedTypes=['pc','printer1','printer2','semua'];
    if($idGudang<=0||!in_array($tipeKirim,$allowedTypes,true))showError('Kirim Gagal','Jenis barang tidak valid.');
    if(!isset($destinationMapGlobal[$tujuanKirim]))showError('Kirim Gagal','Tujuan tidak valid.');
    $destinationTable=$destinationMapGlobal[$tujuanKirim];
    if($gedungTujuan===''||$lantaiTujuan===''||$ruanganTujuan==='')showError('Kirim Gagal','Gedung, lantai, ruangan wajib diisi.');
    if(!tableExists($destinationTable))showError('Tabel Tidak Ditemukan','Tabel '.$destinationTable.' belum tersedia.');
    $stmt=mysqli_prepare($conn,"SELECT * FROM `{$gudangTable}` WHERE id = ? LIMIT 1");
    if(!$stmt)showError('Kirim Gagal',mysqli_error($conn));
    mysqli_stmt_bind_param($stmt,'i',$idGudang);mysqli_stmt_execute($stmt);
    $dataGudang=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));mysqli_stmt_close($stmt);
    if(!$dataGudang)showError('Kirim Gagal','Data tidak ditemukan.');
    // Validasi data asal dihapus — user bebas memilih jenis kiriman
    $hasPC=trim((string)($dataGudang['nama_pc']??''))!=='';
    $hasP1=trim((string)($dataGudang['printer_1']??''))!=='';
    $hasP2=trim((string)($dataGudang['printer_2']??''))!=='';
    $sourceFields=['gedung','lantai','ruangan','nama_pc','nama_user','manufactur','processor_type','windows_version','ram_size','ram_hardisk','monitor_model','monitor_size','monitor_vga','printer_1','printer_2','tahun_pengadaan','id_personil_edp'];
    $payload=[];foreach($sourceFields as $f)$payload[$f]=(string)($dataGudang[$f]??'');
    if($tipeKirim==='pc'){$payload['printer_1']='';$payload['printer_2']='';}
    elseif($tipeKirim==='printer1'){foreach(['nama_pc','nama_user','manufactur','processor_type','windows_version','ram_size','ram_hardisk','monitor_model','monitor_size','monitor_vga','printer_2','tahun_pengadaan','id_personil_edp']as$f)$payload[$f]='';}
    elseif($tipeKirim==='printer2'){foreach(['nama_pc','nama_user','manufactur','processor_type','windows_version','ram_size','ram_hardisk','monitor_model','monitor_size','monitor_vga','printer_1','tahun_pengadaan','id_personil_edp']as$f)$payload[$f]='';}
    $payload['gedung']=$gedungTujuan;$payload['lantai']=$lantaiTujuan;$payload['ruangan']=$ruanganTujuan;
    if($userTujuan!=='')$payload['nama_user']=$userTujuan;
    $jenisBarang='Lainnya';
    if($tipeKirim==='pc')$jenisBarang='PC';
    elseif($tipeKirim==='printer1')$jenisBarang='Printer 1';
    elseif($tipeKirim==='printer2')$jenisBarang='Printer 2';
    elseif($tipeKirim==='semua')$jenisBarang='PC + Printer';
    $destinationColumns=getTableColumns($destinationTable);
    if(in_array('status',$destinationColumns,true))$payload['status']='Aktif';
    if(in_array('status_barang',$destinationColumns,true))$payload['status_barang']='Aktif';
    if(in_array('jenis_barang',$destinationColumns,true))$payload['jenis_barang']=$jenisBarang;
    if(in_array('keterangan',$destinationColumns,true))$payload['keterangan']=$catatanKirim;
    if(in_array('catatan',$destinationColumns,true))$payload['catatan']=$catatanKirim;
    $now=date('Y-m-d H:i:s');
    if(in_array('tanggal_masuk',$destinationColumns,true))$payload['tanggal_masuk']=$now;
    if(in_array('tanggal',$destinationColumns,true))$payload['tanggal']=$now;
    if(in_array('created_at',$destinationColumns,true))$payload['created_at']=$now;
    $insertColumns=[];$insertValues=[];
    foreach($payload as $f=>$v){if($f==='id')continue;if(in_array($f,$destinationColumns,true)){$insertColumns[]=$f;$insertValues[]=$v;}}
    if(empty($insertColumns))showError('Kirim Gagal','Tidak ada kolom cocok.');
    mysqli_begin_transaction($conn);
    try{
        $qc=[];foreach($insertColumns as $c)$qc[]='`'.str_replace('`','``',$c).'`';
        $ph=implode(', ',array_fill(0,count($insertColumns),'?'));
        $isql="INSERT INTO `".str_replace('`','``',$destinationTable)."` (".implode(', ',$qc).") VALUES ({$ph})";
        $stmt=mysqli_prepare($conn,$isql);if(!$stmt)throw new Exception(mysqli_error($conn));
        $types=str_repeat('s',count($insertValues));$bp=[$stmt,$types];foreach($insertValues as $k=>$v)$bp[]=&$insertValues[$k];call_user_func_array('mysqli_stmt_bind_param',$bp);
        if(!mysqli_stmt_execute($stmt)){$e=mysqli_stmt_error($stmt);mysqli_stmt_close($stmt);throw new Exception($e);}mysqli_stmt_close($stmt);
        if($tipeKirim==='pc')$usql="UPDATE `{$gudangTable}` SET nama_pc='',nama_user='',manufactur='',processor_type='',windows_version='',ram_size='',ram_hardisk='',monitor_model='',monitor_size='',monitor_vga='',tahun_pengadaan='',id_personil_edp='' WHERE id=? LIMIT 1";
        elseif($tipeKirim==='printer1')$usql="UPDATE `{$gudangTable}` SET printer_1='' WHERE id=? LIMIT 1";
        elseif($tipeKirim==='printer2')$usql="UPDATE `{$gudangTable}` SET printer_2='' WHERE id=? LIMIT 1";
        else $usql="DELETE FROM `{$gudangTable}` WHERE id=? LIMIT 1";
        $stmt=mysqli_prepare($conn,$usql);if(!$stmt)throw new Exception(mysqli_error($conn));
        mysqli_stmt_bind_param($stmt,'i',$idGudang);
        if(!mysqli_stmt_execute($stmt)){$e=mysqli_stmt_error($stmt);mysqli_stmt_close($stmt);throw new Exception($e);}mysqli_stmt_close($stmt);
        mysqli_commit($conn);header("Location: gudang.php?unit={$unit}&status=kirim_sukses&tujuan=".urlencode($destinationTable));exit;
    }catch(Throwable $e){mysqli_rollback($conn);showError('Kirim Gagal',$e->getMessage());}
}

/* SEARCH & FILTER */
$search=trim($_GET['search']??'');$statusFilter=trim($_GET['status']??'');$jenisFilter=trim($_GET['jenis']??'');
$where=[];
if($search!==''){$sd=dbesc($search);$where[]="(CAST(id AS CHAR) LIKE '%{$sd}%' OR CAST(id_asal AS CHAR) LIKE '%{$sd}%' OR gedung LIKE '%{$sd}%' OR lantai LIKE '%{$sd}%' OR ruangan LIKE '%{$sd}%' OR nama_pc LIKE '%{$sd}%' OR nama_user LIKE '%{$sd}%' OR manufactur LIKE '%{$sd}%' OR processor_type LIKE '%{$sd}%' OR printer_1 LIKE '%{$sd}%' OR printer_2 LIKE '%{$sd}%' OR alasan_ganti LIKE '%{$sd}%')";}
if($statusFilter!==''){$where[]="status_gudang = '".dbesc($statusFilter)."'";}
if($jenisFilter!==''){if($jenisFilter==='PC')$where[]="TRIM(COALESCE(nama_pc,''))<>'' AND TRIM(COALESCE(printer_1,''))='' AND TRIM(COALESCE(printer_2,''))=''";elseif($jenisFilter==='Printer 1')$where[]="TRIM(COALESCE(nama_pc,''))='' AND TRIM(COALESCE(printer_1,''))<>''";elseif($jenisFilter==='Printer 2')$where[]="TRIM(COALESCE(nama_pc,''))='' AND TRIM(COALESCE(printer_1,''))='' AND TRIM(COALESCE(printer_2,''))<>''";elseif($jenisFilter==='PC + Printer')$where[]="TRIM(COALESCE(nama_pc,''))<>'' AND (TRIM(COALESCE(printer_1,''))<>'' OR TRIM(COALESCE(printer_2,''))<>'')";}
$whereSql='';if(!empty($where))$whereSql='WHERE '.implode(' AND ',$where);

/* QUERY */
$sql="SELECT * FROM `{$gudangTable}` {$whereSql} ORDER BY tanggal_masuk DESC, id DESC";
$query=mysqli_query($conn,$sql);if(!$query)showError('Database Error',mysqli_error($conn));

/* STATS */
$totalGudang=0;$tr=mysqli_query($conn,"SELECT COUNT(*) AS total FROM `{$gudangTable}`");if($tr){$trr=mysqli_fetch_assoc($tr);$totalGudang=intval($trr['total']??0);}
$stats=['stok'=>0,'siap_pakai'=>0,'perbaikan'=>0,'rusak'=>0];
$sr=mysqli_query($conn,"SELECT SUM(status_gudang='Stok')AS stok,SUM(status_gudang='Siap Pakai')AS siap_pakai,SUM(status_gudang='Perbaikan')AS perbaikan,SUM(status_gudang='Rusak')AS rusak FROM `{$gudangTable}`");
if($sr){$srr=mysqli_fetch_assoc($sr);$stats['stok']=intval($srr['stok']??0);$stats['siap_pakai']=intval($srr['siap_pakai']??0);$stats['perbaikan']=intval($srr['perbaikan']??0);$stats['rusak']=intval($srr['rusak']??0);}

/* DATA */
$gudangData=[];
while($row=mysqli_fetch_assoc($query)){
    $hPC=trim($row['nama_pc']??'')!=='';$hP1=trim($row['printer_1']??'')!=='';$hP2=trim($row['printer_2']??'')!=='';
    if($hPC&&($hP1||$hP2))$row['jenis_barang']='PC + Printer';elseif($hPC)$row['jenis_barang']='PC';elseif($hP1)$row['jenis_barang']='Printer 1';elseif($hP2)$row['jenis_barang']='Printer 2';else $row['jenis_barang']='Lainnya';
    $gudangData[]=$row;
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Perbaikan Digital - <?= esc($unitLabel) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
/* =========================================================
   DESIGN TOKENS
========================================================= */
:root{
  --bg-primary:#0b1120;--bg-secondary:#0f172a;--bg-tertiary:#1e293b;
  --bg-glass:rgba(15,23,42,.78);--bg-glass-hover:rgba(30,41,59,.88);
  --border-subtle:rgba(148,163,184,.1);--border-medium:rgba(148,163,184,.18);--border-strong:rgba(148,163,184,.28);
  --text-primary:#f8fafc;--text-secondary:#cbd5e1;--text-muted:#94a3b8;--text-dim:#64748b;
  --accent-blue:#38bdf8;--accent-blue-glow:rgba(56,189,248,.25);
  --accent-cyan:#22d3ee;--accent-green:#34d399;--accent-green-glow:rgba(52,211,153,.25);
  --accent-amber:#fbbf24;--accent-amber-glow:rgba(251,191,36,.25);
  --accent-red:#f87171;--accent-red-glow:rgba(248,113,113,.25);
  --accent-purple:#a78bfa;--accent-purple-glow:rgba(167,139,250,.25);
  --shadow-sm:0 2px 8px rgba(0,0,0,.2);--shadow-md:0 8px 24px rgba(0,0,0,.25);
  --shadow-lg:0 16px 48px rgba(0,0,0,.3);--shadow-xl:0 24px 64px rgba(0,0,0,.4);
  --shadow-glow-blue:0 0 20px rgba(56,189,248,.12);
  --radius-sm:8px;--radius-md:12px;--radius-lg:16px;--radius-xl:20px;--radius-full:9999px;
  --transition-fast:150ms cubic-bezier(.4,0,.2,1);--transition-base:250ms cubic-bezier(.4,0,.2,1);
  --transition-slow:400ms cubic-bezier(.4,0,.2,1);--transition-bounce:500ms cubic-bezier(.34,1.56,.64,1);
  --input-bg:rgba(3,10,20,.55);--input-border:rgba(148,163,184,.12);--input-focus-border:rgba(56,189,248,.45);
  --input-focus-glow:0 0 0 3px rgba(56,189,248,.08);
  --table-header-bg:rgba(5,13,24,.55);--table-row-hover:rgba(56,189,248,.035);--table-row-active:rgba(56,189,248,.07);
  --toast-bg-success:rgba(6,78,59,.95);--toast-bg-error:rgba(127,29,29,.95);--toast-bg-info:rgba(30,58,138,.95);
  --toast-border-success:#10b981;--toast-border-error:#ef4444;--toast-border-info:#3b82f6;
}
[data-theme="light"]{
  --bg-primary:#f8fafc;--bg-secondary:#f1f5f9;--bg-tertiary:#e2e8f0;
  --bg-glass:rgba(255,255,255,.82);--bg-glass-hover:rgba(248,250,252,.92);
  --border-subtle:rgba(148,163,184,.18);--border-medium:rgba(148,163,184,.3);--border-strong:rgba(148,163,184,.45);
  --text-primary:#0f172a;--text-secondary:#334155;--text-muted:#64748b;--text-dim:#94a3b8;
  --accent-blue:#0284c7;--accent-blue-glow:rgba(2,132,199,.18);
  --accent-cyan:#0891b2;--accent-green:#059669;--accent-green-glow:rgba(5,150,105,.18);
  --accent-amber:#d97706;--accent-amber-glow:rgba(217,119,6,.18);
  --accent-red:#dc2626;--accent-red-glow:rgba(220,38,38,.18);
  --accent-purple:#7c3aed;--accent-purple-glow:rgba(124,58,237,.18);
  --shadow-sm:0 2px 8px rgba(0,0,0,.05);--shadow-md:0 8px 24px rgba(0,0,0,.07);
  --shadow-lg:0 16px 48px rgba(0,0,0,.09);--shadow-xl:0 24px 64px rgba(0,0,0,.11);
  --input-bg:#ffffff;--input-border:rgba(148,163,184,.25);--input-focus-border:rgba(2,132,199,.45);
  --input-focus-glow:0 0 0 3px rgba(2,132,199,.08);
  --table-header-bg:#f8fafc;--table-row-hover:rgba(2,132,199,.04);--table-row-active:rgba(2,132,199,.08);
  --toast-bg-success:rgba(209,250,229,.97);--toast-bg-error:rgba(254,226,226,.97);--toast-bg-info:rgba(219,234,254,.97);
}

/* =========================================================
   ANIMATIONS
========================================================= */
@keyframes fadeInUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeInDown{from{opacity:0;transform:translateY(-12px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeInScale{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
@keyframes slideInRight{from{opacity:0;transform:translateX(120%)}to{opacity:1;transform:translateX(0)}}
@keyframes slideOutRight{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(120%)}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes countUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
@keyframes modalIn{from{opacity:0;transform:scale(.92) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
@keyframes modalOut{from{opacity:1;transform:scale(1) translateY(0)}to{opacity:0;transform:scale(.92) translateY(20px)}}
@keyframes ripple{to{transform:scale(4);opacity:0}}

/* =========================================================
   BASE
========================================================= */
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
body{
  margin:0;min-height:100vh;color:var(--text-primary);font-family:'Inter',system-ui,sans-serif;
  background:var(--bg-primary);transition:background var(--transition-slow),color var(--transition-slow);overflow-x:hidden;
}
[data-theme="dark"] body{
  background:radial-gradient(ellipse 80% 50% at 20% 0%,rgba(56,189,248,.07),transparent),
    radial-gradient(ellipse 60% 40% at 80% 0%,rgba(167,139,250,.05),transparent),
    radial-gradient(ellipse 50% 30% at 50% 100%,rgba(34,211,238,.03),transparent),
    linear-gradient(180deg,var(--bg-primary),var(--bg-secondary));
}
[data-theme="dark"] body::before{
  content:"";position:fixed;inset:0;pointer-events:none;opacity:.12;z-index:0;
  background-image:linear-gradient(rgba(255,255,255,.012) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.012) 1px,transparent 1px);background-size:40px 40px;
}
[data-theme="light"] body{background:linear-gradient(180deg,var(--bg-primary),var(--bg-secondary));}

.container{width:min(96%,1700px);margin:0 auto;padding:24px 16px 60px;position:relative;z-index:1;}
@media(min-width:768px){.container{padding:32px 24px 80px}}

/* =========================================================
   HEADER
========================================================= */
.header{
  position:relative;overflow:hidden;padding:28px 24px;margin-bottom:24px;
  border:1px solid var(--border-medium);border-radius:var(--radius-xl);
  background:var(--bg-glass);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  box-shadow:var(--shadow-lg);animation:fadeInUp .6s ease both;transition:all var(--transition-base);
}
.header::before{
  content:"";position:absolute;top:0;left:0;right:0;height:2px;border-radius:var(--radius-xl) var(--radius-xl) 0 0;
  background:linear-gradient(90deg,transparent,var(--accent-blue),var(--accent-cyan),var(--accent-purple),transparent);opacity:.6;
}
.header::after{
  content:"";position:absolute;right:-80px;top:-80px;width:280px;height:280px;border-radius:50%;
  background:radial-gradient(circle,rgba(56,189,248,.06),transparent 70%);pointer-events:none;
}
.header h1{
  margin:0 0 10px;font-size:clamp(22px,3vw,34px);font-weight:800;letter-spacing:-.8px;
  display:flex;align-items:center;gap:14px;color:var(--text-primary);animation:fadeInUp .5s .1s both;
}
.header h1 .header-icon{
  display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;
  border-radius:var(--radius-lg);background:linear-gradient(135deg,rgba(56,189,248,.18),rgba(34,211,238,.12));
  border:1px solid rgba(56,189,248,.2);color:var(--accent-blue);font-size:20px;flex-shrink:0;
  box-shadow:var(--shadow-glow-blue);animation:float 3s ease-in-out infinite;
}
.header p{margin:0;color:var(--text-muted);font-size:14px;line-height:1.6;animation:fadeInUp .5s .15s both;}
.header .digital-badge{
  display:inline-flex;align-items:center;gap:8px;margin-top:16px;padding:8px 14px;
  border-radius:var(--radius-full);background:rgba(34,211,238,.06);border:1px solid rgba(34,211,238,.15);
  color:var(--accent-cyan);font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;
  animation:fadeInUp .5s .2s both;
}
.header .digital-badge i{font-size:10px;animation:pulse 2s ease-in-out infinite;}

.header-top{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap;}
.header-content{flex:1;min-width:0}
.header-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;animation:fadeInUp .5s .25s both;}

.header-btn,.theme-toggle{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;
  padding:11px 18px;border-radius:var(--radius-md);border:1px solid var(--border-medium);
  background:var(--bg-glass);color:var(--text-secondary);text-decoration:none;font-size:13px;
  font-weight:700;cursor:pointer;transition:all var(--transition-base);backdrop-filter:blur(10px);
  position:relative;overflow:hidden;
}
.header-btn::before,.theme-toggle::before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,transparent,rgba(255,255,255,.05));opacity:0;transition:opacity var(--transition-base);}
.header-btn:hover::before,.theme-toggle:hover::before{opacity:1}
.header-btn:hover,.theme-toggle:hover{transform:translateY(-2px);border-color:var(--border-strong);box-shadow:var(--shadow-md);color:var(--text-primary);}
.header-btn:active,.theme-toggle:active{transform:translateY(0)}
.header-btn i,.theme-toggle i{font-size:14px;transition:transform var(--transition-base);}
.header-btn:hover i,.theme-toggle:hover i{transform:scale(1.1)}

.header-btn-refresh{border-color:rgba(56,189,248,.25);color:var(--accent-blue);}
.header-btn-refresh:hover{background:rgba(56,189,248,.08);border-color:rgba(56,189,248,.4);box-shadow:var(--shadow-glow-blue);}
.header-btn-back{border-color:rgba(52,211,153,.25);color:var(--accent-green);}
.header-btn-back:hover{background:rgba(52,211,153,.08);border-color:rgba(52,211,153,.4);box-shadow:0 0 20px var(--accent-green-glow);}
.theme-toggle{border-color:rgba(167,139,250,.25);color:var(--accent-purple);}
.theme-toggle:hover{background:rgba(167,139,250,.08);border-color:rgba(167,139,250,.4);box-shadow:0 0 20px var(--accent-purple-glow);}

.unit-nav{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px;animation:fadeInUp .5s .3s both;}
.unit-nav a{
  display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:var(--radius-md);
  border:1px solid var(--border-subtle);color:var(--text-muted);text-decoration:none;font-size:13px;
  font-weight:600;transition:all var(--transition-base);position:relative;overflow:hidden;
}
.unit-nav a::after{content:"";position:absolute;bottom:0;left:50%;width:0;height:2px;
  background:linear-gradient(90deg,var(--accent-blue),var(--accent-cyan));transition:all var(--transition-base);transform:translateX(-50%);
}
.unit-nav a:hover{border-color:var(--border-medium);color:var(--text-primary);background:var(--bg-glass-hover);transform:translateY(-1px);}
.unit-nav a:hover::after{width:60%}
.unit-nav a.active{
  background:linear-gradient(135deg,rgba(56,189,248,.12),rgba(34,211,238,.08));
  border-color:rgba(56,189,248,.35);color:var(--accent-blue);box-shadow:var(--shadow-glow-blue);
}
.unit-nav a.active::after{width:80%}
.unit-nav a i{font-size:13px}

/* =========================================================
   STATS
========================================================= */
.stats{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px;}
@media(min-width:640px){.stats{grid-template-columns:repeat(3,1fr);gap:14px}}
@media(min-width:1024px){.stats{grid-template-columns:repeat(5,1fr);gap:16px}}

.stat{
  position:relative;overflow:hidden;min-height:120px;padding:20px;
  border:1px solid var(--border-subtle);border-radius:var(--radius-xl);
  background:var(--bg-glass);backdrop-filter:blur(16px);box-shadow:var(--shadow-md);
  transition:all var(--transition-base);cursor:default;animation:fadeInUp .5s both;
}
.stat:nth-child(1){animation-delay:.05s}.stat:nth-child(2){animation-delay:.1s}
.stat:nth-child(3){animation-delay:.15s}.stat:nth-child(4){animation-delay:.2s}
.stat:nth-child(5){animation-delay:.25s}

.stat::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;border-radius:var(--radius-xl) var(--radius-xl) 0 0;opacity:.7;transition:opacity var(--transition-base);}
.stat:nth-child(1)::before{background:linear-gradient(90deg,var(--accent-blue),var(--accent-cyan))}
.stat:nth-child(2)::before{background:linear-gradient(90deg,var(--accent-blue),#60a5fa)}
.stat:nth-child(3)::before{background:linear-gradient(90deg,var(--accent-green),#6ee7b7)}
.stat:nth-child(4)::before{background:linear-gradient(90deg,var(--accent-amber),#fcd34d)}
.stat:nth-child(5)::before{background:linear-gradient(90deg,var(--accent-red),#fca5a5)}

.stat::after{content:"";position:absolute;width:120px;height:120px;right:-40px;bottom:-50px;border-radius:50%;opacity:.03;transition:all var(--transition-slow);}
.stat:nth-child(1)::after{background:var(--accent-blue)}.stat:nth-child(2)::after{background:var(--accent-blue)}
.stat:nth-child(3)::after{background:var(--accent-green)}.stat:nth-child(4)::after{background:var(--accent-amber)}
.stat:nth-child(5)::after{background:var(--accent-red)}

.stat:hover{transform:translateY(-4px);border-color:var(--border-medium);box-shadow:var(--shadow-lg);}
.stat:hover::after{opacity:.06;transform:scale(1.2)}.stat:hover::before{opacity:1}

.stat-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
.stat-icon{
  display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;
  border-radius:var(--radius-md);font-size:14px;transition:all var(--transition-base);
}
.stat:nth-child(1) .stat-icon{background:rgba(56,189,248,.1);color:var(--accent-blue)}
.stat:nth-child(2) .stat-icon{background:rgba(56,189,248,.1);color:var(--accent-blue)}
.stat:nth-child(3) .stat-icon{background:rgba(52,211,153,.1);color:var(--accent-green)}
.stat:nth-child(4) .stat-icon{background:rgba(251,191,36,.1);color:var(--accent-amber)}
.stat:nth-child(5) .stat-icon{background:rgba(248,113,113,.1);color:var(--accent-red)}
.stat:hover .stat-icon{transform:scale(1.1) rotate(-5deg)}

.stat-title{color:var(--text-dim);font-size:10px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;}
.stat-number{margin-top:6px;font-size:clamp(26px,3vw,32px);font-weight:900;letter-spacing:-1.5px;color:var(--text-primary);line-height:1;animation:countUp .6s ease both;}
.stat:nth-child(1) .stat-number{color:var(--text-primary)}.stat:nth-child(2) .stat-number{color:var(--accent-blue)}
.stat:nth-child(3) .stat-number{color:var(--accent-green)}.stat:nth-child(4) .stat-number{color:var(--accent-amber)}
.stat:nth-child(5) .stat-number{color:var(--accent-red)}

/* =========================================================
   FILTER
========================================================= */
.filter{
  border:1px solid var(--border-subtle);border-radius:var(--radius-xl);background:var(--bg-glass);
  backdrop-filter:blur(16px);box-shadow:var(--shadow-md);padding:20px;margin-bottom:24px;
  animation:fadeInUp .5s .15s both;transition:all var(--transition-base);
}
.filter:hover{border-color:var(--border-medium);box-shadow:var(--shadow-lg);}
.filter form{display:grid;grid-template-columns:1fr;gap:12px;align-items:end;}
@media(min-width:640px){.filter form{grid-template-columns:minmax(200px,2fr) minmax(150px,1fr) minmax(150px,1fr) auto}}

.form-group{margin-bottom:0}
.form-group label{display:block;margin-bottom:6px;color:var(--text-dim);font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;}

input,select,textarea{
  width:100%;color:var(--text-primary);background:var(--input-bg);border:1px solid var(--input-border);
  border-radius:var(--radius-md);outline:none;padding:12px 14px;font-family:inherit;font-size:14px;transition:all var(--transition-base);
}
input::placeholder,textarea::placeholder{color:var(--text-dim);opacity:.7}
input:focus,select:focus,textarea:focus{border-color:var(--input-focus-border);box-shadow:var(--input-focus-glow);background:rgba(56,189,248,.02);}
select{cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;padding-right:36px;}
[data-theme="light"] select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");}

/* Buttons */
.btn,button[type="submit"],button[type="button"]{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;
  border-radius:var(--radius-md);border:1px solid transparent;font-family:inherit;font-size:13px;
  font-weight:700;cursor:pointer;transition:all var(--transition-base);position:relative;overflow:hidden;
}
.btn::after,button::after{content:"";position:absolute;inset:0;background:linear-gradient(135deg,transparent,rgba(255,255,255,.1));opacity:0;transition:opacity var(--transition-base);}
.btn:hover::after,button:hover::after{opacity:1}
.btn:hover,button:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);}
.btn:active,button:active{transform:translateY(0)}

.btn-primary{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;box-shadow:0 4px 15px rgba(37,99,235,.3);}
.btn-primary:hover{box-shadow:0 8px 25px rgba(37,99,235,.4);}
.btn-success{background:linear-gradient(135deg,#059669,#047857);color:#fff;box-shadow:0 4px 15px rgba(5,150,105,.3);}
.btn-success:hover{box-shadow:0 8px 25px rgba(5,150,105,.4);}
.btn-danger{background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;box-shadow:0 4px 15px rgba(220,38,38,.3);}
.btn-danger:hover{box-shadow:0 8px 25px rgba(220,38,38,.4);}
.btn-warning{background:linear-gradient(135deg,#d97706,#b45309);color:#fff;box-shadow:0 4px 15px rgba(217,119,6,.3);}
.btn-secondary{background:var(--bg-tertiary);color:var(--text-secondary);border-color:var(--border-medium);}
.btn-secondary:hover{background:var(--bg-glass-hover);color:var(--text-primary);}
.btn-sm{padding:8px 14px;font-size:12px;gap:6px;}
.btn i{font-size:13px;transition:transform var(--transition-base)}.btn:hover i{transform:scale(1.15)}

/* =========================================================
   TABLE
========================================================= */
.table-container{
  border:1px solid var(--border-subtle);border-radius:var(--radius-xl);background:var(--bg-glass);
  backdrop-filter:blur(16px);box-shadow:var(--shadow-lg);overflow:hidden;
  animation:fadeInUp .5s .2s both;transition:all var(--transition-base);
}
.table-container:hover{border-color:var(--border-medium);}

.table-header{
  display:flex;align-items:center;justify-content:space-between;padding:18px 20px;
  border-bottom:1px solid var(--border-subtle);background:rgba(255,255,255,.015);
}
.table-header h3{margin:0;font-size:14px;font-weight:700;color:var(--text-secondary);display:flex;align-items:center;gap:8px;}
.table-header h3 i{color:var(--accent-blue)}

.table-scroll-hint{
  display:none;align-items:center;gap:6px;padding:10px 16px;font-size:11px;
  color:var(--text-dim);border-top:1px solid var(--border-subtle);background:rgba(255,255,255,.015);
}
.table-scroll-hint i{animation:float 2s ease-in-out infinite}

table{width:100%;border-collapse:separate;border-spacing:0;}

thead th{
  padding:14px 16px;background:var(--table-header-bg);color:var(--text-dim);
  border-bottom:1px solid var(--border-subtle);font-size:10px;font-weight:800;
  text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;cursor:grab;
  user-select:none;position:relative;transition:background var(--transition-fast);
}
thead th:active{cursor:grabbing;}
thead th.dragging{opacity:.4;background:rgba(56,189,248,.15)!important;}
thead th.drag-over::after{content:"";position:absolute;right:0;top:0;bottom:0;width:3px;background:var(--accent-cyan);}

tbody td{
  padding:14px 16px;color:var(--text-primary);border-bottom:1px solid rgba(148,163,184,.06);
  vertical-align:middle;font-size:13px;transition:background var(--transition-fast);
}
tbody tr{transition:all var(--transition-fast);animation:fadeInUp .4s ease both;}
tbody tr:nth-child(1){animation-delay:.02s}tbody tr:nth-child(2){animation-delay:.04s}
tbody tr:nth-child(3){animation-delay:.06s}tbody tr:nth-child(4){animation-delay:.08s}
tbody tr:nth-child(5){animation-delay:.1s}tbody tr:nth-child(6){animation-delay:.12s}
tbody tr:nth-child(7){animation-delay:.14s}tbody tr:nth-child(8){animation-delay:.16s}
tbody tr:nth-child(9){animation-delay:.18s}tbody tr:nth-child(10){animation-delay:.2s}
tbody tr:hover{background:var(--table-row-hover)!important;}
tbody tr:hover td{color:var(--text-primary);}

/* Badges */
.badge{
  display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border-radius:var(--radius-full);
  font-size:10px;font-weight:800;letter-spacing:.3px;transition:all var(--transition-base);
}
.badge i{font-size:9px}
.badge-blue{background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;box-shadow:0 2px 8px rgba(29,78,216,.25);}
.badge-green{background:linear-gradient(135deg,#047857,#059669);color:#fff;box-shadow:0 2px 8px rgba(5,150,105,.25);}
.badge-amber{background:linear-gradient(135deg,#b45309,#d97706);color:#fff;box-shadow:0 2px 8px rgba(217,119,6,.25);}
.badge-red{background:linear-gradient(135deg,#b91c1c,#dc2626);color:#fff;box-shadow:0 2px 8px rgba(220,38,38,.25);}
.badge-purple{background:linear-gradient(135deg,#6d28d9,#7c3aed);color:#fff;box-shadow:0 2px 8px rgba(124,58,237,.25);}
.badge-gray{background:var(--bg-tertiary);color:var(--text-muted);border:1px solid var(--border-subtle);}

/* Actions */
.actions{display:flex;gap:7px;flex-wrap:wrap;}
.actions form{margin:0;}
.actions .btn{padding:8px 12px;font-size:12px;gap:5px;}
.actions .btn i{font-size:11px}

.col-hint{display:inline-flex;align-items:center;gap:6px;}
.col-hint::before{content:"\22EE\22EE";font-size:10px;opacity:.25;letter-spacing:-2px;}

/* =========================================================
   MODALS
========================================================= */
.modal{
  display:none;position:fixed;z-index:9999;inset:0;background:rgba(2,6,23,.8);
  padding:20px;overflow:auto;backdrop-filter:blur(12px);animation:fadeInScale .2s ease;
}
[data-theme="light"] .modal{background:rgba(15,23,42,.55);}
.modal.active{display:flex;align-items:flex-start;justify-content:center;}

.modal-content{
  width:100%;max-width:760px;margin:auto;background:linear-gradient(145deg,#0f1d32,#0b1426);
  border:1px solid rgba(56,189,248,.15);border-radius:var(--radius-xl);padding:28px;
  box-shadow:var(--shadow-xl);animation:modalIn .35s cubic-bezier(.22,1,.36,1) both;
}
[data-theme="light"] .modal-content{background:linear-gradient(145deg,#ffffff,#f8fafc);border-color:rgba(2,132,199,.15);}

.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid var(--border-subtle);padding-bottom:16px;}
.modal-header h2{margin:0;font-size:20px;display:flex;align-items:center;gap:10px;}
.modal-header h2 i{color:var(--accent-blue);font-size:18px;}
.close{font-size:28px;cursor:pointer;color:var(--text-dim);line-height:1;transition:all var(--transition-base);width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:var(--radius-md);}
.close:hover{color:var(--text-primary);background:var(--bg-tertiary);transform:rotate(90deg);}
.modal-footer{display:flex;justify-content:flex-end;gap:10px;margin-top:24px;padding-top:16px;border-top:1px solid var(--border-subtle);}

/* Component Grid */
.component-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:15px 0 22px;}
@media(min-width:640px){.component-grid{grid-template-columns:repeat(4,1fr)}}
.component-option{position:relative;}
.component-option input{position:absolute;opacity:0;}
.component-option label{
  display:block;padding:16px;text-align:center;border:1px solid var(--border-medium);border-radius:var(--radius-md);
  cursor:pointer;background:var(--bg-primary);transition:all var(--transition-base);color:var(--text-secondary);font-size:13px;font-weight:600;
}
.component-option label:hover{border-color:var(--accent-blue);transform:translateY(-2px);box-shadow:var(--shadow-sm);}
.component-option input:checked + label{
  border-color:var(--accent-blue);background:linear-gradient(135deg,rgba(56,189,248,.15),rgba(34,211,238,.1));
  box-shadow:0 0 0 2px rgba(56,189,248,.15),var(--shadow-sm);color:var(--accent-blue);
}
.component-option input:disabled + label{opacity:.35;cursor:not-allowed;border-color:var(--border-subtle);background:var(--bg-secondary);transform:none;box-shadow:none;}
.component-option label i{display:block;font-size:20px;margin-bottom:6px;}

/* Destination Grid */
.destination-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin:15px 0 22px;}
@media(min-width:640px){.destination-grid{grid-template-columns:repeat(5,1fr)}}
.destination-option{position:relative;}
.destination-option input{position:absolute;opacity:0;}
.destination-option label{
  display:block;padding:14px 8px;text-align:center;border:1px solid var(--border-medium);border-radius:var(--radius-md);
  cursor:pointer;background:var(--bg-primary);font-size:13px;transition:all var(--transition-base);color:var(--text-secondary);font-weight:600;
}
.destination-option label:hover{border-color:var(--accent-green);transform:translateY(-2px);box-shadow:var(--shadow-sm);}
.destination-option input:checked + label{
  border-color:var(--accent-green);background:linear-gradient(135deg,rgba(52,211,153,.15),rgba(16,185,129,.1));
  box-shadow:0 0 0 2px rgba(52,211,153,.15),var(--shadow-sm);color:var(--accent-green);
}
.destination-option label i{display:block;font-size:18px;margin-bottom:6px;}

/* Form Grid */
.form-grid{display:grid;grid-template-columns:1fr;gap:15px;}
@media(min-width:640px){.form-grid{grid-template-columns:1fr 1fr}}
.form-group{margin-bottom:0;}
.form-group label{display:block;margin-bottom:7px;color:var(--text-muted);font-size:13px;font-weight:700;}
.form-group label .required{color:var(--accent-red);margin-left:2px;}
.full{grid-column:1 / -1;}

/* Detail Grid */
.detail-grid{display:grid;grid-template-columns:1fr;gap:12px;}
@media(min-width:640px){.detail-grid{grid-template-columns:repeat(2,1fr)}}
.detail-item{
  background:var(--bg-primary);padding:14px;border-radius:var(--radius-md);border:1px solid var(--border-subtle);
  transition:all var(--transition-base);
}
.detail-item:hover{border-color:var(--border-medium);transform:translateY(-1px);box-shadow:var(--shadow-sm);}
.detail-label{color:var(--text-dim);font-size:10px;margin-bottom:5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;}
.detail-value{color:var(--text-primary);font-weight:700;font-size:14px;word-break:break-word;}

/* Preview Box */
.preview-box{
  background:var(--bg-primary);border:1px solid var(--border-subtle);border-radius:var(--radius-md);
  padding:16px;margin-bottom:20px;font-size:13px;line-height:1.9;color:var(--text-muted);transition:all var(--transition-base);
}
.preview-box:hover{border-color:var(--border-medium);}
.preview-box b{color:var(--accent-blue);font-weight:700;}
.preview-box i{color:var(--text-dim);margin-right:6px;width:16px;text-align:center;}

/* =========================================================
   TOAST
========================================================= */
.toast-container{position:fixed;top:24px;right:24px;z-index:10000;display:flex;flex-direction:column;gap:12px;max-width:400px;width:100%;pointer-events:none;}
.toast{
  position:relative;pointer-events:auto;padding:16px 20px;border-radius:var(--radius-lg);font-size:14px;
  font-weight:600;color:#fff;box-shadow:var(--shadow-xl);transform:translateX(120%);opacity:0;
  animation:toastSlideIn .45s cubic-bezier(.22,1,.36,1) forwards;overflow:hidden;display:flex;align-items:center;gap:12px;
}
.toast.success{background:var(--toast-bg-success);border:1px solid var(--toast-border-success);}
.toast.error{background:var(--toast-bg-error);border:1px solid var(--toast-border-error);}
.toast.info{background:var(--toast-bg-info);border:1px solid var(--toast-border-info);}
[data-theme="light"] .toast.success{color:#064e3b;}[data-theme="light"] .toast.error{color:#7f1d1d;}[data-theme="light"] .toast.info{color:#1e3a8a;}
.toast-icon{font-size:20px;flex-shrink:0;}
.toast-content{flex:1;}
.toast-title{font-weight:800;font-size:13px;margin-bottom:2px;}
.toast-message{font-weight:500;opacity:.9;font-size:13px;}
.toast-progress{position:absolute;bottom:0;left:0;height:3px;background:rgba(255,255,255,.35);animation:toastProgress 5s linear forwards;}
[data-theme="light"] .toast-progress{background:rgba(0,0,0,.12);}
.toast-close{position:absolute;top:8px;right:10px;background:none;border:none;color:inherit;font-size:20px;cursor:pointer;opacity:.5;transition:opacity .2s;padding:0 4px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:var(--radius-sm);}
.toast-close:hover{opacity:1;background:rgba(255,255,255,.1);}
[data-theme="light"] .toast-close:hover{background:rgba(0,0,0,.05);}
@keyframes toastSlideIn{to{transform:translateX(0);opacity:1}}
@keyframes toastSlideOut{to{transform:translateX(120%);opacity:0}}
@keyframes toastProgress{to{width:0%}}

/* =========================================================
   EMPTY STATE
========================================================= */
.empty-state{text-align:center;padding:60px 20px;color:var(--text-dim);animation:fadeInUp .5s ease;}
.empty-state i{font-size:48px;margin-bottom:16px;color:var(--border-medium);display:block;}
.empty-state h3{margin:0 0 8px;font-size:16px;color:var(--text-muted);}
.empty-state p{margin:0;font-size:13px;}

/* =========================================================
   SCROLLBAR
========================================================= */
::-webkit-scrollbar{width:8px;height:8px}
::-webkit-scrollbar-track{background:var(--bg-secondary)}
::-webkit-scrollbar-thumb{background:var(--border-strong);border-radius:var(--radius-full)}
::-webkit-scrollbar-thumb:hover{background:var(--text-dim)}

/* =========================================================
   RESPONSIVE
========================================================= */
@media(max-width:1200px){
  .component-grid{grid-template-columns:repeat(2,1fr)}
  .destination-grid{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:760px){
  .container{width:94%;padding:16px 12px 40px}
  .header{padding:22px 18px;border-radius:var(--radius-lg)}
  .header h1{font-size:22px}
  .header-top{flex-direction:column;align-items:stretch;}
  .header-actions{width:100%;}
  .header-btn,.theme-toggle{flex:1;justify-content:center;}
  .stats{grid-template-columns:repeat(2,1fr)}
  .filter form,.form-grid{grid-template-columns:1fr!important}
  .component-grid,.detail-grid,.destination-grid{grid-template-columns:1fr}
  .table-container{overflow-x:auto!important}
  table{min-width:1000px}
  .table-scroll-hint{display:flex!important}
  .actions .btn{padding:7px 10px;font-size:11px;}
  .toast-container{left:16px;right:16px;top:12px;max-width:none;}
  .modal{padding:12px}
  .modal-content{padding:20px;margin-top:20px;}
}
@media(max-width:480px){
  .stats{grid-template-columns:1fr}
  .stat{min-height:100px;padding:16px}
  .stat-number{font-size:26px}
  .unit-nav a{padding:7px 12px;font-size:12px;}
  .header h1 .header-icon{width:40px;height:40px;font-size:16px;}
}

/* =========================================================
   SKELETON LOADING
========================================================= */
.skeleton{background:linear-gradient(90deg,var(--bg-tertiary) 25%,var(--bg-glass-hover) 50%,var(--bg-tertiary) 75%);background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:var(--radius-sm);}
</style>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<div class="container">

<!-- HEADER -->
<div class="header">
    <div class="header-top">
        <div class="header-content">
            <h1>
                <span class="header-icon"><i class="fas fa-warehouse"></i></span>
                Perbaikan Digital - <?= esc($unitLabel) ?>
            </h1>
            <p>Pengelolaan barang, pemindahan perangkat, restore dan distribusi inventaris.</p>
            <div class="digital-badge"><i class="fas fa-signal"></i> DIGITAL WAREHOUSE MANAGEMENT</div>
            <div class="unit-nav">
                <a href="gudang.php?unit=kartika1" class="<?= $unit === 'kartika1' ? 'active' : '' ?>"><i class="fas fa-hospital"></i> Kartika 1</a>
                <a href="gudang.php?unit=kartika2" class="<?= $unit === 'kartika2' ? 'active' : '' ?>"><i class="fas fa-hospital"></i> Kartika 2</a>
                <a href="gudang.php?unit=kartika3" class="<?= $unit === 'kartika3' ? 'active' : '' ?>"><i class="fas fa-hospital"></i> Kartika 3</a>
                <a href="gudang.php?unit=cicu" class="<?= $unit === 'cicu' ? 'active' : '' ?>"><i class="fas fa-procedures"></i> CICU</a>
                <a href="gudang.php?unit=cvc" class="<?= $unit === 'cvc' ? 'active' : '' ?>"><i class="fas fa-heart-pulse"></i> CVC</a>
            </div>
        </div>
        <div class="header-actions">
            <button type="button" class="theme-toggle" id="themeToggle" title="Ganti Tema">
                <i class="fas fa-sun" id="themeIcon"></i> <span id="themeText">Light</span>
            </button>
            <button type="button" class="header-btn header-btn-refresh" onclick="window.location.reload()" title="Refresh data">
                <i class="fas fa-rotate"></i> <span class="btn-text">Refresh</span>
            </button>
            <a href="inventaris.php" class="header-btn header-btn-back" title="Kembali ke Inventaris">
                <i class="fas fa-arrow-left"></i> <span class="btn-text">Inventaris</span>
            </a>
        </div>
    </div>
</div>

<!-- STATS -->
<div class="stats">
    <div class="stat">
        <div class="stat-header">
            <div class="stat-title">Total Perbaikan</div>
            <div class="stat-icon"><i class="fas fa-cubes"></i></div>
        </div>
        <div class="stat-number"><?= number_format($totalGudang) ?></div>
    </div>
    <div class="stat">
        <div class="stat-header">
            <div class="stat-title">Stok</div>
            <div class="stat-icon"><i class="fas fa-box"></i></div>
        </div>
        <div class="stat-number"><?= number_format($stats['stok']) ?></div>
    </div>
    <div class="stat">
        <div class="stat-header">
            <div class="stat-title">Siap Pakai</div>
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-number"><?= number_format($stats['siap_pakai']) ?></div>
    </div>
    <div class="stat">
        <div class="stat-header">
            <div class="stat-title">Perbaikan</div>
            <div class="stat-icon"><i class="fas fa-screwdriver-wrench"></i></div>
        </div>
        <div class="stat-number"><?= number_format($stats['perbaikan']) ?></div>
    </div>
    <div class="stat">
        <div class="stat-header">
            <div class="stat-title">Rusak</div>
            <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
        </div>
        <div class="stat-number"><?= number_format($stats['rusak']) ?></div>
    </div>
</div>

<!-- FILTER -->
<div class="filter">
    <form method="GET" action="gudang.php">
        <input type="hidden" name="unit" value="<?= esc($unit) ?>">
        <div class="form-group">
            <label><i class="fas fa-magnifying-glass"></i> Pencarian</label>
            <input type="text" name="search" placeholder="Cari ID, PC, user, ruangan, printer..." value="<?= esc($search) ?>">
        </div>
        <div class="form-group">
            <label><i class="fas fa-filter"></i> Status</label>
            <select name="status">
                <option value="">Semua Status</option>
                <option value="Stok" <?= $statusFilter === 'Stok' ? 'selected' : '' ?>>Stok</option>
                <option value="Siap Pakai" <?= $statusFilter === 'Siap Pakai' ? 'selected' : '' ?>>Siap Pakai</option>
                <option value="Perbaikan" <?= $statusFilter === 'Perbaikan' ? 'selected' : '' ?>>Perbaikan</option>
                <option value="Rusak" <?= $statusFilter === 'Rusak' ? 'selected' : '' ?>>Rusak</option>
            </select>
        </div>
        <div class="form-group">
            <label><i class="fas fa-tag"></i> Jenis Barang</label>
            <select name="jenis">
                <option value="">Semua Jenis</option>
                <option value="PC" <?= $jenisFilter === 'PC' ? 'selected' : '' ?>>PC</option>
                <option value="Printer 1" <?= $jenisFilter === 'Printer 1' ? 'selected' : '' ?>>Printer 1</option>
                <option value="Printer 2" <?= $jenisFilter === 'Printer 2' ? 'selected' : '' ?>>Printer 2</option>
                <option value="PC + Printer" <?= $jenisFilter === 'PC + Printer' ? 'selected' : '' ?>>PC + Printer</option>
            </select>
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <button class="btn btn-primary" type="submit"><i class="fas fa-magnifying-glass"></i> Cari</button>
        </div>
    </form>
</div>

<!-- TABLE -->
<div class="table-container">
    <div class="table-header">
        <h3><i class="fas fa-list"></i> Daftar Barang Perbaikan</h3>
        <span style="font-size:12px;color:var(--text-dim);"><i class="fas fa-info-circle"></i> Geser kolom header untuk mengatur urutan</span>
    </div>
    <table id="gudangTable">
        <thead>
            <tr id="tableHeaderRow">
                <th draggable="true" data-col="id"><span class="col-hint">ID</span></th>
                <th draggable="true" data-col="jenis"><span class="col-hint">Jenis</span></th>
                <th draggable="true" data-col="pc"><span class="col-hint">PC</span></th>
                <th draggable="true" data-col="user"><span class="col-hint">User</span></th>
                <th draggable="true" data-col="printer1"><span class="col-hint">Printer 1</span></th>
                <th draggable="true" data-col="printer2"><span class="col-hint">Printer 2</span></th>
                <th draggable="true" data-col="lokasi"><span class="col-hint">Lokasi</span></th>
                <th draggable="true" data-col="status"><span class="col-hint">Status</span></th>
                <th draggable="true" data-col="tahun"><span class="col-hint">Tahun</span></th>
                <th draggable="true" data-col="aksi"><span class="col-hint">Aksi</span></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($gudangData)): ?>
        <tr>
            <td colspan="10">
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>Tidak ada data Perbaikan</h3>
                    <p>Data perbaikan untuk unit ini belum tersedia atau tidak cocok dengan filter.</p>
                </div>
            </td>
        </tr>
        <?php else: ?>
        <?php foreach ($gudangData as $row): ?>
        <tr>
            <td data-col="id"><b>#<?= esc($row['id'] ?? '') ?></b></td>
            <td data-col="jenis">
                <?php
                $jenis = $row['jenis_barang'];
                $badgeClass = 'badge-blue';
                $badgeIcon = 'fa-desktop';
                if ($jenis === 'PC + Printer') { $badgeClass = 'badge-green'; $badgeIcon = 'fa-laptop-code'; }
                elseif ($jenis === 'Printer 1' || $jenis === 'Printer 2') { $badgeClass = 'badge-amber'; $badgeIcon = 'fa-print'; }
                ?>
                <span class="badge <?= $badgeClass ?>"><i class="fas <?= $badgeIcon ?>"></i> <?= esc($jenis) ?></span>
            </td>
            <td data-col="pc"><?= esc($row['nama_pc'] ?: '-') ?></td>
            <td data-col="user"><?= esc($row['nama_user'] ?: '-') ?></td>
            <td data-col="printer1"><?= esc($row['printer_1'] ?: '-') ?></td>
            <td data-col="printer2"><?= esc($row['printer_2'] ?: '-') ?></td>
            <td data-col="lokasi">
                <div style="font-size:12px;line-height:1.7;">
                    <div><i class="fas fa-building" style="color:var(--accent-blue);width:14px;"></i> <?= esc($row['gedung'] ?: '-') ?></div>
                    <div><i class="fas fa-layer-group" style="color:var(--accent-cyan);width:14px;"></i> Lantai <?= esc($row['lantai'] ?: '-') ?></div>
                    <div><i class="fas fa-door-open" style="color:var(--accent-purple);width:14px;"></i> <?= esc($row['ruangan'] ?: '-') ?></div>
                </div>
            </td>
            <td data-col="status">
                <?php
                $status = $row['status_gudang'] ?? 'Stok';
                $statusClass = 'badge-blue';
                $statusIcon = 'fa-box';
                if ($status === 'Siap Pakai') { $statusClass = 'badge-green'; $statusIcon = 'fa-check-circle'; }
                elseif ($status === 'Perbaikan') { $statusClass = 'badge-amber'; $statusIcon = 'fa-screwdriver-wrench'; }
                elseif ($status === 'Rusak') { $statusClass = 'badge-red'; $statusIcon = 'fa-triangle-exclamation'; }
                ?>
                <span class="badge <?= $statusClass ?>"><i class="fas <?= $statusIcon ?>"></i> <?= esc($status) ?></span>
            </td>
            <td data-col="tahun"><?= esc($row['tahun_pengadaan'] ?: '-') ?></td>
            <td data-col="aksi">
                <div class="actions">
                    <button class="btn btn-secondary btn-sm" type="button" onclick='showDetail(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' title="Lihat detail">
                        <i class="fas fa-eye"></i> Detail
                    </button>
                    <button class="btn btn-success btn-sm" type="button" onclick='openKirimModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' title="Kirim barang">
                        <i class="fas fa-paper-plane"></i> Kirim
                    </button>
                    <button class="btn btn-warning btn-sm" type="button" onclick="openStatusModal(<?= (int)$row['id'] ?>, '<?= esc($status) ?>')" title="Ubah status">
                        <i class="fas fa-sliders"></i> Status
                    </button>
                    <form method="POST" action="gudang.php?unit=<?= esc($unit) ?>" onsubmit="return confirm('Restore data ini ke inventaris <?= esc($unitLabel) ?>?');" style="display:inline;">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <button type="submit" class="btn btn-primary btn-sm" name="restore_gudang" title="Restore ke inventaris">
                            <i class="fas fa-rotate-left"></i> Restore
                        </button>
                    </form>
                    <form method="POST" action="gudang.php?unit=<?= esc($unit) ?>" onsubmit="return confirm('Yakin ingin menghapus data gudang ini?');" style="display:inline;">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" name="hapus_gudang" title="Hapus data">
                            <i class="fas fa-trash-can"></i> Hapus
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <div class="table-scroll-hint">
        <i class="fas fa-hand-pointer"></i> Geser tabel ke kiri/kanan untuk melihat data lengkap
    </div>
</div>

</div>

<!-- MODAL KIRIM -->
<div class="modal" id="modalKirim">
<div class="modal-content">
<div class="modal-header">
    <h2><i class="fas fa-paper-plane"></i> Kirim Barang</h2>
    <span class="close" onclick="closeModal('modalKirim')">&times;</span>
</div>
<form method="POST" action="gudang.php?unit=<?= esc($unit) ?>" id="formKirim">
<input type="hidden" name="kirim_ruangan" value="1">
<input type="hidden" name="id" id="kirim_id">

<div class="form-group">
    <label><i class="fas fa-box"></i> Pilih Barang yang Akan Dikirim</label>
    <div class="component-grid">
        <div class="component-option">
            <input type="radio" name="tipe_kirim" value="pc" id="kirim_pc">
            <label for="kirim_pc"><i class="fas fa-desktop"></i> PC</label>
        </div>
        <div class="component-option">
            <input type="radio" name="tipe_kirim" value="printer1" id="kirim_printer1">
            <label for="kirim_printer1"><i class="fas fa-print"></i> Printer 1</label>
        </div>
        <div class="component-option">
            <input type="radio" name="tipe_kirim" value="printer2" id="kirim_printer2">
            <label for="kirim_printer2"><i class="fas fa-print"></i> Printer 2</label>
        </div>
        <div class="component-option">
            <input type="radio" name="tipe_kirim" value="semua" id="kirim_semua">
            <label for="kirim_semua"><i class="fas fa-layer-group"></i> Semua</label>
        </div>
    </div>
</div>

<div class="form-group">
    <label><i class="fas fa-hospital"></i> Pilih Tujuan Pengiriman</label>
    <div class="destination-grid">
        <div class="destination-option">
            <input type="radio" name="tujuan_kirim" value="kartika1" id="tujuan_kartika1">
            <label for="tujuan_kartika1"><i class="fas fa-hospital"></i> Kartika 1</label>
        </div>
        <div class="destination-option">
            <input type="radio" name="tujuan_kirim" value="kartika2" id="tujuan_kartika2">
            <label for="tujuan_kartika2"><i class="fas fa-hospital"></i> Kartika 2</label>
        </div>
        <div class="destination-option">
            <input type="radio" name="tujuan_kirim" value="kartika3" id="tujuan_kartika3">
            <label for="tujuan_kartika3"><i class="fas fa-hospital"></i> Kartika 3</label>
        </div>
        <div class="destination-option">
            <input type="radio" name="tujuan_kirim" value="cicu" id="tujuan_cicu">
            <label for="tujuan_cicu"><i class="fas fa-procedures"></i> CICU</label>
        </div>
        <div class="destination-option">
            <input type="radio" name="tujuan_kirim" value="cvc" id="tujuan_cvc">
            <label for="tujuan_cvc"><i class="fas fa-heart-pulse"></i> CVC</label>
        </div>
    </div>
</div>

<div id="previewKirim" class="preview-box">
    <i class="fas fa-circle-info"></i> Pilih barang terlebih dahulu untuk melihat preview.
</div>

<div class="form-grid">
    <div class="form-group">
        <label>Gedung Tujuan <span class="required">*</span></label>
        <select name="gedung_tujuan" id="kirim_gedung" required>
            <option value="">-- Pilih Tujuan Dulu --</option>
        </select>
    </div>
    <div class="form-group">
        <label>Lantai Tujuan <span class="required">*</span></label>
        <select name="lantai_tujuan" id="kirim_lantai" required disabled>
            <option value="">-- Pilih Gedung Dulu --</option>
        </select>
    </div>
    <div class="form-group">
        <label>Nama User Tujuan <span class="required">*</span></label>
        <select name="nama_user_tujuan" id="kirim_user" required disabled>
            <option value="">-- Pilih Lantai Dulu --</option>
        </select>
    </div>
    <div class="form-group">
        <label>Ruangan Tujuan</label>
        <input type="text" name="ruangan_tujuan" id="kirim_ruangan" readonly placeholder="Otomatis terisi">
    </div>
    <div class="form-group">
        <label>Nama PC (Referensi)</label>
        <input type="text" id="kirim_nama_pc" readonly placeholder="Otomatis terisi">
    </div>
    <div class="form-group full">
        <label>Catatan Pengiriman</label>
        <textarea name="catatan_kirim" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" onclick="closeModal('modalKirim')"><i class="fas fa-xmark"></i> Batal</button>
    <button type="submit" class="btn btn-success" onclick="return validateKirim();"><i class="fas fa-paper-plane"></i> Kirim Barang</button>
</div>
</form>
</div>
</div>

<!-- MODAL STATUS -->
<div class="modal" id="modalStatus">
<div class="modal-content">
<div class="modal-header">
    <h2><i class="fas fa-sliders"></i> Ubah Status Perbaikan</h2>
    <span class="close" onclick="closeModal('modalStatus')">&times;</span>
</div>
<form method="POST" action="gudang.php?unit=<?= esc($unit) ?>">
<input type="hidden" name="update_status" value="1">
<input type="hidden" name="id" id="status_id">
<div class="form-group">
    <label><i class="fas fa-tag"></i> Status Barang</label>
    <select name="status_gudang" id="status_value">
        <option value="Stok">Stok</option>
        <option value="Siap Pakai">Siap Pakai</option>
        <option value="Perbaikan">Perbaikan</option>
        <option value="Rusak">Rusak</option>
    </select>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" onclick="closeModal('modalStatus')"><i class="fas fa-xmark"></i> Batal</button>
    <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Simpan Status</button>
</div>
</form>
</div>
</div>

<!-- MODAL DETAIL -->
<div class="modal" id="modalDetail">
<div class="modal-content">
<div class="modal-header">
    <h2><i class="fas fa-circle-info"></i> Detail Barang</h2>
    <span class="close" onclick="closeModal('modalDetail')">&times;</span>
</div>
<div class="detail-grid" id="detailContent"></div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" onclick="closeModal('modalDetail')"><i class="fas fa-xmark"></i> Tutup</button>
</div>
</div>
</div>

<script>
/* =========================================================
   THEME TOGGLE
========================================================= */
(function() {
    const html = document.documentElement;
    const toggleBtn = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');
    const text = document.getElementById('themeText');
    const saved = localStorage.getItem('gudang_theme') || 'dark';

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        if (theme === 'light') {
            icon.className = 'fas fa-moon';
            text.textContent = 'Dark';
        } else {
            icon.className = 'fas fa-sun';
            text.textContent = 'Light';
        }
    }
    applyTheme(saved);

    toggleBtn.addEventListener('click', function() {
        const current = html.getAttribute('data-theme') || 'dark';
        const next = current === 'dark' ? 'light' : 'dark';
        applyTheme(next);
        localStorage.setItem('gudang_theme', next);
    });
})();

/* =========================================================
   TOAST NOTIFICATIONS
========================================================= */
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
        toast.style.animation = 'toastSlideOut .35s ease forwards';
        setTimeout(function() { if(toast.parentElement) toast.remove(); }, 400);
    }, 5000);
}

/* Status Toast on Load */
(function() {
    const params = new URLSearchParams(window.location.search);
    const status = params.get('status');
    const tujuan = params.get('tujuan');
    if (!status) return;

    if (status === 'kirim_sukses') {
        showToast('Barang berhasil dikirim ke ' + (tujuan ? tujuan.replace('inventaris_', '').toUpperCase() : 'tujuan') + '.', 'success');
    } else if (status === 'restore_sukses') {
        showToast('Barang berhasil di-restore ke inventaris asal.', 'success');
    } else if (status === 'updated') {
        showToast('Status barang berhasil diperbarui.', 'info');
    } else if (status === 'deleted') {
        showToast('Data gudang berhasil dihapus.', 'error');
    }

    if (window.history.replaceState) {
        const cleanUrl = window.location.pathname + '?unit=' + (params.get('unit') || 'kartika2');
        window.history.replaceState({}, document.title, cleanUrl);
    }
})();

/* =========================================================
   DRAG & DROP COLUMN REORDER
========================================================= */
(function() {
    const table = document.getElementById('gudangTable');
    if (!table) return;
    const headerRow = document.getElementById('tableHeaderRow');
    const headers = Array.from(headerRow.querySelectorAll('th'));
    const tbody = table.querySelector('tbody');
    let dragSrcIndex = null;
    const STORAGE_KEY = 'gudang_col_order_' + (new URLSearchParams(window.location.search).get('unit') || 'default');

    function getOrder() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) return JSON.parse(saved);
        return headers.map(function(h, i) { return h.getAttribute('data-col') || i; });
    }
    function setOrder(order) { localStorage.setItem(STORAGE_KEY, JSON.stringify(order)); }

    function reorderColumns(order) {
        const headerCells = Array.from(headerRow.children);
        order.forEach(function(key) {
            const cell = headerCells.find(function(c) { return c.getAttribute('data-col') === key; });
            if (cell) headerRow.appendChild(cell);
        });
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(function(row) {
            const cells = Array.from(row.children);
            order.forEach(function(key) {
                const cell = cells.find(function(c) { return c.getAttribute('data-col') === key; });
                if (cell) row.appendChild(cell);
            });
        });
    }

    const savedOrder = getOrder();
    reorderColumns(savedOrder);

    headers.forEach(function(th, index) {
        th.addEventListener('dragstart', function(e) {
            dragSrcIndex = index;
            th.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', index);
        });
        th.addEventListener('dragend', function() {
            th.classList.remove('dragging');
            headers.forEach(function(h) { h.classList.remove('drag-over'); });
        });
        th.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            th.classList.add('drag-over');
        });
        th.addEventListener('dragleave', function() { th.classList.remove('drag-over'); });
        th.addEventListener('drop', function(e) {
            e.preventDefault();
            th.classList.remove('drag-over');
            const dropIndex = Array.from(headerRow.children).indexOf(th);
            if (dragSrcIndex === null || dragSrcIndex === dropIndex) return;
            const currentOrder = Array.from(headerRow.children).map(function(c) { return c.getAttribute('data-col'); });
            const moved = currentOrder.splice(dragSrcIndex, 1)[0];
            currentOrder.splice(dropIndex, 0, moved);
            reorderColumns(currentOrder);
            setOrder(currentOrder);
            headers.length = 0;
            Array.from(headerRow.querySelectorAll('th')).forEach(function(h) { headers.push(h); });
            dragSrcIndex = null;
        });
    });
})();

/* =========================================================
   MODALS
========================================================= */
function openModal(id) {
    const modal = document.getElementById(id);
    modal.style.display = 'flex';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('.modal-content');
    content.style.animation = 'modalOut .25s ease forwards';
    setTimeout(function() {
        modal.style.display = 'none';
        modal.classList.remove('active');
        content.style.animation = '';
        document.body.style.overflow = '';
    }, 250);
}

window.addEventListener('click', function(event) {
    document.querySelectorAll('.modal').forEach(function(modal) {
        if (event.target === modal) closeModal(modal.id);
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(function(modal) {
            if (modal.style.display === 'flex') closeModal(modal.id);
        });
    }
});

/* =========================================================
   KIRIM MODAL
========================================================= */
/* =========================================================
   CASCADING DROPDOWNS FOR KIRIM MODAL
========================================================= */
var userDataMap = {};

function loadGedung(tujuan) {
    var gedungSel = document.getElementById('kirim_gedung');
    var lantaiSel = document.getElementById('kirim_lantai');
    var userSel   = document.getElementById('kirim_user');
    gedungSel.innerHTML = '<option value="">Memuat gedung...</option>';
    gedungSel.disabled = true;
    lantaiSel.innerHTML = '<option value="">-- Pilih Gedung Dulu --</option>';
    lantaiSel.disabled = true;
    userSel.innerHTML   = '<option value="">-- Pilih Lantai Dulu --</option>';
    userSel.disabled = true;
    document.getElementById('kirim_ruangan').value = '';
    document.getElementById('kirim_nama_pc').value = '';

    fetch('gudang.php?unit=<?= esc($unit) ?>&ajax=gedung&tujuan=' + encodeURIComponent(tujuan))
        .then(function(r){ return r.json(); })
        .then(function(data){
            gedungSel.innerHTML = '<option value="">-- Pilih Gedung --</option>';
            data.forEach(function(g){
                var opt = document.createElement('option');
                opt.value = g; opt.textContent = g;
                gedungSel.appendChild(opt);
            });
            gedungSel.disabled = false;
        })
        .catch(function(){
            gedungSel.innerHTML = '<option value="">Gagal memuat gedung</option>';
        });
}

function loadLantai(tujuan, gedung) {
    var lantaiSel = document.getElementById('kirim_lantai');
    var userSel   = document.getElementById('kirim_user');
    lantaiSel.innerHTML = '<option value="">Memuat lantai...</option>';
    lantaiSel.disabled = true;
    userSel.innerHTML   = '<option value="">-- Pilih Lantai Dulu --</option>';
    userSel.disabled = true;
    document.getElementById('kirim_ruangan').value = '';
    document.getElementById('kirim_nama_pc').value = '';

    fetch('gudang.php?unit=<?= esc($unit) ?>&ajax=lantai&tujuan=' + encodeURIComponent(tujuan) + '&gedung=' + encodeURIComponent(gedung))
        .then(function(r){ return r.json(); })
        .then(function(data){
            lantaiSel.innerHTML = '<option value="">-- Pilih Lantai --</option>';
            data.forEach(function(l){
                var opt = document.createElement('option');
                opt.value = l; opt.textContent = l;
                lantaiSel.appendChild(opt);
            });
            lantaiSel.disabled = false;
        })
        .catch(function(){
            lantaiSel.innerHTML = '<option value="">Gagal memuat lantai</option>';
        });
}

function loadUsers(tujuan, gedung, lantai) {
    var userSel = document.getElementById('kirim_user');
    userSel.innerHTML = '<option value="">Memuat user...</option>';
    userSel.disabled = true;
    document.getElementById('kirim_ruangan').value = '';
    document.getElementById('kirim_nama_pc').value = '';

    fetch('gudang.php?unit=<?= esc($unit) ?>&ajax=users&tujuan=' + encodeURIComponent(tujuan) + '&gedung=' + encodeURIComponent(gedung) + '&lantai=' + encodeURIComponent(lantai))
        .then(function(r){ return r.json(); })
        .then(function(data){
            userDataMap = {};
            userSel.innerHTML = '<option value="">-- Pilih User --</option>';
            data.forEach(function(u){
                var opt = document.createElement('option');
                opt.value = u.nama_user; opt.textContent = u.nama_user;
                userSel.appendChild(opt);
                userDataMap[u.nama_user] = { ruangan: u.ruangan || '', nama_pc: u.nama_pc || '' };
            });
            userSel.disabled = false;
        })
        .catch(function(){
            userSel.innerHTML = '<option value="">Gagal memuat user</option>';
        });
}

/* Bind cascading events once DOM is ready */
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('input[name="tujuan_kirim"]').forEach(function(el){
        el.addEventListener('change', function(){ if(this.checked) loadGedung(this.value); });
    });
    document.getElementById('kirim_gedung').addEventListener('change', function(){
        var tujuan = document.querySelector('input[name="tujuan_kirim"]:checked');
        if(tujuan && this.value) loadLantai(tujuan.value, this.value);
    });
    document.getElementById('kirim_lantai').addEventListener('change', function(){
        var tujuan = document.querySelector('input[name="tujuan_kirim"]:checked');
        var gedung = document.getElementById('kirim_gedung').value;
        if(tujuan && gedung && this.value) loadUsers(tujuan.value, gedung, this.value);
    });
    document.getElementById('kirim_user').addEventListener('change', function(){
        var info = userDataMap[this.value];
        if(info){
            document.getElementById('kirim_ruangan').value = info.ruangan;
            document.getElementById('kirim_nama_pc').value = info.nama_pc;
        } else {
            document.getElementById('kirim_ruangan').value = '';
            document.getElementById('kirim_nama_pc').value = '';
        }
    });
});


function openKirimModal(row) {
    document.getElementById('kirim_id').value = row.id || '';
    document.querySelectorAll('#modalKirim input[type="radio"]').forEach(function(el) {
        el.checked = false;
        el.disabled = false;
    });

    // Reset cascading fields
    var gedungSel = document.getElementById('kirim_gedung');
    var lantaiSel = document.getElementById('kirim_lantai');
    var userSel   = document.getElementById('kirim_user');
    if(gedungSel){ gedungSel.innerHTML = '<option value="">-- Pilih Tujuan Dulu --</option>'; gedungSel.disabled = true; }
    if(lantaiSel){ lantaiSel.innerHTML = '<option value="">-- Pilih Gedung Dulu --</option>'; lantaiSel.disabled = true; }
    if(userSel){   userSel.innerHTML   = '<option value="">-- Pilih Lantai Dulu --</option>';   userSel.disabled = true; }
    document.getElementById('kirim_ruangan').value = '';
    document.getElementById('kirim_nama_pc').value = '';

    // Semua pilihan kiriman selalu aktif, tidak terikat data asal gudang
    document.getElementById('kirim_pc').disabled = false;
    document.getElementById('kirim_printer1').disabled = false;
    document.getElementById('kirim_printer2').disabled = false;
    document.getElementById('kirim_semua').disabled = false;

    var html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">';
    html += '<div><b><i class="fas fa-fingerprint" style="color:var(--accent-blue)"></i> ID:</b> ' + escapeHtml(row.id || '') + '</div>';
    html += '<div><b><i class="fas fa-barcode" style="color:var(--accent-cyan)"></i> ID Asal:</b> ' + escapeHtml(row.id_asal || '') + '</div>';
    html += '<div><b><i class="fas fa-desktop" style="color:var(--accent-blue)"></i> PC:</b> ' + escapeHtml(row.nama_pc || '-') + '</div>';
    html += '<div><b><i class="fas fa-print" style="color:var(--accent-amber)"></i> Printer 1:</b> ' + escapeHtml(row.printer_1 || '-') + '</div>';
    html += '<div><b><i class="fas fa-print" style="color:var(--accent-amber)"></i> Printer 2:</b> ' + escapeHtml(row.printer_2 || '-') + '</div>';
    html += '<div><b><i class="fas fa-user" style="color:var(--accent-green)"></i> User:</b> ' + escapeHtml(row.nama_user || '-') + '</div>';
    html += '</div>';
    document.getElementById('previewKirim').innerHTML = html;

    openModal('modalKirim');
}

function validateKirim() {
    var tipe = document.querySelector('input[name="tipe_kirim"]:checked');
    var tujuan = document.querySelector('input[name="tujuan_kirim"]:checked');
    var gedung = document.getElementById('kirim_gedung').value;
    var lantai = document.getElementById('kirim_lantai').value;
    var user   = document.getElementById('kirim_user').value;
    var ruangan = document.getElementById('kirim_ruangan').value;
    if (!tipe) { showToast('Silakan pilih barang yang akan dikirim.', 'error'); return false; }
    if (!tujuan) { showToast('Silakan pilih tujuan pengiriman.', 'error'); return false; }
    if (!gedung) { showToast('Silakan pilih gedung tujuan.', 'error'); return false; }
    if (!lantai) { showToast('Silakan pilih lantai tujuan.', 'error'); return false; }
    if (!user)   { showToast('Silakan pilih user tujuan.', 'error'); return false; }
    if (!ruangan){ showToast('Ruangan tidak tersedia untuk user ini.', 'error'); return false; }
    var tujuanText = tujuan.nextElementSibling.innerText.trim();
    var tipeText = tipe.nextElementSibling.innerText.trim();
    return confirm('Kirim ' + tipeText + ' ke ' + tujuanText + ' (Gedung: ' + gedung + ', Lantai: ' + lantai + ', User: ' + user + ')?');
}


/* =========================================================
   STATUS MODAL
========================================================= */
function openStatusModal(id, status) {
    document.getElementById('status_id').value = id;
    document.getElementById('status_value').value = status;
    openModal('modalStatus');
}

/* =========================================================
   DETAIL MODAL
========================================================= */
function showDetail(row) {
    var fields = [
        ['ID', row.id, 'fa-fingerprint', 'var(--accent-blue)'],
        ['ID Asal', row.id_asal, 'fa-barcode', 'var(--accent-cyan)'],
        ['Jenis Barang', row.jenis_barang, 'fa-tag', 'var(--accent-purple)'],
        ['Nama PC', row.nama_pc, 'fa-desktop', 'var(--accent-blue)'],
        ['Nama User', row.nama_user, 'fa-user', 'var(--accent-green)'],
        ['Manufactur', row.manufactur, 'fa-industry', 'var(--text-muted)'],
        ['Processor', row.processor_type, 'fa-microchip', 'var(--text-muted)'],
        ['Windows', row.windows_version, 'fa-windows', 'var(--text-muted)'],
        ['RAM', row.ram_size, 'fa-memory', 'var(--text-muted)'],
        ['Harddisk', row.ram_hardisk, 'fa-hard-drive', 'var(--text-muted)'],
        ['Monitor', row.monitor_model, 'fa-display', 'var(--text-muted)'],
        ['Ukuran Monitor', row.monitor_size, 'fa-ruler', 'var(--text-muted)'],
        ['VGA Monitor', row.monitor_vga, 'fa-plug', 'var(--text-muted)'],
        ['Printer 1', row.printer_1, 'fa-print', 'var(--accent-amber)'],
        ['Printer 2', row.printer_2, 'fa-print', 'var(--accent-amber)'],
        ['Gedung', row.gedung, 'fa-building', 'var(--accent-blue)'],
        ['Lantai', row.lantai, 'fa-layer-group', 'var(--accent-cyan)'],
        ['Ruangan', row.ruangan, 'fa-door-open', 'var(--accent-purple)'],
        ['Tahun Pengadaan', row.tahun_pengadaan, 'fa-calendar', 'var(--text-muted)'],
        ['Status Gudang', row.status_gudang, 'fa-tag', 'var(--accent-green)'],
        ['Alasan Ganti', row.alasan_ganti, 'fa-circle-question', 'var(--text-muted)']
    ];
    var html = '';
    fields.forEach(function(item) {
        var val = item[1] || '-';
        var iconColor = item[3] || 'var(--text-muted)';
        html += '<div class="detail-item">';
        html += '<div class="detail-label"><i class="fas ' + item[2] + '" style="color:' + iconColor + ';margin-right:6px;"></i>' + escapeHtml(item[0]) + '</div>';
        html += '<div class="detail-value">' + escapeHtml(val) + '</div>';
        html += '</div>';
    });
    document.getElementById('detailContent').innerHTML = html;
    openModal('modalDetail');
}

function escapeHtml(value) {
    var div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
}

/* =========================================================
   RIPPLE EFFECT ON BUTTONS
========================================================= */
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn, .header-btn, .theme-toggle');
    if (!btn) return;
    var ripple = document.createElement('span');
    var rect = btn.getBoundingClientRect();
    var size = Math.max(rect.width, rect.height);
    ripple.style.cssText = 'position:absolute;border-radius:50%;background:rgba(255,255,255,.25);width:' + size + 'px;height:' + size + 'px;left:' + (e.clientX - rect.left - size/2) + 'px;top:' + (e.clientY - rect.top - size/2) + 'px;pointer-events:none;animation:ripple .6s ease-out;';
    btn.style.position = 'relative';
    btn.style.overflow = 'hidden';
    btn.appendChild(ripple);
    setTimeout(function() { ripple.remove(); }, 600);
});
</script>

</body>
</html>