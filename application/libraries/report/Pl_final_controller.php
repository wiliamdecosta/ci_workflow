<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Json library
* @class Pl_final_controller
* @version 2018-05-25 11:32:24
*/
class Pl_final_controller {

    function readTable() {

        $i_batch_control_id = getVarClean('pbatchcontrolid_pk','int',0);
        $i_search = getVarClean('i_search','str','');

        try {

            $ci = & get_instance();
            $ci->load->model('report/pl_final');
            $table = new Pl_final($i_batch_control_id, $i_search);

            $count = $table->countAll();
            $items = $table->getAll(0, -1);

            if($count < 1) {  echo ''; exit; }

            $output = '';

            foreach($items as $item) {

                $output .= '<tr>';
                    $output .= '<td nowrap>'.$item['plitemname'].'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['domtrafficamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['domnetamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['intltrafficamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['intlnetamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['intladjamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['toweramt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['infraamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['totalamt']).'</td>';
                $output .= '</tr>';


            }

        }catch (Exception $e) {
            $data = array();
            $data['message'] = $e->getMessage();
            return $data;
        }

        echo $output;
        exit;

        

    }

    function download_excel() {

            $i_batch_control_id = getVarClean('pbatchcontrolid_pk','int',0);
            $periodcode = getVarClean('periodcode','str','');
            $periodid_fk = getVarClean('periodid_fk','str','');

            $ci = & get_instance();
            $ci->load->model('report/pl_final');
            $table = new Pl_final($i_batch_control_id, '');

            $count = $table->countAll();
            $items = $table->getAll(0, -1);

            startExcel("final_pl_".$periodid_fk.".xls");

            $output = '';
            $output .= '<div style="font-size: 18px; font-weight : bold;"> P&L by Business Line (After Elimination) </div>';
            $output .= '<div style="font-size: 14px; font-weight : bold;">'.$periodcode.'</div>';

        
            $output .='<table  border="1">';

            $output.='<tr>';
            $output.='  <th rowspan="2" style="vertical-align: middle;">P&L Line Item </th>
                        <th colspan="4" style="text-align: center;">Carrier</th>
                        <th rowspan="2" style="vertical-align: middle;">International Adjacent</th>
                        <th rowspan="2" style="vertical-align: middle;">Towers</th>
                        <th rowspan="2" style="vertical-align: middle;">Infrastructure</th>
                        <th rowspan="2"  style="vertical-align: middle;">Simple Total</th> ';
            $output.='</tr>';

            $output.='<tr>';                         
            $output.='  <th style="text-align: center;">Domestic Traffic</th>
                        <th style="text-align: center;">Domestic Network</th>
                        <th style="text-align: center;">International Traffic</th>
                        <th style="text-align: center;">International Network</th> ';
            $output.='</tr>';

            if($count < 1)  {
                $output .= '</table>';
                echo $output;
                exit;
            }


            foreach($items as $item) {
                 $output .= '<tr>';
                    $output .= '<td nowrap>'.$item['plitemname'].'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['domtrafficamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['domnetamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['intltrafficamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['intlnetamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['intladjamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['toweramt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['infraamt']).'</td>';
                    $output .= '<td nowrap align="right">'.numberFormat($item['totalamt']).'</td>';
                $output .= '</tr>';

            }


            $output .= '</table>';
            echo $output;
            exit;

    }



}

/* End of file Pl_final_controller.php */