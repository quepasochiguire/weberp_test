<?php

include('includes/session.php');
$Title = _('Tipo Mayorista');
$ViewTopic = 'Tipo Mayorista';// Filename in ManualContents.php's TOC.
$BookMark = 'Tipo Mayorista';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		_('Administrar Tipos de Mayorista') . '" />' . ' ' .
		_('Administrar Tipos de Mayorista') . '</p>';

if(isset($_GET['SelectedGroup'])) {
	$SelectedGroup = $_GET['SelectedGroup'];
} elseif(isset($_POST['SelectedGroup'])) {
	$SelectedGroup = $_POST['SelectedGroup'];
}

if(isset($_POST['submit']) OR isset($_GET['remove']) OR isset($_GET['add']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	//first off validate inputs sensible

	if(isset($_POST['CodeMayo']) AND mb_strlen($_POST['CodeMayo'])<0) {
		$InputError = 1;
		prnMsg(_('El código del tipo de mayorista no puede estar vacio'),'error');
	}

	if(isset($_POST['NameMayo']) AND mb_strlen($_POST['NameMayo'])<0) {
		$InputError = 1;
		prnMsg(_('El nombre del tipo de mayorista no puede estar vacio'),'error');
	}


	// if $_POST['GroupName'] then it is a modification of a tax group name
	// else it is either an add or remove of taxgroup
	unset($sql);
	if(isset($_POST['NameMayo']) ) { // Update or Add a tax group
		if(isset($SelectedGroup)) { // Update a tax group
			$sql = "UPDATE typemayorist SET name = '". $_POST['NameMayo'] ."' , code = '". $_POST['CodeMayo'] ."'
					WHERE id = '".$SelectedGroup . "'";
			$ErrMsg = _('The update of the type wholesaler failed because');
			$SuccessMsg = _('The type of wholesaler was updated to') . ' ' . $_POST['NameMayo'];
		} else { // Add new tax group

			$result = DB_query("SELECT id, code, name
								FROM typemayorist
								WHERE name='" . $_POST['NameMayo'] . "'");
			if(DB_num_rows($result)==1) {
				prnMsg( _('A new wholesaler could not be added because a wholesaler already exists for') . ' ' . $_POST['GroupName'],'warn');
				unset($sql);
			} else {
				$sql = "INSERT INTO typemayorist (code, name)
						VALUES ('". $_POST['CodeMayo'] . "', '". $_POST['NameMayo'] . "')";
				$ErrMsg = _('The addition of the group failed because');
				$SuccessMsg = _('Se agrego un nuevo tipo de mayorista') . ' ' . $_POST['NameMayo'];
			}
		}
		unset($_POST['NameMayo']);
		unset($_POST['CodeMayo']);
		unset($SelectedGroup);
	} 
	// Need to exec the query
	if(isset($sql) AND $InputError != 1 ) {
		$result = DB_query($sql,$ErrMsg);
		if( $result ) {
			prnMsg( $SuccessMsg,'success');
		}
	}

} elseif(isset($_GET['Delete'])) {
	/* PREVENT DELETES IF DEPENDENT RECORDS IN 'debstormasters */

	$sql= "SELECT COUNT(*) FROM debtorsmaster WHERE typemayorist_id='" . $_GET['SelectedGroup'] . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);

	if($myrow[0]>0) {
		prnMsg( _('Cannot delete this type mayorist because some customers are setup using it'),'warn');
		echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('type mayorists referring to this customers data');
	} else {
	
		$sql="DELETE FROM typemayorist
					WHERE id='" . $_GET['SelectedGroup'] . "'";
		$result = DB_query($sql);
		$sql="DELETE FROM typemayorist
					WHERE id='" . $_GET['SelectedGroup'] . "'";
		$result = DB_query($sql);
		prnMsg( $_GET['GroupID'] . ' ' . _('type wholesaler has been deleted') . '!','success');
	}
	//end if typemayorists used in other tables
	unset($SelectedGroup);
	unset($_GET['NameMayo']);
	unset($_GET['CodeMayo']);
}

if(!isset($SelectedGroup)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax groups will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of tax group taxes*/

	$sql = "SELECT id,code,
					name
			FROM typemayorist";
	$result = DB_query($sql);

	if( DB_num_rows($result) == 0 ) {
		echo '<div class="centre">';
		prnMsg(_('No hay registros guardados para mostrar.'),'info');
		echo '</div>';
	} else {
		echo '<table class="selection">
			<thead>
				<tr>
					<th class="ascending" >' . _('Código') . '</th>
					<th class="ascending" >' . _('Nombre') . '</th>
					<th colspan="2" >&nbsp;</th>
				</tr>
			</thead>
			<tbody>';

		while($myrow = DB_fetch_array($result)) {
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td><a href="%s&amp;SelectedGroup=%s">' . _('Edit') . '</a></td>
					<td><a href="%s&amp;SelectedGroup=%s&amp;Delete=1&amp;GroupID=%s" onclick="return confirm(\'' . _('Are you sure you wish to delete this mayorist type?') . '\');">' . _('Delete') . '</a></td>
					</tr>',
					$myrow['code'],
					$myrow['name'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?',
					$myrow['id'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$myrow['id'],
					urlencode($myrow['name']));

		} //END WHILE LIST LOOP
		echo '</tbody></table>';
	}
} //end of ifs and buts!

if(isset($SelectedGroup)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Existing Groups') . '</a>
		</div>';
}

if(isset($SelectedGroup)) {
	//editing an existing role

	$sql = "SELECT id, code,
					name
			FROM typemayorist
			WHERE id='" . $SelectedGroup . "'";
	$result = DB_query($sql);
	if( DB_num_rows($result) == 0 ) {
		prnMsg( _('The selected type of wholesaler is no longer available.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['SelectedGroup'] = $myrow['id'];
		$_POST['NameMayo'] = $myrow['name'];
		$_POST['CodeMayo'] = $myrow['code'];

	}
}
echo '<br />';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if( isset($_POST['SelectedGroup'])) {
	echo '<input type="hidden" name="SelectedGroup" value="' . $_POST['SelectedGroup'] . '" />';
}
echo '<table class="selection">';

if(!isset($_POST['NameMayo'])) {
	$_POST['NameMayo']='';
}

if(!isset($_POST['CodeMayo'])) {
	$_POST['CodeMayo']='';
}
echo '<tr><td>' . _('Código') . ':</td>
		<td><input pattern="(?!^ +$)[^><+-]{4,}" title="'._('The group name must be more 4 and less than 40 characters and cannot be left blank').'" placeholder="'._('Introduzca código').'" type="text" name="CodeMayo" size="40" maxlength="40" value="' . $_POST['CodeMayo'] . '" /></td>';
echo '<tr><td>' . _('Nombre') . ':</td>
		<td><input pattern="(?!^ +$)[^><+-]{4,}" title="'._('The group name must be more 4 and less than 40 characters and cannot be left blank').'" placeholder="'._('Introduzca el nombre').'" type="text" name="NameMayo" size="40" maxlength="40" value="' . $_POST['NameMayo'] . '" /></td>';
echo '<td><input type="submit" name="submit" value="' . _('Guardar') . '" /></td>
	</tr>
    </table>
    <br />
    </div>
	</form>';


echo '<br />
	<div class="centre">
		<a href="' . $RootPath . '/TaxAuthorities.php">' . _('Tax Authorities and Rates Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxProvinces.php">' . _('Dispatch Tax Province Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxCategories.php">' . _('Tax Category Maintenance') .  '</a>
	</div>';

include('includes/footer.php');
?>
