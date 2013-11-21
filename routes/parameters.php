<?php
/**
 * Full Parameter List
 * Path: /parameters
 */
$app->get('/parameters', 'getParameters');
function getParameters() {
  $sql = "SELECT * FROM ts_parameters ORDER BY name";
  try {
    $db = DB::getInstance();
    $stmt = $db->query($sql);
    $parameters = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"parameters": ' . json_encode($parameters) . '}';
  } catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Parameter Details
 * Path: /parameters/[parameter_name]
 */
$app->get('/parameters/:id', 'getParameter');
function getParameter($name) {
  $sql = "SELECT * FROM ts_parameters WHERE name=:name";
  try {
    $db = DB::getInstance();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("name", $name);
    $stmt->execute();
    $parameter = $stmt->fetchObject();
    $db = null;
    echo json_encode($parameter);
  } catch(PDOException $e) {
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Station Listing for a given Parameter - TBD
 * 
 */


?>