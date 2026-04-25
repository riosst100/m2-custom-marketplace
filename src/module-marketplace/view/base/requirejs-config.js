var config = {
    'config': {
        'mixins': {
            'Magento_Ui/js/grid/filters/filters': {
                'TCGCollective_MarketPlace/js/grid/filters/filters-hook': true
            }
        }
    },
    map: {
        '*': {
            platformDatabaseUtils: 'TCGCollective_MarketPlace/js/utils/platform-database-utils'
        }
    },
    paths: {
        "datatables": "TCGCollective_MarketPlace/js/lib/dataTables.min",
        "select2": "TCGCollective_MarketPlace/js/lib/select2.min"
    },
    shim: {
        "select2": {
            deps: ["jquery"]
        },
        "datatables": {
            deps: ['jquery']
        }
    }
};