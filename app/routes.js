//
var app = angular.module('app',['gantt','gantt.table','gantt.tooltips','signature','ngDropzone',"highcharts-ng",
	/*'gantt.sortable',
    'gantt.movable',
    'gantt.drawtask',
    'gantt.tooltips',
    'gantt.tree',
    'gantt.bounds',
    'gantt.progress',
    'gantt.table',
    'gantt.groups',
    'gantt.overlap',
    'gantt.resizeSensor',*/
    "pubnub.angular.service",
	'angularUtils.directives.dirPagination','ngCookies','ui.router','ngStorage','jQueryScrollbar','ui.utils',
    'oc.lazyLoad','datatables','ngSanitize','ui.select','angular-sortable-view','dcbImgFallback']);
    
app.config(['$stateProvider', '$urlRouterProvider','$localStorageProvider', '$ocLazyLoadProvider','$provide','$httpProvider',
    function($stateProvider, $urlRouterProvider, $localStorageProvider,$ocLazyLoadProvider,$provide,$httpProvider) {
        $httpProvider.interceptors.push(['$cookies', function ($cookies) {
            return {
                'request': function (config) {
                    if ( config.headers.typeCheck != undefined && config.headers.typeCheck == 'type') {
                       config.headers = {};
                    } else {
                        config.headers = config.headers || {};
                        config.headers['Content-Type'] = 'application/x-www-form-urlencoded';
                        config.headers.Accept = 'application/json;odata=verbose';
                    }
                    var token = $cookies.get('authToken');
                    if (token) {
                        config.headers.Authorization = 'Bearer ' + token;
                    }
                    return config;
                    
                }
            };
       }]);

		$urlRouterProvider.otherwise('/login');
		$provide.decorator('$document',function($delegate){
            $delegate.referrer = null;
            $delegate.params = {};
            return $delegate; 
        });
        $stateProvider
			.state('items', {
				url: '/items',
                templateUrl: 'assets/templates/items.html',
                controller: 'ItemController'
            })
            .state('logout', {
				url: '/logout',
                title: 'Logout',
                templateUrl: 'assets/templates/login.html',
                controller: 'authLogoutCtrl',
                moduleName: 'login',
                resolve: {
					getLogoutUserData: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/login/logout');
					}
				}
            })
            .state('dashboard', {
				url: '/dashboard',
				title: 'Dashboard',
                templateUrl: 'assets/templates/dashboard.html',
                controller: 'AdminController',
                controllerAs: 'dashboard',
                moduleName: 'dashboard',
                  resolve: {
                  	/*getAllVehicles: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/dashboard/index');
					},*/
                    deps: ['$ocLazyLoad', function($ocLazyLoad) {
                        return $ocLazyLoad.load([
                        		'datepicker',
                                'daterangepicker',
								'select',
                                'skycons',
                                'sparkline',
                                'metrojs',
                                'markercluster',
                                'tabcollapse'
                                ], {
                                insertBefore: '#lazyload_placeholder'
                            })
					}]
                }
            })
            .state('login', {
				url: '/login',
                title: 'Login',
                templateUrl: 'assets/templates/login.html',
                controller: 'authCtrl',
                moduleName: 'login',
                showHeader : false,
                 deps: ['$ocLazyLoad', function($ocLazyLoad) {
                        return $ocLazyLoad.load([
                        		'datepicker',
                                'bubbleAnimation'
                                ], {
                                insertBefore: '#lazyload_placeholder'
                            })
					}]
            })
            .state('trucks', {
				url: '/trucks',
				title: 'Trucks',
				templateUrl: 'assets/templates/vehicles/trucksListing.html',
                controller: 'trucksController',
                moduleName: 'trucks',
                showHeader : true,
                resolve:{            
					getTrucksListing: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/vehicles');
					}
				}
			})
			.state('reports', {
				url: '/reports',
				title: 'Reports',
				templateUrl: 'assets/templates/reports.html',
                controller: 'reportsController',
                moduleName: 'reports',
                showHeader : true,
                resolve:{            
					initialData: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/reports');
					},
					deps: ['$ocLazyLoad', function($ocLazyLoad) {
	                    return $ocLazyLoad.load([
								'select',
								'daterangepicker'
	                            ], {
	                            insertBefore: '#lazyload_placeholder'
	                        })
					}]

				}
			})
            .state('departments', {
				url: '/departments',
				title: 'Departments',
				templateUrl: 'assets/templates/departments/departmentsListing.html',
                controller: 'departmentsController',
            })
            .state('drivers', {
				url: '/drivers',
				title: 'Drivers',
				templateUrl: 'assets/templates/drivers/driversListing.html',
                controller: 'driversController',
                moduleName: 'drivers',
                resolve:{            
					getDriversListing: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/drivers');
					}
				}
            })
            .state('editDrivers', {
				url: '/editDrivers/:id',
				title: 'Edit Driver',
				templateUrl: 'assets/templates/drivers/editDrivers.html',
                controller: 'editDriversController',
                controllerAs: 'editDriver',
                moduleName: 'drivers',
				resolve:{            
					getDriversData: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/drivers/edit/' + $stateParams.id);
					},
					getDispatcherList: function(dataFactory,$stateParams) {
						return dataFactory.httpRequest(URL+'/drivers/dispatcherList/'+$stateParams.id);
					},
					 deps: ['$ocLazyLoad', function($ocLazyLoad) {
                        return $ocLazyLoad.load([
								'inputMask',
                                ], {
                                insertBefore: '#lazyload_placeholder'
                            })
					}]
				}
			})
            .state('search', {
				url: '/search',
				title: 'Search',
				templateUrl: 'assets/templates/truckstop/index.html',
                controller: 'truckstopController',
                moduleName: 'loads',
                resolve:{
					 deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
									'datepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    //~ 'dropzone',
                                    'inputMask'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
					getAllTrucksStopData: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/truckstop/index');
					},
				},
			})
            .state('search.popup', {
				url: '/popup/:staticId/:encodedurl',
				title: 'Search Popup',
				controller: 'truckstopController',
				moduleName: 'loads',
				onEnter: function($stateParams, $state, $rootScope) {
						setTimeout(function(){
							$rootScope.editSaveLoad($stateParams.staticId,$stateParams.encodedurl);
						},800);
					},	
			})
            .state('searchresults', {
				url: '/searchresults?:q',
				title: 'Search Results',
				templateUrl: 'assets/templates/truckstop/srp.html',
                controller: 'truckstopController',
                moduleName: 'loads',
                params: {
					type : true,
				},
                resolve:{
					 deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    //~ 'dropzone',
                                    'inputMask',
                                    'telephonefilter'
                                    //~ 'fixedheader'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
					getAllTrucksStopData: function(dataFactory, $stateParams) {
						if( $stateParams.type == true )
							return dataFactory.httpRequest(URL+'/truckstop/fetchSearchResults?'+$stateParams.q);
						else
							return [];
					},
				},
			})
			.state('testApi', {
				url: '/testApi',
				title: 'test api',
				templateUrl: 'assets/templates/truckstop/testApi.html',
                controller: 'testController',
                moduleName: 'loads',
                resolve:{
					 deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    //~ 'dropzone',
                                    'inputMask'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
					getAllTrucksStopData: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/test/index');
					},
				},
			})
			.state('test', {
				url: '/test?:q',
				title: 'Search tew',
				templateUrl: 'assets/templates/truckstop/test.html',
                controller: 'testController',
                moduleName: 'loads',
                resolve:{
					 deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    //~ 'dropzone',
                                    'inputMask'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
					getAllTrucksStopData: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/test/fetchSearchResults?'+$stateParams.q);
					},
				},
			})
            .state('loads', {
				url: '/loads/:key/?:q',
				title: 'Loads',
				templateUrl: 'assets/templates/loads/loads.html',
                controller: 'loadsController',
                moduleName: 'loads',
                params: {
                	type: true,
                },
                resolve:{
					 deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
                                    'daterangepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    'signature',
                                    //~ 'dropzone',
                                    'inputMask',
                                    'telephonefilter',
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
					getAllLoads: function(dataFactory, $stateParams) {
						var qstr = "";
						if($stateParams.q != undefined){
							 qstr = "/?"+$stateParams.q;
						}
						if ( $stateParams.type == true){
							return dataFactory.httpRequest(URL+'/Loads/index/'+$stateParams.key+qstr);
						}
						else{
							var response = {table_title: "",total: 0,vehicleIdRepeat: "", loadSource:"truckstop.com", assigned_loads: [], filterArgs:[]};
							return response;
						}
					},
				},
			}).state('driversInsights', {
                url: '/driversInsights/:key/?:q',
                title: "Drivers Insights",
                templateUrl: 'assets/templates/loads/driversInsights.html',
                controller: 'driversInsightsController',
                controllerAs: 'dInsights',
                moduleName: 'loads',
                params: {
                    type: true,
                },
                resolve:{
                     deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
                                    'daterangepicker',
                                    'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    'signature',
                                    'inputMask',
                                    'telephonefilter',
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
                    driverListing: function(dataFactory, $stateParams) {
                        var qstr = "";
                        if($stateParams.q != undefined){
                             qstr = "/?"+$stateParams.q;
                        }
                        if ( $stateParams.type == true){
                            return dataFactory.httpRequest(URL+'/Loads/driversInsights/'+$stateParams.key+qstr);
                        }
                        else{
                            var response = {total: 0,drivers: [], filterArgs:[]};
                            return response;
                        }
                    },
                },
            }).state('truckInsights', {
				url: '/truckInsights/:key/?:q',
				title: "Truck Insights",
				templateUrl: 'assets/templates/vehicles/truckInsights.html',
                controller: 'truckInsightsController',
                controllerAs: 'tInsights',
                moduleName: 'loads',
                params: {
                	type: true,
                },
                resolve:{
					 deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
                                    'daterangepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    'signature',
                                    'inputMask',
                                    'telephonefilter',
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
					trucksListing: function(dataFactory, $stateParams) {
						var qstr = "";
						if($stateParams.q != undefined){
							 qstr = "/?"+$stateParams.q;
						}
						if ( $stateParams.type == true){
							return dataFactory.httpRequest(URL+'/Loads/truckInsights/'+$stateParams.key+qstr);
						}
						else{
							var response = {total: 0,drivers: [], filterArgs:[]};
							return response;
						}
					},
				},
			}).state('myLoad', {
				url: '/myLoad',
				title: 'My Loads',
				templateUrl: 'assets/templates/loads/assignedLoad.html',
                controller: 'assignedLoadsController',
                moduleName: 'loads',
                params: {
					type : true,
				},
                resolve:{
					 deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
                                    'daterangepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    'signature',
                                    //~ 'dropzone',
                                    'inputMask',
                                    'telephonefilter'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
                   

					getAllAssignedLoads: function(dataFactory, $stateParams) {
						if ( $stateParams.type == true ) 
							return dataFactory.httpRequest(URL+'/Assignedloads/index');
						else
							return [];
					},
				},
			})
			.state('plan',{
				url: '/plan',
				title: 'Plan',
				templateUrl: 'assets/templates/truckstop/iterationloads.html',
				controller: 'iterationLoadsController',
				moduleName: 'loads',
				params: {
					type: true,
				},
				resolve: {
					deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    //~ 'dropzone',
                                    'inputMask',
                                    'telephonefilter'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
					getIterationLoadData: function(dataFactory, $stateParams) {
						if ( $stateParams.type == true )
							return dataFactory.httpRequest(URL+'/iterationloads/index');
						else
							return [];
					}
				}
			})
		    .state('AutoLoad',{
				url: '/autoGenerate',
				title: 'AutoLoad',
				templateUrl: 'assets/templates/truckstop/autoGenerate.html',
				controller: 'autoLoadsController',
				moduleName: 'loads',
				resolve: {
					deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
					getIterationLoadData: function(dataFactory, $stateParams) {
						 return dataFactory.httpRequest(URL+'/autoloads/index');
					}
				}
			})
            .state('editTruck', {
				url: '/editTruck/:id',
				title: 'Edit Truck',
				templateUrl: 'assets/templates/vehicles/editVehicle.html',
                controller: 'editTruckController',
                moduleName: 'trucks',
                resolve:{
					getTruckData: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/vehicles/edit/' + $stateParams.id);
					}
				}
            })                       
            .state('addTruck', {
				url: '/addTruck',
				title: 'Add Truck',
				templateUrl: 'assets/templates/vehicles/addVehicle.html',
                controller: 'addTruckController',
                moduleName: 'trucks',
                resolve:{
					getTruckData: function(dataFactory) {
						return dataFactory.httpRequest(URL+'/vehicles/states/');
					}
				}
            })
            .state('addDriver', {
				url: '/addDriver',
				title: 'Add Driver',
				templateUrl: 'assets/templates/drivers/addDrivers.html',
                controller: 'addDriversController',
                moduleName: 'drivers',
                resolve:{            
					getDispatcherList: function(dataFactory) {
						return dataFactory.httpRequest(URL+'/drivers/dispatcherList');
					}
				}
            })
            .state('broker', {
				url: '/broker',
				title: 'Brokers',
				templateUrl: 'assets/templates/broker/brokerListing.html',
                controller: 'brokersController',
                moduleName: 'brokers',
                resolve:{            
					getBrokersListing: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/brokers');
					}
				}
            })
            .state('editbroker', {
				url: '/editbroker/:id',
				title: 'Edit Broker',
				templateUrl: 'assets/templates/broker/editBroker.html',
                controller: 'editBrokersController',
                moduleName: 'brokers',
				resolve:{            
					getBrokersData: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/brokers/edit/' + $stateParams.id);
					},
					deps: ['$ocLazyLoad', function($ocLazyLoad) {
                        return $ocLazyLoad.load([
							'inputMask',
							], {
							insertBefore: '#lazyload_placeholder'
						})
					}]
				}
			})
			.state('addbroker', {
				url: '/addbroker',
				title: 'Add Broker',
				templateUrl: 'assets/templates/broker/addBroker.html',
                controller: 'addBrokersController',
                moduleName: 'brokers',
            })
            .state('billing',{
				url: '/billing',
				title: 'Billable',
				templateUrl: 'assets/templates/billings/billings.html',
				controller: 'billingsController',
				moduleName: 'loads',
				params: {
					type: true,
				},
				resolve: {
					deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
                                    'daterangepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                   'inputMask',
                                   'telephonefilter'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
					getBillingData: function(dataFactory, $stateParams) {
						if( $stateParams.type == true )
							return dataFactory.httpRequest(URL+'/billings/index/invoice');
						else
							return [];
					}

				}
			}).state('billings',{
				url: '/billings/:key/?:q',
				title: 'Filtered Loads',
				templateUrl: 'assets/templates/billings/filterdBillings.html',
				controller: 'filteredBillingsController',
				moduleName: 'loads',
				params: {
                	type: true,
                },
				resolve: {
					deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
                                    'daterangepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                   'inputMask',
                                   'telephonefilter'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
					getBillingData: function(dataFactory, $stateParams) {
						var qstr = "";
						if($stateParams.q != undefined){
							 qstr = "/?"+$stateParams.q;
						}
						if($stateParams.type){
							return dataFactory.httpRequest(URL+'/Filteredbillings/index/'+$stateParams.key+qstr);
						}else{
							var response = {total:0, loads: [], billType: "billing", filterArgs: []};
							return response;
						}
					}

				}
			})
			.state('sendForPayment',{
				url: '/sendForPayment',
				title: 'Send For Payment',
				templateUrl: 'assets/templates/billings/sendForPayment.html',
				controller: 'sendPaymentController',
                controllerAs: 'sendPayment',
				moduleName: 'loads',
				resolve: {
					deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
									'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
									'inputMask',
									'menuclipper'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
            		getSendBillingData: function(dataFactory, $stateParams) {
						 return dataFactory.httpRequest(URL+'/billings/sendForPayment');
					}
				}
			})
            .state('factoredLoads',{
                url: '/factoredLoads',
                title: 'Factored Loads',
                templateUrl: 'assets/templates/billings/sendForPayment.html',
                controller: 'sendPaymentController',
                controllerAs: 'sendPayment',
                moduleName: 'loads',
                resolve: {
                    deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
                                    'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    'inputMask',
                                    'menuclipper'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
                    getSendBillingData: function(dataFactory, $stateParams) {
                         return dataFactory.httpRequest(URL+'/billings/fetchFactoredPaymentRecords');
                    }
                }
            })
            .state('outbox',{
                url: '/outbox',
                title: 'Outbox',
                templateUrl: 'assets/templates/billings/outboxPayment.html',
                controller: 'outboxPaymentController',
                controllerAs: 'outbox',
                moduleName: 'loads',
                resolve: {
                    deps: ['$ocLazyLoad', function($ocLazyLoad) {
                            return $ocLazyLoad.load([
                                    'datepicker',
                                    'timepicker',
                                    'autonumeric',
                                    'wysihtml5',
                                    'inputMask',
                                    'menuclipper'
                                ], {
                                    insertBefore: '#lazyload_placeholder'
                                });
                        }],
                    getOutboxBillingData: function(dataFactory, $stateParams) {
                         return dataFactory.httpRequest(URL+'/billings/fetchSentPaymentRecords');
                    }
                }
            })
           	.state('trailers', {
				url: '/trailers',
				title: 'Trailers',
				templateUrl: 'assets/templates/trailers/trailersListing.html',
                controller: 'trailersController',
                moduleName: 'trailers',
                resolve:{
					getTrailersListing: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/trailers');
					}
				}
			})
			.state('addTrailer', {
				url: '/addTrailer',
				title: 'Add Trailer',
				templateUrl: 'assets/templates/trailers/addEditTrailer.html',
                controller: 'addEditTrailerController',
                moduleName: 'trailers',
                resolve:{            
					getAddTrailerData: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/trailers/add');
					}
				}
			})
			.state('editTrailer', {
				url: '/editTrailer/:id',
				title: 'Edit Trailer',
				templateUrl: 'assets/templates/trailers/addEditTrailer.html',
                controller: 'addEditTrailerController',
                moduleName: 'trailers',
                resolve:{
					getAddTrailerData: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/trailers/edit/'+ $stateParams.id);
					}
				}
			})
			.state('notifications', {
				url: '/notifications',
				title: 'Notifications',
				templateUrl: 'assets/templates/notifications.html',
                controller: 'notificationsController',
                moduleName: 'loads',
                resolve:{            
					allNotifications: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/Login/notifications');
					}
				}
			}).state('billingDashboard', {
				url: '/billingDashboard',
				title: 'Billing Dashboard',
                templateUrl: 'assets/templates/billings/billingDashboard.html',
                controller: 'billingDashboardController',
                controllerAs: 'billDash',
                moduleName: 'billing',
                  resolve: {
                  	billingStats: function(dataFactory, $stateParams) {
						return dataFactory.httpRequest(URL+'/billings/billingStats');
					},
                    deps: ['$ocLazyLoad', function($ocLazyLoad) {
                        return $ocLazyLoad.load([
                                'daterangepicker'
                                // 'sparkline',
                                // 'skycons',
                                // 'switchery'
                                // 'markercluster',
                                // 'tabcollapse'
                                ], {
                                insertBefore: '#lazyload_placeholder'
                            })
					}]
                }
            })
            .state('shipper', {
                url: '/shipper',
                title: 'Shipper Listing',
                templateUrl: 'assets/templates/shipper/shipperListing.html',
                controller: 'shipperController',
                controllerAs: 'shipper',
                moduleName: 'brokers',
                params: {
                    type : '',
                    message: ''
                },
                resolve:{
                    getShipperListing: function(dataFactory, $stateParams) {
                        return dataFactory.httpRequest(URL+'/shippers');
                    },
                    fetchStatesList : function() {
                        return [];
                    },
                }
            }).state('editshipper', {
                url: '/editshipper/:id',
                title: 'Edit Shipper',
                templateUrl: 'assets/templates/shipper/editShipper.html',
                controller: 'editShipperController',
                controllerAs: 'shipper',
                moduleName: 'brokers',
                resolve:{            
                    getShipperData: function(dataFactory, $stateParams) {
                        return dataFactory.httpRequest(URL+'/shippers/update/' + $stateParams.id);
                    },
                    fetchStatesList : function(dataFactory) {
                        return dataFactory.httpRequest(URL+'/shippers/fetchUsStates');
                    },
                    deps: ['$ocLazyLoad', function($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            'inputMask',
                            ], {
                            insertBefore: '#lazyload_placeholder'
                        })
                    }]
                }
            })
            .state('addshipper', {
                url: '/addshipper',
                title: 'Add Shipper',
                templateUrl: 'assets/templates/shipper/addShipper.html',
                controller: 'shipperController',
                controllerAs: 'shipper',
                moduleName: 'brokers',
                resolve:{ 
                    getShipperListing: function() {
                        return [];
                    },
                    fetchStatesList : function(shipperService) {
                        return shipperService.fetchStatesList();
                    },

                    deps: ['$ocLazyLoad', function($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            'select',
                            ], {
                            insertBefore: '#lazyload_placeholder'
                        })
                    }]
                }
            })
            .state('investor', {
                url: '/investor',
                title: 'Investor Dashboard',
                moduleName: 'loads',
                templateUrl: 'assets/templates/investor/dashboard.html',
                controller: 'investorController',
                controllerAs: 'investor',
                resolve:{ 
                    
                    deps: ['$ocLazyLoad', function($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            'select',
                            'datepicker',
                            'daterangepicker',
                            'markercluster',
                            'metrojs'
                            
                            ], {
                            insertBefore: '#lazyload_placeholder'
                        })
                    }],
                    portlets : function(investorService) {
                        return investorService.getPortletsData();
                    },
                    vehicles : function(investorService) {
                        return investorService.fetchVehiclesList();
                    },
                    
                }

            }).state('investor.trucks', {
                url: '/trucks',
                title: 'Truck Listing',
                moduleName: 'loads',
                templateUrl: 'assets/templates/investor/dashboard.html',
                controller: 'investorController',
                controllerAs: 'investor',
                resolve:{ 
                    
                    deps: ['$ocLazyLoad', function($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            'select',
                            'datepicker',
                            'daterangepicker',
                            'markercluster'
                            
                            ], {
                            insertBefore: '#lazyload_placeholder'
                        })
                    }],
                    portlets : function(investorService) {
                        return investorService.getPortletsData();
                    },
                    vehicles : function(investorService) {
                        return investorService.fetchVehiclesList();
                    },                    
                }
            }).state('organization', {
                url: '/organization',
                title: 'Management Section',
                moduleName: 'loads',
                templateUrl: 'assets/templates/acl/organisations.html',
                controller: 'aclController',
                controllerAs: 'acl',
                resolve:{
                    getOrganisations: function(dataFactory, $stateParams) {
                        return dataFactory.httpRequest(URL+'/acl/organisations');
                    }
                }              
            }).state('manage', {
                url: '/manage/:id',
                title: 'Management Company',
                moduleName: 'loads',
                templateUrl: 'assets/templates/acl/manageCompany.html',
                controller: 'aclManage',
                controllerAs: 'acl',
                resolve:{
                    getAction: function(dataFactory, $stateParams) {
                        return dataFactory.httpRequest(URL+'/acl/getController');
                    }
                }              
            }).state('roles', {
                url: '/roles',
                title: 'Roles',
                moduleName: 'loads',
                templateUrl: 'assets/templates/acl/role.html',
                controller: 'roleManage',
                controllerAs: 'acl',
                resolve:{
                    getRoles: function(dataFactory, $stateParams) {
                        return dataFactory.httpRequest(URL+'/acl/getRoles');
                    }
                }              
            }).state('addrole', {
                url: '/addrole',
                title: 'Add Roles',
                moduleName: 'loads',
                templateUrl: 'assets/templates/acl/addRole.html',
                controller: 'roleManage',
                controllerAs: 'acl',
                resolve:{
                    getAction: function(dataFactory, $stateParams) {
                        return [];
                    },
                    getRoles: function(dataFactory, $stateParams) {
                        return [];
                    }
                }              
            }).state('editrole', {
                url: '/editrole/:id',
                title: 'Update Roles',
                moduleName: 'loads',
                templateUrl: 'assets/templates/acl/addRole.html',
                controller: 'roleManage',
                controllerAs: 'acl',
                resolve:{
                    getRoles: function(dataFactory, $stateParams) {
                        return dataFactory.httpRequest(URL+'/acl/getRoles/'+$stateParams.id);
                    }
                }
            }).state('users', {
                url: '/users',
                title: 'User List',
                moduleName: 'loads',
                templateUrl: 'assets/templates/users/usersList.html',
                controller: 'manageUsersController',
                controllerAs: 'users',
                resolve:{
                    getData: function(dataFactory, $stateParams) {
                        return dataFactory.httpRequest(URL+'/users/');
                    }
                }
            }).state('addUser', {
                url: '/addUser',
                title: 'Add User',
                moduleName: 'loads',
                templateUrl: 'assets/templates/users/userAdd.html',
                controller: 'manageUsersController',
                controllerAs: 'users',
                resolve:{
                    getData: function(dataFactory, $stateParams) {
                        return dataFactory.httpRequest(URL+'/users/getAddEdit');
                    }
                }
            }).state('editUser', {
                url: '/editUser/:id',
                title: 'Update User',
                moduleName: 'loads',
                templateUrl: 'assets/templates/users/userAdd.html',
                controller: 'manageUsersController',
                controllerAs: 'users',
                resolve:{
                    getData: function(dataFactory, $stateParams) {
                        return dataFactory.httpRequest(URL+'/users/getAddEdit/'+$stateParams.id);
                    }
                }
            });
}]);

