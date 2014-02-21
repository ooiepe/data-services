<?php
/**
 * DataHandler Class
 */ 
class DataHandler {

  /**
   * __construct
   */ 
  public function __construct() {
  }

  /**
   * Retrieve observations from an SOS service
   *
   * @param string $network Network name
   * @param string $station Station name
   * @param string $parameter Normalized parameter name
   * @param string $parameter_request Parameter name to be used in requesting data from SOS
   * @param string $parameter_response Parameter name to be extracted from SOS response
   * @param int $start_time Start time of request, unix format
   * @param int $end_time End time of request, unix format
   * @return array
   */ 
  public function getObservations($network,$station,$parameter,$parameter_request,$parameter_response,$start_time,$end_time) {
    $urls = $this->makeSOSUrls($network,$station,$parameter_request,$start_time,$end_time);

    $rollingCurl = new \RollingCurl\RollingCurl();
    foreach ($urls as $url) {
      $rollingCurl->get($url);
    }
    //$results =array(); 
    //$rollingCurl->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$results) {
    //  $results[] = $request->getResponseText();
    //  echo "Processsed (" . $request->getUrl() . ")" . PHP_EOL;
    //});
    $rollingCurl->setSimultaneousLimit(5);
    $rollingCurl->execute();

    $out=array();
    foreach ($rollingCurl->getCompletedRequests() as $request) {
      $data = $this->extractColumn($request->getResponseText(),$parameter_response);
      if($data) {
        $out = array_merge($out,$data['data']);
      }
    }
    usort($out,array($this,'cmp')); // Sort data array by time
    array_unshift($out,array('date_time',$parameter)); // Add header line
    return $out;
  }
 
  /**
   * Comparison functiont to sort dates
   * @return int
   */ 
  static function cmp(array $a, array $b) {
    return strcmp($a[0],$b[0]);
  }

  /**
   * Constructs URLs for SOS services
   *
   * @param string $network Network name
   * @param string $station Station name
   * @param string $parameter_request Parameter name to be used in requesting data from SOS
   * @param int $start_time Start time of request, unix format
   * @param int $end_time End time of request, unix format
   * @return array
   */ 
  public function makeSOSUrls($network,$station,$parameter_request,$start_time,$end_time) {
    $requests = $this->splitDates($start_time,$end_time);
    for ($i=0;$i<count($requests)-1;$i++) {
      if ($network=='ndbc') {
        $urls[$i] = 'http://sdf.ndbc.noaa.gov/sos/server.php?request=GetObservation&service=SOS&version=1.0.0&offering=urn:ioos:station:wmo:' 
        . $station . '&observedproperty=' 
        . $parameter_request 
        . '&responseformat=text/csv&eventtime=' 
        . gmdate("Y-m-d\TH:i:s\Z",$requests[$i]) . '/' . gmdate("Y-m-d\TH:i:s\Z",$requests[$i+1]);
      } elseif ($network=='co-ops') {
        $urls[$i] = 'http://opendap.co-ops.nos.noaa.gov/ioos-dif-sos/SOS?request=GetObservation&service=SOS&version=1.0.0&offering=urn:ioos:station:NOAA.NOS.CO-OPS:' 
        . $station . '&observedproperty=' 
        . $parameter_request 
        . '&responseformat=text/csv&eventtime=' 
        . gmdate("Y-m-d\TH:i:s\Z",$requests[$i]) . '/' . gmdate("Y-m-d\TH:i:s\Z",$requests[$i+1]);
      }
    }
    return $urls;
  }

  /**
   * Divide long date ranges into smaller chunks
   *
   * @param int $start_time Start time of request, unix format
   * @param int $end_time End time of request, unix format
   * @param int $interval Number of days
   * @return array
   */ 
  public function splitDates($start_time,$end_time,$interval=30) {
    $max_interval = 60*60*24*$interval;
    if ($end_time-$start_time > $max_interval) {
      $out = range($start_time,$end_time,$max_interval);
      if (end($out) != $end_time) {
        $out[] = $end_time;
      }
    } else {
      $out[] = $start_time;
      $out[] = $end_time;
    }
    return $out;
  }

  /**
   * Extracts parameter and date columns from CSV response from SOS
   *
   * @param string $data CSV response from SOS
   * @param string $parameter_response Parameter name to be extracted from SOS response
   * @return array
   */ 
  public function extractColumn($data,$parameter_response) {
    // Check for error and extract header line
    if (preg_match("/xml version/i", $data)) {
      return false;
    } else {
      preg_match('/^station_id?.+/i', $data, $matches);
      $header = $matches[0];
    }
    // Remove header and blank lines
    $data = preg_replace('/station_id+.+[\r\n]/',"", $data);
    $data = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/","",$data);

    // Create a header index (adapted from Maracoos)
    $headers = str_getcsv($header);
    $col2idx=null;
    foreach ($headers as $h) {
      $col2idx[$h] = count($col2idx);
      if (preg_match("/^$parameter_response \((.*)\)$/",$h,$matches)) {
        $uomOrig = $matches[1];
      }
    }

    // Parse data and extract only the desired variable column
    $out['header'] = array('date',"$parameter ($uomOrig)");
    $datalines = explode("\n",$data);
    $out['data']=array();
    foreach ($datalines as $dataline) {
      $d = str_getcsv($dataline);
      if (count($d)>1) {
        $out['data'][] = array($d[$col2idx['date_time']], $d[$col2idx["$parameter_response ($uomOrig)"]]);
      }
    }
    return $out;
  }


}