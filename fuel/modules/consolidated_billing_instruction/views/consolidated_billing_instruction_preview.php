<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap.min.css">
<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" />
<script src="<?=$this->asset->js_path('datatables.min', 'coil_labels')?>"></script>

<div id="main_top_panel">
    <h2 class="ico ico_bill_summary">Consolidated Billing</h2>
</div>
<?php include_once(CONSOLIDATED_BILLING_INSTRUCTION_PATH.'views/_blocks/preview_layout.php');?>