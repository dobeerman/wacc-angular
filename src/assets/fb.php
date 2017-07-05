<?php
// ERP by month: http://www.stern.nyu.edu/%7Eadamodar/pc/datasets/betaemerg.xls
// Industry's unlevered beta: http://www.stern.nyu.edu/~adamodar/pc/implprem/ERPbymonth.xls
// http://www.cbr.ru/statistics/b_sector/loans_nonfin_17.xlsx

require __DIR__ .'/firebaselib/firebaseLib.php';

const DEFAULT_URL = 'https://wacc-bf98e.firebaseio.com/';
// const DEFAULT_TOKEN = 'tWn3X3H7o7bhqHKA9L3KcDyOTRYnZzwTfkfPs0Mp'; // https://my-first-fb-proj.firebaseio.com/
const DEFAULT_TOKEN = 'vNcv1KWOBTjqFzH1O066eJOgFgJcoh7YDrZAIvIk';
const DEFAULT_PATH = '/messages';
const DATA_PATH = '/data';
const CBR_DAILYINFO= 'http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL';

$firebase = new \Firebase\FirebaseLib(DEFAULT_URL, DEFAULT_TOKEN);


$func = htmlspecialchars($_GET["run"]);

if (function_exists($func)) {
    $func($firebase);
}

/**
 * Daily cron run
 */
// getUSDrate($firebase);
// getCBOE($firebase);
// getRusBondsData($firebase);

/**
 * Monthly cron run
 */
// parseBetaEmerg($firebase);
// getNonfin($firebase);

/**
 * Manual cron run
 */
// setCaps($firebase);

die();

function getRusBondsData(&$firebase)
{
    // Retrieve rusbonds
    
    $str = file_get_contents('http://www.rusbonds.ru/tyield.asp?tool=124705');

    $dom = new DOMDocument();//Loads DOM document
    $dom->loadHTML($str);//Loads HTML from a previously set variable
    $xpath = new DOMXPath($dom);
    $tables = $xpath->query('//table[@class="tbl_data"]');//Get only table from HTML

    $commsTable = '';
    $commsTable = $dom->saveXML($tables->item(0));//Get only first item of $tables

    $commsHTML = new DOMDocument();
    $commsHTML->loadHTML($commsTable);

    $td = $commsHTML->getElementsByTagName('td');
    $rusbonds = (float)str_replace(',', '.', $td->item(11)->nodeValue); //Convert string to float

    $firebase->update(DATA_PATH, array('rusbonds' => $rusbonds)); // Will be created if null
    

    // Retrieve yield
    $str = file_get_contents('http://www.rusbonds.ru/tyield.asp?tool=120973');

    $dom = new DOMDocument();//Loads DOM document
    $dom->loadHTML($str);//Loads HTML from a previously set variable
    $xpath = new DOMXPath($dom);
    $tables = $xpath->query('//table[@class="tbl_data"]');//Get only table from HTML

    $commsTable = '';
    $commsTable = $dom->saveXML($tables->item(0));//Get only first item of $tables

    $commsHTML = new DOMDocument();
    $commsHTML->loadHTML($commsTable);

    $td = $commsHTML->getElementsByTagName('td');
    $yield = (float)str_replace(',', '.', $td->item(11)->nodeValue); //Convert string to float

    $firebase->update(DATA_PATH, array('yield' => $yield, 'UPD/'. __FUNCTION__ => date('d/m/y'))); // Will be created if null
}

