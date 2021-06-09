<?php // News archive listings ?>

<div id="post-<?php the_ID(); ?>" <?php post_class('card flex-4'); ?>>

	<div class="group-image">

		<a href="<?php echo the_permalink(); ?>">

			<?php cwd_base_get_image(); ?>

			<div class="overlay">
				<h3 class="designer">
					<span class="deco">
						<?php the_title(); ?>
					</span>
				</h3>
			</div>

		</a>
		
	</div>
		
	<div class="group-fields">

		<?php cwd_base_get_the_date(); ?>

		<p class="summary">
			<?php 
			
				$archive_options = get_field('archive_options', 'options');
				$excerpt_length = $archive_options['excerpt_length'];
			
				echo custom_excerpt($excerpt_length); // Characters
			
			?>
		</p>
			
		<?php cwd_base_get_tag_options(); ?>

	</div>

</div>