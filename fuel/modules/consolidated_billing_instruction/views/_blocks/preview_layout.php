<?php 
function getCuttingTable($childCoils, $partyId) {
	?><thead>
	<tr>
		<th>Bundle Number</th>
		<th>Length</th>
		<th>Weight</th>
		<th>Total Number Of Sheets</th>
		<th>No Of Sheets Billed</th>
		<th>No of sheets to be billed</th>
		<th>Billing Status</th>
		<th>Balance</th>
		<th>Balance Weight</th>
	</tr>
	</thead>
	<?php foreach($childCoils as $bundleSlit) { ?>
		<tbody>
		<tr>
			<td><?=$bundleSlit->bundlenumber?></td>
			<td><?=$bundleSlit->length?></td>
			<td><?=$bundleSlit->weight?></td>
			<td><?=$bundleSlit->totalnumberofsheets?></td>
			<td><?=$bundleSlit->noofsheetsbilled?></td>
			<td><input type="text" style="width:55px;" name="cutting-bundles[<?php echo $partyId;?>][<?=$bundleSlit->bundlenumber?>]"/></td>
			<td><?=$bundleSlit->billingstatus?></td>
			<td><?=$bundleSlit->balance?></td>
			<td><?=$bundleSlit->balanceWeight?></td>
		</tr>
		</tbody>
<?php } }

function getSlittingTable($childCoils, $partyId) {
	?> <thead>
	<tr>
		<th>Serial Number</th>
		<th>Length</th>
		<th>Width</th>
		<th>Weight</th>
		<th>Slitting Date</th>
		<th>Billing Status</th>
		</tr>
	</thead>
	<?php foreach($childCoils as $bundleSlit) { 
		?>
		<tbody>
		<tr>
			<td><?=$bundleSlit->slitnumber?></td>
			<td><?=$bundleSlit->length?></td>
			<td><?=$bundleSlit->width?></td>
			<td><?=$bundleSlit->weight?></td>
			<td><?=$bundleSlit->sdate?></td>
			<td><?=$bundleSlit->billingstatus?></td>
		</tr>
		</tbody>
<?php } }
?>

<table cellpadding="0" cellspacing="10" border="0">
    <tr>
        <td><label>Parent Coil Number:</label></td> 
        <td><?php echo $parent_coil[$partyId]['coil']->vIRnumber;?></td>
    </tr>
    <tr>
        <td><label>Coil Description:</label></td>
        <td><?php echo $parent_coil[$partyId]['coil']->vDescription;?></td>
    </tr>
    <tr>
        <td><label>Thickness:</label></td> 
        <td><?php echo $parent_coil[$partyId]['coil']->fThickness;?>(in mm)</td>	  
    </tr>
    <tr>
        <td><label><?=lang('width')?>:</label></td> 
        <td><?php echo $parent_coil[$partyId]['coil']->fWidth;?>(in mm)</td>
    </tr>
    <tr>
        <td><label><?=lang('weight')?>:</label></td> 
        <td><?php echo $parent_coil[$partyId]['coil']->fQuantity;?>(in Kgs)</td>
    </tr>
    <tr>
        <td><label><?=lang('inv_no')?>:</label></td> 
        <td><?php echo $parent_coil[$partyId]['coil']->vInvoiceNo;?></td>
    </tr>
    <tr>
        <td><label><?=lang('Party_Name')?>:</label></td> 
        <td><?php echo $parent_coil[$partyId]['coil']->nPartyName;?></td>
    </tr>
</table>

<form>
    <fieldset id="parent_slits">
		<legend>Selected Parent <?=($parent_coil[$partyId]['coil']->vprocess == 'Slitting') ? 'Slits' : 'Bundles'?></legend>
		<table class="child-coil table table-striped table-bordered">
			<?php if($parent_coil[$partyId]['coil']->vprocess == 'Cutting') {
					getCuttingTable($parent_coil[$partyId]['bs'], $$partyId);
				} else { 
					getSlittingTable($parent_coil[$partyId]['bs'], $partyId);
				}?>
		</table>
    </fieldset>
    
    <fieldset>
		<legend>Selected Children Coil Details</legend>
		<?php foreach($children_coils as $key => $child) {?>
			<div>
				<h4><?=$child['coil']->vIRnumber?></h4>
				<table class="child-coil table table-striped table-bordered">
					<?php if($child['coil']->vprocess == 'Cutting') {
						getCuttingTable($child['bs'], $child['coil']->vIRnumber);
					} else {
						getSlittingTable($child['bs'], $child['coil']->vIRnumber);
					} ?>
				</table>
			</div>
		<?php }?>
    </fieldset>	

    <div class="pad-10">
        <legend>Aditional Charges:</legend> 

		<input type="text" style="width: 394px;height: 30px;" id="txtadditional_type" name="txtadditional_type" placeholder="New Additional Charge Type"/> 
		&nbsp; 
		<input type="text" style="width: 394px;height: 30px;" id="txtamount_mt" name="txtamount_mt"  placeholder="New Additional Charge Type" /> 
	</div>
    <div class="pad-10">
        <legend>Processing Charges:</legend>
	    <input type="text" style="width: 290px;height: 30px;" id="txtoutward_num" name="txtoutward_num" placeholder="Outward Lorry Number" /> 
	    &nbsp;
	    <input type="text" style="width: 290px;height: 30px;" id="driverContact" name="driverContact" placeholder="Driver Contact Number" />  
	    &nbsp;
	    <input type="text" style="width: 290px;height: 30px;" id="txtscrap" name="txtscrap" placeholder="Scrap Sent" /> 
    </div>

    <fieldset>
        <legend>Other Charges:</legend>
    </fieldset>

    <div class="pad-10">
	    Total: <input type="text" style="width: 272px;height: 30px;" id="totalweight_checks" DISABLED /> &nbsp;&nbsp;&nbsp;
        <input type="text" style="width: 272px;height: 30px;" id="totalrates" DISABLED/>&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="text" style="width: 272px;height: 30px;" id="totalamtsslit"  DISABLED/>
    </div>

    <div align="left">
        Select type of GST tax to be applied	<br>
        <input style="margin: 10px;" type="radio" class="gstType" name="gstType" value="Within">&nbsp; Within State</br>
        <input style="margin: 10px;" type="radio" class="gstType" name="gstType" value="Inter">&nbsp; Inter State
    </div>

    <input class="btn btn-success" id="previewBill" type="submit" value="Print The Bill"/>
	<input class="btn btn-danger" id="new" type="button" value="Cancel" onClick="closebutton();"/>
</form>