function setCaps(&$firebase)
{
    $caps = [
        'huge' => [
            'min' => 7687,
            'max' => 100000000000,
            'bonus' => 0
        ],
        'middle' => [
            'min' => 1911,
            'max' => 7686,
            'bonus' => 0.0112
        ],
        'small' => [
            'min' => 515,
            'max' => 1910,
            'bonus' => 0.0185
        ],
        'micro' => [
            'min' => 0,
            'max' => 514,
            'bonus' => 0.0381
        ],
    ];

    // Update caps
    $firebase->update(DATA_PATH, array('caps' => $caps)); // Will be created if null

    $spreadAbove5b = [
        ['min' => 8.50,         'max' => 100000, 'rate' => 'Aaa/AAA',   'spread' => 0.0060],
        ['min' => 6.50,         'max' => 8.50,   'rate' => 'Aa2/AA',    'spread' => 0.0080],
        ['min' => 5.50,         'max' => 6.50,   'rate' => 'A1/A+',     'spread' => 0.0100],
        ['min' => 4.25,         'max' => 5.50,   'rate' => 'A2/A',      'spread' => 0.0110],
        ['min' => 3.00,         'max' => 4.25,   'rate' => 'A3/A-',     'spread' => 0.0125],
        ['min' => 2.50,         'max' => 3.00,   'rate' => 'Baa2/BBB',  'spread' => 0.0160],
        ['min' => 2.25,         'max' => 2.50,   'rate' => 'Ba1/BB+',   'spread' => 0.0250],
        ['min' => 2.00,         'max' => 2.25,   'rate' => 'Ba2/BB',    'spread' => 0.0300],
        ['min' => 1.75,         'max' => 2.00,   'rate' => 'B1/B+',     'spread' => 0.0375],
        ['min' => 1.50,         'max' => 1.75,   'rate' => 'B2/B',      'spread' => 0.0450],
        ['min' => 1.25,         'max' => 1.50,   'rate' => 'B3/B-',     'spread' => 0.0550],
        ['min' => 0.80,         'max' => 1.25,   'rate' => 'Caa/CCC',   'spread' => 0.0650],
        ['min' => 0.65,         'max' => 0.80,   'rate' => 'Ca2/CC',    'spread' => 0.0800],
        ['min' => 0.20,         'max' => 0.65,   'rate' => 'C2/C',      'spread' => 0.1050],
        ['min' => -100000,  'max' => 0.20,   'rate' => 'D2/D',      'spread' => 0.1400],

    ];

    $spreadBelow5b = [
        ['min' => 12.50,    'max' => 100000, 'rate' => 'Aaa/AAA',   'spread' => 0.0060],
        ['min' => 9.50,         'max' => 12.50,  'rate' => 'Aa2/AA',    'spread' => 0.0080],
        ['min' => 7.50,         'max' => 9.50,   'rate' => 'A1/A+',     'spread' => 0.0100],
        ['min' => 6.00,         'max' => 7.50,   'rate' => 'A2/A',      'spread' => 0.0110],
        ['min' => 4.50,         'max' => 6.00,   'rate' => 'A3/A-',     'spread' => 0.0125],
        ['min' => 4.00,         'max' => 4.50,   'rate' => 'Baa2/BBB',  'spread' => 0.0160],
        ['min' => 3.50,         'max' => 4.00,   'rate' => 'Ba1/BB+',   'spread' => 0.0250],
        ['min' => 3.00,         'max' => 3.50,   'rate' => 'Ba2/BB',    'spread' => 0.0300],
        ['min' => 2.50,         'max' => 3.00,   'rate' => 'B1/B+',     'spread' => 0.0375],
        ['min' => 2.00,         'max' => 2.50,   'rate' => 'B2/B',      'spread' => 0.0450],
        ['min' => 1.50,         'max' => 2.00,   'rate' => 'B3/B-',     'spread' => 0.0550],
        ['min' => 1.25,         'max' => 1.50,   'rate' => 'Caa/CCC',   'spread' => 0.0650],
        ['min' => 0.80,         'max' => 1.25,   'rate' => 'Ca2/CC',    'spread' => 0.0800],
        ['min' => 0.50,         'max' => 0.80,   'rate' => 'C2/C',      'spread' => 0.1050],
        ['min' => -100000,  'max' => 0.50,   'rate' => 'D2/D',      'spread' => 0.1400],

    ];

    $firebase->update(DATA_PATH, array('spread' => array('below' => $spreadBelow5b, 'above' => $spreadAbove5b, 'UPD/'. __FUNCTION__ => date('d/m/y'))));
}

function parseERP(&$firebase)
{
    require_once __DIR__ .'/PHPExcel/PHPExcel.php';

    $url = 'http://www.stern.nyu.edu/~adamodar/pc/implprem/ERPbymonth.xls';
    $fileconntents = file_get_contents($url);

    $tmpfname = tempnam(sys_get_temp_dir(), 'tempxlsx');
    file_put_contents($tmpfname, $fileconntents);

    $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
    $excelObj = $excelReader->load($tmpfname);
    $worksheet = $excelObj->getSheetByName('Historical ERP');
    $lastRow = $worksheet->getHighestRow();
    
    for ($i = $lastRow; $i > 0; $i--) {
        if ($ERP = $worksheet->getCell('H'. $i)->getValue()) {
            break;
        }
    }
    $firebase->update(DATA_PATH, array('ERP' => $ERP, 'UPD/'. __FUNCTION__ => date('d/m/y')));
}

