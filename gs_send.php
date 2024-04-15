<?php

require_once('initialize.php');
require('/var/www/html/vendor/autoload.php');
import('form.Form');
import('form.ActionForm');
import('ttReportHelper');
import('ttGoogleSheets');

// Access checks.
if (!(ttAccessAllowed('view_own_reports') || ttAccessAllowed('view_reports') || ttAccessAllowed('view_all_reports')  || ttAccessAllowed('view_client_reports'))) {
  header('Location: access_denied.php');
  exit();
}
// End of access checks.

// Use custom fields plugin if it is enabled.
if ($user->isPluginEnabled('cf')) {
  require_once('plugins/CustomFields.class.php');
  $custom_fields = new CustomFields();
}

$show_cost_per_hour = $user->getConfigOption('report_cost_per_hour') && ($user->can('manage_invoices') || $user->isClient());

// Report settings are stored in session bean before we get here.
$bean = new ActionForm('reportBean', new Form('reportForm'), $request);

// There are 2 variations of report: totals only, or normal. Totals only means that the report
// is grouped by (either date, user, client, project, or task) and user only needs to see subtotals by group.
$totals_only = $bean->getAttribute('chtotalsonly');

// Obtain items.
$options = ttReportHelper::getReportOptions($bean);
if ($totals_only)
  $subtotals = ttReportHelper::getSubtotals($options);
else
  $items = ttReportHelper::getItems($options);

// Build a string to use as filename for the files being downloaded.
$filename = strtolower($i18n->get('title.report')).'_'.$bean->mValues['start_date'].'_'.$bean->mValues['end_date'];

header('Pragma: public'); // This is needed for IE8 to download files over https.
header('Content-Type: text/html; charset=utf-8');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Cache-Control: private', false);

// IMPORTANT: Probably won't need these two headers below anymore, but keep them here for reference until we are done.
// header('Content-Type: application/csv');
// header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

$csv_to_export = "";

// Print UTF8 BOM first to identify encoding.
$bom = chr(239).chr(187).chr(191); // 0xEF 0xBB 0xBF in the beginning of the file is UTF8 BOM.
// $csv_to_export = $csv_to_export . $bom; // Without this Excel does not display UTF8 characters properly.

