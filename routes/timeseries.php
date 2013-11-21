<?php
/**
 * Data Request
 * Path: /
 */
$app->get('/timeseries', 'getData');
function getData() {
  global $normalizedParameters;

  $app = \Slim\Slim::getInstance();
  $req = $app->request();

  // Step 1 - Validate Parameters
  $valid = new validateParams();
	$valid->validateNetworks($req->get('network'));
  $valid->validateStations($req->get('station'));
	$valid->validateParameters($req->get('parameter'));
	$valid->validateTimes($req->get('start_time'),$req->get('end_time'));
  if (count($valid->exceptions) > 0) {
    echo '{"error":'. json_encode($valid->exceptions) .'}';
    exit;
  }
  $network = $valid->networks[0];
  $station = $valid->stations[0];
  $parameter = $valid->parameters[0];  
  $parameter_request =  $normalizedParameters[$network][$parameter]['request'];
  $parameter_response = $normalizedParameters[$network][$parameter]['response'];  
  $dates['start_time'] = $valid->start_time;
  $dates['end_time'] = $valid->end_time;

  // HTTP Caching
  $app->etag($network . $station . $parameter . $dates['start_time'] . $dates['end_time']);
  $app->expires('+2 weeks');

  // Response Headers
  $res = $app->response();
  $res['Content-Type'] = 'text/csv';
  //$res['Access-Control-Allow-Origin'] = '*';

  // Step 2 - If period > 30 days, break up requests
  $max_interval = 60*60*24*30;
  if ($dates['end_time']-$dates['start_time']>$max_interval) {
    $reqs = range($dates['start_time'],$dates['end_time'],$max_interval);
    $reqs[] = $dates['end_time'];
  } else {
    $reqs[] = $dates['start_time'];
    $reqs[] = $dates['end_time'];
  }
  for ($i=0;$i<count($reqs)-1;$i++) {
    if ($network=='ndbc') {
      $urls[$i] = 'http://sdf.ndbc.noaa.gov/sos/server.php?request=GetObservation&service=SOS&version=1.0.0&offering=urn:ioos:station:wmo:' . $station . '&observedproperty=' . $parameter_request . '&responseformat=text/csv&eventtime=' . gmdate("Y-m-d\TH:i:s\Z",$reqs[$i]) . '/' . gmdate("Y-m-d\TH:i:s\Z",$reqs[$i+1]);
    } elseif ($network=='co-ops') {
      $urls[$i] = 'http://opendap.co-ops.nos.noaa.gov/ioos-dif-sos/SOS?request=GetObservation&service=SOS&version=1.0.0&offering=urn:ioos:station:NOAA.NOS.CO-OPS:' . $station . '&observedproperty=' . $parameter_request . '&responseformat=text/csv&eventtime=' . gmdate("Y-m-d\TH:i:s\Z",$reqs[$i]) . '/' . gmdate("Y-m-d\TH:i:s\Z",$reqs[$i+1]);  
    }  
  }
  //echo "<pre>";
  //print_r($urls);

  // Step 3 - Request data asynchronously and cat it together
  $data = request_data($urls);

  // Check for error and extract header line
  if (preg_match("/xml version/i", $data)) {
    echo '{"error":"Invalid request","url":'.$urls[0].',"response":'.htmlspecialchars($data).';}';
    exit;
  } else {
    preg_match('/^station_id?.+/i',	$data, $matches);
    $header = $matches[0];
  }
  // Remove header and blank lines
  $data = preg_replace('/station_id+.+[\r\n]/',"", $data);
  $data = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/","",$data);

  // Create a header index
  $headers = str_getcsv($header);
  $col2idx=null;
  foreach ($headers as $h) {
    $col2idx[$h] = count($col2idx);
    if (preg_match("/^$parameter_response \((.*)\)$/",$h,$matches)) {
      $uomOrig = $matches[1];
    }
  }

  // Step 5 - Parse data and extract only the desired variable column
  $dataout[] = array('date',"$parameter ($uomOrig)");
  $datalines = explode("\n",$data);
  foreach ($datalines as $dataline) {
    $d = str_getcsv($dataline); 
    if (count($d)>1) {
      $dataout[] = array($d[$col2idx['date_time']], $d[$col2idx["$parameter_response ($uomOrig)"]]);
    }
  }

  // Write the CSV file
  csv_output($dataout);

}

// Test URLs
//  http://api.localhost/timeseries?network=NDBC&station=44025&parameter=air_temperature&start_time=5&end_time=now
//  http://api.localhost/timeseries?network=CO-OPS&station=8635750&parameter=air_temperature&start_time=1&end_time=2013-07-01





/* csv_output
 * Adapted from http://php.net/manual/en/function.fputcsv.php
 */
function csv_output($data) {
  $outstream = fopen("php://output", 'w');
  function __outputCSV(&$vals, $key, $filehandler) {
    fputcsv($filehandler, $vals, ',', '"');
  }
  array_walk($data, '__outputCSV', $outstream);
  fclose($outstream);
}

/* request_data 
 * Request data asynchronously from third-party web services using curl_multi
 * Adapted from http://www.sitepoint.com/using-curl-for-remote-requests/
 */
function request_data($urls) {
  // initialize the multihandler
  $session = curl_multi_init();

  $channels = array();
  foreach ($urls as $key => $url) {
    // initiate individual channel
    $channels[$key] = curl_init();
    curl_setopt_array($channels[$key], array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true
    ));
 
    // add channel to multihandler
    curl_multi_add_handle($session, $channels[$key]);
    //echo curl_exec($channels[$key]);
    //curl_close($channels[$key]);
  }

  // execute - if there is an active connection then keep looping
  $active = null;
  do {
    $status = curl_multi_exec($session, $active);
    curl_multi_select($session);
  } while ($active>0);
  $response = '';

  // echo the content, remove the handlers, then close them
  foreach ($channels as $chan) {
    $response .= curl_multi_getcontent($chan);
    //print_r(curl_getinfo($chan));
    curl_multi_remove_handle($session, $chan);
    //curl_close($chan);
  }

  // close the multihandler
  curl_multi_close($session);
  return $response;
}



?>