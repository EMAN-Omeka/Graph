<?php

/* 
 * E-man Plugin
 *
 * Functions to customize Omeka for the E-man Project
 *
 */

class GraphPlugin extends Omeka_Plugin_AbstractPlugin 
{
  protected $_hooks = array(
   		'define_acl',  		
  		'define_routes',  	
  		'public_head',	
  		'public_content_top',
  );
  
  protected $_filters = array(
  	'admin_navigation_main',
  );
  
  public function filterAdminNavigationMain($nav)
  {
    $nav[] = array(
                    'label' => __('Graph'),
                    'uri' => url('graph')
                  );
    return $nav;
  }
  
  function hookPublicContentTop($args)
  {    
		$params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
		$graphLink = "";
		if ($params['controller'] == 'items' && $params['action'] == 'show' || $params['controller'] == 'eman' && $params['action'] == 'items-show') {
  		// L'item a-t-il des relations ? 
  		if ($this->itemHasRelations($params['id'])) {
    		$graphLink = WEB_ROOT . "/graphitem/" . $params['id'];
    		print "<a class='eman-edit-link' style='margin-top:0px;' href='$graphLink'>Afficher la visualisation des relations de la notice</a>";	
  		}				
    }
		if ($params['controller'] == 'collections' && $params['action'] == 'show' || $params['controller'] == 'eman' && $params['action'] == 'collections-show') {
  		if ($this->collectionHasRelations($params['id'])) {  		
  			$graphLink = WEB_ROOT . "/graphcollection/" . $params['id'];
  			print "<a class='eman-edit-link' style='margin-top:0px;' href='$graphLink'>Afficher la visualisation des relations dans la collection</a>";  	       		  }
    }
  	return true;
  }

  private function itemHasRelations($id) {
    $item = get_record_by_id('Item', $id);
    $relations = ItemRelationsPlugin::prepareObjectRelations($item);
    if ($relations) {
      return true;        
    }
    $relations = ItemRelationsPlugin::prepareSubjectRelations($item);
    if ($relations) {
      return true;        
    }
    return false;
  }
  
  private function collectionHasRelations($id) {
    $db = get_db();
    $collection = get_record_by_id('Collection', $id);
    $query = "SELECT id FROM `{$db->Items}` WHERE collection_id = $id";
    $records = $db->query($query)->fetchAll();
    foreach ($records as $i => $itemId) {     
      if ($this->itemHasRelations($itemId['id'])) {
        return true;
      }
    }
    return false;
  }  
  public function hookPublicHead()
  {
 		queue_js_file('graph');
  }  
  
  function hookDefineRoutes($args)
  {
      $router = $args['router'];
      if (is_admin_theme()) {
    		$router->addRoute(
    				'eman_graph_admin_page',
    				new Zend_Controller_Router_Route(
    						'graph',
    						array(
    								'module' => 'graph',
    								'controller'   => 'page',
    								'action'       => 'preferences',
    						)
    				)
    		);
    		return;
      }

  		$router->addRoute(
  				'eman_graph_relation',
  				new Zend_Controller_Router_Route(
  						'graphrelation',
  						array(
  								'module' => 'graph',
  								'controller'   => 'index',
  								'action'       => 'relationgraph',
  						)
  				)
  		);
/*
   		$router->addRoute(
   				'eman_graph_timeline',
   				new Zend_Controller_Router_Route(
   						'timeline',
   						array(
   								'module' => 'graph',
   								'controller'   => 'index',
   								'action'       => 'timeline',
   						)
   				)
   		);
*/
   		$router->addRoute(
   				'eman_graph_item',
   				new Zend_Controller_Router_Route(
   						'graphitem/:itemid',
   						array(
   								'module' => 'graph',
   								'controller'   => 'index',
   								'action'       => 'itemgraph',
   						)
   				)
   		);   		 
   		$router->addRoute(
   				'eman_graph_collection',
   				new Zend_Controller_Router_Route(
   						'graphcollection/:collectionid',
   						array(
   								'module' => 'graph',
   								'controller'   => 'index',
   								'action'       => 'collectiongraph',
   						)
   				)
   		);  
   		$router->addRoute(
   				'eman_graph',
   				new Zend_Controller_Router_Route(
   						'graphall',
   						array(
   								'module' => 'graph',
   								'controller'   => 'index',
   								'action'       => 'allgraph',
   						)
   				)
   		); 
   		$router->addRoute(
  				'graph_item_ajax',
  				new Zend_Controller_Router_Route(
  						'graph/:itemid', 
  						array(
  								'module' => 'graph',
  								'controller'   => 'index',
  								'action'       => 'ajaxitemgraph',
  								'itemid'					=> ''
  						)
  				)
  		);   		  		 
  }

