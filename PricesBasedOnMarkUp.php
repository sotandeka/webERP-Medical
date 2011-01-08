<?php
/* $Revision: 1.9 $ */
/* $Id$*/
//$PageSecurity=11;


include('includes/session.inc');
$title=_('Update Pricing From Costs');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('Search') .
		'" alt="" />' . $title.'</p>';

echo '<br><div class="page_help_text">' . _('This page adds new prices or updates already existing prices for a specified sales type (price list) and currency for the stock category selected - based on a percentage mark up from cost prices or from preferred supplier cost data. The rounding factor ensures that prices are at least this amount or a multiple of it. A rounding factor of 1000 would mean that prices would be a minimum of 1000 and other prices would be expressed as multiples of 1000.') . '</div><br><div class="centre">';

echo "<form method='POST' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT sales_type, typeabbrev FROM salestypes";

$PricesResult = DB_query($SQL,$db);

echo '<p><table class=selection>
             <tr>
          	   <td>' . _('Select the Price List to update') .':</td>
               <td><select name="PriceList">';

if (!isset($_POST['PriceList']) OR $_POST['PriceList']=='0'){
	echo '<option selected VALUE="0">' . _('No Price List Selected');
}

while ($PriceLists=DB_fetch_array($PricesResult)){
	if (isset($_POST['PriceList']) and $_POST['PriceList']==$PriceLists['typeabbrev']){
		echo "<option selected value='" . $PriceLists['typeabbrev'] . "'>" . $PriceLists['sales_type'] . '</option>';
	} else {
		echo "<option value='" . $PriceLists['typeabbrev'] . "'>" . $PriceLists['sales_type'] . '</option>';
	}
}

echo '</select></td></tr>';

$SQL = "SELECT currency, currabrev FROM currencies";

$result = DB_query($SQL,$db);

echo '<tr>
        <td>' . _('Select the price list currency to update') . ':</td>
                            <td><select name="CurrCode">';

if (!isset($_POST['CurrCode'])){
	echo '<option selected value=0>' . _('No Price List Currency Selected');
}

while ($Currencies=DB_fetch_array($result)){
	if (isset($_POST['CurrCode']) and $_POST['CurrCode']==$Currencies['currabrev']) {
		echo '<option selected value="' . $Currencies['currabrev'] . '">' . $Currencies['currency'] . '</option>';
	} else {
		echo '<option value="' . $Currencies['currabrev'] . '">' . $Currencies['currency'] . '</option>';
	}
}

echo '</select></td></tr>';

if ($_SESSION['WeightedAverageCosting']==1){
	$CostingBasis = _('Weighted Average Costs');
} else {
	$CostingBasis = _('Standard Costs');
}

echo '<tr><td>' . _('Cost/Preferred Supplier Data Or Other Price List') . ':</td>
                <td><select name="CostType">';
if ($_POST['CostType']=='PreferredSupplier'){
     echo ' <option selected value="PreferredSupplier">' . _('Preferred Supplier Cost Data') . '</option>
            <option value="StandardCost">' . $CostingBasis . '</option>
            <option value="OtherPriceList">' . _('Another Price List') . '</option>';
} elseif ($_POST['CostType']=='StandardCost'){
	 echo ' <option value="PreferredSupplier">' . _('Preferred Supplier Cost Data') . '</option>
            <option selected value="StandardCost">' . $CostingBasis . '</option>
            <option value="OtherPriceList">' . _('Another Price List') . '</option>';
} else {
	echo ' <option value="PreferredSupplier">' . _('Preferred Supplier Cost Data') . '</option>
            <option value="StandardCost">' . $CostingBasis . '</option>
            <option selected value="OtherPriceList">' . _('Another Price List') . '</option>';
}
echo '</select></td></tr>';

DB_data_seek($PricesResult,0);

if (isset($_POST['CostType']) and $_POST['CostType']=='OtherPriceList'){
     echo '<tr><td>' . _('Select the Base Price List to Use') . ':</td>
                            <td><select name="BasePriceList">';

	if (!isset($_POST['BasePriceList']) OR $_POST['BasePriceList']=='0'){
		echo '<option selected VALUE=0>' . _('No Price List Selected');
	}
	while ($PriceLists=DB_fetch_array($PricesResult)){
		if (isset($_POST['BasePriceList']) and $_POST['BasePriceList']==$PriceLists['typeabbrev']){
			echo "<option selected value='" . $PriceLists['typeabbrev'] . "'>" . $PriceLists['sales_type'] . '</option>';
		} else {
			echo "<option value='" . $PriceLists['typeabbrev'] . "'>" . $PriceLists['sales_type'] . '</option>';
		}
	}
	echo '</select></td></tr>';
}

