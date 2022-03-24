<?php
//error_reporting(0);
$db_config_path = '../application/config/database.php';
$db_config_path2 = '../cdn/application/config/database.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST) {
    
	require_once('taskCoreClass.php');
	require_once('includes/databaseLibrary.php');

	$core = new Core();
	$database = new Database();

	if($core->checkEmpty($_POST) == true){
		if($database->create_database($_POST) == false){
			$message = $core->show_message('error',"Gagal mengimpor database, pastikan form host, username, password, database sudah sesuai.");
		}elseif($database->create_tables($_POST) == false){
			$message = $core->show_message('error',"Gagal mengimpor database, pastikan form host, username, password, database sudah sesuai.");
		}elseif($core->checkFile() == false){
			$message = $core->show_message('error',"File application/config/database.php tidak ditemukan");
		}elseif($core->write_config($_POST) == false){
			$message = $core->show_message('error',"Tidak dapat menyimpan konfigurasi file, silahkan hubungi cs jadiorder. Error Code: 777");
		}

        if(!isset($message)){
            $pesan = "
                Selesai melakukan instalasi website ke server, silahkan cek kembali website Anda. Apabila sudah berjalan lancar, silahkan <b>hapus folder install</b> dan <b>tutup halaman instalasi ini</b>.<br/>&nbsp;<br/>
                <a href='javascript:history.back()' class='btn btn-warning btn-sm'>&laquo; Ulangi Instalasi</a>
                &nbsp; | &nbsp;
                <a href='".$_POST["url"]."' target='_blank' class='btn btn-primary btn-sm'>Cek Website &raquo;</a>
            ";
        }
	}else{
		$message = $core->show_message('error','Semua formulir wajib di isi.');
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Web Installer | Jadiorder</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <style rel="stylesheet" type="text/css">
            body{
                font-family: 'Nunito', sans-serif;
                background-color: #f5fafa;
            }
            .logo{
                width: 400px;
                max-width: 80%;
                margin: 30px auto;
            }
            .logo img{ width: 100%; }
            .wrapper{
                background-color: #fff;
                border-radius: 12px;
                border: 1px solid #ccc;
                padding: 30px;
                margin: 10px auto 30px auto;
            }
            .title{
                text-transform: uppercase;
                margin-bottom: 20px;
                font-weight: bold;
                border-left: 8px solid #5aa;
                padding: 6px 12px;
                background-color: #f5fafa;
            }
            .help-block{
                font-size: 90%;
                color: #d35400;
                margin-top: 2px;
            }
            label{
                margin-bottom: 4px;
            }
            .form-group{
                margin-bottom: 20px;
            }
        </style>
    </head>
	<body>
        <div class="container">
            <div class="logo"><img src="logo-jadiorder.png" /></div>
            <div class="wrapper col-md-9">
                <h2 style="text-align:center;margin-bottom:30px;">Website Installer</h2>
                <?php 
                    if(is_writable($db_config_path) AND is_writable($db_config_path2) AND !isset($pesan)){
                ?>
                    <?php
                        if(isset($message)) {
                            echo '
                            <div class="alert alert-warning alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            ' . $message . '
                            </div>';
                        }
                    ?>
                    
                    <form id="install_form" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-md-6">
                                <div class="title">Domain & Lisensi</div>
                                <div class="form-group">
                                    <label for="database">URL Website</label>
                                    <input type="text" id="url" class="form-control" name="url" placeholder="https://jadiorder.com" required />
                                </div>
                                <div class="form-group">
                                    <label for="database">License Key</label>
                                    <textarea type="text" id="lisensi" class="form-control" name="lisensi" rows=4 required></textarea>
                                    <p class="help-block">Kode lisensi yang Anda dapatkan dari halaman member jadiorder.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="title">Database</div>
                                <div class="form-group">
                                    <label for="hostname">Hostname</label>
                                    <input type="text" id="hostname" value="localhost" class="form-control" name="hostname" required />
                                </div>
                                
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" class="form-control" name="username" required />
                                </div>
                                
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" id="password" class="form-control" name="password" />
                                </div>
                                
                                <div class="form-group">
                                    <label for="database">Database Name</label>
                                    <input type="text" id="database" class="form-control" name="database" required />
                                </div>
                            </div>
                        </div>
                        <center><button type="submit" class="btn btn-success" id="submit">Instal Sekarang</button></center>
                    </form>
            
                <?php 
                    }else{
                        if(isset($pesan)){
                            echo "<p class='alert alert-success' style='text-align:center;'>".$pesan."</p>";
                        }else{
                ?>
                    <p class="alert alert-danger">
                        Please make the application/config/database.php file writable.<br>
                        <strong>Example</strong>:<br />
                        <code>chmod 777 application/config/database.php</code>
                    </p>
                <?php 
                        } 
                    } 
                ?>
            </div>
            
            <footer>
                <div class="col-md-12" style="text-align:center;margin-bottom:20px">
                    Copyright <?=date("Y")?> &copy; Birumuda Karya Teknologi
                </div>
            </footer>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	</body>
</html>
