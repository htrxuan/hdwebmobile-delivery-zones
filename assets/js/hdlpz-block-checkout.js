(function (wp, wc) {
	'use strict';

	if (!wp || !wp.plugins || !wp.element || !wc || !wc.blocksCheckout) {
		return;
	}

	var createElement = wp.element.createElement;

	// The slot passes its fill props to this element, including the Store API cart's
	// extension data, so the note follows the shipping address as the shopper edits it.
	function ZoneNote(props) {
		var extensions = props.extensions || {};
		var note = extensions.hdlpz ? extensions.hdlpz.note : '';

		if (!note) {
			return null;
		}

		return createElement(
			'div',
			{ className: 'wc-block-components-totals-wrapper hdlpz-zone-note' },
			createElement('p', { style: { margin: 0, padding: '12px 16px' } }, note)
		);
	}

	wp.plugins.registerPlugin('hdlpz-zone-note', {
		scope: 'woocommerce-checkout',
		render: function () {
			return createElement(wc.blocksCheckout.ExperimentalOrderMeta, null, createElement(ZoneNote));
		},
	});
})(window.wp, window.wc);
