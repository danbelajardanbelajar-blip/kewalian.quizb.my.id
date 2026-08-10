<?php
$csv = <<<EOF
ABELIA ZAHWA NATASYAFIRAH MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085655942396
AHMAD FADHIL ELFAJRI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085607122165
AHMAD HASAN ARIFIN MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085817417107
AHMAD HUSAIN ARIFIN MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,,
AHMAD ULIL ALBAB ARRIDLO MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085731742098
AULIYA VITA RAHMANI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,,
CITRA ATHIROTUL HUBBIYAH MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085608076770
DEVA RAMADANI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,081331558170
DEWI MARTHA TSABITAH MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,082333186816
DWI CAHYANI PUTRI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085852782411
FITRI SALMA QURROTAA'YUN MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,087864519278
GEA REGINA PUTRI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,,
KHARISMA ALIFATUZZAHRAH AL-AZKA MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085706379197
KIRANA ARSELIA MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085746669841
M. ANFAL ALDI FIRMANSYAH MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,081252918610
M. NIDZOM MUHAKKAM MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085234341887
MILHATUR RAHMAH WULANDARI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,0881036404432
MOHAMMAD WILDAN MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085191173082
MUHAMMAD AIDIL FITRO MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085812659591
MUHAMMAD RAIZ RAIHAN MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,081278412095
MUHAMMAD SUAIDY AL ABRAR MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085232002782
MUHAMMAD TAUFIQUR ROHMAN NASRULLAH MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085953776343
NAISYLA AIRIN AZZAHRA MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085604154750
NIKEN AJENG LESTARI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085781232341
NUR FARIHATUL LAILI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085854876587
NUR GHILMAN BAIHAQI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085606392190
NUR MAFAIDHATUN NABILA MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,081230066883
RACHEL IMAMATUL KAMILAH MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,081231369410
RADIFA RUZANA PUTRI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,082131406712
SU'DAA ARINAL UDLHIYYAH MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,081375198013
SURYA PANCA OCTAVIAN PUTRA MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,081386465361
WAHIDA RASYADA ARINI MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,,
ZIDATUN NAYLA IZZA BILLAH MAK PPDB2026,,,,,,,,,,,,,,,,Imported on 20/07 ::: * myContacts,Mobile,085745331919
EOF;

$lines = explode("\n", trim($csv));
$phones = [];
foreach ($lines as $line) {
    if (empty(trim($line))) continue;
    $parts = explode(',', $line);
    $name = str_replace(" MAK PPDB2026", "", $parts[0]);
    $phone = end($parts);
    if (preg_match('/^08\d+$/', trim($phone))) {
        $phone = '62' . substr(trim($phone), 1);
        $phones[$name] = $phone;
    }
}

$jsonFile = 'D:/all_project/kewalian/data.json';
$data = json_decode(file_get_contents($jsonFile), true);

foreach ($data['data_siswa'] as &$siswa) {
    $nama = $siswa['nama'];
    if (isset($phones[$nama])) {
        $siswa['no_hp'] = $phones[$nama];
    } else {
        $siswa['no_hp'] = '';
    }
}

file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Done updating data.json";
