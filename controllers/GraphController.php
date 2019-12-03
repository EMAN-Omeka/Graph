<?php 
  class Graph_GraphController extends Omeka_Controller_AbstractActionController 
{
  public function init() {
    $this->collectionColors = unserialize(get_option('graph_color_preferences')); 
		$this->iconTypes = unserialize(get_option('graph_preferences'));  
		$this->caption = array('typeIcons' => [], 'collectionColors' => []);  
		$this->nodes = $this->edges = [];
		$this->relationsCounter = 0;
		$this->view->pageTitle = 'Visualisation par graphe des relations du corpus';    	    		
  }  
	  	
  public function choixAction() {		   
    $form = $this->getChooseForm();
    $this->view->form2 = $this->getChooseRelationForm();
    $this->view->form = $form;
    $this->view->caption = [];   
   	if ($this->_request->isPost()) {
  		$formData = $this->_request->getPost();
   		if ($form->isValid($formData)) {  		
    		if (isset($formData['relations'])) {
      		$this->drawRelationNodes($formData['relations']);  
        } elseif (isset($formData['collections'])) {
          $this->extRelations = false;
          $formData['extRelations'] == true ? $this->extRelations = true : null; 
      		$this->drawCollections($formData['collections'], $formData['drawItems'], $formData['drawFiles']);		
      		$this->drawCollectionsRelations($formData['collections']);		        
          $this->view->caption = $this->buildCaption();           		
        }         		
  		}
  	}
    $this->view->nodes = $this->nodes;
    $this->view->edges = $this->edges;  	
    $this->view->icons = '';  	
    $this->view->colors = '';  	
  }
  
  public function drawCollections($collections, $drawItems = false, $drawFiles = false) {
    $content = '';
    $nodes = [];
    foreach ($collections as $id => $collectionId) {
      $collection = get_record_by_id('collection', $collectionId);
      $collectionTitle = metadata($collection, array('Dublin Core', 'Title'));
      if (isset($this->collectionColors[$collectionId])) {
     		$collectionColor = $this->collectionColors[$collectionId];         
      } else {
     		$collectionColor = "0000000";                 
      }
   		$this->caption['collectionColors'][$collectionId] = '#' . $collectionColor;
      $icon = "{'size': 70, 'face': 'FontAwesome', 'code': '\\uf029', 'color': '#$collectionColor'}"; 	    			   
      $this->nodes[] = "{'id': 10000$collectionId, 'label': '" . addslashes($collectionTitle) . "', 'title': '" . addslashes($collectionTitle) . "', 'shape': 'icon', 'icon': $icon}";
      if ($drawItems) {
        $this->drawItems($collectionId, $drawFiles);
      }
    } 
  }
  
  public function drawCollectionsRelations($collections) {
    foreach ($collections as $id => $collectionId) {
			$relations = get_db()->getTable('CollectionRelationsRelation')->findBySubjectCollectionId($collectionId);
			foreach ($relations as $relationNum => $relation) {	
				$objectId = $relation['object_collection_id'];
				$subjectId = $relation['subject_collection_id'];		
				$textRelation = addslashes($relation['property_label']);
				$textRelation = get_db()->getTable('CollectionRelationsRelation')->translate($textRelation, $relation['vocabulary_namespace_prefix']);		
				if (isset($colors['edges'][$textRelation])) {
					$edgeColor = $colors['edges'][$textRelation];
				} else {
					$edgeColor = '#888888';
				}
				$connected[] = $objectId;
				$connected[] = $collectionId;
				$this->edges[$this->relationsCounter] = '{"from":10000' . $subjectId . ', "to": 10000' . $objectId . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color":"#555555", "highlight" : "#559955", "width" : 3}';
				$this->relationsCounter++;
      } 
    }
  }
  
