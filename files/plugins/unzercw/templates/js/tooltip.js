$(window).load(
	function(){
		if (typeof window['isCwToolTipActive'] === 'undefined') {
			window['isCwToolTipActive'] = true;
			contentTabs.on('tabchange', function(event) {
				event.activeTab.getUpdater().on('update', function() {
					this.el.select('tooltip', true).each(function (element) {
						var content = new String(this.dom.innerHTML);
						content = content.replace('"', '&quot;');
						this.update("<img src=\"../xtAdmin/images/icons/information.png\" title=\"" + content + "\" />", true);
					});
				});
			});
		}
	}
)


