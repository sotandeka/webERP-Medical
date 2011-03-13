<?php
/* $Id: Z_ChangeSupplierCode.php 4466 2011-01-13 09:33:59Z daintree $*/
/*Script to Delete all sales transactions*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('UTILITY PAGE To Changes A Supplier Code In All Tables');
include('includes/header.inc');

if (isset($_POST['ProcessSupplierChange']))
    ProcessSupplier($_POST['OldSupplierNo'], $_POST['NewSupplierNo']);

echo "<form action='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' method=post>";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '
    <div class="centre">
    <table>
	<tr><td>' . _('Existing Supplier Code') . ':</td>
		<td><input type="Text" name="OldSupplierNo" size=20 maxlength=20></td>
	</tr>
        <tr><td> ' . _('New Supplier Code') . ':</td>
	<td><input type=Text name="NewSupplierNo" size=20 maxlength=20></td>
	</tr>
    </table>
    <input type=submit name="ProcessSupplierChange" VALUE="' . _('Process') . '">
    <div>
    </form>';

include('includes/footer.inc');
exit();


function ProcessSupplier($oldCode, $newCode) {
    global $db;
    $table_key= array (
        'grns' => 'supplierid',
        'offers'=>'supplierid',
        'purchdata'=>'supplierno',
        'purchorders'=>'supplierno',
        'shipments'=>'supplierid',
        'suppliercontacts'=>'supplierid',
        'supptrans'=>'supplierno',
        'www_users'=>'supplierid');
    
    // First check the Supplier code exists
    if (!checkSupplierExist($oldCode)) {
        prnMsg ('<br><br>' . _('The Supplier code') . ': ' . $oldCode . ' ' .
                _('does not currently exist as a Supplier code in the system'),'error');
        return;
    }
    $newCode = trim($newCode);
    if (checkNewCode($newCode)) {
        // Now check that the new code doesn't already exist
		if (checkSupplierExist($newCode)) {
				prnMsg(_('The replacement Supplier code') .': ' .
						$newCode . ' ' . _('already exists as a Supplier code in the system') . ' - ' . _('a unique Supplier code must be entered for the new code'),'error');
				return;
		}
    } else {
        return;
    }

    $result = DB_Txn_Begin($db);

    prnMsg(_('Inserting the new supplier record'),'info');
    $sql = "INSERT INTO suppliers (`supplierid`,
        `suppname`,  `address1`, `address2`, `address3`,
        `address4`,  `address5`,  `address6`, `supptype`, `lat`, `lng`,
        `currcode`,  `suppliersince`, `paymentterms`, `lastpaid`,
        `lastpaiddate`, `bankact`, `bankref`, `bankpartics`,
        `remittance`, `taxgroupid`, `factorcompanyid`, `taxref`,
        `phn`, `port`, `email`, `fax`, `telephone`)
    SELECT '" . $newCode . "',
        `suppname`,  `address1`, `address2`, `address3`,
        `address4`,  `address5`,  `address6`, `supptype`, `lat`, `lng`,
        `currcode`,  `suppliersince`, `paymentterms`, `lastpaid`,
        `lastpaiddate`, `bankact`, `bankref`, `bankpartics`,
        `remittance`, `taxgroupid`, `factorcompanyid`, `taxref`,
        `phn`, `port`, `email`, `fax`, `telephone`
        FROM suppliers WHERE supplierid='" . $oldCode . "'";

    $DbgMsg =_('The SQL that failed was');
    $ErrMsg = _('The SQL to insert the new debtors master record failed') . ', ' . _('the SQL statement was');
    $result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

    foreach ($table_key as $table=>$key) {
        prnMsg(_('Changing').' '. $table.' ' . _('records'),'info');
	$sql = "UPDATE $table SET $key='" . $newCode . "' WHERE $key='" . $oldCode . "'";
	$ErrMsg = _("The SQL to update $table records failed");
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
    }

    prnMsg(_('Deleting the Supplier code from the DebtorsMaster table'),'info');
    $sql = "DELETE FROM suppliers WHERE supplierid='" . $oldCode . "'";

    $ErrMsg = _('The SQL to delete the old debtor record failed');
    $result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

    $result = DB_Txn_Commit($db);
}

function checkSupplierExist($codeSupplier) {
    global $db;
    $result=DB_query("SELECT supplierid FROM suppliers WHERE supplierid='" .
            $codeSupplier . "'",$db);
    if (DB_num_rows($result)==0) return false;
    return true;
}

function checkNewCode($code) {
    $tmp = str_replace(' ','',$code);
    if ($tmp != $code) {
        prnMsg ('<br><br>' . _('The New supplier code') . ': ' . $code . ' ' .
                _('must be not empty nor with spaces'),'error');
        return false;
    }
    return true;
}
?>
