<?php
	/**
	 * Custom Citation
	 *
	 * @copyright Copyright 2023-2026 Daniele Binaghi
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
			'custom_citation_style'      => 'omeka',
			'custom_citation_collection' => '0'
		);

		protected $_filters = array(
			'item_citation'
		);

		/**
		 * Nobility particles / prepositions by language, used in name parsing.
		 * Includes Italian, English, French, Spanish, Arabic, German, Dutch.
		 */
		protected $_nameParticles = array(
			'da', 'de', 'del', 'dell', 'della', 'dei', 'degli', 'delle', 'di', 'd',
			'of', 'the',
			'du', 'des', 'le', 'la', 'les',
			'al', 'el', 'bin', 'bint', 'abu', 'ibn',
			'von', 'van', 'vom', 'zu', 'zur', 'auf', 'der', 'dem', 'ten', 'ter',
			'los', 'las'
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
			set_option('custom_citation_style',      $post['custom_citation_style']);
			set_option('custom_citation_collection', $post['custom_citation_collection']);
		}

		public function hookConfigForm()
		{
			include 'config_form.php';
		}

		// -----------------------------------------------------------------------
		// Name parsing
		// -----------------------------------------------------------------------

		/**
		 * Parse a name string into its components.
		 * Handles both "Last, First" and "First Last" formats,
		 * including nobility particles in multiple languages.
		 *
		 * @param string $name
		 * @return array ['first' => string, 'last' => string, 'suffix' => string]
		 */
		protected function parseName($name)
		{
			$name   = trim(strip_tags($name));
			$first  = '';
			$last   = '';
			$suffix = '';

			if (empty($name)) {
				return compact('first', 'last', 'suffix');
			}

			// Handle Arabic hyphenated particles: Al-Rashid => Al Rashid
			$name = preg_replace('/\b(al|el)-/i', '$1 ', $name);

			// Known suffixes
			$knownSuffixes = array('jr', 'jr.', 'sr', 'sr.', 'ii', 'iii', 'iv', 'v');

			if (strpos($name, ',') !== false) {
				// Format: "Last, First [Suffix]"
				$parts = explode(',', $name, 3);
				$last  = trim($parts[0]);
				$rest  = trim($parts[1]);

				// Check for suffix in a third comma-separated segment
				if (isset($parts[2])) {
					$potentialSuffix = trim($parts[2]);
					if (in_array(strtolower($potentialSuffix), $knownSuffixes)) {
						$suffix = $potentialSuffix;
					} else {
						$rest .= ', ' . $potentialSuffix;
					}
				}

				$first = $rest;

			} else {
				// Format: "First [Particle] Last [Suffix]"
				$words = preg_split('/\s+/', $name);

				// Extract trailing suffix
				if (count($words) > 1 && in_array(strtolower(end($words)), $knownSuffixes)) {
					$suffix = array_pop($words);
				}

				if (count($words) === 1) {
					$last  = $words[0];
					$first = '';
				} else {
					// Walk backwards: last word is core surname,
					// preceding particles also belong to the surname
					$lastParts  = array();
					$firstParts = array();
					$foundCore  = false;

					for ($i = count($words) - 1; $i >= 0; $i--) {
						$word = $words[$i];
						if (!$foundCore) {
							array_unshift($lastParts, $word);
							$foundCore = true;
						} elseif (in_array(strtolower($word), $this->_nameParticles)) {
							array_unshift($lastParts, $word);
						} else {
							// Prepend remaining words as first name
							$firstParts = array_slice($words, 0, $i + 1);
							break;
						}
					}

					$last  = implode(' ', $lastParts);
					$first = implode(' ', $firstParts);
				}
			}

			return array(
				'first'  => trim($first),
				'last'   => trim($last),
				'suffix' => trim($suffix)
			);
		}

		/**
		 * Get initials from a first name string.
		 * E.g. "Lazaro Ludoviko" => "L. L."
		 *
		 * @param string $first
		 * @return string
		 */
		protected function getInitials($first)
		{
			if (empty($first)) {
				return '';
			}
			$words    = preg_split('/\s+/', trim($first));
			$initials = array();
			foreach ($words as $word) {
				if (!empty($word)) {
					$initials[] = mb_strtoupper(mb_substr($word, 0, 1)) . '.';
				}
			}
			return implode(' ', $initials);
		}

		// -----------------------------------------------------------------------
		// People formatting per style
		// -----------------------------------------------------------------------

		/**
		 * Format authors for Wikipedia style (Chicago-based).
		 * 1:  First Last
		 * 2:  First1 Last1 and First2 Last2
		 * 3:  First1 Last1, First2 Last2 and First3 Last3
		 * 4+: First1 Last1 et al.
		 */
		protected function formatPeopleWikipedia($people)
		{
			$formatted = array();
			foreach ($people as $person) {
				$p     = $this->parseName($person);
				$name  = trim($p['first'] . ' ' . $p['last']);
				if ($p['suffix']) $name .= ' ' . $p['suffix'];
				$formatted[] = trim($name);
			}

			$count = count($formatted);
			if ($count === 1) {
				return $formatted[0];
			} elseif ($count === 2) {
				return $formatted[0] . ' ' . __('and') . ' ' . $formatted[1];
			} elseif ($count === 3) {
				return $formatted[0] . ', ' . $formatted[1] . ' ' . __('and') . ' ' . $formatted[2];
			} else {
				return $formatted[0] . ' ' . __('et al.');
			}
		}

		/**
		 * Format authors for APA style.
		 * 1-20: Last, I.[, Last2, I.][, & LastN, I.]
		 * 21+:  first 19 ... last
		 */
		protected function formatPeopleApa($people)
		{
			$formatted = array();
			foreach ($people as $person) {
				$p        = $this->parseName($person);
				$initials = $this->getInitials($p['first']);
				$entry    = $p['last'];
				if ($initials) $entry .= ', ' . $initials;
				if ($p['suffix']) $entry .= ', ' . $p['suffix'];
				$formatted[] = $entry;
			}

			$count = count($formatted);
			if ($count === 1) {
				return $formatted[0];
			} elseif ($count <= 20) {
				$last   = array_pop($formatted);
				return implode(', ', $formatted) . ', &amp; ' . $last;
			} else {
				$first19 = array_slice($formatted, 0, 19);
				$last    = end($formatted);
				return implode(', ', $first19) . ', ... ' . $last;
			}
		}

		/**
		 * Format authors for MLA style.
		 * 1:  Last, First
		 * 2:  Last1, First1, and First2 Last2
		 * 3+: Last1, First1, et al.
		 */
		protected function formatPeopleMla($people)
		{
			$count = count($people);
			if ($count === 0) return '';

			$p0    = $this->parseName($people[0]);
			$first = $p0['last'] . ', ' . $p0['first'];
			if ($p0['suffix']) $first .= ' ' . $p0['suffix'];

			if ($count === 1) {
				return $first;
			} elseif ($count === 2) {
				$p1     = $this->parseName($people[1]);
				$second = trim($p1['first'] . ' ' . $p1['last']);
				if ($p1['suffix']) $second .= ' ' . $p1['suffix'];
				return $first . ', ' . __('and') . ' ' . $second;
			} else {
				return $first . ', ' . __('et al.');
			}
		}

		/**
		 * Format authors for Chicago/Turabian style.
		 * 1:   Last, First
		 * 2-3: Last1, First1, First2 Last2[, and First3 Last3]
		 * 4+:  Last1, First1, et al.
		 */
		protected function formatPeopleChicago($people)
		{
			$count = count($people);
			if ($count === 0) return '';

			$p0    = $this->parseName($people[0]);
			$first = $p0['last'] . ', ' . $p0['first'];
			if ($p0['suffix']) $first .= ' ' . $p0['suffix'];

			if ($count === 1) {
				return $first;
			} elseif ($count <= 3) {
				$others = array();
				for ($i = 1; $i < $count; $i++) {
					$p    = $this->parseName($people[$i]);
					$name = trim($p['first'] . ' ' . $p['last']);
					if ($p['suffix']) $name .= ' ' . $p['suffix'];
					$others[] = $name;
				}
				$last   = array_pop($others);
				$result = $first;
				if (!empty($others)) {
					$result .= ', ' . implode(', ', $others);
				}
				return $result . ', ' . __('and') . ' ' . $last;
			} else {
				return $first . ', ' . __('et al.');
			}
		}

		/**
		 * Format authors for Harvard style.
		 * 1-3: Last, I.[and Last2, I.[and Last3, I.]]
		 * 4+:  Last, I. et al.
		 */
		protected function formatPeopleHarvard($people)
		{
			$formatted = array();
			foreach ($people as $person) {
				$p        = $this->parseName($person);
				$initials = $this->getInitials($p['first']);
				$entry    = $p['last'];
				if ($initials) $entry .= ', ' . $initials;
				if ($p['suffix']) $entry .= ', ' . $p['suffix'];
				$formatted[] = $entry;
			}

			$count = count($formatted);
			if ($count === 1) {
				return $formatted[0];
			} elseif ($count <= 3) {
				$last = array_pop($formatted);
				return implode(', ', $formatted) . ' ' . __('and') . ' ' . $last;
			} else {
				return $formatted[0] . ' ' . __('et al.');
			}
		}

		/**
		 * Format authors for IEEE style.
		 * 1-6: I. Last[, I2. Last2[, ... and IN. LastN]]
		 * 7+:  first 6 + et al.
		 */
		protected function formatPeopleIeee($people)
		{
			$etAl      = count($people) > 6;
			$pool      = array_slice($people, 0, $etAl ? 6 : count($people));
			$formatted = array();

			foreach ($pool as $person) {
				$p        = $this->parseName($person);
				$initials = $this->getInitials($p['first']);
				$entry    = trim($initials . ' ' . $p['last']);
				if ($p['suffix']) $entry .= ' ' . $p['suffix'];
				$formatted[] = $entry;
			}

			if ($etAl) {
				return implode(', ', $formatted) . ' ' . __('et al.');
			}

			$count = count($formatted);
			if ($count === 1) {
				return $formatted[0];
			} else {
				$last = array_pop($formatted);
				return implode(', ', $formatted) . ' ' . __('and') . ' ' . $last;
			}
		}

		// -----------------------------------------------------------------------
		// Shared helpers
		// -----------------------------------------------------------------------

		/**
		 * Return the item URL wrapped in a span.
		 */
		public function getURL($item)
		{
			return '<span class="citation-url">' . html_escape(record_url($item, null, true)) . '</span>';
		}

		/**
		 * Return collection title if collection option is enabled.
		 */
		public function getCollection($item)
		{
			if (!get_option('custom_citation_collection')) {
				return '';
			}
			$collection = get_collection_for_item($item);
			if ($collection) {
				return metadata($collection, array('Dublin Core', 'Title'));
			}
			return '';
		}

		/**
		 * Extract ISBN or ISSN from item metadata.
		 */
		protected function getIsbnIssn($item)
		{
			if (element_exists('Item Type Metadata', 'ISBN')) {
				$val = metadata($item, array('Item Type Metadata', 'ISBN'));
				if ($val) return 'ISBN ' . $val;
			}
			if (element_exists('Item Type Metadata', 'ISSN')) {
				$val = metadata($item, array('Item Type Metadata', 'ISSN'));
				if ($val) return 'ISSN ' . $val;
			}
			$identifier = metadata($item, array('Dublin Core', 'Identifier'));
			if ($identifier && (strpos($identifier, 'ISBN') !== false || strpos($identifier, 'ISSN') !== false)) {
				return $identifier;
			}
			return '';
		}

		/**
		 * Extract a 4-digit year from a date string.
		 */
		protected function getYear($date)
		{
			if (preg_match('/\b(\d{4})\b/', $date, $matches)) {
				return $matches[1];
			}
			return '';
		}

		/**
		 * Filter callback: skip null or empty values.
		 */
		public static function formatCitation($val)
		{
			return ($val !== null && $val !== '');
		}

		// -----------------------------------------------------------------------
		// Main filter
		// -----------------------------------------------------------------------

		public function filterItemCitation($citation, $args)
		{
			$item  = $args['item'];
			$style = get_option('custom_citation_style');

			if ($style === 'omeka') {
				return $citation;
			}

			// Gather metadata
			$creators   = metadata($item, array('Dublin Core', 'Creator'),   array('all' => true));
			$publishers = metadata($item, array('Dublin Core', 'Publisher'), array('all' => true));
			$title      = metadata($item, 'display_title');
			if (!$title) $title = metadata($item, array('Dublin Core', 'Title'));
			$date       = metadata($item, array('Dublin Core', 'Date'));
			$year       = $this->getYear($date);
			$collection = $this->getCollection($item);
			$url        = $this->getURL($item);
			$isbnIssn   = $this->getIsbnIssn($item);

			$titleItalic  = $title           ? '<em>' . $title . '</em>'                     : '';
			$publisherStr = !empty($publishers) ? implode('; ', array_filter($publishers))   : '';

			switch ($style) {

				// -----------------------------------------------------------
				case 'apa':
					$parts = array();

					if (!empty($creators)) {
						$parts[] = $this->formatPeopleApa($creators) . ' (' . ($year ?: __('n.d.')) . ').';
					} else {
						$parts[] = '(' . ($year ?: __('n.d.')) . ').';
					}
					if ($titleItalic)  $parts[] = $titleItalic . '.';
					if ($publisherStr) $parts[] = $publisherStr . '.';
					if ($collection)   $parts[] = $collection . '.';
					$parts[] = __('Retrieved') . ' ' . date('F j, Y') . ', ' . __('from') . ' ' . $url;

					return implode(' ', array_filter($parts, 'CustomCitationPlugin::formatCitation'));

				// -----------------------------------------------------------
				case 'chicago':
					$parts = array();

					if (!empty($creators)) {
						$parts[] = $this->formatPeopleChicago($creators) . '.';
					}
					if ($titleItalic) $parts[] = $titleItalic . '.';

					$pubYear = array_filter(array($publisherStr, $year), 'CustomCitationPlugin::formatCitation');
					if (!empty($pubYear)) $parts[] = implode(', ', $pubYear) . '.';

					if ($collection) $parts[] = $collection . '.';
					$parts[] = __('Accessed') . ' ' . date('F j, Y') . '. ' . $url;

					return implode(' ', array_filter($parts, 'CustomCitationPlugin::formatCitation'));

				// -----------------------------------------------------------
				case 'harvard':
					$parts = array();

					if (!empty($creators)) {
						$parts[] = $this->formatPeopleHarvard($creators) . ' (' . ($year ?: __('n.d.')) . ')';
					} else {
						$parts[] = '(' . ($year ?: __('n.d.')) . ')';
					}
					if ($titleItalic)  $parts[] = $titleItalic . '.';
					if ($publisherStr) $parts[] = $publisherStr . '.';
					if ($collection)   $parts[] = $collection . '.';
					$parts[] = __('Available at:') . ' ' . $url . ' (' . __('Accessed:') . ' ' . date('j F Y') . ')';

					return implode(' ', array_filter($parts, 'CustomCitationPlugin::formatCitation'));

				// -----------------------------------------------------------
				case 'ieee':
					$parts = array();

					if (!empty($creators)) {
						$parts[] = $this->formatPeopleIeee($creators) . ',';
					}
					if ($titleItalic)  $parts[] = $titleItalic . ',';
					if ($publisherStr) $parts[] = $publisherStr . ',';
					if ($year)         $parts[] = $year . '.';
					if ($collection)   $parts[] = $collection . '.';
					$parts[] = __('Accessed:') . ' ' . date('M. j, Y') . '. [Online]. ' . __('Available:') . ' ' . $url;

					return '[1] ' . implode(' ', array_filter($parts, 'CustomCitationPlugin::formatCitation'));

				// -----------------------------------------------------------
				case 'mla':
					$parts = array();

					if (!empty($creators)) {
						$parts[] = $this->formatPeopleMla($creators) . '.';
					}
					if ($titleItalic) $parts[] = $titleItalic . '.';

					$pubYear = array_filter(array($publisherStr, $year), 'CustomCitationPlugin::formatCitation');
					if (!empty($pubYear)) $parts[] = implode(', ', $pubYear) . '.';

					if ($collection) $parts[] = $collection . '.';
					$parts[] = __('Accessed') . ' ' . date('j M. Y') . '. ' . $url;

					return implode(' ', array_filter($parts, 'CustomCitationPlugin::formatCitation'));

				// -----------------------------------------------------------
				case 'wikipedia':
					$parts = array();

					if (!empty($creators)) {
						$authorStr = $this->formatPeopleWikipedia($creators);
						$parts[]   = $authorStr . ' (' . ($year ?: __('n.d.')) . ')';
					}
					if ($titleItalic)  $parts[] = $titleItalic;
					if ($publisherStr) $parts[] = $publisherStr;
					if ($isbnIssn)     $parts[] = $isbnIssn;
					if ($collection)   $parts[] = $collection;
					$parts[] = __('accessed') . ' ' . date('F jS\, Y') . ', ' . $url;

					return implode(', ', array_filter($parts, 'CustomCitationPlugin::formatCitation'));

				// -----------------------------------------------------------
				default:
					return $citation;
			}
		}
	}
?>
