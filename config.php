<?php
function getdb(){
	$db=pg_connect('host=192.168.122.30 user=postgres password=postgres dbname=asientos connect_timeout=5') or die('connection failed');
	return $db;
}
function get_all_records(){
        $con = getdb();
        $Sql = "SELECT registro,tipo_doc,nro_doc,item,pos_aranc,u_medida,aduana_reg,aduana_sal,fecha_cum,cant,fob_dolar,tipo_cambio FROM rif01";
        $result = pg_query($con, $Sql);  
        if (pg_num_rows($result) > 0) {
         echo "<div class='table-responsive'><table id='myTable' class='table table-striped table-bordered'>
				 <thead><tr><th>REGISTRO</th>
							<th>TIPO_DOC</th>
                            <th>NRO_DOC</th>
                            <th>ITEM</th>
                            <th>POS_ARANCELARIA</th>
                            <th>UNID_MEDIDA</th>
                            <th>ADUANA_REG</th>
                            <th>ADUANA_SAL</th>
                            <th>FECHA</th>
                            <th>CANT</th>
                            <th>FOB_Us</th>
                            <th>CAMBIO</th>
                          </tr></thead><tbody>";
         while($row = pg_fetch_assoc($result)) {
				 echo "<tr>
						<td>" . $row['id']."</td>
						<td>" . $row['tipo_doc']."</td>
                       <td>" . $row['nro_doc']."</td>
                       <td>" . $row['item']."</td>
                       <td>" . $row['pos_aranc']."</td>
                       <td>" . $row['u_medida']."</td>       
                       <td>" . $row['aduana_reg']."</td>
                       <td>" . $row['aduana_sal']."</td>
                       <td>" . $row['fecha_cum']."</td>       
                       <td>" . $row['cant']."</td>
                       <td>" . $row['fob_dolar']."</td>
                       <td>" . $row['tipo_cambio']."</td></tr>";        
         }
         echo "</tbody></table></div>";
    	} else {
         echo "no hay registros";
    	}
	pg_close($con);
    }
?>
