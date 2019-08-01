<style>
.weigh-updation label span {
  display: inline-block;
  width: 150px;
}

.weigh-updation input {
  height: auto !important;
  box-sizing: border-box;
  border: 1px solid #999;
}

select.vehiclenumber, select.weighmentWeight {
  padding: 0px 6px !important;
}

.weigh-updation label {
  position: relative;
}

.weigh-updation label em {
  position: absolute;
  right: 5px;
  top: 20px;
}

#contentprocess input {
  width: auto !important;
  margin: 0;
}

#contentprocess td:nth-child(5), #contentprocess td:nth-child(6), #contentprocess td:nth-child(7) {
  text-align: center;
}
</style>

<form class="weigh-updation">
    <div>
        <a style="border:none;padding:0px;float: right;" href="#" id="export" onclick="saveToExcel();"><input class="btn btn-success"  type="button" value="Export to an excel"/> </a> &nbsp; &nbsp; &nbsp;
<!--        <input class="btn btn-success" style="float: right;" id="save-weighment-outward" type="button" value="Export to an excel" onclick="saveToExcel();">-->
    </div>
  <section>
    <p>
      <label for="card">
        <span>Date:</span>
      </label>
      <input id="date" name="date" type="text"/>
    </p>
    <p>
      <label for="card">
        <span>Vehicle No.:</span>
      </label>
      <select name="vehiclenumber" class="vehiclenumber"></select>
    </p>
    <p>
      <label for="card">
        <span>Weighment Slip Weight:</span>
      </label>
      <select name="weighmentWeight" class="weighmentWeight"></select>
    </p>
  </section>
  <section>
  <div id="contentsholderprocess" class="flexcroll" style="width:100%; height:300px; overflow-x:hidden; overflow-y:auto;">
    <div id="contentprocess" style="width:100%; overflow:hidden;">
      <div id="DynamicGrid_2">
        <!-- No Record! -->
      </div>
    </div>
  </div>
</section>
</form>
<script>
    var reportData;
$(function() {
  $( "#date" ).picker({
    onSelect: function(datetext, inst) {
      $.ajax({
          type: "POST",
          url: "<?php echo fuel_url('vehicle_inward/getOutwardVehiclesWithDate');?>",
          data: "date="+datetext,
          dataType: "json"
          }).done(function( msg ) {
            if(msg.length) {
              var strSelect = "<option value=''>Select Vehicle Number</option>";
              for (var i = 0; i < msg.length; i++) {
                strSelect += "<option value='"+msg[i].vehiclenumber+"'>"+msg[i].vehiclenumber+"</option>";
              }
            }
            $('.vehiclenumber').html(strSelect);
          });
    }
  });

  $('.vehiclenumber').on('change', function (e) {
    $("option:selected", this);
    var vehicleNumber = this.value;
    if(vehicleNumber) {
        $.ajax({
            type: "POST",
            url: "<?php echo fuel_url('vehicle_inward/fetchWeighmentsWithDateAndVehicleNumber');?>",
            data: "vehicleNumber="+vehicleNumber+"&date="+$('#date').val(),
            dataType: "json"
        }).done(function( msg ) {
            if(msg.length) {
                var strSelect = "<option value=''>Select Weighment slip weight</option>";
                for (var i = 0; i < msg.length; i++) {
                    strSelect += "<option value='"+msg[i].weight+"'>"+msg[i].weight+"</option>";
                }
            }
            $('.weighmentWeight').html(strSelect);
        });
    }
  });

  $('.weighmentWeight').on('change', function (e) {
        $("option:selected", this);
        var selectWeight = this.value;
        if(selectWeight) {
            displayWeighments(selectWeight);
        }
  });
});

