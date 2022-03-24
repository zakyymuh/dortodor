<div class="table-responsive">
	<table class="table table-condensed table-hover">
		<tr>
			<th scope="col">No Transaksi</th>
			<th scope="col">Pembeli</th>
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

			$where = "status = 1 AND resi = '' AND (orderid LIKE '%$cari%' OR resi LIKE '%$cari%' OR usrid IN(".$in."))"; 
			//$this->db->like("orderid",$cari);
			//$this->db->where("status",1);
			//$this->db->where("resi","");
			$this->db->select("id");
			$this->db->where($where);
			$rows = $this->db->get("transaksi");
			$rows = $rows->num_rows();

			//$this->db->like("orderid",$cari);
			//$this->db->where("status",1);
			//$this->db->where("resi","");
			$this->db->where($where);
			$this->db->order_by("id","DESC");
			$this->db->limit($perpage,($page-1)*$perpage);
			$db = $this->db->get("transaksi");
			
			if($rows > 0){
				$no = 1;
				foreach($db->result() as $r){
					$kurir = strtoupper($this->func->getKurir($r->kurir,"nama"))."<br/><small class='text-primary'>".strtoupper($this->func->getPaket($r->paket,"nama"))."</small>";
					$alamat = $this->func->getAlamat($r->alamat,"semua");
					$cod = ($r->cod == 1) ? "<br/><span class='badge badge-warning' style='font-weight:normal'>Bayar Ditempat (COD)</span>" : "";
					$cod .= ($r->dropship != "") ? "<br/><span class='badge badge-info' style='font-weight:normal'>d</span>" : "";
					$cod .= ($r->po > 0) ? "<br/><span class='badge badge-warning' style='font-weight:normal'><i class='fas fa-history'></i> Pre Order</span>" : "";
					$profil = $this->func->getProfil($r->usrid,"semua","usrid");
					$pembeli = "<span class='text-primary'>[".$this->security->xss_clean($profil->nama)."]</span>";
					$pembeli .= "<br/><small>".$this->security->xss_clean($alamat->nama." (".$alamat->nohp).")</small>";
					$pembeli .= "<br/><small class='m-t--4 dis-block'><i>".$this->security->xss_clean($alamat->alamat)."</i></small>";
		?>
			<tr>
				<td><?=$this->func->ubahTgl("d M Y H:i",$r->tgl);?><br/><b><?=$r->orderid.$cod?></b></td>
				<td><?=$pembeli?></td>
				<td><?=$kurir?><br/><?=$this->func->formUang($r->ongkir)?></td>
				<td>
					<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
						Pilih Aksi
					</button>
					<div class="dropdown-menu">
						<a href="javascript:cetak(<?=$r->id?>)" class="dropdown-item p-tb-8"><i class="fas fa-print text-warning"></i> Invoice</a>
						<a href="javascript:detail(<?=$r->id?>)" class="dropdown-item p-tb-8"><i class="fas fa-list text-primary"></i> Detail</a>
						<?php if($r->kurir == "cod" OR $r->kurir == "toko"){ ?>
						<a href="javascript:void(0)" onclick="kirimPaket(<?=$r->id?>)" class="dropdown-item p-tb-8"><i class="fas fa-shipping-fast text-success"></i> Kirim Pesanan</a>
						<?php }else{ ?>
						<a href="javascript:void(0)" onclick="inputResi(<?=$r->id?>)" class="dropdown-item p-tb-8"><i class="fas fa-shipping-fast text-success"></i> Resi</a>
						<?php } ?>
						<a href="<?=site_url("api/cetakLabel?id=".$r->id)?>" target="_blank" class="dropdown-item p-tb-8"><i class="fas fa-print text-secondary"></i> Label</a>
					</div>
				</td>
			</tr>
		<?php	
					$no++;
				}
			}else{
				echo "<tr><td colspan=6 class='text-center text-danger'>Belum ada pesanan</td></tr>";
			}
		?>
	</table>

	<?=$this->func->createPagination($rows,$page,$perpage,"loadDikemas");?>
</div>

<script type="text/javascript">
	$(function(){
		$(".simpanresi").on("submit",function(e){
			e.preventDefault();
			var datar = $(this).serialize();
			datar = datar + "&" + $("#names").val() + "=" + $("#tokens").val();
			$.post("<?=site_url("api/inputresi")?>",datar,function(msg){
				var data = eval("("+msg+")");
				updateToken(data.token);
				$(".modal").modal("hide");
				if(data.success == true){
					swal.fire("Berhasil","Pesanan telah diupdate","success").then((val)=>{
						loadDikirim(1);
					});
				}else{
					swal.fire("Gagal","Terjadi kesalahan saat menyimpan data, coba ulangi beberapa saat lagi","error");
				}
			});
		});
	});
		
	function inputResi(id){
		$("#theid").val(id);
		$("#modal").modal();
	}
	function kirimPaket(id){
		$("#theidcod").val(id);
		$("#modalcod").modal();
	}
</script>
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLagu" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title"><i class="fas fa-shipping-fast"></i> Input Nomer Resi</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form class="simpanresi">
				<input type="hidden" id="theid" name="theid" value="0" />
				<div class="modal-body">
					<div class="form-group">
						<label>Masukkan Nomer Resi</label>
						<input type="text" class="form-control" name="resi" required />
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" id="submit" class="btn btn-success">Simpan</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="modalcod" tabindex="-1" role="dialog" aria-labelledby="modalLagu" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title"><i class="fas fa-shipping-fast"></i> Kirim Pesanan</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form class="simpanresi">
				<input type="hidden" id="theidcod" name="theid" value="0" />
				<div class="modal-body">
					<div class="form-group">
						<label>Masukkan Nama Kurir dan No HP</label>
						<input type="text" class="form-control" name="resi" required />
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" id="submit" class="btn btn-success">Simpan</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
				</div>
			</form>
		</div>
	</div>
</div>