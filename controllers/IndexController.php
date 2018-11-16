<?php
class Graph_IndexController extends Omeka_Controller_AbstractActionController 
{
  public function init() {
    // TODO : IHM admin pour couleurs collections
		$this->collectionsPalette = array("#9a822f","#6fc5e3","#984a36","#77e4d3","#d5726d","#9adf9e","#f39672","#368097","#efc583","#57afb0","#9f6134","#47a98a","#d28976","#51955c","#9a635a","#b7d481","#8c6c4f","#cae6b9","#7c6027","#e8de9e","#3f7455","#db9f6c","#4d6b26","#dcb19a","#61642e","#8bb68a","#ac8a56","#819247","#bdb16d","#899466");      
		$this->iconTypes = unserialize(get_option('graph_preferences'));  
		$this->legend = array();  
  }
    
	public function timelineAction() {
		$items = get_records('Item', array(), 10000);
    // TODO : loop sur collections
//   	$items =  array_merge(get_records('Item', array('collection' => 2), 10000), get_records('Item', array('collection' => 3), 10000));
		$nbItems = 500;				
		$nodes = [];
		foreach ($items as $num => $item) {
			$itemId = metadata($item, 'id');
			$itemTitle = addslashes(metadata($item, array('Dublin Core', 'Title'), array('snippet' => 100, 'no_escape' => false)));
			$itemType = metadata($item, 'item_type_name');
// 			$itemDate = metadata($item, 'added');
			$itemDate = metadata($item, array('Dublin Core', 'Date'), array('all' => true));
      if (! $itemDate) : continue; endif;
			$time = strtotime($itemDate[0]);
			$itemDateStart = date('Y-m-d', $time);
			$itemDateEnd = '';			
   		$className = 'bleu';			
   		$type= 'box';
			if (isset($itemDate[1])) {
//   		Zend_Debug::dump($itemDate[1]);
    		$timeEnd = strtotime($itemDate[1]);
//         if ($timeEnd - $time > 86400) {
        if ($timeEnd > $time) {
      		$itemDateEnd = ", end: '" . date('Y-m-d', $timeEnd) . "'";  			      		
          $className = 'vert';
          $type = 'range';
    		} else {
      		continue;
    		}
			} 
      echo $num . ' : ' . $time . '/' . $timeEnd . '|' . $itemDate[0] . ' / ' . $itemDate[1] .'<br />'; 
			$borderColor = "#9999999";
			if (! $itemTitle) {
				$itemTitle = "[Sans Titre]";
			}
				$nodes[$itemId] =  "{id: $itemId, content: '$itemTitle', type: '$type', className: '$className', start:'$itemDateStart' $itemDateEnd}";
		}		
		$this->view->nodes = $nodes;
		$this->view->content = '';
	}

	public function allgraphAction()
	{
		$db = get_db();	
		$items = get_records('Item', array(), 500);
		$nbItems = 500;
		$collections = get_records('Collection', array('all' => true));
		$nodes = $edges = array();
		$relationCounter = 0;
		$connected = array();
		foreach ($items as $num => $item) {
			$itemId = metadata($item, 'id');
			$itemTitle = addslashes(metadata($item, array('Dublin Core', 'Title'), array('snippet' => 100, 'no_escape' => false)));
			$itemLabel = $itemTitle;
			if (strlen($itemLabel) > 25) {
        $itemLabel = substr($itemLabel, 0, 20) . ' ...';  			
			}
			if (! $itemTitle) {
				$itemTitle = "[Sans Titre]";
			}
			// Couleur selon collection 
  		$itemCollection = get_collection_for_item($item);
  		if ($itemCollection) {    		
    		$nodeColor = $this->getCollectionColor($itemCollection->id);
  		} else {
    		$nodeColor = '#333333';
  		}
			// Icône selon type
			$itemType = $this->getTypeIcon(metadata($item, 'item_type_name'));	
			$itemIcon = '{"size": 50, "face": "FontAwesome", "code": "\\' . $itemType . '", "color": "' . $nodeColor . '"}';		    
			$nodes[$itemId] =  '{"id": ' . $itemId . ', "label": "' . $itemLabel . '", "title": "' . $itemTitle . '", "shape": "icon", "icon": ' . $itemIcon . '}';
			// Fetch relations
			$relations = get_db()->getTable('ItemRelationsRelation')->findBySubjectItemId($itemId);
			$relation_present = false;
			foreach ($relations as $relationNum => $relation) {
				$objectId = $relation['object_item_id'];
				$object = get_record_by_id('Item', $objectId);
				try {
					$objectTitle = addslashes(metadata($object, array('Dublin Core', 'Title'), array('snippet' => 100)));
				} catch (Exception $e) {
					$objectTitle = "Notice manquante (Item $objectId)";
				}
				$textRelation = addslashes($relation['property_label']);
				$textRelation = get_db()->getTable('ItemRelationsRelation')->translate($textRelation, $relation['vocabulary_namespace_prefix']);				
				$textComment = $relation['relation_comment'];
				$connected[] = $objectId;
				$connected[] = $itemId;
				$edges[$relationCounter] = '{"from": ' . $objectId . ', "to": ' . $itemId . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color": {"color" : "#cccccc", "highlight" : "#cceecc"}}';
				
/*
				color:'#848484',
    	      highlight:'#ee0000',
    	      hover: '#ee0000',
    	      inherit: false,
*/
				$relationCounter++;
				$relation_present = true;
			}
		}
		$connected = array_values(array_unique($connected));
  	$nodes = array_intersect_key($nodes, array_flip($connected));
		$this->view->nodes = $nodes;
		$this->view->edges = $edges;
		$this->view->legende = $this->buildLegend();
    $page = $db->getTable('SimplePagesPage')->find(8);
    $this->view->simple_pages_page = $page;
	}
	 
