<?php
echo head(array('bodyclass' => 'items show'));

$visdir = WEB_ROOT . '/plugins/Graph/javascripts/vis/'; ?>

<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/plugins/Graph/javascripts/moment-with-locales.js"></script>
<script type="text/javascript" src="<?php echo $visdir; ?>vis.js"></script>
<link href="<?php echo $visdir; ?>vis.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Graph/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Graph/css/graph.css">

    <style type="text/css">
        #mynetwork {
            width: 100%;
            height: 800px;
            padding:12px 16px;
            margin:10px;
            border: 1px solid lightgray;
        }
    </style>
  
<div id="visualization"></div>

<script type="text/javascript">
  // DOM element where the Timeline will be attached
  var container = document.getElementById('visualization');

  // Create a DataSet (allows two way data-binding)
    var nodes = new vis.DataSet([
 			<?php echo implode(',', $nodes); ?>                                
    ]);  

  // Configuration for the Timeline
  var options = {  
    locale: 'fr',
//     limitSize : false, 
    orientation: {
      axis: 'both',
    }
  };

  // Create a Timeline
  var timeline = new vis.Timeline(container, nodes, options);



  // Click ouvre la page de l'item
/*
  timeline.on("click", function (params) {
    params.event = "[original event]";
    var graphToLoad = params['item'];
    draw(params['item']);
  });
*/

</script>


<?php echo foot(); ?>
