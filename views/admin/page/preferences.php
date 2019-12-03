<?php
echo head(array('title' => "Graph Icons Preferences"));
?>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/graph'>Icônes des notices</a>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/graph/colors'>Couleur des collections</a><br /><br /><br />
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