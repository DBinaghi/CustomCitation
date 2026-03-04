<?php
	$view = get_view();

	$styles = array(
		'omeka' => array(
			'label'       => __('Omeka default style'),
			'description' => __('Uses the built-in Omeka citation format without any modification.'),
			'example'     => __('Zamenhof, Lazaro Ludoviko, "Unua Libro," <em>Omeka Test</em>, last accessed March 04, 2026, http://example.com'),
		),
		'apa' => array(
			'label'       => __('APA'),
			'description' => __('Author-date style published by the American Psychological Association. Widely used in social sciences, education and psychology.'),
			'example'     => __('Zamenhof, L. L. (1887). <em>Unua Libro</em>. Kelter. Retrieved March 4, 2026, from http://example.com'),
		),
		'chicago' => array(
			'label'       => __('Chicago / Turabian'),
			'description' => __('Style published by the University of Chicago Press. Widely used in history, arts and social sciences.'),
			'example'     => __('Zamenhof, Lazaro Ludoviko. <em>Unua Libro</em>. Kelter, 1887. Accessed March 4, 2026. http://example.com'),
		),
		'harvard' => array(
			'label'       => __('Harvard'),
			'description' => __('Author-date style widely used in UK universities and in natural and social sciences.'),
			'example'     => __('Zamenhof, L. L. (1887) <em>Unua Libro</em>. Kelter. Available at: http://example.com (Accessed: 4 March 2026)'),
		),
		'ieee' => array(
			'label'       => __('IEEE'),
			'description' => __('Numbered reference style published by the Institute of Electrical and Electronics Engineers. Standard in engineering, computer science and technology.'),
			'example'     => __('[1] L. L. Zamenhof, <em>Unua Libro</em>, Kelter, 1887. Accessed: Mar. 4, 2026. [Online]. Available: http://example.com'),
		),
		'mla' => array(
			'label'       => __('MLA'),
			'description' => __('Style published by the Modern Language Association. Common in humanities, literature and language studies.'),
			'example'     => __('Zamenhof, Lazaro Ludoviko. <em>Unua Libro</em>. Kelter, 1887. Accessed 4 Mar. 2026. http://example.com'),
		),
		'wikipedia' => array(
			'label'       => __('Wikipedia'),
			'description' => __('Chicago-based style used in Wikipedia references. Suitable for encyclopaedic and general-purpose archives.'),
			'example'     => __('Zamenhof Lazaro Ludoviko (1887), <em>Unua Libro</em>, Kelter, accessed March 4th, 2026, http://example.com'),
		),
	);

	$currentStyle = get_option('custom_citation_style');
?>

<style>
	.field label {
		margin: 0;
	}
	
	.custom-citation-toggle {
		margin-bottom: 5px !important;
	}
	
	.custom-citation-style-option {
		margin-bottom: 12px;
	}
	
	.custom-citation-style-option label {
		font-weight: bold;
		cursor: pointer;
	}
	
	.custom-citation-style-desc {
		margin: 2px 0 2px 26px;
		color: #555;
		font-size: 0.92em;
	}
	
	.custom-citation-toggle {
		margin-left: 26px;
		font-size: 0.88em;
		color: #0073aa;
		cursor: pointer;
		background: none;
		border: none;
		padding: 0;
		text-decoration: underline;
	}
	
	.custom-citation-toggle:hover {
		color: #005177;
	}
	
	.custom-citation-example {
		display: none;
		margin: 0 22px 22px 22px;
		padding: 8px 12px;
		background: #f7f7f7;
		border-left: 3px solid #bbb;
		font-size: 0.88em;
		color: #333;
		font-family: Georgia, serif;
	}
</style>

<div id="customCitationSetting">

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Citation style'); ?></label>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('Choose the citation style for your site.'); ?>
			</p>

			<?php foreach ($styles as $key => $info): ?>
				<div class="custom-citation-style-option">
					<label>
						<input type="radio"
							name="custom_citation_style"
							value="<?php echo $key; ?>"
							<?php echo ($currentStyle === $key) ? 'checked="checked"' : ''; ?> />
						<?php echo $info['label']; ?>
					</label>
					<p class="custom-citation-style-desc"><?php echo $info['description']; ?></p>

					<button
							type="button"
							class="custom-citation-toggle"
							aria-expanded="false"
							data-target="example-<?php echo $key; ?>"
						><?php echo __('Show example ▼'); ?></button>
						<div class="custom-citation-example" id="example-<?php echo $key; ?>">
							<?php echo $info['example']; ?>
						</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Add Collection'); ?></label>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation">
				<?php echo __('Add collection info to citation.'); ?>
			</p>
			<?php echo $view->formCheckbox('custom_citation_collection', get_option('custom_citation_collection'), null, array('1', '0')); ?>
		</div>
	</div>

</div>

<script>
	(function () {
		var toggles = document.querySelectorAll('.custom-citation-toggle');
		toggles.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var targetId  = btn.getAttribute('data-target');
				var example   = document.getElementById(targetId);
				var expanded  = btn.getAttribute('aria-expanded') === 'true';

				if (expanded) {
					example.style.display = 'none';
					btn.setAttribute('aria-expanded', 'false');
					btn.textContent = '<?php echo addslashes(__('Show example ▼')); ?>';
				} else {
					example.style.display = 'block';
					btn.setAttribute('aria-expanded', 'true');
					btn.textContent = '<?php echo addslashes(__('Hide example ▲')); ?>';
				}
			});
		});
	})();
</script>
