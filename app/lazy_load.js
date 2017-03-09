angular.module('app')
    .config(['$ocLazyLoadProvider', function($ocLazyLoadProvider) {
        $ocLazyLoadProvider.config({
            debug: true,
            events: true,
            modules: [{
                    name: 'sparkline',
                    files: [
                        'assets/plugins/jquery-sparkline/jquery.sparkline.min.js',
                    'assets/plugins/angular-sparkline/angular-sparkline.js'
                    ]
                }
                ]
                });
			});
