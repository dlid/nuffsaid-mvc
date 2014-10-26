var lookAtMeTimer = null;

$(function() {
	if(typeof lookAtMeUrl != "undefined") {	
		startTimer();
	}

		myMarkdownSettings = {
		    nameSpace:          'markdown', // Useful to prevent multi-instances CSS conflict
		    previewParserPath:  '~/sets/markdown/preview.php',
		    onShiftEnter:       {keepDefault:false, openWith:'\n\n'},
		    markupSet: [
		        {name:'First Level Heading', key:"1", placeHolder:'Your title here...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '=') } },
		        {name:'Second Level Heading', key:"2", placeHolder:'Your title here...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '-') } },
		        {name:'Heading 3', key:"3", openWith:'### ', placeHolder:'Your title here...' },
		        {name:'Heading 4', key:"4", openWith:'#### ', placeHolder:'Your title here...' },
		        {name:'Heading 5', key:"5", openWith:'##### ', placeHolder:'Your title here...' },
		        {name:'Heading 6', key:"6", openWith:'###### ', placeHolder:'Your title here...' },
		        {separator:'---------------' },        
		        {name:'Bold', key:"B", openWith:'**', closeWith:'**'},
		        {name:'Italic', key:"I", openWith:'_', closeWith:'_'},
		        {separator:'---------------' },
		        {name:'Bulleted List', openWith:'- ' },
		        {name:'Numeric List', openWith:function(markItUp) {
		            return markItUp.line+'. ';
		        }},
		        {separator:'---------------' },
		        {name:'Picture', key:"P", replaceWith:'![[![Alternative text]!]]([![Url:!:http://]!] "[![Title]!]")'},
		        {name:'Link', key:"L", openWith:'[', closeWith:']([![Url:!:http://]!] "[![Title]!]")', placeHolder:'Your text to link here...' },
		        {separator:'---------------'},    
		        {name:'Quotes', openWith:'> '},
		        {name:'Code Block / Code', openWith:'(!(\t|!|`)!)', closeWith:'(!(`)!)'},
		        {separator:'---------------'},
		        {name:'Förhandsgrandskning', call:'preview', className:"preview"}
		    ]
		}

	$(document).ready(function() {
		$(".markdown").markItUp(myMarkdownSettings);
	});

	 $("#e1").select2({
	 	width: '100%',
	 	createSearchChoice : function(term, data) {
	 		return {
	 			id : term,
	 			text: term
	 		};
	 	},
	 	minimumInputLength: 2,
	 	tags: [],
	 	tokenSeparators: [','],
	 	ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
        url: rootUrl + "/questions/search",
        dataType: 'json',
        data: function (term, page) {
            return {
                q: term, // search term
                a : 3
            };
        },
        results: function (data, page) { 
        	console.warn(data);
        // parse the results into the format expected by Select2.
            // since we are using custom formatting functions we do not need to alter remote JSON data
            return {results: data};
        }
    }
	 });

});

function startTimer() {
	endTimer();
	lookAtMeTimer = setTimeout(function() {
		$.getJSON(lookAtMeUrl, { 'c' : (new Date()).getTime() }, onSeen);
	}, 30000);
}

function endTimer() {
	if(lookAtMeTimer) {
		clearInterval(lookAtMeTimer);
	}
}

function onSeen() {
	startTimer();
}