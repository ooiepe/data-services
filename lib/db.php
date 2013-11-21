<?php
/**
 * Database Connection Class
 * Adapted from http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
 */
class DB {
  private static $instance;

  private function __construct() {
  }

  public static function getInstance() {
    global $config;
    if (!isset(self::$instance)) {
      $dbhost=$config['database']['host'];
      $dbuser=$config['database']['login'];
      $dbpass=$config['database']['password'];
      $dbname=$config['database']['database'];
      self::$instance = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
      self::$instance-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return self::$instance;
  }

}

?>