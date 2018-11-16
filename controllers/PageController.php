<?php
class Graph_PageController extends Omeka_Controller_AbstractActionController 
{
	public function preferencesAction() {
  	$form = new Zend_Form();
  	// Sauvegarde
  	if ($this->_request->isPost()) {
  		$formData = $this->_request->getPost();
  		if ($form->isValid($formData)) {
    		unset($formData['save']);
        $icones = serialize($formData);
        set_option('graph_preferences', $icones);
  			$this->_helper->flashMessenger("Préférences Graph sauvegardées.");
  		}
  	}
  	$form = $this->getPreferencesForm();  	
  	$form = "<div>" . $form. "</div>";
  	// Unicode codes for fa icons
    $faIcons = yaml_parse_file(PLUGIN_DIR . "/Graph/font-awesome/icons.yml");
    $iconsHTML = "<div>";
    foreach ($faIcons['icons'] as $index => $icone) {
     	$iconsHTML .= "<span style='display:none;' id='fa-" . $icone['id'] . "'>" . $icone['unicode'] . "</span>";
    }
  	$iconsHTML .= "</div>";  	 
  	$this->view->form = $form;
  	$this->view->iconcodes = $iconsHTML;
  }
  
  private function getPreferencesForm() {
    	    		    	
    	$form = new Zend_Form();
    	$form->setName('GraphPreferences');	
	    // Create decoration for form's elements
    	$elementDecoration = array(
    			'ViewHelper',
    			'Description',
    			'Errors',    			
    			array(array('data'=>'HtmlTag'), array('tag' => 'td', 'valign' => 'TOP')),
    			array('Errors'),
    			array(array('row'=>'HtmlTag'),array('tag'=>'tr'))
    	);    	
    	
    	$db = get_db();
    	$types = $db->query("SELECT * FROM `$db->ItemTypes`")->fetchAll(); 
    	$icones = unserialize(get_option('graph_preferences'));  	
      foreach ($types as $i => $type) {
        $iconName = $type['name'];
  			$fieldType = new Zend_Form_Element_Text($iconName);
  			$fieldType->setValue($icones[str_replace(' ', '', $iconName)]);
  			$fieldType->setName($iconName);
  			$fieldType->setLabel($iconName);       
   			$fieldType->setAttrib('size', 2);  							 
   			$fieldType->setAttrib('class', 'faicon'); 
   			$desc = "Ic&ograve;ne non choisi";
   			if ($icones[str_replace(' ', '', $iconName)] <> '') {
     			$desc = '<i class="fa fa-2x">&#x' . $icones[str_replace(' ', '', $iconName)] . ';</i>';
   			}
   			$fieldType->setDescription($desc); 							      			
        $fieldType->getDecorator('Description')->setEscape(false); 			 
  			$form->addElement($fieldType);       
      }
      $form->addElement(new Zend_Form_Element_Submit(
            'save',
            array(
                'label' => 'Soumettre',
            )
        ));
    	        	       				        	
    	return $form;    
  }
}