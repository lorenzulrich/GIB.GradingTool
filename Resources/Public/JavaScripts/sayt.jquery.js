/*
 *******************************************************************************
 *
 * sayt - Save As You Type
 *
 *******************************************************************************
 *
 * This plugin autosaves form input as it is being entered by the
 * user. It is saved to the local browser storage during each keyup and change.
 *
 * When a page is reloaded for any reason, the form data is automatically
 * re-entered for the user by reading the storage. The storage can be deleted
 * on the fly by the plugin if required.
 *
 *******************************************************************************
 *
 * Intructions: 
 * By: Ben Griffiths, ben@ben-griffiths.com
 * Version: 1.1.1
 * Updated: April 8th, 2012
 *
 * Dependencies:
 *
 * jStorage 0.4.8 (and its dependencies): https://github.com/andris9/jStorage
 *
 *******************************************************************************
 *
 * Licensed under The MIT License (MIT)
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Copyright (c) 2011 Ben Griffiths (mail@thecodefoundryltd.com)
 * Copyright (c) 2014 Lorenz Ulrich (lorenz.ulrich@visol.ch)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *******************************************************************************
 */
(function($)
{
    $.fn.sayt = function(options)
    {

        /*
         * Get/Set the settings
         */
        var settings = $.extend(
            {
                'erase'          : false,
                'days'           : false,
                'autosave'       : true,
                'savenow'        : false,
                'recover'        : false,
                'autorecover'    : true,
                'checksaveexists': false
            }, options);


        /*
         * Define the form
         */
        var theform = this;

        /*
         * Erase storage data for one form
         */
        if (settings['erase'] == true) {
            $.jStorage.deleteKey('autosaveForm-' + theform.attr('id'));

            return true;
        }


        /*
         * Get the stored form data
         */
        var autoSavedData = $.jStorage.get('autosaveForm-' + theform.attr('id'));


        /*
         * Check to see if saved data exists
         */
        if (settings['checksaveexists'] == true) {
            if (autoSavedData) {
                return true;
            }
            else {
                return false;
            }
        }


        /*
         * Perform a manual save
         */
        if (settings['savenow'] == true) {
            var form_data = theform.serializeArray();
            saveData(form_data);

            return true;
        }


        /*
         * Recover the form info from the storage (if it has one)
         */
        if (settings['autorecover'] == true || settings['recover'] == true) {
            if (autoSavedData)
            {
                var newDataString = autoSavedData.split(':::--FORMSPLITTERFORVARS--:::');

                var field_names_array = {};

                $.each(newDataString, function(i, field) {
                    var fields_arr = field.split(':::--FIELDANDVARSPLITTER--:::');

                    if ($.trim(fields_arr[0]) != '') {
                        if($.trim(fields_arr[0]) in field_names_array) {
                            field_names_array[$.trim(fields_arr[0])] = (field_names_array[$.trim(fields_arr[0])] + ':::--MULTISELECTSPLITTER--:::' + fields_arr[1]);
                        } else {
                            field_names_array[$.trim(fields_arr[0])] = fields_arr[1];
                        }
                    }
                });

                $.each(field_names_array, function(key, value) {
                    if (strpos(value, ':::--MULTISELECTSPLITTER--:::') > 0) {
                        var tmp_array = value.split(':::--MULTISELECTSPLITTER--:::');

                        $.each(tmp_array, function(tmp_key, tmp_value) {
                            $('input[name="' + key + '"], select[name="' + key + '"], textarea[name="' + key + '"]').find('[value="' + tmp_value + '"]').prop('selected', true);
                            $('input[name="' + key + '"][value="' + tmp_value + '"], select[name="' + key + '"][value="' + tmp_value + '"], textarea[name="' + key + '"][value="' + tmp_value + '"]').prop('checked', true);
                        });
                    } else {
                        $('input[name="' + key + '"], select[name="' + key + '"], textarea[name="' + key + '"]').val([value]);
                    }
                });
            }

            /*
             * if manual recover action, return false
             */
            if (settings['recover'] == true) {
                return true;
            }
        }


        /*
         * Autosave - on typing and changing
         */
        if (settings['autosave'] == true) {
            this.find('input, select, textarea').each(function(index) {
                $(this).change(function() {
                    var form_data = theform.serializeArray();
                    saveData(form_data);
                });

                $(this).keyup(function() {
                    var form_data = theform.serializeArray();
                    saveData(form_data);
                });
            });
        }


        /*
         * Save form data to the storage
         */
        function saveData(data) {
            var dataString = '';

            jQuery.each(data, function(i, field) {
                dataString = dataString + field.name + ':::--FIELDANDVARSPLITTER--:::' + field.value + ':::--FORMSPLITTERFORVARS--:::';
            });
            $.jStorage.set('autosaveForm-' + theform.attr('id'), dataString);
            if (settings['days'] !== false) {
                $.jStorage.setTTL('autosaveForm-' + theform.attr('id'), settings['days'] * 24 * 60 * 60 * 1000);
            }
        }


        /*
         * strpos - equiv to PHP's strpos
         */
        function strpos(haystack, needle, offset) {
            var i = (haystack+'').indexOf(needle, (offset || 0));
            return i === -1 ? false : i;
        }

    };
})(jQuery);