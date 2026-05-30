<?php
require_once 'config.php';

// Tangkap pesan sukses dari session
$msg = '';
if (isset($_GET['msg'])) {
    $messages = [
        'added'   => ['type' => 'success', 'text' => 'Data mahasiswa berhasil ditambahkan!'],
        'updated' => ['type' => 'success', 'text' => 'Data mahasiswa berhasil diperbarui!'],
        'deleted' => ['type' => 'danger',  'text' => 'Data mahasiswa berhasil dihapus!'],
    ];
    $msg = $messages[$_GET['msg']] ?? '';
}

// Ambil semua data mahasiswa (ORDER BY terbaru)
$sql    = "SELECT * FROM mahasiswa ORDER BY created_at DESC";
$result = $conn->query($sql);

// Cek jika query gagal
if (!$result) {
    die("<p style='color:red'>Query Error: " . $conn->error . "</p>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa | CRUD App</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #0d0f14;
            --surface:  #161920;
            --border:   #252830;
            --accent:   #4fffb0;
            --accent2:  #ff6b6b;
            --text:     #e8eaf0;
            --muted:    #6b7280;
            --radius:   8px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Syne', sans-serif;
            min-height: 100vh;
        }
        /* HEADER */
        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        header h1 span { color: var(--accent); }
        .badge {
            background: var(--accent);
            color: #000;
            font-size: 0.7rem;
            font-weight: 700;
            font-family: 'Space Mono', monospace;
            padding: 3px 8px;
            border-radius: 4px;
            letter-spacing: 1px;
            margin-left: 10px;
        }
        /* MAIN */
        main { max-width: 1100px; margin: 0 auto; padding: 40px 20px; }
        /* ALERT */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            font-size: 0.9rem;
            font-weight: 600;
            border-left: 4px solid;
        }
        .alert-success { background: #0d2e20; border-color: var(--accent); color: var(--accent); }
        .alert-danger  { background: #2e0d0d; border-color: var(--accent2); color: var(--accent2); }
        /* TOOLBAR */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .toolbar h2 { font-size: 1.3rem; font-weight: 800; }
        .toolbar h2 small {
            font-size: 0.75rem;
            color: var(--muted);
            font-family: 'Space Mono', monospace;
            margin-left: 8px;
        }
        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: var(--radius);
            font-family: 'Syne', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-primary  { background: var(--accent); color: #000; }
        .btn-primary:hover  { background: #6fffbf; transform: translateY(-1px); }
        .btn-warning  { background: #ffd700; color: #000; }
        .btn-warning:hover  { background: #ffe040; }
        .btn-danger   { background: var(--accent2); color: #fff; }
        .btn-danger:hover   { background: #ff8585; }
        /* TABLE */
        .table-wrap {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #1e2130; }
        thead th {
            padding: 14px 16px;
            text-align: left;
            font-size: 0.72rem;
            font-family: 'Space Mono', monospace;
            letter-spacing: 1.5px;
            color: var(--muted);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
        }
        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.12s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #1c1f2a; }
        td { padding: 13px 16px; font-size: 0.875rem; vertical-align: middle; }
        .nim-cell {
            font-family: 'Space Mono', monospace;
            font-size: 0.8rem;
            color: var(--accent);
        }
        .jurusan-badge {
            background: var(--border);
            padding: 3px 9px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .angkatan-cell { color: var(--muted); font-family: 'Space Mono', monospace; }
        .actions { display: flex; gap: 8px; }
        /* EMPTY STATE */
        .empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }
        .empty .icon { font-size: 3rem; margin-bottom: 12px; }
        .empty p { font-size: 0.9rem; }
        /* FOOTER */
        footer {
            text-align: center;
            padding: 30px;
            color: var(--muted);
            font-size: 0.75rem;
            font-family: 'Space Mono', monospace;
        }
    </style>
</head>
<body>

<header>
    <h1>📚 Data<span>Mahasiswa</span></h1>
    <div>
        <span class="badge">MySQLi OOP</span>
        <span class="badge">PHP CRUD</span>
    </div>
</header>

<main>
    <!-- ALERT PESAN -->
    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg['type'] ?>">
            <?= $msg['text'] ?>
        </div>
    <?php endif; ?>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <h2>
            Daftar Mahasiswa
            <small><?= $result->num_rows ?> data ditemukan</small>
        </h2>
        <a href="create.php" class="btn btn-primary">+ Tambah Mahasiswa</a>
    </div>

    <!-- TABEL DATA -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>NIM</th>
                    <th>Nama Lengkap</th>
                    <th>Jurusan</th>
                    <th>Angkatan</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="nim-cell"><?= htmlspecialchars($row['nim']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><span class="jurusan-badge"><?= htmlspecialchars($row['jurusan']) ?></span></td>
                        <td class="angkatan-cell"><?= htmlspecialchars($row['angkatan']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <div class="actions">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning" style="font-size:0.8rem; padding:6px 12px">✏️ Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger"
                                   style="font-size:0.8rem; padding:6px 12px"
                                   onclick="return confirm('Yakin ingin menghapus data <?= htmlspecialchars($row['nama']) ?>?')">
                                   🗑️ Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty">
                                <div class="icon">📭</div>
                                <p>Belum ada data mahasiswa. <a href="create.php" style="color:var(--accent)">Tambah sekarang</a></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer>
    CRUD App &mdash; Pemrograman Web II &mdash; MySQLi OOP Style
</footer>

</body>
</html>
<?php $conn->close(); ?>
