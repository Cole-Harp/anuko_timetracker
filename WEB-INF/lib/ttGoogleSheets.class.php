<?php

class ttGoogleSheets {
    private static $sheetsService = null;
    private static $driveService = null;

    // Initialize Google Client and Services (Sheets and Drive)
    private static function initializeGoogleServices() {
        if (self::$sheetsService === null || self::$driveService === null) {
            $serviceAccountFile = '/var/www/html/credentials.json';
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $serviceAccountFile);

            $client = new Google_Client();
            $client->useApplicationDefaultCredentials();
            $client->setScopes([
                Google_Service_Sheets::SPREADSHEETS,
                Google_Service_Drive::DRIVE,
            ]);

            self::$sheetsService = new Google_Service_Sheets($client);
            self::$driveService = new Google_Service_Drive($client);
        }
    }

    static public function getSheetsService() {
        self::initializeGoogleServices();
        return self::$sheetsService;
    }

    // adds a new Google Sheet record.
    static function add($user_id, $spreadsheet_id) {
        #self validate fuction right here, only trigger the add if it is validated.
        #check if the spreadsheet id is already in the database
        try {
            $mdb2 = getConnection();
            $sql = "select count(*) from tt_google_sheets where spreadsheet_id = " . $mdb2->quote($spreadsheetId, 'text');
            $result = $mdb2->query($sql);
            $count = $result->fetchRow();

            if (self::validateSheet($spreadsheet_id) && ($count < 1)) {
                $sql = "insert into tt_google_sheets (user_id, spreadsheet_id) values ($user_id, " . $mdb2->quote($spreadsheet_id) . ")";
                $affectedRows = $mdb2->exec($sql);

                if (!is_a($affectedRows, 'PEAR_Error')) {
                    return true; // Successfully added.
                } else {
                    throw new Exception('Error in add operation at ttGoogleSheet');
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }


    // deletes a Google Sheet record by spreadsheet_id.
    static function delete($spreadsheetId) {
        try {
            $mdb2 = getConnection();
            $sql = "delete from tt_google_sheets where spreadsheet_id = " . $mdb2->quote($spreadsheetId, 'text');
            $affectedRows = $mdb2->exec($sql);

            if (is_a($affectedRows, 'PEAR_Error')) {
                throw new Exception('Error in delete operation at ttGoogleSheet');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // retrieves all Google Sheets shared to serbice bot.
    public static function fetchSpreadsheetDetails() {
        try {
            $mdb2 = getConnection();
            $sql = "SELECT spreadsheet_id FROM tt_google_sheets";
            $res = $mdb2->query($sql);
            $dataArray = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
            self::initializeGoogleServices(); 
            $listOfSpreadsheets = [];

            foreach ($dataArray as $row) {
                // Get the spreadsheet
                $spreadsheet = self::$sheetsService->spreadsheets->get($row['spreadsheet_id']);
                // Get the title of the spreadsheet
                $spreadsheetName = $spreadsheet->getProperties()->getTitle();
                // Name of spreadsheet
                $listOfSpreadsheets[$row['spreadsheet_id']] = $spreadsheetName;
            }
            return $listOfSpreadsheets;
        } catch (Exception $e) {
            error_log('Caught exception in fetchSpreadsheetDetails: ' . $e->getMessage());
            return [];
        }
    }

    // retrieves all tabs from a Google Sheet by spreadsheet_id.
    public static function getTabs($id) {
        try {
            self::initializeGoogleServices(); 
            $spreadsheetDetails = self::$sheetsService->spreadsheets->get($id);
            $tabs = [];
            foreach ($spreadsheetDetails->getSheets() as $sheet) {
                $sheetTitle = $sheet->getProperties()->getTitle();
                $tabs[] = $sheetTitle;
            }
            return $tabs;
        } catch (Exception $e) {
            error_log('Caught exception in getTabs: ' . $e->getMessage());
            return [];
        }
    }

    // validates a Google Sheet by spreadsheet_id
    public static function validateSheet($spreadsheet_id) {
        try {
            self::initializeGoogleServices();
            $spreadsheet = self::$sheetsService->spreadsheets->get($spreadsheet_id);
            return true;
        } catch (Exception $e) {
            error_log('An error occurred in validateSheet: ' . $e->getMessage());
            return false;
        }
    }
}
