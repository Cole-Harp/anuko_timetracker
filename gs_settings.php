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

$form = new Form('settingsForm');
// Adding only the necessary input for existing Spreadsheet ID
$form->addInput(['type' => 'text', 'name' => 'addSheetId', 'attributes' => ['placeholder' => 'Existing Spreadsheet ID']]);
$form->addInput(['type' => 'submit', 'name' => 'btn_add_sheet', 'value' => 'Add Sheet']);
$form->addInput(array('type'=>'combobox', 'name'=>'sheetId', 'data'=>$listOfSpreadsheets, 'empty'=>array(''=>$i18n->get('dropdown.select'))));
$form->addInput(['type' => 'submit', 'name' => 'btn_delete_sheet', 'value' => 'Delete Sheet']);

$bean = new ActionForm('sheetsBean', $form, $request);

if ($request->isPost() && !empty($bean->getAttribute('addSheetId'))) {
    $addSheetId = $bean->getAttribute('addSheetId');
    if (ttGoogleSheets::add($user->id, $addSheetId)) {
        header('Location: gs_choose_sheet.php');
        exit();
    } else {
        echo "Failed to add the existing sheet ID.";
    }
}
if ($request->isPost() && !empty($bean->getAttribute('sheetId'))) {

    $spreadsheetToDelete = $bean->getAttribute('sheetId');
    if (ttGoogleSheets::delete($spreadsheetToDelete)) {
        // Handle successful addition, e.g., redirect or show a success message.
        header('Location: gs_choose_sheet.php');
        exit();
    } else {
        // Handle failed addition, e.g., redirect or show an error message.
        echo "Failed to add the existing sheet ID.";
    }
}
  

// Assign form to Smarty and display.
$smarty->assign([
  'title' => 'Add Existing Google Sheet',
  'forms' => [$form->getName() => $form->toArray()],
  'content_page_name' => 'gs_settings.tpl' // Make sure to create this template.
]);
$smarty->display('index.tpl');
