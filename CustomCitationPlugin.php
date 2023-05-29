<?php
	/**
	 * Custom Citation
	 *
	 * @copyright Copyright 2023 Daniele Binaghi
	 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
	 */

	class CustomCitationPlugin extends Omeka_Plugin_AbstractPlugin
	{
		/**
		 * @var array Hooks for the plugin.
		 */
		protected $_hooks = array(
			'install',
			'uninstall',
			'initialize',
			'config',
			'config_form'
		);

		/**
		 * @var array Options and their default values.
		 */
		protected $_options = array(
			'custom_citation_style' => 'omeka',
			'custom_citation_collection' => '0'
		);

		protected $_filters = array(
			'item_citation'
		);

		/**
		 * Install the plugin.
		 */
		public function hookInstall()
		{
			$this->_installOptions();
		}

		/**
		 * Uninstall the plugin.
		 */
		public function hookUninstall()
		{
			$this->_uninstallOptions();
		}
		
		/**
		 * Add the translations.
		 */
		public function hookInitialize()
		{
			add_translation_source(dirname(__FILE__) . '/languages');
		}

		public function hookConfig($args)
		{
			$post = $args['post'];
			set_option('custom_citation_style',		$post['custom_citation_style']);
			set_option('custom_citation_collection',$post['custom_citation_collection']);
		}
		
		public function hookConfigForm()
		{
			include 'config_form.php';
		}

		public function getAccessed()
		{
			return __('accessed') . ' ' . date('F jS\, Y');
		}

		public function getCollection($item)
		{
			$collection = get_collection_for_item($item);
			if ($collection) {
				return metadata($collection, ['Dublin Core', 'Title']);
			}
			return "";
		}

		public function getSiteTitle()
		{
			$title = option('site_title');
			if ($title) {
				return "<em>{$title}</em>";
			}
			return "";
		}

		public function getURL($item)
		{
			return '<span class="citation-url">' . html_escape(record_url($item, null, true)) . '</span>';
		}

		public function stripCreatorLinks($creator) 
		{
			if ($creator) {
				return "<span>" . strip_tags($creator) . "</span>";
			}
		}

		public function getCreators($item) 
		{
			$creators = metadata($item, ['Dublin Core', 'Creator'], ['all' => true]);
			return $creators;
			// if (count($creators) > 0) {
				// return implode(', ', array_map("CustomCitationPlugin::stripCreatorLinks", $creators));
			// }
			// return NULL;
		}

		public function stripPublisherLinks($publisher) 
		{
			if ($publisher) {
				return "<span>" . strip_tags($publisher) . "</span>";
			}
		}

		public function getPublishers($item) 
		{
			$publishers = metadata($item, ['Dublin Core', 'Publisher'], ['all' => true]);
			return $publishers;
			// if (count($publishers) > 0) {
				// return implode(', ', array_map("CustomCitationPlugin::stripPublisherLinks", $publishers));
			// }
			// return NULL;
		}
		
		public function getChicagoStyledPeople($people) {
			switch (count($people)) {
				case 1:
					$result = preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $people[0]);
					break;
				case 2:
					// Chicago-style item citation: two authors
					$result = __('%1$s and %2$s', preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $people[0]), preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $people[1]));
					break;
				case 3:
					// Chicago-style item citation: three authors
					$result = __('%1$s, %2$s and %3$s', preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $people[0]), preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $people[1]), preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $people[2]));
					break;
				default:
					// Chicago-style item citation: more than three authors
					$result = __('%s et al.', preg_replace('/^(\w+),\s(\w+)/i', '$2 $1', $people[0]));
			}
			return $result;			
		}	

		public static function formatCitation($val) {
			if ($val === NULL) {
				return false;
			}

			if ($val === "") {
				return false;
			}

			return true;
		}
		
		public function filterItemCitation($citation, $args)
		{
			$item = $args['item'];
			$creators = $this->getCreators($item);
			$publishers = $this->getPublishers($item);
			$title = (metadata($item, 'display_title') != '' ? metadata($item, 'display_title') : metadata($item, ['Dublin Core', 'Title']));
			$collection = $this->getCollection($item);
			$siteTitle = $this->getSiteTitle();
			$dateAccessed = $this->getAccessed();
			$url = $this->getURL($item);
			$date = metadata($item, array('Dublin Core', 'Date'));

			$_citation = [];
			
			switch (get_option('custom_citation_style')) {
				case "wikipedia":
					if ($creators) $_citation[0] = $this->getChicagoStyledPeople($creators);
					if ($title) $_citation[1] = '<em>' . $title . '</em>';
					if ($publishers) $_citation[2] = $this->getChicagoStyledPeople($publishers);
					$_citation[3] = (strlen($date) > 3 ? substr($date, 0, 4) : '');
					if ($_citation[0]) {
						$_citation[0] .= ' (' . $_citation[3] . ')';
						$_citation[3] = '';
					}						

					if (element_exists('Item Type Metadata', 'ISBN')) {
						$_citation[4] = metadata($item, array('Item Type Metadata', 'ISBN')) ? 'ISBN ' . metadata($item, array('Item Type Metadata', 'ISBN')) . ', ' : ''; 
					} elseif (element_exists('Item Type Metadata', 'ISSN')) {
						$_citation[4] = metadata($item, array('Item Type Metadata', 'ISSN')) ? 'ISSN ' . metadata($item, array('Item Type Metadata', 'ISSN')) . ', ' : ''; 
					} elseif (strpos(metadata($item, array('Dublin Core', 'Identifier')), 'ISBN') !== false || strpos(metadata($item, array('Dublin Core', 'Identifier')), 'ISSN') !== false) {
						$_citation[4] = metadata($item, array('Dublin Core', 'Identifier')) . ',';
					}		

					$_citation[5] = $dateAccessed;
					$_citation[6] = '<span class="citation-url">' . $url . '</span>';
					
					return implode(', ', array_filter($_citation, "CustomCitationPlugin::formatCitation"));
					break;
				default:
					return $citation;
			}
		}   
	}
?>