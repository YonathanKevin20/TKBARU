var productVue = new Vue ({
    el: '#productVue',
    data: {
        productList: [],
        statusDDL: [],
        prodTypeDDL: [],
        unitDDL: [],
        mode: '',
        product: { },
        search_product_query: '',
        active_page: 0
    },
    mounted: function () {
        this.mode = 'list';
        this.getAllProduct();
        this.getLookupStatus();
        this.getProductType();
        this.getUnit();
    },
    methods: {
        validateBeforeSubmit: function() {
            this.$validator.validateAll().then(isValid => {
                if (!isValid) return;
                this.loadingPanel('#productCRUDBlock', 'TOGGLE');
                if (this.mode == 'create') {
                    axios.post(route('api.post.product.save').url(),
                        new FormData($('#productForm')[0]),
                        { headers: { 'content-type': 'multipart/form-data' } }).then(response => {
                        this.backToList();
                        this.loadingPanel('#productCRUDBlock', 'TOGGLE');
                    }).catch(e => {
                        this.handleErrors(e);
                        this.loadingPanel('#productCRUDBlock', 'TOGGLE');
                    });
                } else if (this.mode == 'edit') {
                    axios.post(route('api.post.product.edit', this.product.hId).url(),
                        new FormData($('#productForm')[0]),
                        { headers: { 'content-type': 'multipart/form-data' } }).then(response => {
                        this.backToList();
                        this.loadingPanel('#productCRUDBlock', 'TOGGLE');
                    }).catch(e => {
                        this.handleErrors(e);
                        this.loadingPanel('#productCRUDBlock', 'TOGGLE');
                    });
                } else { }
            });
        },
        getAllProduct: function(page) {
            this.loadingPanel('#productListBlock', 'TOGGLE');

            var qS = [];
            if (this.search_product_query) { qS.push({ 'key':'p', 'value':this.search_product_query }); }
            if (page && typeof(page) == 'number') {
                this.active_page = page;
                qS.push({ 'key':'page', 'value':page });
            }

            axios.get(route('api.get.product.read').url() + this.generateQueryStrings(qS)).then(response => {
                this.productList = response.data;
                this.loadingPanel('#productListBlock', 'TOGGLE');
            }).catch(e => {
                this.handleErrors(e);
                this.loadingPanel('#productListBlock', 'TOGGLE');
            });
        },
        createNew: function() {
            this.mode = 'create';
            this.errors.clear();
            this.product = this.emptyProduct();
        },
        editSelected: function(idx) {
            this.mode = 'edit';
            this.errors.clear();
            this.product = this.productList.data[idx];

            for (var i = 0; i < this.product.product_units; i++) {
                if (this.product.product_units[i].is_base) {
                    this.product.product_units[i].is_base_val = 1;
                } else {
                    this.product.product_units[i].is_base_val = 0;
                }
            }
        },
        showSelected: function(idx) {
            this.mode = 'show';
            this.errors.clear();
            this.product = this.productList.data[idx];
        },
        deleteSelected: function(idx) {
            axios.post(route('api.post.product.delete', idx).url()).then(response => {
                this.backToList();
            }).catch(e => { this.handleErrors(e); });
        },
        backToList: function() {
            this.mode = 'list';
            this.errors.clear();

            if (this.active_page != 0 || this.active_page != 1) {
                this.getAllProduct(this.active_page);
            } else {
                this.getAllProduct();
            }
        },
        emptyProduct: function() {
            return {
                hId: '',
                productTypeHId: '',
                name: '',
                symbol: '',
                status: '',
                remarks: '',
                product_categories: [],
                product_units: []
            }
        },
        addCategory: function() {
            this.product.product_categories.push({
                'code':'',
                'name':'',
                'description':'',
                'level':0
            });
        },
        removeCategory: function(idx) {
            this.product.product_categories.splice(idx, 1);
        },
        addNewProductUnit: function () {
            this.product.product_units.push({
                'unitHId': '',
                'is_base': false,
                'is_base_val': 0,
                'display': false,
                'display_val': 0,
                'conversion_value': '',
                'remarks': ''
            });
        },
        removeProductUnit: function (idx) {
            this.product.product_units.splice(idx, 1);
        },
        changeIsBase: function (idx) {
            if (this.product.product_units[idx].is_base) {
                this.product.product_units[idx].is_base_val = 1;
                this.product.product_units[idx].conversion_value = '1';
                for (var i = 0; i < this.product.product_units.length; i++) {
                    if (i == idx) continue;
                    this.product.product_units[i].is_base = !this.product.product_units[idx].is_base;
                    this.product.product_units[i].is_base_val = 0;
                }
            }
        },
        changeDisplay: function (idx) {
            if (this.product.product_units[idx].display) {
                this.product.product_units[idx].display_val = 1;
                for (var i = 0; i < this.product.product_units.length; i++) {
                    if (i == idx) continue;
                    this.product.product_units[i].display = !this.product.product_units[idx].display;
                }
            }
        },
        getLookupStatus: function() {
            axios.get(route('api.get.lookup.bycategory', 'STATUS').url()).then(
                response => { this.statusDDL = response.data; }
            );
        },
        getProductType: function() {
            axios.get(route('api.get.product.product_type.read').url()).then(
                response => { this.prodTypeDDL = response.data; }
            );
        },
        getUnit: function() {
            axios.get(route('api.get.settings.unit.read').url()).then(
                response => { this.unitDDL = response.data; }
            );
        }
    },
    watch: {
        mode: function() {
            switch (this.mode) {
                case 'create':
                case 'edit':
                case 'show':
                    this.contentPanel('#productListBlock', 'CLOSE')
                    this.contentPanel('#productCRUDBlock', 'OPEN')
                    break;
                case 'list':
                default:
                    this.contentPanel('#productListBlock', 'OPEN')
                    this.contentPanel('#productCRUDBlock', 'CLOSE')
                    break;
            }
        }
    },
    computed: {
        defaultPleaseSelect: function() {
            return '';
        },
        generatedImageUrl: function(image_filename) {
            return this.getCurrentUrl + '/' + image_filename;
        }
    }
});
