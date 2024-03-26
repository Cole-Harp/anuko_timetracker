<?php
require('initialize.php');
require('/var/www/html/vendor/autoload.php');
import('form.Form');
import('form.ActionForm');
import('ttReportHelper');
import('ttGoogleSheets');

$bean = new ActionForm('sheetsBean', new Form('googleSheetsForm'), $request);

if ($request->isPost()) {
    $bean->loadBean();
}

# For existing sheet update
$selectedSheetId = $bean->getAttribute('sheetId');
$selectedSheetName = $bean->getDetachedAttribute('sheetName');
# For new sheet creation
$folderId = $bean->getAttribute('folderId');
$newSheetName = $bean->getAttribute('newSheet');

$bean->destroyBean();



// If there is a selected sheet, fetch its tabs.
if (!empty($selectedSheetId)) {
    $tabs = ttGoogleSheets::getTabs($selectedSheetId);
    $tabOptions = array_combine($tabs, $tabs);
} else {
    $tabOptions = [];
}

$form = new Form('tabsForm');

$form->addInput(['type' => 'combobox','name' => 'tabId','data' => $tabOptions,'empty' => ['' => 'Select a Tab or Enter New Tab Name Below']]);
$form->addInput(['type' => 'text', 'name' => 'newTab', 'attributes' => ['placeholder' => 'New Tab Name']]);
$form->addInput(['type' => 'submit', 'name' => 'btn_select_tab', 'value' => 'Select Tab']);
// Hidden inputs for sheetId and folderId to persist data.  There might be a better way to do this. like adding based on some logic.
$form->addInput(['type' => 'hidden', 'name' => 'sheetId', 'value' => $selectedSheetId]);
$form->addInput(['type' => 'hidden', 'name' => 'folderId', 'value' => $folderId]);
$form->addInput(['type' => 'hidden', 'name' => 'newSheetName', 'value' => $newSheetName]);

$bean = new ActionForm('sheetsBean', $form, $request);


if ($request->isPost()) {
    if (($selectedTabId && !$newTab) || ($newTab && !$selectedTabId)) { 
    // Create new sheet and add it to local db before passing it to the update script
        if (!empty($newSheetName)){
            $addSheetId = ttGoogleSheets::createSheet($newSheetName, $bean->getAttribute('folderId'));
            ttGoogleSheets::add($user->id, $addSheetId);
            $bean->setAttribute('sheetId', $addSheetId);
            }

        $bean->saveBean();
        header('Location: gs_send.php');
        exit();
    }
}


$smarty->assign([
    'title' => 'Time Tracker - Select or Create Tab',
    'selectedSheetId' => $selectedSheetId,
    'selectedSheetName' => $selectedSheetName,
    'forms' => [$form->getName() => $form->toArray()],
    'content_page_name' => 'gs_choose_tab.tpl'
]);
$smarty->display('index.tpl');
