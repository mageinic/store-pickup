define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    "Magento_Ui/js/form/form"
],function($,Abstract,provider) {

    return Abstract.extend({
        defaults: {

        },

        /**
         * Initializes component, invokes initialize method of Abstract class.
         *
         *  @returns {Object} Chainable.
         */
        initialize: function () {
            return this._super();
        },


        /**
         * Returns class based on current selected color
         *
         * @returns {String}
         */
        isSelected: function () {
            console.log(this.value());
            if(this.value()){
                if(!isNaN(this.value())){
                    var state = this.value();
                    $('.state-select option[value="'+state+'"]').attr('selected','selected').change();
                    setTimeout(function (){
                        $('.state-select option[value="'+state+'"]').attr('selected','selected').change();// val(state);
                    },10000);
                }else{
                    $('.state-text').val(state);
                }
            }

        }
    });
});
