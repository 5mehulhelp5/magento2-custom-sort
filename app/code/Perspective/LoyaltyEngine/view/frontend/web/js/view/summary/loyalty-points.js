/**
 * Summary total component for displaying loyalty points discount row in checkout Order Summary.
 */
define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Perspective_LoyaltyEngine/summary/loyalty-points'
        },
        totals: quote.getTotals(),

        /**
         * Determine whether loyalty discount row should be displayed in Order Summary
         *
         * @returns {Boolean}
         */
        isDisplayed: function () {
            return this.isFullMode() && this.getPureValue() !== 0;
        },

        /**
         * Get loyalty discount row title
         *
         * @returns {String}
         */
        getTitle: function () {
            var segment = this.getLoyaltySegment();
            return segment && segment.title ? segment.title : this.title;
        },

        /**
         * Get numeric discount amount
         *
         * @returns {Number}
         */
        getPureValue: function () {
            var segment = this.getLoyaltySegment();
            return segment && segment.value ? parseFloat(segment.value) : 0.0;
        },

        /**
         * Get formatted localized price
         *
         * @returns {String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        },

        /**
         * Find loyalty_points segment from quote totals
         *
         * @returns {Object|null}
         */
        getLoyaltySegment: function () {
            var totalsData = this.totals();

            if (!totalsData || !totalsData.total_segments) {
                return null;
            }

            return totalsData.total_segments.find(function (segment) {
                return segment.code === 'loyalty_points';
            }) || null;
        }
    });
});
