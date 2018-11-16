<?php
echo head(array('title' => "Graph Preferences"));
?>
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Graph/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Graph/css/graph.css">
<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/plugins/Graph/javascripts/simple-iconpicker.js"></script>
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Graph/css/simple-iconpicker.min.css">
<?php 
echo flash(); 
echo $iconcodes;
echo $form; 
?>

<script type="text/javascript">
  $ = jQuery;
 $('.faicon').iconpicker(".faicon");
 </script>
  
<?php echo foot(); ?>