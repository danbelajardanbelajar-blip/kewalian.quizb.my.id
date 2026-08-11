<?php
/**
 * Script untuk menghapus opsi "Telat" dari semua pertanyaan
 * yang ada di database.
 */

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/core/Database.php';

$db = new Database();

// Ambil semua pertanyaan bertipe pilihan_ganda
$db->query("SELECT id, opsi, judul FROM pertanyaan WHERE tipe = 'pilihan_ganda'");
$pertanyaan_list = $db->resultSet();

$berhasil_update = 0;
$tidak_ada_telat = 0;

echo "<h2>Proses Menghapus Opsi 'Telat' dari Database...</h2>";

foreach ($pertanyaan_list as $row) {
    $opsi_array = json_decode($row['opsi'], true);
    
    // Periksa apakah decoding JSON berhasil
    if (is_array($opsi_array)) {
        $opsi_baru = [];
        $ditemukan = false;
        
        foreach ($opsi_array as $op) {
            // Jika valuenya bukan 'telat', masukkan ke array opsi_baru
            if (isset($op['value']) && strtolower($op['value']) !== 'telat') {
                $opsi_baru[] = $op;
            } else {
                $ditemukan = true; // Tandai bahwa opsi telat ada dan dilewati (dihapus)
            }
        }
        
        // Jika ada opsi telat yang dihapus, update ke database
        if ($ditemukan) {
            $opsi_json_baru = json_encode($opsi_baru);
            $db->query("UPDATE pertanyaan SET opsi = :opsi WHERE id = :id");
            $db->bind(':opsi', $opsi_json_baru);
            $db->bind(':id', $row['id']);
            $db->execute();
            
            echo "✅ Dihapus dari pertanyaan: <strong>" . htmlspecialchars($row['judul']) . "</strong> (ID: " . $row['id'] . ")<br>";
            $berhasil_update++;
        } else {
            $tidak_ada_telat++;
        }
    }
}

echo "<br><strong>Selesai!</strong><br>";
echo "- Berhasil menghapus opsi Telat dari: $berhasil_update pertanyaan.<br>";
echo "- Tidak ada opsi Telat pada: $tidak_ada_telat pertanyaan.<br>";

?>
