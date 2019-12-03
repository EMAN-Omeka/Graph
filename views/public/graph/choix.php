<?php
  echo head(array('bodyclass' => 'graph collections', 'title' => 'Visualisation par graphe des relations du corpus '));
  $visdir = WEB_ROOT . '/plugins/Graph/javascripts/vis/'; 
?>  

<script type="text/javascript" src="<?php echo $visdir; ?>vis.min.js"></script>
    <link href="<?php echo $visdir; ?>vis.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Graph/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo WEB_ROOT; ?>/plugins/Graph/css/graph.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style type="text/css">
        #mynetwork, #resleg {
            width: 100%;
            height: 800px;
            border: 1px solid lightgray;
            clear:both;
        }
        #resleg {
            height: <?php echo $height ?>px;
        }
    </style>
<h2><?php echo $pageTitle; ?></h2>
<h2>Sélectionnez les collections</h2>
<?php
  echo $form; ?>
 <br /><br /> 
<?php
  echo $form2; 
?>
 <br /><br /> 
<div style="clear:both;">En plaçant le curseur sur une flèche, le nom de la relation apparait</div>
<div id="mynetwork"></div>
<h3><b>Légende des icônes et des couleurs</b></h3>
<div id="resleg"></div>
<input type="hidden" id="phpWebRoot" value="<?php echo WEB_ROOT; ?>">
<?php 
  if (isset($caption['icons'])) {
    $icons = implode(',', $caption['icons']); 
    $colors = implode(',', $caption['colors']);       
  }
?>
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
    
   // Légende
    var nodesleg = new vis.DataSet([<?php echo $icons; ?>]); 
    <?php echo 'captionElement = [' . $colors ?>];
    nodesleg.add(captionElement);     
    var datalegs = {
      nodes: nodesleg,
    }    
    options.interaction = {
    	    keyboard: false,
    	    navigationButtons: false,    	    
    	    zoomView: false,      
    }

    var caption = document.getElementById('resleg');
        
    var leg = new vis.Network(caption, datalegs, options);   
    var x = - leg.clientWidth / 2 - 200;
    var y = - leg.clientHeight / 2 + 200;
    var step = 120;         
    
    // Click ouvre la page du fichier, de la collection ou de l'item
    network.on("click", function (params) {
      params.event = "[original event]";
      var url = '';
      if (typeof params['nodes'][0] != 'undefined') {
        var nodeObj = network.body.data.nodes._data[params['nodes'][0]];
        var texte = nodeObj['label'];        
        id = params['nodes'][0];      
        id = id.toString();         
        if (id.substring(0, 5) == '20000') {
          pathNotice = '/files/show/';
          pathGraph = '/graphfile/';
          id = id.substring(5);
          nom = 'Fichier';
        } else if (id.substring(0, 5) == '10000') {
          pathNotice = '/collections/show/';
          pathGraph = '/graphcollection/';
          id = id.substring(5);
          nom = 'Collection';
        } else {    
          pathNotice = '/items/show/';
          pathGraph = '/graphitem/';          
          nom = 'Notice';          
        }
        $('#dialog-confirm').html(nom + ' "' + texte + '" (' + id + ')');
       $(function() {
        $( "#dialog-confirm" ).dialog({
          resizable: false,
          height: "auto",
          width: 400,
          modal: true,
          buttons: {
            "Voir le contenu" : function() {
              url = "<?php echo WEB_ROOT ?>" + pathNotice + id;      
              $(this).dialog( "close" );
              window.open(url);
            },
            "Voir le graphe": function() {
              url = "<?php echo WEB_ROOT ?>" + pathGraph + id;      
              $(this).dialog( "close" );
              window.open(url);
            }
          }
        });
      });        
    }      
  });
  
</script>
<div id="dialog-confirm" style="background:white;display:none;" title="">
</div>  
  
<?php echo foot(); ?>
