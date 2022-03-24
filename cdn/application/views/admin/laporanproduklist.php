<div class="text-center m-t-20 m-b-30">
	<h4><b>LAPORAN PENJUALAN PRODUK</b></h4><br/>
	Periode: <?=$this->func->ubahTgl("d/m/Y",$_POST["tglmulai"])?> sampai <?=$this->func->ubahTgl("d/m/Y",$_POST["tglselesai"])?>
</div>
<div class="table-responsive">
	<table class="table table-condensed table-hover table-bordered">
		<tr>
			<th scope="col">No</th>
			<th scope="col">Produk</th>
			<th scope="col">Jml Produk</th>
			<th scope="col">Total Penjualan</th>
		</tr>
	<?php
		$cari = (isset($_POST["cari"]) AND $_POST["cari"] != "") ? $_POST["cari"] : "";
		$orderby = (isset($data["orderby"]) AND $data["orderby"] != "") ? $data["orderby"] : "id";
        $perpage = 10;
        
        $this->db->where("status = '1' AND tglupdate BETWEEN '".$_POST["tglmulai"]." 00:00:00' AND '".$_POST["tglselesai"]." 23:59:59'");
        $dbs = $this->db->get("pembayaran");
        $in = array("x");
        foreach($dbs->result() as $rs){
            $this->db->where("idbayar",$rs->id);
            $db = $this->db->get("transaksi");
            foreach($db->result() as $re){
                $in[] = $re->id;
            }
        }
        
        $this->db->select("SUM(jumlah) as jml,idproduk,SUM(jumlah*harga) as total");
        $this->db->where_in("idtransaksi",$in);
        $this->db->order_by("jml,total","DESC");
        $this->db->group_by("idproduk");
		$db = $this->db->get("transaksiproduk");
			
		if($db->num_rows() > 0){
			$no = 1;
			$total = 0;
			$jumlah = 0;
			foreach($db->result() as $r){
                $total += $r->total;
                $jumlah += $r->jml;
                $produk = $this->func->getProduk($r->idproduk,"nama");
                $produk = ($produk != null) ? $produk : "<span class='text-danger'><i class='fas fa-times'></i> &nbsp;Produk telah dihapus</span>";
	?>
			<tr>
				<td><?=$no?></td>
				<td><?=$produk?></td>
				<td class='text-right'><?=$this->func->formUang($r->jml)?> pcs</td>
				<td class='text-right'><?=$this->func->formUang($r->total)?></td>
			</tr>
	<?php	
				$no++;
			}
			echo "
			<tr>
				<th class='text-right' colspan=2>TOTAL</th>
				<th class='text-right'>".$this->func->formUang($jumlah)." pcs</th>
				<th class='text-right'>Rp. ".$this->func->formUang($total)."</th>
			</tr>
			";
		}else{
			echo "<tr><td colspan=5 class='text-center text-danger'>Belum ada data</td></tr>";
		}
	?>
	</table>
</div>