	public function itemgraphAction()
	{
	  $request = Zend_Controller_Front::getInstance()->getRequest();
  	$params = $request->getParams();
  	$itemId = $params['itemid'];	
    $itemInfo = $this->fetchInfo($itemId);
   
    $this->view->itemId = $params['itemid'];
    $this->view->nodes = $itemInfo['nodes'];
    $this->view->edges = $itemInfo['edges'];
    $this->view->links = $itemInfo['links'];		
    
    $this->view->legende = $this->buildLegend();	    
	}

	public function collectiongraphAction()
	{
		$nodes = $edges = array();  	
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$params = $request->getParams();
		$collectionId = $params['collectionid'];		

		$colors = array(
				'edges' => array(
						'précède' => "#77ffff",
						'est en relation avec' => "#ff77ff",
						'Is Required By' => "#ffff77",
						'est en écho réciproque à' => "#77ffff",
						'est un état résultant de' => "#ff77ff",
						'est un état fabriqué par l\'éditeur à partir de' => "#ffff77",
						'est influencé par' => "#77ffff",
						'est en écho unilatéral avec' => "#ff77ff",
						'est une recopie intégrale de' => "#ffff77",
						'est une recopie avec ajouts de' => "#77ffff",
				),
		);
		$items = get_records('Item', array('collection_id' => $collectionId), 500);
		$relationCounter = 0;
		$connected = array();
		foreach ($items as $num => $item) {
			$itemId = metadata($item, 'id');
				try {
					$objectTitle = addslashes(metadata($item, array('Dublin Core', 'Title'), array('snippet' => 100)));
				} catch (Exception $e) {
					$objectTitle = "Notice manquante (Item $objectId)";
				}			
  		$objectLabel = $objectTitle;
  		if (strlen($objectLabel) > 25) {
        $objectLabel = substr($objectLabel, 0, 20) . ' ...';  			
  		}					
			// Couleur selon collection 
  		$itemCollection = get_collection_for_item($item);
  		if ($itemCollection) {    		
    		$nodeColor = $this->getCollectionColor($itemCollection->id);
  		} else {
    		$nodeColor = '#333333';
  		}
			// Icône selon type
			$itemType = $this->getTypeIcon(metadata($item, 'item_type_name'));	
			$itemIcon = '{"size": 50, "face": "FontAwesome", "code": "\\' . $itemType . '", "color": "' . $nodeColor . '"}';		    
			$nodes[$itemId] =  '{"id": ' . $itemId . ', "label": "' . $objectLabel. '", "title": "' . $objectTitle . '", "shape": "icon", "icon": ' . $itemIcon . '}';			// Fetch relations
			$relations = get_db()->getTable('ItemRelationsRelation')->findBySubjectItemId($itemId);
			$relation_present = false;
			foreach ($relations as $relationNum => $relation) {	
				$objectId = $relation['object_item_id'];
				$object = get_record_by_id('Item', $objectId);
        $objectTitle = addslashes(metadata($item, array('Dublin Core', 'Title'), array('snippet' => 100, 'no_escape' => false)));				
				$textRelation = addslashes($relation['property_label']);
				$textRelation = get_db()->getTable('ItemRelationsRelation')->translate($textRelation, $relation['vocabulary_namespace_prefix']);		
				$textComment = $relation['relation_comment'];
				if (isset($colors['edges'][$textRelation])) {
					$edgeColor = $colors['edges'][$textRelation];
				} else {
					$edgeColor = '#888888';
				}
				$connected[] = $objectId;
				$connected[] = $itemId;
				$edges[$relationCounter] = '{"from":' . $objectId . ', "to": ' . $itemId . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color":"' . $edgeColor . '"}';
				$relationCounter++;
				$relation_present = true;
			}
		}

		$this->view->nodes = $nodes;
		$this->view->edges = $edges;
    $this->view->legende = $this->buildLegend();			
	
	}
	
