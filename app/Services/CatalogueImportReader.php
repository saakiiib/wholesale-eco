<?php
namespace App\Services;

use RuntimeException;
use ZipArchive;

/** Small, bounded XLSX/CSV reader; no vendor package required. */
class CatalogueImportReader
{
    public const HEADERS = ['sku','product_name','category_path','variant_label','units_per_pack','packs_per_pallet','allow_pallet_orders','show_pallet_units','purchase_price_gbp','markup_percent','vat_percent','stock_packs','description'];

    public function read(string $path, string $extension): array
    {
        $rows = strtolower($extension) === 'csv' ? $this->csv($path) : $this->xlsx($path);
        if (!$rows) throw new RuntimeException('The file is empty.');
        $headers = array_map(fn($v) => strtolower(trim((string) $v)), array_shift($rows));
        // BOM from Excel UTF-8 CSV
        if (isset($headers[0])) $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        if ($headers !== self::HEADERS) throw new RuntimeException('Incorrect columns or column order. Download and use the supplied template.');
        if (count($rows) > 200) throw new RuntimeException('Maximum 200 products per file. Split larger files into batches.');
        $result = [];
        foreach ($rows as $index => $values) {
            if (!array_filter($values, fn($v) => trim((string) $v) !== '')) continue;
            if (count($values) !== count($headers)) throw new RuntimeException('Row '.($index + 2).' has an incorrect number of columns.');
            $result[] = array_combine($headers, array_map(fn($v)=>trim((string)$v), $values)) + ['line' => $index + 2];
            $result[array_key_last($result)]['line'] = $index + 2;
        }
        if (!$result) throw new RuntimeException('No product rows found.');
        return $result;
    }

    private function csv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (!$handle) throw new RuntimeException('Cannot read the uploaded CSV.');
        try {
            $rows = [];
            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (count($rows) > 201) throw new RuntimeException('Maximum 200 products per file.');
                $rows[] = $row;
            }
            return $rows;
        } finally { fclose($handle); }
    }

    private function xlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class) || !function_exists('simplexml_load_string')) {
            throw new RuntimeException('XLSX requires the PHP zip and SimpleXML extensions. CSV uploads are supported without them.');
        }
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) throw new RuntimeException('Invalid XLSX file.');
        try {
            $xml = $this->entry($zip, 'xl/worksheets/sheet1.xml');
            if ($xml === null) throw new RuntimeException('The workbook must contain products on the first worksheet.');
            $strings = [];
            if (($sharedXml = $this->entry($zip, 'xl/sharedStrings.xml')) !== null) {
                $doc = $this->xml($sharedXml);
                $ns = $doc->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                foreach ($ns->si as $item) {
                    $text = '';
                    foreach ($item->xpath('.//*[local-name()="t"]') ?: [] as $t) $text .= (string)$t;
                    $strings[] = $text;
                }
            }
            $doc = $this->xml($xml);
            $ns = $doc->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $rows = [];
            foreach ($ns->sheetData->row as $row) {
                if (count($rows) > 201) throw new RuntimeException('Maximum 200 products per file.');
                $values = array_fill(0, count(self::HEADERS), '');
                foreach ($row->c as $cell) {
                    $ref = (string)$cell['r'];
                    if (!preg_match('/^([A-Z]+)\d+$/', $ref, $m)) continue;
                    $column = 0;
                    foreach (str_split($m[1]) as $letter) $column = $column * 26 + ord($letter) - 64;
                    $column--;
                    if ($column < 0 || $column >= count(self::HEADERS)) continue;
                    $type = (string)$cell['t'];
                    if ($type === 's') $value = $strings[(int)$cell->v] ?? '';
                    elseif ($type === 'inlineStr') $value = implode('', array_map('strval', $cell->xpath('.//*[local-name()="t"]') ?: []));
                    else $value = (string)$cell->v;
                    $values[$column] = $value;
                }
                $rows[] = $values;
            }
            return $rows;
        } finally { $zip->close(); }
    }

    private function entry(ZipArchive $zip, string $name): ?string
    {
        $index = $zip->locateName($name);
        if ($index === false) return null;
        $stat = $zip->statIndex($index);
        if (($stat['size'] ?? 0) > 4 * 1024 * 1024) throw new RuntimeException('Spreadsheet XML exceeds the safety limit.');
        $contents = $zip->getFromIndex($index);
        if ($contents === false) throw new RuntimeException('Unable to read spreadsheet data.');
        return $contents;
    }

    private function xml(string $xml): \SimpleXMLElement
    {
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET);
        if ($doc === false) throw new RuntimeException('Invalid spreadsheet XML.');
        return $doc;
    }
}
