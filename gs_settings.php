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

$form = new Form('googleSheetsForm');

$form->addInput(array('type'=>'combobox', 'name'=>'sheetId', 'data'=>$listOfSpreadsheets, 'empty'=>array(''=>$i18n->get('dropdown.select'))));
$form->addInput(['type' => 'text', 'name' => 'newSheet', 'attributes' => ['placeholder' => 'New Spreadsheet']]);
$form->addInput(['type' => 'submit', 'name' => 'btn_add', 'value' => 'Add']);
$form->addInput(['type' => 'submit', 'name' => 'btn_delete', 'value' => 'Delete']);
$form->addInput(['type' => 'submit', 'name' => 'btn_back', 'value' => 'Back']);


$bean = new ActionForm('sheetsBean', $form, $request);

if ($request->isPost()) {

  if ($request->getParameter('btn_add')) {
    $newSheetId = $bean->getAttribute('newSheet');
    ttGoogleSheets::add($user->id, $newSheetId);
  }
  elseif ($request->getParameter('btn_delete')) {
    $selectedSheetId = $bean->getAttribute('sheetId');
    ttGoogleSheets::delete($selectedSheetId);
  }
  elseif ($request->getParameter('btn_back')) {
    header('Location: gs_choose_sheet.php');
    exit();
  }
}




$smarty->assign(['title' => 'Time Tracker - Choose Spreadsheet',
  'forms' => [$form->getName() => $form->toArray()], 
  'content_page_name' => 'gs_settings.tpl']);
$smarty->display('index.tpl');