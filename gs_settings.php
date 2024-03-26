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

if ($request->isPost()) {
    // fill Here
}

// Assign form to Smarty and display.
$smarty->assign([
  'title' => 'Add Existing Google Sheet',
  'forms' => [$form->getName() => $form->toArray()],
  'content_page_name' => 'gs_settings.tpl' // Make sure to create this template.
]);
$smarty->display('index.tpl');
