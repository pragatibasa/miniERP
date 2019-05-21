<?php 
function getCuttingTable($childCoils, $coilNumber, $selected_cutting_coil) {
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
		<?php
			foreach($childCoils as $bundleDetails) {
				?><tr>
					<td><?=$bundleDetails->bundlenumber?></td>
					<td><?=$bundleDetails->length?></td>
					<td><?=$bundleDetails->weight?></td>
					<td><?=$bundleDetails->totalnumberofsheets?></td>
					<td><?=$bundleDetails->noofsheetsbilled?></td>
					<td><?=$selected_cutting_coil[$coilNumber]['number_billed'][$bundleDetails->bundlenumber]?></td>
					<td><?=$bundleDetails->billingstatus?></td>
					<td><?=$bundleDetails->balance?></td>
					<td><?=$bundleDetails->balanceWeight?></td>
				</tr>
			<?php }
}

function getSlittingTable($childCoils, $coilNumber) {
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
	?><tr>
		<td><?=$bundleSlit->slitnumber?></td>
		<td><?=$bundleSlit->length?></td>
		<td><?=$bundleSlit->width?></td>
		<td><?=$bundleSlit->weight?></td>
		<td><?=$bundleSlit->sdate?></td>
		<td><?=$bundleSlit->billingstatus?></td>
	</tr>
	<?php }
}
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
    <fieldset id="cutting_bundles">
		<legend>Selected Cutting Bundles</legend>
		<table class="table table-striped table-bordered">
			<?php foreach($selected_cutting_coil as $coilNumber => $bundleList) {  ?>
				<h4><?=$coilNumber?></h4>
				<?php getCuttingTable($bundleList[0], $coilNumber, $selected_cutting_coil);
			}?>
		</table>
    </fieldset>
    
    <fieldset>
		<legend>Selected Slitting Coil Details</legend>
		<table class="table table-striped table-bordered">
			<?php foreach($selected_slitting_coil as $coilNumber => $bundleList) {  ?>
				<h4><?=$coilNumber?></h4>
				<?php getSlittingTable($bundleList[0], $coilNumber);
			}?>
		</table>
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
	    Total: 
        <input value="<?php echo $thickness_rate->subtotal; ?>" type="text" style="width: 272px;height: 30px;" id="totalrates" DISABLED/>&nbsp;&nbsp;&nbsp;&nbsp;
    </div>

    <div align="left">
        Select type of GST tax to be applied	<br>
        <input style="margin: 10px;" type="radio" class="gstType" name="gstType" value="Within">&nbsp; Within State</br>
        <input style="margin: 10px;" type="radio" class="gstType" name="gstType" value="Inter">&nbsp; Inter State
    </div>

    <input class="btn btn-success" id="previewBill" type="submit" value="Print The Bill"/>
	<input class="btn btn-danger" id="new" type="button" value="Cancel" onClick="closebutton();"/>
</form>