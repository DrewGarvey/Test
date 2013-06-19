$(document).ready(function(){
    /** 
     * Programmed by Biber Ltd. (http://biberltd.com)
     * Author: Can Berkol
     * Version: 1.0.0
     * 
     * Handles control panel user-interactions of Biber CK Editor Field Type.
     * http://biberltd.com/wiki/English:ckeditor/
     */
     /**
      * EVENT :: CLICK :: preset_custom
      * Shows / Hides #bbr_ck_custom_settings
      */
      $('#preset_custom').click(function(){
        var custom_settings_panel = $('#bbr_ck_custom_settings');
        
        if(custom_settings_panel.is(':hidden')){
            custom_settings_panel.slideDown();
        }
        else{
            custom_settings_panel.slideUp();
        }
        /**
         * Other radio buttons must close the custom settings panel
         */
        $('#preset_minimalistic').click(function(){
            $('#bbr_ck_custom_settings:visible').slideUp();
        });
        $('#preset_fundemental').click(function(){
            $('#bbr_ck_custom_settings:visible').slideUp();
        });
        $('#preset_full').click(function(){
            $('#bbr_ck_custom_settings:visible').slideUp();
        });
      });
     /**
      * EVENT :: CLICK :: custom_row1_add
      * Moves options from #custom_row1 to #custom_row1_selected
      */
      $('#custom_row1_add').click(function(){
        $('#custom_row1 :selected').each(function(i, selected){
            $(selected).attr('selected', '').hide();
            $('#custom_row1_selected').append($(selected).attr('selected', '').show());
            // $('#custom_row1_selected').sortOptions();
            /**
             *  Delete the option from other rows as well.
             */
            $('#custom_row2').children().each(function(i, todelete){
                if($(selected).attr('value') == $(todelete).attr('value')){
                    $(todelete).remove();
                }
            });
            $('#custom_row3').children().each(function(i, todelete){
                if($(selected).attr('value') == $(todelete).attr('value')){
                    $(todelete).remove();
                }
            });
        });
      });
      /**
      * EVENT :: CLICK :: custom_row1_remove
      * Moves options from #custom_row1_selected to #custom_row1
      */
      $('#custom_row1_remove').click(function(){
        $('#custom_row1_selected :selected').each(function(i, selected){
            var re_add1 = $(selected).clone();
            var re_add2 = $(selected).clone();
            $(selected).attr('selected', '').hide();
            $('#custom_row1').append($(selected).attr('selected', '').show());
            $('#custom_row1').sortOptions();
            $('#custom_row2').append(re_add1);
            $('#custom_row2').sortOptions();
            $('#custom_row3').append(re_add2);
            $('#custom_row3').sortOptions();
        });
      });
    /**
      * EVENT :: CLICK :: custom_row2_add
      * Moves options from #custom_row2 to #custom_row2_selected
      */
      $('#custom_row2_add').click(function(){
        $('#custom_row2 :selected').each(function(i, selected){
            $(selected).attr('selected', '').hide();
            $('#custom_row2_selected').append($(selected).attr('selected', '').show());
            // $('#custom_row2_selected').sortOptions();
            /**
             *  Delete the option from other rows as well.
             */
            $('#custom_row1').children().each(function(i, todelete){
                if($(selected).attr('value') == $(todelete).attr('value')){
                    $(todelete).remove();
                }
            });
            $('#custom_row3').children().each(function(i, todelete){
                if($(selected).attr('value') == $(todelete).attr('value')){
                    $(todelete).remove();
                }
            });
        });
      });
      /**
      * EVENT :: CLICK :: custom_row2_remove
      * Moves options from #custom_row2_selected to #custom_row2
      */
      $('#custom_row2_remove').click(function(){
        $('#custom_row2_selected :selected').each(function(i, selected){
            var re_add1 = $(selected).clone();
            var re_add2 = $(selected).clone();
            $(selected).attr('selected', '').hide();
            $('#custom_row2').append($(selected).attr('selected', '').show());
            $('#custom_row2').sortOptions();
            $('#custom_row1').append(re_add1);
            $('#custom_row1').sortOptions();
            $('#custom_row3').append(re_add2);
            $('#custom_row3').sortOptions();
        });
      });
    /**
      * EVENT :: CLICK :: custom_row3_add
      * Moves options from #custom_row3 to #custom_row3_selected
      */
      $('#custom_row3_add').click(function(){
        $('#custom_row3 :selected').each(function(i, selected){
            $(selected).attr('selected', '').hide();
            $('#custom_row3_selected').append($(selected).attr('selected', '').show());
            // $('#custom_row3_selected').sortOptions();
            /**
             *  Delete the option from other rows as well.
             */
            $('#custom_row2').children().each(function(i, todelete){
                if($(selected).attr('value') == $(todelete).attr('value')){
                    $(todelete).remove();
                }
            });
            $('#custom_row1').children().each(function(i, todelete){
                if($(selected).attr('value') == $(todelete).attr('value')){
                    $(todelete).remove();
                }
            });
        });
      });
      /**
      * EVENT :: CLICK :: custom_row1_remove
      * Moves options from #custom_row3_selected to #custom_row3
      */
      $('#custom_row3_remove').click(function(){
        $('#custom_row3_selected :selected').each(function(i, selected){
            var re_add1 = $(selected).clone();
            var re_add2 = $(selected).clone();
            $(selected).attr('selected', '').hide();
            $('#custom_row3').append($(selected).attr('selected', '').show());
            $('#custom_row3').sortOptions();
            $('#custom_row2').append(re_add1);
            $('#custom_row2').sortOptions();
            $('#custom_row1').append(re_add2);
            $('#custom_row1').sortOptions();
        });
      });
      /**
      * EVENT :: CLICK :: sibling p's child .submit of #bbr_fieldtype_settings_container
      * Select options from #custom_row1_selected, #custom_row2_selected, #custom_row3_selected
      */
      $('#bbr_fieldtype_settings_container').next().children('.submit').click(function(e){
         $('#custom_row1_selected').children().each(function(i, option){
            $(option).attr('selected', 'selected');
        });
        $('#custom_row2_selected').children().each(function(i, option){
            $(option).attr('selected', 'selected');
        });
        $('#custom_row3_selected').children().each(function(i, option){
            $(option).attr('selected', 'selected');
        });
      });
});