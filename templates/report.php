<?php include('header.php');?>

<div align="right"><a href="<?php echo $app_base?>/admin">Admin Menu</a> | <a href="<?php echo $app_base?>/">Homepage</a></div>
<hr>

<h1>Installation Report</h1>
<pre>
<?php if(!empty($output)):?>
<p class="error"><?php echo $output?></p>
<?php endif;?>
</pre>

<?php include('footer.php');?>