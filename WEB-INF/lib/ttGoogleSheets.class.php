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

    static function getSheetsService() {
        self::initializeGoogleServices();
        return self::$sheetsService;
    }

    // adds a new Google Sheet record.
    static function add($user_id, $spreadsheet_id) {
        $mdb2 = getConnection();
        $sql = "insert into tt_google_sheets (user_id, spreadsheet_id) values ($user_id, " . $mdb2->quote($spreadsheet_id) . ")";
        $affectedRows = $mdb2->exec($sql);

        if (!is_a($affectedRows, 'PEAR_Error')) {
            return true; // Successfully added.
        } else {
            error_log('Error in add operation at ttGoogleSheet');
            return false; // Failed to add.
        }
    }


    // deletes a Google Sheet record by spreadsheet_id.
    static function delete($spreadsheetId) {
        $mdb2 = getConnection();
        $sql = "delete from tt_google_sheets where spreadsheet_id = " . $mdb2->quote($spreadsheetId, 'text');
        $affectedRows = $mdb2->exec($sql);
    }

    // retrieves all Google Sheets shared to serbice bot.
    public static function fetchSpreadsheetDetails() {

        $mdb2 = getConnection();
        $sql = "SELECT spreadsheet_id FROM tt_google_sheets";
        $res = $mdb2->query($sql);
        $dataArray = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        try {
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
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        return $listOfSpreadsheets;
    }

    // retrieves all tabs from a Google Sheet by spreadsheet_id.
    public static function getTabs($id) {
        self::initializeGoogleServices(); 
        $spreadsheetDetails = self::$sheetsService->spreadsheets->get($id);
        $tabs = [];

        foreach ($spreadsheetDetails->getSheets() as $sheet) {
            $sheetTitle = $sheet->getProperties()->getTitle();
            $tabs[] = $sheetTitle;
        }

        return $tabs;
    }


    public static function createSheet($sheetName, $folderId) {
        self::initializeGoogleServices();

        // Create a new spreadsheet using Google Sheets API
        $spreadsheetProperties = new Google_Service_Sheets_SpreadsheetProperties();
        $spreadsheetProperties->setTitle($sheetName);

        $spreadsheet = new Google_Service_Sheets_Spreadsheet();
        $spreadsheet->setProperties($spreadsheetProperties);

        try {
            $spreadsheet = self::$sheetsService->spreadsheets->create($spreadsheet);
            $spreadsheetId = $spreadsheet->getSpreadsheetId();

            // Move the spreadsheet to the specified folder using Google Drive API
            $emptyFileMetadata = new Google_Service_Drive_DriveFile();
            $currentParents = self::$driveService->files->get($spreadsheetId, ['fields' => 'parents'])->parents;
            $file = self::$driveService->files->update($spreadsheetId, $emptyFileMetadata, [
                'addParents' => $folderId,
                'removeParents' => join(',', $currentParents),
                'fields' => 'id, parents',
            ]);
            
            return $spreadsheetId;
        } catch (Exception $e) {
            // Consider better error handling or logging here
            echo 'Error creating spreadsheet: ' . $e->getMessage();
            return null; // Or handle the error as appropriate
        }
    }
    
    // retrieves all Google Drive folders shared to service bot.
    public static function fetchFolders() {
        self::initializeGoogleServices();

        $folders = [];
        try {
            $optParams = [
                'pageSize' => 10,
                'fields' => 'nextPageToken, files(id, name)',
                'q' => "mimeType='application/vnd.google-apps.folder'"
            ];
            $results = self::$driveService->files->listFiles($optParams);
            foreach ($results->getFiles() as $file) {
                $folders[$file->getId()] = $file->getName();
            }
        } catch (Exception $e) {
            echo 'An error occurred: ' . $e->getMessage();
        }

        return $folders;
    }

    public static function validateSheet($spreadsheet_id) {
        self::initializeGoogleServices();
        try {
            $spreadsheet = self::$sheetsService->spreadsheets->get($spreadsheet_id);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
