/**
 * {license_notice}
 *
 * @category    mage
 * @package     mage
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true */
(function($, console) {
    $.widget("mage.loader", {
        loaderStarted: 0,
        options: {
            defaultContainer: '[data-container=body]',
            loaderContainer: '[data-role="loader"]',
            icon: '',
            texts: {
                loaderText: $.mage.__('Please wait...'),
                imgAlt: $.mage.__('Loading...')
            },
            template: '<div class="loading-mask" data-role="loader">' +
                '<div class="loader">' +
                '<img {{if texts.imgAlt}}alt="${texts.imgAlt}"{{/if}} src="${icon}">' +
                '<p>{{if texts.loaderText}}${texts.loaderText}{{/if}}</p>' +
                '</div>' +
                '</div>'
        },

        /**
         * Loader creation
         * @protected
         */
        _create: function() {
            this._bind();
        },

        /**
         * Bind on ajax events
         * @protected
         */
        _bind: function() {
            this._on({
                'processStop': 'hide',
                'processStart': 'show',
                'show.loader': 'show',
                'hide.loader': 'hide',
                'contentUpdated.loader': '_contentUpdated'
            });
        },

        /**
         * Verify loader present after content updated
         *
         * This will be cleaned up by the task MAGETWO-11070
         *
         * @param event
         * @private
         */
        _contentUpdated: function(event) {
            this.show();
        },

        /**
         * Show loader
         */
        show: function() {
            this._render();
            this.loaderStarted++;
            this._findLoader().show();
            return false;
        },

        /**
         * Hide loader
         */
        hide: function() {
            if (this.loaderStarted > 0) {
                this.loaderStarted--;
                if (this.loaderStarted === 0) {
                    this._findLoader().hide();
                }
            }
            return false;
        },

        _findLoader: function() {
            return this.element.find(this.options.loaderContainer);
        },

        /**
         * Render loader
         * @protected
         */
        _render: function() {
            if (this._findLoader().length === 0) {
                this.loader = $.tmpl(this.options.template, this.options)
                    .css(this._getCssObj());
                this.element.prepend(this.loader);
            }
        },

        /**
         * Prepare object with css properties for loader
         * @protected
         */
        _getCssObj: function() {
            var isBodyElement = this.element.is(this.options.defaultContainer),
                width = isBodyElement ? $(window).width() : this.element.outerWidth(),
                height = isBodyElement ? $(window).height() : this.element.outerHeight(),
                position = isBodyElement ? 'fixed' : 'relative';
            return {
                height: height + 'px',
                width: width + 'px',
                position: position,
                'margin-bottom': '-' + height + 'px'
            };
        },

        /**
         * Destroy loader
         */
        _destroy: function() {
            this._findLoader().remove();
        }
    });

    /**
     * This widget takes care of registering the needed loader listeners on the body
     */
    $.widget("mage.loaderAjax", {
        options: {
            defaultContainer: '[data-container=body]'
        },
        _create: function() {
            this._bind();
            // There should only be one instance of this widget, and it should be attached
            // to the body only. Having it on the page twice will trigger multiple processStarts.
            if (!this.element.is(this.options.defaultContainer) && $.mage.isDevMode(undefined)) {
                console.warn("This widget is intended to be attached to the body, not below.");
            }
        },
        _bind: function() {
            this._on(this.options.defaultContainer, {
                'ajaxSend': '_onAjaxSend',
                'ajaxComplete': '_onAjaxComplete'
            });
        },
        _getJqueryObj: function(loaderContext) {
            var ctx;
            // Check to see if context is jQuery object or not.
            if (loaderContext) {
                if (loaderContext.jquery) {
                    ctx = loaderContext;
                } else {
                    ctx = $(loaderContext);
                }
            } else {
                ctx = $('[data-container="body"]');
            }
            return ctx;
        },
        _onAjaxSend: function(e, jqxhr, settings) {
            if (settings && settings.showLoader) {
                var ctx = this._getJqueryObj(settings.loaderContext);
                ctx.trigger('processStart');

                // Check to make sure the loader is there on the page if not report it on the console.
                // NOTE that this check should be removed before going live. It is just an aid to help
                // in finding the uses of the loader that maybe broken.
                if (console && !ctx.parents('[data-role="loader"]').length) {
                    console.warn('Expected to start loader but did not find one in the dom');
                }
            }
        },
        _onAjaxComplete: function(e, jqxhr, settings) {
            if (settings && settings.showLoader) {
                this._getJqueryObj(settings.loaderContext).trigger('processStop');
            }
        }

    });
})(jQuery, console);