echo '<tr><td>' . _('Stock Category From') . ':</td>
                <td><select name="StkCatFrom">';

$sql = "SELECT categoryid, categorydescription FROM stockcategory";

$ErrMsg = _('The stock categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['StkCatFrom']) and $myrow['categoryid']==$_POST['StkCatFrom']){
		echo "<option selected VALUE='". $myrow['categoryid'] . "'>" . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
	} else {
		echo "<option VALUE='". $myrow['categoryid'] . "'>"  . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
	}
}
echo '</select></td></tr>';

DB_data_seek($result,0);

echo '<tr><td>' . _('Stock Category To') . ':</td>
                <td><select name="StkCatTo">';

while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['StkCatFrom']) and $myrow['categoryid']==$_POST['StkCatTo']){
		echo "<option selected VALUE='". $myrow['categoryid'] . "'>" . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
	} else {
		echo "<option VALUE='". $myrow['categoryid'] . "'>"  . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
	}
}
echo '</select></td></tr>';

if (!isset($_POST['RoundingFactor'])){
	$_POST['RoundingFactor']=1;
}

if (!isset($_POST['PriceStartDate'])) {
	$_POST['PriceStartDate']=DateAdd(date($_SESSION['DefaultDateFormat']),'d',1);
}

if (!isset($_POST['PriceEndDate'])) {
	$_POST['PriceEndDate']=DateAdd(date($_SESSION['DefaultDateFormat']), 'y', 1);
}

echo '<tr><td>' . _('Rounding Factor') . ':</td>
                <td><input type=text class=number name="RoundingFactor" size="6" maxlength="6" value=' . $_POST['RoundingFactor'] . '></td></tr>';

echo '<tr><td>' . _('New Price To Be Effective From') . ':</td>
                <td><input type=text class=date alt="' . $_SESSION['DefaultDateFormat'] . '" name="PriceStartDate" size="10" maxlength="10" value="' . $_POST['PriceStartDate'] . '"></td></tr>';

echo '<tr><td>' . _('New Price To Be Effective To (Blank = No End Date)') . ':</td>
                <td><input type=text class=date alt="' . $_SESSION['DefaultDateFormat'] . '" name="PriceEndDate" size="10" maxlength="10" value="' . $_POST['PriceEndDate'] . '"></td></tr>';

if (!isset($_POST['IncreasePercent'])){
	$_POST['IncreasePercent']=0;
}

echo '<tr><td>' . _('Percentage Increase (positive) or decrease (negative)') . "</td>
                <td><input type=text name='IncreasePercent' class=number size=4 maxlength=4 VALUE=" . $_POST['IncreasePercent'] . "></td></tr></table>";


echo "<p><div class='centre'><input type=submit name='UpdatePrices' VALUE='" . _('Update Prices') . '\'  onclick="return confirm(\'' . _('Are you sure you wish to update or add all the prices according to the criteria selected?') . '\');"></div>';

echo '</form>';

