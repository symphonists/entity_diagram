<?php

	Class extension_entity_diagram extends Extension {
	
		public function about(){
			return array(
				'name'			=> 'Entity Diagram',
				'version'		=> '1.3',
				'release-date'	=> '2009-07-01',
				'author' => array(
					'name'		=> 'Nick Dunn',
					'website'	=> 'http://airlock.com',
					'email'		=> 'nick.dunn@airlock.com')
			);
		}

		public function fetchNavigation() {
			return array(
				array(
					'location' => 'Blueprints',
					'name'	=> 'Entity Diagram',
					'link'	=> '/diagram/'
				)
			);
		}
			
	}

?>