app.run(function ($rootScope,$location,$state,$cookies,dataFactory, $document) {
	 
    /*	$rootScope.$on( '$locationChangeStart', function(e, newUrl  , oldUrl , newState, oldState) {
    			alert(oldState);
    	});
    */

	$rootScope.$on( '$stateChangeStart', function(e, toState  , toParams , fromState, fromParams) {
		$rootScope.srcPage = fromState.name;
		$rootScope.changeState = toState.name;
		$rootScope.loginCheck($rootScope.changeState);
		$rootScope.languageArray = $rootScope.LangArr[toState.moduleName];
		if ($rootScope.changeState.indexOf('?') !== -1 ) {
			$rootScope.statesArr = $rootScope.changeState.split('?');
		} else {
			$rootScope.statesArr = $rootScope.changeState.split('.');
		}
	});
	
	$rootScope.$on('$stateChangeSuccess', function (event,data) {
		
		$rootScope.absUrl = $location.search();
		$('#headerFixed').removeClass('headerFixed');
        $rootScope.pageTitle = data.title;
        $rootScope.state = data.name;
        angular.element($('.overlay')).hide();

		$('.modal-backdrop').removeClass('modal-backdrop fade in');
		$rootScope.Title = data.title;
		 
			var counter = '';
			var timer = '';
			if($rootScope.Title === 'Search Results' || $rootScope.Title == 'Plfan'){ 
				
				localStorage.countDown = 0;
				localStorage.loadCount = 0;
				localStorage.PlanCount = 0;
				
				/*** Repeat load search after every 30 seconds ****/
					var timer = setInterval(function(){
						if($rootScope.Title === 'Search Results'){
							if(localStorage.loadCount >= 30) {
								//~ $rootScope.fetchLoadsAfterEvery();
								localStorage.loadCount = 0;
							}	
							localStorage.loadCount++;
						} else if ($rootScope.Title === 'Plan' ) {
							if(localStorage.PlanCount >= 60)
							{
								//$rootScope.fetchPlanLoadsAfterEvery();
								//localStorage.PlanCount = 0;
							}	
							localStorage.PlanCount++;
						} else {
							clearInterval(timer);
						}
					},1000);   
				
				/*** Repeat load search after every 30 seconds ends ****/
				
				/** Logout chect after 30 minutes of inactivity **/
				angular.element(document).find('body').on('mousemove keydown DOMMouseScroll mousewheel mousedown touchstart mouseover', function(e){
					localStorage.countDown = 0;
				});
				
				var counter = setInterval(function(){ 
					if($rootScope.Title === 'Search') {
						if(localStorage.countDown >= 18000)
						{
							$rootScope.$apply(function() {
								$location.path('logout');
							});
							$rootScope.logoutmessage = true;
						}
						localStorage.countDown++;
					}
					else{
						clearInterval(counter);
					} 
				
				},1000);
				/** Logout chect after 5 minutes of inactivity ends **/
			}
	});
});

