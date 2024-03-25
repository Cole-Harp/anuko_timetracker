<?php

require('initialize.php');
require('/var/www/html/vendor/autoload.php');
import('form.Form');
import('form.ActionForm');
import('ttReportHelper');
import('ttGoogleSheets');



// Access checks.
if (!(ttAccessAllowed('view_own_reports') || ttAccessAllowed('view_reports') || ttAccessAllowed('view_all_reports') || ttAccessAllowed('view_client_reports'))) {
    header('Location: access_denied.php');
    exit();
}
$listOfSpreadsheets = ttGoogleSheets::fetchSpreadsheetDetails();
$folders = ttGoogleSheets::fetchFolders();

$form = new Form('googleSheetsForm');

$form->addInput(array('type'=>'combobox', 'name'=>'folderId', 'data'=>$folders, 'empty'=>array(''=>$i18n->get('dropdown.selectFolder'))));
$form->addInput(array('type'=>'combobox', 'name'=>'sheetId', 'data'=>$listOfSpreadsheets, 'empty'=>array(''=>$i18n->get('dropdown.select'))));
$form->addInput(['type' => 'text', 'name' => 'newSheet', 'attributes' => ['placeholder' => 'New Spreadsheet']]);
$form->addInput(['type' => 'submit', 'name' => 'btn_send', 'value' => 'Next']);
$form->addInput(['type' => 'submit', 'name' => 'btn_settings', 'value' => 'Settings']);


$bean = new ActionForm('sheetsBean', $form, $request);

if ($request->isPost()) {

  if ($request->getParameter('btn_settings')) {
      // Redirect to gs_settings.php
      header('Location: gs_settings.php');
      exit();
  }

  $selectedSheetId = $bean->getAttribute('sheetId');

  // $listOfSpreadsheets maps IDs to names.
  $selectedSheetName = isset($listOfSpreadsheets[$selectedSheetId]) ? $listOfSpreadsheets[$selectedSheetId] : null;

  $bean->saveDetachedAttribute('sheetName', $selectedSheetName);

  $bean->saveBean(); // Persists the changes.
  header('Location: gs_choose_tab.php');
  exit();
}

$smarty->assign(['title' => 'Time Tracker - Choose Spreadsheet', 'forms' => [$form->getName() => $form->toArray()], 'content_page_name' => 'gs_choose_sheet.tpl']);
$smarty->display('index.tpl');
