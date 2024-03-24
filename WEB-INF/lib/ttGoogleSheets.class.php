<?php

class ttGoogleSheets {

// getAll - retrieves all Google Sheets records.
static function getAll() {
    $mdb2 = getConnection();

    $sql = "SELECT id, user_id, spreadsheet_id FROM tt_google_sheets";
    $res = $mdb2->query($sql);

    $result = array();
    if (!is_a($res, 'PEAR_Error')) {
        while ($val = $res->fetchRow(MDB2_FETCHMODE_ASSOC)) {
            $result[] = $val;
        }
    }
    return $result;
}

// add - adds a new Google Sheet record.
static function add($user_id, $spreadsheet_id) {
    $mdb2 = getConnection();

    $sql = "INSERT INTO tt_google_sheets (user_id, spreadsheet_id) VALUES (?, ?)";
    $sth = $mdb2->prepare($sql);
    $res = $sth->execute(array($user_id, $spreadsheet_id));

    if (!is_a($res, 'PEAR_Error')) {
        return true; // Successfully added.
    }
    return false; // Failed to add.
}

// delete - deletes a Google Sheet record by id.
static function delete($id) {
    $mdb2 = getConnection();

    $sql = "DELETE FROM tt_google_sheets WHERE id = ?";
    $sth = $mdb2->prepare($sql);
    $res = $sth->execute(array($id));

    if (!is_a($res, 'PEAR_Error')) {
        return true; // Successfully deleted.
    }
    return false; // Failed to delete.
}

static function getTabs($id) {
    $service_account_file = '/var/www/html/credentials.json';
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $service_account_file);

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $service = new Google_Service_Sheets($client);

    $spreadsheetDetails = $service->spreadsheets->get($id);
    $tabs = [];

    foreach ($spreadsheetDetails->getSheets() as $sheet) {
        $sheetTitle = $sheet->getProperties()->getTitle();
        $tabs[] = $sheetTitle;
    }

    return $tabs;
    }

}
