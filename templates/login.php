<?php include('header.php');?>

<h1>Admin Login</h1>

<?php if(!empty($error)):?>
<p class="error"><?php echo $error?></p>
<?php endif;?>

<form action="/login" method="POST">
  <p>Username <input type="text" name="username" id="username" value="" /></p>
  <p>Password <input type="password" name="password" id="password" /></p>
  <p><input type="submit" value="Login" />
</form>

<?php include('footer.php');?>