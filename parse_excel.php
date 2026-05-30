<?php
$file = 'materias.xlsx';
if (!file_exists($file)) {
    die("File not found");
}

$zip = new ZipArchive();
if ($zip->open($file) === TRUE) {
    // Read shared strings
    $sharedStrings = [];
    $ssData = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssData) {
        $xml = simplexml_load_string($ssData);
        foreach ($xml->si as $val) {
            if (isset($val->t)) {
                $sharedStrings[] = (string)$val->t;
            } elseif (isset($val->r)) {
                $text = '';
                foreach ($val->r as $r) {
                    $text .= (string)$r->t;
                }
                $sharedStrings[] = $text;
            }
        }
    }

    // Read sheet1
    $sheetData = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetData) {
        $xml = simplexml_load_string($sheetData);
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $v = (string)$cell->v;
                if (isset($cell['t']) && $cell['t'] == 's') {
                    $rowData[] = $sharedStrings[(int)$v] ?? '';
                } else {
                    $rowData[] = $v;
                }
            }
            $rows[] = $rowData;
        }
        
        echo "Sheet 1 Rows:\n";
        foreach ($rows as $r) {
            echo implode(" | ", $r) . "\n";
        }
    } else {
        echo "Sheet1 not found\n";
    }
    $zip->close();
} else {
    echo "Failed to open zip";
}
?>
