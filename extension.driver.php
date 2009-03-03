<?php

	Class extension_entity_diagram extends Extension {
	
		public function about(){
			return array(
				'name'			=> 'Entity Diagram',
				'version'		=> '1.2',
				'release-date'	=> '2009-02-04',
				'author' => array(
					'name'		=> 'Nick Dunn',
					'website'	=> 'http://airlock.com',
					'email'		=> 'nick.dunn@airlock.com')
			);
		}

		public function fetchNavigation(){			
			return array(
				array(
					'location'	=> 300,
					'name'	=> 'Entity Diagram',
					'link'	=> '/diagram/'
				)
			);		
		}
			
	}

?>