<?php
require_once 'config.php';

// Ambil ID dari URL, validasi integer
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

// AMBIL DATA YANG AKAN DIEDIT
$stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$mhs    = $result->fetch_assoc();
$stmt->close();

// Jika data tidak ditemukan
if (!$mhs) {
    header("Location: index.php");
    exit;
}

$errors = [];

// PROSES UPDATE saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nim      = trim($_POST['nim']      ?? '');
    $nama     = trim($_POST['nama']     ?? '');
    $jurusan  = trim($_POST['jurusan']  ?? '');
    $angkatan = trim($_POST['angkatan'] ?? '');
    $email    = trim($_POST['email']    ?? '');

    // Simpan input ke $mhs agar form tetap terisi
    $mhs = array_merge($mhs, compact('nim', 'nama', 'jurusan', 'angkatan', 'email'));

    // VALIDASI
    if (empty($nim))      $errors['nim']      = 'NIM tidak boleh kosong.';
    if (empty($nama))     $errors['nama']     = 'Nama tidak boleh kosong.';
    if (empty($jurusan))  $errors['jurusan']  = 'Jurusan tidak boleh kosong.';
    if (empty($angkatan)) $errors['angkatan'] = 'Angkatan tidak boleh kosong.';
    if (empty($email))    $errors['email']    = 'Email tidak boleh kosong.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
                          $errors['email']    = 'Format email tidak valid.';

    // Cek NIM duplikat (kecuali milik sendiri)
    if (empty($errors['nim'])) {
        $check = $conn->prepare("SELECT id FROM mahasiswa WHERE nim = ? AND id != ?");
        $check->bind_param("si", $nim, $id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errors['nim'] = 'NIM sudah digunakan mahasiswa lain.';
        }
        $check->close();
    }

    // UPDATE jika tidak ada error
    if (empty($errors)) {
        $stmt = $conn->prepare(
            "UPDATE mahasiswa
             SET nim = ?, nama = ?, jurusan = ?, angkatan = ?, email = ?
             WHERE id = ?"
        );
        $stmt->bind_param("sssisi", $nim, $nama, $jurusan, $angkatan, $email, $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?msg=updated");
            exit;
        } else {
            $errors['db'] = "Gagal memperbarui data: " . $stmt->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa | CRUD App</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:      #0d0f14;
            --surface: #161920;
            --border:  #252830;
            --accent:  #ffd700;
            --accent2: #ff6b6b;
            --text:    #e8eaf0;
            --muted:   #6b7280;
            --radius:  8px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text); font-family: 'Syne', sans-serif; min-height: 100vh; }
        header { background: var(--surface); border-bottom: 1px solid var(--border); padding: 20px 40px; }
        header h1 { font-size: 1.5rem; font-weight: 800; }
        header h1 span { color: #4fffb0; }
        main { max-width: 640px; margin: 0 auto; padding: 40px 20px; }
        .page-title { font-size: 1.6rem; font-weight: 800; margin-bottom: 6px; }
        .page-sub { color: var(--muted); font-size: 0.85rem; margin-bottom: 32px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 32px; }
        .id-badge {
            display: inline-block;
            background: var(--border);
            font-family: 'Space Mono', monospace;
            font-size: 0.75rem;
            color: var(--accent);
            padding: 4px 10px;
            border-radius: 4px;
            margin-bottom: 24px;
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.78rem; font-family: 'Space Mono', monospace; letter-spacing: 1px; color: var(--muted); text-transform: uppercase; margin-bottom: 8px; }
        input, select { width: 100%; background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 11px 14px; color: var(--text); font-family: 'Syne', sans-serif; font-size: 0.9rem; outline: none; transition: border-color 0.15s; }
        input:focus, select:focus { border-color: var(--accent); }
        input.error, select.error { border-color: var(--accent2); }
        .error-msg { color: var(--accent2); font-size: 0.78rem; margin-top: 5px; font-family: 'Space Mono', monospace; }
        .alert-db { background: #2e0d0d; border: 1px solid var(--accent2); color: var(--accent2); padding: 12px 16px; border-radius: var(--radius); font-size: 0.85rem; margin-bottom: 20px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .btn-group { display: flex; gap: 12px; margin-top: 28px; }
        .btn { flex: 1; padding: 12px; border-radius: var(--radius); font-family: 'Syne', sans-serif; font-size: 0.9rem; font-weight: 700; border: none; cursor: pointer; text-decoration: none; text-align: center; transition: all 0.15s; }
        .btn-warning { background: var(--accent); color: #000; }
        .btn-warning:hover { background: #ffe040; }
        .btn-secondary { background: var(--border); color: var(--text); }
        .btn-secondary:hover { background: #2e323d; }
        select option { background: var(--surface); }
    </style>
</head>
<body>
<header>
    <h1>📚 Data<span>Mahasiswa</span></h1>
</header>

<main>
    <div class="page-title">Edit Data Mahasiswa</div>
    <p class="page-sub">Perbarui informasi mahasiswa di bawah ini.</p>

    <div class="card">
        <div class="id-badge">ID: #<?= $id ?> &mdash; NIM: <?= htmlspecialchars($mhs['nim']) ?></div>

        <?php if (isset($errors['db'])): ?>
            <div class="alert-db"><?= $errors['db'] ?></div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?= $id ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="nim">NIM *</label>
                    <input type="text" id="nim" name="nim"
                           value="<?= htmlspecialchars($mhs['nim']) ?>"
                           class="<?= isset($errors['nim']) ? 'error' : '' ?>">
                    <?php if (isset($errors['nim'])): ?>
                        <div class="error-msg"><?= $errors['nim'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="angkatan">Angkatan *</label>
                    <input type="number" id="angkatan" name="angkatan"
                           value="<?= htmlspecialchars($mhs['angkatan']) ?>"
                           class="<?= isset($errors['angkatan']) ? 'error' : '' ?>"
                           min="2000" max="2099">
                    <?php if (isset($errors['angkatan'])): ?>
                        <div class="error-msg"><?= $errors['angkatan'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap *</label>
                <input type="text" id="nama" name="nama"
                       value="<?= htmlspecialchars($mhs['nama']) ?>"
                       class="<?= isset($errors['nama']) ? 'error' : '' ?>">
                <?php if (isset($errors['nama'])): ?>
                    <div class="error-msg"><?= $errors['nama'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="jurusan">Jurusan *</label>
                <select id="jurusan" name="jurusan" class="<?= isset($errors['jurusan']) ? 'error' : '' ?>">
                    <option value="">-- Pilih Jurusan --</option>
                    <?php
                    $jurusanList = ['Teknik Informatika','Sistem Informasi','Teknologi Informasi ','Manajemen ','Akuntansi','Komunikasi'];
                    foreach ($jurusanList as $j):
                        $sel = ($mhs['jurusan'] === $j) ? 'selected' : '';
                    ?>
                        <option value="<?= $j ?>" <?= $sel ?>><?= $j ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['jurusan'])): ?>
                    <div class="error-msg"><?= $errors['jurusan'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($mhs['email']) ?>"
                       class="<?= isset($errors['email']) ? 'error' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-msg"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>

            <div class="btn-group">
                <a href="index.php" class="btn btn-secondary">← Batal</a>
                <button type="submit" class="btn btn-warning">Update Data</button>
            </div>

        </form>
    </div>
</main>
</body>
</html>
<?php $conn->close(); ?>
