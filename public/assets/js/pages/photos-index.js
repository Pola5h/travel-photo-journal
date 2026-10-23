function photosIndex() {
    return {
        photos: [],
        meta: null,
        loading: false,
        error: '',
        success: '',
        async load(page) {
            this.loading = true;
            this.error = '';

            try {
                const response = await Api.get('/photos?page=' + (page || 1));
                this.photos = response.data.items;
                this.meta = response.data.meta;
            } catch (e) {
                this.error = firstError(e);
            } finally {
                this.loading = false;
            }
        },
        async remove(id) {
            if (!window.confirm('Delete this photo?')) {
                return;
            }

            this.error = '';
            this.success = '';

            try {
                await Api.delete('/photos/' + id);
                this.success = 'Photo deleted.';

                const currentPage = this.meta ? this.meta.current_page : 1;
                const page = this.photos.length === 1 && currentPage > 1 ? currentPage - 1 : currentPage;

                await this.load(page);
            } catch (e) {
                this.error = firstError(e);
            }
        },
    };
}
