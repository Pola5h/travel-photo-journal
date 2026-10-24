function photoForm(config) {
    return {
        mode: config.mode,
        photoId: config.photoId || null,
        name: '',
        location: '',
        description: '',
        summary: null,
        image: null,
        imageUrl: null,
        previewUrl: null,
        isDragging: false,
        loading: false,
        error: '',
        success: '',
        async init() {
            if (this.mode !== 'edit') {
                return;
            }

            this.loading = true;

            try {
                const response = await Api.get('/photos/' + this.photoId);
                const photo = response.data;

                this.name = photo.name;
                this.location = photo.location;
                this.description = photo.description;
                this.summary = photo.summary;
                this.imageUrl = photo.image_url;
            } catch (e) {
                this.error = firstError(e);
            } finally {
                this.loading = false;
            }
        },
        setFile(file) {
            this.image = file;
            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
            }
            if (file) {
                this.previewUrl = URL.createObjectURL(file);
            }
        },
        onFile(event) {
            const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
            this.setFile(file);
        },
        onDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer?.files?.[0] || null;
            if (file) {
                this.setFile(file);
            }
        },
        removeImage() {
            this.setFile(null);
            const input = document.getElementById('image');
            if (input) {
                input.value = '';
            }
        },
        async submit() {
            this.loading = true;
            this.error = '';
            this.success = '';

            try {
                if (this.mode === 'create') {
                    const form = new FormData();
                    form.append('name', this.name);
                    form.append('location', this.location);
                    form.append('description', this.description);

                    if (this.image) {
                        form.append('image', this.image);
                    }

                    const response = await Api.postForm('/photos', form);
                    this.summary = response.data.summary;
                    this.success = 'Photo created.';

                    window.setTimeout(function () {
                        window.location.href = '/photos';
                    }, 400);
                } else {
                    const response = await Api.put('/photos/' + this.photoId, {
                        name: this.name,
                        location: this.location,
                        description: this.description,
                    });

                    this.summary = response.data.summary;
                    this.success = 'Photo updated.';
                }
            } catch (e) {
                this.error = firstError(e);
            } finally {
                this.loading = false;
            }
        },
    };
}
