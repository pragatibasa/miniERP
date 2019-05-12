<?php 
function getCuttingTable($childCoils, $partyId) {
	?><thead>
	<tr>
		<th>Select</th>
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
			<td><input name="cutting-bundles[<?php echo $partyId;?>][<?=$bundleSlit->bundlenumber?>]" value="<?php echo $bundleSlit->bundlenumber;?>" type="checkbox" <?php echo ($bundleSlit->balance <= 0) ? 'disabled="true"' : '';?> /></td>
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
		<th>Select</th>
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
			<td><input name="slitting-bundles[<?php echo $partyId;?>][]" value="<?php echo $bundleSlit->slitnumber;?>" type="checkbox" /></td>
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

<form id="bil_ins" method="post" action="<?php echo fuel_url('consolidated_billing_instruction/previewBillPage');?>" class="bil_ins">
	<fieldset id="parent_slits">
		<legend>Parent <?=($parent_coil[$partyId]['coil']->vprocess == 'Slitting') ? 'Slits' : 'Bundles'?></legend>
		<table class="child-coil table table-striped table-bordered">
			<?php if($parent_coil[$partyId]['coil']->vprocess == 'Cutting') {
					getCuttingTable($parent_coil[$partyId]['bs'], $$partyId);
				} else { 
					getSlittingTable($parent_coil[$partyId]['bs'], $partyId);
				}?>
		</table>
	</fieldset>
	<fieldset>
		<legend>Children Coil Details</legend>
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
	<input type="hidden" name="partyid" value="<?=$partyId?>" />
	<input class="btn btn-success" id="previewBill" type="submit" value="Preview The Bill"/>
	<input class="btn btn-danger" id="new" type="button" value="Close" onClick="closebutton();"/>
</form>

<script>
	$(document).ready(function() { 
		$('.child-coil').DataTable({paging: false,searching: false,ordering: false,"bInfo":false});
	});
   </script>