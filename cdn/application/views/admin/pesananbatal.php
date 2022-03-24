<div class="table-responsive">
	<table class="table table-condensed table-hover">
		<tr>
			<th scope="col">Tanggal</th>
			<th scope="col">No Transaksi</th>
			<th scope="col">Nama Pembeli</th>
			<th scope="col">Total</th>
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

			$where = "status = 4 AND (orderid LIKE '%$cari%' OR usrid IN(".$in."))";
			$this->db->select("id");
			$this->db->where($where);
			//$this->db->like("orderid",$cari);
			//$this->db->where("status",4);
			$rows = $this->db->get("transaksi");
			$rows = $rows->num_rows();

			$this->db->where($where);
			//$this->db->like("orderid",$cari);
			//$this->db->where("status",4);
			$this->db->order_by("id","DESC");
			$this->db->limit($perpage,($page-1)*$perpage);
			$db = $this->db->get("transaksi");
			
			if($rows > 0){
				$no = 1;
				foreach($db->result() as $r){
					//$trx = $this->func->getTransaksi($r->id,"semua","idbayar");
					$total = $this->func->getBayar($r->idbayar,"total");
					$cod = ($r->cod == 1) ? "<br/><span class='badge badge-warning' style='font-weight:normal'>Bayar Ditempat (COD)</span>" : "";
					$cod .= ($r->dropship != "") ? "<br/><span class='badge badge-info' style='font-weight:normal'>d</span>" : "";
					$cod .= ($r->po > 0) ? "<br/><span class='badge badge-warning' style='font-weight:normal'><i class='fas fa-history'></i> Pre Order</span>" : "";
					$cod .= ($r->digital == 1) ? "<br/><span class='badge badge-primary' style='font-weight:normal'><i class='fas fa-cloud'></i> Produk Digital</span>" : "";
					$profil = $this->func->getProfil($r->usrid,"semua","usrid");
					if($r->digital != 1){
						$kurir = strtoupper($this->func->getKurir($r->kurir,"nama"))."<br/><small class='text-primary'>".strtoupper($this->func->getPaket($r->paket,"nama"))."</small>";
						$alamat = $this->func->getAlamat($r->alamat,"semua");
						$pembeli = "<span class='text-primary'>[".$this->security->xss_clean($profil->nama)."]</span>";
						$pembeli .= "<br/><small>".$this->security->xss_clean($alamat->nama." (".$alamat->nohp).")</small>";
						$pembeli .= "<br/><small class='m-t--4 dis-block'><i>".$this->security->xss_clean($alamat->alamat)."</i></small>";
					}else{
						$kurir = "";
						$pembeli = "<span class='text-primary'>".strtoupper(strtolower($this->security->xss_clean($profil->nama)))."</span><br/>".$profil->nohp;
					}
		?>
			<tr>
				<td class="text-center"><i class="fas fa-times-circle text-danger"></i> &nbsp; <?=$this->func->ubahTgl("d M Y H:i",$r->tgl);?></td>
				<td><?=$r->orderid.$cod?></td>
				<td><?=$pembeli?></td>
				<td><?=$this->func->formUang($total)?></td>
				<td><?=$kurir?></td>
				<td>
					<a href="javascript:detail(<?=$r->id?>)" class="btn btn-sm btn-primary"><i class="fas fa-list"></i> Detail</a>
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

	<?=$this->func->createPagination($rows,$page,$perpage,"loadBatal");?>
</div>