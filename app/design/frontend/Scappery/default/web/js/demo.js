function getBackgroundColor() {
	var colorThief = new BackgroundColorTheif();
	var rgb = colorThief.getBackGroundColor(document.getElementById("backgroundImage"));
	console.log('background-color = '+rgb);
	//document.getElementById("backGroundColor").style.backgroundColor = 'rgb(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] +')';
}


function fontColorChange() {  // deprecated. See below.

	/* var colorThief = new BackgroundColorTheif();
	var rgb = colorThief.getBackGroundColor(document.getElementById("backgroundImage"));
	console.log('background-color = '+rgb);
	//document.getElementById("backGroundColor").style.backgroundColor = 'rgb(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] +')';
	
	color = 'rgb(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] +')';
	percent = 70;
    var num = parseInt(color,16),
    amt = Math.round(2.55 * percent),
    R = (num >> 16) + amt,
    G = (num >> 8 & 0x00FF) + amt,
    B = (num & 0x0000FF) + amt;
	
    document.getElementById("storeview").style.color = '#'+(0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (G<255?G<1?0:G:255)*0x100 + (B<255?B<1?0:B:255)).toString(16).slice(1); */
}


if (window.FileReader) {
	var drop;
	addEventHandler(
			window,
			'load',
			function() {
				drop = document.getElementById('drop');
				
				function cancel(e) {
					if (e.preventDefault) {
						e.preventDefault();
					}
					return false;
				}

				// Tells the browser that we *can* drop on this target
				addEventHandler(drop, 'dragover', cancel);
				addEventHandler(drop, 'dragenter', cancel);

				addEventHandler(
						drop,
						'drop',
						function(e) {
							e = e || window.event; // get window.event if e argument missing (in IE)   
							if (e.preventDefault) {
								e.preventDefault();
							} // stops the browser from redirecting off to the image.

							var dt = e.dataTransfer;
							var files = dt.files;
							for (var i = 0; i < files.length; i++) {
								var file = files[i];
								var reader = new FileReader();

								//attach event handlers here...

								reader.readAsDataURL(file);
								addEventHandler(
										reader,
										'loadend',
										function(e, file) {
											var bin = this.result;
											var img = document.getElementById("backgroundImage");
											img.file = file;
											img.src = bin;
										}.bindToEventHandler(file));
							}
							return false;
						});
				Function.prototype.bindToEventHandler = function bindToEventHandler() {
					var handler = this;
					var boundParameters = Array.prototype.slice.call(arguments);
					//create closure
					return function(e) {
						e = e || window.event; // get window.event if e argument missing (in IE)   
						boundParameters.unshift(e);
						handler.apply(this, boundParameters);
					};
				};
			});
} else {
	alert("This browser doesn't support file reader. Please use HTML5 supported browser");
}
function addEventHandler(obj, evt, handler) {
	if(obj !=null){		
		if (obj.addEventListener) {
			// W3C method
			obj.addEventListener(evt, handler, false);
		} else if (obj.attachEvent) {
			// IE method.
			obj.attachEvent('on' + evt, handler);
		} else {
			// Old school method.
			obj['on' + evt] = handler;
		}
	}
}
