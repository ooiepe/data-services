<?php include('header.php');?>

<h1>Report</h1>
<pre>
<?php if(!empty($output)):?>
<p class="error"><?php echo $output?></p>
<?php endif;?>
</pre>

<?php include('footer.php');?>