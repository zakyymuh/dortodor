<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Checkout extends CI_Controller {
	public function __construct(){
		parent::__construct();

		/*\Midtrans\Config::$serverKey = $this->func->getSetting("midtrans_server");
		\Midtrans\Config::$isProduction = false;
		\Midtrans\Config::$isSanitized = true;
		\Midtrans\Config::$is3ds = true;

		if($this->func->maintenis() == TRUE) {
			include(APPPATH.'views/maintenis.php');

			die();
		}*/
    }

    function index(){
		if($this->func->cekLogin() == true){
			if($_SESSION["status"] == 1){
                $this->load->view("headv2");
                $this->load->view("checkout/main");
                $this->load->view("footv2");
			}else{
				$this->load->view("headv2");
				$this->load->view("main/sukses_verifikasi",array("belumverif"=>true));
				$this->load->view("footv2");
			}
		}else{
            redirect("home/signin");
        }
    }

    function alamat(){
		if($this->func->cekLogin() == true){
			if($_SESSION["status"] == 1){
                $this->load->view("checkout/alamat");
			}else{
				$this->load->view("headv2");
				$this->load->view("main/sukses_verifikasi",array("belumverif"=>true));
				$this->load->view("footv2");
			}
		}else{
            redirect("home/signin");
        }
    }
    function simpanalamat(){
        if($this->func->cekLogin() == true AND isset($_SESSION["prebayar"])){
			if($_SESSION["status"] == 1){
                if($_POST["alamat"] == "0"){
					$this->db->where("usrid",$_SESSION["usrid"]);
					$statusal = ($this->db->get("alamat")->num_rows() > 0) ? 0 : 1;
					$alamat = array(
						"usrid"		=> $_SESSION["usrid"],
						"status"	=> $statusal,
						"idkec"		=> $_POST["idkec"],
						"judul"		=> $_POST["judul"],
						"alamat"	=> $_POST["alamatbaru"],
						"kodepos"	=> $_POST["kodepos"],
						"nama"		=> $_POST["nama"],
						"nohp"		=> $_POST["nohp"]
					);
					$this->db->insert("alamat",$alamat);
					$idalamat = $this->db->insert_id();
                    $tujuan = $_POST["idkec"];
				}else{
					$idalamat = $_POST["alamat"];
                    $this->db->where("usrid",$_SESSION["usrid"]);
                    $this->db->where("id",$idalamat);
                    $al = $this->db->get("alamat");
					$idalamat = 0;
					$tujuan = 0;
                    foreach($al->result() as $r){
                        $idalamat = $r->id;
                        $tujuan = $r->idkec;
                    }
				}

                if($idalamat > 0 AND $tujuan > 0){
					$data = ["alamat"=>$idalamat,"tujuan"=>$tujuan];
					if(isset($_POST["dropship"])){
						$data["dropship"] = $_POST["dropship"];
						$data["dropshipnomer"] = $_POST["dropshipnomer"];
						$data["dropshipalamat"] = $_POST["dropshipalamat"];
					}
                    $this->db->where("id",$_SESSION["prebayar"]);
                    $this->db->update("pembayaran_pre",$data);
                    echo json_encode(["success"=>true]);
                }else{
                    echo json_encode(["success"=>false]);
                }
			}else{
				$this->load->view("headv2");
				$this->load->view("main/sukses_verifikasi",array("belumverif"=>true));
				$this->load->view("footv2");
			}
		}else{
            redirect("home/signin");
        }
    }

    function kurir(){
		if($this->func->cekLogin() == true){
			if($_SESSION["status"] == 1){
                $this->load->view("checkout/kurir");
			}else{
				$this->load->view("headv2");
				$this->load->view("main/sukses_verifikasi",array("belumverif"=>true));
				$this->load->view("footv2");
			}
		}else{
            redirect("home/signin");
        }
    }
    function simpankurir(){
        if($this->func->cekLogin() == true AND isset($_SESSION["prebayar"])){
			if($_SESSION["status"] == 1){
                $prebayar = $this->func->getPreBayar($_SESSION["prebayar"],"semua");
                if($prebayar->id > 0){
                    $result = $this->func->cekOngkir($prebayar->dari,$prebayar->berat,$prebayar->tujuan,$_POST["kurir"],$_POST["paket"]);
                    if($result["success"] == true){
						//$total = intval($result["harga"]) + $prebayar->total;
                        $this->db->where("id",$_SESSION["prebayar"]);
                        $this->db->update("pembayaran_pre",["kurir"=>$_POST["kurir"],"paket"=>$_POST["paket"],"ongkir"=>$result["harga"],"cod"=>$result["cod"]]);
                        echo json_encode(["success"=>true]);
                    }else{
                        echo json_encode(["success"=>false]);
                    }
                }else{
                    echo json_encode(["success"=>false]);
                }
			}else{
				$this->load->view("headv2");
				$this->load->view("main/sukses_verifikasi",array("belumverif"=>true));
				$this->load->view("footv2");
			}
		}else{
            redirect("home/signin");
        }
    }

    function kupon(){
		if($this->func->cekLogin() == true){
			if($_SESSION["status"] == 1){
				if(isset($_POST["kode"])){
					$voc = $this->func->getVoucher($_POST["kode"],"semua","kode");
					$pre = $this->func->getPreBayar($_SESSION["prebayar"],"semua");
					
					if(is_object($voc) AND is_object($pre) AND $pre->digital == $voc->digital){
						$harga = 0;
						$this->db->where_in("id",explode("|",$pre->produk));
						$tb = $this->db->get("transaksiproduk");
						foreach($tb->result() as $t){
							$harga = $t->harga*$t->jumlah;
						}

						$this->db->select("id");
						$this->db->where("voucher",$voc->id);
						$this->db->where("usrid",$_SESSION["usrid"]);
						$this->db->where("status <",2);
						$sudah = $this->db->get("pembayaran");

						$tgla = $this->func->ubahTgl("Ymd",$voc->mulai);
						$tglb = $this->func->ubahTgl("Ymd",$voc->selesai);
						if($tgla <= date("Ymd") AND $tglb >= date("Ymd") AND $sudah->num_rows() < $voc->peruser){
							if($voc->jenis == 1){
								if($voc->tipe == 2){
									$diskon = ($harga >= $voc->potonganmin) ? $voc->potongan : 0;
								}else{
									$diskon = ($harga >= $voc->potonganmin) ? $harga * ($voc->potongan/100) : 0;
								}
								$diskonmax = $diskon;
								$diskon = ($harga >= $diskon) ? $diskon : $harga;
							}elseif($voc->jenis == 2){
								if($voc->tipe == 2){
									$diskon = ($harga >= $voc->potonganmin) ? $voc->potongan : 0;
								}else{
									$diskon = ($harga >= $voc->potonganmin) ? $pre->ongkir * ($voc->potongan/100) : 0;
								}
								$diskonmax = $diskon;
								$diskon = ($pre->ongkir >= $diskon) ? $diskon : $pre->ongkir;
							}else{
								$diskon = 0;
								$diskonmax = 0;
							}
							if($voc->potonganmaks != 0){
								$diskon = ($diskon < $voc->potonganmaks) ? $diskon : $voc->potonganmaks;
							}

							$this->db->where("id",$pre->id);
							$this->db->update("pembayaran_pre",["diskon"=>$diskon,"voucher"=>$voc->id]);
							echo json_encode(["success"=>true,"diskon"=>$diskon,"diskonmax"=>$diskonmax,"token"=>$this->security->get_csrf_hash()]);
						}else{
							echo json_encode(["success"=>false,"msg"=>"masa promo habis atau kuota sudah penuh","token"=>$this->security->get_csrf_hash()]);
						}
					}else{
						echo json_encode(["success"=>false,"msg"=>"tidak ditemukan","token"=>$this->security->get_csrf_hash()]);
					}
				}else{
					echo json_encode(["success"=>false,"msg"=>"form tidak lengkap","token"=>$this->security->get_csrf_hash()]);
				}
			}else{
				$this->load->view("headv2");
				$this->load->view("main/sukses_verifikasi",array("belumverif"=>true));
				$this->load->view("footv2");
			}
		}else{
            redirect("home/signin");
        }
    }

    function bayar(){
		if($this->func->cekLogin() == true){
			if($_SESSION["status"] == 1){
                $this->load->view("checkout/bayar");
			}else{
				$this->load->view("headv2");
				$this->load->view("main/sukses_verifikasi",array("belumverif"=>true));
				$this->load->view("footv2");
			}
		}else{
            redirect("home/signin");
        }
    }
    function simpanbayar(){
        if($this->func->cekLogin() == true AND isset($_SESSION["prebayar"])){
			if($_SESSION["status"] == 1){
                $pre = $this->func->getPreBayar($_SESSION["prebayar"],"semua");
                $saldo = $this->func->getSaldo($_SESSION["usrid"],"saldo","usrid");
                $set = $this->func->globalset("semua");
				$usrid = $this->func->getUser($_SESSION["usrid"],"semua");

                if($pre->id > 0 AND isset($_POST["metode"]) AND isset($_POST["metode_bayar"])){
					$text = "";
					$produkwa = "";
					$hrgwatotal = 0;
					$wa = isset($_GET["type"]) AND $_GET["type"] == "wasap" ? "wasap" : null;
					$idbayar = 0;
					$kodebayaran = rand(100,999);
					$kodebayar = $kodebayaran;
					$total = $pre->ongkir + $pre->total - $pre->diskon;
					$psaldo = ($saldo >= $total) ? $total : $saldo;
					if($_POST["metode"] == 2){
						$transfer = $total - $psaldo;
						$saldopotong = $psaldo;
					}else{
						$transfer = $total;
						$saldopotong = 0;
					}
					
					if($_POST["metode_bayar"] == 2){
						$total = $kodebayaran + $total;
					}else{
						$kodebayar = 0;
					}
		
					$status = 0;
					$biaya_cod = (floatval($set->biaya_cod) > 0) ? (floatval($set->biaya_cod)/100)*($total - $pre->ongkir) : 0;
					$biaya_cod = (floatval($set->biaya_cod) > 100) ? $set->biaya_cod : $biaya_cod;
					$total = ($_POST["metode_bayar"] == 1) ? $total + $biaya_cod : $total;
					$bcod = ($_POST["metode_bayar"] == 1) ? $biaya_cod : 0;
					$status = ($_POST["metode"] == 2 AND $transfer <= 0) ? 1 : 0;
					$status = ($_POST["metode_bayar"] == 1) ? 1 : $status;
					$cod = ($_POST["metode_bayar"] == 1) ? 1 : 0;
	
					$bayar = array(
						"usrid"	=> $_SESSION["usrid"],
						"tgl"	=> date("Y-m-d H:i:s"),
						"total"	=> $total,
						"saldo"	=> $saldopotong,
						"kodebayar"	=> $kodebayar,
						"transfer"	=> $transfer,
						"digital"	=> $pre->digital,
						"voucher"	=> $pre->voucher,
						"metode"	=> $_POST["metode"],
						"metode_bayar"	=> $_POST["metode_bayar"],
						"biaya_cod"	=> $bcod,
						"diskon"	=> $pre->diskon,
						"status"	=> $status,
						"kadaluarsa"=> date('Y-m-d H:i:s', strtotime("+2 days"))
					);
					$this->db->insert("pembayaran",$bayar);
					$idbayar = $this->db->insert_id();
	
					if($_POST["metode"] == 2){
						$saldoawal = $saldo;
						$saldoakhir = $saldoawal - $saldopotong;
						$this->db->where("usrid",$_SESSION["usrid"]);
						$this->db->update("saldo",array("saldo"=>$saldoakhir,"apdet"=>date("Y-m-d H:i:s")));
	
						$sh = array(
							"tgl"	=> date("Y-m-d H:i:s"),
							"usrid"	=> $_SESSION["usrid"],
							"jenis"	=> 2,
							"jumlah"	=> $saldopotong,
							"darike"	=> 3,
							"sambung"	=> $idbayar,
							"saldoawal"	=> $saldoawal,
							"saldoakhir"	=> $saldoakhir
						);
						$this->db->insert("saldohistory",$sh);
					}
	
					$invoice = date("Ymd").$idbayar.$kodebayaran;
					$this->db->where("id",$idbayar);
					$this->db->update("pembayaran",array("invoice"=>$invoice));
					$invoice = "#".$invoice;
	
					$status = ($status == 1 AND $pre->digital == 1) ? 3 : $status;
					$transaksi = array(
						"orderid"	=> "TRX".date("YmdHis"),
						"tgl"		=> date("Y-m-d H:i:s"),
						"tglupdate"	=> date("Y-m-d H:i:s"),
						"kadaluarsa"=> date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s"). ' + 2 days')),
						"usrid"	=> $_SESSION["usrid"],
						"digital"	=> $pre->digital,
						"alamat"=> $pre->alamat,
						"berat"	=> $pre->berat,
						"ongkir"=> $pre->ongkir,
						"kurir"	=> $pre->kurir,
						"paket"	=> $pre->paket,
						"dari"	=> $pre->dari,
						"tujuan"=> $pre->tujuan,
						"cod"	=> $cod,
						"biaya_cod"	=> $bcod,
						"status"	=> $status,
						"idbayar"	=> $idbayar
					);
					if($pre->dropship != ""){
						$transaksi["dropship"] = $pre->dropship;
						$transaksi["dropshipnomer"] = $pre->dropshipnomer;
						$transaksi["dropshipalamat"] = $pre->dropshipalamat;
					}
					$this->db->insert("transaksi",$transaksi);
					$idtransaksi = $this->db->insert_id();
	
					$produk = explode("|",$pre->produk);
					for($i=0; $i<count($produk); $i++){
						$this->db->where("id",$produk[$i]);
						$this->db->update("transaksiproduk",array("idtransaksi"=>$idtransaksi));
					}
					
					// UPDATE STOK PRODUK
					$this->db->where("idtransaksi",$idtransaksi);
					$db = $this->db->get("transaksiproduk");
					$nos = 1;
					$po = 0;
					$afiliasi = 0;
					foreach($db->result() as $r){
						$pro = $this->func->getProduk($r->idproduk,"semua");
						$afiliasi += $pro->afiliasi * $r->jumlah;
						$po = ($pro->preorder > 0 AND $pro->pohari > $po) ? $pro->pohari : $po;
						if($r->variasi != 0){
							$var = $this->func->getVariasi($r->variasi,"semua","id");
							if($r->jumlah > $var->stok){
								$produk = explode("|",$pre->produk);
								for($i=0; $i<count($produk); $i++){
									$this->db->where("id",$produk[$i]);
									$this->db->update("transaksiproduk",array("idtransaksi"=>0));
								}
								$this->db->where("id",$idtransaksi);
								$this->db->delete("transaksi");
								$this->db->where("id",$idbayar);
								$this->db->delete("pembayaran");

								echo json_encode(array("success"=>false,"message"=>"stok produk tidak mencukupi"));
								$stok = 0;
								exit;
							}
							
							$stok = $var->stok - $r->jumlah;
							$prostok = $pro->stok - $r->jumlah;
							$this->db->where("id",$r->idproduk);
							$this->db->update("produk",["stok"=>$prostok,"tglupdate"=>date("Y-m-d H:i:s")]);
	
							$this->db->where("id",$r->variasi);
							$this->db->update("produkvariasi",["stok"=>$stok,"tgl"=>date("Y-m-d H:i:s")]);
							
							$data = array(
								"usrid"	=> $_SESSION["usrid"],
								"stokawal" => $var->stok,
								"stokakhir" => $stok,
								"variasi" => $r->variasi,
								"jumlah" => $r->jumlah,
								"tgl"	=> date("Y-m-d H:i:s"),
								"idtransaksi" => $idtransaksi
							);
							$this->db->insert("historystok",$data);
						}else{
							if($r->jumlah > $pro->stok){
								$produk = explode("|",$pre->produk);
								for($i=0; $i<count($produk); $i++){
									$this->db->where("id",$produk[$i]);
									$this->db->update("transaksiproduk",array("idtransaksi"=>0));
								}
								$this->db->where("id",$idtransaksi);
								$this->db->delete("transaksi");
								$this->db->where("id",$idbayar);
								$this->db->delete("pembayaran");
								
								echo json_encode(array("success"=>false,"message"=>"stok produk tidak mencukupi"));
								$stok = 0;
								exit;
							}
							$stok = $pro->stok - $r->jumlah;
							$this->db->where("id",$r->idproduk);
							$this->db->update("produk",["stok"=>$stok,"tglupdate"=>date("Y-m-d H:i:s")]);
	
							$data = array(
								"usrid"	=> $_SESSION["usrid"],
								"stokawal" => $pro->stok,
								"stokakhir" => $stok,
								"variasi" => 0,
								"jumlah" => $r->jumlah,
								"tgl"	=> date("Y-m-d H:i:s"),
								"idtransaksi" => $idtransaksi
							);
							$this->db->insert("historystok",$data);
						}
	
						if($wa != null){
							$variasee = $this->func->getVariasi($r->variasi,"semua");
							if(isset($variasee->harga)){
								if($_SESSION["lvl"] == 5){
									$hargawa = $variasee->hargadistri;
								}elseif($_SESSION["lvl"] == 4){
									$hargawa = $variasee->hargaagensp;
								}elseif($_SESSION["lvl"] == 3){
									$hargawa = $variasee->hargaagen;
								}elseif($_SESSION["lvl"] == 2){
									$hargawa = $variasee->hargareseller;
								}else{
									$hargawa = $variasee->harga;
								}
							}else{
								if($_SESSION["lvl"] == 5){
									$hargawa = $pro->hargadistri;
								}elseif($_SESSION["lvl"] == 4){
									$hargawa = $pro->hargaagensp;
								}elseif($_SESSION["lvl"] == 3){
									$hargawa = $pro->hargaagen;
								}elseif($_SESSION["lvl"] == 2){
									$hargawa = $pro->hargareseller;
								}else{
									$hargawa = $pro->harga;
								}
							}
							$hargawatotal = $hargawa*$r->jumlah;
							$hrgwatotal += $hargawatotal;
							$variaksi = ($r->variasi != 0 AND $variasee != null) ? $this->func->getWarna($variasee->warna,"nama")." ".$pro->subvariasi." ".$this->func->getSize($variasee->size,"nama") : "";
							$produkwa .= "*".$nos.". ".$pro->nama."*\n";
							$produkwa .= ($r->variasi != 0 AND $variasee != null) ? "    Varian : ".$variaksi."\n" : "";
							$produkwa .= "    Jumlah : ".$r->jumlah."\n";
							$produkwa .= "    Harga (@) : Rp ".$this->func->formUang($hargawa)."\n";
							$produkwa .= "    Harga Total : Rp ".$this->func->formUang($hargawatotal)."\n \n";
							$nos++;
						}
					}
					$this->db->where("id",$idtransaksi);
					$this->db->update("transaksi",['po'=>$po]);
					
					// AFILIASI
					if($usrid->upline > 0 AND $afiliasi > 0){
						$affs = array(
							"tgl"	=> date("Y-m-d H:i:s"),
							"usrid"	=> $usrid->upline,
							"idtransaksi"	=> $idtransaksi,
							"status"=> $status,
							"jumlah"=> $afiliasi
						);
						$this->db->insert("afiliasi",$affs);
					}
					
					//$idbayar = $this->func->arrEnc(array("idbayar"=>$idbayar),"encode");
					if($pre->digital == 0){
						$alamat = $this->func->getAlamat($pre->alamat,"semua");
						$kec = $this->func->getKec($alamat->idkec,"semua");
						$kab = $this->func->getKab($kec->idkab,"nama");
						$alamatz = $alamat->alamat.", ".$kec->nama.", ".$kab." - ".$alamat->kodepos;
						$kurir = $this->func->getKurir($pre->kurir,"nama")." ".$this->func->getPaket($pre->paket,"nama");
					}
	
					if($wa != null){
						$text = "Halo kak admin ".$set->nama.", saya mau order produk berikut dong\n\n";
						$text .= $produkwa;
						$text .= "Subtotal : *Rp ".$this->func->formUang($hrgwatotal)."*\n";
						$text .= $pre->digital == 0 ? "Ongkos Kirim : *Rp ".$this->func->formUang($pre->ongkir)."*\n" : "";
						$text .= $kodebayar > 0 ? "Kode Bayar : *Rp ".$this->func->formUang($kodebayar)."*\n" : "";
						$text .= $pre->diskon > 0 ? "Diskon : *Rp ".$this->func->formUang($pre->diskon)."*\n" : "";
						$text .= "Total : *Rp ".$this->func->formUang($total)."*\n";
						if($pre->digital == 0){
						$text .= "------------------------------\n\n";
						$text .= "*Nama Penerima*\n";
						$text .= $alamat->nama." (".$alamat->nohp.")\n\n";
						$text .= "*Alamat Pengiriman*\n";
						$text .= $alamatz."\n\n";
						$text .= "*Jasa Kurir*\n";
						$text .= strtoupper($kurir);
						}
					}else{
						$text = "";
					}
	
					if($wa == null){
						$profil = $this->func->getProfil($_SESSION["usrid"],"semua","usrid");
						$diskon = $pre->diskon != 0 ? "Diskon: <b>Rp ".$this->func->formUang($pre->diskon)."</b><br/>" : "";
						$diskonwa = $pre->diskon != 0 ? "Diskon: *Rp ".$this->func->formUang($pre->diskon)."*\n" : "";
	
						// NOTIFIKASI PEMBELI
						$pesan = "
							Halo <b>".$profil->nama."</b><br/>".
							"Terimakasih sudah membeli produk kami.<br/>".
							"Saat ini kami sedang menunggu pembayaran darimu sebelum kami memprosesnya. Sebagai informasi, berikut detail pesananmu <br/>".
							"No Invoice: <b>".$invoice."</b><br/> <br/>".
							"Total Pesanan: <b>Rp ".$this->func->formUang($total)."</b><br/>";
						if($pre->digital == 0){
							$pesan .= "Ongkos Kirim: <b>Rp ".$this->func->formUang($pre->ongkir)."</b><br/>".$diskon.
								"Metode Pengiriman: <b>".strtoupper($kurir)."</b><br/> <br/>".
								"Detail Pengiriman <br/>".
								"Penerima: <b>".$alamat->nama."</b> <br/>".
								"No HP: <b>".$alamat->nohp."</b> <br/>".
								"Alamat: <b>".$alamatz."</b>".
								"<br/> <br/>";
						}
						if($_POST["metode_bayar"] == 2){
							$pesan .= "Berikut informasi rekening untuk pembayaran pesanan<br/>";
							$this->db->where("usrid",0);
							$rek = $this->db->get("rekening");
							foreach($rek->result() as $re){
								$pesan .= "<b style='font-size:120%'>".$this->func->getBank($re->idbank,"nama")." ".$re->norek."</b><br/>";
								$pesan .= "a/n ".$re->atasnama."<br/> <br/>";
							}
							$pesan .= "Untuk konfirmasi pembayaran silahkan langsung klik link berikut:<br/>".
								"<a href='".site_url("manage/pesanan")."?konfirmasi=".$idbayar."'>Bayar Pesanan Sekarang &raquo;</a>
							";
						}else{
							$pesan .= "Untuk pembayaran silahkan langsung klik link berikut:<br/>".
								"<a href='".site_url("home/invoice")."?inv=".$idbayar."'>Bayar Pesanan Sekarang &raquo;</a>
							";
						}
						$this->func->sendEmail($usrid->username,$set->nama." - Pesanan",$pesan,"Pesanan");
						$pesan = "
							Halo *".$profil->nama."*\n".
							"Terimakasih sudah membeli produk kami.\n".
							"Saat ini kami sedang menunggu pembayaran darimu sebelum kami memprosesnya. Sebagai informasi, berikut detail pesananmu \n \n".
							"No Invoice: *".$invoice."*\n".
							"Total Pesanan: *Rp ".$this->func->formUang($total)."*\n";
						if($pre->digital == 0){
							$pesan .= "Ongkos Kirim: *Rp ".$this->func->formUang($pre->ongkir)."*\n".$diskonwa.
								"Metode Pengiriman: *".strtoupper($kurir)."*\n \n".
								"Detail Pengiriman \n".
								"Penerima: *".$alamat->nama."*\n".
								"No HP: *".$alamat->nohp."*\n".
								"Alamat: *".$alamatz."*\n \n";
						}
						if($_POST["metode_bayar"] == 2){
							$pesan .= "Berikut informasi rekening untuk pembayaran pesanan \n";
							foreach($rek->result() as $re){
								$pesan .= "*".$this->func->getBank($re->idbank,"nama")." ".$re->norek."* \n";
								$pesan .= "a/n ".$re->atasnama."\n \n";
							}
							$pesan .= "Untuk konfirmasi pembayaran silahkan langsung klik link berikut\n".site_url("manage/pesanan")."?konfirmasi=".$idbayar;
						}else{
							$pesan .= "Untuk pembayaran silahkan langsung klik link berikut\n".site_url("home/invoice")."?inv=".$idbayar;
						}
						$this->func->sendWA($profil->nohp,$pesan);
	
						// SEND NOTIFICATION MOBILE
						$this->func->notifMobile("Pesanan ".$invoice,"Segera lakukan pembayaran agar pesananmu juga segera diproses","",$usrid->id);
	
						// NOTIFIKASI PENJUAL/ADMIN
						$pesan = "
							<h3>Pesanan Baru</h3><br/>
							<b>".strtoupper(strtolower($profil->nama))."</b> telah membuat pesanan baru dengan total pembayaran 
							<b>Rp. ".$this->func->formUang($total)."</b> Invoice ID: <b>".$invoice."</b>
							<br/>&nbsp;<br/>&nbsp;<br/>
							Cek Pesanan Pembeli di <b>Dashboard Admin ".$set->nama."</b><br/>
							<a href='".site_url("cdn")."'>Klik Disini</a>
						";
						$this->func->sendEmail($set->email,$set->nama." - Pesanan Baru",$pesan,"Pesanan Baru di ".$set->nama);
						$pesan = "
							*Pesanan Baru*\n".
							"*".strtoupper(strtolower($profil->nama))."* telah membuat pesanan baru dengan detail:\n".
							"Total Pembayaran: *Rp. ".$this->func->formUang($total)."*\n".
							"Invoice ID: *".$invoice."*".
							"\n \n".
							"Cek Pesanan Pembeli di *Dashboard Admin ".$set->nama."*
							"; 
						$this->func->sendWA($set->wasap,$pesan);
					}

					$this->db->where("id",$pre->id);
					$this->db->update("pembayaran_pre",["status"=>1,"idbayar"=>$idbayar,"saldo"=>$saldopotong,"transfer"=>$transfer,"kodebayar"=>$kodebayar,"metode"=>$_POST["metode"],"metode_bayar"=>$_POST["metode_bayar"]]);
	
					$url = ($status == 0) ? site_url("home/invoice")."?inv=".$idbayar : site_url("manage/pesanan?tab=dikemas");
					$url = ($status > 0 AND $pre->digital == 1) ? site_url("manage/pesanan?tab=digital") : $url;
					echo json_encode(array("success"=>true,"url"=>$url,"text"=>urlencode($text)));
                }else{
                    echo json_encode(["success"=>false]);
                }
			}else{
				$this->load->view("headv2");
				$this->load->view("main/sukses_verifikasi",array("belumverif"=>true));
				$this->load->view("footv2");
			}
		}else{
            redirect("home/signin");
        }
    }
}