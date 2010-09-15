<?php
	require_once(TOOLKIT . '/class.sectionmanager.php');

	Class extension_entity_diagram extends Extension {

		public function about(){
			return array(
				'name'			=> 'Entity Diagram',
				'version'		=> '1.4.1',
				'release-date'	=> '2010-08-27',
				'author' => array(
					'name'		=> 'Nick Dunn',
					'website'	=> 'http://nick-dunn.co.uk')
			);
		}

		public function fetchNavigation() {
			return array(
				array(
					'location' => __('Blueprints'),
					'name'	=> __('Entity Diagram'),
					'link'	=> '/diagram/'
				)
			);
		}

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
			);
		}

		public function export($section_ids, $pagesize){
			//var_dump($pagesize);
			$output = "digraph g {

			graph [
				rankdir = LR";

			if($pagesize != "auto"){
				$output .= "
				size=\"".$pagesize[0]."\"
				ratio=fill";
			}

			$output .= "
			];

			node [
				fontsize = 12
				shape = plaintext
				fontname = Arial
			];\n\n";

			$sm = new SectionManager(Administration::instance());
		  	$sections = $sm->fetch();

			$relationships = array();

			// get all section links
			$section_associations = $this->_Parent->Database->fetch("SELECT * FROM tbl_sections_association");

			foreach($sections as $section) {
				if (!in_array($section->get('id'), $section_ids)) continue;

				$output .= sprintf(
					"\t\t\ts%d [
						label = <<table border=\"0\" cellborder=\"1\" cellspacing=\"0\" color=\"#666666\">
									<tr><td bgcolor=\"#dddddd\" align=\"left\" port=\"h%1\$d\"><font color=\"#000000\">(%1\$d) %2\$s</font></td></tr>",
					$section->get("id"),
					$section->get("name")
				);

				// get list of fields
				$fields = $section->fetchFields();
				if (!is_array($fields)) $fields = array();

				foreach($fields as $field) {

					$output .= sprintf("<tr><td port=\"f%1\$d\" align=\"left\"><font color=\"#aaaaaa\" point-size=\"10\">(%1\$d)</font> <font color=\"#333333\">%2\$s</font> <font color=\"#aaaaaa\" point-size=\"10\">%3\$s</font></td></tr>",
						$field->get("id"),
						$field->get("label"),
						$field->get('type')
					);

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
								if ($child_section_field) {
									// get the properties of this relationship
									$linked_section_id = $child_section->_data["id"];
									$linked_section_name = $child_section->_data["name"];
									$linked_section_handle = $child_section->_data["handle"];
									$linked_field_name = $child_section_field->get("label");
									$linked_field_id = $child_section_field->get("id");
								}
							} else {
								if ($parent_section_field) {
									// get the properties of this relationship
									$linked_section_id = $parent_section->_data["id"];
									$linked_section_name = $parent_section->_data["name"];
									$linked_section_handle = $parent_section->_data["handle"];
									$linked_field_name = $parent_section_field->get("label");
									$linked_field_id = $parent_section_field->get("id");
								}
							}

							if ($linked_section_id && in_array($linked_section_id, $section_ids)) {
								$relationships[] = array($section->get("id"), $field->get("id"), $linked_section_id, $linked_field_id);
							}

						}

					}

					if ($field->get('type') == 'subsectionmanager') {
						$child_section = $sm->fetch($field->get('subsection_id'));
						$relationships[] = array($section->get("id"), $field->get("id"), $child_section->get('id'), null);
					}

				}

				$output .= sprintf("
						</table>>\n\t\t\t];\n\n"
				);

			}

			foreach($relationships as $rel) {
				$output .= sprintf(
					"\t\t\ts%d:f%d -> %s [arrowsize = 0.5, color=\"#666666\"];\n",
					$rel[0],
					$rel[1],
					(($rel[3] == null) ? 's' . $rel[2] . ':h' . $rel[2] : 's' . $rel[2] . ':f' . $rel[3])
				);
			}

			$output .= '}';

			$file_path = MANIFEST . '/tmp/';
			$file_name = Lang::createHandle(Administration::instance()->Configuration->get('sitename', 'general')) . '-' . date('Ymd', time()) . '.gv';

			file_put_contents($file_path . $file_name, $output);

			header('Content-type: application/.gv');
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		    header('Content-disposition: attachment; filename=' . $file_name);
		    header('Pragma: no-cache');

			readfile($file_path . $file_name);
			exit();

		}

		public function appendPreferences($context){

			if(isset($_POST['action']['entity-diagram-graphviz-export'])){
				$this->export($_POST['fields']['entity-diagram-sections'], $_POST['fields']['entity-diagram-page-size']);
			}

			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', 'Entity Diagram Export'));

			$aTableBody = array();
			$options = array();
			$pagesize = array();

			$sm = new SectionManager($this->_Parent);
		  	$sections = $sm->fetch(NULL, 'ASC', 'name');

			$selected = true;

			if(!empty($sections)){
				foreach($sections as $section) {
					$options[] = array($section->get('id'), $selected, $section->get('name'));
					$attributes = NULL;
				}
			}else{
				$options = NULL;
				$attributes = array('disabled'=>'disabled');
			}

			$pagesize[] = array('auto', true, 'Auto');
			$pagesize[] = array('11.19,7.76', false, 'A4');
			$pagesize[] = array('16.03,11.19', false, 'A3');
			$pagesize[] = array('22.88,16.03', false, 'A2');
			$pagesize[] = array('32.61,22.88', false, 'A1');
			$pagesize[] = array('46.31,32.61', false, 'A0');

			$label = Widget::Label(
				'Included Sections',
				Widget::Select(
					'fields[entity-diagram-sections][]',
					$options,
					array(
						'multiple' => 'multiple',
						'style' => ('height:' . (count($sections) * 12) . 'px;')
					)
				)
			);
			$label2 = Widget::Label(
				'Page Size',
				Widget::Select(
					'fields[entity-diagram-page-size][]',
					$pagesize,
					array(
						'style' => 'width:20%'
					)
				)
			);

			$group->appendChild($label);
			$group->appendChild($label2);
			$group->appendChild(
				Widget::Input('action[entity-diagram-graphviz-export]', 'Export Graphviz', 'submit', $attributes)
			);

			$context['wrapper']->appendChild($group);

		}

	}

?>
