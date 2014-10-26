var lookAtMeTimer = null;

$(function() {
	if(typeof lookAtMeUrl != "undefined") {	
		startTimer();
	}

		myMarkdownSettings = {
		    nameSpace:          'markdown', // Useful to prevent multi-instances CSS conflict
		    previewParserPath:  '~/sets/markdown/preview.php',
		    width: '400px',
		    onShiftEnter:       {keepDefault:false, openWith:'\n\n'},
		    markupSet: [
		        {name:'Rubrik nivå 1', key:"1", placeHolder:'Din rubrik här...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '=') } },
		        {name:'Rubrik nivå 2', key:"2", placeHolder:'Din rubrik här...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '-') } },
		        {name:'Rubrik 3', key:"3", openWith:'### ', placeHolder:'Din rubrik här...' },
		        {name:'Rubrik 4', key:"4", openWith:'#### ', placeHolder:'Din rubrik här...' },
		        {name:'Rubrik 5', key:"5", openWith:'##### ', placeHolder:'YDin rubrik här...' },
		        {name:'Rubrik 6', key:"6", openWith:'###### ', placeHolder:'Din rubrik här...' },
		        {separator:'---------------' },        
		        {name:'Fet stil', key:"B", openWith:'**', closeWith:'**'},
		        {name:'Kursiv stil', key:"I", openWith:'_', closeWith:'_'},
		        {separator:'---------------' },
		        {name:'Punktlista', openWith:'- ' },
		        {name:'Numrerad list', openWith:function(markItUp) {
		            return markItUp.line+'. ';
		        }},
		        {separator:'---------------' },
		        {name:'Bild', key:"P", replaceWith:'![[![Alternative text]!]]([![Url:!:http://]!] "[![Title]!]")'},
		        {name:'Länk', key:"L", openWith:'[', closeWith:']([![Url:!:http://]!] "[![Title]!]")', placeHolder:'Your text to link here...' },
		        {separator:'---------------'},    
		        {name:'Citat', openWith:'> '},
		        {name:'Kodexempel', openWith:'(!(\t|!|`)!)', closeWith:'(!(`)!)'}
		    ]
		}


	$('a[rel=external]').each(function() {
		$(this).attr('target', '_blank');
		$(this).after('&nbsp;<span class="fa fa-external-link"></span>')
	});

	$(document).ready(function() {
		var width = 620;
		if($(".markdown").length > 0) {
			$(".markdown").markItUp(myMarkdownSettings).each(function() {
				$(this).css('width', width - 60)
					.closest('.markItUpContainer').width(width - 14)
					.closest('.markItUp').width(width);
			});
		}
	});


	startMomentJsTimer();



	$(document).on('focus', 'input[data-helpbox], textarea[data-helpbox]', activateHelpbox);

	if($("#e1").length ==1) {
	 $("#e1").select2({
	 	width: '100%',
	 	createSearchChoice : function(term, data) {
	 		return {
	 			id : '_' + encodeURIComponent(term),
	 			text: term
	 		};
	 	},
	 	placeholder : 'Sätt åtminstone en tagg. Du kan max sätta fem.',
	 	maximumSelectionSize: 5,
	 	minimumInputLength: 2,
	 	initSelection : function(element, callback) {

	 		var val = element.val(),
	 			items = [],
	 			data = [];
	 		if( val !="") {
	 			items = val.split(',');
	 			var existing = [];
	 			for( var i=0; i < items.length; i++) {
	 				if( isNaN(items[i])) {
	 					data.push({
	 						id : items[i],
	 						text : decodeURIComponent(items[i].substr(1))
	 					});
	 				} else {
	 					existing.push(parseInt(items[i]));
	 					data.push({
	 						id : parseInt(items[i]),
	 						text : 'load me...'
	 					})
	 				}
	 			}
	 		}

	 		$.ajax({
	 			url : rootUrl	+ '/tags/getByIds/' + existing.join('/'),
	 			dataType: 'json',
	 			success : function(response) {
	 				for( var i=0; i < data.length; i++) {
	 					index = $.inArray(data[i].id, existing);
	 					if( index != -1) {
	 						for( var n=0; n < response.items.length; n++ ){
	 							if( response.items[n].id == data[i].id ) {
	 								data[i].text = response.items[n].name;
	 								break;
	 							}
	 						}
	 					}
	 				}
	 				callback(data);
	 			},
	 			error : function() {
	 				var newData = [];
	 				for( var i=0; i < data.length; i++) {
	 					index = $.inArray(data[i].id, existing);
	 					if( index == -1) {
	 						newData.push(data[i]);
	 					}
	 				}
	 				if( console ) {
	 					console.error('Trouble loading existing tags');
	 				}
	 				callback(newData);
	 			}
	 		});


	 		
	 	},	
	 	tags: [],
	 	tokenSeparators: [','],
	 	ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
        url: rootUrl + "/tags/editorSearch",
        dataType: 'json',
        data: function (term, page) {
            return {
                q: term
            };
        },
        results: function (data, page) { 
        // parse the results into the format expected by Select2.
            // since we are using custom formatting functions we do not need to alter remote JSON data
            return {results: data};
        }
    }
	 }).on('select2-focus', activateHelpbox);
}

});

function startTimer() {
	endTimer();
	lookAtMeTimer = setTimeout(function() {
		$.getJSON(lookAtMeUrl, { 'c' : (new Date()).getTime() }, onSeen);
	}, 30000);
}


function startMomentJsTimer() {
	$('date.js-relative').each(function() {
		$(this).text(moment($(this).attr('datetime')).from()) ;
	});
	setTimeout(startMomentJsTimer, 30000);
}

function endTimer() {
	if(lookAtMeTimer) {
		clearInterval(lookAtMeTimer);
	}
}

function onSeen() {
	startTimer();
}


function activateHelpbox(e) {
	if( $(this).attr('data-helpbox') ) {
		var newHelpbox = $('#' + $(this).attr('data-helpbox')),
			visibleHelpbox = $('.helpbox:visible');

		if( newHelpbox.length == 1) {
			if( visibleHelpbox.attr('id') == newHelpbox.attr('id')) {
					return;
			}
			$('.helpbox:visible').fadeOut('fast')
				.promise()
	      .done(function(a) {
						newHelpbox.fadeIn('fast');
				});
		}
		
		
	}
}




// moment.js locale configuration
// locale : swedish (sv)
// author : Jens Alm : https://github.com/ulmus
(function (factory) {
    factory(moment);
}(function (moment) {
    return moment.defineLocale('sv', {
        months : 'januari_februari_mars_april_maj_juni_juli_augusti_september_oktober_november_december'.split('_'),
        monthsShort : 'jan_feb_mar_apr_maj_jun_jul_aug_sep_okt_nov_dec'.split('_'),
        weekdays : 'söndag_måndag_tisdag_onsdag_torsdag_fredag_lördag'.split('_'),
        weekdaysShort : 'sön_mån_tis_ons_tor_fre_lör'.split('_'),
        weekdaysMin : 'sö_må_ti_on_to_fr_lö'.split('_'),
        longDateFormat : {
            LT : 'HH:mm',
            L : 'YYYY-MM-DD',
            LL : 'D MMMM YYYY',
            LLL : 'D MMMM YYYY LT',
            LLLL : 'dddd D MMMM YYYY LT'
        },
        calendar : {
            sameDay: '[Idag] LT',
            nextDay: '[Imorgon] LT',
            lastDay: '[Igår] LT',
            nextWeek: 'dddd LT',
            lastWeek: '[Förra] dddd[en] LT',
            sameElse: 'L'
        },
        relativeTime : {
            future : 'om %s',
            past : 'för %s sedan',
            s : 'några sekunder',
            m : 'en minut',
            mm : '%d minuter',
            h : 'en timme',
            hh : '%d timmar',
            d : 'en dag',
            dd : '%d dagar',
            M : 'en månad',
            MM : '%d månader',
            y : 'ett år',
            yy : '%d år'
        },
        ordinal : function (number) {
            var b = number % 10,
                output = (~~(number % 100 / 10) === 1) ? 'e' :
                (b === 1) ? 'a' :
                (b === 2) ? 'a' :
                (b === 3) ? 'e' : 'e';
            return number + output;
        },
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });
}));