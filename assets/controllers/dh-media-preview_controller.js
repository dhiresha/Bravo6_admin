import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
	static targets = [ "mediaPreviewImg", "errorMediaPreview"];

	connect() {
		console.log("Connected to Stimulus Media Controller")
	}

	onImageLoad(event) {
        console.log('Image loaded:', event.target);
        // Your image load logic here
		this.element.classList.remove("is-loading");
    }

	onImageError(event){
		console.log("Image Loading Error", event.target);;

		this.element.classList.remove("is-loading");
		this.element.classList.add('is-error');

		this.element.classList.add(...["border", "border-danger", "border-2", "text-danger"])
		this.errorMediaPreviewTarget.classList.remove("hide-error-msg");
	}
}