app.filter('titleCase', function() {
	return function(input) {
  		input = input || '';
  		return input.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	};
});




app.directive('closeOnClickOutside', function($document){
	return {
		restrict: 'A',
		link: function(scope, elem, attr, ctrl) {
  			elem.bind('click', function(e) {
    			e.stopPropagation();
  			});
  			$document.bind('click', function() {
    			scope.$apply(attr.closeOnClickOutside);
  			})
		}
	}
});

app.filter('propsFilter', function() {
	return function(items, props) {
    var out = [];
        if (angular.isArray(items)) {
          items.forEach(function(item) {
                var itemMatches = false;
                var keys = Object.keys(props);
                for (var i = 0; i < keys.length; i++) {
                      var prop = keys[i];
                      var text = props[prop].toLowerCase();
                      if (item[prop].toString().toLowerCase().indexOf(text) !== -1) {
                            itemMatches = true;
                            break;
                          }
                    }
                    if (itemMatches) {
                      out.push(item);
                    }
              });
        } else {
          // Let the output be the input untouched
              out = items;
        }
        return out;
  };
});


app.directive('dynamic', function ($compile) {
  return {
    restrict: 'A',
    replace: true,
    link: function (scope, ele, attrs) {
      scope.$watch(attrs.dynamic, function(html) {
        ele.html(html);
        $compile(ele.contents())(scope);
      });
    }
  };
});
