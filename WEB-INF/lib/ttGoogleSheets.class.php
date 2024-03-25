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

    // retrieves all Google Sheets records.
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

    // adds a new Google Sheet record.
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


    // deletes a Google Sheet record by spreadsheet_id.
    static function delete($spreadsheetId) {
        $mdb2 = getConnection();

        // Adjust the SQL to target spreadsheet_id instead of id
        $sql = "DELETE FROM tt_google_sheets WHERE spreadsheet_id = ?";
        $sth = $mdb2->prepare($sql);
        $res = $sth->execute(array($spreadsheetId));

        if (!is_a($res, 'PEAR_Error')) {
            return true;
        }
        return false;
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
    // retrieves all Google Sheets shared to serbice bot.
    public static function fetchSpreadsheetDetails() {
        self::initializeGoogleServices(); 

        $listOfSpreadsheets = [];
        try {
            $dataArray = self::getAll();

            foreach ($dataArray as $row) {
                $spreadsheetId = $row['spreadsheet_id'];
                $spreadsheetDetails = self::$sheetsService->spreadsheets->get($spreadsheetId);
                $title = $spreadsheetDetails->getProperties()->getTitle();
                $listOfSpreadsheets[$spreadsheetId] = $title;
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        return $listOfSpreadsheets;
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


}