  public function drawItems($collectionId, $drawFiles = false) {
 		$db = get_db();
 		$itemsIds = $db->query("SELECT id, public FROM `{$db->Items}` WHERE collection_id = $collectionId")->fetchAll();
    $items = [];
 		foreach ($itemsIds as $i => $itemId) {
      $items[] = get_record_by_id('Item' , $itemId['id']);
 		} 	
		foreach ($items as $id => $item) {
  		try {
    		$itemId = metadata($item, 'id');    		
  		} catch (Exception $e) {
				$objectTitle = "Notice privée (Item $itemId)";   
				continue; 		
      } 
				try {
					$objectTitle = addslashes(metadata($item, array('Dublin Core', 'Title'), array('snippet' => 100)));
				} catch (Exception $e) {
					$objectTitle = "Notice manquante (Item $itemId)";
				}   							
  		$objectLabel = $objectTitle;
  		if (strlen($objectLabel) > 25) {
        $objectLabel = substr($objectLabel, 0, 20) . ' ...';  			
  		}				
			// Icône selon type
			$itemType = metadata($item, 'item_type_name');
			if (isset($itemType)) {
  			$this->caption['typeIcons'][$itemType] = $this->getTypeIcon($itemType); 
  			$icon =	$this->caption['typeIcons'][$itemType];		
			} else {
  			$icon = "uf29c";
			}
			$itemIcon = '{"size": 50, "face": "FontAwesome", "code": "\\' . $icon . '", "color": "#' . $this->collectionColors[$collectionId] . '"}';		    
			$this->nodes[] =  '{"id": ' . $itemId . ', "label": "' . $objectLabel. '", "title": "' . $objectTitle . '", "shape": "icon", "icon": ' . $itemIcon . '}';  	
			$this->drawItemRelations($itemId, $collectionId); 	
			if ($drawFiles) {
        $this->drawFiles($itemId, $this->collectionColors[$collectionId]);			  			
			}
		}
  }

