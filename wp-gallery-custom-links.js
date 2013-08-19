jQuery(document).ready(function () {
	jQuery('a.no-lightbox, a.no-lightbox img').unbind('click');
	jQuery('a.no-lightbox').click(wp_gallery_custom_links_click);
	jQuery('a.set-target').unbind('click');
	jQuery('a.set-target').click(wp_gallery_custom_links_click);
});
function wp_gallery_custom_links_click() {
	if(!this.target || this.target == '' || this.target == '_self')
		window.location = this.href;
	else
		window.open(this.href,this.target);
	return false;
}