if (isset($_POST['UpdatePrices'])){
	$InputError =0; //assume the best
	if ($_POST['PriceList']=='0'){
		prnMsg(_('No price list is selected to update. No updates will take place'),'error');
		$InputError =1;
	}
	if ($_POST['CurrCode']=='0'){
		prnMsg(_('No price list currency is selected to update. No updates will take place'),'error');
		$InputError =1;
	}

	if (! Is_Date($_POST['PriceEndDate']) AND $_POST['PriceEndDate']!=''){
		$InputError =1;
		prnMsg (_('The date the new price is to be in effect to must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (! Is_Date($_POST['PriceStartDate'])){
		$InputError =1;
		prnMsg (_('The date this price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (Date1GreaterThanDate2($_POST['PriceStartDate'],$_POST['PriceEndDate']) AND $_POST['PriceEndDate']!=''){
		$InputError =1;
		prnMsg (_('The end date is expected to be after the start date, enter an end date after the start date for this price'),'error');
	}
	if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']),$_POST['PriceStartDate'])){
		$InputError =1;
		prnMsg(_('The date this new price is to start from is expected to be after today'),'error');
	}
	if ($_POST['StkCatTo']<$_POST['StkCatFrom']){
		prnMsg(_('The stock category from must be before the stock category to - there would be not items in the range to update'),'error');
		$InputError =1;
	}
	if ($_POST['CostType']=='OtherPriceList' AND $_POST['BasePriceList']=='0'){
		echo '<br>Base price list selected: ' .$_POST['BasePriceList'];
		prnMsg(_('When you are updating prices based on another price list - the other price list must also be selected. No updates will take place until the other price list is selected'),'error');
		$InputError =1;
	}
	if ($_POST['CostType']=='OtherPriceList' AND $_POST['BasePriceList']==$_POST['PriceList']){
		prnMsg(_('When you are updating prices based on another price list - the other price list cannot be the same as the price list being used for the calculation. No updates will take place until the other price list selected is different from the price list to be updated' ),'error');
		$InputError =1;
	}

	if ($InputError==0) {
		prnMsg(_('For a log of all the prices changed this page should be printed with CTRL+P'),'info');
		echo '<br>' . _('So we are using a price list/sales type of') .' : ' . $_POST['PriceList'];
		echo '<br>' . _('updating only prices in') . ' : ' . $_POST['CurrCode'];
		echo '<br>' . _('and the stock category range from') . ' : ' . $_POST['StkCatFrom'] . ' ' . _('to') . ' ' . $_POST['StkCatTo'];
		echo '<br>' . _('and we are applying a markup percent of') . ' : ' . $_POST['IncreasePercent'];
		echo '<br>' . _('against') . ' ';

		if ($_POST['CostType']=='PreferredSupplier'){
			echo _('Preferred Supplier Cost Data');
		} elseif ($_POST['CostType']=='OtherPriceList') {
			echo _('Price List')  . ' ' . $_POST['BasePriceList'];
		} else {
			echo $CostingBasis;
		}

		if ($_POST['PriceList']=='0'){
			echo '<br>' . _('The price list/sales type to be updated must be selected first');
			include ('includes/footer.inc');
			exit;
		}
		if ($_POST['CurrCode']=='0'){
			echo '<br>' . _('The currency of prices to be updated must be selected first');
			include ('includes/footer.inc');
			exit;
		}
		if (Is_Date($_POST['PriceEndDate'])){
			$SQLEndDate = FormatDateForSQL($_POST['PriceEndDate']);
		} else {
			$SQLEndDate = '0000-00-00';
		}
		$sql = "SELECT stockid,
						materialcost+labourcost+overheadcost AS cost
				FROM stockmaster
				WHERE categoryid>='" . $_POST['StkCatFrom'] . "'
				AND categoryid <='" . $_POST['StkCatTo'] . "'";
		$PartsResult = DB_query($sql,$db);

		$IncrementPercentage = $_POST['IncreasePercent']/100;

		$CurrenciesResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_POST['CurrCode'] . "'",$db);
		$CurrencyRow = DB_fetch_row($CurrenciesResult);
		$CurrencyRate = $CurrencyRow[0];

		while ($myrow=DB_fetch_array($PartsResult)){

	//Figure out the cost to use
			if ($_POST['CostType']=='PreferredSupplier'){
				$sql = "SELECT purchdata.price/purchdata.conversionfactor/currencies.rate AS cost
							FROM purchdata INNER JOIN suppliers
								ON purchdata.supplierno=suppliers.supplierid
								INNER JOIN currencies
								ON suppliers.currcode=currencies.currabrev
							WHERE purchdata.preferred=1 AND purchdata.stockid='" . $myrow['stockid'] ."'";
				$ErrMsg = _('Could not get the supplier purchasing information for a preferred supplier for the item') . ' ' . $myrow['stockid'];
				$PrefSuppResult = DB_query($sql,$db,$ErrMsg);
				if (DB_num_rows($PrefSuppResult)==0){
					prnMsg(_('There is no preferred supplier data for the item') . ' ' . $myrow['stockid'] . ' ' . _('prices will not be updated for this item'),'warn');
					$Cost = 0;
				} elseif(DB_num_rows($PrefSuppResult)>1) {
					prnMsg(_('There is more than a single preferred supplier data for the item') . ' ' . $myrow['stockid'] . ' ' . _('prices will not be updated for this item'),'warn');
					$Cost = 0;
				} else {
					$PrefSuppRow = DB_fetch_row($PrefSuppResult);
					$Cost = $PrefSuppRow[0];
				}
			} elseif ($_POST['CostType']=='OtherPriceList'){
				$sql = "SELECT price FROM
								prices
							WHERE typeabbrev= '" . $_POST['BasePriceList'] . "'
								AND currabrev='" . $_POST['CurrCode'] . "'
								AND debtorno=''
								AND enddate='0000-00-00'
								AND stockid='" . $myrow['stockid'] . "'
							ORDER BY startdate DESC";
				$ErrMsg = _('Could not get the base price for the item') . ' ' . $myrow['stockid'] . _('from the price list') . ' ' . $_POST['BasePriceList'];
				$BasePriceResult = DB_query($sql,$db,$ErrMsg);
				if (DB_num_rows($BasePriceResult)==0){
					prnMsg(_('There is no default price defined in the base price list for the item') . ' ' . $myrow['stockid'] . ' ' . _('prices will not be updated for this item'),'warn');
					$Cost = 0;
				} else {
					$BasePriceRow = DB_fetch_row($BasePriceResult);
					$Cost = $BasePriceRow[0];
				}
			} else { //Must be using standard/weighted average costs
				$Cost = $myrow['cost'];
				if ($Cost<=0){
					prnMsg(_('The cost for this item is not set up or is set up as less than or equal to zero - no price changes will be made based on zero cost items. The item concerned is:') . ' ' . $myrow['stockid'],'warn');
				}
			}

			if ($_POST['CostType']!='OtherPriceList'){
				$RoundedPrice = round(($Cost * (1+ $IncrementPercentage) * $CurrencyRate+($_POST['RoundingFactor']/2))/$_POST['RoundingFactor']) * $_POST['RoundingFactor'];
				if ($RoundedPrice <=0){
					$RoundedPrice = $_POST['RoundingFactor'];
				}
			} else {
				$RoundedPrice = round(($Cost * (1+ $IncrementPercentage)+($_POST['RoundingFactor']/2))/$_POST['RoundingFactor']) * $_POST['RoundingFactor'];
				if ($RoundedPrice <=0){
					$RoundedPrice = $_POST['RoundingFactor'];
				}
			}

			if ($Cost > 0) {
				$CurrentPriceResult = DB_query("SELECT price FROM
												prices
												WHERE typeabbrev= '" . $_POST['PriceList'] . "'
												AND debtorno =''
												AND currabrev='" . $_POST['CurrCode'] . "'
												AND enddate='" . $SQLEndDate . "'
												AND startdate='" . FormatDateForSQL($_POST['PriceStartDate']) . "'
												AND stockid='" . $myrow['stockid'] . "'",$db);
				if (DB_num_rows($CurrentPriceResult)==1){
					$sql = "UPDATE prices SET price='" . $RoundedPrice . "'
							WHERE typeabbrev='" . $_POST['PriceList'] . "'
							AND currabrev='" . $_POST['CurrCode'] . "'
							AND debtorno=''
							AND startdate ='" . FormatDateForSQL($_POST['PriceStartDate']) . "'
							AND enddate ='" . $SQLEndDate . "'
							AND stockid='" . $myrow['stockid'] . "'";
					$ErrMsg =_('Error updating prices for') . ' ' . $myrow['stockid'] . ' ' . _('because');
					$result = DB_query($sql,$db,$ErrMsg);
					prnMsg(_('Updating price for') . ' ' . $myrow['stockid'] . ' ' . _('to') . ' ' . $RoundedPrice,'info');
				} else {
					$sql = "INSERT INTO prices (stockid,
												typeabbrev,
												currabrev,
												startdate,
												enddate,
												price)
									VALUES ('" . $myrow['stockid'] . "',
											'" . $_POST['PriceList'] . "',
											'" . $_POST['CurrCode'] . "',
											'" . FormatDateForSQL($_POST['PriceStartDate']) . "',
											'" . $SQLEndDate . "',
									 		'" . $RoundedPrice . "')";
					$ErrMsg =_('Error inserting prices for') . ' ' . $myrow['stockid'] . ' ' . _('because');
					$result = DB_query($sql,$db,$ErrMsg);
					prnMsg(_('Inserting new price for') . ' ' . $myrow['stockid'] . ' ' . _('to') . ' ' . $RoundedPrice,'info');
				} //end if update or insert
			}// end if cost > 0
		}//end while loop around items in the category
	}
}
include('includes/footer.inc');
?>