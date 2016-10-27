<?php global $mdw_cms_admin; ?>

<div class="mdw-cms-admin-page taxonomies-page">

	<h2>Taxonomies <a href="<?php mdw_cms_admin_link(array('tab' => 'taxonomies', 'action' => 'update')); ?>" class="page-title-action">Add New</a></h2>

	<table class="wp-list-table widefat fixed striped mdw-cms-taxonomies">
		<thead>
		<tr>
			<th scope="col" id="id" class="id">ID</th>
			<th scope="col" id="taxonomy" class="taxonomy">Taxonomy</th>
			<th scope="col" id="post-types" class="object-types">Object Types</th>
			<th scope="col" id="actions" class="actions">&nbsp;</th>
		</thead>

		<tbody class="metaboxes-list">
			<?php if (count($mdw_cms_admin->options['taxonomies'])) : ?>
				<?php foreach($mdw_cms_admin->options['taxonomies'] as $taxonomy) : ?>
					<tr id="metabox-<?php echo $id; ?>" class="id">
						<td class="id" data-colname="ID"><?php echo $taxonomy['name']; ?></td>
						<td class="metabox" data-colname="Metabox">
							<strong><a class="row-title" href="<?php mdw_cms_admin_link(array('tab' => 'metaboxes', 'action' => 'update', 'id' => $taxonomy['name'])); ?>"><?php echo $taxonomy['args']['label']; ?></a></strong>
						</td>
						<td class="object-types" data-colname="Object Types"><?php mdw_cms_metabox_post_types_list($taxonomy['object_type']); ?></td>
						<td class="actions" data-colname="Actions"><a href="<?php mdw_cms_admin_link(); ?>"><span class="dashicons dashicons-trash"></span></a></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>

			<?php endif; ?>
			</tbody>

		<tfoot>
			<tr>
			</tr>
		</tfoot>

	</table>

</div>