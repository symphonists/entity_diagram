#  Entity Diagram

* Version: 1.4.4
* Author: Nick Dunn
* Build Date: 2011-02-05
* Requirements: Symphony 2.2

## Installation

1. Download and upload the 'entity_diagram' folder in this archive to your Symphony 'extensions' folder.
2. Enable it by selecting the "Entity Diagram", choose Enable from the with-selected menu, then click Apply.
3. Select "Entity Diagram" from the System menu within Symphony.

## Changes

- 1.4.4
	- updates for Symphony 2.2 compatibility

1.4.3
	- README update blunder

- 1.4.2
	- removed very broken print stylesheets (no longer supported)
	- added output of IDs to Graphviz (thanks designermonkey!)
	- added page size (A4 etc.) to Graphviz (thanks designermonkey!)
	- added optional output of relationship arrows
	- tidied up Graphviz output

- 1.4.1 (thanks phoque!)
	- removed jQuery, now uses Symphony's jQuery bundle
	- added German localisation

- 1.4
	- various bug fixes
	- added support for Graphviz export in System > Preferences
	- added support for Subsection Manager field

- 1.3
	- Now appears under the Blueprints menu. Requires Symphony 2.0.3.

- 1.2
	- Fixed a bug related to Select Box Link field v1.7+
	- Entity Diagram now appears under the System menu

- 1.1
	- Fixed by where sections added to the DOM were not checked on every iteration
	- Field labels now correctly use the `label` field from the database rather than handle-ised `element_name`

- 1.0
	- Initial release