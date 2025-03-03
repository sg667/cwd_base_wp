<?php // Taxonomy listings ?>

<div id="post-<?php the_ID(); ?>" <?php post_class('card'); ?>>

	<div class="group-image">

		<a href="<?php echo the_permalink(); ?>">

			<?php cwd_base_get_image(); ?>

			<div class="overlay">
				<h2 class="h3">
					<span class="deco">
						<?php the_title(); ?>
					</span>
				</h2>
			</div>

		</a>

	</div>

	<div class="group-fields">

		<?php cwd_base_get_the_date(); ?>

		<p class="summary">
									
			<?php 
			
				$archive_options = get_field('archive_options', 'options');
				$excerpt_length = $archive_options[$post_type]['excerpt_length_' . $post_type];
			
				echo custom_excerpt($excerpt_length); // Characters
			
			?>
		</p>
			
		<?php cwd_base_get_tag_options(); ?>

	</div>

</div>