/**
 * Basic sample plugin inserting abbreviation elements into CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */

// Register the plugin within the editor.
CKEDITOR.plugins.add( 'translatebutton', {

	// Register the icons.
	icons: 'translatebutton',

	// The plugin initialization logic goes inside this method.
	init: function( editor ) {

		// Define an editor command that opens our dialog.
		editor.addCommand( 'translatebutton', new CKEDITOR.dialogCommand( 'translateContent' ) );

		// Create a toolbar button that executes the above command.
		editor.ui.addButton( 'Translate', {

			// The text part of the button (if available) and tooptip.
			label: 'Translate Content',

			// The command to execute on click.
			command: 'translatebutton',

			// The button placement in the toolbar (toolbar group name).
			toolbar: 'insert'
		});

		// Register our dialog file. this.path is the plugin folder path.
		CKEDITOR.dialog.add( 'translateContent', this.path + 'dialogs/translatebutton.js' );
	}
});

