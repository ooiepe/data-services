<?php
/**
 * Timeseries Data Request
 * Path: /timeseries
 */
$app->get('/timeseries', 'getData');

/**
 * Slim callback for Timeseries Data Request
 */
function getData() {
  global $normalizedParameters;

  $app = \Slim\Slim::getInstance();
  $req = $app->request();

  // Validate Parameters
  $valid = new validateParams();
	$valid->validateNetworks($req->get('network'));
  $valid->validateStations($req->get('station'));
	$valid->validateParameters($req->get('parameter'));
	$valid->validateTimes($req->get('start_time'),$req->get('end_time'));
  if (count($valid->exceptions) > 0) {
    jsonOutput(400,array('error'=>$valid->exceptions));
    $app->stop();
  }
  $network = $valid->networks[0];
  $station = $valid->stations[0];
  $parameter = $valid->parameters[0];  
  $parameter_request =  $normalizedParameters[$network][$parameter]['request'];
  $parameter_response = $normalizedParameters[$network][$parameter]['response'];  
  $start_time = $valid->start_time;
  $end_time = $valid->end_time;

  // Response Header
  $app->contentType('text/csv');

  // HTTP Caching
  $app->etag($network . $station . $parameter . $start_time . $end_time);
  if ( $end_time<(time()-60*60*24) ) {
    $app->expires('+2 weeks'); //Older request, extend cache
  } else {
    $app->expires('+1 hour'); //Real-time request, limit cache
  }
  
  // Local File Cache
  $cache_file = $app->config('cache.path') . '/'. $network . $station . $parameter . $start_time . $end_time;
  if (checkCache($cache_file)) {
    //var_dump("SERVED FROM CACHE");
    $app->stop();
  }

  $start = microtime(true);

  $dh = new DataHandler;
  $data = $dh->getObservations($network,$station,$parameter,$parameter_request,$parameter_response,$start_time,$end_time);
  
  // Write the CSV file
  csvOutput($data);
  //echo '<pre>';
  //print_r($data);
  
  // Save the output to the cache (for requests ending at least 1-day ago) 
  if ($dates['end_time']<(time()-60*60*24)) {
    saveCache($cache_file, ob_get_contents());
  }
  //var_dump("SERVED LIVE");

  //echo "...done in " . (microtime(true) - $start) . PHP_EOL;
  //print('Peak Memory:' . memory_get_peak_usage()/1024/1024 . 'MB');  
}

/**
 * Outputs a response in json format
 *
 * Adapted from http://www.androidhive.info/2014/01/how-to-create-rest-api-for-android-app-using-php-slim-and-mysql-day-23/
 * @param string $status_code HTTP response code
 * @param array $response Array of data for JSON response
 */
function jsonOutput($status_code, $response) {
  $app = \Slim\Slim::getInstance();
  // Http response code
  $app->status($status_code);
 
  // setting response content type to json
  $app->contentType('application/json');
 
  echo json_encode($response);
}

/**
 * Outputs a response in csv format
 *
 * Adapted from http://php.net/manual/en/function.fputcsv.php
 * @param array $data Array of data to output
 */ 
function csvOutput($data) {
  $outstream = fopen("php://output", 'w');
  function __outputCSV(&$vals, $key, $filehandler) {
    fputcsv($filehandler, $vals, ',', '"');
  }
  array_walk($data, '__outputCSV', $outstream);
  fclose($outstream);
}


/**
 * Check for the specified cache file and echo it to the browser if available
 *
 * Also deletes older files to refresh cache (currently set at 30 days)
 * Adapted from http://help.slimframework.com/discussions/questions/369-cache-app-render
 * @param string $file Cache file (full path)
 * @return bool
 */
function checkCache($file) {
  if (!file_exists($file)) {
    return false;
  }
  if (filectime($file)<(time()-60*60*24*30)) {
    unlink($file);
    return false;
  }
  readfile($file);
  return true;
}


/** 
 * Save contents to a specified file
 *
 * @param string $file Cache file (full path)
 * @param string $contents Contents to write to the file
 */
function saveCache($file, $contents) {
  file_put_contents($file, $contents);
}

?>