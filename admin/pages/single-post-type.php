<?php $pickle_cms_post_type_args=pickle_cms_setup_post_type_args(); ?>

<h3><?php echo $pickle_cms_post_type_args['header']; ?> <a href="<?php pickle_cms_admin_link(array('tab' => 'post-types', 'action' => 'update')); ?>" class="page-title-action">Add New</a></h3>

<div class="pickle-cms-admin-page single-post-type-page">

	<form class="custom-post-types" method="post">
		<?php wp_nonce_field('update_cpts', 'pickle_cms_admin'); ?>

		<table class="form-table">
			<tbody>

				<tr>
					<th scope="row">
						<label for="name" class="required ">Post Type Name</label>
					</th>
					<td>
						<input type="text" name="name" id="name" class="required" value="<?php echo $pickle_cms_post_type_args['name']; ?>" /><span class="example">(e.g. movie)</span>

						<div id="pickle-cms-name-error" class="<?php echo $pickle_cms_post_type_args['error_class']; ?>"></div>
						<p class="description">
							Max 20 characters, can not contain capital letters or spaces. Reserved post types: post, page, attachment, revision, nav_menu_item.
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="label" class="required">Label</label>
					</th>
					<td>
						<input type="text" name="label" id="label" class="required" value="<?php echo $pickle_cms_post_type_args['label']; ?>" /><span class="example">(e.g. Movies)</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="singular_label" class="">Singular Label</label>
					</th>
					<td>
						<input type="text" name="singular_label" id="singular_label" value="<?php echo $pickle_cms_post_type_args['singular_label']; ?>" /><span class="example">(e.g. Movie)</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="description" class="">Description</label>
					</th>
					<td>
						<textarea name="description" id="description" rows="4" cols="40"><?php echo $pickle_cms_post_type_args['description']; ?></textarea>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="title" class="">Title</label>
					</th>
					<td>
						<select name="supports[title]" id="title">
							<option value="1" <?php selected($pickle_cms_post_type_args['supports']['title'], 1); ?>>True</option>
							<option value="0" <?php selected($pickle_cms_post_type_args['supports']['title'], 0); ?>>False</option>
						</select>
						<span class="example">(default True)</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="thumbnail" class="">Thumbnail</label>
					</th>
					<td>
						<select name="supports[thumbnail]" id="thumbnaill">
							<option value="1" <?php selected($pickle_cms_post_type_args['supports']['thumbnail'], 1); ?>>True</option>
							<option value="0" <?php selected($pickle_cms_post_type_args['supports']['thumbnail'], 0); ?>>False</option>
						</select>
						<span class="example">(default True)</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="editor" class="">Editor</label>
					</th>
					<td>
					<select name="supports[editor]" id="editor" >
						<option value="1" <?php selected($pickle_cms_post_type_args['supports']['editor'], 1); ?>>True</option>
						<option value="0" <?php selected($pickle_cms_post_type_args['supports']['editor'], 0); ?>>False</option>
					</select>
					<span class="example">(default True)</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="revisions" class="">Revisions</label>
					</th>
					<td>
						<select name="supports[revisions]" id="revisions">
							<option value="1" <?php selected($pickle_cms_post_type_args['supports']['revisions'], 1); ?>>True</option>
							<option value="0" <?php selected($pickle_cms_post_type_args['supports']['revisions'], 0); ?>>False</option>
						</select>
						<span class="example">(default True)</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="hierarchical" class="">Hierarchical</label>
					</th>
					<td>
						<select name="hierarchical" id="hierarchical">
							<option value="1" <?php selected($pickle_cms_post_type_args['hierarchical'], 1); ?>>True</option>
							<option value="0" <?php selected($pickle_cms_post_type_args['hierarchical'], 0); ?>>False</option>
						</select>
						<span class="example">(default False)</span>
						<p class="description">
							Whether the post type is hierarchical (e.g. page). Allows Parent to be specified. Note: "page-attributes" must be set to true to show
							the parent select box.
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="page_attributes" class="">Page Attributes</label>
					</th>
					<td>
						<select name="supports[page_attributes]" id="page_attributes">
							<option value="1" <?php selected($pickle_cms_post_type_args['supports']['page_attributes'], 1); ?>>True</option>
							<option value="0" <?php selected($pickle_cms_post_type_args['supports']['page_attributes'], 0); ?>>False</option>
						</select>

						<span class="example">(default False)</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="excerpt" class="">Excerpt</label>
					</th>
					<td>
						<select name="supports[excerpt]" id="has_excerpt">
							<option value="1" <?php selected($pickle_cms_post_type_args['supports']['excerpt'], 1); ?>>True</option>
							<option value="0" <?php selected($pickle_cms_post_type_args['supports']['excerpt'], 0); ?>>False</option>
						</select>

						<span class="example">(default False)</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="comments" class=""><?php echo __('Comments'); ?></label>
					</th>
					<td>
						<select name="supports[comments]" id="comments">
							<option value="1" <?php selected($pickle_cms_post_type_args['supports']['comments'], 1); ?>>True</option>
							<option value="0" <?php selected($pickle_cms_post_type_args['supports']['comments'], 0); ?>>False</option>
						</select>

						<span class="example">(default False)</span>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="icon" class=""><?php echo __('Icon'); ?></label>
					</th>
					<td>
						<input type="hidden" id="selected-dashicon" name="icon" value="<?php echo $pickle_cms_post_type_args['icon']; ?>" />
						<div class="selected-icon"><span class="dashicons <?php echo $pickle_cms_post_type_args['icon']; ?>"></span></div>
						<div class="change-text">Click icon to change:</div>
						<?php pickle_cms_dashicon_grid(); ?>
					</td>
				</tr>

			</tbody>
		</table>

		<p class="submit">
			<input type="submit" name="add-cpt" id="submit" class="button button-primary" value="<?php echo $pickle_cms_post_type_args['btn_text']; ?>">
		</p>

		<input type="hidden" name="cpt-id" id="cpt-id" value=<?php echo $pickle_cms_post_type_args['id']; ?> />
	</form>

</div>