/**
 * Script handle ajax when change dropdown
 */

jQuery(document).ready(function() {
    vn_location.initAjax();
});

var vn_location = {
    initAjax: function() {
        jQuery('#tbl-location').on('change', '.v-select', function() {
            var type = jQuery(this).attr('id');
            var val = jQuery(this).find('option:selected').val();
            
            jQuery.ajax({
                type: 'GET',
                url: ajaxObj.ajax_url,
                cache: false,
                dataType: 'json',
                data: {
                    action: 'cb_ajax_location',
                    type: type,
                    val: val
                },
                success: function(data, textStatus, jqXHR) {
                    if (data.length > 0) {                    
                        if (type == 'v_city') {
                            var options = '<option value="0"> -- Select -- </option>';
                            jQuery(data).each(function(i) {
                                options += '<option value="' + data[i].value + '">' + data[i].name + '</option>'
                            })
                            jQuery('#v-district').empty();
                            jQuery('#v-ward').empty();
                            jQuery('<option value="0"> -- Select -- </option>').appendTo('#v-ward');
                            jQuery(options).appendTo('#v-district');
                        } else if (type == 'v-district') {
                            jQuery(data).each(function(i) {
                                options += '<option value="' + data[i].value + '">' + data[i].name + '</option>'
                            })
                            jQuery('#v-ward').empty();
                            jQuery(options).appendTo('#v-ward');
                        }
                    }
                }
            });
        });
    },
}

