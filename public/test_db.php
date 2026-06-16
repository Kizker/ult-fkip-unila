<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=ult_fkip', 'root', '');
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "✅ Database berhasil diupdate! Terdapat " . count($tables) . " tabel.<br>";
        echo "Daftar tabel: " . implode(", ", $tables);
    } else {
        echo "❌ Database kosong. Proses import mungkin belum selesai atau gagal.";
    }
} catch (Exception $e) {
    echo "❌ Error koneksi: " . $e->getMessage();
}
