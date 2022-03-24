	<!-- breadcrumb -->
	<div class="container">
		<div class="bread-crumb flex-w p-l-25 p-r-15 p-t-30 p-lr-0-lg">
			<a href="<?php echo site_url(); ?>" class="text-primary">
				Home
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>
			<a href="<?php echo site_url("manage/pesanan"); ?>" class="text-primary">
				Pesananku
				<i class="fa fa-angle-right m-l-9 m-r-10" aria-hidden="true"></i>
			</a>
			<span class="text-dark">
				Detail Pesanan
			</span>
		</div>
	</div>


	<!-- Shoping Cart -->
	<form class="p-b-85">
    <div class="container p-t-50 p-b-50">
		<?php
			$this->db->where("usrid",$_SESSION["usrid"]);
			$this->db->where("orderid",$this->func->clean($_GET["orderid"]));
			$dbs = $this->db->get("transaksi");

			if($dbs->num_rows() > 0){
				foreach($dbs->result() as $transaksi){
					$bayar = $this->func->getBayar($transaksi->idbayar,"semua");
					
					$this->db->where("idtransaksi",$transaksi->id);
					$db = $this->db->get("transaksiproduk");
					$po = 0;
					$pohari = date("Y-m-d");
					foreach($db->result() as $r){
						$produk = $this->func->getProduk($r->idproduk,"semua");
						$po = ($produk->preorder > 0) ? 1 : 0;
						$pohari = ($produk->preorder > 0) ? $produk->pohari : $pohari;
					}
		?>
		<div class="row m-lr-0">
			<div class="col-md-7 m-b-30">
				<h4 class="text-primary font-bold p-b-20">
					Order ID <span class="text-success">#<?php echo $transaksi->orderid; ?></span>
				</h4>
				<div class="section p-lr-24 p-tb-20 m-lr-0-xl p-lr-15-sm">
					<div class="row m-b-12">
						<div class="col-md-6 p-b-10 p-t-10">
							<p class="m-b-10">
							Waktu Pemesanan:<br/>
							<i class="font-medium"><?php echo $this->func->ubahTgl("d M Y H:i",$transaksi->tgl); ?> WIB</i>
							</p>
							<p class="">
							Waktu Pembayaran:<br/>
							<i class="font-medium"><?php echo $this->func->ubahTgl("d M Y H:i",$bayar->tgl); ?> WIB</i>
							</p>
							<?php if($po > 0){ ?>
							<p class="text-warning">
							Pesanan akan siap pada:<br/>
							<i class="font-medium"><?php echo $this->func->ubahTgl("d M Y",$pohari); ?> WIB</i>
							</p>
							<?php } ?>
						</div>
						<div class="col-md-6">
							<?php if($transaksi->status == 0){ ?>
								<!-- Belum Dibayar -->
								<p class="bg-warning m-b-10 status-pesanan">Belum Dibayar</p>
								<p class="m-b-5">segera lakukan pembayaran maks. 1x24jam untuk menghindari pembatalan otomatis.</p>
							<?php }elseif($transaksi->status == 2 AND $transaksi->resi != ""){ ?>
							<!-- Dalam Pengiriman -->
							<p class="bg-primary m-b-10 status-pesanan">Sedang Dikirim</p>
								<p class="m-b-5">pesanan Anda sudah dalam perjalanan, untuk melihat proses pengiriman silahkan cek info dibawah.</p>
							<?php }elseif($transaksi->status == 1){ ?>
							<!-- Sedang Dikemas -->
							<p class="bg-primary m-b-10 status-pesanan">Sedang Dikemas</p>
								<p class="m-b-5">pesanan sedang dikemas oleh admin dan akan segera dikirim.</p>
							<?php }elseif($transaksi->status == 3){ ?>
							<!-- Selesai -->
							<p class="bg-success m-b-10 status-pesanan">Telah Diterima</p>
								<p class="m-b-5">pesanan telah diterima oleh pembeli.</p>
							<?php }elseif($transaksi->status == 4){ ?>
							<!-- Selesai -->
							<p class="bg-danger m-b-10 status-pesanan">Pesanan Dibatalkan</p>
								<p class="m-b-5">pesanan dibatalkan karena <?php echo $transaksi->keterangan; ?></p>
							<?php } ?>
						</div>
					</div>
					<a href="<?=site_url("manage/cetakinvoice?id=".$transaksi->id)?>" class="btn btn-block btn-success"><i class="fas fa-print"></i> &nbsp;C E T A K &nbsp; I N V O I C E</a>
				</div>
				<h4 class="text-primary font-bold p-t-30 p-b-20">
					Produk Pesanan
				</h4>
				<div class="produk">
					<?php
						$total = 0;
						$totalqty = 0;
						foreach($db->result() as $res){
							//$total += $res->harga * $res->jumlah;
							$total += ($r->diskon+$r->harga)*$r->jumlah;
							$berat = !empty($prod) ? $prod->berat*$r->jumlah : 0;
							$totalqty += $r->jumlah;
							$produk = $this->func->getProduk($res->idproduk,"semua");
							$variasee = $this->func->getVariasi($res->variasi,"semua");
							$variasi = ($res->variasi != 0 AND isset($variasee->warna)) ? $this->func->getWarna($variasee->warna,"nama")." ".$produk->subvariasi." ".$this->func->getSize($variasee->size,"nama") : "";
							$variasi = ($res->variasi != 0 AND isset($variasee->warna)) ? "<small class='text-primary'>".$produk->variasi.": ".$variasi."</small>" : "";
					?>
						<div class="p-b-20">
							<div class="row produk-item m-lr-0">
								<div class="col-4 col-md-3">
									<div class="img" style="background-image:url('<?php echo $this->func->getFoto($res->idproduk,"utama"); ?>')" alt="IMG"></div>
								</div>
								<div class="col-8 col-md-9">
									<p class="font-medium"><?php echo $produk->nama; ?></p>
									<?php echo $variasi; ?>
									<p>Rp <?php echo $this->func->formUang($res->harga); ?> <span class="fs-14">x <?php echo $res->jumlah; ?></span></p>
								</div>
							</div>
						</div>
					<?php
						}
						$beratkg = $transaksi->berat/1000;
						$beratkg = round($beratkg,2,PHP_ROUND_HALF_UP);
					?>
				</div>
			</div>
			<div class="col-md-5 m-b-30">
				<h4 class="text-primary font-bold p-b-20">
					Rincian Pembayaran
				</h4>
				<div class="section p-lr-24 p-tb-30 m-lr-0-xl p-lr-15-sm m-b-30">
					<div class="row p-tb-8">
						<div class="col-6">TOTAL HARGA<br/>(<?=$totalqty?> BARANG)</div>
						<div class="col-6 font-medium text-right">Rp <?=$this->func->formUang($total)?></div>
					</div>
					<hr/>
					<div class="row p-tb-8">
						<div class="col-6">Total Ongkir (<?=$beratkg?>kg)</div>
						<div class="col-6 font-medium text-right">Rp <?=$this->func->formUang($transaksi->ongkir)?></div>
					</div>
					<div class="row p-tb-8 m-b-12">
						<div class="col-6 text-danger">Diskon</div>
						<div class="col-6 font-medium text-right text-danger">-Rp <?=$this->func->formUang($bayar->diskon)?></div>
					</div>
					<hr/>
					<div class="row p-tb-8">
						<div class="col-6 font-medium text-primary">Grand Total</div>
						<div class="col-6 font-bold text-right text-primary">Rp <?=$this->func->formUang($total+$transaksi->biaya_cod+$transaksi->ongkir-$bayar->diskon)?></div>
					</div>
				</div>
				<h4 class="text-primary font-bold p-b-20">
					Informasi Pengiriman
					<?php
						$alamat = $this->func->getAlamat($transaksi->alamat,"semua");
						$kec = $this->func->getKec($alamat->idkec,"semua");
						$kab = $this->func->getKab($kec->idkab,"nama");
					?>
				</h4>
				<div class="section p-lr-24 p-tb-30 m-lr-0-xl p-lr-15-sm">
					<div class="p-b-14">
						<div class="row p-tb-10">
							<div class="col-md-6">
								<h5 class="p-b-10">KURIR & PAKET</h5>
								<p>
									<?php
										echo "<div class='text-warning fs-18 font-regular font-bold'>".strtoupper($this->func->getKurir($transaksi->kurir,"nama"))."<br/>".strtoupper($this->func->getPaket($transaksi->paket,"nama"))."</div>";
									?>
								</p>
							</div>
							
						</div>
						<hr/>
						<div class="row p-t-20">
							<div class="col-md-6">
								<h5 class="text-black p-b-10">Nama Penerima</h5>
								<p><?php echo strtoupper(strtolower($alamat->nama)); ?></p>
							</div>
							<div class="col-md-6">
								<h5 class="text-black p-b-10">No Telepon</h5>
								<p><?php echo $alamat->nohp; ?></p>
							</div>
						</div>
						<div class="row p-t-20">
							<div class="col-md-12">
								<h5 class="text-black p-b-10">Alamat Pengiriman</h5>
								<p>
									<?php echo strtoupper(strtolower($alamat->alamat)); ?><br>
									<?php echo $kec->nama.", ".$kab; ?><br>
									Kodepos <?php echo $alamat->kodepos; ?>
								</p>
							</div>
						</div>
					</div>
					<?php if($transaksi->resi != "" AND $transaksi->kurir != "cod" AND $transaksi->kurir != "toko"){ ?>
					<div class="m-t-30"></div>
					<a href="<?php echo site_url("manage/lacakpaket/".$transaksi->orderid); ?>" class="btn btn-warning btn-lg btn-block"><i class="fas fa-shipping-fast"></i> &nbsp;<b>CEK STATUS PENGIRIMAN</b></a>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
				}
			}else{
		?>
		<div class="m-b-30 m-lr-auto">
			<div class="section p-lr-24 p-tb-30">
				<div class="text-danger fs-20 text-center">
					Pesanan yang Anda cari tidak ditemukan.
				</div>
			</div>
		</div>
		<?php
			}
		?>
	</div>
	</form>
