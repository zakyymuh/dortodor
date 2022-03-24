<?php
    $pre = $this->func->getPreBayar($_SESSION["prebayar"],"semua");
    $set = $this->func->globalset("semua");
    $saldo = $this->func->getSaldo($_SESSION["usrid"],"saldo","usrid");
    $biaya_cod = ($set->biaya_cod > 100) ? $set->biaya_cod : $pre->total*(floatval($set->biaya_cod)/100);
    if($pre->digital == 0){
        $alamat = $this->func->getAlamat($pre->alamat,"semua");
        $kec = $this->func->getKec($alamat->idkec,"semua");
        $kab = $this->func->getKab($kec->idkab,"semua");
        $prov = $this->func->getProv($kab->idprov,"nama");
        $alamats = $alamat->alamat."<br/>".strtoupper(strtolower($kec->nama.", ".$kab->tipe." ".$kab->nama.", ".$prov));
    }else{
        $biaya_cod = 0;
    }
?>
<div class="row">
    <div class="col-md-8 m-b-30">
        <?php if($pre->digital == 0){ ?>
        <div class="section p-all-24 m-b-20">
            <div class="fs-20 font-bold m-b-20">Informasi Pengiriman</div>
            <div class="p-lr-12 p-tb-8 bg-foot rounded m-b-20">
                <div class="font-medium"><?=$alamat->nama." (".$alamat->nohp.")"?></div>
                <i><?=$alamats." - ".$alamat->kodepos?></i>
            </div>
            <div class="m-b-12 font-medium">Kurir Pengiriman</div>
            <div class="p-lr-12 p-tb-8 bg-foot rounded d-inline-block">
                <div class="font-medium"><?=$this->func->getKurir($pre->kurir,"nama")." - ".$this->func->getPaket($pre->paket,"nama")?></div>
            </div>
        </div>
        <?php } ?>
        <div class="section p-all-24">
            <div class="fs-20 font-bold m-b-20">Voucher Diskon</div>
            <div class="input-group m-t-10 col-md-6 p-lr-0">
                <input type="text" class="form-control" placeholder="Masukkan kode voucher" id="kodevoucher" name="kodevoucher" />
                <input type="hidden" name="diskon" id="diskon" value='0' />
                <div class="input-group-append">
                <button class="btn btn-primary" type="button" onclick="cekVoucher()">Cek Voucher</button>
                </div>
            </div>
            <div class="m-t-10 m-b-10">
                <div class="vouchergagal text-danger" style="display:none;">Maaf, Voucher sudah tidak berlaku</div>
                <div class="vouchersukses text-success" style="display:none;">Selamat, Voucher berhasil dipakai dan nikmati potongannya</div>
            </div>
            <div class="voucher p-t-10 row">
                <?php
                $this->db->where("mulai <=",date("Y-m-d"));
                $this->db->where("selesai >=",date("Y-m-d"));
                $this->db->where("public",1);
                $this->db->where("digital",$pre->digital);
                $voc = $this->db->get("voucher");
                foreach($voc->result() as $v){
                    //echo $v->potongan;
                    $pot = $v->tipe == 2 ? $v->potongan/1000 : $v->potongan;
                    $pot = $this->func->formUang($pot);
                    $potongan = $v->tipe == 2 ? "<div class=\"font-bold text-center\"><span class='fs-12'>Rp</span><span class='fs-24'>".$pot."K</span></div>" : '<div class="font-bold fs-28 text-center">'.$pot.'%</div>';
                    $jenis = $v->jenis == 1 ? "Harga" : "Ongkir";
                ?>
                    <div class="col-md-6">
                        <div class="voucher-item m-b-14 cursor-pointer bg-warning" data-kode="<?=$v->kode?>">
                            <i class="fas fa-ellipsis-v faleft"></i>
                            <i class="fas fa-ellipsis-v faright"></i>
                            <div class="row" style="align-items:center;">
                                <div class="col-md-4 col-5">
                                    <div class="m-b--4 t-center fs-10 font-medium">Diskon <?=$jenis?> </div>
                                    <?=$potongan?>
                                    <div class="line"></div>
                                </div>
                                <div class="col-md-8 col-7">
                                    <div class="elip font-bold fs-18"><?=$v->kode?></div>
                                    <div class="elip"><?=$v->nama?></div>
                                    <div class="elip"><small>minimal Rp. <?=$this->func->formUang($v->potonganmin)?></small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="section p-all-24 m-b-30">
            <form id="cekout">
                <input type="hidden" id="saldoval" value="<?=$saldo;?>" />
                <input type="hidden" id="diskon" value="0" />
                <input type="hidden" id="subtotal" value="<?php echo $pre->total; ?>" />
                <input type="hidden" id="ongkir" value="<?php echo $pre->ongkir; ?>" />
                <input type="hidden" name="saldo" id="saldopotong" value="0" />
                <input type="hidden" name="metode" id="metode" value="1" />
                <input type="hidden" name="total" id="total" value="<?php echo $pre->total+$pre->ongkir; ?>" />
                <input type="hidden" name="biaya_cod" id="biayacod" value="<?php echo $biaya_cod; ?>" />
                <input type="hidden" name="metode_bayar" id="metode_bayar" value="0" />
            </form>
            <div class="fs-20 font-bold m-b-20">Informasi Pembayaran</div>
            <!-- Subtotal -->
            <div class="row">
                <div class="col-6">
                    <p>Subtotal</p>
                </div>
                <div class="col-6">
                    <p style="text-align: right">Rp <span id="subtotalbayar"><?php echo $this->func->formUang($pre->total); ?></p>
                </div>
            </div>
            <?php if($pre->digital == 0){ ?>
            <div class="row">
                <div class="col-6">
                    <p>Ongkos Kirim</p>
                </div>
                <div class="col-6">
                    <p style="text-align: right">Rp <?php echo $this->func->formUang($pre->ongkir); ?></p>
                </div>
            </div>
          
            <?php } ?>
            <div class="row">
                <div class="col-6">
                    <p>Diskon</p>
                </div>
                <div class="col-6">
                    <p style="text-align: right">- Rp <span id="diskonshow">0</span></p>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-4">
                    <h5>Total</h5>
                </div>
                <div class="col-8 font-bold">
                    <h5 style="text-align: right">Rp <span id="totalbayar"><?php echo $this->func->formUang($pre->total+$pre->ongkir); ?></span></h5>
                </div>
            </div>
        </div>
        <div class="section p-all-24">
            <div class="fs-20 font-medium m-b-20">Pilih Metode Pembayaran</div>
            <div class="metode m-b-20">
                <div class="m-b-12 p-all-12 m-lr-0 metodebayar" id="bayarsaldo" data-bayar="cod">
                    <i class="fas fa-check-circle"></i>
                    <b class="fs-16 font-medium text-success"><i class="fas fa-wallet"></i> &nbsp;Bayar dengan Saldo</b><br/>
                    <span class="fs-12">Saldo saat ini Rp. <?=$this->func->formUang($saldo)?></span>
                </div>
                <p class="text-danger text-center fs-12" id="saldokosong" style="display:none">
                    pembayaran menggunakan saldo tidak dapat diaktifkan karena saldo Anda saat ini Rp. 0
                </p>
                <div class="bg-medium p-all-8 text-center fs-12" style="border-radius:8px;display:none" id="saldokurang">
                    saldo Anda saat ini kurang dari total tagihan pembayaran, maka sisanya silahkan lunasi dengan pilihan metode pembayaran dibawah
                    <div class="font-medium p-t-12 fs-14">Sisa Pembayaran:<br/><span class="text-danger font-bold" id="sisabayar">Rp. 0</span></div>
                </div>
            </div>

            <div class="fs-20 font-medium m-b-20 metodelainnya">Metode Pembayaran Lainnya</div>
            <div class="metode m-b-30 metodelainnya">
                <?php
                    $set = $this->func->getSetting("semua");
                    if($set->payment_cod == 1 AND $pre->cod == 1){
                ?>
                    <div class="m-b-12 row m-lr-0 metodebayar methods" id="bayarcod" data-metode="1" data-bayar="cod">
                        <i class="fas fa-check-circle"></i>
                        <div class="col-md-6 col-4 p-lr-0">
                            <div class="bg-logo p-all-4"><img src="<?=base_url("assets/images/cod.png")?>" style="width:100%" /></div>
                        </div>
                        <div class="col-md-6 col-8">
                            COD
                        </div>
                    </div>
                <?php
                    }
                    if($set->payment_transfer == 1){
                ?>
                    <div class="m-b-12 row m-lr-0 metodebayar methods" id="bayartransfer" data-metode="2" data-bayar="transfer">
                        <i class="fas fa-check-circle"></i>
                        <div class="col-md-6 col-4 p-lr-0">
                            <div class="bg-logo p-all-4"><img src="<?=base_url("assets/images/transfer.png")?>" style="width:100%" /></div>
                        </div>
                        <div class="col-md-6 col-8">
                            TRANSFER MANUAL
                        </div>
                    </div>
                <?php
                    }
                    if($set->payment_tripay == 1){
                ?>
                    <div class="m-b-12 row m-lr-0 metodebayar methods" id="bayartripay" data-metode="3" data-bayar="tripay">
                        <i class="fas fa-check-circle"></i>
                        <div class="col-md-6 col-4 p-lr-0">
                            <div class="bg-logo p-all-4"><img src="<?=base_url("assets/images/tripay.png")?>" style="width:100%" /></div>
                        </div>
                        <div class="col-md-6 col-8">
                            <small>VA, Gopay, OVO, Alfamart dll</small>
                        </div>
                    </div>
                <?php
                    }
                    if($set->payment_midtrans == 1){
                ?>
                    <div class="m-b-12 row m-lr-0 metodebayar methods" id="bayarmidtrans" data-metode="4" data-bayar="midtrans">
                        <i class="fas fa-check-circle"></i>
                        <div class="col-md-6 col-4 p-lr-0">
                            <div class="bg-logo p-all-4"><img src="<?=base_url("assets/images/midtrans.png")?>" style="width:100%" /></div>
                        </div>
                        <div class="col-md-6 col-8">
                            <small>VA, Gopay, OVO, Alfamart dll</small>
                        </div>
                    </div>
                <?php
                    }
                ?>
            </div>
            <div class="error text-danger" id="error-bayar">
                <i>Belum dapat menyelesaikan pesanan, silahkan lengkapi alamat dan total beserta ongkos kirim terlebih dahulu.</i><p>
            </div>
            <div class="text-warning" id="proses" style="display:none;">
                <h5><i class="fas fa-compact-disk fa-spin"></i> <i>Memproses pesanan, mohon tunggu sebentar</i></h5><p>
            </div>
            <div class="pembayaran" style="display:none;">
                <a href="javascript:void(0);" onclick="checkoutWA();" class="btn btn-lg btn-success btn-block m-b-12">
                    <i class="fab fa-whatsapp"></i> &nbsp;Checkout Whatsapp
                </a>
                <a href="javascript:void(0);" onclick="checkoutNow();" class="btn btn-lg btn-primary btn-block">
                    <i class="fas fa-chevron-circle-right"></i> &nbsp;Lanjut Pembayaran
                </a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        $(".voucher-item").on("click",function(){
            var kode = $(this).data("kode");
            //confirm(kode);//
            $("#kodevoucher").val(kode);
            setTimeout(cekVoucher(),1000);
        });
        
        $(".methods").on("click",function(){
            var valmet = $("#metode_bayar").val();
            $("#metode_bayar").val($(this).data("metode"));
            $(".methods").removeClass("active");
            $("#bayar"+$(this).data("bayar")).addClass("active");
            showCekout();
            var saldo = parseFloat($("#saldoval").val());
            if($(this).data("metode") == 1){
                $(".codon").show();
                var total = parseInt($("#subtotal").val()) + parseInt($("#ongkir").val()) + parseInt($("#biayacod").val()) - parseFloat($("#diskon").val());
                var minus = total - saldo;
                $("#sisabayar").html("Rp. "+formUang(minus.toString()));
                //$("#total").val(total);
                $("#totalbayar").html(formUang(total.toString()));
            }else{
                if(valmet == 1){
                    var total = parseInt($("#subtotal").val()) + parseInt($("#ongkir").val()) - parseFloat($("#diskon").val());// - parseInt($("#biayacod").val());
                    var minus = total - saldo;
                    //$("#total").val(total);
                    $("#totalbayar").html(formUang(total.toString()));
                    $(".codon").hide();
                    $("#sisabayar").html("Rp. "+formUang(minus.toString()));
                }
            }
        });

        $("#bayarsaldo").click(function(){
            if($(this).hasClass("active") == false){
                var saldo = parseFloat($("#saldoval").val());
                //var total = parseFloat($("#total").val());
                var total = parseInt($("#subtotal").val()) + parseFloat($("#ongkir").val()) - parseFloat($("#diskon").val());
                var minus = total - saldo;

                if(saldo > 0){
                    $("#metode").val(2);
                    $("#saldokosong").hide();
                    $(this).addClass("active");
                    if(saldo >= total){
                        $(this).addClass("active");
                        $("#saldopotong").val(total);
                        $(".metodelainnya").hide();
                        showCekout();
                    }else{
                        //var selisih = total - saldo;
                        $(".metodelainnya").show();
                        $("#saldokurang").show();
                        $("#saldopotong").val(saldo);
                        $("#sisabayar").html("Rp. "+formUang(minus.toString()));
                        hideCekout();
                        //$("#saldo").html("Rp "+formUang(saldo));
                        //$("#transfer").html("Rp "+formUang(selisih));
                    }
                }else{
                    $(this).removeClass("active");
                    $("#metode").val(1);
                    $("#saldopotong").val(0);
                    $("#saldokurang").hide();
                    $("#saldokosong").show();
                    hideCekout();
                    //$("#saldo").html("Rp 0");
                    //$("#transfer").html("Rp "+formUang(total));
                }
            }else{
                $("#metode").val(1);
                $(".metodelainnya").show();
                $("#saldopotong").val(0);
                $(this).removeClass("active");
                $("#saldokurang").hide();
                $("#saldokosong").hide();
                hideCekout();
            }
        });
    });
    
	//CEK VOUCHER
	function cekVoucher(){
		if($("#kodevoucher").val() != ""){
			$.post("<?=site_url("checkout/kupon")?>",{"kode":$("#kodevoucher").val(),"harga":$("#totalharga").val(),[$("#csrf_name").val()]: $("#csrf_token").val(),"ongkir":$("#ongkir").val()},function(msg){
				var data = eval("("+msg+")");
                $(".csrf_token").val(data.token);
                updateToken(data.token);
				if(data.success == true){ 
					var total = parseFloat($("#subtotal").val()) + parseFloat($("#ongkir").val()) - data.diskon;
                    if($("#metode_bayar").val() == 1){
                        total = total + parseFloat($("#biayacod").val());
                    }
					$("#diskon").val(data.diskon);
					$("#diskonshow").html(formUang(data.diskon.toString()));
					$("#totalbayar").html(formUang(total.toString()));
					$(".vouchergagal").hide();
					$(".vouchersukses").show();
				}else{
					$("#diskon").val(0);
					$("#diskonshow").html(0);
					$(".vouchergagal").show();
					$(".vouchersukses").hide();
				}
			});
		}else{
			swal.fire("Masukkan Kode Voucher!","masukkan kode voucher terlebih dahulu lalu klik tombol cek voucher","warning");
		}
	}

    function showCekout(){
        $(".pembayaran").show();
        $("#error-bayar").hide();
        $("#proses").hide();
    }
    function hideCekout(){
        $(".pembayaran").hide();
        $("#error-bayar").show();
        $("#proses").hide();
    }

    function checkoutNow(){
        $(".pembayaran").hide();
        $("#proses").show();

        /*
            $(".idproduks").each(function(){
                var id = $(this).val();
                var nama = $("#namaproduks_"+id).val();
                var kategori = $("#kategoriproduks_"+id).val();
                var total = $("#totalproduks_"+id).val();
                fbq('track', 'Purchase', {content_ids:id,content_type:kategori,content_name:nama,currency: "IDR", value: total});
            });
        */

        $.post("<?php echo site_url("checkout/simpanbayar"); ?>",$("#cekout").serialize(),function(msg){
            var data = eval("("+msg+")");
            if(data.success == true){
                window.location.href = data.url;
                //confirm(data);
            }else{
                swal.fire("Gagal checkout","Terjadi kesalahan saat melakukan checkout, cek kembali metode pembayaran yg Anda pilih","error");
                $(".pembayaran").show();
                $("#proses").hide();
            }
        });
    }
    function checkoutWA(){
        $(".pembayaran").hide();
        $("#proses").show();

        /*
            $(".idproduks").each(function(){
                var id = $(this).val();
                var nama = $("#namaproduks_"+id).val();
                var kategori = $("#kategoriproduks_"+id).val();
                var total = $("#totalproduks_"+id).val();
                fbq('track', 'Purchase', {content_ids:id,content_type:kategori,content_name:nama,currency: "IDR", value: total});
            });
        */

        $.post("<?php echo site_url("checkout/simpanbayar?type=wasap"); ?>",$("#cekout").serialize(),function(msg){
            var data = eval("("+msg+")");
            if(data.success == true){
                window.location.href = "https://wa.me/<?=$this->func->getRandomWasap()?>/?text="+data.text;
            }else{
                swal.fire("Gagal checkout","Terjadi kesalahan saat melakukan checkout, cek kembali metode pembayaran yg Anda pilih","error");
                $(".pembayaran").show();
                $("#proses").hide();
            }
        });
    }
</script>