<?php
class Graph_IndexController extends Omeka_Controller_AbstractActionController 
{
  public function init() {
    // TODO : IHM admin pour couleurs collections
		$this->collectionsPalette = array("#9a822f", "#6fc5e3", "#984a36", "#77e4d3", "#d5726d", "#9adf9e", "#f39672", "#368097", "#efc583", "#57afb0", "#9f6134", "#47a98a", "#d28976", "#51955c", "#9a635a", "#b7d481", "#8c6c4f", "#cae6b9", "#7c6027", "#e8de9e", "#3f7455", "#db9f6c", "#4d6b26", "#dcb19a", "#61642e", "#8bb68a", "#ac8a56", "#819247" ,"#bdb16d", "#899466");
		$collections = get_recent_collections(50);
// 		Zend_Debug::dump($collections);
		$collectionsColors = array();
		foreach ($collections as $id => $collection) {
  		$collectionsColors[$collection['id']] = array_pop($this->collectionsPalette);
		}
		$this->iconTypes = unserialize(get_option('graph_preferences'));  
		// TODO : Initialiser les couleurs des collections.
// 		$this->legend = array('typeIcons' => $this->iconTypes, 'collectionColors' => $collectionsColors);  
		
		$this->caption = array('typeIcons' => [], 'collectionColors' => []);  		
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
	
	public function filegraphAction()
	{
	  $request = Zend_Controller_Front::getInstance()->getRequest();
  	$params = $request->getParams();
  	$fileId = $params['fileid'];	
		$file = get_record_by_id('File', $fileId);
		$itemId = $file->item_id;
		$item = get_record_by_id('Item', $itemId);
		$fileTitle = addslashes(metadata($file, array('Dublin Core', 'Title'), array('snippet' => 100)));
		$fileTitle == '' ? $fileTitle = 'Sans Titre' : null;
		if (strlen($fileTitle) > 25) {
      $fileTitle = substr($fileTitle, 0, 20) . ' ...';  			
		}		
		$itemType = $this->getTypeIcon(metadata($item, 'item_type_name'));
		$itemCollection = get_collection_for_item($item);		
		if ($itemCollection) {    		
  		$nodeColor = $this->getCollectionColor($itemCollection->id);
		} else {
  		$nodeColor = '#333333';
		}		
		$fileIcon = '{"size": 75, "face": "FontAwesome", "code": "\\uf02d", "color": "' . $nodeColor . '"}';		
				
		$nodes = $edges = array();
		$links = "";
		
		$nodes[1] = '{"id": 0, "label": "' . $fileTitle . '", "title": "' . $fileTitle .'", "level": 2, "shape": "icon", "icon" : ' . $fileIcon . ', "font" : "20px arial #111111"}';
		$links .= '{"node" : 0, "file" : ' . $fileId . '}';
		$index = 2;
  	// Relations : File Sujet
		$relations = get_db()->getTable('FileRelationsRelation')->findBySubjectFileId($fileId);
		foreach ($relations as $num => $relation) {
			$objectId = $relation['object_file_id'];
			$object = get_record_by_id('File', $objectId);
			if (! $object) : continue; endif;
			$objectTitle = addslashes(metadata($object, array('Dublin Core', 'Title'), array('snippet' => 100)));
			$objectLabel = $objectTitle;
			if (strlen($objectLabel) > 25) {
        $objectLabel = substr($objectLabel, 0, 20) . ' ...';  			
			}			
  		$itemCollection = get_collection_for_item($object);		
  		if ($itemCollection) {    		
    		$nodeColor = $this->getCollectionColor($itemCollection->id);
  		} else {
    		$nodeColor = '#333333';
  		}
			$fileIcon = '{"size": 50, "face": "FontAwesome", "code": "\\uf02d", "color": "' . $nodeColor . '"}';		
			$nodes[$index] = '{"id": ' . $index . ', "label": "' . $objectLabel . '", "title": "' . $objectTitle . '", "level": 1, "shape": "icon", "icon": ' . $fileIcon . '}';
			$links .= ',{"node" :' . $index . ', "file" : ' . $objectId . '}';		
			$textRelation = $relation['property_label'];
			$textRelation = get_db()->getTable('ItemRelationsRelation')->translate($textRelation, $relation['vocabulary_namespace_prefix']);			
			$textComment = $relation['relation_comment'];
			$edges[$index] = '{"from": 0, "to": ' . $index . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color":"#cccccc"}';
			$index++;
		}
		// Relations : File Objet
		$relations = get_db()->getTable('FileRelationsRelation')->findByObjectFileId($fileId);
		foreach ($relations as $num => $relation) {
			$objectId = $relation['subject_file_id'];
			$object = get_record_by_id('File', $objectId);
// 			set_current_record('Item', $object);
      if (!$object) : echo $objectId; continue; endif;
  		$itemCollection = get_collection_for_item($object);		
  		if ($itemCollection) {    		
    		$nodeColor = $this->getCollectionColor($itemCollection->id);
  		} else {
    		$nodeColor = '#333333';
  		}
			$objectTitle = addslashes(metadata($object, array('Dublin Core', 'Title'), array('snippet' => 100)));
			if ($objectTitle == '') {$objectTitle = "Fichier " . $objectId;};			
			$objectLabel = $objectTitle;
			if (strlen($objectLabel) > 25) {
        $objectLabel = substr($objectLabel, 0, 20) . ' ...';  			
			}				
			$relationText = $relation['subject_relation_text'];
// 			$itemType = $this->getTypeIcon(metadata($object, 'item_type_name'));	
			$itemIcon = '{"size": 50, "face": "FontAwesome", "code": "\\' . $itemType . '", "color": "' . $nodeColor . '"}';		
			$nodes[$index] = '{"id": ' . $index . ', "label": "' . $objectLabel . '", "title": "' . $objectTitle . '", "level": 3, "shape": "icon", "icon": ' . $itemIcon . '}';
			$links .= ',{"node" : ' . $index . ', "file" : '. $objectId . '}';				
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
		
    $this->view->fileId = $params['fileid'];
    $this->view->nodes = $nodes;
    $this->view->edges = $edges;
    $this->view->links = $links;		
    
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
				$edges[$relationCounter] = '{"from":' . $itemId . ', "to": ' . $objectId . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color":"' . $edgeColor . '"}';
				$relationCounter++;
				$relation_present = true;
			}
		}
    $this->view->collectionId = $params['collectionid'];
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
    $legend = [];  
    $legend['icons'] = [];
    $i = 0;
    $j = 0;
    $x = -550;
    // Calcul de la hauteur du conteneur
    $nbElements = ceil(count($this->caption['typeIcons']) / 8) + ceil(count($this->caption['collectionColors']) / 8) + 2;
    $this->view->height = 60 * $nbElements;
    // Décalage vertical initial
    $y = -30 * $nbElements;        
    $icon = '{"size": 30, "face": "FontAwesome", "code": "\\uf0da", "color": "#eeeeee"}';    
    $legend['icons'][] = '{"id": "9999999", "x": ' . ($x + 120) . ', "y": ' . $y . ', "font": {"size": 24, "multi": "html"}, "label": "<b>Icône(s)</b>", "value": 1, "fixed": "true", "physics": false, "shape": "icon", "icon": ' . $icon . '}';  
    $y += 60;      
    foreach ($this->caption['typeIcons'] as $type => $icon) {
      if ($j >= 8) {
        $x = -550;
        $y += 60;
        $j = 0;
      }  
      $icon = '{"size": 40, "face": "FontAwesome", "code": "\\u' . $icon . '", "color": "#bbbbb"}';
      $x += 120;
      $legend['icons'][] = '{"id": ' . ($i + 1000) . ', "x": ' . $x . ', "y": '. $y . ', "font": {"size": 20, "multi": true}, "label": "' . strip_tags($type) . '", "widthConstraint": {"maximum" : 10}, "value": 1, "fixed": "true", "physics": false, "shape": "icon", "icon": ' . $icon . '}';
      $i++;
      $j++;
    }
    $x = -550;
    $y += 60;
    $icon = '{"size": 30, "face": "FontAwesome", "code": "\\uf0da", "color": "#eeeeee"}';    
    $legend['icons'][] = '{"id": "99999999", "x": ' . ($x + 120) . ', "y": ' . $y . ', "font": {"size": 24, "multi": "html"}, "label": "<b>Collection(s)</b>", "value": 1, "fixed": "true", "physics": false, "shape": "icon", "icon": ' . $icon . '}';    
    $x = -550;
    $y += 60;
    $j = 0;   
    foreach ($this->caption['collectionColors'] as $id => $color) {
      if ($j >= 8) {
        $x = -550;
        $y += 60;
        $j = 0;
      }  
      // Collection Name
      $collection = get_record_by_id('Collection', $id);
//       if (! $collection) : continue; endif;       
      $collectionName = $title = strip_tags(metadata($collection, array('Dublin Core' ,'Title')));
      if (strlen($collectionName) > 25) {
        $collectionName = substr($collectionName, 0, 20) . ' ...';
      }
      $icon = '{"size": 40, "face": "FontAwesome", "code": "\\uf029", "color": "' . $color . '"}';
      $x += 120;
      $legend['colors'][] = '{"id": ' . ($i + 1000) . ', "x": ' . $x . ', "y": '. $y . ', "font": {"size": 20, "multi": "html"}, "label": "' . addslashes($collectionName) . '","title": "' . addslashes($title) . '", "widthConstraint": {"maximum" : 10}, "value": 1, "fixed": "true", "physics": false, "shape": "icon", "icon": ' . $icon . '}';
      $i++;
      $j++;      
    }    
    return $legend;
  }


