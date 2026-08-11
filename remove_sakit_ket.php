<?php
/**
 * Script untuk menghapus kewajiban keterangan dari opsi "Sakit" 
 * pada semua pertanyaan di database.
 */

require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/core/Database.php';

$db = new Database();

// Ambil semua pertanyaan bertipe pilihan_ganda dan ganda_dan_angka
$db->query("SELECT id, opsi, judul, tipe FROM pertanyaan WHERE tipe IN ('pilihan_ganda', 'ganda_dan_angka')");
$pertanyaan_list = $db->resultSet();

$berhasil_update = 0;
$tidak_ada_perubahan = 0;

echo "<h2>Proses Menghilangkan Wajib Ket pada Opsi 'Sakit'...</h2>";

foreach ($pertanyaan_list as $row) {
    $opsi_array = json_decode($row['opsi'], true);
    
    if (is_array($opsi_array)) {
        $diubah = false;
        
        if ($row['tipe'] === 'pilihan_ganda') {
            foreach ($opsi_array as $k => $op) {
                if (isset($op['value']) && strtolower($op['value']) === 'sakit') {
                    if (!empty($op['require_ket'])) {
                        $opsi_array[$k]['require_ket'] = false;
                        $diubah = true;
                    }
                }
            }
        } else if ($row['tipe'] === 'ganda_dan_angka' && isset($opsi_array['pilihan'])) {
            foreach ($opsi_array['pilihan'] as $k => $op) {
                if (isset($op['value']) && strtolower($op['value']) === 'sakit') {
                    if (!empty($op['require_ket'])) {
                        $opsi_array['pilihan'][$k]['require_ket'] = false;
                        $diubah = true;
                    }
                }
            }
        }
        
        if ($diubah) {
            $opsi_json_baru = json_encode($opsi_array);
            $db->query("UPDATE pertanyaan SET opsi = :opsi WHERE id = :id");
            $db->bind(':opsi', $opsi_json_baru);
            $db->bind(':id', $row['id']);
            $db->execute();
            
            echo "✅ 'Wajib Ket' dihilangkan dari opsi Sakit pada: <strong>" . htmlspecialchars($row['judul']) . "</strong> (ID: " . $row['id'] . ")<br>";
            $berhasil_update++;
        } else {
            $tidak_ada_perubahan++;
        }
    }
}

echo "<br><strong>Selesai!</strong><br>";
echo "- Berhasil mengubah opsi Sakit pada: $berhasil_update pertanyaan.<br>";
echo "- Tidak ada perubahan diperlukan pada: $tidak_ada_perubahan pertanyaan.<br>";

?>