  public function drawItemRelations($itemId, $collectionId, $horsCollection = true) {
			$relations = get_db()->getTable('ItemRelationsRelation')->findBySubjectItemId($itemId);	
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
        $edgeColor = "#555555";
        try {
  				$objectItemCollection = get_collection_for_item($object);	          
        } catch (Exception $e) {
					$objectTitle = "Notice privée (Item $objectId)";
				}
				if ($objectItemCollection->id <> $collectionId) {
          if (! $this->extRelations) {continue;}
          $edgeColor = "#559999";
          $textRelation .= " (hors-collection)";          
				}
				$this->edges[$this->relationsCounter] = '{"from": ' . $itemId . ', "to": ' . $objectId . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color": {"color" : "' . $edgeColor . '", "highlight" : "#559955"}, "width" : 3}';
				$this->relationsCounter++;
			}
			$this->edges[$this->relationsCounter] = '{"from": ' . $itemId . ', "to": 10000' . $collectionId . ', "label": "appartient à la collection", "title": "appartient à la collection", "color": {"color" : "#cccccc", "highlight" : "#cceecc"}, "width" : .5 }';
			$this->relationsCounter++;					
		}
			
  public function drawFiles($itemId, $collectionColor) {
 		$db = get_db();
 		$filesIds = $db->query("SELECT id FROM `{$db->Files}` WHERE item_id = $itemId")->fetchAll();
 		$this->nbFiles = count($filesIds);
    $files = [];
 		foreach ($filesIds as $i => $fileId) {
      $files[] = get_record_by_id('File' , $fileId['id']);
 		} 	
		foreach ($files as $id => $file) {
  			$fileId = metadata($file, 'id');
  				try {
  					$fileLabel = addslashes(metadata($file, array('Dublin Core', 'Title'), array('snippet' => 100)));
  					$fileLabel == '' ? $fileLabel = 'Sans titre' : null;
  				} catch (Exception $e) {
  					$fileLabel = "Fichier manquant (File $fileId)";
  				}			
    		if (strlen($fileLabel) > 25) {
          $fileLabel = substr($fileLabel, 0, 20) . ' ...';  			
    		} 
  			$fileIcon = '{"size": 50, "face": "FontAwesome", "code": "\\uf02d", "color": "#' . $collectionColor . '"}';		    
  			$this->nodes[] =  '{"id": 20000' . $fileId . ', "label": "' . $fileLabel. '", "title": "' . $fileLabel . '", "shape": "icon", "icon": ' . $fileIcon . '}';
  			$this->drawFilesRelations($fileId, $itemId); 	
			} 	
    } 		
    
    public function drawFilesRelations($fileId, $itemId) { 
			$relations = get_db()->getTable('FileRelationsRelation')->findBySubjectFileId($fileId);	
			foreach ($relations as $relationNum => $relation) {
				$objectId = $relation['object_file_id'];
				$object = get_record_by_id('File', $objectId);
				$objectItem = get_record_by_id('Item', $object->item_id);
				if ($objectItem) {
  				$objectCollection = get_collection_for_item($objectItem);				  				
				}
				$subject = get_record_by_id('File', $fileId);
				$subjectItem = get_record_by_id('Item', $subject->item_id);
				$subjectCollection = get_collection_for_item($subjectItem);
				
				$item = get_record_by_id('Item', $itemId);
				try {
					$objectTitle = addslashes(metadata($object, array('Dublin Core', 'Title'), array('snippet' => 100)));
				} catch (Exception $e) {
					$objectTitle = "Fichier manquant (Fichier $objectId)";
				}
				$textRelation = addslashes($relation['property_label']);
				$textRelation = get_db()->getTable('ItemRelationsRelation')->translate($textRelation, $relation['vocabulary_namespace_prefix']);				
				// Couleur flèche selon lien inter-collection ou non
        $edgeColor = "#555555";
				$objectFileCollection = get_collection_for_item($item);	
				if ($subjectCollection->id <> $objectCollection->id) {
          if (! $this->extRelations) {continue;}  				
          $edgeColor = "#559999";
          $textRelation .= " (hors-collection)";
				}
				$this->edges[$this->relationsCounter] = '{"from": 20000' . $fileId . ', "to": 20000' . $objectId . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color": {"color" : "' . $edgeColor . '", "highlight" : "#559955"}, "width" : 3}';
				$this->relationsCounter++;			
			}
  			$this->edges[$this->relationsCounter] = '{"from": 20000' . $fileId . ', "to": ' . $itemId . ', "label": "fait partie de la notice", "title": "fait partie de la notice", "color": {"color" : "#cccccc", "highlight" : "#cceecc"}, "width" : .5 }';
        $this->relationsCounter++;				
    }
    
    private function drawRelationNodes($propertyId) {
  		$db = get_db();
  		$items = $db->query("SELECT r.subject_item_id sid, r.object_item_id oid FROM `{$db->ItemRelationsRelation}` r RIGHT JOIN `{$db->Items}` i ON i.id = r.subject_item_id WHERE property_id = " . $propertyId)->fetchAll();			  			 
			foreach($items as $i => $itemIds) {
  			$subjectItem = get_record_by_id('Item', $itemIds['sid']);
  			try {
    			$title = metadata($subjectItem, array('Dublin Core', 'Title'));    			
  			} catch  (Exception $e) {
					$objectTitle = "Notice privée (Item " . $itemIds['sid'] . ")";
					continue;
				}
				$itemType = metadata($subjectItem, 'item_type_name');
  			if (isset($itemType)) {
    			$this->caption['typeIcons'][$itemType] = $this->getTypeIcon($itemType); 
    			$icon =	$this->caption['typeIcons'][$itemType];		
  			} else {
    			$icon = "uf29c";
  			}  			
        $collection = get_collection_for_item($subjectItem); 
     		$collectionColor = $this->collectionColors[$collection->id]; 
     		$this->caption['collectionColors'][$collection->id] = '#' . $collectionColor;
        $itemIcon = '{"size": 50, "face": "FontAwesome", "code": "\\' . $icon . '", "color": "#' . $collectionColor . '"}';	         			
  			$this->nodes[$itemIds['sid']] =  '{"id": ' . $subjectItem->id . ', "label": "' . addslashes($title) . '", "title": "' . addslashes($title) . '", "shape": "icon", "icon": ' . $itemIcon . '}';			
  			$objectItem = get_record_by_id('Item', $itemIds['oid']);
  			try {
    			$title = metadata($objectItem, array('Dublin Core', 'Title'));    			
  			} catch  (Exception $e) {
					$objectTitle = "Notice privée (Item " . $itemIds['sid'] . ")";
					continue;
				}
  			$this->nodes[$itemIds['oid']] =  '{"id": ' . $objectItem->id . ', "label": "' . addslashes($title) . '", "title": "' . addslashes($title) . '", "shape": "icon", "icon": ' . $itemIcon . '}';	
        $relation = $db->query("SELECT r.label label, v.name voc, v.namespace_prefix prefix FROM `{$db->ItemRelationsProperty}` r LEFT JOIN `{$db->ItemRelationsVocabulary}` v ON v.id = r.vocabulary_id WHERE r.id = $propertyId")->fetch();  
				$textRelation = get_db()->getTable('ItemRelationsRelation')->translate($relation['label'], $relation['prefix']);				        			
				$this->edges[$this->relationsCounter] = '{"from": ' . $subjectItem->id . ', "to": ' . $objectItem->id  . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color": {"color" : "#555555", "highlight" : "#559955", "width" : 3}}';  
        $this->relationsCounter++;	
			}
  		$files = $db->query("SELECT r.subject_file_id sid, r.object_file_id oid FROM `{$db->FileRelationsRelation}` r WHERE property_id = " . $propertyId)->fetchAll();				
			foreach($files as $i => $fileIds) {
  			$subjectFile = get_record_by_id('File', $fileIds['sid']);
  			try {
    			$title = metadata($subjectFile, array('Dublin Core', 'Title'));    			
  			} catch  (Exception $e) {
					$objectTitle = "Fichier privé (File " . $fileIds['sid'] . ")";
					continue;
				}
  			$itemFile = get_record_by_id('Item', $subjectFile->item_id);
        $collection = get_collection_for_item($itemFile); 
     		$collectionColor = $this->collectionColors[$collection->id]; 
     		$this->caption['collectionColors'][$collection->id] = '#' . $collectionColor;
  			$fileIcon = '{"size": 50, "face": "FontAwesome", "code": "\\uf02d", "color": "#' . $collectionColor . '"}';		
  			$this->nodes[$fileIds['sid']] = '{"id": 20000' . $subjectFile->id . ', "label": "' . addslashes($title) . '", "title": "' . addslashes($title) . '", "shape": "icon", "icon": ' . $fileIcon . '}';		
  			$objectFile = get_record_by_id('File', $fileIds['oid']);
  			try {
  			  $title = metadata($objectFile, array('Dublin Core', 'Title'));
  			} catch  (Exception $e) {
					$objectTitle = "Fichier privé (File " . $fileIds['sid'] . ")";
					continue;
				}  			
  			$this->nodes[$fileIds['oid']] =  '{"id": 20000' . $objectFile->id . ', "label": "' . addslashes($title) . '", "title": "' . addslashes($title) . '", "shape": "icon", "icon": ' . $fileIcon . '}';	
        $relation = $db->query("SELECT r.label label, v.name voc, v.namespace_prefix prefix FROM `{$db->ItemRelationsProperty}` r LEFT JOIN `{$db->ItemRelationsVocabulary}` v ON v.id = r.vocabulary_id WHERE r.id = $propertyId")->fetch();  
				$textRelation = get_db()->getTable('ItemRelationsRelation')->translate($relation['label'], $relation['prefix']);				        			
				$this->edges[$this->relationsCounter] = '{"from": 20000' . $subjectFile->id . ', "to": 20000' . $objectFile->id  . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color": {"color" : "#555555", "highlight" : "#559955", "width" : 3}}';  
        $this->relationsCounter++;	
			}					
      $collections = $db->query("SELECT r.subject_collection_id sid, r.object_collection_id oid FROM `{$db->CollectionRelationsRelation}` r RIGHT JOIN `{$db->Collection}` i ON i.id = r.subject_collection_id WHERE property_id = " . $propertyId)->fetchAll();	
      foreach ($collections as $id => $collection) {
        $collectionSubject = get_record_by_id('collection', $collection['sid']);
        $collectionObject = get_record_by_id('collection', $collection['oid']);
     		$collectionColor = $this->collectionColors[$collectionSubject->id];         
     		$this->caption['collectionColors'][$collectionSubject->id] = '#' . $collectionColor;        
        $collectionIcon = '{"size": 50, "face": "FontAwesome", "code": "\\uf029", "color": "#' . $collectionColor . '"}';	
  			try {
  			  $title = metadata($collectionSubject, array('Dublin Core', 'Title'));
  			} catch  (Exception $e) {
					$objectTitle = "Collection privée (Collection " . $collection['sid'] . ")";
					continue;
				}                 			
  			$this->nodes[$collection['sid']] =  '{"id": 10000' . $collection['sid'] . ', "label": "' . addslashes($title) . '", "title": "' . addslashes($title) . '", "shape": "icon", "icon": ' . $collectionIcon . '}'; 
     		$collectionColor = $this->collectionColors[$collectionObject->id];         
     		$this->caption['collectionColors'][$collectionObject->id] = '#' . $collectionColor;   			       
        $collectionIcon = '{"size": 50, "face": "FontAwesome", "code": "\\uf029", "color": "#' . $collectionColor . '"}';	         			
  			$this->nodes[$collection['oid']] =  '{"id": 10000' . $collection['oid'] . ', "label": "' . metadata($collectionObject, array('Dublin Core', 'Title')) . '", "title": "' . metadata($collectionObject, array('Dublin Core', 'Title')) . '", "shape": "icon", "icon": ' . $collectionIcon . '}'; 
				$this->edges[$this->relationsCounter] = '{"from": 10000' . $collectionSubject->id . ', "to": 10000' . $collectionObject->id  . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color": {"color" : "#555555", "highlight" : "#559955", "width" : 3}}';  
  			$this->relationsCounter++;       
      } 
			$this->view->caption = $this->buildCaption();
    }    

  public function itemFilesGraphAction() {
		$this->view->pageTitle = "Les relations des documents de la notice";   
	  $request = Zend_Controller_Front::getInstance()->getRequest();
  	$params = $request->getParams();		 
		$itemId = $params['itemid'];
		$item = get_record_by_id('Item', $itemId);
		if ($item) {
  		$collection = get_collection_for_item($item);
  		if ($collection) {
     		$collectionColor = $this->collectionColors[$collection->id];   		    		
        $this->caption['collectionColors'][$collection->id] = '#' . $collectionColor;
  		}
		} else {
  		$collectionColor = 'FFFFFF';
		}
    $this->caption['typeIcons']['Fichier'] = 'f02d';
    $this->drawFiles($itemId, $collectionColor);  
    $this->view->itemId = $itemId;
    $this->view->nodes = $this->nodes;
    $this->view->edges = $this->edges;
    $this->view->caption = $this->buildCaption();
//     $this->links .= ',{"node" :' . $index . ', "item" : ' . $objectId . '}';	
    $this->view->links = '';
  }
  
	public function allgraphAction()
	{
		$db = get_db();	
		$items = get_records('Item', array(), 500);
		$nodes = $edges = $connected = [];
		$relationCounter = 0;
		$this->view->pageTitle = 'Les relations entre toutes les notices du corpus';
		$nbitems = 0;
		foreach ($items as $num => $item) {
  		$nbitems++;
			$itemId = metadata($item, 'id');
			$itemTitle = addslashes(metadata($item, array('Dublin Core', 'Title'), array('snippet' => 100, 'no_escape' => false)));
			$itemLabel = $itemTitle;
			if (strlen($itemLabel) > 25) {
        $itemLabel = substr($itemLabel, 0, 20) . ' ...';  			
			}
			if (! $itemTitle) {
				$itemTitle = "[Sans Titre]";
			}
			// Fetch relations
			$relations = get_db()->getTable('ItemRelationsRelation')->findBySubjectItemId($itemId) + get_db()->getTable('ItemRelationsRelation')->findByObjectItemId($itemId);
			if (count($relations) <> 0) {
  			// Couleur selon collection 
    		$itemCollection = get_collection_for_item($item);
    		$nodeColor = "#333333";
    		if ($itemCollection) {
      		$nodeColor = $this->collectionColors[$itemCollection->id];
          $this->caption['collectionColors'][$itemCollection->id] = '#' . $nodeColor;    
        }
  			// Icône selon type
  			$itemType = $this->getTypeIcon(metadata($item, 'item_type_name'));	
  			$itemIcon = '{"size": 50, "face": "FontAwesome", "code": "\\' . $itemType . '", "color": "#' . $nodeColor . '"}';		      			
  			$nodes[$itemId] =  '{"id": ' . $itemId . ', "label": "' . $itemLabel . '", "title": "' . $itemTitle . '", "shape": "icon", "icon": ' . $itemIcon . '}';					  			
			}
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
				$edges[$relationCounter] = '{"from": ' . $itemId . ', "to": ' . $objectId . ', "label": "' . $textRelation . '", "title": "' . $textRelation . '", "color": {"color" : "#cccccc", "highlight" : "#cceecc"}}';
        $relationCounter++;
			}			
		}
		$connected = array_values(array_unique($connected));
  	$nodes = array_intersect_key($nodes, array_flip($connected));
		$this->view->nodes = $nodes;
		$this->view->edges = $edges;
		$this->view->caption = $this->buildCaption();
	}
	
  private function getChooseForm() {
    $db = get_db();
  	$form = new Zend_Form();  
  	$form->setName('graphchoix');
  		
		$collections = $db->query("SELECT id FROM `$db->Collections`")->fetchAll();
		$options = [];
		foreach ($collections as $i => $collection_id) {
  		$collection = get_record_by_id('Collection', $collection_id['id']);
			try {
    		$title = metadata($collection, array('Dublin Core', 'Title'));
			} catch  (Exception $e) {
				$title = "Collection privée (Collection " . $collection_id['id'] . ")";
				continue;
			}   		
  		if (strlen($title) > 50) {
        $title = substr($title, 0, 50) . ' ...';  			
  		}	  		
  		$options[$collection->id] = $title;
		}
		$multi = new Zend_Form_Element_Multiselect('ColMulti');
		$multi->setName("collections");
		$multi->setLabel(""); 
		$multi->setAttrib('size', 20);  			
		$multi->addMultiOptions($options);		
    $form->addElement($multi); 
    
		$externalRelations = new Zend_Form_Element_Checkbox('ExtRelations');
		$externalRelations->setName("extRelations");
		$externalRelations->setLabel("Afficher les relations hors collection ?"); 			
		$form->addElement($externalRelations);    

		$drawItems = new Zend_Form_Element_Checkbox('drawItems');
		$drawItems->setName("drawItems");
		$drawItems->setLabel("Afficher les notices des collections ?"); 			
		$form->addElement($drawItems);    

		$drawFiles = new Zend_Form_Element_Checkbox('drawFiles');
		$drawFiles->setName("drawFiles");
		$drawFiles->setLabel("Afficher les fichiers des notices ?"); 				 			
		$form->addElement($drawFiles);    

    $form->addElement(new Zend_Form_Element_Submit(
          'save',
          array(
              'label' => 'Soumettre',
          )
    ));

    $form->setDecorators(
      array(
        'FormElements',
        'Form'
      )
    );        		    
    $form->setElementDecorators(
      array(
      'ViewHelper',
      'Label',
        array(
        'Errors',
        array(
        'class' => 'error'
        )
      ),
      'HtmlTag'
    ));   
    $el = $form->getElement('save');
    $el->removeDecorator('Label'); 
  	return $form;
  }

  private function getChooseRelationForm() {
  	$form = new Zend_Form();  
  	$form->setName('graphchooserelation'); 		
    $db = get_db();
    $relations = $db->query("SELECT DISTINCT(property_id) id, label, v.namespace_prefix prefix, v.name voc FROM `{$db->ItemRelationsRelations}` r INNER JOIN `{$db->ItemRelationsProperty}` p ON r.property_id = p.id INNER JOIN `{$db->ItemRelationsVocabulary}` v ON p.vocabulary_id = v.id WHERE label is not null AND r.subject_item_id IN (SELECT id FROM `$db->Items`) AND r.object_item_id IN (SELECT id FROM `$db->Items`)
                              UNION
                              SELECT DISTINCT(property_id) id, label, v.namespace_prefix prefix, v.name voc FROM `{$db->FileRelationsRelations}` r INNER JOIN `{$db->ItemRelationsProperty}` p ON r.property_id = p.id INNER JOIN `{$db->ItemRelationsVocabulary}` v ON p.vocabulary_id = v.id WHERE label is not null AND r.subject_file_id IN (SELECT id FROM `$db->Files`) AND r.object_file_id IN (SELECT id FROM `$db->Files`) 
                              UNION
                              SELECT DISTINCT(property_id) id, label, v.namespace_prefix prefix, v.name voc FROM `{$db->CollectionRelationsRelations}` r INNER JOIN `{$db->ItemRelationsProperty}` p ON r.property_id = p.id INNER JOIN `{$db->ItemRelationsVocabulary}` v ON p.vocabulary_id = v.id WHERE label is not null AND r.subject_collection_id IN (SELECT id FROM `$db->Collections`) AND r.object_collection_id IN (SELECT id FROM `$db->Collections`)
                              ORDER BY id")->fetchAll();
		$options = [];
		usort($relations, function($a, $b) {
  		return strnatcasecmp($a['label'], $b['label']);
		});		
		foreach ($relations as $i => $relation) {
			$textRelation = get_db()->getTable('ItemRelationsRelation')->translate($relation['label'], $relation['prefix']);				  		
  		$options[$relation['id']] = $textRelation; // . ' (' . $relation['voc'] . ')';
		}
		$relations = new Zend_Form_Element_Select('Relations');
		$relations->setName("relations");
		$relations->setLabel("Sélectionnez un type de relation");  		
		if (isset($_POST['relations'])) {
  		$relations->setValue($_POST['relations']);  		
		} 			
		$relations->setMultiOptions($options);		
    $form->addElement($relations);  
    $form->addElement(new Zend_Form_Element_Submit(
          'save2',
          array(
              'label' => 'Soumettre',
          )
    ));    		
    
    $form->setDecorators(
      array(
        'FormElements',
        'Form'
      )
    );        		    
    $form->setElementDecorators(
      array(
      'ViewHelper',
      'Label',
        array(
        'Errors',
        array(
        'class' => 'error'
        )
      ),
      'HtmlTag'
    ));          
    $el = $form->getElement('save2');
    $el->removeDecorator('Label');     
  	return $form;
  }
  
  private function getTypeIcon($itemType) {
    $itemType = str_replace(' ', '', $itemType);
    if (isset($this->iconTypes[$itemType])) {
   		$this->caption['typeIcons'][$itemType] = $this->iconTypes[$itemType];
      return 'u' . $this->iconTypes[$itemType];
    }
		return 'uf29c';
  }
  
  private function buildCaption() {
    $caption = [];  
    $caption['icons'] = [];
    $caption['colors'] = [];
    $i = $j = 0;
    $x = -550;
    // Calcul de la hauteur du conteneur
    $nbElements = ceil(count($this->caption['typeIcons']) / 8) + ceil(count($this->caption['collectionColors']) / 8) + 2;
    $this->view->height = 60 * $nbElements;
    // Décalage vertical initial
    $y = -30 * $nbElements;    
    $icon = '{"size": 30, "face": "FontAwesome", "code": "\\uf0da", "color": "#eeeeee"}';
    $caption['icons'][] = '{"id": "9999999", "x": ' . ($x + 120) . ', "y": ' . $y . ', "font": {"size": 24, "multi": "html"}, "label": "<b>Icône(s)</b>", "value": 1, "fixed": "true", "physics": false, "shape": "icon", "icon": ' . $icon . '}';  
    $y += 60;
    foreach ($this->caption['typeIcons'] as $type => $icon) {
      if ($j >= 8) {
        $x = -550;
        $y += 60;
        $j = 0;
      }           
      $icone = '{"size": 30, "face": "FontAwesome", "code": "\\' . $icon . '", "color": "#bbbbb"}';
      $x += 120;
      $caption['icons'][] = '{"id": ' . ($i + 1000) . ', "x": ' . $x . ', "y": ' . $y . ', "font": {"size": 20, "multi": true}, "label": "' . strip_tags($type) . '", "widthConstraint": {"maximum" : 10}, "value": 1, "fixed": "true", "physics": false, "shape": "icon", "icon": ' . $icone . '}'; 
      $i++;
      $j++;
    }
    $x = -550;
    $y += 60;
    $j = 0;
    $icone = '{"size": 30, "face": "FontAwesome", "code": "\\uf0da", "color": "#eeeeee"}';
    $caption['icons'][] = '{"id": "99999999", "x": ' . ($x + 120) . ', "y": ' . $y . ', "font": {"size": 24, "multi": "html"}, "label": "<b>Collection(s)</b>", "widthConstraint": {"maximum" : 10}, "value": 1, "fixed": "true", "physics": false, "shape": "icon", "icon": ' . $icone . '}';       
    $y += 60;
    foreach ($this->caption['collectionColors'] as $id => $color) {
      if ($j >= 8) {
        $x = -550;
        $y += +60;
        $j = 0;
      }      
      // Collection Name
      $collection = get_record_by_id('Collection', $id);
      try {
        $title = metadata($collection, array('Dublin Core' ,'Title'));       
      } catch (Exception $e) {
				$title = "Collection privée (Collection $itemId)";   
				continue;         
      }
      $collectionName = $title = strip_tags($title);
      if (strlen($collectionName) > 25) {
        $collectionName = substr($collectionName, 0, 20) . ' ...';
      }
      $icone = '{"size": 30, "face": "FontAwesome", "code": "\\uf029", "color": "' . $color . '"}';
      $x += 120;
      $caption['colors'][] = '{"id": ' . ($i + 1000) . ', "x": ' . $x . ', "y": ' . $y . ', "font": {"size": 20, "multi": "html"}, "label": "' . addslashes($collectionName) . '","title": "' . addslashes($title) . '", "widthConstraint": {"maximum" : 10}, "value": 1, "fixed": "true", "physics": false, "shape": "icon", "icon": ' . $icone . '}';
      $i++;
      $j++;
    }    
    return $caption;
  }  
}