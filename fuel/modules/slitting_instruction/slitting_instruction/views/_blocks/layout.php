<style>.btn-info {
    background-color: #49AFCD;
    background-image: linear-gradient(to bottom, #5BC0DE, #2F96B4);
    background-repeat: repeat-x;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
    color: #FFFFFF;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
}</style>
<div id="innerpanel"> 
&nbsp;
&nbsp;
<fieldset>
<legend>Coil Details</legend>
	<div>  
		<table cellpadding="0" cellspacing="10" border="0">
			<tr>
				<td>
					<label><?=lang('party_id')?></label>
				</td>  
				<td>
					<input id="pid" name="vIRnumber" type="text" DISABLED/>
				</td>
				<td>
					<label><?=lang('party_name')?></label>
				</td>
				<td> 
					<input id="pname" type="text" value="<?php echo $partyname; ?>" DISABLED />
				</td>
			</tr>
			<tr>	
				<td>
					<label><?=lang('Material_description')?></label>
				</td> 
				<td>
					<input id="mat_desc" name="vDescription" type="text" DISABLED/>
				</td>
				<td>
					<label><?=lang('width_txt')?></label>
				</td> 
				<td>
					<input id="wid" name="fWidth" type="text" DISABLED/> (in mm)
				</td>
			</tr>	
			<tr>
				<td>
					<label><?=lang('thickness_txt')?></label>
				</td>  
				<td>
					<input id="thic" name="fThickness" type="text" DISABLED/> (in mm)
				</td>
				<td>
					<label><?=lang('weight_txt')?></label>
				</td>
				<td> 
					<input id="wei" name="fQuantity" type="text" DISABLED/> (in Kgs)
				</td>
			</tr>
			<tr>
				<td>
					<label><?=lang('remaining_coil_length')?></label>
				</td>
				<td> 
					<input name="remaining_weight" id="remaining_length" type="text" DISABLED/> (in mm)
				</td>
			</tr>
		</table>
	</div>
</fieldset>
<fieldset>
<legend>Slitting Instruction</legend>	
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td width="40%" align="left" valign="top">	
<form id="cisave" method="post" action="" class="__fuel_edit__" style="font-size:14px;width:500px;">
		<div class="pad-10 hide">
			<div id="date_text_label"> Date </div>
			<input type="text" id="date1" value="<?php echo date("Y-m-d"); ?>" DISABLED/>
		</div>
		<div class="pad-10">
			<div id="bundle_weight_text_label"> Length  </div>
			<input type="radio" name="balance_length" id="balance_length" onClick="balance();"/>&nbsp;Balance Length</br></br>
			<input id="length_v" type="text" name="length" onkeyup="doweight();"/>
			<input id="txtslitingnumber" type="hidden"   />
		</div>
		<div class="pad-10">
			<div id="bundle_weight_text_label"> Width  </div>
			<input id="width_v" class="width" type="text" name="width"/>
			<span class="__fuel_edit_marker_new__" title="Click to add new width" style="position: absolute; padding: 8px; margin-left: 5px; margin-top: 7px;cursor: pointer;"></span>
			<input id="txtslitingnumber" type="hidden"   />
		</div>
		<div class="pad-10">
			<div id="bundle_weight_text_label"> Weight  </div>
			<input id="weight_v" type="text" name="weight" disabled/>
			<input id="txtslitingnumber" type="hidden"   />
		</div>
		<div class="pad-10">
		<!--	<input id="newsize" type="button" value="Add New Size" onClick="functionsave();"/> &nbsp; &nbsp; &nbsp;
			<input id="edit" type="button" value="UPDATE/EDIT" onClick="functionedit();" hidden/>&nbsp; &nbsp; &nbsp;
			<input id="reset" type="reset" value="Reset" onclick="functionreset();"/>-->
			<input class="btn btn-success" type="button" value="Add New Size" id="newsize" onClick="functionsave();"/> &nbsp; &nbsp; &nbsp;
			<input class="btn btn-danger" id="reset" type="reset" value="Reset" onClick="functionreset();" /> &nbsp; &nbsp; &nbsp;
			<input class=" btn-info"  type="button" value="UPDATE/EDIT"  id="edit" onClick="functionedit();" hidden/> &nbsp; &nbsp; &nbsp; 
		</div>
</form>
</td>
<td width="60%" align="left" valign="top">							
    <div id="contentsholder" class="flexcroll" style="width:100%; height:350px; overflow-x:hidden; overflow-y:auto;">
		<div id="content" style="width:100%; min-height:350px; overflow:hidden;"> 
			<div id="DynamicGrid_2">
				No Record!
			</div>
		</div>
	</div>
</td>
</tr>
<td>	
</td>
<td align="right">
	<label>Total Width</label>
		<input id="txttotalwidth" type="text" DISABLED/>(in mm)  
		&nbsp; &nbsp; &nbsp;
		<input class="btn btn-success"  id="saveci" type="button" value="Save" onClick="savechange();"/>  
		<input id="finishci" type="button" value="Finsh" onClick="finishinstructionbutton();" hidden/>&nbsp; &nbsp; &nbsp;		
</td>
</tr>
</table>
</fieldset>
</div>

<script type="text/javascript" language="javascript">
function functionreset(){
	$("#newsize").show();
	$("#edit").hide();
}

$(document).on( 'click', '.__fuel_edit_marker_new__',function() {
	$(this).after('<span title="Delete" class="ico_delete" style="margin-top: 7px; height: 8px; margin-left: 22px; padding: 5px; position: absolute; width: 7px;cursor:pointer;"></span><input type="text" class="width" name="width" id="width_v"><span title="Click to add new width" style="position: absolute; padding: 8px; margin-left: 5px; margin-top: 7px;cursor: pointer;" class="__fuel_edit_marker_new__"></span>');
	$(this).next('.ico_delete').css('margin-left','4px');
	$(this).remove();
});

$(document).on( 'click', '.ico_delete', function() {
	$(this).prev('input').remove();
	$(this).remove();
});

$(document).on( 'keyup', '.width',function() {
	var pid   =	$('#pid').val();
	var totalWidth = 0;

	$('.width').each(function() {
		if($(this).val() !== '')
			totalWidth = totalWidth+parseInt($(this).val());
	});
	if(totalWidth > parseInt($('#wid').val())) {
		alert('Sum of slits width is greated width of coil.');
		return false;
	}
	var thickness = $('#thic').val();
	var length = $('#length_v').val();
	var weight = $('#wei').val();

	if(($('.width').length == 1 && $('.width').val() == '') || thickness == ''){
		$('#rate').val('');
		alert("All fields are mandatory");
	} else {
		var resultbundle= (0.00000785 *totalWidth*thickness*length);
		var resultbundle = Math.round(resultbundle).toFixed(3);
		document.getElementById('weight_v').value = resultbundle;
	}
});

function balance() {
	var pid = $('#pid').val();
	var remaining_length = $('#remaining_length').val();
	var dataString = 'remaining_length='+remaining_length+'&pid='+pid;
	
	$.ajax({
        type: 'POST',
        url: "<?php echo fuel_url('slitting_instruction/getBalanceLength');?>",
		data: dataString,
		success: function(msg){  
			$('#length_v').val(msg);
		}
    });
}

function doweight() {
	var length = parseInt($('#length_v').val());
	var pid   =	$('#pid').val();
	var totalWidth = 0;

	if( length > parseInt($('#remaining_length').val())) {
		alert("Selected length exceeds estimated length");
		return false;
	}

	if($('.width').length == 1 && $('.width').val() == '') {
		return false;
	}

	$('.width').each(function() {
		if($(this).val() !== '')
			totalWidth = totalWidth+parseInt($(this).val());
	});
	if(totalWidth > parseInt($('#wid').val())) {
		alert('Sum of slits width is greated width of coil.');
		return false;
	}
	var thickness = $('#thic').val();
	var weight = $('#wei').val();

	if(thickness == ''){
		$('#rate').val('');
		alert("All fields are mandatory");return false;
	} else {
		var resultbundle = (0.00000785 *totalWidth*thickness*length);
		var resultbundle = Math.round(resultbundle).toFixed(3);
		document.getElementById('weight_v').value = resultbundle;
	}
}

function loadfolderlist(account, accname) {
	$('#DynamicGrid_2').hide();
	var loading = '<div id="DynamicGridLoading_2"> '+
            	   ' <img src="<?=img_path() ?>loading.gif" /><span> Loading slit List... </span> '+ 
    	    	   ' </div>';
    $("#content").empty();
	$('#content').html(loading);
    $.ajax({
        type: "POST",
        url: "<?php echo fuel_url('slitting_instruction/listslittingdetails');?>",
        data: "partyid=" + account,
        dataType: "json"
        }).done(function( msg ) {
			if(msg.length == 0) {
			$('#DynamicGrid_2').hide();
			$('#DynamicGridLoading_2').hide();
			var loading1 = '<div id="error_msg"> '+
                           'No Result!'+ 
						   '</div>';
			$('#content').html(loading1);  
			} else{
            var partydata = [];
            for (var i = 0; i < msg.length; i++) {
            var item = msg[i];
            var thisdata = {};
			thisdata["Sno"] = item.Sno;
            thisdata["Slittingdate"] = item.Slittingdate;
            thisdata["width"] = item.width;
            thisdata["weight"] = item.weight;
            thisdata["length"] = item.length;
			var edit = '<a class="ico_coil_edit" title="Edit" href="#" onClick=radioload('+item.Sno+','+item.width+')><img src="<?php echo img_path('iconset/ico_edit.png'); ?>" /></a>';
			var dl = '<a class="ico_coil_delete" title="Delete" href="'+item.dl+'" onClick=deleteItem('+item.Sno+')><img src="<?php echo img_path('iconset/ico_cancel.png'); ?>" /></a>';
            thisdata["action"] = edit+' '+dl;
			//thisdata["action"] = '';
            partydata.push(thisdata);
			}
			if (partydata.length) {
            // If there are files
				$('#DynamicGrid_2').hide();
				$('#DynamicGridLoading_2').hide();
				$('#content').html(CreateTableViewX(partydata, "lightPro", true)); 
				var lcScrollbar = $('#contentsholder');	 
				fleXenv.updateScrollBars(lcScrollbar); 
				$(".ico_coil_delete").click(function (e) {
                // When a delete icon is clicked, stop the href action
                //  and do an ajax call to delete it instead
                e.preventDefault();
                var data = {account_name: account};
                var href = $(this).attr('href');
                $.post(href, data, function (d) {
                loadfolderlist(account, accname);
                });
                });
			} else {
				$('#DynamicGrid_2').hide();
				$('#DynamicGridLoading_2').hide();
				var loading1 = '<div id="error_msg"> '+
							   'No Result!'+ 
							   '</div>';
				$('#content').html(loading1); 
				var lfScrollbar = $('#contentsholder');	 
				fleXenv.updateScrollBars(lfScrollbar);  
                }
			}
    });
}

function totalwidth_check(){
	var partyid = $('#pid').val();
	var dataString = '&partyid='+partyid;
$.ajax({  
	   type: "POST",  
	   url : "<?php echo fuel_url('slitting_instruction/totalwidth');?>/",  
		data: dataString,
		datatype : "json",
		success: function(msg){
		var msg3=eval(msg);
		$.each(msg3, function(i, j){
			 var width = j.width;
			document.getElementById("txttotalwidth").value = width;});
	   }  
	}); 
}

function deleteItem(sn){
	document.getElementById('txtslitingnumber').value = sn;
	var slitingnumber = $('#txtslitingnumber').val();
	var pid = $('#pid').val();
    var checkstr =  confirm('Are you sure you want to delete this?');
	var dataString = {Slitingnumber : slitingnumber,Pid:pid};
    if(checkstr == true){
      $.ajax({
	    type: "POST",
		url	: "<?php echo fuel_url('slitting_instruction/delete_slit');?>",
		data : dataString,
		datatype: json,
			success: function(msg){ 
				refresh_folderlist(); 
			}
		});
    }else{
    return false;
    }
  }

function savechange(id) {
    var pid   =	$('#pid').val();
	var totalwidth = $('#txttotalwidth').val();
	var coilwidth = $('#width_v').val();
	if(parseInt(totalwidth) > parseInt(coilwidth) ){
		alert('Sorry the Total width of bundle is more then width of coil please edit the width or delete to progress!!');
	} else{
		var dataString = 'pid='+pid;
     	$.ajax({
                type: 'POST',
                url: "<?php echo fuel_url('slitting_instruction/save_button');?>",
				data: dataString,
                success: function() {
					alert("Saved Succesfully");
					refresh_folderlist();
					totalwidth_check();	
				}
        });
	}
}

function functionedit(){
	var bundlenumber = $('#bundlenumber').val();
	var width_v = $('#width_v').val();
	   var dataString = 'bundlenumber='+bundlenumber+'&width_v='+width_v;
	   $.ajax({  
	   type: "POST",  
	   url : "<?php echo fuel_url('slitting_instruction/editbundle');?>/",  
	   data: dataString,
	   success: function(msg){
	   alert("Updated Successfully");
		//$('#bundlenumber').val('');
		$('#width_v').val('');
		//$("#newsize").show();
		$("#newsize").show();
		$("#edit").hide();
		refresh_folderlist();
		totalwidth_check();	
	   }  
	  }); 
	}

function radioload(b,bn) {
	$("#edit").show();
	$("#newsize").hide();
	document.getElementById('bundlenumber').value = b;
	document.getElementById('width_v').value = bn;
}

</script>

<script>
var json = <?php echo($adata); ?>;
for(key in json){
	if(json.hasOwnProperty(key))
	$('input[name='+key+']').val(json[key]);
}

function functionsave() {
	var date1 = $('#date1').val();
	var length = $('#length_v').val();
	var pid = $('#pid').val();
	var thickness = $('#thic').val();
	var allWidths = [];
	
	$('.width').each(function() {
		if($(this).val() !== '')	
			allWidths.push($(this).val());
	});

	 if( length =='' ) {
	  	alert('ENTER SOMETHING');
	  	return false;
	 } else{
		var dataString = 'date1='+date1+'&widths='+allWidths+'&pid='+pid+'&length='+length+'&thickness='+thickness;
		$.ajax({  
			type: "POST",  
			url : "<?php echo fuel_url('slitting_instruction/savebundleslit');?>/",  
			data: dataString,
			success: function(msg){  
				$('#width_v').val('');
				refresh_folderlist();
				totalwidth_check();	
			}
		}); 
	}

}

function addDate(){
	date = new Date();             
	var month = date.getMonth()+1;
	var day = date.getDate();
	var year = date.getFullYear();
	if (document.getElementById('date1').value == ''){
	document.getElementById('date1').value = day + '-' + '0' +month + '-' + '0'+ year;
	}
}

function timedRefresh(timeoutPeriod){
	setTimeout("location.reload(true);",timeoutPeriod);
}

function finishinstructionbutton(id){
	var pid  =	$('#pid').val();
	var party = $('#pname').val();
	var dataString = 'partyid='+pid+'&partyname='+party+'&task=sit';
	$.ajax({  
	type: "POST",  
	url	: "<?php echo site_url('finish_task/finish_slit');?>/",  
	data: dataString,
	success: function(){  
		setTimeout("location.href='<?= site_url('fuel/finish_task'); ?>/?"+ dataString+"'", 3000);
		}
	});
}

function deleterecord(){
	var deleteid = $('#deletevalue').val();
	var dataString = 'number='+deleteid;
		$.ajax({  
			type: "POST",  
			url	: "<?php echo fuel_url('slitting_instruction/deleterow');?>/",  
			data: dataString,
			success: function(msg){  
			$("#deletemsg").html(msg);
			$('#deletevalue').val('');
			}  
		}); 
}
</script>