/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint jquery:true*/
(function($) {
    'use strict';
    /**
     * Widget theme edit
     */
    $.widget('vde.themeEdit', {
        options: {
            editEvent: 'themeEdit',
            dialogSelector: '',
            confirmMessage: '',
            title: '',
            launchUrl: ''
        },
        themeId: null,

        /**
         * Form creation
         * @protected
         */
        _create: function() {
            this._bind();
            $(this.options.dialogSelector).on('dialogopen', $.proxy(this._wrapButton, this));

        },

        /**
         * Bind handlers
         * @protected
         */
        _bind: function() {
            $('body').on(this.options.editEvent, $.proxy(this._onEdit, this));
        },

        /**
         * @param event
         * @param data
         * @protected
         */
        _onEdit: function(event, data) {
            this.themeId = data.theme_id;
            var dialog = data.dialog = $(this.options.dialogSelector).data('dialog');
            dialog.messages.clear();
            dialog.text.set(this.options.confirmMessage);
            dialog.title.set(this.options.title);
            var buttons = data.confirm_buttons || [{
                text: 'Got it',
                id: this._getButtonHtmlId(),
                'class': 'primary',
                click: function() {}
            }];

            dialog.setButtons(buttons);
            dialog.open();
        },

        /**
         * @returns string
         * @protected
         */
        _getButtonHtmlId: function() {
            return 'get-it-theme-' + this.themeId;
        },

        /**
         * @protected
         */
        _wrapButton: function() {
            var link = $("<a></a>");
            link.attr({
                'target': '_blank',
                'href': [this.options.launchUrl + 'theme_id', this.themeId].join('/')
            });
            $('#' + this._getButtonHtmlId()).wrap(link);
        }
    });
})(jQuery);
