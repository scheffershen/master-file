<?php

namespace App\Library;

use App\Constants;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class MPdfConverter implements HtmlToPdfConverter
{
    private $cacheDirectory;

    public function __construct(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    public function convertToPdf(string $html)
    {
        $mpdf = new Mpdf(['tempDir' => $this->cacheDirectory]);
        $mpdf->creator = Constants::OWNER;

        if ((int) ini_get('pcre.backtrack_limit') < 1000000) {
            @ini_set('pcre.backtrack_limit', 1000000);
        }

        $parts = explode('<pagebreak>', $html);
        for ($i = 0; $i < count($parts); $i++) {
            $mpdf->WriteHTML($parts[$i]);
            if ($i < count($parts) - 1) {
                $mpdf->WriteHTML('<pagebreak>');
            }
        }

        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
