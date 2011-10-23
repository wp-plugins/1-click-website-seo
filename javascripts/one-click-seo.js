var OneClickWebSEO = {
	// when submit button is clicked for the plugin
	submitClicked: function(event)
	{
		// if keyword is empty
		if ('' == $('one_click_seo_keyword').value)
		{
			// show error
			alert("Keyword should be entered")
			
			// set focus on enter keyword
			$('one_click_seo_keyword').focus()
			
			// stop submitting
			event.stop()
		}
	}
}

// after dom is loaded
document.observe("dom:loaded", function()
{
	// catch when submit button is clicked
	$('one_click_seo_submit').observe('click', OneClickWebSEO.submitClicked)
	
	// create sortable for the One Click SEO panel
	Sortable.create('one_click_seo_sortable', {
		tag: 'p'
	})
})