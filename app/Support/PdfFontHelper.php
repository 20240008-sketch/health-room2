<?php

namespace App\Support;

use TCPDF;

class PdfFontHelper
{
    /**
     * Candidate font filenames to try from the public directory.
     */
    private const FONT_CANDIDATES = [
        'seieiIPexMincho.ttf',
        'seieiIPAexMincho.ttf',
    ];

    private static ?string $fontName = null;
    private static ?string $fontPath = null;

    /**
     * Apply the school font to a TCPDF instance.
     *
     * @param TCPDF $pdf
     * @param float $bodySize
     * @param float|null $headerSize
     * @param float|null $footerSize
     * @return string
     */
    public static function applyFont(TCPDF $pdf, float $bodySize = 10, ?float $headerSize = null, ?float $footerSize = null): string
    {
        $fontName = self::registerFont();

        $pdf->SetFont($fontName, '', $bodySize);

        if ($headerSize !== null) {
            $pdf->setHeaderFont([$fontName, '', $headerSize]);
        }

        if ($footerSize !== null) {
            $pdf->setFooterFont([$fontName, '', $footerSize]);
        }

        return $fontName;
    }

    /**
     * Register the custom font with TCPDF if it has not been registered yet.
     */
    public static function registerFont(): string
    {
        if (self::$fontName !== null) {
            return self::$fontName;
        }

        $fontPath = self::resolveFontPath();

        // Register the TrueType font with TCPDF and enable font subsetting (flag 32)
        self::$fontName = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32);

        return self::$fontName;
    }

    /**
     * Provide the absolute path to the custom font file located in the public directory.
     */
    public static function getFontPath(): string
    {
        return self::resolveFontPath();
    }

    private static function resolveFontPath(): string
    {
        if (self::$fontPath !== null) {
            return self::$fontPath;
        }

        foreach (self::FONT_CANDIDATES as $candidate) {
            $path = public_path($candidate);
            if (file_exists($path)) {
                self::$fontPath = $path;
                return self::$fontPath;
            }
        }

        throw new \RuntimeException('Custom PDF font file (seieiIPexMincho.ttf) was not found in the public directory.');
    }
}