  function hookDefineAcl($args)
  {
  	$acl = $args['acl'];
  }
  
public function getGraphOptions() {
    $options = "var options = {
    	  nodes:{
    	    borderWidth: 3,
    	    borderWidthSelected: 4,
    	    brokenImage:undefined,
    	    fixed: {
    	      x:false,
    	      y:false
    	    },
          shape: 'icon', 'icon': {'size': 50, 'face': 'FontAwesome', 'code': '\uf15c', 'color': '#899466'},    	    
    	    font: {
    	      color: '#343434',
    	      size: 14, // px
    	      face: 'arial',
    	      background: 'none',
    	      strokeWidth: 0, // px
    	      strokeColor: '#ffffff',
    	      align: 'center'
    	    },
    	    group: 'fictions',
    	    hidden: false,
    	    label: undefined,
    	    labelHighlightBold: true,
    	    level: undefined,
    	    mass: 1.5,
    	    scaling: {
    	      min: 5,
    	      max: 30,
    	      label: {
    	        enabled: true,
    	        min: 10,
    	        max: 10,
    	        maxVisible: 30,
    	        drawThreshold: 5
    	      },
    	    },
    	    shadow:{
    	      enabled: false,
    	      color: 'rgba(0,0,0,0.5)',
    	      size:10,
    	      x:5,
    	      y:5
    	    },
    	    size: 25,
    	    title: undefined,
    	    value: undefined,
/*
    	    widthConstraint: {
      	    maximum:25,
      	 },
*/
    	  },
    	  edges: {
    	    smooth: {
    	      type: 'discrete',
    	      roundness: 0.2
    	    },
    	    arrows: {
    	      to:     {enabled: true, scaleFactor:1, type:'arrow'},
    	      middle: {enabled: false, scaleFactor:1},
    	      from:   {enabled: false, scaleFactor:1}
    	    },
    	    arrowStrikethrough: true,
    	    color: {
    	      color:'#848484',
    	      highlight:'#848484',
    	      hover: '#33ee33',
    	      inherit: false,
    	      opacity:1.0
    	    },
    	    dashes: false,
    	    font: {
    	      color: '#343434',
    	      size: 10, // px
    	      face: 'arial',
    	      background: 'none',
    	      strokeWidth: 1, // px
    	      strokeColor: '#ffffff',
    	      align:'middle'
    	    },
    	    hidden: false,
    	    hoverWidth: 1.5,
    	    label: undefined,
    	    labelHighlightBold: true,
    	    length: undefined,
    	    scaling:{
    	      min: 1,
    	      max: 15,
    	      label: {
    	        enabled: true,
    	        min: 1,
    	        max: 30,
    	        maxVisible: 30,
    	        drawThreshold: 15
    	      },
    	    },
    	    selectionWidth: 1,
    	    selfReferenceSize:20,
    	    shadow:{
    	      enabled: false,
    	      color: 'rgba(0,0,0,0.5)',
    	      size:10,
    	      x:5,
    	      y:5
    	    },
    	    smooth: {
    	      enabled: true,
    	      type: 'dynamic',
    	      roundness: 1
    	    },
    	    title:undefined,
    	    width: 1,
    	    widthConstraint: 200,    	    
    	    value: 1
    	  },
    	  layout: {
    	    randomSeed: 15,
    	    improvedLayout:true,
    	    hierarchical: {
    	      enabled:false,
    	      levelSeparation: 150,
    	      nodeSpacing: 30,
    	      treeSpacing: 20,
    	      direction: 'LR',        // UD, DU, LR, RL
    	      sortMethod: 'hubsize'   // hubsize, directed
    	    }
    	  },
    	  interaction:{
    	    keyboard: false,
    	    navigationButtons: true,    	    
    	    zoomView: true,
    	  }, 
    	  physics: {
      	  enabled: true,
      	  solver:'repulsion',
      	  stabilization: {
            enabled: true,            	    	         
       	  },
       	  repulsion: {
         	  centralGravity: 0,
         	  springLength: 100,
         	  nodeDistance:150,
         	},
//           adaptiveTimestep: true,        	  
        },
        manipulation: {
          enabled: false,             	    	         
        },
        interaction: {
          dragNodes: false,             	    	         
          navigationButtons: true,
          hover: true,
          hoverConnectedEdges: true,
        },
/*
        configure: {
          enabled: true,             	    	         
        },
*/
    };";

    return $options;  
  }
     
}
