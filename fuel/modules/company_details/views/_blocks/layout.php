<div id="innerpanel">
    &nbsp;
    &nbsp;
    <fieldset>
        <legend><strong>Company Details</strong><br/></legend>
        &nbsp;<form id="cisave" method="post" name="companyDetails" action="">

            <div>
                <table cellpadding="0" cellspacing="10" border="0">
                    <tr>
                        <td>
                            <label>The name of the company<span class="required">*</span></label>
                        </td>
                        <td>
                            <input id="cname" type="text" name="cname" value="<?=$data[0]->company_name?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>The default name or identifier to use for all receivable operations.<span
                                        class="required">*</span></label>
                        </td>
                        <td>
                            <input id="ide_receive" name="ide_receive" type="text" value="<?=$data[0]->identifier_receivable?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>The default name or identifier to use for all payable operations.<span
                                        class="required">*</span></label>
                        </td>
                        <td>
                            <input id="ide_payable" name="ide_payable" type="text" value="<?=$data[0]->identifier_payable?>"/>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>Head office address<span class="required">*</span></label>
                        </td>
                        <td><textarea id="addr1" name="headOffice" type="text"><?=$data[0]->head_address?></textarea>&nbsp;&nbsp;<span>Info : Please enter the exact head office address to be displayed on the bills</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>Branch office address<span class="required">*</span></label>
                        </td>
                        <td><textarea id="addr2" name="branchOffice" type="text"><?=$data[0]->branch_address?></textarea>&nbsp;&nbsp;<span>Info : Please enter the exact branch office address to be displayed on the bills</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>Enter the general company contact number</label>
                        </td>
                        <td>
                            <input id="email" name="contact" type="text" value="<?=$data[0]->contact?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>Enter the general company email address</label>
                        </td>
                        <td>
                            <input id="email" name="email" type="text" value="<?=$data[0]->email?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>Enter GST registration number</label>
                        </td>
                        <td>
                            <input id="duty_no" name="gstNumber" type="text" value="<?=$data[0]->gst_no?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>Enter TIN number</label>
                        </td>
                        <td>
                            <input id="tin_no" name="tinNumber" type="text" value="<?=$data[0]->tin_no?>"/>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="pad-10">
                <input id="newsize" class="btn btn-success" type="button" value="Save" onClick="functionsave(); "/>
                &nbsp; &nbsp; &nbsp;
            </div>
        </form>
    </fieldset>

</div>

<script language="javascript" type="text/javascript">

    function inwardregistrybutton(id) {
        $.ajax({
            type: "POST",
            success: function () {
                setTimeout("location.href='<?= site_url('fuel/company_details_entry'); ?>'", 100);
            }
        });
    }

    function functionsave() {
        var data = $('form').serialize();
        $.ajax({
            type: "POST",
            url: "<?php echo fuel_url('company_details/savedetails');?>/",
            data: data,
            success: function (msg) {
                alert("Company details saved successfully");
            }
        });

    }
</script>