	private function fetchInfo($itemId)
	{
		$item = get_record_by_id('Item', $itemId);
		$itemTitle = addslashes(metadata($item, array('Dublin Core', 'Title'), array('snippet' => 100)));
		$itemLabel = $itemTitle;
		if (strlen($itemLabel) > 25) {
      $itemLabel = substr($itemLabel, 0, 20) . ' ...';  			
		}		
		$itemType = $this->getTypeIcon(metadata($item, 'item_type_name'));
		$itemCollection = get_collection_for_item($item);		
		if ($itemCollection) {    		
  		$nodeColor = $this->getCollectionColor($itemCollection->id);
		} else {
  		$nodeColor = '#333333';
		}		
		$itemIcon = '{"size": 75, "face": "FontAwesome", "code": "\\' . $itemType . '", "color": "' . $nodeColor . '"}';		
				
  	// Relations : Item Sujet
		$relations = get_db()->getTable('ItemRelationsRelation')->findBySubjectItemId($itemId);
		$nodes = $edges = array();
		$links = "";
		
		$nodes[1] = '{"id": 0, "label": "' . $itemLabel . '", "title": "' . $itemTitle .'", "level": 2, "shape": "icon", "icon": ' . $itemIcon . ', "font" : "20px arial #111111"}';
		$links .= '{"node" : 0, "item" : ' . $itemId . '}';
		$index = 2;
		foreach ($relations as $num => $relation) {
			$objectId = $relation['object_item_id'];
			$object = get_record_by_id('Item', $objectId);
			if (! $object) : continue; endif;
			$objectTitle = addslashes(metadata($object, array('Dublin Core', 'Title'), array('snippet' => 100)));
			$objectLabel = $objectTitle;
			if (strlen($objectLabel) > 25) {
        $objectLabel = substr($objectLabel, 0, 20) . ' ...';  			
			}			
			$itemType = $this->getTypeIcon(metadata($object, 'item_type_name'));
  		$itemCollection = get_collection_for_item($object);		
  		if ($itemCollection) {    		
    		$nodeColor = $this->getCollectionColor($itemCollection->id);
  		} else {
    		$nodeColor = '#333333';
  		}
			$itemIcon = '{"size": 50, "face": "FontAwesome", "code": "\\' . $itemType . '", "color": "' . $nodeColor . '"}';		
			$nodes[$index] = '{"id": ' . $index . ', "label": "' . $objectLabel . '", "title": "' . $objectTitle . '", "level": 1, "shape": "icon", "icon": ' . $itemIcon . '}';
			$links .= ',{"node" :' . $index . ', "item" : ' . $objectId . '}';		
			$textRelation = $relation['property_label'];
			$textRelation = get_db()->getTable('ItemRelationsRelation')->translate($textRelation, $relation['vocabulary_namespace_prefix']);			
			$textComment = $relation['relation_comment'];
			$edges[$index] = '{"from": 0, "to": ' . $index . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color":"#cccccc"}';
			$index++;
		}

		// Relations : Item Objet
		$relations = get_db()->getTable('ItemRelationsRelation')->findByObjectItemId($itemId);
		foreach ($relations as $num => $relation) {
			$objectId = $relation['subject_item_id'];
			$object = get_record_by_id('Item', $objectId);
// 			set_current_record('Item', $object);
      if (!$object) : echo $objectId; continue; endif;
  		$itemCollection = get_collection_for_item($object);		
  		if ($itemCollection) {    		
    		$nodeColor = $this->getCollectionColor($itemCollection->id);
  		} else {
    		$nodeColor = '#333333';
  		}
			$objectTitle = addslashes(metadata($object, array('Dublin Core', 'Title'), array('snippet' => 100)));
			if ($objectTitle == '') {$objectTitle = "Notice " . $objectId;};			
			$objectLabel = $objectTitle;
			if (strlen($objectLabel) > 25) {
        $objectLabel = substr($objectLabel, 0, 20) . ' ...';  			
			}				
			$relationText = $relation['subject_relation_text'];
			$itemType = $this->getTypeIcon(metadata($object, 'item_type_name'));	
			$itemIcon = '{"size": 50, "face": "FontAwesome", "code": "\\' . $itemType . '", "color": "' . $nodeColor . '"}';		
			$nodes[$index] = '{"id": ' . $index . ', "label": "' . $objectLabel . '", "title": "' . $objectTitle . '", "level": 3, "shape": "icon", "icon": ' . $itemIcon . '}';
			$links .= ',{"node" : ' . $index . ', "item" : '. $objectId . '}';				
			$textRelation = $relation['property_label'];
			$textRelation = get_db()->getTable('ItemRelationsRelation')->translate($textRelation, $relation['vocabulary_namespace_prefix']);						
			if ($relation['relation_comment']) {
				$textComment = $relation['relation_comment'];
			} else {
				$textComment = "No comment";
			}
			$edges[$index] = '{"from": ' . $index . ', "to": 0, "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color":"#cccccc"}';
			$index++;
		}
		$itemInfo['nodes'] = $nodes;
		$itemInfo['edges'] = $edges;
		$itemInfo['links'] = $links;
	
		return $itemInfo;
  }