function parseBetaEmerg(&$firebase)
{
    // http://www.stern.nyu.edu/~adamodar/pc/datasets/betaemerg.xls
    require_once __DIR__ .'/PHPExcel/PHPExcel.php';

    $url = 'http://www.stern.nyu.edu/~adamodar/pc/datasets/betaemerg.xls';
    $fileconntents = file_get_contents($url);

    $tmpfname = tempnam(sys_get_temp_dir(), 'tempxlsx');
    file_put_contents($tmpfname, $fileconntents);

    $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
    $excelObj = $excelReader->load($tmpfname);
    $worksheet = $excelObj->getSheet(0);
    $lastRow = $worksheet->getHighestRow();

    $industry = [];
    for ($i = 9; $i <= $lastRow-2; $i++) {
        $industry[] = [
            'industry' => $worksheet->getCell('A'. $i)->getValue(),
            'ubeta' => $worksheet->getCell('F'. $i)->getValue()
        ];
    }

    $firebase->update(DATA_PATH, array('betaemerg' => $industry, 'UPD/'. __FUNCTION__ => date('d/m/y')));
}

function getUSDrate(&$firebase)
{
    /**
     * Getting USR/RUB rate from CBRF (daily)
     */
    $cbr = new SoapClient(CBR_DAILYINFO, array('soap_version' => SOAP_1_2, 'exceptions' => true));
    $date = $cbr->GetLatestDateTime();
    $result = $cbr->GetCursOnDateXML(array('On_date'=>$date->GetLatestDateTimeResult))->GetCursOnDateXMLResult->any;
    
    if ($result) {
      // $incl[] = 'USD/RUB ran';
        $xml = new SimpleXMLElement($result);
        $currencyCode = 'USD';
        $xPath = "/ValuteData/ValuteCursOnDate[VchCode='". $currencyCode ."']";
        $result = $xml->xpath($xPath);
        // \Drupal::logger('wacc')->notice(print_r($xml->xpath('/ValuteData/ValuteCursOnDate')));
        $cbr_usd = [
        'Vcurs' => floatval((string)$result[0]->Vcurs / (string)$result[0]->Vnom),
        'date' => date('d/m/y', strtotime((string)$xml['OnDate']))
        ];
        $firebase->update(DATA_PATH, array('USD' => $cbr_usd, 'UPD/'. __FUNCTION__ => date('d/m/y')));
    }
}

function getCBOE(&$firebase)
{
    /**
     * Getting CBOE Interest Rate 10 Year T No (^TNX) (daily)
     * http://finance.yahoo.com/quote/%5ETNX/history?p=%5ETNX
     */
    if (($handle = fopen("http://download.finance.yahoo.com/d/quotes.csv?s=^TNX&f=l1d1&e=.csv", "r")) !== false) {
      // $incl[] = 'US10Y';
      // $config->set('wacc_us10y', fgetcsv($handle, 1000, ","))->save();
        $CBOE = fgetcsv($handle, 1000, ",");
        $data = [
        'bsd' => $CBOE[0],
        'date' => $CBOE[1]
        ];
        fclose($handle);
        $firebase->update(DATA_PATH, array('CBOE' => $data, 'UPD/'. __FUNCTION__ => date('d/m/y')));
    }
}

function getNonfin(&$firebase)
{
    require_once __DIR__ .'/PHPExcel/PHPExcel.php';

    $url = sprintf('http://www.cbr.ru/statistics/b_sector/loans_nonfin_%d.xlsx', date('y'));

    $fileconntents = file_get_contents($url);

    $tmpfname = tempnam(sys_get_temp_dir(), 'tempxlsx');
    file_put_contents($tmpfname, $fileconntents);

    $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
    $excelObj = $excelReader->load($tmpfname);
    $worksheet = $excelObj->getSheet(0);
    $lastRow = $worksheet->getHighestRow();

    $loan = [];
    for ($i = 7; $i <= $lastRow; $i++) {
        $h = $worksheet->getCell('H'. $i)->getValue();
        $p = $worksheet->getCell('P'. $i)->getValue();
        if (!$h) {
            break;
        }
        $loan['RUB'] = ['regular' => $h, 'msp' => $p];
    }

    $worksheet = $excelObj->getSheet(1);
    $lastRow = $worksheet->getHighestRow();

    for ($i = 7; $i <= $lastRow; $i++) {
        $h = $worksheet->getCell('H'. $i)->getValue();
        $p = $worksheet->getCell('P'. $i)->getValue();
        if (!$h) {
            break;
        }
        $loan['USD'] = ['regular' => $h, 'msp' => $p];
    }

    $firebase->update(DATA_PATH, array('loans' => $loan, 'UPD/'. __FUNCTION__ => date('d/m/y')));
}
