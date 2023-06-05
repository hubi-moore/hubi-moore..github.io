/* DATE PICKER START */
jQuery("#cleardata").click(function () {
    jQuery("#datadostawy").val("");
});
let dostawa_zew_notice = jQuery('.messages.dostawa_zew.im_visible li.notice-msg ul li span:not([class])');
let original_txt = dostawa_zew_notice.text();
jQuery(function ($){
    $('input[name="shipping_date_type"]').on('change',function (){
        console.log(this);
        if(this.value === 'shipping_date_asap') {
            shipping_date('asap');
        } else if(this.value === 'shipping_date_later') {
            shipping_date('later');
        } else if(this.value === 'shipping_date_waiting') {
            shipping_date('waiting');
        }
    });
})
function shipping_date(type)
{
    let current_target = window.event.target;

    if(typeof  type === 'string' && type === 'asap') {
        // reset picker hide picker
        jQuery("#datadostawy").val("");
        jQuery(".pick_shipping_date_later").hide();
        jQuery('input[name="shipping_date_type"]').each((index, item)=>{
            jQuery(item).removeAttr('checked');
            if(item === current_target) {
                jQuery(current_target).prop('checked',true);
            }
        });
        if(dostawa_zew_notice) {
            let new_txt = dostawa_zew_notice.text();
            dostawa_zew_notice.text(new_txt.replace('Twoje zamówienie w całości zostanie wysłane jedną dostawą w ciągu 5 dni roboczych. ','Część zamówienia zostanie wysłana osobną dostawą powyżej 24h. '));
            dostawa_zew_notice.parent().find('.dostawa_zew_show').show();
        }
        jQuery("#termin_realizacji").val('Natychmiastowa');
        setChosenTerminRealizacji('asap');
        updateCheckout('shipping_method');
    } else if(typeof  type === 'string' && type === 'later') {
        // show picker
        jQuery('input[name="shipping_date_type"]').each((index, item)=>{
            jQuery(item).removeAttr('checked');
            if(item === current_target) {
                jQuery(current_target).prop('checked',true);
            }
        });
        jQuery("#datepicker").datepicker();
        jQuery("#termin_realizacji").val('Późniejszy termin');
        jQuery(".pick_shipping_date_later").show();
        // $("#datepicker").datepicker('show');
        let selectedDate = jQuery.datepicker._getDateDatepicker(jQuery("#datepicker")[0], true);
        let formattedDate = jQuery.datepicker.formatDate('yy-mm-dd', selectedDate);
        console.log(selectedDate);
        setChosenTerminRealizacji('later');
        jQuery("#datadostawy").val(formattedDate);
        updateCheckout('shipping_method');
    } else if(typeof  type === 'string' && type === 'waiting') {
        jQuery('input[name="shipping_date_type"]').each((index, item)=>{
            jQuery(item).removeAttr('checked');
            if(item === current_target) {
                jQuery(current_target).prop('checked',true);
            }
        });
        dostawa_zew_notice.text(original_txt.replace('Część zamówienia zostanie wysłana osobną dostawą powyżej 24h. ','Twoje zamówienie w całości zostanie wysłane jedną dostawą w ciągu 5 dni roboczych. '));
        dostawa_zew_notice.parent().find('.dostawa_zew_show').hide();
        jQuery("#datadostawy").val("");
        jQuery("#termin_realizacji").val('2-3 dni robocze');
        setChosenTerminRealizacji('wait');
        updateCheckout('shipping_method');
    }
}

/* Polish initialisation for the jQuery UI date picker plugin. */
/* Written by Jacek Wysocki (jacek.wysocki@gmail.com). */
jQuery(function ($) {
    $.datepicker.regional['pl'] = {
        closeText: 'Zamknij',
        prevText: '&#x3c;Poprzedni',
        nextText: 'Następny&#x3e;',
        currentText: 'Dziś',
        monthNames: ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec',
            'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'],
        monthNamesShort: ['Sty', 'Lu', 'Mar', 'Kw', 'Maj', 'Cze',
            'Lip', 'Sie', 'Wrz', 'Pa', 'Lis', 'Gru'],
        dayNames: ['Niedziela', 'Poniedzialek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota'],
        dayNamesShort: ['Nie', 'Pn', 'Wt', 'Śr', 'Czw', 'Pt', 'So'],
        dayNamesMin: ['N', 'Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'So'],
        weekHeader: 'Tydz',
        dateFormat: 'yy-mm-dd',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: '',
        minDate: 3,
        maxDate: 30,

    };

    $.datepicker.setDefaults($.datepicker.regional['pl']);
    $.datepicker.setDefaults({
        onSelect: function ()
        {
            // console.log('onSelect');
            let selectedDate = $.datepicker._getDateDatepicker($("#datepicker")[0], true);
            let formattedDate = $.datepicker.formatDate('yy-mm-dd', selectedDate);
            $("#datadostawy").val(formattedDate);
            updateCheckout('shipping_method');
        }
    });
    $("#datepicker").datepicker();
    // $( ".selector" ).datepicker( "option", "dateFormat", "yyyy-mm-dd" );
    $(".pick_shipping_date_later").hide();
});
/* DO NOT ALLOW datepicker when payment is banktransfer etc  inside base/default/js/opcheckout.js getvalue */
jQuery(function ($) {
    $('input[name="payment[method]"]').each((index, item)=>{
        let isBanktransfer1 = item.value === 'banktransfer';
        // let isBanktransfer2 = item.value === 'checkmo';
        // if((isBanktransfer1 || isBanktransfer2) && $(item).is(':checked')) {
        if(isBanktransfer1 && $(item).is(':checked')) {
            $("#datadostawy").val("");
            $(".pick_shipping_date_later").hide();
            $('#shipping_date_asap').prop('checked',true);
            $('.shipping_date_item.later').hide();
        }
    });
});
/* DATE PICKER END */