	public function ajaxitemgraphAction() {
	  $request = Zend_Controller_Front::getInstance()->getRequest();
  	$params = $request->getParams();
  	$itemId = $params['q'];	  	
  	// Call to item nodes and edges population function
  	$itemInfo = $this->fetchInfo($itemId);
  	$nodes = implode(',', array_values($itemInfo['nodes']));
  	$edges = implode(',', array_values($itemInfo['edges']));
    
    $json = '{"nodes": [' . $nodes . '], "edges": [' . $edges . ']}' ;
    echo $json;        
  	exit;  	
  } 
  
  private function buildLegend() {
    $legend = "";  
    $i = 0;
    $x = -500;
    foreach ($this->legend['typeIcons'] as $type => $icon) {
      $icon = '{"size": 40, "face": "FontAwesome", "code": "\\u' . $icon . '", "color": "#bbbbb"}';
      $x += 120;
      $legend['icons'][] = '{"id": ' . ($i + 1000) . ', "x": ' . $x . ', "y": -50, "font": {"size": 20, "multi": true}, "label": "' . strip_tags($type) . '", "widthConstraint": {"maximum" : 10}, "value": 1, "fixed": "true", "physics": false, "shape": "icon", "icon": ' . $icon . '}';
      $i++;
    }
    $x = -500;
    foreach ($this->legend['collectionColors'] as $id => $color) {
      // Collection Name
      $collection = get_record_by_id('Collection', $id);
      $collectionName = $title = strip_tags(metadata($collection, array('Dublin Core' ,'Title')));
      if (strlen($collectionName) > 25) {
        $collectionName = substr($collectionName, 0, 20) . ' ...';
      }
      $icon = '{"size": 40, "face": "FontAwesome", "code": "\uf111", "color": "' . $color . '"}';
      $x += 120;
      $legend['colors'][] = '{"id": ' . ($i + 1000) . ', "x": ' . $x . ', "y": +10, "font": {"size": 20, "multi": "html"}, "label": "' . addslashes($collectionName) . '","title": "' . $title . '", "widthConstraint": {"maximum" : 10}, "value": 1, "fixed": "true", "physics": false, "shape": "icon", "icon": ' . $icon . '}';
      $i++;
    }    
    return $legend;
  }
		
  private function getTypeIcon($itemType) {
    $itemType = str_replace(' ', '', $itemType);
    if (isset($this->iconTypes[$itemType])) {
   		$this->legend['typeIcons'][$itemType] = $this->iconTypes[$itemType];
      return 'u' . $this->iconTypes[$itemType];
    }
		return 'uf29c';    
  }
  
  private function getCollectionColor($collectionId) {
    if (!$collectionId) {return "#ccc";};
		if (isset($this->collectionColors[$collectionId])) {
  		return $this->collectionColors[$collectionId];  		
		}
		$color = array_pop($this->collectionsPalette);
		$this->collectionColors[$collectionId] = $color;
		$this->legend['collectionColors'][$collectionId] = $color;
		return $color;		
  }	
}

