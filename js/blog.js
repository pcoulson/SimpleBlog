var el = document.getElementById("deleteButton");

el.addEventListener('click', function(e){				// Listen for a click on the delete button
	if (!confirm('Are you sure you want to delete this blog?')) {	// Make sure you want to delete it
		e.preventDefault();					// do not submit the form
	}
	return;								// Carry on and delete the blog (submit the form)
}, false);