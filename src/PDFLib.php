<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFLib{
    public $pdf;
    public $html;
    public $keywords;

    public function __construct($html, $keywords)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', TRUE);
        $this->html = $html;
        $this->keywords = $keywords;
        $this->pdf = new Dompdf($options);
        $this->pdf->setPaper('A4', 'portrait');
        $this->replaceKeywords();
    }

    public function setHtmlString()
    {
        $this->pdf->loadHtml($this->html);
        return $this;
    }

    public function stream()
    {
        $this->setHtmlString();
        $this->pdf->render();
        $this->pdf->stream('certificate.pdf',array('Attachment'=>0));
        return $this;
    }


    public function replaceKeywords(){
        foreach($this->keywords as $key=>$value){
            if(isset($value)){
                $this->html = str_replace('${'.$key.'}',$value,$this->html);
            }
        }
    }
}