if ($totals_only) {
  // Totals only report.
  $group_by_header = ttReportHelper::makeGroupByHeader($options);

  // Print headers.
  $csv_to_export = $csv_to_export . '"'.$group_by_header.'"';
  if ($bean->getAttribute('chduration')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.duration').'"';
  if ($bean->getAttribute('chunits')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.work_units_short').'"';
  if ($bean->getAttribute('chcost')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.cost').'"';
  $csv_to_export = $csv_to_export . "\n";

  // Print subtotals.
  foreach ($subtotals as $subtotal) {
    $csv_to_export = $csv_to_export . '"'.$subtotal['name'].'"';
    if ($bean->getAttribute('chduration')) {
      $val = $subtotal['time'];
      if($val && isTrue('EXPORT_DECIMAL_DURATION'))
        $val = time_to_decimal($val);
      $csv_to_export = $csv_to_export . ',"'.$val.'"';
    }
    if ($bean->getAttribute('chunits')) $csv_to_export = $csv_to_export . ',"'.$subtotal['units'].'"';
    if ($bean->getAttribute('chcost')) {
      if ($user->can('manage_invoices') || $user->isClient())
        $csv_to_export = $csv_to_export . ',"'.$subtotal['cost'].'"';
      else
        $csv_to_export = $csv_to_export . ',"'.$subtotal['expenses'].'"';
    }
    $csv_to_export = $csv_to_export . "\n";
  }
} else {
  // Normal report. Print headers.
  $csv_to_export = $csv_to_export . '"'.$i18n->get('label.date').'"';
  if ($user->can('view_reports') || $user->can('view_all_reports') || $user->isClient()) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.user').'"';
  // User custom field labels.
  if (isset($custom_fields) && $custom_fields->userFields) {
    foreach ($custom_fields->userFields as $userField) {
      $field_name = 'user_field_'.$userField['id'];
      $checkbox_control_name = 'show_'.$field_name;
      if ($bean->getAttribute($checkbox_control_name)) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($userField['label']).'"';
    }
  }
  if ($bean->getAttribute('chclient')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.client').'"';
  if ($bean->getAttribute('chproject')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.project').'"';
  // Project custom field labels.
  if (isset($custom_fields) && $custom_fields->projectFields) {
    foreach ($custom_fields->projectFields as $projectField) {
      $field_name = 'project_field_'.$projectField['id'];
      $checkbox_control_name = 'show_'.$field_name;
      if ($bean->getAttribute($checkbox_control_name)) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($projectField['label']).'"';
    }
  }
  if ($bean->getAttribute('chtask')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.task').'"';
  // Time custom field labels.
  if (isset($custom_fields) && $custom_fields->timeFields) {
    foreach ($custom_fields->timeFields as $timeField) {
      $field_name = 'time_field_'.$timeField['id'];
      $checkbox_control_name = 'show_'.$field_name;
      if ($bean->getAttribute($checkbox_control_name)) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($timeField['label']).'"';
    }
  }
  if ($bean->getAttribute('chstart')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.start').'"';
  if ($bean->getAttribute('chfinish')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.finish').'"';
  if ($bean->getAttribute('chduration')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.duration').'"';
  if ($bean->getAttribute('chunits')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.work_units_short').'"';
  if ($bean->getAttribute('chnote')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.note').'"';
  if ($bean->getAttribute('chcost')) {
    if ($show_cost_per_hour)
      $csv_to_export = $csv_to_export . ',"'.$i18n->get('form.report.per_hour').'"';
    $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.cost').'"';
  }
  if ($bean->getAttribute('chapproved')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.approved').'"';
  if ($bean->getAttribute('chpaid')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.paid').'"';
  if ($bean->getAttribute('chip')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.ip').'"';
  if ($bean->getAttribute('chinvoice')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.invoice').'"';
  if ($bean->getAttribute('chtimesheet')) $csv_to_export = $csv_to_export . ',"'.$i18n->get('label.timesheet').'"';
  $csv_to_export = $csv_to_export . "\n";

  // Print items.
  foreach ($items as $item) {
    $csv_to_export = $csv_to_export . '"'.$item['date'].'"';
    if ($user->can('view_reports') || $user->can('view_all_reports') || $user->isClient()) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($item['user']).'"';
    // User custom fields.
    if (isset($custom_fields) && $custom_fields->userFields) {
      foreach ($custom_fields->userFields as $userField) {
        $field_name = 'user_field_'.$userField['id'];
        $checkbox_control_name = 'show_'.$field_name;
        if ($bean->getAttribute($checkbox_control_name)) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($item[$field_name]).'"';
      }
    }
    if ($bean->getAttribute('chclient')) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($item['client']).'"';
    if ($bean->getAttribute('chproject')) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($item['project']).'"';
    // Project custom fields.
    if (isset($custom_fields) && $custom_fields->projectFields) {
      foreach ($custom_fields->projectFields as $projectField) {
        $field_name = 'project_field_'.$projectField['id'];
        $checkbox_control_name = 'show_'.$field_name;
        if ($bean->getAttribute($checkbox_control_name)) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($item[$field_name]).'"';
      }
    }
    if ($bean->getAttribute('chtask')) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($item['task']).'"';
    // Time custom fields.
    if (isset($custom_fields) && $custom_fields->timeFields) {
      foreach ($custom_fields->timeFields as $timeField) {
        $field_name = 'time_field_'.$timeField['id'];
        $checkbox_control_name = 'show_'.$field_name;
        if ($bean->getAttribute($checkbox_control_name)) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($item[$field_name]).'"';
      }
    }
    if ($bean->getAttribute('chstart')) $csv_to_export = $csv_to_export . ',"'.$item['start'].'"';
    if ($bean->getAttribute('chfinish')) $csv_to_export = $csv_to_export . ',"'.$item['finish'].'"';
    if ($bean->getAttribute('chduration')) {
      $val = $item['duration'];
      if($val && isTrue('EXPORT_DECIMAL_DURATION'))
        $val = time_to_decimal($val);
      $csv_to_export = $csv_to_export . ',"'.$val.'"';
    }
    if ($bean->getAttribute('chunits')) $csv_to_export = $csv_to_export . ',"'.$item['units'].'"';
    if ($bean->getAttribute('chnote')) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($item['note']).'"';
    if ($bean->getAttribute('chcost')) {
      if ($user->can('manage_invoices') || $user->isClient()) {
        if ($show_cost_per_hour)
          $csv_to_export = $csv_to_export . ',"'.$item['cost_per_hour'].'"';
        $csv_to_export = $csv_to_export . ',"'.$item['cost'].'"';
      } else {
        $csv_to_export = $csv_to_export . ',"'.$item['expense'].'"';
      }
    }
    if ($bean->getAttribute('chapproved')) $csv_to_export = $csv_to_export . ',"'.$item['approved'].'"';
    if ($bean->getAttribute('chpaid')) $csv_to_export = $csv_to_export . ',"'.$item['paid'].'"';
    if ($bean->getAttribute('chip')) {
      $ip = $item['modified'] ? $item['modified_ip'].' '.$item['modified'] : $item['created_ip'].' '.$item['created'];
      $csv_to_export = $csv_to_export . ',"'.$ip.'"';
    }
    if ($bean->getAttribute('chinvoice')) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($item['invoice']).'"';
    if ($bean->getAttribute('chtimesheet')) $csv_to_export = $csv_to_export . ',"'.ttNeutralizeForCsv($item['timesheet_name']).'"';
    $csv_to_export = $csv_to_export . "\n";
  }
}
try {
  $destination = new ActionForm('sheetsBean', new Form('tabsForm'), $request);
  $destination->loadBean();

  $spreadsheet_id = $destination->getAttribute('sheetId');
  // Proceed with existing tab or newTab logic
  $existingTab = $destination->getDetachedAttribute('tabId');
  $newTab = $destination->getDetachedAttribute('newTab');
  $spreadsheet_range = !empty($newTab) ? $newTab : $existingTab;

  // Prepare the data to update
  $lines = explode("\n", $csv_to_export);
  $values = array_map(function($v) {
      return str_getcsv($v, ",");
  }, $lines);

  $service = ttGoogleSheets::getSheetsService();

  // If newTab is specified, add the new tab
  if (!empty($newTab)) {
      $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
          'requests' => [
              [
                  'addSheet' => [
                      'properties' => ['title' => $newTab]
                  ]
              ]
          ]
      ]);
      $service->spreadsheets->batchUpdate($spreadsheet_id, $batchUpdateRequest);
  }

  $valueRange = new Google_Service_Sheets_ValueRange(['values' => $values]);
  $conf = ["valueInputOption" => "RAW"];
  
  // Update the spreadsheet/tab with the data
  $result = $service->spreadsheets_values->update($spreadsheet_id, $spreadsheet_range, $valueRange, $conf);

  // Redirect back to the report page.
  header('Location: report.php');
} catch (Exception $e) {
  echo 'Caught exception: ' . $e->getMessage() . PHP_EOL;
}