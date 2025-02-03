<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class ContractExportService
{
    private Dompdf $dompdf;
    
    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        
        $this->dompdf = new Dompdf($options);
    }
    
    public function exportToPdf(string $html, string $outputPath): bool
    {
        try {
            // Load HTML content
            $this->dompdf->loadHtml($html);
            
            // Set paper size and orientation
            $this->dompdf->setPaper('A4', 'portrait');
            
            // Render PDF
            $this->dompdf->render();
            
            // Save to file
            file_put_contents($outputPath, $this->dompdf->output());
            
            return true;
        } catch (\Exception $e) {
            error_log("Error exporting contract to PDF: " . $e->getMessage());
            return false;
        }
    }
    
    public function getBase64Pdf(string $html): string
    {
        try {
            // Load HTML content
            $this->dompdf->loadHtml($html);
            
            // Set paper size and orientation
            $this->dompdf->setPaper('A4', 'portrait');
            
            // Render PDF
            $this->dompdf->render();
            
            // Get PDF as base64
            return base64_encode($this->dompdf->output());
        } catch (\Exception $e) {
            error_log("Error generating base64 PDF: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function downloadPdf(string $html, string $filename): void
    {
        try {
            // Load HTML content
            $this->dompdf->loadHtml($html);
            
            // Set paper size and orientation
            $this->dompdf->setPaper('A4', 'portrait');
            
            // Render PDF
            $this->dompdf->render();
            
            // Download PDF
            $this->dompdf->stream($filename, [
                'Attachment' => true
            ]);
        } catch (\Exception $e) {
            error_log("Error downloading PDF: " . $e->getMessage());
            throw $e;
        }
    }
} 