/**
 * {license_notice}
 *
 * @category    frontend product msrp
 * @package     mage
 * @copyright   {copyright}
 * @license     {license_link}
 */

/*jshint browser:true jquery:true*/
(function($, window) {
    "use strict";
    $.widget('mage.addWishListToCart', {

        options: {
            dataAttribute: 'item-id',
            nameFormat: 'qty[{0}]',
            wishListFormSelector: '#wishlist-view-form',
            btnRemoveSelector: '.btn-remove',
            qtySelector: '.qty',
            addToCartSelector: '.btn-cart',
            addAllToCartSelector: '.btn-add',
            commentInputType: 'textarea'
        },

        /**
         * Bind handlers to events
         */
        _create: function() {
            $(this.options.wishListFormSelector).on('click', this.options.addToCartSelector, $.proxy(this._addItemsToCart, this))
                .on('click', this.options.btnRemoveSelector, $.proxy(this._confirmRemoveWishlistItem, this))
                .on('click', this.options.addAllToCartSelector, $.proxy(this._addAllWItemsToCart, this))
                .on('focusin focusout', this.options.commentInputType, $.proxy(this._focusComment, this));
        },

        /**
         * Validate and Redirect
         * @private
         * @param {string} url
         */
        _validateAndRedirect: function(url) {
            if ($(this.options.wishListFormSelector).validation({
                errorPlacement: function(error, element) {
                    error.insertAfter(element.next());
                }
            }).valid()) {
                window.location.href = url;
            }
        },

        /**
         * Add items to cart
         * @private
         * @param {event} e
         */
        _addItemsToCart: function(e) {
            var btn = $(e.currentTarget),
                itemId = btn.data(this.options.dataAttribute),
                url = this.options.addToCartUrl.replace('%item%', itemId),
                inputName = $.validator.format(this.options.nameFormat, itemId),
                inputValue = $(this.options.wishListFormSelector).find('[name="' + inputName + '"]').val(),
                separator = (url.indexOf('?') >= 0) ? '&' : '?';
            url += separator + inputName + '=' + encodeURIComponent(inputValue);

            this._validateAndRedirect(url);
        },

        /**
         * Confirmation window for removing wish list item
         * @private
         */
        _confirmRemoveWishlistItem: function() {
            return window.confirm(this.options.confirmRemoveMessage);
        },

        /**
         * Add all wish list items to cart
         * @private
         */
        _addAllWItemsToCart: function() {
            var url = this.options.addAllToCartUrl;
            var separator = (url.indexOf('?') >= 0) ? '&' : '?';
            $(this.options.wishListFormSelector).find(this.options.qtySelector).each(
                function(index, elem) {
                    url += separator + $(elem).prop('name') + '=' + encodeURIComponent($(elem).val());
                    separator = '&';
                }
            );

            this._validateAndRedirect(url);
        },

        /**
         * Toggle comment string
         * @private
         * @param {event} e
         */
        _focusComment: function(e) {
            var commentInput = e.currentTarget;
            commentInput.value = commentInput.value === this.options.commentString ? '' : this.options.commentString;
        }
    });
})(jQuery, window);
