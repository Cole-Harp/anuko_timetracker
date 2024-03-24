<?php


// // Initialize the ActionForm with 'googleSheetsForm' as the form name.

// $bean = new ActionForm('reportBean', new Form('reportForm'), $request);
// // If we are in post, load the bean from session, as the constructor does it only in get.
// if ($request->isPost()) $bean->loadBean();

// $selectedSheetId = $form->getAttribute('selectedSheetId'); // Retrieve the saved spreadsheet ID

// // Fetch the tabs for the selected spreadsheet.
// $tabs = ttGoogleSheets::getTabs($selectedSheetId);

// // Setup form for displaying tabs.
// $form = new Form('tabsForm');
// $form->addInput([
//     'type' => 'combobox',
//     'name' => 'tabId',
//     'data' => array_combine($tabs, $tabs), // Assuming tabs are both keys and values
//     'empty' => ['' => 'Select a Tab']
// ]);
// $form->addInput(['type' => 'submit', 'name' => 'btn_select_tab', 'value' => 'Select Tab']);

// // Assign form to Smarty and display.
// $smarty->assign('title', 'Select a Tab');
// $smarty->assign('forms', [$form->getName() => $form->toArray()]);
// $smarty->display('choose_tab.tpl'); // Ensure this tpl file is set up to display the 'tabsForm'.

// Initialize the ActionForm with 'googleSheetsForm' as the form name.
require('initialize.php');
require('/var/www/html/vendor/autoload.php');
import('form.Form');
import('ttReportHelper');
import('ttGoogleSheets');

$selectedSheetId = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['sheetId'])) {
    // Retrieve and sanitize the spreadsheet ID from the POST data.
    $selectedSheetId = filter_var($_POST['sheetId'], FILTER_SANITIZE_STRING);
}

// Fetch the tabs for the selected spreadsheet.
$tabs = ttGoogleSheets::getTabs($selectedSheetId);

// Assign form data to Smarty and display.
$smarty->assign('title', 'Select a Tab');
$smarty->assign('tabs', $tabs);
$smarty->display('gs_choose_tab.tpl'); // Template file for selecting a tab.
$smarty->display('index.tpl');
