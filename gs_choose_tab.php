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
// Get bean for the form
$bean = new ActionForm('sheetsBean', new Form('googleSheetsForm'), $request);

$selectedSheetId = $bean->getAttribute('sheetId');
$truncatedSheetId = substr($selectedSheetId, 0, 10) . '...';
$selectedSheetName = $bean->getDetachedAttribute('sheetName');

$folderId = $bean->getAttribute('folderId');
$newSheetName = $bean->getAttribute('newSheet');

$form = new Form('tabsForm');

// If there is a selected sheet, fetch its tabs.
if (!empty($selectedSheetId)) {
  $tabs = ttGoogleSheets::getTabs($selectedSheetId);
  $tabOptions = array_combine($tabs, $tabs);
  $form->addInput(['type' => 'combobox', 'name' => 'tabId', 'data' => $tabOptions, 'empty' => ['' => 'Select Tab']]);
} else {
  $tabOptions = [];
}

$form->addInput(['type' => 'text', 'name' => 'newTab', 'attributes' => ['placeholder' => 'New Tab Name']]);
$form->addInput(['type' => 'submit', 'name' => 'btn_select_tab', 'value' => 'Select Tab']);
// Hidden inputs for sheetId and folderId to persist data.  There might be a better way to do this
$form->addInput(['type' => 'hidden', 'name' => 'sheetId', 'value' => $selectedSheetId]);
$form->addInput(['type' => 'hidden', 'name' => 'folderId', 'value' => $folderId]);
$form->addInput(['type' => 'hidden', 'name' => 'newSheetName', 'value' => $newSheetName]);

$bean = new ActionForm('sheetsBean', $form, $request);

if ($request->isPost()) {
  $selectedTabId = $bean->getAttribute('tabId');
  $newTab = $bean->getAttribute('newTab');
  $newSheetName = $bean->getAttribute('newSheetName');
  if (($selectedTabId !== "" && $newTab === "") || ($newTab !== "" && $selectedTabId === "")) {    // Create new sheet, add it to local db, and validate it before passing it to the upload script
    if (!empty($newSheetName)){
      $newSheetId = ttGoogleSheets::createSheet($newSheetName, $bean->getAttribute('folderId'));
      ttGoogleSheets::add($user->id, $newSheetId);
      $bean->setAttribute('sheetId', $newSheetId);
    }
    $bean->saveBean();
    header('Location: gs_send.php');
    exit();
  }
  else {
    // Store error message in a session variable
    $_SESSION['error_message'] = 'Incorrect parameter selection: Choose a Tab OR a New Tab Name.';
    header('Location: gs_choose_sheet.php');
    exit();
  }
}

// Check for error message in session and assign it to smarty
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
  } else {
    $errorMessage = null;
  }

$smarty->assign('error_message', $errorMessage);
$smarty->assign([
    'title' => 'Time Tracker - Select or Create Tab',
    'selectedSheetId' => $truncatedSheetId,
    'selectedSheetName' => $selectedSheetName,
    'forms' => [$form->getName() => $form->toArray()],
    'content_page_name' => 'gs_choose_tab.tpl'
]);
$smarty->display('index.tpl');
