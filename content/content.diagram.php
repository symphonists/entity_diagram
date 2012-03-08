<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.sectionmanager.php');
	require_once(TOOLKIT . '/class.fieldmanager.php');
	require_once(TOOLKIT . '/class.entrymanager.php');
	
	Class contentExtensionEntity_DiagramDiagram extends AdministrationPage{

		function __construct(){
			parent::__construct();
		}
	
	
		function view(){
			
			$this->addStylesheetToHead(URL . '/extensions/entity_diagram/assets/erd.css', 'all', 271);
			$this->addScriptToHead(URL . '/extensions/entity_diagram/assets/erd.js', 274);

			$this->setPageType('table');
			$this->appendSubheading(__('Entity Diagram'));
			$this->setTitle('Symphony &ndash; ' . __('Entity Diagram'));
			
			// get all sections
		  	$sections = SectionManager::fetch(NULL, 'ASC', 'name');
			
			$output = new XMLElement("div");
			$output->setAttribute("id", "diagram");
			
			// get all section links
			$section_associations = Symphony::Database()->fetch("SELECT * FROM tbl_sections_association");
			
			foreach($sections as $section) {
				
				// count number of entries
				$entry_count = Symphony::Database()->fetchRow(0, "SELECT COUNT(id) as count FROM tbl_entries WHERE section_id='" . $section->get('id') . "'");
				
				$section_node = new XMLElement("div");
				$section_node->setAttribute("class", "section");
				$section_node->setAttribute("id", "section-" . $section->get('id'));
				
				if ($entry_count["count"] == 1) {
					$entries = __('1 entry');
				} else {
					$entries = __('%d entries', array($entry_count["count"]));
				}
				
				// add section name and entry count
				$section_node->appendChild(new XMLElement("h3", "<span><span class=\"section-id\">" . $section->get('id') . "</span>" . $section->get('name') . "<span class=\"entry-count\">".$entries."</span></span><a href=\"" . URL . "/symphony/blueprints/sections/edit/" . $section->get('id') . "/\" class=\"edit\">" . __("edit") . "</a>"));
				
				$field_list = new XMLElement("ul");
				
				// get list of fields
				$fields = $section->fetchFields();
					
				if (!is_array($fields)) {
					$field_node = new XMLElement("li", __("No fields"), array('class' => 'field inactive'));
					$field_list->appendChild($field_node);
					$section_node->appendChild($field_list);
					$output->appendChild($section_node);
					continue;
				}				
				
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
							$parent_section = SectionManager::fetch($relationship["parent_section_id"]);
							$parent_section_field = FieldManager::fetch($relationship["parent_section_field_id"]);

							// get the child section and the child field
							$child_section = SectionManager::fetch($relationship["child_section_id"]);
							$child_section_field = FieldManager::fetch($relationship["child_section_field_id"]);
							
							$relationship_exists = false;
							
							// if a parent
							if ($field_properties["id"] == $relationship["parent_section_field_id"]) {
								if ($child_section_field) {
									// get the properties of this relationship
									$linked_section_name = $child_section->get('name');
									$linked_field_name = $child_section_field->get("label");
								}								
							} else {
								if ($parent_section_field) {
									// get the properties of this relationship
									$linked_section_name = $parent_section->get('name');
									$linked_field_name = $parent_section_field->get("label");
								}								
							}
							
							if ($linked_field_name) {
								$sb_meta = new XMLElement("span", __("Linked to <span class='name'>%s</span> in %s", array($linked_field_name, $linked_section_name)));
								$sb_meta->setAttribute("class", "section-link section-link-" . $relationship["id"]);		
								$field_type->appendChild($sb_meta);
							}
							
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