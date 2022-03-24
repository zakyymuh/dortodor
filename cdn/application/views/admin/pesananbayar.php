<div class="table-responsive">
	<table class="table table-condensed table-hover">
		<tr>
			<th scope="col">Tanggal</th>
			<th scope="col">No Invoice</th>
			<th scope="col">Nama Pembeli</th>
			<th scope="col">Total</th>
			<th scope="col">Kode Bayar</th>
			<th scope="col">Kurir</th>
			<th scope="col">Aksi</th>
		</tr>
		<?php
			$page = (isset($_GET["page"]) AND $_GET["page"] != "") ? $_GET["page"] : 1;
			$cari = (isset($_POST["cari"]) AND $_POST["cari"] != "") ? $_POST["cari"] : "";
			$orderby = (isset($data["orderby"]) AND $data["orderby"] != "") ? $data["orderby"] : "id";
			$perpage = 10;
			
			$in = 0;
			$arr = array();
			$this->db->select("usrid");
			$this->db->like("nama",$cari);
			$this->db->or_like("alamat",$cari);
			$this->db->or_like("nohp",$cari);
			$al = $this->db->get("alamat");
			foreach($al->result() as $l){
				$arr[] = $l->usrid;
			}
			$this->db->select("usrid");
			$this->db->like("nama",$cari);
			$this->db->or_like("nohp",$cari);
			$al = $this->db->get("profil");
			foreach($al->result() as $l){
				$arr[] = $l->usrid;
			}
			$arr = array_unique($arr);
			$arr = array_values($arr);
			for($i=0; $i<count($arr); $i++){
				$ins = ",".$arr[$i];
				$in = ($in != 0) ? $in.$ins : $arr[$i];
			}

			$where = "(invoice LIKE '%$cari%' OR total LIKE '%$cari%' OR kodebayar LIKE '%$cari%' OR usrid IN(".$in.")) AND status = 0";
			$this->db->select("id");
			$this->db->where($where);
			$rows = $this->db->get("pembayaran");
			$rows = $rows->num_rows();

			$this->db->from('pembayaran');
			$this->db->where($where);
			$this->db->order_by($orderby,"desc");
			$this->db->limit($perpage,($page-1)*$perpage);
			$pro = $this->db->get();
			
			if($rows > 0){
				$no = 1;
				foreach($pro->result() as $r){
					$bukti = "";
					$trx = $this->func->getTransaksi($r->id,"semua","idbayar");
					$tgl = $this->func->ubahTgl("d M Y H:i",$r->tgl);
					$this->db->where("idbayar",$r->id);
					$dbs = $this->db->get("konfirmasi");
					if($dbs->num_rows() > 0){
						foreach($dbs->result() as $res){
							$bukti = $res->bukti;
							$tgl .= "<br/><a href='javascript:void(0)' onclick='bukti(\"".base_url("konfirmasi/".$res->bukti)."\")'>&raquo; Lihat Bukti Transfer</a>";
						}
					}
					$img = ($r->tripay_ref != "") ? "<img style='height:12px;' src='".base_url("assets/img/tripay.png")."'>" : "";
					$img = ($r->midtrans_id != "") ? "<img style='height:12px;' src='".base_url("assets/img/midtrans.png")."'>" : $img;
					$trxid = $trx->id;
					$cod = ($trx->dropship != "") ? "<br/><span class='badge badge-info' style='font-weight:normal'>d</span>" : "";
					$cod .= ($trx->po > 0) ? "<br/><span class='badge badge-warning' style='font-weight:normal'><i class='fas fa-history'></i> Pre Order</span>" : "";
					$cod .= ($r->digital == 1) ? "<br/><span class='badge badge-primary' style='font-weight:normal'><i class='fas fa-cloud'></i> Produk Digital</span>" : "";
					$profil = $this->func->getProfil($r->usrid,"semua","usrid");
					if($r->digital != 1){
						$kurir = strtoupper($this->func->getKurir($trx->kurir,"nama"))."<br/><small class='text-primary'>".strtoupper($this->func->getPaket($trx->paket,"nama"))."</small>";
						$alamat = $this->func->getAlamat($trx->alamat,"semua");
						$pembeli = "<span class='text-primary'>[".$this->security->xss_clean($profil->nama)."]</span>";
						$pembeli .= "<br/><small>".$this->security->xss_clean($alamat->nama." (".$alamat->nohp).")</small>";
						$pembeli .= "<br/><small class='m-t--4 dis-block'><i>".$this->security->xss_clean($alamat->alamat)."</i></small>";
					}else{
						$kurir = "";
						$pembeli = "<span class='text-primary'>".strtoupper(strtolower($this->security->xss_clean($profil->nama)))."</span><br/>".$profil->nohp;
					}
					$lepel = "";
					switch($this->func->getUserdata($r->usrid,"level")){
						case 2: $lepel = "<span class='badge badge-success' style='font-weight:normal'>Reseller</span>";
						break;
						case 3: $lepel = "<span class='badge badge-success' style='font-weight:normal'>Agen</span>";
						break;
						case 4: $lepel = "<span class='badge badge-success' style='font-weight:normal'>Agen Premium</span>";
						break;
						case 5: $lepel = "<span class='badge badge-success' style='font-weight:normal'>Distributor</span>";
						break;
					}
					switch($r->metode_bayar){
						case 1: $metode = "Bayar Ditempat (COD)";
						break;
						case 2: $metode = "Transfer";
						break;
						case 3: $metode = "Tripay";
						break;
						case 4: $metode = "Midtrans";
						break;
					}
					$metodes = ($r->metode == 2) ? "Saldo" : $metode;
					$metodes = ($r->metode == 2 AND $r->transfer > 0) ? $metodes.": <span class='text-danger'>Rp. ".$this->func->formUang($r->saldo)."</span><br/>" : $metodes;
					$metodes = ($r->metode == 2 AND $r->transfer > 0) ? $metodes.$metode.": <span class='text-danger'>Rp. ".$this->func->formUang($r->transfer)."</span>" : $metodes;
		?>
			<tr>
				<td class="text-center"><i class="fas fa-circle text-danger blink"></i> &nbsp; <?=$tgl;?></td>
				<td><?=$r->invoice?> &nbsp; <?php echo $img.$cod; ?></td>
				<td><?=$pembeli?> &nbsp;<?=$lepel?></td>
				<td><?=$this->func->formUang($r->total-$r->kodebayar+$r->biaya_cod)."<br/><small class='text-primary'>".$metodes."</small>"?></td>
				<td><?=$this->func->formUang($r->kodebayar)?></td>
				<td><?=$kurir?></td>
				<td style="min-width:220px">
				<div class="btn-group">
					<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						Pilih Aksi
					</button>
					<div class="dropdown-menu">
						<?php if($r->tripay_ref == "" OR $bukti != ""){?><a href="javascript:void(0)" onclick="konfirm(<?=$r->id?>)" class="dropdown-item p-tb-8"><i class="fas fa-check text-success"></i> Verifikasi</a><?php } ?>
						<a href="javascript:void(0)" onclick="detail(<?=$trxid?>)" class="dropdown-item p-tb-8"><i class="fas fa-list text-primary"></i> Detail</a>
						<?php if($r->tripay_ref == ""){?><a href="javascript:void(0)" onclick="batalin(<?=$r->id?>)" class="dropdown-item p-tb-8 text-danger"><i class="fas fa-times"></i> Batalkan</a><?php } ?>
					</div>
				</div>
				</td>
			</tr>
		<?php	
					$no++;
				}
			}else{
				echo "<tr><td colspan=7 class='text-center text-danger'>Belum ada pesanan</td></tr>";
			}
		?>
	</table>

	<?=$this->func->createPagination($rows,$page,$perpage,"loadBayar");?>
