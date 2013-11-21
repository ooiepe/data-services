<?php
// Load Normalized Parameter List
//  require 'parameters.php';

/* validateParams Class
 * Used to setup and validate search parameters
 */
class ValidateParams {
	public $exceptions = array();
  private $allowedNetworks = array();
  private $allowedParameters = array();

  public $networks = 'all';
  public $parameters = 'all';
  public $llat = -90; 
  public $ulat = 90;
  public $llon = -180;
  public $ulon = 180;
  public $start_time = null;
  public $end_time = null;
  public $stations = null;
  
  public function __construct() {
    global $normalizedParameters;
    $this->allowedNetworks = array_keys($normalizedParameters);
    foreach($this->allowedNetworks as $network) {
      $this->allowedParameters = array_unique(array_merge($this->allowedParameters,array_keys($normalizedParameters[$network])));
    }
  }

  public function validateNetworks($input) {  
    $out=null;
    if (empty($input)) {
      $this->exceptions[] = "Missing parameter: network";
    } else {
      if (strcasecmp($input,'all')==0){
        $out = array('all');
      } else {
        $items = explode(',',$input);
        foreach ($items as $it) {
          if (in_array(strtolower($it),array_map('strtolower',$this->allowedNetworks))) {
            $out[] = strtolower($it);
          } else {
            $this->exceptions[] = "Network not found: " . htmlspecialchars($it);
          }
        }
      }
    } 
    $this->networks = $out;  
  }
  
  public function validateParameters($input) {  
    $out=null;
    if (empty($input)) {
      $this->exceptions[] = "Missing parameter: parameter";
    } else {
      if (strcasecmp($input,'all')==0){
        $out = 'all';
      } else {
        $items = explode(',',$input);
        foreach ($items as $it) {
          if (in_array(strtolower($it),array_map('strtolower',$this->allowedParameters))) {
            $out[] = strtolower($it);
          } else {
            $this->exceptions[] = "Parameter not found: " . htmlspecialchars($it);
          }
        }
      }
    }
    $this->parameters = $out;  
  }  
  
  public function validateStations($input) {
    $out=null;
    if (empty($input)) {
      $this->exceptions[] = "Missing parameter: station";
    } else {
      $items = explode(',',$input);
      foreach ($items as $it) {
        $it = filter_var($it,FILTER_SANITIZE_STRING);
        if ($it) {
          $out[] = $it;
        } else {
          $this->exceptions[] = "Missing or invalid parameter: station";
        }
      }
    }
    $this->stations = $out;  
  }

  public function validateTimes($start_time,$end_time) {
    $out=null;
    if (empty($start_time)) {
      $this->exceptions[] = "Missing parameter: start_time";
    } elseif (empty($end_time)) {
      $this->exceptions[] = "Missing parameter: end_time";
    } else {
      if (strcasecmp($end_time,'now') == 0) {
  			$this->end_time = time();
  	  } else {
  			$_tss = $this->readISO8601($end_time);
  			if ($_tss) {
  			  $this->end_time = $_tss;
  		  } else {
          $this->exceptions[] = "Invalid end_time. Format should be 0000-00-00T00:00Z or 0000-00-00 or 'now' ";
  		  }
  		}
      if (is_numeric($start_time) ) { //&& ($out['end_time'])
  			$this->start_time = $this->end_time - $start_time*60*60*24;
  	  } else {
  			$_tse = $this->readISO8601($start_time);
  			if ($_tse) {
    			$this->start_time = $_tse;
        } else {
          $this->exceptions[] = "Invalid start_time. Format should be 0000-00-00T00:00Z or 0000-00-00 or number of days before end_time.";
        }
	    }
    }
    if ($this->start_time > $this->end_time) {
      $this->exceptions[] = "Error: start_time can not be greater than end_time.";
    }
  }
  
  /**
   * readISO8601
   * Adapted from NDBC SOS
   */
  private function readISO8601($str) {
  	$_matches = array();
  	$_ts = null;
  	if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(\.\d{2})?)Z$/D',$str,$_matches) === 1) {
  		$_ts = gmmktime(intval($_matches[4]),intval($_matches[5]),round($_matches[6]),intval($_matches[2]),intval($_matches[3]),intval($_matches[1]));
  	} elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})Z$/D',$str,$_matches) === 1) {
  		$_ts = gmmktime(intval($_matches[4]),intval($_matches[5]),0,intval($_matches[2]),intval($_matches[3]),intval($_matches[1]));
  	} elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D',$str,$_matches) === 1) {
  		$_ts = gmmktime(0,0,0,intval($_matches[2]),intval($_matches[3]),intval($_matches[1]));
  	}
  	return $_ts;
  }  
  
  public function validateLocation($input) {
    if (preg_match('/^(-?\d+(\.\d+)?),(-?\d+(\.\d+)?),(-?\d+(\.\d+)?),(-?\d+(\.\d+)?)$/iD',$input,$_matches) == 1) {
      $_llon = $_matches[1];
      $_llat = $_matches[3];
      $_ulon = $_matches[5];
      $_ulat = $_matches[7];
  		if ($_llat >= -90 && $_llat <= 90) {
  		  $this->llat = $_llat;
      } else {
  			$this->exceptions[] = 'Lower Latitude out of range (-90 to 90).';
  		}
  		if ($_ulat >= -90 && $_ulat <= 90) {
  		  $this->ulat = $_ulat;
      } else {
  			$this->exceptions[] = 'Upper Latitude out of range (-90 to 90).';
  		}
  		if ($_llon >= -360 && $_llon <= 360) {
  		  $this->llon = $_llon;
      } else {
  			$this->exceptions[] = 'Lower Longitude out of range (-360 to 360).';
  		}
  		if ($_ulon >= -360 && $_ulon <= 360) {
  		  $this->ulon = $_ulon;
      } else {
  			$this->exceptions[] = 'Upper Longitude out of range (-360 to 360).';
  		}
  		if ($_llat > $_ulat) {
  			$this->exceptions[] = 'Lower Latitude cannot be greater than Upper Latitude.';
  		}
  		//if ($_llon > $_ulon) {
  		//	$this->exceptions[] = 'Lower Longitude cannot be greater than Upper Longitude.';
  		//}
  		if (($_ulon - $_llon) > 360) {
  			$this->exceptions[] = 'Longitude range must not exceed 360 degrees.';
  		}
    } else {
			$this->exceptions[] = 'Bad location format.';
    }
  }

}

?>