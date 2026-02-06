jQuery(function ($) {
	$('.hst-showcase-category-btn').on('click', '.hst-btn', function () {
		const $btn = $(this);
		const blockId = $btn.data('blockid');
		const cat = $btn.data('cat');
		const $wrapper = $('.hst-showcase-posts[data-blockid="' + blockId + '"]');
		const postsPerPage = $wrapper.data('posts-per-page');
		const order = $wrapper.data('order');

		$('.hst-btn[data-blockid="' + blockId + '"]').removeClass('active');
		$btn.addClass('active');

		$.ajax({
			type: 'POST',
			url: spiceBlocksAjax.ajaxurl,
			data: {
				action: 'spiceblocks_filter',
				cat: cat,
				postsPerPage: postsPerPage,
				order: order,
				nonce: spiceBlocksAjax.nonce,
			},
			beforeSend: function () {
				$wrapper.html('<p>Loading...</p>');
			},
			success: function (res) {
				if (res.success) {
					$wrapper.html(res.data);
				} else {
					$wrapper.html('<p>No posts found.</p>');
				}
			},
			error: function () {
				$wrapper.html('<p>Failed to load posts.</p>');
			}
		});
	});
});
