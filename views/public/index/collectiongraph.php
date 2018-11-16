<?php
echo head(array('bodyclass' => 'items show'));

$visdir = WEB_ROOT . '/plugins/Graph/javascripts/vis/'; ?>

<script type="text/javascript" src="<?php echo $visdir; ?>vis.js"></script>
<link href="<?php echo $visdir; ?>vis.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Graph/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Graph/css/graph.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <style type="text/css">
        #mynetwork {
            width: 100%;
            height: 800px;
            border: 1px solid lightgray;
        }
        #resleg {
            height: 200px;
        }
        
    </style>
<div id="mynetwork"></div>
<div id="resleg"></div>
<script type="text/javascript">
    var nodes = new vis.DataSet([
 			<?php echo implode(',', $nodes); ?>                                
    ]);    

    var edges = new vis.DataSet([
 			<?php echo implode(',', $edges); ?>                                   
    ]);

    var container = document.getElementById('mynetwork');

    var data = {
        nodes: nodes,
        edges: edges
    };
      <?php print GraphPlugin::getGraphOptions(); ?>

    var network = new vis.Network(container, data, options);

   // Legend
    var legende = document.getElementById('resleg');
    var nodesleg = new vis.DataSet([<?php echo implode(',', $legende['icons']); ?>]); 
    <?php echo 'legendeElement = [' . implode(',', $legende['colors']) . '];'; ?>
    nodesleg.add(legendeElement);     
    var datalegs = {
      nodes: nodesleg,
    }    

    options['interaction'] = {
    	    keyboard: false,
    	    navigationButtons: false,    	    
    	    zoomView: false,      
    }
    var leg = new vis.Network(legende, datalegs, options);   
    var x = - leg.clientWidth / 2 - 200;
    var y = - leg.clientHeight / 2 + 200;
    var step = 120;          
    
    // Click ouvre la page de l'item
    network.on("click", function (params) {
      params.event = "[original event]";
      var url = '';
      if (typeof params['nodes'][0] != 'undefined') {
       $( function() {
        $( "#dialog-confirm" ).dialog({
          resizable: false,
          height: "auto",
          width: 400,
          modal: true,
          buttons: {
            "Voir la notice": function() {
              url = "<?php echo WEB_ROOT . '/items/show/'?>" + params['nodes'][0];      
              $( this ).dialog( "close" );
              window.open(url);
            },
            "Voir le graphe": function() {
              url = "<?php echo WEB_ROOT . '/graphitem/'?>" + params['nodes'][0];      
              $( this ).dialog( "close" );
              window.open(url);
            }
          }
        });
      });        
    }      
  });   
</script>
<div id="dialog-confirm" style="background:white;display:none;" title="Que souhaitez vous voir ?">
</div>
<?php echo foot(); ?>
