<?php

require_once('initialize.php');
require_once('/var/www/html/vendor/autoload.php');
require_once('gs_api_control.php');
import('form.Form');
import('form.ActionForm');
import('ttReportHelper');

// Access checks.
if (!(ttAccessAllowed('view_own_reports') || ttAccessAllowed('view_reports') || ttAccessAllowed('view_all_reports')  || ttAccessAllowed('view_client_reports'))) {
  header('Location: access_denied.php');
  exit();
}
// End of access checks.

// $mdb2 = getConnection();

// $sql = "select spreadsheet_id from tt_google_sheets where user_id = $user->id";

// $result = $mdb2->query($sql);

// $dataArray = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

// $listOfSpreadsheets = [];
// foreach ($dataArray as $row) {
//   // Get the spreadsheet
//   $spreadsheet = $service->spreadsheets->get($row['spreadsheet_id']);
//   // Get the title of the spreadsheet
//   $spreadsheetName = $spreadsheet->getProperties()->getTitle();
//   // Name of spreadsheet
//   $listOfSpreadsheets[$row['spreadsheet_id']] = $spreadsheetName;
// }

$new_sheet_id = 'Hello';

$form = new Form('googleSheetsForm');
// $form->addInput(array('type'=>'combobox',
//   'name'=>'sheetId',
//   'data'=>$listOfSpreadsheets,
//   'empty'=>array(''=>$i18n->get('dropdown.select'))));
$form->addInput(array('type'=>'text','name'=>'newSheetId','value'=>$new_sheet_id));
$form->addInput(array('type'=>'submit','name'=>'btn_send','value'=>'Next'));

$smarty->assign('title', 'Time Tracker - Add Spreadsheet');
$smarty->assign('forms', array($form->getName()=>$form->toArray()));
$smarty->assign('content_page_name', 'gs_add_sheet.tpl');
$smarty->display('index.tpl');