import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect() {
        this.element.addEventListener('dropzone:connect', this._onConnect);
        this.element.addEventListener('dropzone:change', this._onChange);
        this.element.addEventListener('dropzone:clear', this._onClear);
    }

    disconnect() {
        // You should always remove listeners when the controller is disconnected to avoid side-effects
        this.element.removeEventListener('dropzone:connect', this._onConnect);
        this.element.removeEventListener('dropzone:change', this._onChange);
        this.element.removeEventListener('dropzone:clear', this._onClear);
    }

    _onConnect(event) {
        // The dropzone was just created
    }

    _onChange(event) {
		// The dropzone just changed
		const mainDropzoneContainer = this;
		const mainPreviewContainer = mainDropzoneContainer.querySelector('.dropzone-preview');
		const inputElement = mainDropzoneContainer.querySelector('[id*="_uploadMedia"]');
		const files = inputElement.files;
		// mainPreviewContainer.
		console.log(files);

		// Helper function to create and append an image element
		const createImagePreview = (src) => {
			const imgElement = document.createElement('img');
			imgElement.src = src;
			imgElement.classList.add('dropzone-preview-image-dh');
			mainPreviewContainer.appendChild(imgElement);
		};

		for (const file of files) {
			if (file.type.startsWith('video/')) {
				// It's a video file, let's generate a thumbnail
				const videoElement = document.createElement('video');
				const objectUrl = URL.createObjectURL(file);
				videoElement.src = objectUrl;
	
				// videoElement.addEventListener('loadeddata', () => {
				// 	const canvas = document.createElement('canvas');
				// 	canvas.width = videoElement.videoWidth;
				// 	canvas.height = videoElement.videoHeight;
				// 	canvas.getContext('2d').drawImage(videoElement, 0, 0, canvas.width, canvas.height);
				// 	createImagePreview(canvas.toDataURL()); // Create image from canvas
				// 	URL.revokeObjectURL(objectUrl); // Clean up
				// });
				videoElement.addEventListener('loadedmetadata', () => {
					// Now we know the duration
					videoElement.currentTime = videoElement.duration / 2; // Seek to middle
				});
				
				videoElement.addEventListener('seeked', () => {
					// Video is now at the middle frame
					const canvas = document.createElement('canvas');
					canvas.width = videoElement.videoWidth;
					canvas.height = videoElement.videoHeight;
					canvas.getContext('2d').drawImage(videoElement, 0, 0, canvas.width, canvas.height);
					createImagePreview(canvas.toDataURL()); // Create image from canvas
					URL.revokeObjectURL(objectUrl); // Clean up
				});
	
				videoElement.addEventListener('error', () => {
					console.error('Error loading video file for thumbnail preview.');
					createImagePreview('/path/to/default-thumbnail.png'); // Fallback thumbnail
					URL.revokeObjectURL(objectUrl); // Clean up
				});
			} else if (file.type.startsWith('image/')) {
				// It's an image file, proceed as usual
				const reader = new FileReader();
				reader.onload = (e) => createImagePreview(e.target.result);
				reader.onerror = () => {
					console.error('Error reading image file.');
					createImagePreview('/path/to/default-thumbnail.png'); // Fallback thumbnail
				};
				reader.readAsDataURL(file);
			} else {
				// Not an image or video file
				createImagePreview('/path/to/default-thumbnail.png'); // Fallback thumbnail
			}
        }
    }

    _onClear(event) {
        // The dropzone has just been cleared
		const mainDropzoneContainer = this;
		const mainPreviewContainer = mainDropzoneContainer.querySelector('.dropzone-preview');
		const imagePreviews = mainPreviewContainer.querySelectorAll('.dropzone-preview-image-dh');

		imagePreviews?.forEach(preview => {
			//Remove the previews
			preview.remove(); // This removes the preview element from the DOM
		});


    }
}