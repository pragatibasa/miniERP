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

select.vehiclenumber {
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

.total-bill-weight {
  float: left;
  width: 39%;
  text-align: center;
}

.sum-allocated-weight {
  float: right;
  width: 35%;
  text-align: left;
}

.total {
  display: none;
}

.submit-container {
  clear: both;
  display: none;
}

.weight-info {
  opacity: 0.85;
}
</style>

<form class="weigh-updation">
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
        <span>Weigh Bridge Name:</span>
      </label>
      <input name="weighBridgeName" type="text" />
    </p>
    <p>
      <label for="card">
        <span>Weighment Slip No.:</span>
      </label>
      <input name="slipNo" type="text" />
    </p>
    <p>
      <label for="card">
        <span>Loaded Vehicle Weight:</span>
      </label>
      <input name="loaded-weight" class="loaded-weight" type="text" />
    </p>
    <p>
      <label for="card">
        <span>Empty Vehicle Weight:</span>
      </label>
      <input name="empty-weight" class="empty-weight" type="text" />
    </p>
    <p>
      <label for="card">
        <span>Net weight in kgs:</span>
      </label>
      <input name="net-weight" class="net-weight" type="text" /> <span class="weight-info">Note : If weighed on weigh scale then key in weight directly here</span>
    </p>
  </section>
  <input type="button" class="btn btn-success" onclick="allocateWeight();" value="Allocate Weight">
  </br></br>
  <section>
  <div id="contentsholderprocess" class="flexcroll" style="width:100%; height:300px; overflow-x:hidden; overflow-y:auto;">
    <div id="contentprocess" style="width:100%; overflow:hidden;">
      <div id="DynamicGrid_2">
        <!-- No Record! -->
      </div>
    </div>
  </div>
</section>
<div class="total">
  <div class="total-bill-weight">Total Billed Weight&nbsp;<span class="bill-weight">0</span></div>
  <div class="sum-allocated-weight">Total Allocated Weight&nbsp;<span class="allocated-weight">0</span></div>
</div>
</br>
</br>
</br>
</br>
</br>
</br>
<div class="submit-container">
  <input class="btn btn-success" id="save-weighment-outward" type="button" value="Save Weighment Details" onclick="saveWeightmentDetails();">
</div>
</form>
<script>
$(function() {
  $( "#date" ).picker({
    onSelect: function(datetext, inst) {
      $.ajax({
          type: "POST",
          url: "<?php echo fuel_url('weigh_updation/getOutwardVehiclesWithDate');?>",
          data: "date="+datetext,
          dataType: "json"
          }).done(function( msg ) {
            if(msg.length) {
              var strSelect = '';
              for (var i = 0; i < msg.length; i++) {
                strSelect += "<option>"+msg[i].vehiclenumber+"</option>";
              }
            }
            $('.vehiclenumber').html(strSelect);
          });
    }
  });

  $('.loaded-weight, .empty-weight').blur(function () {
    updateNetWeight();
  });
});

function updateNetWeight() {
  var loadedWeight = parseInt($('.loaded-weight').val());
  var emptyWeight = parseInt($('.empty-weight').val());


  if(loadedWeight && emptyWeight) {
      $('.net-weight').val(loadedWeight - emptyWeight);
  } else {
    $('.net-weight').val('');
  }
}

function allocateWeight() {
  $('#DynamicGrid_2').hide();
  var date=$('#date').val();
  var vehiclenumber=$('.vehiclenumber').val();
  var loading = '<div id="DynamicGridLoading_2"><img src="<?=img_path() ?>loading.gif" /><span> Loading Processing Details... </span></div>';
    $("#contentprocess").empty();
    $('#contentprocess').html(loading);
    $.ajax({
        type: "POST",
        url: "<?php echo fuel_url('weigh_updation/allocate_weight');?>",
        data: "date="+date+"&vehiclenumber="+vehiclenumber,
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
                  thisdata[''] = i+1;
                  thisdata["Bill Number"] = item.billnumber;
                  thisdata["Partyname"] = item.partyname;
                  thisdata["Bill Weight"] = item.billweight*1000+'<input type="hidden" name="billWeight[]" value="'+item.billweight*1000+'"/>';

                  var selectcoil = '<input type="checkbox" data-bill-weight="'+item.billweight+'" name="billnumbers[]" class="checkbundle" id="check_'+item.billnumber+'" value="'+item.billnumber+'" onClick="enableWeights('+item.billnumber+')" />';
                  thisdata["select"] = selectcoil;

                  thisdata["Material Weight"] = '<input class="'+item.billnumber+' material_weight" disabled type="text" size="5" name="material_weight[]" onblur="calculateAllocatedWeights('+item.billnumber+')" />';
                  thisdata["Packing Material Weight"] = '<input class="'+item.billnumber+' packing_weight" disabled type="text" size="5" name="packing_weight[]" onblur="calculateAllocatedWeights('+item.billnumber+')" />';
                  thisdata["Total allocated Weight"] = '<span class="total-allocated-weight '+item.billnumber+'"></span><input type="hidden" name="totAllocatedWeight[]" class="total-allocated-weight '+item.billnumber+'" value=""/>';
                  thisdata["Difference Weight"] = '<span class="difference-weight '+item.billnumber+'"></span><input type="hidden" name="differenceWeight[]" class="difference-weight '+item.billnumber+'" value=""/>';

                  partydata.push(thisdata);

                  $('.submit-container').show();
                }
                if (partydata.length) {
                // If there are files
                  $('#DynamicGrid_2').hide();
                  $('#DynamicGridLoading_2').hide();
                  $('#contentprocess').html(CreateTableViewX(partydata, "lightPro", true));
                  var lcScrollbar = $('#contentsholderprocess');
                  fleXenv.updateScrollBars(lcScrollbar);
                  $('.total').show();
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
}

function enableWeights(billNumber) {
  if(this.document.activeElement.checked) {
    $('.'+billNumber).attr('disabled', false);
    $('.bill-weight').text(parseInt($('.bill-weight').text())+parseInt($('#check_'+billNumber).data('billWeight')*1000)+' kgs');
  } else {
    $('.'+billNumber).attr('disabled', true);
    $('.allocated-weight').text(parseInt($('.allocated-weight').text())-parseInt($('.total-allocated-weight.'+billNumber).text())+' kgs');
    $('.total-allocated-weight.'+billNumber).text('');
    $('.difference-weight.'+billNumber).text('');
    $('.'+billNumber).val('');
    $('.bill-weight').text(parseInt($('.bill-weight').text())-parseInt($('#check_'+billNumber).data('billWeight')*1000)+' kgs');
  }
}

function calculateAllocatedWeights(billNumber) {
  var packing_weight = parseInt($('.packing_weight.'+billNumber).val());
  var material_weight = parseInt($('.material_weight.'+billNumber).val());

  if(packing_weight && material_weight) {
    var totAllocatedWeight = material_weight + packing_weight;
    var differenceWeight = (parseInt($('#check_'+billNumber).data('billWeight')*1000)) - parseInt(totAllocatedWeight);

    $('.total-allocated-weight.'+billNumber).text(totAllocatedWeight).val(totAllocatedWeight);
    $('.difference-weight.'+billNumber).text(differenceWeight).val(differenceWeight);

    var sum = 0
    $('input[name="totAllocatedWeight[]"]').each(function(index) {
        if($(this).val()) {
            sum += parseInt($(this).val());
        }
    });

    $('.allocated-weight').text(sum+' kgs');
  }
}

function saveWeightmentDetails() {
    $('#save-weighment-outward').attr('disabled','disabled');
    var data = $('form').serialize();
    $.ajax({
        type: "POST",
        url: "<?php echo fuel_url('weigh_updation/saveOutwardWeightment');?>",
        data: data,
    }).done(function( msg ) {
      if(msg == 'success') {
        alert('Weighment has been successfully updated. Please click to update a new weighment');
        window.location="<?php echo fuel_url('weigh_updation');?>";
      } else {
          $('#save-weighment-outward').attr('disabled','false');
      }
  });
}
</script>
