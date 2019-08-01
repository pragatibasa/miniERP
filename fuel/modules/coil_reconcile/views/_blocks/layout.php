<style>
.party-name {
    padding: 0 6px;
}
    #coilNumber {
        height: auto !important;
        box-sizing: border-box;
        border: 1px solid #999;
        display: inline-block;
        height: 20px;
        padding: 4px 6px;
        margin-bottom: 9px;
        font-size: 14px;
        line-height: 20px;
        color: #555555;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
    }
</style>

<form class="coil_reconcile">
    <section>
        <p>
            <label>
                <span>Party Name:</span>
            </label>
            <select class="party-name">
                <option>Select party name</option>
                <?php
                    foreach($parties as $party) {
                ?><option value="<?=$party->nPartyId?>"><?=$party->nPartyName?></option>
                    <?php }
                ?>
            </select>
        </p>
        <p>
            <label>
                <span>Coil Number:</span>
            </label>
            <input id="coilNumber"><span class="error-info">&nbsp;&nbsp;&nbsp; Please select a party name</span>
        </p>
        <div class="coil-details">
            <legend>Coil Details:</legend>
            <div class="alert alert-info coil-alert hide" role="alert">This coil has been upgraded/snipped. Please check children coils section for more details</div>
            <table width="100%" cellpadding="2" cellspacing="10" border="0">
                <tr>
                    <td>
                        <span><label><?=lang('Material_description')?>:</label></span>
                        <span><label class="mat-desc"></label></span>
                    </td>
                    <td>
                        <span><label><?=lang('thickness_txt')?>:</label></span>
                        <span><label class="thickness"></label></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span><label><?=lang('width_txt')?>:</label></span>
                        <span><label class="width"></label></span>
                    </td>
                    <td>
                        <span><label><?=lang('length_txt')?>:</label></span>
                        <span><label class="length"></label></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span><label><?=lang('weight_txt')?>:</label></span>
                        <span><label class="weight"></label></span>
                    </td>
                    <td>
                        <span><label>Invoice/Challan No:</label></span>
                        <span><label class="invoice-number"></label></span>
                    </td>
                </tr>
            </table>
            <br/>
            <div class="child-coils">
                <div>
                    <legend>Children coil Details:</legend>
                </div>
                <div id="contentsholderprocess_1" class="flexcroll" style="width:100%; height:300px; overflow-x:hidden; overflow-y:auto;">
                    <div id="contentprocess_1" style="width:100%; overflow:hidden;">
                        <div id="DynamicGrid_1">
                            <!-- No Record! -->
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <legend>Bill Details:</legend>
            </div>
            <div id="contentsholderprocess" class="flexcroll" style="width:100%; height:300px; overflow-x:hidden; overflow-y:auto;">
                <div id="contentprocess" style="width:100%; overflow:hidden;">
                    <div id="DynamicGrid_2">
                        <!-- No Record! -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</form>