	public function timelineAction() {
		$items = get_records('Item', array(), 10000);
    // TODO : loop sur collections
//   	$items =  array_merge(get_records('Item', array('collection' => 2), 10000), get_records('Item', array('collection' => 3), 10000));
		$nodes = [];
		foreach ($items as $num => $item) {
			$itemId = metadata($item, 'id');
			$itemTitle = addslashes(metadata($item, array('Dublin Core', 'Title'), array('snippet' => 100, 'no_escape' => false)));
			$itemType = metadata($item, 'item_type_name');
// 			$itemDate = metadata($item, 'added');
			$itemDate = metadata($item, array('Dublin Core', 'Date'), array('all' => true));
      if (! $itemDate) : continue; endif;
      $date = date_create_from_format("Y", $itemDate[0]);
      if ($date === FALSE) {
  			$time = strtotime($itemDate[0]);
      } else {
    		$time = $date->getTimeStamp();          
      }			
      $itemDateStart = date('Y-m-d', $time);
			$itemDateEnd = '';			
   		$className = 'bleu';			
   		$type= 'box';
			if (isset($itemDate[1])) { 			
        $date = date_create_from_format("Y", $itemDate[1]);
        if ($date === FALSE) {
          $timeEnd = strtotime($date);
        } else {
      		$timeEnd = $date->getTimeStamp();          
        }
        if ($timeEnd > $time) {	
      		$itemDateEnd = ", end: '" . date('Y-m-d', $timeEnd) . "'";    					      		
          $className = 'vert';
          $type = 'range';
    		}
    	} 
//       echo $num . ' : ' . $time . '/' . $timeEnd . '|' . $itemDate[0] . ' / ' . $itemDate[1] .'<br />'; 
			$borderColor = "#9999999";
			if (! $itemTitle) {
				$itemTitle = "[Sans Titre]";
			}
      $nodes[$itemId] =  "{id: $itemId, content: '$itemTitle', type: '$type', className: '$className', start:'$itemDateStart' $itemDateEnd}";
		}		
		$this->view->nodes = $nodes;
		$this->view->content = '';
	}

		
  private function getTypeIcon($itemType) {
    $itemType = str_replace(' ', '', $itemType);
    if (isset($this->iconTypes[$itemType])) {
   		$this->caption['typeIcons'][$itemType] = $this->iconTypes[$itemType];
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
		$this->caption['collectionColors'][$collectionId] = $color;
		return $color;		
  }	
}

