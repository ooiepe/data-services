<?php
$authenticateForRole = function () {
    return function () {
    if (!isset($_SESSION['admin'])) {
      $app = \Slim\Slim::getInstance();
      $app->flash('error', 'Login required');
      $app->redirect($app->urlFor('login'));
    }    
  };
};

/**
 * Admin Menu
 */
$app->get('/admin', $authenticateForRole('admin'), function () use ($app) {
  $app->render('admin_menu.php');
});

/**
 * Login
 */
$app->map("/login", function () use ($app) {
  global $config;
  $error='';
  $flash = $app->view()->getData('flash');
  if (isset($flash['error'])) {
      $error = $flash['error'];
  }
  // Check for form submission
  if($app->request()->isPost()) {
    // Check for both fields
    $username = $app->request()->post('username');
    $password = $app->request()->post('password');
    if(!empty($username) && !empty($password)) {
      // Check for valid login
      if($username == $config['login']['username'] && $password == $config['login']['password']) {
        $_SESSION['admin'] = true;
        $app->redirect('/admin');
      } else {
        $error = "Username and password are not correct";
      }
    } else {
      $error = "Username and password must be specified";    
    }
  }  
  $app->render('login.php',array('error'=>$error));
})->via('GET','POST')->name('login');

/**
 * Logout
 */
$app->get("/logout", function () use ($app) {
   unset($_SESSION['admin']);
   $app->render('logout.php');
});



?>