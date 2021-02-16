<?php defined('BASEPATH') OR exit('No direct script access allowed');
require('fpdf/fpdf.php');
require('fpdf/invClassExtend.php');

class Pl_final_pdf extends CI_Controller{

  var $fontSize = 10;
    var $fontFam = 'Arial';
    var $yearId = 0;
    var $yearCode="";
    var $paperWSize = 297;
    var $paperHSize = 210;
    var $height = 5;
    var $currX;
    var $currY;
    var $widths;
    var $aligns;

    function __construct() {
        parent::__construct();
        //$this->formCetak();
        $pdf = new FPDF();
        $this->startY = $pdf->GetY();
        $this->startX = $this->paperWSize-42;
        $this->lengthCell = $this->startX+20;
    }

    function newLine($pdf){
        $pdf->Cell($this->lengthCell, $this->height, "", "", 0, 'L');
        $pdf->Ln();
    }


  function pageCetak() {
    $pbatchcontrolid_pk = getVarClean('pbatchcontrolid_pk','int',0);
    $periodid_fk = getVarClean('periodid_fk','str','');
    $periodcode = getVarClean('periodcode','str','');


    $sql = "SELECT s01 plitemname,
                    n01 domtrafficamt,
                    n02 domnetamt,
                    n03 intltrafficamt,
                    n04 intlnetamt,
                    n05 intladjamt,
                    n06 toweramt,
                    n07 infraamt,
                    n08 totalamt
            FROM table (f_ShowFinalPL(".$pbatchcontrolid_pk.", ''))";

    $data = array();
    $output = $this->db->query($sql);
    $data = $output->result_array();


    $pdf = new FPDF();


    
    $pdf->AliasNbPages();
    $pdf->AddPage("L", "A4");
    $pdf->SetFont('Arial', '', 16);

    $pdf->Cell($this->lengthCell, $this->height, "P&L by Business Line (After Elimination)", "", 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(8);
    $pdf->Cell($this->lengthCell, $this->height, $periodcode."", "", 0, 'L');
    $pdf->Ln(8);

    $kolom1 = ($this->lengthCell * 4) / 20;
    $kolom2 = ($this->lengthCell * 2) / 20;
    $kolom3 = ($this->lengthCell * 2) / 20;
    $kolom4 = ($this->lengthCell * 2) / 20;
    $kolom5 = ($this->lengthCell * 2) / 20;
    $kolom6 = ($this->lengthCell * 2) / 20;
    $kolom7 = ($this->lengthCell * 2) / 20;
    $kolom8 = ($this->lengthCell * 2) / 20;
    $kolom9 = ($this->lengthCell * 2) / 20;

    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($kolom1, $this->height, "", "TLR", 0, 'L');
    $pdf->Cell($kolom2+$kolom3+$kolom4+$kolom5, $this->height, "Carrier", "TLR", 0, 'C');
    $pdf->Cell($kolom6, $this->height, "", "TLR", 0, 'C');
    $pdf->Cell($kolom7, $this->height, "", "TLR", 0, 'C');
    $pdf->Cell($kolom8, $this->height, "", "TLR", 0, 'C');
    $pdf->Cell($kolom9, $this->height, "", "TLR", 0, 'C');
    $pdf->Ln();

    $pdf->Cell($kolom1, $this->height, "P&L Line Item", "LR", 0, 'L');
    $pdf->Cell($kolom2, $this->height, "Domestic", "TLR", 0, 'C');
    $pdf->Cell($kolom3, $this->height, "Domestic", "TLR", 0, 'C');
    $pdf->Cell($kolom4, $this->height, "International", "TLR", 0, 'C');
    $pdf->Cell($kolom5, $this->height, "International", "TLR", 0, 'C');
    $pdf->Cell($kolom6, $this->height, "International", "LR", 0, 'C');
    $pdf->Cell($kolom7, $this->height, "Towers", "LR", 0, 'C');
    $pdf->Cell($kolom8, $this->height, "Infrastructure", "LR", 0, 'C');
    $pdf->Cell($kolom9, $this->height, "Simple Total", "LR", 0, 'C');
    $pdf->Ln();

    $pdf->Cell($kolom1, $this->height, "", "BLR", 0, 'L');
    $pdf->Cell($kolom2, $this->height, "Traffic", "BLR", 0, 'C');
    $pdf->Cell($kolom3, $this->height, "Network", "BLR", 0, 'C');
    $pdf->Cell($kolom4, $this->height, "Traffic", "BLR", 0, 'C');
    $pdf->Cell($kolom5, $this->height, "Network", "BLR", 0, 'C');
    $pdf->Cell($kolom6, $this->height, "Adjacent", "BLR", 0, 'C');
    $pdf->Cell($kolom7, $this->height, "", "BLR", 0, 'C');
    $pdf->Cell($kolom8, $this->height, "", "BLR", 0, 'C');
    $pdf->Cell($kolom9, $this->height, "", "BLR", 0, 'C');
    $pdf->Ln();

    // $pdf->Cell($kolom1, $this->height, "1", "BLR", 0, 'L');
    // $pdf->Cell($kolom2, $this->height, "2", "BLR", 0, 'C');
    // $pdf->Cell($kolom3, $this->height, "3", "BLR", 0, 'C');
    // $pdf->Cell($kolom4, $this->height, "4", "BLR", 0, 'C');
    // $pdf->Cell($kolom5, $this->height, "5", "BLR", 0, 'C');
    // $pdf->Cell($kolom6, $this->height, "6", "BLR", 0, 'C');
    // $pdf->Cell($kolom7, $this->height, "7", "BLR", 0, 'C');
    // $pdf->Cell($kolom8, $this->height, "8", "BLR", 0, 'C');
    // $pdf->Cell($kolom9, $this->height, "9", "BLR", 0, 'C');
    // $pdf->Ln();

    $pdf->SetWidths(array($kolom1, $kolom2, $kolom3, $kolom4, $kolom5, $kolom6, $kolom7, $kolom8, $kolom9));
    $pdf->SetAligns(array("L", "R", "R", "R", "R", "R", "R", "R", "R"));

    foreach($data as $item) {

        // $pdf->RowMultiBorderWithHeight(array($item['plitemname'],
        //                                      number_format($item['domtrafficamt'],2,",","."),
        //                                      "1",
        //                                      "1",
        //                                      "1",
        //                                     "1",
        //                                      "1",
        //                                      "1",
        //                                      "1"),
        //                 array('TBLR',
        //                       'TBLR',
        //                       'TBLR',
        //                       'TBLR',
        //                       'TBLR',
        //                       'TBLR',
        //                       'TBLR',
        //                       'TBLR',
        //                       'TBLR',
        //                       )
        //                     ,$this->height);

        $pdf->RowMultiBorderWithHeight(array($item['plitemname'],
                                             number_format($item['domtrafficamt'],2,",","."),
                                             number_format($item['domnetamt'],2,",","."),
                                             number_format($item['intltrafficamt'],2,",","."),
                                             number_format($item['intlnetamt'],2,",","."),
                                             number_format($item['intladjamt'],2,",","."),
                                             number_format($item['toweramt'],2,",","."),
                                             number_format($item['infraamt'],2,",","."),
                                             number_format($item['totalamt'],2,",",".")),
                        array('TBLR',
                              'TBLR',
                              'TBLR',
                              'TBLR',
                              'TBLR',
                              'TBLR',
                              'TBLR',
                              'TBLR',
                              'TBLR',
                              )
                            ,$this->height);
        // $pdf->Ln();
   }

    $pdf->Output("","I");

  }

}



