<?php

require('initialize.php');
require('/var/www/html/vendor/autoload.php');
// Assuming gs_api_control.php is not required if we're not making API calls in this script.
import('form.Form');
import('form.ActionForm');
import('ttReportHelper');
import('ttGoogleSheets');

// Access checks.
if (!(ttAccessAllowed('view_own_reports') || ttAccessAllowed('view_reports') || ttAccessAllowed('view_all_reports') || ttAccessAllowed('view_client_reports'))) {
    header('Location: access_denied.php');
    exit();
}




$form = new Form('googleSheetsForm');
$spreadsheetDetailsArray = [];

try {
  $service_account_file = '/var/www/html/credentials.json';
  putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

  $client = new Google_Client();
  $client->useApplicationDefaultCredentials();
  $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
  $service = new Google_Service_Sheets($client);

  $dataArray = ttGoogleSheets::getAll(); // Fetch all spreadsheet details.

  $listOfSpreadsheets = [];
  foreach ($dataArray as $row) {
      $spreadsheet_id = $row['spreadsheet_id'];
      $spreadsheetDetails = $service->spreadsheets->get($spreadsheet_id);
      $title = $spreadsheetDetails->getProperties()->getTitle();
      $listOfSpreadsheets[$spreadsheet_id] = $title;
  }
} catch (Exception $e) {
  echo 'Caught exception: ',  $e->getMessage(), "\n";
}



//   // Add form inputs for Spreadsheet ID, New Spreadsheet, Sheet Tab, and New Sheet Tab
//   $form->addInput(array('type'=>'combobox',
//   'name'=>'sheetId',
//   'data'=>$listOfSpreadsheets,
//   'empty'=>array(''=>$i18n->get('dropdown.select'))));
//   $form->addInput(['type' => 'text', 'name' => 'newSheet', 'attributes' => ['placeholder' => 'New Spreadsheet']]);
//   $form->addInput(['type' => 'submit', 'name' => 'btn_send', 'value' => 'Next']);

//   $bean = new ActionForm('sheetsBean', $form, $request);


// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//   // Assuming 'sheetId' is the name attribute of your spreadsheet dropdown.
//   $selectedSheetId = $_POST['sheetId']; // Make sure to validate and sanitize input.
//   $bean->setAttribute('selectedSheetId', $selectedSheetId);
//   $bean->saveBean(); // Save the selected sheet ID in the session.

//   header('Location: gs_choose_tab.php'); // Redirect to the tab selection script.
//   exit();
// }

$smarty->assign('title', 'Time Tracker - Choose Spreadsheet');
$smarty->assign('listOfSpreadsheets', $listOfSpreadsheets);
$smarty->assign('content_page_name', 'gs_choose_sheet.tpl');
$smarty->display('index.tpl');

