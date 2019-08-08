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
    <div class="export-excel">
        <a style="border:none;padding:0px;float: right;" href="#" id="export" onclick="saveToExcel();"><input class="btn btn-success"  type="button" value="Export to an excel"/> </a> &nbsp; &nbsp; &nbsp;
    </div>
    <section>
        <p>
            <label>
                <span>Party Name:</span>
            </label>
            <select class="party-name">
                <option>Select party name</option>
                <?php
                    foreach($parties as $party) {
                ?><option data-partyname="<?=$party->nPartyName?>" value="<?=$party->nPartyId?>"><?=$party->nPartyName?></option>
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
                        <span><label>Total Weight:</label></span>
                        <span><label class="weight"></label></span>
                    </td>
                    <td>
                        <span><label>Invoice/Challan No:</label></span>
                        <span><label class="invoice-number"></label></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span><label>Instock Weight:</label></span>
                        <span><label class="balance-weight"></label></span>
                    </td>
                    <td>
                        <span><label>Billed Weight:</label></span>
                        <span><label class="billed-weight"></label></span>
                    </td>
                </tr>
            </table>
            <br/>
            <div class="child-coils">
                <div>
                    <legend>Children coil details:</legend>
                </div>
                <div id="contentsholderprocess_1" class="flexcroll" style="width:100%; overflow-x:hidden; overflow-y:auto;">
                    <div id="contentprocess_1" style="width:100%; overflow:hidden;">
                        <div id="DynamicGrid_1">
                            <!-- No Record! -->
                        </div>
                    </div>
                </div>
            </div>
            <br/>
            <div>
                <legend>Bill details:</legend>
            </div>
            <div id="contentsholderprocess" class="flexcroll" style="width:100%; height:300px; overflow-x:hidden; overflow-y:auto;">
                <div id="contentprocess" style="width:100%; overflow:hidden;">
                    <div id="DynamicGrid_2">
                        <!-- No Record! -->
                    </div>
                </div>
            </div>
            <div class="total-bill-weight" style="float: left;width: 53%;text-align: center;">Total Billed Weight&nbsp;<span class="bill-weight">0</span></div>
        </div>
    </section>
</form>

<script>
    var partyId = '';
    var partyname = '';
    var coilNumber = ''
    var previousParty = '';
    $(function() {
        $('.coil-details, .export-excel, .child-coils').hide();
        $('.party-name').on('change', function () {
            $("option:selected", this);
             partyId = this.value;
             partyname = $("option:selected")[0].dataset.partyname;

             if(previousParty != partyId) {
                 $('.coil-details, .export-excel').hide();
                 $('#coilNumber').val('');
                 previousParty = partyId;
             }

            if(partyId) {
                $('.error-info').hide();
                $( "#coilNumber" ).autocomplete({
                    autoFocus: true,
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
                            $('.thickness').text(msg.coil_details.fThickness+' mm');
                            $('.width').text(msg.coil_details.fWidth+' mm');
                            $('.length').text(msg.coil_details.fLength+' mm');
                            $('.weight').text(msg.coil_details.fQuantity+' kgs');
                            $('.invoice-number').text(msg.coil_details.vInvoiceNo);
                            $('.balance-weight').text(msg.coil_details.fpresent+' kgs');

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
                                var billedWeight = 0;
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

                                    billedWeight += parseFloat(item[3]);
                                    partydata.push(thisdata);
                                }
                                $('.bill-weight').text(billedWeight+' kgs');
                                $('.billed-weight').text(billedWeight+' kgs');

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
                        $('.coil-details, .export-excel').show();
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

    function saveToExcel() {
        var tab_text = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        tab_text = tab_text + '<head><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';

        tab_text = tab_text + '<x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet>';
        tab_text = tab_text + '</x:ExcelWorksheets></x:ExcelWorkbook></xml></head><body>';

        tab_text = tab_text + '<table><tr><td colspan="5" style="font-size:60px; font-style:italic; font-family: fantasy;"><h1>ASPEN STEEL PVT LTD</h1><h4>Branch At: Plot no 16E, Bidadi Industrial Area, Phase 2 Sector 1, Bidadi, Ramnagara-562105, Email: aspensteel_unit2@yahoo.com Head Office At: 54/1, Medahalli, Old Madras Road, Bangalore-560049</h4></td></tr></table>';
        tab_text = tab_text + '<table><tr><td colspan="5"><h2>Coil Reconciliation Report</h2></td></tr>' +
            '<tr><td><b>Party name: </b></td><td align="right"><b>'+partyname+'</b></td></tr>' +
            '<tr><td><b>Coil Number: </b></td><td align="right"><b>'+$('#coilNumber').val()+'</b></td></tr> ' +
            '<tr><td><b>Material Description: </b></td><td align="right"><b>'+$('.mat-desc').text()+'</b></td></tr> ' +
            '<tr><td><b>Thickness: </b></td><td align="right"><b>'+$('.thickness').text()+'</b></td></tr> ' +
            '<tr><td><b>Width: </b></td><td align="right"><b>'+$('.width').text()+'</b></td></tr> ' +
            '<tr><td><b>Length: </b></td><td align="right"><b>'+$('.length').text()+'</b></td></tr> ' +
            '<tr><td><b>Weight: </b></td><td align="right"><b>'+$('.weight').text()+'</b></td></tr> ' +
            '<tr><td><b>Balance Weight: </b></td><td align="right"><b>'+$('.balance-weight').text()+'</b></td></tr> ' +
            '<tr><td><b>Billed Weight: </b></td><td align="right"><b>'+$('.billed-weight').text()+'</b></td></tr> ' +
            '<tr><td><b>Invoice Number: </b></td><td align="right"><b>'+$('.invoice-number').text()+'</b></td></tr> ' +
            '</table>';

        if($(".child-coils").is(":visible")) {
            tab_text = tab_text + "<table>";
            tab_text = tab_text + "'<tr><td colspan='5'><h2>Child coil details</h2></td></tr>";
            tab_text = tab_text + "</table><table></table>";


            tab_text = tab_text + "<table>";
            tab_text = tab_text + $('#contentprocess_1').html();
            tab_text = tab_text + '</table>';
        }

        tab_text = tab_text + "<table>";
        tab_text = tab_text + "'<tr><td colspan='5'><h2>Bill details</h2></td></tr>";
        tab_text = tab_text + "</table>";


        tab_text = tab_text + "<table>";
        tab_text = tab_text + $('#contentprocess').html();
        tab_text = tab_text + '</table>';

        var data_type = 'data:application/vnd.ms-excel';

        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
            if (window.navigator.msSaveBlob) {
                var blob = new Blob([tab_text], {
                    type: "application/csv;charset=utf-8;"
                });
                navigator.msSaveBlob(blob, '_Stock_Report.xls');
            }
        } else {
            $('#export').attr('href', data_type + ', ' + encodeURIComponent(tab_text));
            $('#export').attr('download', 'coil_reconciliation_report_'+$('#coilNumber').val()+'.xls');
        }
    }
</script>