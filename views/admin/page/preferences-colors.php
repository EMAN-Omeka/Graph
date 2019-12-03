<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<?php
queue_js_file('jscolor');  
echo head(array('title' => "Graph Color Preferences"));
?>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/graph'>Icônes des notices</a>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/graph/colors'>Couleur des collections</a><br /><br /><br />
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Graph/css/graph.css">
<?php 
echo flash(); 
echo $form; 
?>

<script type="text/javascript">
  $ = jQuery;
 $(document).ready(function() {
  });
</script>
  
<?php echo foot(); ?>