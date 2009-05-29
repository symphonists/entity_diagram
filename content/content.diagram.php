<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.sectionmanager.php');
	require_once(TOOLKIT . '/class.fieldmanager.php');
	require_once(TOOLKIT . '/class.entrymanager.php');
	
	Class contentExtensionEntity_DiagramDiagram extends AdministrationPage{

		function __construct(&$parent){
			parent::__construct($parent);
			$this->setTitle('Symphony &ndash; Entity Diagram');
		}
	
	
		function view(){

			$this->_Parent->Page->addStylesheetToHead(URL . '/extensions/entity_diagram/assets/erd.css', 'screen', 70);
			$this->_Parent->Page->addScriptToHead(URL . '/extensions/entity_diagram/assets/jquery-1.3.min.js', 75);
			$this->_Parent->Page->addScriptToHead(URL . '/extensions/entity_diagram/assets/erd.js', 80);

			$this->setPageType('table');
			$this->appendSubheading("Entity Diagram");
			
			// get all sections
			$sm = new SectionManager($this->_Parent);
		  	$sections = $sm->fetch(NULL, 'ASC', 'name');
			
			$output = new XMLElement("div");
			$output->setAttribute("id", "diagram");
			
			// get all section links
			$section_associations = $this->_Parent->Database->fetch("SELECT * FROM tbl_sections_association");
			
			foreach($sections as $section) {
				
				// count number of entries
				$entry_count = $this->_Parent->Database->fetchRow(0, "SELECT COUNT(id) as count FROM tbl_entries WHERE section_id='" . $section->_data["id"] . "'");
				
				$section_node = new XMLElement("div");
				$section_node->setAttribute("class", "section");
				$section_node->setAttribute("id", "section-" . $section->_data["id"]);
				
				if ($entry_count["count"] == 1) {
					$entries = "1 entry";
				} else {
					$entries = $entry_count["count"] . " entries";
				}
				
				// add section name and entry count
				$section_node->appendChild(new XMLElement("h3", "<span><span class=\"section-id\">" . $section->_data["id"] . "</span>" . $section->_data["name"] . "<span class=\"entry-count\">".$entries."</span></span><a href=\"" . URL . "/symphony/blueprints/sections/edit/" . $section->_data["id"] . "/\" class=\"edit\">edit</a>"));
				
				// get list of fields
				$fields = $section->fetchFields();
				
				$field_list = new XMLElement("ul");
				
				foreach($fields as $field) {
					
					// get array of properties for this field (ID, type, name etc.)
					$field_properties = $field->get();
					
					$field_node = new XMLElement("li");
					$field_node->setAttribute("class", "field");
					$field_node->setAttribute("id", "field-" . $field_properties["id"]);

					$field_name = new XMLElement("span", "<span class=\"field-id\">" . $field_properties["id"] . "</span> " . $field_properties["label"]);
					$field_name->setAttribute("class", "name");

					$field_type = new XMLElement("span", "(" . $field_properties["type"] . ")");
					$field_type->setAttribute("class", "type");
					
					// find a section link involving this field
					foreach ($section_associations as $relationship) {

						// if this field is a parent or child in a relationship
						if ($field_properties["id"] == $relationship["parent_section_field_id"] || $field_properties["id"] == $relationship["child_section_field_id"]) {

							// get the parent section and the parent field
							$parent_section = $sm->fetch($relationship["parent_section_id"]);
							$parent_section_field = $section->_fieldManager->fetch($relationship["parent_section_field_id"]);

							// get the child section and the child field
							$child_section = $sm->fetch($relationship["child_section_id"]);
							$child_section_field = $section->_fieldManager->fetch($relationship["child_section_field_id"]);
							
							// if a parent
							if ($field_properties["id"] == $relationship["parent_section_field_id"]) {
								// get the properties of this relationship
								$linked_section_name = $child_section->_data["name"];
								$linked_field_name = $child_section_field->get("label");
							} else {
								// get the properties of this relationship
								$linked_section_name = $parent_section->_data["name"];
								$linked_field_name = $parent_section_field->get("label");
							}
							
							$sb_meta = new XMLElement("span", "Linked to <span class='name'>" . $linked_field_name . "</span> in " . $linked_section_name);
							$sb_meta->setAttribute("class", "section-link section-link-" . $relationship["id"]);		
							$field_type->appendChild($sb_meta);
							
						}
						
					}
										
					$field_node->appendChild($field_name);
					$field_node->appendChild($field_type);
					
					$field_list->appendChild($field_node);
					
				}
								
				$section_node->appendChild($field_list);
				
				$output->appendChild($section_node);

			}
			
			$this->Form->appendChild($output);

		}
	}
	
?>