</div>

<script type="text/javascript">	
	function konfirm(id){
		swal.fire({
			title: "Perhatian!",
			text: "pastikan uang sudah benar-benar masuk/ditranfer, lebih baik cek kembali mutasi.",
			type: "warning",
			showCancelButton: true,
			cancelButtonText: "Batal"
		}).then((val)=>{
			if(val.value){
				loadingDulu();
				$.post("<?=site_url("api/updatepesanan")?>",{"id":id,"statusbayar":1,[$("#names").val()]:$("#tokens").val()},function(e){
					var data = eval("("+e+")");
					updateToken(data.token);
					if(data.success == true){
						swal.fire("Berhasil!","Pesanan siap untuk segera dikirim","success");
						loadBayar(1);
					}else{
						swal.fire("Gagal!","Terjadi kendala saat mengupdate data, cobalah beberapa saat lagi","error");
					}
				});
			}
		});
	}
	function batalin(id){
		swal.fire({
			title: "Perhatian!",
			text: "pesanan akan dibatalkan dan stok akan bertambah kembali.",
			type: "warning",
			showCancelButton: true,
			cancelButtonText: "Tidak Jadi"
		}).then((val)=>{
			loadingDulu();
			if(val.value){
				$.post("<?=site_url('api/batalkanpesanan')?>",{"id":id,[$("#names").val()]:$("#tokens").val()},function(e){
					var data = eval("("+e+")");
					updateToken(data.token);
					if(data.success == true){
						swal.fire("Berhasil!","Pesanan telah dibatalkan","success");
						loadBayar(1);
					}else{
						swal.fire("Gagal!","Terjadi kendala saat mengupdate data, cobalah beberapa saat lagi","error");
					}
				});
			}
		});
	}
	
	function bukti(url){
		$("#bukti").attr("src",url);
		$("#modalbukti").modal();
	}
</script>

<div class="modal fade" id="modalbukti" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<img id="bukti" src="<?=base_url('assets/img/no-image.png')?>" style='width:100%;' />
		</div>
	</div>
</div>
