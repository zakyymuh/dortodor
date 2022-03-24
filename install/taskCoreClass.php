<?php
class Core {
	function checkEmpty($data)
	{
	    if(!empty($data['hostname']) && !empty($data['username']) && !empty($data['database']) && !empty($data['url']) && !empty($data['lisensi'])){
	        return true;
	    }else{
	        return false;
	    }
	}

	function show_message($type,$message) {
		return $message;
	}
	
	function getAllData($data) {
		return $data;
	}

	function write_config($data) {

        $template_path 	= 'includes/templatevthree.php';

		$output_path 	= '../lisensi.json';
		$handle = fopen($output_path,'w+');
		@chmod($output_path,0777);
		if(is_writable(dirname($output_path))) {
			if(fwrite($handle,json_encode(["domain"=>$data['url'],"key"=>$data['lisensi']]))) {
				$ls1 = true;
			} else {
				$ls1 = false;
			}
		} else {
			$ls1 = false;
		}

		$output_path 	= '../cdn/lisensi.json';
		$handle = fopen($output_path,'w+');
		@chmod($output_path,0777);
		if(is_writable(dirname($output_path))) {
			if(fwrite($handle,json_encode(["domain"=>$data['url'],"key"=>$data['lisensi']]))) {
				$ls2 = true;
			} else {
				$ls2 = false;
			}
		} else {
			$ls2 = false;
		}

		$output_path 	= '../application/config/database.php';
		$database_file = file_get_contents($template_path);

		$new  = str_replace("%HOSTNAME%",$data['hostname'],$database_file);
		$new  = str_replace("%USERNAME%",$data['username'],$new);
		$new  = str_replace("%PASSWORD%",$data['password'],$new);
		$new  = str_replace("%DATABASE%",$data['database'],$new);

		$handle = fopen($output_path,'w+');
		@chmod($output_path,0777);
		
		if(is_writable(dirname($output_path))) {

			if(fwrite($handle,$new)) {
				$db1 = true;
			} else {
				$db1 = false;
			}
		} else {
			$db1 = false;
		}

		$output_path 	= '../cdn/application/config/database.php';
		$database_file = file_get_contents($template_path);

		$new  = str_replace("%HOSTNAME%",$data['hostname'],$database_file);
		$new  = str_replace("%USERNAME%",$data['username'],$new);
		$new  = str_replace("%PASSWORD%",$data['password'],$new);
		$new  = str_replace("%DATABASE%",$data['database'],$new);

		$handle = fopen($output_path,'w+');
		@chmod($output_path,0777);
		
		if(is_writable(dirname($output_path))) {

			if(fwrite($handle,$new)) {
				$db2 = true;
			} else {
				$db2 = false;
			}
		} else {
			$db2 = false;
		}

		if($db1 AND $db2 AND $ls1 AND $ls2){
			return true;
		}else{
			return false;
		}
	}
	
	function checkFile(){
	    $output_path = '../application/config/database.php';
	    $output_path2 = '../cdn/application/config/database.php';
	    
	    if(file_exists($output_path) AND file_exists($output_path2)) {
           return true;
        } 
        else{
            return false;
        }
	}
}