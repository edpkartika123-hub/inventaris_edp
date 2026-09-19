<?php
include 'koneksi.php';

if (isset($_POST['submit'])) {
    $gedung = $_POST['gedung'];
    $lantai = $_POST['lantai'];
    $ruangan = $_POST['ruangan'];
    $nama_pc = $_POST['nama_pc'];
    $nama_user = $_POST['nama_user'];
    $manufactur = $_POST['manufactur'];
    $windows_version = $_POST['windows_version'];
    $processor_type = $_POST['processor_type'];
    $ram_size = $_POST['ram_size'];
    $ram_hardisk = $_POST['ram_hardisk'];
    $monitor_model = $_POST['monitor_model'];
    $monitor_vga = $_POST['monitor_vga'];
    $monitor_size = $_POST['monitor_size'];
    $printer_1 = $_POST['printer_1'];
    $printer_2 = $_POST['printer_2'];
    $tahun_pengadaan = $_POST['tahun_pengadaan'];
    $id_personil_edp = $_POST['id_personil_edp'];

    $query = "INSERT INTO inventaris_edp VALUES (
        null, '$gedung', '$lantai', '$ruangan', '$nama_pc', '$nama_user', 
        '$manufactur', '$windows_version', '$processor_type', '$ram_size', 
        '$ram_hardisk', '$monitor_model', '$monitor_vga', '$monitor_size', 
        '$printer_1', '$printer_2', '$tahun_pengadaan', '$id_personil_edp'
    )";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Inventaris EDP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4 mb-5">
        <h2 class="mb-4">Form Tambah Inventaris EDP</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="" method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gedung</label>
                            <input type="text" name="gedung" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Lantai</label>
                            <input type="text" name="lantai" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ruangan</label>
                            <input type="text" name="ruangan" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama PC</label>
                            <input type="text" name="nama_pc" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama User</label>
                            <input type="text" name="nama_user" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Manufactur (Merk)</label>
                            <input type="text" name="manufactur" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Windows Version</label>
                            <input type="text" name="windows_version" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Processor Type</label>
                            <input type="text" name="processor_type" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ram Size</label>
                            <input type="text" name="ram_size" class="form-control" placeholder="Contoh: 8GB" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ram / Hardisk (Penyimpanan)</label>
                            <input type="text" name="ram_hardisk" class="form-control" placeholder="Contoh: 512GB SSD" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Monitor Model</label>
                            <input type="text" name="monitor_model" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Monitor VGA</label>
                            <input type="text" name="monitor_vga" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Monitor Size</label>
                            <input type="text" name="monitor_size" class="form-control" placeholder="Contoh: 24 Inch" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Printer 1 (Opsional)</label>
                            <input type="text" name="printer_1" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Printer 2 (Opsional)</label>
                            <input type="text" name="printer_2" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Pengadaan</label>
                            <input type="number" name="tahun_pengadaan" class="form-control" placeholder="YYYY" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ID Personil EDP</label>
                            <input type="text" name="id_personil_edp" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="submit" class="btn btn-success">Simpan Data</button>
                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>