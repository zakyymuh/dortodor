<div class="table-responsive">
	<table class="table table-condensed table-hover">
		<tr>
			<th scope="col">No</th>
			<th scope="col">Nama Gudang</th>
			<th scope="col">Kota Pengiriman</th>
			<th scope="col">Penanggungjawab</th>
			<th scope="col">Keterangan</th>
			<th scope="col">Aksi</th>
		</tr>
	<?php
		$page = (isset($_GET["page"]) AND $_GET["page"] != "") ? $_GET["page"] : 1;
		$cari = (isset($_POST["cari"]) AND $_POST["cari"] != "") ? $_POST["cari"] : "";
		$orderby = (isset($data["orderby"]) AND $data["orderby"] != "") ? $data["orderby"] : "id";
		$perpage = 10;
		
		$this->db->select("id");
		$rows = $this->db->get("gudang");
		$rows = $rows->num_rows();
		
		$this->db->order_by("id","DESC");
		$this->db->limit($perpage,($page-1)*$perpage);
		$db = $this->db->get("gudang");
			
		if($rows > 0){
			$no = 1;
			foreach($db->result() as $r){
                $kab = $this->func->getKab($r->idkab,"semua");
	?>
			<tr>
				<td><?=$no?></td>
				<td><?=$r->nama?></td>
				<td><?=$kab->tipe." ".$kab->nama?></td>
				<td><?=$r->kontak."<br/>".$r->kontak_nohp?></td>
				<td><?=$r->keterangan?></td>
				<td>
					<button onclick="edit(<?=$r->id?>)" class="btn btn-xs btn-warning"><i class="fas fa-pencil-alt"></i> edit</button>
                    <?php if($db->num_rows() > 1){ ?>
					<button onclick="hapus(<?=$r->id?>)" class="btn btn-xs btn-danger"><i class="fas fa-times"></i> hapus</button>
                    <?php } ?>
				</td>
			</tr>
	<?php	
				$no++;
			}
		}else{
			echo "<tr><td colspan=4 class='text-center text-danger'>Belum ada gudang</td></tr>";
		}
	?>
	</table>

	<?=$this->func->createPagination($rows,$page,$perpage,"loadTesti");?>
</div>