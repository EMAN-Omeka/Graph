<?php
echo head(array('bodyclass' => 'files show', 'title' => 'Les relations du document'));

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
            height: <?php echo $height; ?>px;
        }                
    </style>
<h2>Les relations du document</h2>
<p>    
  <a href="<?php echo WEB_ROOT; ?>/files/show/<?php echo $fileId; ?>">Voir la page de ce fichier</a><br />
 </p>
<div>En plaçant le curseur sur une flèche, le nom de la relation apparait</div>
<div id="mynetwork"></div>
<h3><b>Légende des icônes et des couleurs</b></h3>
<div id="resleg"></div>
<i class="fa fa-flag" style="visibility:hidden;"></i> 
<script type="text/javascript">

    var container = document.getElementById('mynetwork');
    var network = null;

    <?php print GraphPlugin::getGraphOptions(); ?>  	  
    	              
      var nodes = new vis.DataSet([
   			<?php echo implode(',', $nodes); ?>                                
      ]);    
  
      var edges = new vis.DataSet([
   			<?php echo implode(',', $edges); ?>                                   
      ]);

      var data = {
          nodes: nodes,
          edges: edges
      };       
  	  options.layout = {
      	    randomSeed: 15,
      	    improvedLayout:true,
      	    hierarchical: {
      	      enabled:true,
      	      levelSeparation: 250,
      	      nodeSpacing: 60,
      	      treeSpacing: 20,
  //     	      blockShifting: false,
  //     	      edgeMinimization: false,
  //     	      parentCentralization: false,
      	      direction: 'LR',        // UD, DU, LR, RL
      	      sortMethod: 'directed'   // hubsize, directed
      	    }
      	  };      

	    var network = new vis.Network(container, data, options);

   // Legend
    var legende = document.getElementById('resleg');
    options['layout']['hierarchical'] = false;
        
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
    
    // Click ouvre la page du fichier, de la collection ou de l'item
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
            "Voir le fichier": function() {
              url = "<?php echo WEB_ROOT . '/files/show/'?>" + LinkFromNode(params['nodes'][0]);      
              $( this ).dialog( "close" );
              window.open(url);
            },
            "Voir le graphe": function() {
              url = "<?php echo WEB_ROOT . '/graphfile/'?>" + LinkFromNode(params['nodes'][0]);      
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
<script>
		function LinkFromNode(nid) {
		  var links = [<?php echo $links; ?>];    
			var arrayLength = links.length;
			for (var i = 0; i < arrayLength; i++) {
					if (links[i].node == nid) { return links[i].file;}	    
			}	
		}


</script>

<?php echo foot(); ?>

