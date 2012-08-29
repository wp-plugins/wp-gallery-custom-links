jQuery(document).ready(function () {
	jQuery('a.no-lightbox').unbind('click');
	jQuery('a.no-lightbox').off('click');
	jQuery('a.no-lightbox').click(wp_gallery_custom_links_click);
	jQuery('a.set-target').unbind('click');
	jQuery('a.set-target').off('click');
	jQuery('a.set-target').click(wp_gallery_custom_links_click);
});
function wp_gallery_custom_links_click() {
	if(!this.target || this.target == '')
		window.location = this.href;
	else
		window.open(this.href,this.target);
	return false;
}
