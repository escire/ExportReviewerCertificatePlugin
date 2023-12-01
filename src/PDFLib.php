<?php

/**
 * @file plugins/generic/exportReviewerCertificate/src/PDFLib.php
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PDFLib
 * @brief File implemeting facade pdf generation file.
 * 
 * @author epsomsegura
 * @email segurajaramilloepsom@gmail.com
 * @github https://github.com/epsomsegura
 */

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @class PDFLib
 * @brief Class implemeting facade pdf generation file.
 */
class PDFLib
{
    public $pdf;
    public $html;
    public $keywords;

    /**
     * Constructor
     * @param $keywords array All needed keywords and content for design and export a certificate
     */
    public function __construct($keywords)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', TRUE);
        $this->keywords = $keywords;
        $this->createPDFHandler();
        $this->pdf = new Dompdf($options);
        $this->pdf->setPaper('A4', 'portrait');
    }

    /**
     * Set HTML String
     */
    public function setHtmlString(): self
    {
        $this->pdf->loadHtml($this->html);
        return $this;
    }

    /**
     * Stream pdf file
     */
    public function stream(): self
    {
        $this->setHtmlString();
        $this->pdf->render();
        $this->pdf->stream($this->keywords['reviewer_fullname'].'-certificate.pdf', array('Attachment' => 0));
        return $this;
    }

    /**
     * Create PDF Handler
     */
    private function createPDFHandler(): self
    {
        $pdfHtml = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>'.$this->keywords['reviewer_fullname'].' certificate</title>
            <style>
                /* General */
                @page {
                    margin: 2cm 2.5cm !important; position: relative;
                }
                div.background-watermark--certificate {
                    position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 0; max-width: 16cm; width: 16cm; min-width: 16cm;
                }
                div.background-watermark--certificate img {
                    width: 100%; opacity: 0.2;
                }
                /* Header */
                div.header--container {
                    width: 100%; text-align: center; display: flex; margin: -1cm auto; padding-bottom: 50px; z-index: 2;
                }
                div.header--container img {
                    margin: auto; max-width: 100%; width: 100%; min-width: 100%; opacity: 0.5;
                }
                div.certificate--content {
                    z-index: 2;
                }
                div.certificate--content p {
                    font-family: \'Times New Roman\', Times, serif; font-style: 12px; font-style: italic; text-align: justify;
                }
                div.certificate--content p:first-of-type {
                    font-size: 20px !important; font-style: normal !important; text-align: center !important; font-weight: lighter !important;
                }
                div.sender--container {
                    font-family: \'Times New Roman\', Times, serif; font-size: 16px; text-align: center; line-height: 1.2rem; margin-bottom: 2rem;
                }
                div.sender--container div {
                    position: relative; height: 3cm; max-height: 3cm; width: 8cm; max-width: 8cm; margin: auto;
                }
                div.sender--container div img {
                    position: relative; height: 100%; max-height: 100%; opacity: 0.9;
                }
                div.sender--container span {
                    display: flex; flex-direction: row;
                }
                div.sender--container span:nth-of-type(1) {
                    text-transform: uppercase;
                }
            </style>
        </head>
        <body>';
        if (array_key_exists('certificate_watermark', $this->keywords) && isset($this->keywords['certificate_watermark'])) {
            $pdfHtml .=  '<div class="background-watermark--certificate"><img src="'.$this->keywords['certificate_watermark'].'" alt="Certificate watermark"></div>';
        }
        if (array_key_exists('certificate_header', $this->keywords) && isset($this->keywords['certificate_header'])) {
            $pdfHtml .=  '<div class="header--container"><img src="'.$this->keywords['certificate_header'].'" alt="Certificate header" /></div>';
        }
        // Content
        $pdfHtml .= '<div class="certificate--content">';
        $pdfHtml .= ($this->keywords['certificate_greeting'] ?? '-').'<br>';
        $pdfHtml .= ($this->keywords['certificate_content'] ?? '-').'<br>';
        $pdfHtml .= ($this->keywords['institution_description'] ?? '-').'<br>';
        $pdfHtml .= ($this->keywords['certificate_date'] ?? '-').'<br>';
        $pdfHtml .= ($this->keywords['certificate_goodbye'] ?? '-').'<br>';
        $pdfHtml .= '<div class="sender--container">
        <div><img src="'.$this->keywords['certificate_editor_sign'].'" alt="'.$this->keywords['certificate_editor_name'].' signature" /></div>
        <span>'.$this->keywords['certificate_editor_name'].'</span>';
        if (array_key_exists('certificate_editor_institution', $this->keywords) && isset($this->keywords['certificate_editor_institution'])) {
            $pdfHtml .= '<span>'.$this->keywords['certificate_editor_institution'].'</span>';
        }
        if (array_key_exists('certificate_editor_email', $this->keywords) && isset($this->keywords['certificate_editor_email'])) {
            $pdfHtml .= '<span>Email: <a href="mailto:'.$this->keywords['certificate_editor_email'].'">'.$this->keywords['certificate_editor_email'].'</a><span>';
        }
        $pdfHtml .= '</div>';
        $pdfHtml .= '</div>
        </body>
        </html>';
        
        if (array_key_exists('reviewer_gender', $this->keywords) && isset($this->keywords['reviewer_gender'])) {
            $pdfHtml = str_replace('{{reviewer_gender}}',$this->keywords['reviewer_gender'],$pdfHtml);
        }
        if (array_key_exists('reviewer_title', $this->keywords) && isset($this->keywords['reviewer_title'])) {
            $pdfHtml = str_replace('{{reviewer_title}}',$this->keywords['reviewer_title'],$pdfHtml);
        }
        if (array_key_exists('reviewer_fullname', $this->keywords) && isset($this->keywords['reviewer_fullname'])) {
            $pdfHtml = str_replace('{{reviewer_fullname}}',$this->keywords['reviewer_fullname'],$pdfHtml);
        }
        if (array_key_exists('reviewer_institution', $this->keywords) && isset($this->keywords['reviewer_institution'])) {
            $pdfHtml = str_replace('{{reviewer_institution}}'," (".$this->keywords['reviewer_institution'].")",$pdfHtml);
        }
        else{
            $pdfHtml = str_replace('{{reviewer_institution}}',"",$pdfHtml);
        }
        if (array_key_exists('publication_title', $this->keywords) && isset($this->keywords['publication_title'])) {
            $pdfHtml = str_replace('{{publication_title}}',$this->keywords['publication_title'],$pdfHtml);
        }

        if (array_key_exists('day_number', $this->keywords) && isset($this->keywords['day_number'])) {
            $pdfHtml = str_replace("{{day_number}}",$this->keywords['day_number'],$pdfHtml);
            $pdfHtml = str_replace("{{day_text}}",$this->getDayNumber($this->keywords['day_number']),$pdfHtml);
        }
        if (array_key_exists('month_name', $this->keywords) && isset($this->keywords['month_name'])) {
            $pdfHtml = str_replace("{{month_name}}",strtolower($this->keywords['month_name']),$pdfHtml);
        }
        if (array_key_exists('year_number', $this->keywords) && isset($this->keywords['year_number'])) {
            $pdfHtml = str_replace("{{year_number}}",$this->keywords['year_number'],$pdfHtml);
        }
        
        $this->html = $pdfHtml;
        return $this;
    }

    private function getDayNumber(string $dayNumber) : string
    {
        $key = $dayNumber < 10 ? "0".$dayNumber : $dayNumber;
        $dayTextArray=[
            "01"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text01'),
            "02"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text02'),
            "03"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text03'),
            "04"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text04'),
            "05"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text05'),
            "06"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text06'),
            "07"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text07'),
            "08"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text08'),
            "09"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text09'),
            "10"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text10'),
            "11"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text11'),
            "12"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text12'),
            "13"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text13'),
            "14"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text14'),
            "15"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text15'),
            "16"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text16'),
            "17"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text17'),
            "18"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text18'),
            "19"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text19'),
            "20"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text20'),
            "21"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text21'),
            "22"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text22'),
            "23"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text23'),
            "24"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text24'),
            "25"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text25'),
            "26"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text26'),
            "27"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text27'),
            "28"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text28'),
            "29"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text29'),
            "30"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text30'),
            "31"=>__('plugins.generic.exportReviewerCertificate.pdf.day.text31'),
        ];
        return $dayTextArray[$key];
    }
}
