<?php
function sendRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        throw new Exception("cURL Error: " . curl_error($ch));
    }

    curl_close($ch);
    return $response;
}

function get_currency_for_a_week($valute, $startDate, $endDate) {
    $_startDate = clone $startDate;
    try {
    $currencyData = [];
    for ($date = $_startDate; $date <= $endDate; $date->modify('+1 day')) {
        
        $_date = $date->format('d/m/Y');
        $url = "https://www.cbr.ru/scripts/XML_daily.asp?date_req={$_date}";

        $response = sendRequest($url);

        $xml = simplexml_load_string($response);
        if ($xml === false) {
            throw new Exception("XML Parsing Error");
        }

        $xpathQuery = "/ValCurs/Valute[CharCode = '$valute']/Value";
        $exchangeRate = $xml->xpath($xpathQuery);

        $formatted_date =$date->format('Y-m-d');
        if (!empty($exchangeRate)) {
            $currencyData[$formatted_date] = (string)$exchangeRate[0];
        } else {
            throw new Exception("XML Parsing Error, Valute not found");
        }
    }
    return $currencyData;
 } catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
 }
}


function foo($v) {
   return "foo $v\n" ;
}
$today= new DateTime();
$startDate = new DateTime('last monday');
$valutes = array( "USD","EUR", "KGS");
foreach( $valutes as $v) {
    echo "$v\n" ;
    $data = get_currency_for_a_week($v, $startDate, $today);
    foreach ($data as $key => $value) {
        echo "$key $value\n" ;
    }
}
