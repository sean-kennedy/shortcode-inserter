(function() {
	
	if (shortcodeInserter.tinyMceShortcodes) {

		tinymce.PluginManager.add('shortcode_inserter_button', function(editor, url) {
			
			var shortcodes = [];
				
			shortcodeInserter.tinyMceShortcodes.forEach(function(shortcode) {
				
				var newShortcode = {};
				
				newShortcode.text = shortcode.text;
				
				newShortcode.onClick = function() {
					editor.insertContent(shortcode.content);
				}
				
				shortcodes.push(newShortcode);
				
			});
			
			editor.addButton('shortcode_inserter_button', {
				title: 'Shortcodes',
				type:  'menubutton',
				icon:  'icon shortcode-inserter-icon',
				image: shortcodeInserter.pluginUrl + 'admin/images/tinymce-button.png',
				menu: shortcodes
			});
			
		});
	
	}
	
})();
