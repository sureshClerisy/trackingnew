/* ============================================================
 * File: config.lazyload.js
 * Configure modules for ocLazyLoader. These are grouped by 
 * vendor libraries. 
 * ============================================================ */

angular.module('app')
    .config(['$ocLazyLoadProvider', function($ocLazyLoadProvider) {
        $ocLazyLoadProvider.config({
            debug: true,
            events: true,
            modules: [{
                    name: 'isotope',
                    files: [
                        'assets/plugins/imagesloaded/imagesloaded.pkgd.min.js',
                        'assets/plugins/jquery-isotope/isotope.pkgd.min.js'
                    ]
                }, {
                    name: 'wysihtml5',
                    files: [
                        'assets/plugins/bootstrap3-wysihtml5/bootstrap3-wysihtml5.min.css',
                        'assets/plugins/bootstrap3-wysihtml5/bootstrap3-wysihtml5.all.min.js'
                    ]
                }, {
                    name: 'jquery-ui',
                    files: ['assets/plugins/jquery-ui-touch/jquery.ui.touch-punch.min.js']
                }, {
                    name: 'line-icons',
                    files: ['assets/plugins/simple-line-icons/simple-line-icons.css']
                }, {
                    //https://github.com/angular-ui/ui-select
                    name: 'select',
                    files: [
                        'assets/plugins/bootstrap-select2/select2.css',
                        'assets/plugins/angular-ui-select/select.min.css',
                        'assets/plugins/angular-ui-select/select.min.js'
                    ]
                }, {
                    name: 'datepicker',
                    files: [
                        'assets/plugins/bootstrap-datepicker/css/datepicker3.css',
                        'assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
                    ]
                }, {
                    name: 'daterangepicker',
                    files: [
                        /*'assets/plugins/moment/moment.min.js',*/
                        'assets/plugins/bootstrap-daterangepicker/daterangepicker.min.css',
                        /*'assets/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css',*/
                        'assets/plugins/bootstrap-daterangepicker/daterangepicker.js',
                        'assets/plugins/angular-daterangepicker/angular-daterangepicker.js'
                    ],
                    serie: true
                }, {
                    name: 'timepicker',
                    files: [
                        'assets/plugins/bootstrap-timepicker/bootstrap-timepicker.min.css',
                        'assets/plugins/bootstrap-timepicker/bootstrap-timepicker.js'
                    ]
                }, {
                    name: 'inputMask',
                    files: [
                        'assets/plugins/jquery-inputmask/jquery.inputmask.min.js'
                    ]
                }, {
                    name: 'telephonefilter',
                    files: [
                        'assets/js/directives/angular-telephone-filter.min.js'
                    ]
                }, {
                    name: 'autonumeric',
                    files: [
                        'assets/plugins/jquery-autonumeric/autoNumeric.js'
                    ]
                }, {
                    name: 'dataTables',
                    files: [
                        'assets/plugins/jquery-datatable/media/css/dataTables.bootstrap.min.css',
                        'assets/plugins/jquery-datatable/extensions/FixedColumns/css/dataTables.fixedColumns.min.css',
                        'assets/plugins/datatables-responsive/css/datatables.responsive.css',
                        'assets/plugins/jquery-datatable/media/js/jquery.dataTables.min.js',
                        'assets/plugins/jquery-datatable/extensions/TableTools/js/dataTables.tableTools.min.js',
                        'assets/plugins/jquery-datatable/media/js/dataTables.bootstrap.js',
                        'assets/plugins/jquery-datatable/extensions/Bootstrap/jquery-datatable-bootstrap.js',
                        'assets/plugins/datatables-responsive/js/datatables.responsive.js',
                        'assets/plugins/datatables-responsive/js/lodash.min.js'
                    ],
                    serie: true // load in the exact order
                }, {
                    name: 'google-map',
                    files: [
                        'assets/plugins/angular-google-map-loader/google-map-loader.js',
                        'assets/plugins/angular-google-map-loader/google-maps.js'
                    ]
                }, {
                    name: 'typehead',
                    files: [
                        'assets/plugins/bootstrap-typehead/typeahead.bundle.min.js',
                        'assets/plugins/bootstrap-typehead/typeahead.jquery.min.js',
                        'assets/plugins/bootstrap-typehead/bloodhound.min.js',
                        'assets/plugins/angular-typehead/angular-typeahead.min.js'
                    ]
                },{
                    name: 'metrojs',
                    files: [
                        'assets/plugins/jquery-metrojs/MetroJs.min.js',
                        'assets/plugins/jquery-metrojs/MetroJs.css'
                    ]
                }, {
                    name: 'skycons',
                    files: ['assets/plugins/skycons/skycons.js']
                },{
                    name: 'sparkline',
                    files: [
                    'assets/plugins/jquery-sparkline/jquery.sparkline.min.js',
                    'assets/plugins/angular-sparkline/angular-sparkline.js'
                    ]
                },{
                    name: 'markercluster',
                    files: [
                    'assets/js/directives/markerclusterer.js'
                    ]
                },{
                    name: 'signature',
                    files: [
                    'assets/plugins/angular-signature/src/signature_pad.min.js',
                    ]
                },
                {
                    name: 'dropzone',
                    files: [
                        'assets/plugins/dropzone/css/dropzone.css'
                        
                    ],
                    serie: true
                },{
                    name: 'moment',
                    files: ['assets/plugins/moment/moment.min.js',
                        'assets/plugins/moment/moment-with-locales.min.js'
                    ]
                },{
                    name: 'tagsInput',
                    files: [
                        'assets/plugins/bootstrap-tag/bootstrap-tagsinput.css',
                        'assets/plugins/bootstrap-tag/bootstrap-tagsinput.min.js'
                    ]
                },{
                    name: 'switchery',
                    files: [
                        'assets/plugins/switchery/js/switchery.min.js',
                        'assets/plugins/ng-switchery/ng-switchery.js',
                        'assets/plugins/switchery/css/switchery.min.css',
                    ]
                },{
                    name: 'inputMask',
                    files: [
                        'assets/plugins/jquery-inputmask/jquery.inputmask.min.js'
                    ]
                },{
                    name: 'menuclipper',
                    files: [
                        'assets/plugins/jquery-menuclipper/jquery.menuclipper.css',
                        'assets/plugins/jquery-menuclipper/jquery.menuclipper.js'
                    ]
                }

            ]
        });
    }]);
