<?php

namespace App\Support;

use ZipArchive;

/**
 * Penulis .docx sederhana untuk membuat satu tabel dari array data.
 * Tidak memerlukan library eksternal; cukup ZipArchive bawaan PHP.
 */
class DocxTable
{
    /**
     * Bangun isi file .docx berisi judul dan satu tabel.
     *
     * Opsi yang didukung:
     * - logo: path absolut ke file gambar (png/jpeg)
     * - brand: teks di samping logo, mis. "MANAJEMEN INVENTARIS"
     * - subtitle: teks kecil di bawah brand
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array{logo?: string|null, brand?: string|null, subtitle?: string|null}  $options
     */
    public static function make(string $title, array $headers, array $rows, bool $landscape = true, array $options = []): string
    {
        $columnCount = max(1, count($headers));

        $pageWidth = $landscape ? 16838 : 11906;
        $pageHeight = $landscape ? 11906 : 16838;
        $margin = 720;
        $contentWidth = $pageWidth - ($margin * 2);
        $colWidth = intdiv($contentWidth, $columnCount);

        $media = [];
        $documentRels = '';
        $brandXml = '';

        // Header branding: logo di kiri, teks di sampingnya.
        $logoPath = $options['logo'] ?? null;
        $brand = $options['brand'] ?? null;
        $imageXml = null;

        if ($logoPath && is_file($logoPath)) {
            [$imageXml, $mediaPart, $relId] = self::logoRun($logoPath);

            if ($imageXml !== null) {
                $media['word/media/logo-sttni.png'] = $mediaPart;
                $documentRels = '<Relationship Id="'.$relId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/logo-sttni.png"/>';
            }
        }

        if ($brand) {
            $brandXml = self::brandTable($brand, $options['subtitle'] ?? null, $imageXml ?? null, $contentWidth);
            $brandXml .= '<w:p><w:pPr><w:pBdr><w:bottom w:val="single" w:sz="12" w:space="4" w:color="1F4E79"/></w:pBdr><w:spacing w:after="160"/></w:pPr></w:p>';
        }

        $body = $brandXml;
        $body .= self::paragraph($title, bold: true, size: 26, align: 'center');
        $body .= self::paragraph('Dicetak: '.now()->translatedFormat('d F Y H:i'), size: 18, align: 'center');

        $body .= '<w:tbl>';
        $body .= '<w:tblPr><w:tblW w:w="'.$contentWidth.'" w:type="dxa"/><w:tblBorders>'
            .'<w:top w:val="single" w:sz="4" w:space="0" w:color="808080"/>'
            .'<w:left w:val="single" w:sz="4" w:space="0" w:color="808080"/>'
            .'<w:bottom w:val="single" w:sz="4" w:space="0" w:color="808080"/>'
            .'<w:right w:val="single" w:sz="4" w:space="0" w:color="808080"/>'
            .'<w:insideH w:val="single" w:sz="4" w:space="0" w:color="808080"/>'
            .'<w:insideV w:val="single" w:sz="4" w:space="0" w:color="808080"/>'
            .'</w:tblBorders></w:tblPr>';

        $body .= '<w:tr><w:trPr><w:tblHeader/></w:trPr>';
        foreach ($headers as $header) {
            $body .= self::cell((string) $header, $colWidth, bold: true, shade: 'D9E1F2');
        }
        $body .= '</w:tr>';

        foreach ($rows as $row) {
            $body .= '<w:tr>';
            foreach (array_values($row) as $value) {
                $body .= self::cell($value === null ? '' : (string) $value, $colWidth);
            }
            $body .= '</w:tr>';
        }

        $body .= '</w:tbl>';

        $body .= '<w:sectPr>'
            .'<w:pgSz w:w="'.$pageWidth.'" w:h="'.$pageHeight.'"'.($landscape ? ' w:orient="landscape"' : '').'/>'
            .'<w:pgMar w:top="'.$margin.'" w:right="'.$margin.'" w:bottom="'.$margin.'" w:left="'.$margin.'" w:header="720" w:footer="720" w:gutter="0"/>'
            .'</w:sectPr>';

        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document '
            .'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
            .'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
            .'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            .'<w:body>'.$body.'</w:body></w:document>';

        $parts = [
            '[Content_Types].xml' => self::contentTypes(),
            '_rels/.rels' => self::rels(),
            'word/document.xml' => $document,
        ];

        if ($documentRels !== '') {
            $parts['word/_rels/document.xml.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .$documentRels.'</Relationships>';
        }

        return self::zip($parts + $media);
    }

    /**
     * Bangun tabel header dua kolom tanpa garis: logo + teks brand.
     *
     * @param  string|array<int, array{text: string, color?: string, bold?: bool, size?: int}>  $brand
     */
    private static function brandTable(string|array $brand, ?string $subtitle, ?string $imageXml, int $contentWidth): string
    {
        $logoWidth = 900;
        $textWidth = $contentWidth - $logoWidth;

        $logoCell = '<w:tc><w:tcPr><w:tcW w:w="'.$logoWidth.'" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr><w:p>'
            .($imageXml ?? '').'</w:p></w:tc>';

        $textRuns = self::brandRuns($brand);

        if ($subtitle) {
            $textRuns .= '</w:p><w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr>'
                .'<w:r><w:rPr><w:color w:val="595959"/><w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr>'
                .'<w:t xml:space="preserve">'.self::escape($subtitle).'</w:t></w:r>';
        }

        $textCell = '<w:tc><w:tcPr><w:tcW w:w="'.$textWidth.'" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>'
            .'<w:p><w:pPr><w:spacing w:before="0" w:after="0"/></w:pPr>'.$textRuns.'</w:p></w:tc>';

        return '<w:tbl><w:tblPr><w:tblW w:w="'.$contentWidth.'" w:type="dxa"/><w:tblBorders>'
            .'<w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/>'
            .'<w:insideH w:val="nil"/><w:insideV w:val="nil"/></w:tblBorders></w:tblPr>'
            .'<w:tr>'.$logoCell.$textCell.'</w:tr></w:tbl>';
    }

    /**
     * Susun run teks brand; setiap segmen bisa diwarnai sendiri.
     *
     * @param  string|array<int, array{text: string, color?: string, bold?: bool, size?: int}>  $brand
     */
    private static function brandRuns(string|array $brand): string
    {
        if (is_string($brand)) {
            $brand = [['text' => $brand]];
        }

        $runs = '';

        foreach ($brand as $segment) {
            $text = (string) ($segment['text'] ?? '');

            if ($text === '') {
                continue;
            }

            $color = (string) ($segment['color'] ?? '1F4E79');
            $bold = (bool) ($segment['bold'] ?? true);
            $size = (int) ($segment['size'] ?? 32);

            $runs .= '<w:r><w:rPr>'
                .($bold ? '<w:b/>' : '')
                .'<w:color w:val="'.$color.'"/>'
                .'<w:sz w:val="'.$size.'"/><w:szCs w:val="'.$size.'"/>'
                .'</w:rPr><w:t xml:space="preserve">'.self::escape($text).'</w:t></w:r>';
        }

        return $runs;
    }

    /**
     * Siapkan run berisi gambar logo (inline) beserta media dan rel id.
     *
     * @return array{0: string|null, 1: string, 2: string}
     */
    private static function logoRun(string $path): array
    {
        $info = @getimagesize($path);

        if ($info === false) {
            return [null, '', ''];
        }

        $width = (int) $info[0];
        $height = (int) $info[1];

        if ($width <= 0 || $height <= 0) {
            return [null, '', ''];
        }

        $targetHeight = 48;
        $targetWidth = (int) round($targetHeight * ($width / $height));

        $emuW = $targetWidth * 9525;
        $emuH = $targetHeight * 9525;

        $relId = 'rIdLogo';

        $run = '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0">'
            .'<wp:extent cx="'.$emuW.'" cy="'.$emuH.'"/>'
            .'<wp:effectExtent l="0" t="0" r="0" b="0"/>'
            .'<wp:docPr id="1" name="Logo STTNI"/>'
            .'<a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            .'<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            .'<pic:nvPicPr><pic:cNvPr id="1" name="Logo STTNI"/><pic:cNvPicPr/></pic:nvPicPr>'
            .'<pic:blipFill><a:blip r:embed="'.$relId.'"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
            .'<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="'.$emuW.'" cy="'.$emuH.'"/></a:xfrm>'
            .'<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
            .'</pic:pic></a:graphicData></a:graphic>'
            .'</wp:inline></w:drawing></w:r>';

        return [$run, (string) file_get_contents($path), $relId];
    }

    private static function paragraph(string $text, bool $bold = false, int $size = 20, string $align = 'left'): string
    {
        $run = '<w:r><w:rPr>'
            .($bold ? '<w:b/>' : '')
            .'<w:sz w:val="'.$size.'"/><w:szCs w:val="'.$size.'"/>'
            .'</w:rPr><w:t xml:space="preserve">'.self::escape($text).'</w:t></w:r>';

        return '<w:p><w:pPr><w:jc w:val="'.$align.'"/></w:pPr>'.$run.'</w:p>';
    }

    private static function cell(string $text, int $width, bool $bold = false, ?string $shade = null): string
    {
        $text = str_replace(["\r\n", "\r", "\n"], ' ', $text);

        $run = '<w:r><w:rPr>'
            .($bold ? '<w:b/>' : '')
            .'<w:sz w:val="18"/><w:szCs w:val="18"/>'
            .'</w:rPr><w:t xml:space="preserve">'.self::escape($text).'</w:t></w:r>';

        return '<w:tc><w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/>'
            .($shade ? '<w:shd w:val="clear" w:color="auto" w:fill="'.$shade.'"/>' : '')
            .'</w:tcPr><w:p>'.$run.'</w:p></w:tc>';
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Default Extension="png" ContentType="image/png"/>'
            .'<Default Extension="jpeg" ContentType="image/jpeg"/>'
            .'<Default Extension="jpg" ContentType="image/jpeg"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'</Types>';
    }

    private static function rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'</Relationships>';
    }

    private static function zip(array $parts): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'docx');

        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($parts as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();

        $binary = (string) file_get_contents($tmp);
        @unlink($tmp);

        return $binary;
    }
}
