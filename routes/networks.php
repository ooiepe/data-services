<?php
/**
 * Networks
 * Path: /networks
 */
$app->get('/networks', function() use ($app) {
  $app->render('networks.php'); 
});

?>