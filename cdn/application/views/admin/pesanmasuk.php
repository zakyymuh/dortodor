<div class="table-responsive">
	<table class="table table-stripped table-hover">
		<tr>
			<td>#</td>
			<td>Pengguna</td>
			<td>Pesan</td>
			<td>Aksi</td>
		</tr>
		<?php
			$page = (isset($_GET["page"]) AND $_GET["page"] != "") ? $_GET["page"] : 1;
			$cari = (isset($_POST["cari"]) AND $_POST["cari"] != "") ? $_POST["cari"] : "";
			$orderby = (isset($data["orderby"]) AND $data["orderby"] != "") ? $data["orderby"] : "id";
			$perpage = 10;
			$blink = "";

			$this->db->select("MAX(id) AS id");
			$this->db->where("(isipesan LIKE '%$cari%' OR tgl LIKE '%$cari%') AND tujuan = 0");
			$this->db->group_by("dari");
			$dbs = $this->db->get("pesan");
			$idin = array(0);
			foreach($dbs->result() as $is){
				$idin[] = $is->id;
			}
			
			$this->db->select("id");
			$this->db->where_in("id",$idin);
			//$this->db->group_by("dari");
			$rows = $this->db->get("pesan");
			$rows = $rows->num_rows();
			
			$this->db->where_in("id",$idin);
			//$this->db->group_by("dari");
			$this->db->order_by("id DESC");
			$this->db->limit($perpage,($page-1)*$perpage);
			$db = $this->db->get("pesan");
				
			if($rows > 0){
				$no = 1;
				foreach($db->result() as $r){
					$blink = ($r->baca == 0) ? '<i class="fas fa-circle text-danger blink"></i>' : '<i class="fas fa-check-double text-success"></i>';
					$pesan = $this->func->clean($r->isipesan);
					if($r->idproduk > 0){
						$prod = $this->func->getProduk($r->idproduk,"semua");
						$pesan = "Tagging Produk: ".$prod->nama;
					}
					$pesan = $this->func->potong($pesan,80,"..");
		?>
			<tr>
				<td><?=$blink?></td>
				<td>
					<?=strtoupper($this->func->getProfil($r->dari,"nama","usrid"))?><br/>
					<small><?=$this->func->ubahTgl("d/m/Y H:i",$r->tgl)?></small>
				</td>
				<td><?=$pesan?></td>
				<td>
					<button type="button" onclick="openPesan(<?=$r->dari?>)" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> &nbsp;Lihat</button>
				</td>
			</tr>
		<?php	
					$no++;
				}
			}else{
				echo "<div class='well well-success text-center text-danger'>Belum ada pesan</div>";
			}
		?>
	</table>
</div>

<?=$this->func->createPagination($rows,$page,$perpage,"loadSemua");?>