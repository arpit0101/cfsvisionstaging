;(function( $, window, undefined ){

	$.fn.selectizing = function( options ){

		// default options
        var settings = $.extend({
            color: "#FFF",
            background: "#777",
            title: '',
            valueHidden: '',
			change: function(){}, // event change
        }, options );

		return this.each(function() {

			var selectParent = this,
				$selectParent = $(this);
				$selectParent.hide();

			var listItems = [],
				initialWidth,
				firstTitle = settings.title;

			// preparing array with select options
			if( $selectParent.children('option').length == 0 )  return;
			$selectParent.children('option').each(function(){
				var listItem = $(this),
					title = listItem.text(),
					selected = listItem.is(':selected') ? 1 : 0;
					if(selected && firstTitle == '') firstTitle = title;

				listItems.push({
					value: listItem.attr('value'),
					text: title,
					selected: selected,
				})

			});

			// create a DOM Selectizing element
			var domSelectizing = document.createElement('ul');
				domSelectizing.classList.add('dom-selectizing');

			var domSelectizingClone = document.createElement('ul');
				domSelectizingClone.classList.add('dom-selectizing-clone');

			var liFirst = document.createElement('li');

			var spanArrow = document.createElement('span');
				spanArrow.classList.add('arrow');
				spanArrow.classList.add('ion-chevron-right');
				spanArrow.style.backgroundColor = settings.background;
				spanArrow.style.color = settings.color;

			var spanTitle = document.createElement('span');
				spanTitle.classList.add('title');
				spanTitle.style.backgroundColor = settings.background;
				spanTitle.style.color = settings.color;
				spanTitle.innerHTML = firstTitle;

			var spanSearch = document.createElement('span');
				spanSearch.classList.add('search');
				spanSearch.style.backgroundColor = settings.background;
				spanSearch.style.color = settings.color;

			var inputSearch = document.createElement('input');
				inputSearch.type = 'text';
				inputSearch.classList.add('input-search');
				inputSearch.style.color = settings.color;

			var ulChild = document.createElement('ul');

			var numVisibleOptions = 0;

			// create each option elements
			for (var listItem in listItems) {
			    var item = listItems[listItem];

		    	var liOption = document.createElement('li');
		    		liOption.value = item.value;
		    		if(item.selected) liOption.classList.add('selected');
		    		if(item.value == settings.valueHidden) liOption.classList.add('hidden');
					else {numVisibleOptions += 1; $(liOption).attr('data-element', numVisibleOptions);}
		    		liOption.innerHTML = item.text;

		    	// adding liOption elements to ulChild element
		    	ulChild.appendChild(liOption);
			}

			// adding data-elements attribute to ulChild
			$(ulChild).attr('data-elements', numVisibleOptions);

			// adding elements into their parent elements
			spanSearch.appendChild(inputSearch);

			liFirst.appendChild(spanArrow);
			liFirst.appendChild(spanTitle);

			// creating clone domSelectizing for responsive purpose
			var clonedLiFirst = liFirst.cloneNode(true);
			domSelectizingClone.appendChild(clonedLiFirst);

			liFirst.appendChild(spanSearch);
			liFirst.appendChild(ulChild);
			domSelectizing.appendChild(liFirst);

    		// add DOM Selectizing to DOM fust after his own select
    		$selectParent.before(domSelectizingClone);
    		$selectParent.after(domSelectizing);

    		// adding some css to DOM Selectizing box
    		initialWidth = domSelectizing.clientWidth + 'px';
			// spanTitle.style.width = initialWidth;
			// spanSearch.style.width = initialWidth;
			// $(domSelectizingClone).find('.title').css('width', initialWidth);

			// preparing some variables
			var $DomSelectizing = $(domSelectizing),
				$DomSelectizingClone = $(domSelectizingClone),
				$spanTitle = $DomSelectizing.find('.title'),
		  		$inputFind = $DomSelectizing.find('.input-search'),
		  		$listOptions = $DomSelectizing.find('li > ul > li'),
		  		$firstSelected = $DomSelectizing.find('li > ul > li.selected');

		  	$DomSelectizingClone.click(function(e) {
		  		$(this).next().next().click();
		  	});

			// handle active select
			$DomSelectizing.click(function(e) {
			  	e.stopPropagation();

			  	var isOpened = $(this).hasClass('open') ? true : false;

			  	$(this).removeClass('search-active');
			  	$inputFind.val('');
			  	$firstSelected = $(this).find('.selected');

			  	// closing other DOM Selectizing elements openeds
			  	var allDomSelectizing = document.querySelectorAll('.dom-selectizing');
			  	for(var selectizing in allDomSelectizing) {
			  		var ul = allDomSelectizing[selectizing];
			  		if(ul.tagName == 'UL') ul.classList.remove('open');
			  	}

			  	if( isOpened ) {
			  		$(this).removeClass('open');
			  	} else {
			  		$(this).addClass('open');
			  		$inputFind.focus();
			  	}

			  	$listOptions.show();
			}) // end click function

			// handle choose option
			$listOptions.click(function(e){
				var value = $(this).val(),
					title = $(this).text();

				if( $(this).hasClass('selected') ){
					//
				} else {
					$listOptions.removeClass('selected');
					$(this).addClass('selected');
				}

				$DomSelectizing.prev().prev().find('.title').text(title);
				$spanTitle.text(title);
				$selectParent.val(value);
				settings.change.call(selectParent); // call event change
			}) // end click function

			// handle search
			$inputFind.keyup(function(e) {
				var value = $(this).val(),
					keyPressed = e.keyCode,
					$ulOptionsList = $(this).parent().next(),
					$optionSelected = $ulOptionsList.find('.selected'),
					$nextOption = $optionSelected.nextAll(':visible:first'),
					$prevOption = $optionSelected.prevAll(':visible:first');

				if(keyPressed == 40) { // 40 -> arrow down
					$nextOption = $nextOption.is('li') ? $nextOption : $ulOptionsList.find(':visible:first');

					$ulOptionsList.children().removeClass('selected');
					$nextOption.addClass('selected');
				}

				if(keyPressed == 38) { // 38 -> arrow up
					$prevOption = $prevOption.is('li') ? $prevOption : $ulOptionsList.find(':visible:last');

					$ulOptionsList.children().removeClass('selected');
					$prevOption.addClass('selected');
				}

				if(keyPressed == 27) { //  8 -> escape
					$DomSelectizing.removeClass('open search-active');
					$ulOptionsList.children().removeClass('selected');
					$firstSelected.addClass('selected');
					$(this).val('');
				}

				if(keyPressed == 13) { //  13 -> enter
					var optionSelected = $(this).parent().next().find('li.selected');
					optionSelected.click();
					return true;
				}

				if(keyPressed == 8) { //  8 -> backspace
					$ulOptionsList.children().removeClass('selected');
					$ulOptionsList.find(':visible:first').addClass('selected');
				}

				var usedKeyCodes = new Set([13, 27, 38, 40]);
				if( !usedKeyCodes.has(keyPressed) ) { // not enter, not arrow down, not arrow up
				    if(value) {
						$DomSelectizing.addClass('search-active');
						$listOptions.each(function() {
							if( $(this).text().search(new RegExp(value, "i")) < 0 ) { // not is in list
								$(this).fadeOut(0, function(){
									if($(this).hasClass('selected')) {
										$(this).removeClass('selected');
										$ulOptionsList.find(':visible:first').addClass('selected');
									}
							    });
							} else { // is in list
								$(this).fadeIn();
							}
						});
					} else {
						$DomSelectizing.removeClass('search-active');
						$listOptions.fadeIn();
						$ulOptionsList.children().removeClass('selected');
						$ulOptionsList.find(':visible:first').addClass('selected');
					}
				}
			}) // end keyup function

		});

	}

})(jQuery, window)