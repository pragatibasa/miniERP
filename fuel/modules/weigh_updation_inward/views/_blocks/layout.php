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
  <div class="total-bill-weight">Total Inward Weight&nbsp;<span class="bill-weight">0</span></div>
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
          url: "<?php echo fuel_url('weigh_updation_inward/getInwardVehiclesWithDate');?>",
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
        url: "<?php echo fuel_url('weigh_updation_inward/allocate_weight');?>",
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
                  var thisdata = {};
                  thisdata[''] = i+1;
                  thisdata["Coil Number"] = item.coilNumber;
                  thisdata["Partyname"] = item.partyname;
                  thisdata["Weight in kgs"] = item.fQuantity+'<input type="hidden" name="coilWeight[]" value="'+item.fQuantity+'"/>';

                  var selectcoil = '<input type="checkbox" data-coil-weight="'+item.fQuantity+'" name="coilNumbers[]" class="checkbundle" id="check_'+item.coilNumber+'" value="'+item.coilNumber+'" onClick="enableWeights(this)" />';
                  thisdata["select"] = selectcoil;

                  thisdata["Material Weight"] = '<input class="material_weight" disabled type="text" size="5" data-number="'+item.coilNumber+'" name="material_weight[]" onblur="calculateAllocatedWeights(this)" />';
                  thisdata["Packing Material Weight"] = '<input class="packing_weight" disabled type="text" data-packing="'+item.coilNumber+'" size="5" name="packing_weight[]" onblur="calculateAllocatedWeights(this)" />';
                  thisdata["Total allocated Weight"] = '<span data-totalAllocatedSpan="'+item.coilNumber+'" class="total-allocated-weight"></span><input data-totalAllocated="'+item.coilNumber+'" type="hidden" name="totAllocatedWeight[]" class="total-allocated-weight" value=""/>';
                  thisdata["Difference Weight"] = '<span data-differenceSpan="'+item.coilNumber+'" class="difference-weight"></span><input type="hidden" data-difference="'+item.coilNumber+'" name="differenceWeight[]" class="difference-weight" value=""/>';

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

function enableWeights($this) {
    var coilNumber = $this.value;
    if(this.document.activeElement.checked) {
        $('input[data-number="'+coilNumber+'"]').attr('disabled', false);
        $('input[data-packing="'+coilNumber+'"]').attr('disabled', false);
        $('.bill-weight').text(parseInt($('.bill-weight').text())+parseInt($('input[id="check_'+coilNumber+'"]').data('coilWeight'))+' kgs');
    } else {
        $('input[data-number="'+coilNumber+'"]').attr('disabled', true);
        $('.allocated-weight').text(parseInt($('.allocated-weight').text())-parseInt($('span[data-differenceSpan="'+coilNumber+'"]').text())+' kgs');
        $('span[data-totalAllocatedSpan="'+coilNumber+'"]').text('');
        $('span[data-differenceSpan="'+coilNumber+'"]').text('');
        $('.'+coilNumber).val('');
        $('.bill-weight').text(parseInt($('.bill-weight').text())-parseInt($('#check_'+coilNumber).data('billWeight'))+' kgs');
    }
}

function calculateAllocatedWeights($this) {
    var coilNumber = $this.dataset.number || $this.dataset.packing;

    var packing_weight = parseFloat($('input[data-packing="'+coilNumber+'"]').val());
    var material_weight = parseFloat($('input[data-number="'+coilNumber+'"]').val());
    var billWeight = Math.round(parseFloat($('input[id="check_'+coilNumber+'"]').data('coilWeight')));

    if(!isNaN(packing_weight) && !isNaN(material_weight)) {
        var totAllocatedWeight = material_weight + packing_weight;
        var differenceWeight = billWeight - parseFloat(totAllocatedWeight);

        $('input[data-totalAllocated="'+coilNumber+'"]').val(totAllocatedWeight);
        $('span[data-totalAllocatedSpan="'+coilNumber+'"]').text(totAllocatedWeight)
        $('input[data-totalDifference="'+coilNumber+'"]').val(differenceWeight);
        $('span[data-differenceSpan="'+coilNumber+'"]').text(differenceWeight)

        var sum = 0
        $('input[name="totAllocatedWeight[]"]').each(function(index) {
            if($(this).val()) {
                sum += parseFloat($(this).val());
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
        url: "<?php echo fuel_url('weigh_updation_inward/saveInwardWeightment');?>",
        data: data,
    }).done(function( msg ) {
        if(msg == 'success') {
            alert('Weighment has been successfully updated. Please click to update a new weighment');
            window.location="<?php echo fuel_url('weigh_updation_inward');?>";
        } else {
            $('#save-weighment-outward').attr('disabled','false');
        }
    });
}
</script>
