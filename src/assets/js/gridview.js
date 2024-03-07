/**
 * Copyright 2024. Néstor Acevedo
 */
// (function () {
var gridViewObject = {
    init: function (filters, url) {
        this.filters = document.querySelectorAll(filters);
        this.url = new URL(url.replace(/&amp;/g, "&"));
        this._query = this.url.search;
        this._anchor = this.url.hash ?? '';
        this._params = new URLSearchParams(this._query);
    },
    apply: function () {
        let self = this;

        this.filters.forEach((item) => {
            if (item.tagName === 'INPUT') {
                item.value = self._params.get(item.id) ?? '';
                item.addEventListener('keypress', function (event) {
                    if (event.keyCode !== 13) {
                        return; // Solo la tecla enter
                    } else {
                        self.url.searchParams.delete(this.id);
                        self.url.searchParams.append(this.id, this.value);
                        document.location.assign(self.url + self._anchor);
                    }
                });
            } else if (item.tagName === 'SELECT') {
                item.addEventListener('change', function (event) {
                    self.url.searchParams.delete(this.id);
                    self.url.searchParams.append(this.id, this.value);
                    document.location.assign(self.url + self._anchor);
                });
            }
        });
    },
    debug: function () {
        console.log(this);
    }
};

// })();
var gridView = Object.create(gridViewObject);