function displayWeighments() {
  $('#DynamicGrid_2').hide();
  var date = $('#date').val();
  var vehiclenumber = $('.vehiclenumber').val();
  var weight = $('.weighmentWeight').val();

  var loading = '<div id="DynamicGridLoading_2"><img src="<?=img_path() ?>loading.gif" /><span> Loading Processing Details... </span></div>';
    $("#contentprocess").empty();
    $('#contentprocess').html(loading);
    $.ajax({
        type: "POST",
        url: "<?php echo fuel_url('vehicle_inward/displayWeightmentDetails');?>",
        data: "date="+date+"&vehiclenumber="+vehiclenumber+"&weight="+weight,
        dataType: "json"
        }).done(function( msg ) {
          if(msg.length == 0) {
            $('#DynamicGrid_2').hide();
            $('#DynamicGridLoading_2').hide();
            var loading1 = '<div id="error_msg">No Result!</div>';
            $('#contentprocess').html(loading1);
          } else{
                var partydata = [];
                for (var i = 0; i < msg.length; i++) {
                  var item = msg[i];
                  // $('#txtRateTotal').val(item.rate);
                  var thisdata = {};
                  thisdata['Sl No'] = i+1;
                  thisdata["Coil Number"] = item.vIRnumber;
                  thisdata["Partyname"] = item.partyname;
                  thisdata["Bill Number"] = item.billnumber;
                  thisdata["Date"] = item.dBillDate;
                  thisdata["Item Description"] = item.vDescription;
                  thisdata["Thickness"] = item.fThickness;
                  thisdata["Width"] = item.fWidth;
                  thisdata["Length"] = item.fLength;
                  thisdata["No of Pcs"] = item.ntotalpcs;
                  thisdata["Billed Weight in Kgs"] = item.billWeight;
                  thisdata["Allocated material wt as per weigh bridge"] = item.materialWeight;
                  thisdata["Allocated Packing Wt"] = item.packagingWeight;
                  thisdata["Total Allocated Wt"] = item.totalAllocatedWeight;

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

    $.ajax({
        type: "POST",
        url: "<?php echo fuel_url('vehicle_inward/getWeighmentDetails');?>",
        data: "date="+$('#date').val()+"&vehiclenumber="+$('.vehiclenumber').val()+"&weight="+$('.weighmentWeight').val(),
        dataType: "json"
    }).done(function( msg ) {
        reportData = msg;
    });
}

function saveToExcel() {
    var tab_text = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    tab_text = tab_text + '<head><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';

    tab_text = tab_text + '<x:WorksheetOptions><x:Panes></x:Panes></x:WorksheetOptions></x:ExcelWorksheet>';
    tab_text = tab_text + '</x:ExcelWorksheets></x:ExcelWorkbook></xml></head><body>';

    tab_text = tab_text + '<table><tr><td style="font-size:60px; font-style:italic; font-family:fantasy;" colspan="7" align="center"><h1>Vehicle Dispatch Report</h1></td></tr>';

    tab_text = tab_text + '<tr></tr><tr><td><b>Vehicle Despatch Date: </b></td><td><b>'+$('#date').val()+'</b></td></tr>' +
        '<tr><td><b>Vehicle Number: </b></td><td><b>'+$('.vehiclenumber').val()+'</b></td></tr> ' +
        '<tr><td><b>Weigh Bridge Name: </b></td><td align="right"><b>'+reportData[0].bridgeName+'</b></td></tr> ' +
        '<tr><td><b>Weighment Slip No: </b></td><td><b>'+reportData[0].slipNo+'</b></td></tr> ' +
        '<tr><td><b>Net Weight: </b></td><td><b>'+reportData[0].netWeight+'</b></td></tr> ' +
        '<tr><td><b>Report created date: </b></td><td><b>'+reportData[0].createdDate+'</b></td></tr> ' +
        '<tr></tr><tr></tr>'+
        '</table>';

    tab_text = tab_text + "<table border='1px'>";
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
        $('#export').attr('download', 'vehicle_despatch_report_'+$('#date').val()+'.xls');
    }
}
</script>
