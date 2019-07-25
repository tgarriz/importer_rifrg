<?php
include 'config.php';

if(isset($_POST["Import"])){
	$con = getdb();
    $result = pg_query($con,'truncate rif01');
	if(!isset($result)) {
		echo 'Error truncando table';
	}
	$filename=$_FILES["file"]["tmp_name"];
    revisacsv($filename);	
     if($_FILES["file"]["size"] > 0) {
			$file = fopen($filename, "r");
			$reg = 0;
            $sql = "INSERT into rif01 (id,registro,tipo_doc,nro_doc,item,pos_aranc,u_medida,aduana_reg,aduana_sal,fecha_cum,cant,fob_dolar,tipo_cambio)
					values ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13)";
			$result = pg_prepare($con,"my_sql",$sql);
			while (($getData = fgetcsv($file, 10000, ";")) !== FALSE){
               //$sql = "INSERT into asiento01 (id,nro_asiento,fecha,concepto,tipo,cod_cta,leyenda,debe,haber) 
		  //values ('".++$reg."','".$getData[0]."','".$getData[1]."','".$getData[2]."','".$getData[3]."','".$getData[4]."','".$getData[5]."','".$getData[6]."','".$getData[7]."')";
              $result = pg_execute($con, "my_sql",array(++$reg,$getData[0],$getData[1],$getData[2],$getData[3],$getData[4],preg_replace('/[^A-Za-z0-9\-]/', '',$getData[5]),$getData[6],$getData[7],$getData[8],$getData[9],$getData[10],$getData[11],$getData[12],$getData[12]));
          	  if(!isset($result)) {
            	echo "<script type=\"text/javascript\">
              	alert(\"CSV Invalido.\");
	            window.location = \"index.php\"
      	        </script>";    
			  } else {
      	        echo "<script type=\"text/javascript\">
              	alert(\"CSV Importado.\");
	            window.location = \"index.php\"
      	        </script>";
           	  }
           }
          
	    fclose($file);
	 }
	 pg_close($con);
}

if(isset($_POST["Generar"])){
	vaciar_dir();
	$con = getdb();
	$result = pg_query($con,'select distinct(nro_asiento) from asiento01');
	while($row = pg_fetch_assoc($result)) {
		crea_encabezado($con,$row['nro_asiento']);
		crea_datos($con,$row['nro_asiento']);
	}
	crea_zip();
	enviar();
	pg_close($con);
}

function crea_datos($con){
		$sql = "select '1' ||
					lpad(tipo_doc,2,'0') ||
					lpad(nro_doc,16,'0') ||
					lpad(item,4,'0') ||
					lpad(pos_aranc,12,'0') ||
					lpad(u_medida,2,'0') ||
					creaNumero9e4d(cant) ||
					creaNumero13e2d(fob_dolar) ||
					creaNumero4e6d(tipo_cambio) ||
					lpad(aduana_reg,3,'0') ||
					lpad(aduana_sal,3,'0') ||
					formaFecha(fecha_cum)
				from rif01;";
	$result = pg_query($con,$sql);
	$file = fopen("files/rif_rg.txt","w");
	while($row = pg_fetch_assoc($result)) {
		fputs($file,$row['campo']."\r\n");
	}
	fclose($file);
}

function vaciar_dir(){
	$files = glob('files/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file){
    	if(is_file($file))
	    unlink($file); //elimino el fichero
	}
}

function crea_zip(){
	$zip = new ZipArchive();
	$ret = $zip->open('files/rifs_rg.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
	if ($ret !== TRUE) {
    	printf('Error con el código %d', $ret);
	} else {
    	$zip->addGlob('files/*.{txt}', GLOB_BRACE);
	    $zip->close();
	}
}

function enviar(){
	$zipname = "rifs_rg.zip";
	$zippath = "files/rifs_rg.zip";
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-type: application/octet-stream");
	header('Content-Disposition: attachment; filename="'.$zipname.'"');
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($zippath));
	ob_end_flush();
	@readfile($zippath);
}

function revisacsv($filename){
	$file = fopen($filename, "r") or exit("No subio csv");
	$nro_linea = 1;
	$max = count(file($filename));
	//echo "max es ".$max;
	while(!feof($file) and $nro_linea<$max){
		if(!revisaLinea(fgets($file))){
			echo "Error en CSV, linea ".$nro_linea;
			exit();	
		}
		$nro_linea++;

	}
	fclose($file);
}

function revisaLinea($line){
	return (substr_count($line,';') == 7) ? true : false;
}
?>
