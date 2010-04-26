<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.sectionmanager.php');
	require_once(TOOLKIT . '/class.fieldmanager.php');
	require_once(TOOLKIT . '/class.entrymanager.php');
	
	Class contentExtensionEntity_DiagramGraphviz extends AdministrationPage{

		function __construct(&$parent){
			parent::__construct($parent);
		}
	
		function view(){
			
			$output = 'digraph g {

			graph [
				rankdir = LR
			];

			node [
				fontsize = 12
				shape = plaintext
				fontname = Arial
			];';
			
			// get all sections
			$sm = new SectionManager($this->_Parent);
		  	$sections = $sm->fetch(NULL, 'ASC', 'name');
		
			$relationships = array();
			
			// get all section links
			$section_associations = $this->_Parent->Database->fetch("SELECT * FROM tbl_sections_association");
			
			foreach($sections as $section) {
				
				$output .= sprintf(
					'"%s" [
						label = <<table border="0" cellborder="1" cellspacing="0" color="#666666"><tr><td bgcolor="#dddddd"><font color="#000000">%s</font></td></tr>',
					$section->get("handle"),
					$section->get("name")
				);
				
				// get list of fields
				$fields = $section->fetchFields();
				if (!is_array($fields)) $fields = array();
				
				$i = 1;
				foreach($fields as $field) {

					$output .= sprintf('<tr><td port="f%d" align="left"><font color="#333333">%s</font> <font color="#aaaaaa" point-size="10">%s</font></td></tr>',
						$field->get("id"),
						$field->get("label"),
						$field->get('type')
					);
					/*if ($i != count($fields)) {
						$output .= '|';
					}*/
					$i++;
					
					// find a section link involving this field
					foreach ($section_associations as $relationship) {

						// if this field is a parent or child in a relationship
						if ($field->get("id") == $relationship["parent_section_field_id"] || $field->get("id") == $relationship["child_section_field_id"]) {

							// get the parent section and the parent field
							$parent_section = $sm->fetch($relationship["parent_section_id"]);
							$parent_section_field = $section->_fieldManager->fetch($relationship["parent_section_field_id"]);

							// get the child section and the child field
							$child_section = $sm->fetch($relationship["child_section_id"]);
							$child_section_field = $section->_fieldManager->fetch($relationship["child_section_field_id"]);
							
							// if a parent
							if ($field->get("id") == $relationship["parent_section_field_id"]) {
								// get the properties of this relationship
								$linked_section_name = $child_section->_data["name"];
								$linked_section_handle = $child_section->_data["handle"];
								$linked_field_name = $child_section_field->get("label");
								$linked_field_id = $child_section_field->get("id");
							} else {
								// get the properties of this relationship
								$linked_section_name = $parent_section->_data["name"];
								$linked_section_handle = $parent_section->_data["handle"];
								$linked_field_name = $parent_section_field->get("label");
								$linked_field_id = $parent_section_field->get("id");
							}
							
							$relationships[] = array($section->get("handle"), $field->get("id"), $linked_section_handle, $linked_field_id);
							
						}
						
					}
					
				}
				
				$output .= sprintf('
						</table>>];'
				);

			}
			
			foreach($relationships as $rel) {
				$output .= sprintf(
					'"%s":f%d -> "%s":f%d [arrowsize = 0.5, color="#666666"];',
					$rel[0],
					$rel[1],
					$rel[2],
					$rel[3]
				);
			}
			
			$output .= '}';
			
			file_put_contents(MANIFEST . '/' . Lang::createHandle(Administration::instance()->Configuration->get('sitename', 'general')) . '-' . date('Ymd', time()) . '.gv', $output);
			
			die;

		}
	}
	
?>