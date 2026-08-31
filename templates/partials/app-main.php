<?php
/**
 * Main app content: toolbar + table + pagination + modal (form & delete).
 * Used in front-end dashboard (/short/dashboard) and wp-admin menu.
 *
 * @var object[]    $links       Links on this page.
 * @var int         $total       Total results.
 * @var int         $total_pages Number of pages.
 * @var array       $stats       {total_links, total_clicks, active}
 * @var string      $search      Initial search keyword.
 * @var int         $page        Active page.
 * @var string      $list_url    Base URL for the link list.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	<div class="toolbar">
			<form method="get" class="search-bar" action="<?php echo esc_url( $list_url ); ?>" id="search-form">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" id="search-input"
					placeholder="Search alias or destination URL&hellip;" autocomplete="off">
			</form>
			<button type="button" class="btn btn-primary" id="btn-create">
				<span class="dashicons dashicons-plus"></span> Create link
			</button>
		</div>

		<div class="table-wrap" id="table-wrap">
			<table class="link-table">
				<thead>
					<tr>
						<th>Link</th>
						<th>Destination URL</th>
						<th class="col-num">Clicks</th>
						<th>Expires</th>
						<th class="col-actions">Actions</th>
					</tr>
				</thead>
				<tbody id="link-tbody">
					<?php foreach ( $links as $link ) : ?>
						<?php
						$expired   = Yasyes_Shortlink_Link_Model::is_expired( $link );
						$short_url = home_url( '/' . $link->short_code );
						?>
						<tr data-id="<?php echo esc_attr( $link->id ); ?>">
							<td>
								<a class="short-code" href="<?php echo esc_url( $short_url ); ?>" target="_blank" rel="noopener">/<?php echo esc_html( $link->short_code ); ?></a>
								<?php if ( $expired ) : ?><span class="badge badge-danger">expired</span><?php endif; ?>
							</td>
							<td><span class="target-url" title="<?php echo esc_attr( $link->original_url ); ?>"><?php echo esc_html( $link->original_url ); ?></span></td>
							<td class="col-num"><?php echo esc_html( number_format_i18n( (int) $link->click_count ) ); ?></td>
							<td><?php echo $link->expired_at ? esc_html( date_i18n( 'd M Y H:i', strtotime( $link->expired_at ) ) ) : '&mdash;'; ?></td>
							<td class="col-actions">
								<button type="button" class="btn-icon" data-copy="<?php echo esc_attr( $short_url ); ?>" title="Copy link"><span class="dashicons dashicons-admin-page"></span></button>
								<button type="button" class="btn-icon" data-edit="<?php echo esc_attr( $link->id ); ?>" title="Edit"><span class="dashicons dashicons-edit"></span></button>
								<button type="button" class="btn-icon btn-icon-danger" data-delete="<?php echo esc_attr( $link->id ); ?>" title="Delete"><span class="dashicons dashicons-trash"></span></button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( empty( $links ) ) : ?>
				<p class="muted empty" id="empty-state">
					<?php echo '' !== $search ? 'No results found.' : 'No links yet. Click "Create link" to get started.'; ?>
				</p>
			<?php endif; ?>
		</div>

		<nav class="pagination" id="pagination" aria-label="Page navigation"<?php echo $total_pages <= 1 ? ' hidden' : ''; ?>></nav>

<div class="modal-overlay" id="link-modal" hidden>
	<div class="modal modal-wide" role="dialog" aria-modal="true" aria-labelledby="form-title">
		<h2 id="form-title">Create link</h2>
		<form method="post" action="<?php echo esc_url( $list_url ); ?>" id="link-form">
			<?php wp_nonce_field( 'yasyes_shortlink_link_save' ); ?>
			<input type="hidden" name="action_type" value="create">

			<label class="field">
				<span>Destination URL</span>
				<input type="url" name="original_url" required placeholder="https://example.com/long-page" id="form-url">
			</label>

			<label class="field">
				<span>Alias <em class="optional">(optional when creating)</em></span>
				<input type="text" name="alias" pattern="[A-Za-z0-9]{3,20}" autocomplete="off"
					placeholder="promo2026" id="form-alias">
				<small class="hint">3&ndash;20 alphanumeric characters. Leave empty for auto-generate.</small>
			</label>

			<label class="field">
				<span>Expires <em class="optional">(optional)</em></span>
				<input type="datetime-local" name="expired_at">
			</label>

			<button type="submit" class="btn btn-primary btn-block">Save</button>
		</form>
	</div>
</div>

<div class="modal-overlay" id="delete-modal" hidden>
	<div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-title">
		<h2 id="delete-title">Delete link?</h2>
		<p class="muted">/<strong id="delete-code"></strong> will be permanently deleted.</p>
		<div class="modal-actions">
			<button type="button" class="btn" id="delete-cancel">Cancel</button>
			<button type="button" class="btn btn-danger" id="delete-confirm">
				<span class="dashicons dashicons-trash"></span> Yes, delete
			</button>
		</div>
	</div>
</div>