<script>
    var partyId = '';
    var coilNumber = ''
    $(function() {
        $('.coil-details').hide();
        $('.child-coils').hide();
        $('.party-name').on('change', function () {
            $("option:selected", this);
             partyId = this.value;

            if(partyId) {
                $('.error-info').hide();
                $( "#coilNumber" ).autocomplete({
                    source: "<?php echo fuel_url('coil_reconcile/searchCoilNumber');?>/?partyId="+partyId,
                    minLength: 2,
                    select: function( event, ui ) {
                        coilNumber = ui.item.value;
                        $.ajax({
                            type: "POST",
                            url: "<?php echo fuel_url('coil_reconcile/getCoilReconcileDetails');?>",
                            data: "coilNumber="+coilNumber,
                            dataType: "json"
                        }).done(function( msg ) {
                            $('.mat-desc').text(msg.coil_details.vDescription);
                            $('.thickness').text(msg.coil_details.fThickness);
                            $('.width').text(msg.coil_details.fWidth);
                            $('.length').text(msg.coil_details.fLength);
                            $('.weight').text(msg.coil_details.fQuantity);
                            $('.invoice-number').text(msg.coil_details.vInvoiceNo);
                            if(msg.coil_details.coil_upgrade > 1) {
                                $('.child-coils').show();
                                $('.coil-alert').show();
                                if(!msg.child_details) {
                                    $('#DynamicGrid_1').hide();
                                    $('#DynamicGridLoading_1').hide();
                                    var loading1 = '<div id="error_msg">No Result!</div>';
                                    $('#contentprocess_1').html(loading1);
                                } else{
                                    var partydata = [];
                                    var i = 1;
                                    for (var key in msg.child_details) {
                                        var item = msg.child_details[key];
                                        var thisdata = {};
                                        thisdata['Sl No'] = i++;
                                        thisdata["Coil Number"] = item[0].vIRnumber;
                                        thisdata["Inward Date"] = item[0].dReceivedDate;
                                        thisdata["Material Desc"] = item[0].vDescription;
                                        thisdata["Thickness"] = item[0].fThickness;
                                        thisdata["Width"] = item[0].fWidth;
                                        thisdata["Length"] = item[0].fLength;
                                        thisdata["Weight"] = item[0].fQuantity;
                                        thisdata["Invoice No."] = item[0].vInvoiceNo;
                                        thisdata["Vehicle No."] = item[0].vLorryNo;
                                        thisdata["Status"] = item[0].vStatus;
                                        thisdata["Process"] = item[0].vprocess;

                                        partydata.push(thisdata);
                                    }
                                    if (partydata.length) {
                                        // If there are files
                                        $('#DynamicGrid_1').hide();
                                        $('#DynamicGridLoading_1').hide();
                                        $('#contentprocess_1').html(CreateTableViewX(partydata, "lightPro", true));
                                        var lcScrollbar = $('#contentsholderprocess_1');
                                        fleXenv.updateScrollBars(lcScrollbar);
                                    } else {
                                        $('#DynamicGrid_1').hide();
                                        $('#DynamicGridLoading_1').hide();
                                        var loading1 = '<div id="error_msg">No Result!</div>';
                                        $('#content').html(loading1);
                                        var lfScrollbar = $('#contentsholderprocess_1');
                                        fleXenv.updateScrollBars(lfScrollbar);
                                    }
                                }
                            }

                            if(msg.bill_details.length == 0) {
                                $('#DynamicGrid_2').hide();
                                $('#DynamicGridLoading_2').hide();
                                var loading1 = '<div id="error_msg">No Result!</div>';
                                $('#contentprocess').html(loading1);
                            } else{
                                var partydata = [];
                                var i = 1;
                                for (var key in msg.bill_details) {
                                    var item = msg.bill_details[key];

                                    var thisdata = {};
                                    thisdata['Sl No'] = i++;
                                    thisdata["Bill Number"] = item[0];
                                    thisdata["Date"] = item[1];
                                    thisdata["No of Pcs"] = item[2];
                                    thisdata["Billed Weight in Kgs"] = item[3];
                                    thisdata["Allocated material wt as per weigh bridge"] = item[4];
                                    thisdata["Allocated Packing Wt"] = item[5];
                                    thisdata["Total Allocated Wt"] = item[6];

                                    partydata.push(thisdata);
                                }
                                if (partydata.length) {
                                    // If there are files
                                    $('#DynamicGrid_2').hide();
                                    $('#DynamicGridLoading_2').hide();
                                    $('#contentprocess').html(CreateTableViewX(partydata, "lightPro", true));
                                    var lcScrollbar = $('#contentsholderprocess');
                                    fleXenv.updateScrollBars(lcScrollbar);
                                } else {
                                    $('#DynamicGrid_2').hide();
                                    $('#DynamicGridLoading_2').hide();
                                    var loading1 = '<div id="error_msg">No Result!</div>';
                                    $('#content').html(loading1);
                                    var lfScrollbar = $('#contentsholderprocess');
                                    fleXenv.updateScrollBars(lfScrollbar);
                                }
                            }
                        });
                        $('.coil-details').show();
                    }
                });
            }
        });

        $('#coilNumber').keyup(function() {
            if(!partyId) {
                $('.error-info').show();
            }
        });
    });
</script>