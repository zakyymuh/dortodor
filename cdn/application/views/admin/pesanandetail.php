<?php
	$this->db->where("id",intval($_GET["theid"]));
	$db = $this->db->get("transaksi");
	
	foreach($db->result() as $rs){
		$usr = $this->func->getProfil($rs->usrid,"semua","usrid");
		$alamat = $this->func->getAlamat($rs->alamat,"semua");
		$kurir = strtoupper($this->func->getKurir($rs->kurir,"nama"))." - ".strtoupper($this->func->getPaket($rs->paket,"nama"));
		$cod = ($rs->cod == 1) ? "<br/><span class='badge badge-warning' style='font-weight:normal'>Bayar Ditempat (COD)</span>" : "";
		$cod .= ($rs->dropship != "") ? "<br/><span class='badge badge-info' style='font-weight:normal'>d</span>" : "";
		$cod .= ($rs->digital == 1) ? "<br/><span class='badge badge-primary' style='font-weight:normal'><i class='fas fa-cloud'></i> Produk Digital</span>" : "";
?>
	<div class="m-b-30">
		<div class="m-b-20">
			<b>Pembeli</b>
			<div class="text-primary m-t-4"><?=$usr->nama."<br/>".$usr->nohp?></div>
		</div>
		<div class="m-b-20">
			<b>Tanggal Pesanan</b>
			<div class="text-primary m-t-4"><?=$this->func->ubahTgl("d M Y H:i",$rs->tgl)?></div>
			<?=$cod?>
		</div>
		<?php if($rs->dropship != ""){ ?>
			<div class="m-b-20">
				<b>Informasi Pengirim</b>
				<div class="w-full">
					<div class="text-primary m-t-8">
						<?=ucwords($rs->dropship." (".$rs->dropshipnomer.")<br/>".$rs->dropshipalamat)?>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php if($rs->digital != 1){ ?>
			<div class="m-b-20">
				<b>Informasi Penerima</b>
				<div class="w-full">
					<div class="text-primary m-t-8">
						<?=ucwords($alamat->nama." (".$alamat->nohp.")<br/>".$alamat->alamat)?>
					</div>
				</div>
			</div>
			<div class="m-b-20">
				<b>Metode Pengiriman</b>
				<div class="w-full">
					<div class="text-primary m-t-8">
						<?=$kurir?>
					</div>
				</div>
			</div>
			
		<?php } ?>
	</div>
	<div class="m-b-12"><b>PRODUK PESANAN</b></div>
<?php
		$this->db->where("idtransaksi",intval($_GET["theid"]));
		$db = $this->db->get("transaksiproduk");
		
		foreach($db->result() as $r){
			$produk = $this->func->getProduk($r->idproduk,"semua");
			if(is_object($produk)){
				$nama = $produk->nama;
				$variasi = $r->variasi != 0 ? $this->func->getVariasi($r->variasi,"semua","id") : "";
				$variasi = $r->variasi != 0 ? $this->func->getVariasiWarna($variasi->warna,"nama")." ".$produk->subvariasi." ".$this->func->getVariasiSize($variasi->size,"nama") : "";
			}else{
				$nama = "Produk telah dihapus";
				$variasi = "";
			}
			$preo = ($produk->preorder > 0) ? "<i class='fas fa-history'></i> Pre Order: <b>".$produk->pohari."</b> hari<br/>" : "";
			$preo .= ($produk->preorder > 0) ? "<b class='text-danger'>".$this->func->ubahTgl("D, d M Y",date("Y-m-d H:i:s",strtotime($rs->tglupdate . "+".$produk->pohari." days")))."</b>" : $preo;
	?>
	<div class="m-b-12" style="border:1px solid #ccc;border-radius:8px;padding:12px;">
		<div class="row">
			<div class="col-4 col-md-3">
				<img class="col-12" src="<?=$this->func->getFoto($r->idproduk,"utama")?>" />
			</div>
			<div class="col-8 col-md-8 row">
				<div class="col-8 col-md-6">
					<b><?=$nama?></b><br/>
					<small><?=$variasi?></small><br/>
					<div class="text-warning"><?=$preo?></div>
				</div>
				<div class="col-4 col-md-6">
					<?=$r->jumlah." x Rp ".$this->func->formUang($r->harga)?>
				</div>
			</div>
		</div>
	</div>
<?php
		}
	}
?>