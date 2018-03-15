<?php

ob_start();
?>

<page backcolor="#FEFEFE" backimg="" backimgx="center" backimgy="bottom" backimgw="90%" backtop="0" backbottom="30mm" style="font-size: 12pt">


<page_footer>
<table cellspacing="0" style="width: 1740px;">
    <tr>
       <td style="width:3%;"> &nbsp;</td>
        <td style="width:12%; text-align: center;">
           <br>
           <br>
           <br>
            <hr>
            Recibi conforme
        </td>
        <td style="width:29%;"> &nbsp;</td>
        
    </tr>    
</table>       
</page_footer>

<table>
    <tr>
    <td  style="width: 820px;">
        
    </td>
    <td>
    </td>
    </tr>
    <tr>
        <td colspan="2">
            <table>
                <tr>
                    <th style="width: 700px;"><h3>COMPROANTE DE RETENCIÓN DEL IMPUESTO SOBRE LA RENTA</h3></th>
                    <td  style="width: 220px;">
                        <table style="border-radius: 5px;border:solid;border-width:1px;padding: 2px;">
                            <tr>
                                
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    

    
</table>
<table style="text-align: center; font-size: 10pt;" >
    <tr>
        <th style="width:1005px;"><h3>IMPUESTO RETENIDO Y ENTERADO</h3></th>
    </tr>
</table>

<div style="text-align: right; font-size:12px">
    
</div>
<div style="text-align: right; font-size:12px">
</div>

</page>
<?php
$html = ob_get_clean();
    if(file_exists('../vendor/html2pdf/html2pdf.class.php')){
        require_once('../vendor/html2pdf/html2pdf.class.php');
    }else{
        die("ERROR AL CARGAR PDF");
    }   
    try{
        $html2pdf = new HTML2PDF('L', 'letter', 'es');
        $html2pdf->pdf->SetTitle('Factura');
        $html2pdf->writeHTML($html);
        $html2pdf->Output('retencion.pdf');
    }catch(HTML2PDF_exception $e) {
            echo $e;
        exit;
    }
?>