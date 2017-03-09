'use strict';

/**
 * @ngdoc service
 * @name angularGanttDemoApp.Sample
 * @description
 * # Sample
 * Service in the angularGanttDemoApp.
 */
app.service('Sample', function Sample() {
        return {
            getVehicleLog: function(data) {
                //console.log(data);
                var vlog  = [] , tasks = [], onDutyList = [], offDutyList = [], drivingList = [],  breakList  = [];
                var  onDutyCounter =0, drivingCounter = 0, offDutyCounter = 0, breakCounter = 0;
                var color = '#077ED0';
                var origin  = capitalizeMe(data.OriginCity.toLowerCase())+', '+data.OriginState.toUpperCase();
                var destination = capitalizeMe(data.DestinationCity.toLowerCase())+', '+data.DestinationState.toUpperCase();
                angular.forEach(data.vehicleLog, function(value, key) {
                    //------------- Display tasks of drivers on gantt chart according to Hours of Service -------------------  
                    var date_future = new Date(value.to);
                    var date_now = new Date(value.from);
                    // get total seconds between the times
                    var delta = Math.abs(date_future - date_now) / 1000;

                    // calculate (and subtract) whole days
                    var days = Math.floor(delta / 86400);
                    delta -= days * 86400;

                    // calculate (and subtract) whole hours
                    var hours = Math.floor(delta / 3600) % 24;
                    delta -= hours * 3600;

                    // calculate (and subtract) whole minutes
                    var minutes = Math.floor(delta / 60) % 60;
                    delta -= minutes * 60;

                    // what's left is seconds
                    var seconds = delta % 60;  // in theory the modulus is not required


                    var time = ((days * 24) + hours) + ":" + minutes + ":" + seconds;

                    if( key.indexOf('MOVING') != -1 || key.indexOf('START') != -1 ){
                        drivingList[drivingCounter++]   = { name: "Driving" , color:color, from: moment(value.from),  to: moment(value.to), origin:origin,  destination:destination,    hrs:time};
                    }else if(key.indexOf('STOP') != -1){
                        breakList[breakCounter++]       = { name: "Break"   , color:color, from: moment(value.from),  to: moment(value.to),  origin:origin, destination:destination,    hrs:time};    
                    }else if(key.indexOf('IGOFF') != -1){
                        offDutyList[offDutyCounter++]   = { name: "Off Duty", color:color, from: value.from,  to: value.to,  origin:origin, destination:destination,    hrs:time};             
                    }else if(key.indexOf('INFORMATION') != -1 || key.indexOf('IGON') != -1 ) {
                        onDutyList[onDutyCounter++]     = { name: "On Duty" , color:color, from: moment(value.from),  to: moment(value.to),  origin:origin, destination:destination,    hrs:time};             
                    }
                    //------------- Display tasks of drivers on gantt chart according to Hours of Service -------------------  
                });
                vlog =  [   {name   : "off Duty "   ,    tasks: offDutyList     },
                            {name   : "On Duty"     ,    tasks: onDutyList      },
                            {name   : "Driving"     ,    tasks: drivingList     },
                            {name   : "Take Break"  ,    tasks: breakList       }
                        ];
                return vlog;

            },

            fetchHoursOfServiceOld: function(hos,hosFor,data){
                var vlog  = [] , onDutyList = [], offDutyList = [], drivingList = [],  sleeperBerthList  = [];
                var hosLastDate = new Date(hos.endDate);            hosLastDate.setHours(0, 0, 0, 0);
                var hosStartDate = new Date(hos.startDate);         hosStartDate.setHours(0, 0, 0, 0);
                var hosForDate  = new Date(hosFor.startOf('day'));  hosForDate.setHours(0, 0, 0, 0);
                var holiday = ["01/01","04/15","05/28","07/03","09/03","11/22","12/24","12/30"]; //m-d-y format
                var currentYear = hosFor.format('YYYY');
                var offDuty = 0, onDuty = 0 , SB = 0, driving = 0, hoursRemainingNextDay = 0,  totalHrs = 0, flag = false, totalDrivingTime = 0, addTasksToGantt = false, lastDayExists = false, skipMe = false, color = '#077ED0';
                var origin = '', destination = '', dmEstTime = 0, from = hosFor, to = '', k=1;
                var deliveryOnWeekdaysSkipFlag  = false,  deliveryOnWeekdaysCounter   = 0;
                angular.forEach(data, function(value, key) {
                    origin      = capitalizeMe(value.OriginCity.toLowerCase())+', '+value.OriginState.toUpperCase();
                    destination = capitalizeMe(value.DestinationCity.toLowerCase())+', '+value.DestinationState.toUpperCase();
                    var pickUpDate = '';
                    if (value.PickUpDate.toLowerCase() === "daily"){
                        pickUpDate = value.pickDate;
                        console.log("pickUpDate = "+pickUpDate);
                        if(pickUpDate.indexOf('/') !== -1){
                            var dateArray = pickUpDate.split('/');
                            var year = moment().format("YYYY");
                            if(dateArray[dateArray.length -1].length == 2){
                                year = year.substr(0,2)+dateArray[dateArray.length -1]; 
                            }
                            pickUpDate = dateArray[0]+'/'+dateArray[1]+'/'+year;
                        }
                    } else {
                        pickUpDate = value.PickUpDate;
                        if(pickUpDate.indexOf('/') !== -1){
                            var dateArray = pickUpDate.split('/');
                            var year = moment().format("YYYY");
                            if(dateArray[dateArray.length -1].length == 2){
                                year = year.substr(0,2)+dateArray[dateArray.length -1]; 
                            }
                            pickUpDate = dateArray[0]+'/'+dateArray[1]+'/'+year;
                        }
                    }

                    var nextPickUpDate  = new Date(value.nextPickupDate1);
                    var pickUpDate      = new Date(pickUpDate);
                    var limitToDate     = moment(nextPickUpDate);//.subtract(1,'day');
                    var loadTime = 2,   unloadTime = 2 ;
                    totalDrivingTime    = value.totalDrivingHour;
                    dmEstTime = value.dmEstTime.hours;
                    dmEstTime = parseInt(dmEstTime) +  parseFloat(value.dmEstTime.mins/100);
                    dmEstTime = parseFloat(dmEstTime).toFixed(2);
                    var deadMileDrivingTime = 0;
                    var date = pickUpDate;
                    
                    //console.log("%c\t Load "+(k++)+" \t ", 'background: #222; color: #bada55');
                    for (var i = 0 ; date <= limitToDate; date.setDate(date.getDate() + 1), i++) {
                        //console.log("%c\t\t i = "+i+" \t ", 'color: #424242');
                        skipMe = false;
                        for (var j = 0; j < holiday.length; j++) { 
                            currentYear = moment(date).format('YYYY');
                            var hday    = holiday[j] + '/' + currentYear;
                            hday        = new Date(hday); hday.setHours(0, 0, 0, 0);
                            var cdate   = date;
                            cdate.setHours(0,0,0,0);
                            if((hosForDate>=hday && hosForDate <= hday) || cdate>=hday && cdate <= hday){
                                to      = moment(from).add('1', 'days');
                                skipMe  = true;
                            }
                        }

                        if(!skipMe){ //Skip the calculations and date if holiday occurs
                            //console.log("%c\t\t\t totalDrivingTime"+totalDrivingTime+" \t ", 'color: #424242');
                            if(moment(date).isSame(hosFor, 'day') && moment(date).isSame(hosFor, 'month') && moment(date).isSame(hosFor, 'year')){ 
                                addTasksToGantt = true;
                            }else{
                                addTasksToGantt = false;
                            }

                            flag = false;  //If driving is left for deadmiles
                            totalHrs = (!lastDayExists ) ? 0 : totalHrs;    //If current date is last day or not
                            
                            //Display deadmiles on gantt
                            if(dmEstTime > 0){
                                var consumedTime = makeHours(totalHrs);
                                var canDrive     = subtractTime(consumedTime);
                                if(canDrive > 0  && dmEstTime >= parseFloat(canDrive)){
                                    dmEstTime    = subTime(dmEstTime,canDrive);
                                    deadMileDrivingTime = canDrive;
                                    flag = true;
                                }else if(canDrive > 0 && dmEstTime < canDrive){
                                    deadMileDrivingTime = dmEstTime;
                                    dmEstTime   = subTime(dmEstTime,dmEstTime);
                                    flag = true;
                                }
                            }


                            //-------------------- Skip dates if delivery date is in weekdays ----------------------------
                            if(value.skippedWeekdays.isSkippedToMonday){
                                var weekDayDate = moment(value.skippedWeekdays.deliveryDate);
                                if(moment(date).isSame(weekDayDate, 'day') && moment(date).isSame(weekDayDate, 'month') && moment(date).isSame(weekDayDate, 'year')){
                                    var drivingTime    = makeHours(value.skippedWeekdays.drivingOnWeekDay);
                                    if(value.skippedWeekdays.drivingOnWeekDay > 0){
                                        deliveryOnWeekdaysSkipFlag  = true;
                                        if(addTasksToGantt){
                                            totalDrivingTime = 0;
                                            deliveryOnWeekdaysCounter ++;
                                            from = moment(to);
                                            to = moment(from).add(drivingTime.hrs, 'hours');
                                            to = moment(to).add(drivingTime.mins, 'minutes');
                                            drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:drivingTime.hrs+":"+drivingTime.mins});
                                            from = moment(to);
                                            var tConsumedHours  = fromTime(0.45) + fromTime(value.skippedWeekdays.drivingOnWeekDay);
                                            tConsumedHours = toTime(tConsumedHours);

                                            var weekDayOffDuty = subTime(24,tConsumedHours);
                                            if(weekDayOffDuty > 0){
                                                offDuty = weekDayOffDuty;
                                                weekDayOffDuty = makeHours(weekDayOffDuty);
                                                from = moment(to); to = moment(to).add(weekDayOffDuty.hrs,'hours');to = moment(to).add(weekDayOffDuty.mins, 'minutes');
                                                offDutyList.push({ name: "Off Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:weekDayOffDuty.hrs+':'+weekDayOffDuty.mins});
                                                from = moment(to);    
                                            }
                                        }
                                        totalHrs = fromTime(value.skippedWeekdays.drivingOnWeekDay) + fromTime(totalHrs); totalHrs = toTime(totalHrs);
                                    }else if(value.skippedWeekdays.drivingOnWeekDay == 0){
                                        if(addTasksToGantt ){
                                            //to = moment(from).add('45', 'minutes');
                                            deliveryOnWeekdaysSkipFlag  = true;
                                            offDuty = 24;
                                            weekDayOffDuty = makeHours(23.59);
                                            to = moment(from); to = moment(to).add(weekDayOffDuty.hrs,'hours');to = moment(to).add(weekDayOffDuty.mins, 'minutes');
                                            offDutyList.push({ name: "Off Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:weekDayOffDuty.hrs+':'+weekDayOffDuty.mins});
                                            from = moment(to);   
                                        } 
                                    }
                                //}else if( (moment(date).isSame(weekDayDate, 'year') || moment(date).isAfter(weekDayDate, 'year')) && (moment(date).isSame(weekDayDate, 'month') || moment(date).isAfter(weekDayDate, 'month') ) && moment(date).isAfter(weekDayDate, 'day') ){
                                  }else if(deliveryOnWeekdaysSkipFlag){  
                                        if(moment(date).isSame(limitToDate, 'day') && moment(date).isSame(limitToDate, 'month') && moment(date).isSame(limitToDate, 'year')){
                                            deliveryOnWeekdaysSkipFlag  = false;
                                            totalDrivingTime = 0;
                                        }else{
                                            deliveryOnWeekdaysCounter ++;
                                            deliveryOnWeekdaysSkipFlag  = true;
                                            if(addTasksToGantt ){
                                                offDuty = 24;
                                                weekDayOffDuty = makeHours(23.59);
                                                from = moment(to); to = moment(to).add(weekDayOffDuty.hrs,'hours');to = moment(to).add(weekDayOffDuty.mins, 'minutes');
                                                offDutyList.push({ name: "Off Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:weekDayOffDuty.hrs+':'+weekDayOffDuty.mins});
                                                from = moment(to);   
                                            } 
                                        }
                                    
                                }else if(moment(date).isSame(limitToDate, 'day') && moment(date).isSame(limitToDate, 'month') && moment(date).isSame(limitToDate, 'year')){
                                    if(addTasksToGantt){
                                        deliveryOnWeekdaysSkipFlag  = false;
                                    }
                                }
                            }

                            //-------------------- Skip dates if delivery date is in weekdays ----------------------------




                                
                            if(value.deleted !== true){
                                if(!lastDayExists && !deliveryOnWeekdaysSkipFlag || (moment(date).isSame(limitToDate, 'day') && moment(date).isSame(limitToDate, 'month') && moment(date).isSame(limitToDate, 'year'))){
                                    if(addTasksToGantt ){
                                        to = moment(from).add('45', 'minutes');
                                        onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:'0:45'});
                                        onDuty = fromTime(onDuty) + fromTime(0.45); onDuty = toTime(onDuty);
                                    }
                                }else{
                                    to = moment(from);
                                    lastDayExists = false;
                                }


                                
                                if(!deliveryOnWeekdaysSkipFlag){
                            
                                    if(moment(date).isSame(limitToDate, 'day') && moment(date).isSame(limitToDate, 'month') && moment(date).isSame(limitToDate, 'year')){

                                        var HRND = fromTime(value.hoursRemainingNextDay);
                                        HRND = toTime(HRND);
                                        var tempHRND = 0;
                                        if(totalDrivingTime > 0){
                                            var pendingUnloadTime = 0;
                                            totalHrs = fromTime(totalHrs) + fromTime(totalDrivingTime) ;
                                            totalHrs = toTime(totalHrs);
                                            if(HRND >= totalHrs){
                                                tempHRND = subTime(HRND,totalHrs);
                                                if(tempHRND >=2){
                                                    pendingUnloadTime = subTime(tempHRND,2);
                                                }
                                            }
                                            if(pendingUnloadTime > 0){
                                                if(addTasksToGantt){
                                                    from = moment(to); 
                                                    var consumedTime = makeHours(pendingUnloadTime);
                                                    to = moment(from).add(consumedTime.hrs, 'hours');
                                                    to = moment(to).add(consumedTime.mins, 'minutes');
                                                    onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:consumedTime.hrs+":"+consumedTime.mins});
                                                    from = moment(to);
                                                    onDuty = fromTime(onDuty) + fromTime(pendingUnloadTime); 
                                                    onDuty = toTime(onDuty);
                                                }
                                                totalHrs = fromTime(pendingUnloadTime) + fromTime(totalHrs);
                                                totalHrs = toTime(totalHrs);
                                            }
                                        }


                                        if(totalDrivingTime > 0 ){
                                            var driveTime = totalDrivingTime;
                                            if(addTasksToGantt){
                                                from = moment(to);
                                                var addDrivingTime = makeHours(driveTime);
                                                to = moment(from).add(addDrivingTime.hrs, 'hours');
                                                to = moment(to).add(addDrivingTime.mins, 'minutes');
                                                drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addDrivingTime.hrs+":"+addDrivingTime.mins});
                                                from = moment(to);
                                                driving = fromTime(driving) + fromTime(driveTime);
                                                driving = toTime(driving);
                                            }
                                            totalDrivingTime = subTime(totalDrivingTime, totalDrivingTime);
                                            
                                            //totalHrs = fromTime(driveTime) + fromTime(totalHrs); totalHrs = toTime(totalHrs);
                                        }
                                       
                                        if(HRND >= totalHrs){
                                            HRND = subTime(HRND, totalHrs);     
                                        }

                                        if(HRND > 0){
                                           if(addTasksToGantt){
                                                from = moment(to); 
                                                var consumedTime = makeHours(HRND);
                                                to = moment(from).add(consumedTime.hrs, 'hours');
                                                to = moment(to).add(consumedTime.mins, 'minutes');
                                                onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:consumedTime.hrs+":"+consumedTime.mins});
                                                from = moment(to);
                                                onDuty = fromTime(onDuty) + fromTime(HRND);
                                                onDuty = toTime(onDuty);
                                            }
                                            totalHrs = fromTime(HRND) + fromTime(totalHrs);
                                            totalHrs = toTime(totalHrs);
                                        }
                                        lastDayExists = true;
                                    } // last condition end (HRND)
                                    
                                    //Dead Mile Driving
                                    if(deadMileDrivingTime > 0 && flag){
                                        var consumedTime = makeHours(totalHrs);
                                        var canDrive     = subtractTime(consumedTime);
                                        var rDeadMiles   = 0;
                                        if(canDrive > 0  && deadMileDrivingTime >= canDrive){
                                            rDeadMiles   = subTime(deadMileDrivingTime,canDrive);
                                            deadMileDrivingTime = canDrive;
                                            dmEstTime = fromTime(dmEstTime) + fromTime(rDeadMiles);
                                            dmEstTime = toTime(dmEstTime);
                                        }else if(canDrive > 0 && deadMileDrivingTime < canDrive){
                                            dmEstTime   = subTime(deadMileDrivingTime,deadMileDrivingTime);
                                            deadMileDrivingTime = deadMileDrivingTime;
                                        }

                                        if(deadMileDrivingTime > 0){
                                            
                                            var drivingHrs = makeHours(deadMileDrivingTime);
                                            if(addTasksToGantt){
                                                from = moment(to); to = moment(to).add(drivingHrs.hrs,'hours'); to = moment(to).add(drivingHrs.mins,'minutes'); 
                                                drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:drivingHrs.hrs + ":" + drivingHrs.mins});
                                                driving  = fromTime(driving) + fromTime(deadMileDrivingTime); driving = toTime(driving);
                                            }
                                            
                                            totalHrs = fromTime(totalHrs) + fromTime(deadMileDrivingTime);
                                            totalHrs = toTime(totalHrs);
                                            totalDrivingTime    = subTime(totalDrivingTime, deadMileDrivingTime); 
                                            
                                            deadMileDrivingTime = 0;
                                        }
                                        
                                    } //End deadmiles driving
                                    
                                    if(totalHrs < 8 ){ //On Duty Hours for load the truck/vehicle
                                        var consumedTime = makeHours(totalHrs);
                                        var timeLeftToday  = subtractTime(consumedTime);
                                        var dutyTime = 0;
                                        if( timeLeftToday >= loadTime && loadTime > 0 ){
                                            dutyTime = loadTime;
                                            loadTime = subTime(loadTime, dutyTime);
                                        }else if( timeLeftToday > 0 && timeLeftToday < loadTime && loadTime > 0){
                                            dutyTime = timeLeftToday;
                                            loadTime = subTime(loadTime, timeLeftToday); 
                                        }
                                        if(dutyTime > 0 ){
                                            if(addTasksToGantt){
                                                from = moment(to); 
                                                var addOnDutyTime = makeHours(dutyTime);
                                                to = moment(from).add(addOnDutyTime.hrs, 'hours');
                                                to = moment(to).add(addOnDutyTime.mins, 'minutes');
                                                onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addOnDutyTime.hrs+":"+addOnDutyTime.mins});
                                                from = moment(to);
                                                onDuty = fromTime(onDuty) + fromTime(dutyTime); onDuty = toTime(onDuty);
                                            }
                                            totalHrs = fromTime(dutyTime) + fromTime(totalHrs);
                                            totalHrs = toTime(totalHrs);
                                        } //End onduty hours for load the truck/vehicle
                                    }

                                    // For driving hours
                                    var consumedTime    = makeHours(totalHrs);
                                    var timeLeftToday   = subtractTime(consumedTime);
                                    var drivingTime     = 0;
                                    if(timeLeftToday > 0 && parseFloat(timeLeftToday) <= parseFloat(totalDrivingTime)){
                                        totalDrivingTime    = subTime(totalDrivingTime, timeLeftToday); 
                                        drivingTime = timeLeftToday;
                                    }else if(timeLeftToday > 0 && (timeLeftToday > totalDrivingTime && totalDrivingTime > 0)){ 
                                        drivingTime        = totalDrivingTime;
                                        totalDrivingTime   = subTime(totalDrivingTime, totalDrivingTime); 
                                    }

                                    if(drivingTime > 0){
                                        if(addTasksToGantt){

                                            from = moment(to);
                                            var addDrivingTime = makeHours(drivingTime);
                                            to = moment(from).add(addDrivingTime.hrs, 'hours');
                                            to = moment(to).add(addDrivingTime.mins, 'minutes');
                                            drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addDrivingTime.hrs+":"+addDrivingTime.mins});
                                            from = moment(to);
                                            driving  = fromTime(driving) + fromTime(drivingTime); driving = toTime(driving);
                                        }
                                        
                                        totalHrs = fromTime(drivingTime) + fromTime(totalHrs); totalHrs = toTime(totalHrs);
                                    }

                                    if(!lastDayExists){
                                        //On Duty Hours for unload the load
                                        var consumedTime   = makeHours(totalHrs);
                                        var timeLeftToday  = subtractTime(consumedTime);
                                        var dutyTime = 0;
                                        if( timeLeftToday >= unloadTime && unloadTime > 0 ){
                                            dutyTime = 2;
                                            unloadTime = subTime(unloadTime, dutyTime);
                                        }else if( timeLeftToday > 0 && timeLeftToday < unloadTime && unloadTime > 0){
                                            dutyTime = timeLeftToday;
                                            unloadTime = subTime(unloadTime, timeLeftToday); 
                                        }

                                        if(dutyTime > 0 ){
                                            if(addTasksToGantt){
                                                from = moment(to); 
                                                var addOnDutyTime = makeHours(dutyTime);
                                                to = moment(from).add(addOnDutyTime.hrs, 'hours');
                                                to = moment(to).add(addOnDutyTime.mins, 'minutes');
                                                onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addOnDutyTime.hrs+":"+addOnDutyTime.mins});
                                                from = moment(to);
                                                onDuty = fromTime(onDuty) + fromTime(dutyTime); onDuty = toTime(onDuty);
                                            }
                                            totalHrs = fromTime(dutyTime) + fromTime(totalHrs);
                                            totalHrs = toTime(totalHrs);
                                            
                                        } //End onduty hours
                                    }
                                    if(totalHrs >= 8 ){
                                        if(addTasksToGantt){
                                            var timeLeftToday = subTime(23.15,totalHrs);
                                            if(timeLeftToday >= 8){
                                                var sleepTime = subTime(23.15,totalHrs);
                                                var addSleeperBerthTime = makeHours(sleepTime);
                                                from = moment(to); to = moment(to).add(addSleeperBerthTime.hrs,'hours');to = moment(to).add(addSleeperBerthTime.mins, 'minutes');
                                                sleeperBerthList.push({ name: "Off Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addSleeperBerthTime.hrs + ":"+addSleeperBerthTime.mins});
                                                from = moment(to);
                                                SB = fromTime(SB) + fromTime(sleepTime); SB = toTime(SB);
                                                totalHrs = fromTime(SB) + fromTime(totalHrs);
                                                totalHrs = toTime(totalHrs);
                                            }
                                            var timeLeftToday = subTime(23.15,totalHrs);
                                            var addOffDutyTime = makeHours(timeLeftToday);
                                            from = moment(to); to = moment(to).add(addOffDutyTime.hrs,'hours');to = moment(to).add(addOffDutyTime.mins, 'minutes');
                                            offDutyList.push({ name: "Off Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addOffDutyTime.hrs + ":"+addOffDutyTime.mins});
                                            from = moment(to);
                                            offDuty = timeLeftToday;
                                        }
                                    }
                                }
                            }  // if(!value.deleted)
                        }   // if(!skipMe)
                    } //for (var i = 0 ; date <= limitToDate; date.setDate(date.getDate() + 1), i++) {
                }); //angular.forEach(data, function(value, key) {


                vlog =  [   
                            {   name   : "off Duty "   ,    tasks: offDutyList     },
                            {   name   : "Sleeper Berth",   tasks: sleeperBerthList},
                            {   name   : "Driving"     ,    tasks: drivingList     },
                            {   name   : "On Duty"     ,    tasks: onDutyList      }
                            
                        ];

                var total = fromTime(onDuty) + fromTime(offDuty) + fromTime(SB) + fromTime(driving);
                total = toTime(total);
                var totals = { "offDuty" : offDuty, "onDuty" : onDuty, "SB" : SB, "driving" : driving, "thours" : total};
                return {vlog:vlog, totals:totals};
            },


            //Show HOS on Gantt for team
            fetchHoursOfService: function(hos,hosFor,data,source){
                var vlog  = [] , onDutyList = [], offDutyList = [], drivingList = [],  sleeperBerthList  = [];
                var hosLastDate = new Date(hos.endDate);            hosLastDate.setHours(0, 0, 0, 0);
                var hosStartDate = new Date(hos.startDate);         hosStartDate.setHours(0, 0, 0, 0);
                var hosForDate  = new Date(hosFor.startOf('day'));  hosForDate.setHours(0, 0, 0, 0);
                var holiday = ["01/01","04/15","05/28","07/03","09/03","11/22","12/24","12/30"]; //m-d-y format
                var currentYear = hosFor.format('YYYY');
                var offDuty = 0, onDuty = 0 , SB = 0, driving = 0, hoursRemainingNextDay = 0,  totalHrs = 0, flag = false, totalDrivingTime = 0, addTasksToGantt = false, lastDayExists = false, skipMe = false, color = '#077ED0';
                var origin = '', destination = '', dmEstTime = 0, from = hosFor, to = '', k=1, dailyDrivingLimit = 11, sleeperBirthTime = 8, breakTime = 0.30;
                if(source == "team" ||source == "_team" ){
                    dailyDrivingLimit = 11;
                }else{
                    dailyDrivingLimit = 8;
                }
                
                var deliveryOnWeekdaysSkipFlag  = false,  deliveryOnWeekdaysCounter   = 0;
                angular.forEach(data, function(value, key) {
                    origin      = capitalizeMe(value.OriginCity.toLowerCase())+', '+value.OriginState.toUpperCase();
                    destination = capitalizeMe(value.DestinationCity.toLowerCase())+', '+value.DestinationState.toUpperCase();
                    var pickUpDate = '';
                    if (value.PickUpDate.toLowerCase() === "daily"){
                        pickUpDate = value.pickDate;
                        if(pickUpDate.indexOf('/') !== -1){
                            var dateArray = pickUpDate.split('/');
                            var year = moment().format("YYYY");
                            if(dateArray[dateArray.length -1].length == 2){
                                year = year.substr(0,2)+dateArray[dateArray.length -1]; 
                            }
                            pickUpDate = dateArray[0]+'/'+dateArray[1]+'/'+year;
                        }
                    } else {
                        pickUpDate = value.PickUpDate;
                        if(pickUpDate.indexOf('/') !== -1){
                            var dateArray = pickUpDate.split('/');
                            var year = moment().format("YYYY");
                            if(dateArray[dateArray.length -1].length == 2){
                                year = year.substr(0,2)+dateArray[dateArray.length -1]; 
                            }
                            pickUpDate = dateArray[0]+'/'+dateArray[1]+'/'+year;
                        }
                    }

                    var nextPickUpDate  = new Date(value.nextPickupDate1);
                    var pickUpDate      = new Date(pickUpDate);
                    var limitToDate     = moment(nextPickUpDate);//.subtract(1,'day');
                    var loadTime = 2,   unloadTime = 2 ;
                    totalDrivingTime    = value.totalDrivingHour;
                    dmEstTime = value.dmEstTime.hours;
                    dmEstTime = parseInt(dmEstTime) +  parseFloat(value.dmEstTime.mins/100);
                    dmEstTime = parseFloat(dmEstTime).toFixed(2);
                    var deadMileDrivingTime = 0;
                    var date = pickUpDate;
                    //console.log("%c\t Load "+(k++)+" \t ", 'background: #222; color: #bada55');
                    for (var i = 0 ; date <= limitToDate; date.setDate(date.getDate() + 1), i++) {
                        //console.log("%c\t\t i = "+i+" \t ", 'color: #424242');
                        skipMe = false;
                        for (var j = 0; j < holiday.length; j++) { 
                            currentYear = moment(date).format('YYYY');
                            var hday    = holiday[j] + '/' + currentYear;
                            hday        = new Date(hday); hday.setHours(0, 0, 0, 0);
                            var cdate   = date;
                            cdate.setHours(0,0,0,0);
                            if((hosForDate>=hday && hosForDate <= hday) || cdate>=hday && cdate <= hday){
                                to      = moment(from).add('1', 'days');
                                skipMe  = true;
                            }
                        }

                        if(!skipMe){ //Skip the calculations and date if holiday occurs
                            //console.log("%c\t\t\t totalDrivingTime"+totalDrivingTime+" \t ", 'color: #424242');
                            if(moment(date).isSame(hosFor, 'day') && moment(date).isSame(hosFor, 'month') && moment(date).isSame(hosFor, 'year')){ 
                                addTasksToGantt = true;
                            }else{
                                addTasksToGantt = false;
                            }

                            flag = false;  //If driving is left for deadmiles
                            totalHrs = (!lastDayExists ) ? 0 : totalHrs;    //If current date is last day or not
                            
                            //Display deadmiles on gantt
                            if(dmEstTime > 0){
                                //var consumedTime = makeHours(totalHrs);
                                //var canDrive     = subtractTime(consumedTime,'team');
                                var canDrive     = subTime(dailyDrivingLimit,totalHrs);
                                if(canDrive > 0  && dmEstTime >= parseFloat(canDrive)){
                                    dmEstTime    = subTime(dmEstTime,canDrive);
                                    deadMileDrivingTime = canDrive;
                                    flag = true;
                                }else if(canDrive > 0 && dmEstTime < parseFloat(canDrive)){
                                    deadMileDrivingTime = dmEstTime;
                                    dmEstTime   = subTime(dmEstTime,dmEstTime);
                                    flag = true;
                                }
                            }


                            //-------------------- Skip dates if delivery date is in weekdays ----------------------------
                            if(value.skippedWeekdays.isSkippedToMonday){
                                //console.log(value.skippedWeekdays.deliveryDate[0]);
                                var weekDayDate = moment(value.skippedWeekdays.deliveryDate[0]);
                                //var weekArray = ["2017-02-11","2017-02-12"];
                                var weekArray = moment(value.skippedWeekdays.deliveryDate);
                                var cdate = moment(date).format("YYYY-MM-DD");
                                if(weekArray.length > 0 && (weekArray.indexOf(cdate) >= 1)){
                                    deliveryOnWeekdaysSkipFlag  = true;
                                }
                                if(moment(date).isSame(weekDayDate, 'day') && moment(date).isSame(weekDayDate, 'month') && moment(date).isSame(weekDayDate, 'year')){
                                    var drivingTime    = makeHours(value.skippedWeekdays.drivingOnWeekDay);
                                    if(value.skippedWeekdays.drivingOnWeekDay > 0){
                                        if(addTasksToGantt ){
                                            to = moment(from).add('45', 'minutes');
                                            onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:'0:45'});
                                            onDuty = fromTime(onDuty) + fromTime(0.45); onDuty = toTime(onDuty);
                                            from = moment(to);
                                        }


                                        deliveryOnWeekdaysSkipFlag  = true;
                                        if(addTasksToGantt){
                                            totalDrivingTime = 0;
                                            // ---------------------- Driving on weekdays ----------------------
                                            var tempDrivingOnWeekDay = value.skippedWeekdays.drivingOnWeekDay;
                                            var needBreak = false;
                                            if(tempDrivingOnWeekDay > sleeperBirthTime){  //Check if driving time is greator to 8 hours then me must add a 30 mins break.
                                                tempDrivingOnWeekDay = subTime(tempDrivingOnWeekDay,sleeperBirthTime);
                                                var addDrivingTime = makeHours(sleeperBirthTime);
                                                to = moment(from).add(addDrivingTime.hrs, 'hours');
                                                to = moment(to).add(addDrivingTime.mins, 'minutes');
                                                drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addDrivingTime.hrs+":"+addDrivingTime.mins});
                                                from = moment(to);
                                                needBreak = true;
                                                //drivingTime = makeHours(tempDrivingOnWeekDay);
                                            } 
                                            var tConsumedHours  = 0;
                                            if(needBreak){ 
                                                needBreak =false;
                                                var addSleeperBerthTime = makeHours(breakTime);
                                                from = moment(to); to = moment(to).add(addSleeperBerthTime.hrs,'hours');to = moment(to).add(addSleeperBerthTime.mins, 'minutes');
                                                sleeperBerthList.push({ name: "Sleeper Berth" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addSleeperBerthTime.hrs + ":"+addSleeperBerthTime.mins});
                                                from = moment(to);
                                                SB = fromTime(SB) + fromTime(breakTime); SB = toTime(SB);
                                                totalHrs = fromTime(breakTime) + fromTime(totalHrs);
                                                totalHrs = toTime(totalHrs);
                                                tConsumedHours  =  fromTime(breakTime);
                                            }

                                            if(tempDrivingOnWeekDay > 0){
                                                drivingTime = makeHours(tempDrivingOnWeekDay);
                                                to = moment(from).add(drivingTime.hrs, 'hours'); to = moment(to).add(drivingTime.mins, 'minutes');
                                                drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:drivingTime.hrs+":"+drivingTime.mins});
                                            }
                                            driving  = fromTime(driving) + fromTime(value.skippedWeekdays.drivingOnWeekDay); driving = toTime(driving);
                                            totalHrs = fromTime(value.skippedWeekdays.drivingOnWeekDay) + fromTime(totalHrs); totalHrs = toTime(totalHrs);
                                        // -------------------- Driving on weekdays end ------------------


                                            from = moment(to);
                                            tConsumedHours  = fromTime(tConsumedHours) + fromTime(value.skippedWeekdays.drivingOnWeekDay);
                                            tConsumedHours = toTime(tConsumedHours);
                                            var weekDayOffDuty = subTime(23.15,tConsumedHours);
                                            if(weekDayOffDuty > 0){
                                                offDuty = weekDayOffDuty;
                                                weekDayOffDuty = makeHours(weekDayOffDuty);
                                                from = moment(to); to = moment(to).add(weekDayOffDuty.hrs,'hours');to = moment(to).add(weekDayOffDuty.mins, 'minutes');
                                                offDutyList.push({ name: "Off Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:weekDayOffDuty.hrs+':'+weekDayOffDuty.mins});
                                                from = moment(to);    
                                            }
                                        }
                                        
                                    }else if(value.skippedWeekdays.drivingOnWeekDay == 0){
                                        if(addTasksToGantt ){
                                            //to = moment(from).add('45', 'minutes');
                                            deliveryOnWeekdaysSkipFlag  = true;
                                            offDuty = 24;
                                            weekDayOffDuty = makeHours(24);
                                            to = moment(from); to = moment(to).add(weekDayOffDuty.hrs,'hours');to = moment(to).add(weekDayOffDuty.mins, 'minutes');
                                            offDutyList.push({ name: "Off Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:weekDayOffDuty.hrs+':'+weekDayOffDuty.mins});
                                            from = moment(to);   
                                        } 
                                    }
                                  }else if(deliveryOnWeekdaysSkipFlag){  
                                        if(moment(date).isSame(limitToDate, 'day') && moment(date).isSame(limitToDate, 'month') && moment(date).isSame(limitToDate, 'year')){
                                            deliveryOnWeekdaysSkipFlag  = false;
                                            totalDrivingTime = 0;
                                        }else{
                                            deliveryOnWeekdaysSkipFlag  = true;
                                            if(addTasksToGantt ){
                                                offDuty = 24;
                                                weekDayOffDuty = makeHours(24);
                                                to = moment(from).add(weekDayOffDuty.hrs,'hours'); to = moment(to).add(weekDayOffDuty.mins, 'minutes');
                                                offDutyList.push({ name: "Off Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:weekDayOffDuty.hrs+':'+weekDayOffDuty.mins});
                                                from = moment(to);   
                                            } 
                                        }
                                    
                                }else if(moment(date).isSame(limitToDate, 'day') && moment(date).isSame(limitToDate, 'month') && moment(date).isSame(limitToDate, 'year')){
                                    if(addTasksToGantt){
                                        deliveryOnWeekdaysSkipFlag  = false;
                                    }
                                }
                            }

                            //-------------------- Skip dates if delivery date is in weekdays ----------------------------


                                
                            if(value.deleted !== true){
                                if(!lastDayExists && !deliveryOnWeekdaysSkipFlag || (moment(date).isSame(limitToDate, 'day') && moment(date).isSame(limitToDate, 'month') && moment(date).isSame(limitToDate, 'year'))){
                                    if(addTasksToGantt ){
                                        to = moment(from).add('45', 'minutes');
                                        onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:'0:45'});
                                        onDuty = fromTime(onDuty) + fromTime(0.45); onDuty = toTime(onDuty);
                                    }
                                }else{
                                    to = moment(from);
                                    lastDayExists = false;
                                }


                                
                                if(!deliveryOnWeekdaysSkipFlag){
                            
                                    if(moment(date).isSame(limitToDate, 'day') && moment(date).isSame(limitToDate, 'month') && moment(date).isSame(limitToDate, 'year')){

                                        var HRND = fromTime(value.hoursRemainingNextDay);
                                        HRND = toTime(HRND);
                                        var tempHRND = 0;
                                        if(totalDrivingTime > 0){
                                            var pendingUnloadTime = 0;
                                            totalHrs = fromTime(totalHrs) + fromTime(totalDrivingTime) ;
                                            totalHrs = toTime(totalHrs);
                                            if(parseFloat(HRND) >= parseFloat(totalHrs)){
                                                tempHRND = subTime(HRND,totalHrs);
                                                if(tempHRND >=2){
                                                    pendingUnloadTime = subTime(tempHRND,2);
                                                }
                                            }
                                            if(pendingUnloadTime > 0){
                                                if(addTasksToGantt){
                                                    from = moment(to); 
                                                    var consumedTime = makeHours(pendingUnloadTime);
                                                    to = moment(from).add(consumedTime.hrs, 'hours');
                                                    to = moment(to).add(consumedTime.mins, 'minutes');
                                                    onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:consumedTime.hrs+":"+consumedTime.mins});
                                                    from = moment(to);
                                                    onDuty = fromTime(onDuty) + fromTime(pendingUnloadTime); 
                                                    onDuty = toTime(onDuty);
                                                }
                                                totalHrs = fromTime(pendingUnloadTime) + fromTime(totalHrs);
                                                totalHrs = toTime(totalHrs);
                                            }
                                        }


                                        if(totalDrivingTime > 0 ){
                                            var driveTime = totalDrivingTime;
                                            if(addTasksToGantt){
                                                from = moment(to);
                                                var needBreak = false;
                                                if(driveTime > sleeperBirthTime){  //Check if driving time is greator to 8 hours then me must add a 30 mins break.
                                                    driveTime = subTime(driveTime,sleeperBirthTime);
                                                    var addDrivingTime = makeHours(sleeperBirthTime);
                                                    to = moment(from).add(addDrivingTime.hrs, 'hours');
                                                    to = moment(to).add(addDrivingTime.mins, 'minutes');
                                                    drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addDrivingTime.hrs+":"+addDrivingTime.mins});
                                                    from = moment(to);
                                                    driving  = fromTime(driving) + fromTime(sleeperBirthTime); driving = toTime(driving);
                                                    needBreak = true;
                                                } 

                                                if(needBreak){ 
                                                    needBreak =false;
                                                    var addSleeperBerthTime = makeHours(breakTime);
                                                    from = moment(to); to = moment(to).add(addSleeperBerthTime.hrs,'hours');to = moment(to).add(addSleeperBerthTime.mins, 'minutes');
                                                    sleeperBerthList.push({ name: "Sleeper Berth" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addSleeperBerthTime.hrs + ":"+addSleeperBerthTime.mins});
                                                    from = moment(to);
                                                    SB = fromTime(SB) + fromTime(breakTime); SB = toTime(SB);
                                                    totalHrs = fromTime(breakTime) + fromTime(totalHrs);
                                                    totalHrs = toTime(totalHrs);
                                                }

                                                if(driveTime > 0){
                                                    var addDrivingTime = makeHours(driveTime);
                                                    to = moment(from).add(addDrivingTime.hrs, 'hours');
                                                    to = moment(to).add(addDrivingTime.mins, 'minutes');
                                                    drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addDrivingTime.hrs+":"+addDrivingTime.mins});
                                                    from = moment(to);
                                                    driving = fromTime(driving) + fromTime(driveTime);
                                                    driving = toTime(driving);
                                                }
                                            }
                                            //totalHrs = fromTime(totalDrivingTime) + fromTime(totalHrs); totalHrs = toTime(totalHrs);
                                            totalDrivingTime = subTime(totalDrivingTime, totalDrivingTime);
                                            
                                            
                                        }
                                       
                                        if(parseFloat(HRND) >= parseFloat(totalHrs)){
                                            HRND = subTime(HRND, totalHrs);     
                                        }

                                        if(HRND > 0){
                                           if(addTasksToGantt){
                                                from = moment(to); 
                                                var consumedTime = makeHours(HRND);
                                                to = moment(from).add(consumedTime.hrs, 'hours');
                                                to = moment(to).add(consumedTime.mins, 'minutes');
                                                onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:consumedTime.hrs+":"+consumedTime.mins});
                                                from = moment(to);
                                                onDuty = fromTime(onDuty) + fromTime(HRND);
                                                onDuty = toTime(onDuty);
                                            }
                                            totalHrs = fromTime(HRND) + fromTime(totalHrs);
                                            totalHrs = toTime(totalHrs);
                                        }
                                        lastDayExists = true;
                                    } // last condition end (HRND)
                                    
                                    //Dead Mile Driving
                                    if(deadMileDrivingTime > 0 && flag){
                                        var canDrive = subTime(dailyDrivingLimit,totalHrs);
                                        var rDeadMiles   = 0;
                                        if(canDrive > 0  && deadMileDrivingTime >= parseFloat(canDrive)){
                                            rDeadMiles   = subTime(deadMileDrivingTime,canDrive);
                                            deadMileDrivingTime = canDrive;
                                            dmEstTime = fromTime(dmEstTime) + fromTime(rDeadMiles);
                                            dmEstTime = toTime(dmEstTime);
                                        }else if(canDrive > 0 && deadMileDrivingTime < parseFloat(canDrive)){
                                            dmEstTime   = subTime(deadMileDrivingTime,deadMileDrivingTime);
                                            deadMileDrivingTime = deadMileDrivingTime;
                                        }

                                        if(deadMileDrivingTime > 0){
                                            
                                            var drivingHrs = makeHours(deadMileDrivingTime);
                                            if(addTasksToGantt){

                                                var tempDrivingHrs = deadMileDrivingTime;
                                                from = moment(to);
                                                var needBreak = false;
                                                if(tempDrivingHrs > sleeperBirthTime){  //Check if driving time is greator to 8 hours then me must add a 30 mins break.
                                                    
                                                    tempDrivingHrs = subTime(tempDrivingHrs,sleeperBirthTime);
                                                    var addDrivingTime = makeHours(sleeperBirthTime);
                                                    to = moment(from).add(addDrivingTime.hrs, 'hours');
                                                    to = moment(to).add(addDrivingTime.mins, 'minutes');
                                                    drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addDrivingTime.hrs+":"+addDrivingTime.mins});
                                                    from = moment(to);
                                                    //driving  = fromTime(driving) + fromTime(sleeperBirthTime); driving = toTime(driving);
                                                    needBreak = true;
                                                    

                                                } 

                                                if(needBreak){ 
                                                    needBreak =false;
                                                    var addSleeperBerthTime = makeHours(breakTime);
                                                    from = moment(to); to = moment(to).add(addSleeperBerthTime.hrs,'hours');to = moment(to).add(addSleeperBerthTime.mins, 'minutes');
                                                    sleeperBerthList.push({ name: "Sleeper Berth" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addSleeperBerthTime.hrs + ":"+addSleeperBerthTime.mins});
                                                    from = moment(to);
                                                    SB = fromTime(SB) + fromTime(breakTime); SB = toTime(SB);
                                                    totalHrs = fromTime(breakTime) + fromTime(totalHrs);
                                                    totalHrs = toTime(totalHrs);
                                                }

                                                if(tempDrivingHrs > 0){
                                                    drivingHrs = makeHours(tempDrivingHrs);
                                                    from = moment(to); to = moment(to).add(drivingHrs.hrs,'hours'); to = moment(to).add(drivingHrs.mins,'minutes'); 
                                                    drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:drivingHrs.hrs + ":" + drivingHrs.mins});
                                                }
                                                
                                                driving  = fromTime(driving) + fromTime(deadMileDrivingTime); driving = toTime(driving);
                                            }
                                            
                                            totalHrs = fromTime(totalHrs) + fromTime(deadMileDrivingTime);
                                            totalHrs = toTime(totalHrs);
                                            totalDrivingTime    = subTime(totalDrivingTime, deadMileDrivingTime); 
                                            
                                            deadMileDrivingTime = 0;
                                        }
                                        
                                    } //End deadmiles driving
                                    
                                    if(totalHrs < dailyDrivingLimit && !lastDayExists ){ //On Duty Hours for load the truck/vehicle
                                        //var consumedTime = makeHours(totalHrs);
                                        //var timeLeftToday  = subtractTime(consumedTime,'team');
                                        var timeLeftToday = subTime(dailyDrivingLimit,totalHrs);
                                        var dutyTime = 0;
                                        if( timeLeftToday >= loadTime && loadTime > 0 ){
                                            dutyTime = loadTime;
                                            loadTime = subTime(loadTime, dutyTime);
                                        }else if( timeLeftToday > 0 && timeLeftToday < loadTime && loadTime > 0){
                                            dutyTime = timeLeftToday;
                                            loadTime = subTime(loadTime, timeLeftToday); 
                                        }
                                        if(dutyTime > 0 ){
                                            if(addTasksToGantt){
                                                from = moment(to); 
                                                var addOnDutyTime = makeHours(dutyTime);
                                                to = moment(from).add(addOnDutyTime.hrs, 'hours');
                                                to = moment(to).add(addOnDutyTime.mins, 'minutes');
                                                onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addOnDutyTime.hrs+":"+addOnDutyTime.mins});
                                                from = moment(to);
                                                onDuty = fromTime(onDuty) + fromTime(dutyTime); onDuty = toTime(onDuty);
                                            }
                                            totalHrs = fromTime(dutyTime) + fromTime(totalHrs);
                                            totalHrs = toTime(totalHrs);
                                        } //End onduty hours for load the truck/vehicle
                                    }

                                    // For driving hours
                                    //var consumedTime    = makeHours(totalHrs);
                                    //var timeLeftToday   = subtractTime(consumedTime,'team');
                                    var timeLeftToday = subTime(dailyDrivingLimit,totalHrs);
                                    var drivingTime     = 0;
                                    if(timeLeftToday > 0 && parseFloat(timeLeftToday) <= parseFloat(totalDrivingTime)){
                                        totalDrivingTime    = subTime(totalDrivingTime, timeLeftToday); 
                                        drivingTime = timeLeftToday;
                                    }else if(timeLeftToday > 0 && (timeLeftToday > totalDrivingTime && totalDrivingTime > 0)){ 
                                        drivingTime        = totalDrivingTime;
                                        totalDrivingTime   = subTime(totalDrivingTime, totalDrivingTime); 
                                    }

                                    if(drivingTime > 0){
                                        if(addTasksToGantt){
                                            var tempDrivingTime = drivingTime;
                                            from = moment(to);
                                            var needBreak = false;
                                            if(drivingTime > sleeperBirthTime){  //Check if driving time is greator to 8 hours then me must add a 30 mins break.
                                                
                                                drivingTime = subTime(drivingTime,sleeperBirthTime);
                                                var addDrivingTime = makeHours(sleeperBirthTime);
                                                to = moment(from).add(addDrivingTime.hrs, 'hours');
                                                to = moment(to).add(addDrivingTime.mins, 'minutes');
                                                drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addDrivingTime.hrs+":"+addDrivingTime.mins});
                                                from = moment(to);
                                                driving  = fromTime(driving) + fromTime(sleeperBirthTime); driving = toTime(driving);
                                                needBreak = true;

                                            } 

                                            if(needBreak){ 
                                                needBreak =false;
                                                var addSleeperBerthTime = makeHours(breakTime);
                                                from = moment(to); to = moment(to).add(addSleeperBerthTime.hrs,'hours');to = moment(to).add(addSleeperBerthTime.mins, 'minutes');
                                                sleeperBerthList.push({ name: "Sleeper Berth" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addSleeperBerthTime.hrs + ":"+addSleeperBerthTime.mins});
                                                from = moment(to);
                                                SB = fromTime(SB) + fromTime(breakTime); SB = toTime(SB);
                                                totalHrs = fromTime(breakTime) + fromTime(totalHrs);
                                                totalHrs = toTime(totalHrs);
                                            }


                                            //Remaining Driving time
                                            if(drivingTime > 0){
                                                var addDrivingTime = makeHours(drivingTime);
                                                to = moment(from).add(addDrivingTime.hrs, 'hours');
                                                to = moment(to).add(addDrivingTime.mins, 'minutes');
                                                drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addDrivingTime.hrs+":"+addDrivingTime.mins});
                                                from = moment(to);
                                                driving  = fromTime(driving) + fromTime(drivingTime); driving = toTime(driving);
                                            }
                                        }
                                        
                                        totalHrs = fromTime(tempDrivingTime) + fromTime(totalHrs); totalHrs = toTime(totalHrs);
                                    }

                                    if(!lastDayExists){
                                        //On Duty Hours for unload the load
                                        var timeLeftToday = subTime(dailyDrivingLimit,totalHrs);
                                        var dutyTime = 0;
                                        if( timeLeftToday >= unloadTime && unloadTime > 0 ){
                                            dutyTime = 2;
                                            unloadTime = subTime(unloadTime, dutyTime);
                                        }else if( timeLeftToday > 0 && timeLeftToday < unloadTime && unloadTime > 0){
                                            dutyTime = timeLeftToday;
                                            unloadTime = subTime(unloadTime, timeLeftToday); 
                                        }

                                        if(dutyTime > 0 ){
                                            if(addTasksToGantt){
                                                from = moment(to); 
                                                var addOnDutyTime = makeHours(dutyTime);
                                                to = moment(from).add(addOnDutyTime.hrs, 'hours');
                                                to = moment(to).add(addOnDutyTime.mins, 'minutes');
                                                onDutyList.push({ name: "On Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addOnDutyTime.hrs+":"+addOnDutyTime.mins});
                                                from = moment(to);
                                                onDuty = fromTime(onDuty) + fromTime(dutyTime); onDuty = toTime(onDuty);
                                            }
                                            totalHrs = fromTime(dutyTime) + fromTime(totalHrs);
                                            totalHrs = toTime(totalHrs);
                                            
                                        } //End onduty hours
                                    }
                                    if(totalHrs >= dailyDrivingLimit ){
                                        if(addTasksToGantt){
                                            var timeLeftToday = subTime(23.15,totalHrs);
                                            if(timeLeftToday >= sleeperBirthTime){
                                                var sleepTime = sleeperBirthTime;
                                                var addSleeperBerthTime = makeHours(sleepTime);
                                                from = moment(to); to = moment(to).add(addSleeperBerthTime.hrs,'hours');to = moment(to).add(addSleeperBerthTime.mins, 'minutes');
                                                sleeperBerthList.push({ name: "Sleeper Berth" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addSleeperBerthTime.hrs + ":"+addSleeperBerthTime.mins});
                                                from = moment(to);
                                                SB = fromTime(SB) + fromTime(sleepTime); SB = toTime(SB);
                                                totalHrs = fromTime(sleepTime) + fromTime(totalHrs);
                                                totalHrs = toTime(totalHrs);
                                                
                                            }
                                            timeLeftToday = subTime(23.15,totalHrs);                                            
                                            var addOffDutyTime = makeHours(timeLeftToday);
                                            from = moment(to); to = moment(to).add(addOffDutyTime.hrs,'hours');to = moment(to).add(addOffDutyTime.mins, 'minutes');
                                            offDutyList.push({ name: "Off Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:addOffDutyTime.hrs + ":"+addOffDutyTime.mins});
                                            from = moment(to);
                                            offDuty = timeLeftToday;
                                        }
                                    }
                                }
                            }  // if(!value.deleted)
                        }   // if(!skipMe)
                    } //for (var i = 0 ; date <= limitToDate; date.setDate(date.getDate() + 1), i++) {
                }); //angular.forEach(data, function(value, key) {


                vlog =  [   
                            {   name   : "off Duty "   ,    tasks: offDutyList     },
                            {   name   : "Sleeper Berth",   tasks: sleeperBerthList},
                            {   name   : "Driving"     ,    tasks: drivingList     },
                            {   name   : "On Duty"     ,    tasks: onDutyList      }
                            
                        ];

                var total = fromTime(onDuty) + fromTime(offDuty) + fromTime(SB) + fromTime(driving);
                total = toTime(total);
                var totals = { "offDuty" : offDuty, "onDuty" : onDuty, "SB" : SB, "driving" : driving, "thours" : total};
                return {vlog:vlog, totals:totals};
            },




            //Show Hours of service for assigned load page
            fetchEastimateHOS: function(hos,hosFor){
                var vlog  = [] , onDutyList = [], offDutyList = [], drivingList = [],  sleeperBerthList  = [];
                var hosLastDate = new Date(hos.endDate);            hosLastDate.setHours(0, 0, 0, 0);
                var hosStartDate= new Date(hos.startDate);          hosStartDate.setHours(0, 0, 0, 0);
                var hosForDate  = new Date(hosFor.startOf('day'));  hosForDate.setHours(0, 0, 0, 0);
                var color       = '#077ED0';
                var offDuty     = 0, onDuty = 0 , SB = 0, driving = 0, hoursRemainingNextDay = 0, hosStatus = 0, thours = 0;
                var origin      = '', destination = '';
                var holiday = ["01/01","04/15","05/28","07/03","09/03","11/22","12/24","12/30"]; //m-d-y format
                var currentYear = hosFor.format('YYYY');
                var skipMe      = false;
                origin      = capitalizeMe(hos.originCity.toLowerCase())+', '+hos.OriginState.toUpperCase();
                destination = capitalizeMe(hos.DestinationCity.toLowerCase())+', '+hos.DestinationState.toUpperCase();

                
                if(moment(hosLastDate).isSame(hosFor, 'day') && moment(hosLastDate).isSame(hosFor, 'month') && moment(hosLastDate).isSame(hosFor, 'year') && hos.lastDayHours > 0 ){
                    var from = hosFor;
                    // var to = moment(from).add('1', 'hours');
                    // to = moment(to).add('15', 'minutes');
                    // onDutyList[0] = { name: "On Duty" , color:color, from: hosFor,  to: to, origin:origin,  destination:destination, hrs:'1:15'};
                    // onDuty = 1.15;
                    var hours = Math.floor(hos.lastDayHours);
                    var n = Math.abs(hos.lastDayHours); // Change to positive
                    var mins = (n - Math.floor(n)).toFixed(2);
                    mins = mins * 100;
                    var xtraHours = Math.floor(mins / 60);
                    mins = (mins % 60);
                    hours += xtraHours;
                    var to = moment(hosFor).add(hours,'hours'); to = moment(to).add(mins, 'minutes');
                    drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:hours+":"+mins});
                    driving = hours+"."+mins;
                }else if(hosForDate >= hosStartDate && hosForDate<= hosLastDate){

                    for (var i = 0; i < holiday.length; i++) { 
                        var hday = holiday[i] + '/' + currentYear;
                        hday = new Date(hday); hday.setHours(0, 0, 0, 0);
                        if(hosForDate>=hday && hosForDate <= hday){
                            skipMe = true;
                            break;
                        }
                    }

                    if(!skipMe){
                        var from = hosFor;
                        var to = moment(from).add('1', 'hours');
                        to = moment(to).add('15', 'minutes');
                        SB = 0;
                        onDutyList[0] = { name: "On Duty" , color:color, from: hosFor,  to: to, origin:origin,  destination:destination, hrs:'1:15'};
                        onDuty = 1.15;
                        from = moment(to); to = moment(to).add('8','hours'); 
                        drivingList.push({ name: "Driving" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:'8:00'});
                        driving = 8;
                        from = moment(to); to = moment(to).add('14','hours');to = moment(to).add('44', 'minutes');
                        offDutyList.push({ name: "Off Duty" , color:color, from: from,  to: to, origin:origin,  destination:destination, hrs:'14:45'});
                        offDuty = 14.45;    
                    }
                }

                
                var total = parseFloat(parseFloat(onDuty) + parseFloat(offDuty) + parseFloat(SB) + parseFloat(driving)).toFixed(2);
                var hours = Math.floor(total);
                var n = Math.abs(total); // Change to positive
                var mins = (n - Math.floor(n)).toFixed(2);
                mins = mins * 100;
                var xhrs = Math.floor(mins / 60);
                var thours = total;
                if(xhrs > 0){
                    thours = parseFloat(hours) + parseFloat(xhrs);
                }

                vlog =  [   
                            {   name   : "off Duty "   ,    tasks: offDutyList     },
                            {   name   : "Sleeper Berth",   tasks: sleeperBerthList},
                            {   name   : "Driving"     ,    tasks: drivingList     },
                            {   name   : "On Duty"     ,    tasks: onDutyList      }
                            
                        ];
                var totals = { "offDuty" : offDuty, "onDuty" : onDuty, "SB" : SB, "driving" : driving, "thours" : thours};
                return {vlog:vlog, totals:totals};
            },
            getSampleTimespans: function() {
                return [
                        {
                            from: new Date(2016, 12, 2, 8, 0, 0),
                            to: new Date(2016, 12, 2, 15, 0, 0),
                            name: 'Sprint 1 Timespan'
                            //priority: undefined,
                            //classes: [],
                            //data: undefined
                        }
                    ];
            },
            
        };


    });

function capitalizeMe(val){
    return val.charAt(0).toUpperCase()+val.substr(1).toLowerCase();
}

function makeHours(time) {
    
    time = parseFloat(time).toFixed(2);
    var timeArray = time.toString().split('.');
    var hours = parseInt(timeArray[0]);
    var minutes = parseInt(timeArray[1]);
    return {hrs: hours, mins: minutes};
}


function subtractTime(consumedTime,from){
    
    var realTime = '8:00';
    if(from == "team"){
        realTime = '11:00';
    }

    var onDutyHours = 0.00;
    var duration = moment.duration({hours: consumedTime.hrs, minutes: consumedTime.mins});
    var h = moment(realTime, 'HH:mm').subtract(duration).format( "h");
    if(h == 12 && from != "team")
        onDutyHours = moment(realTime, 'HH:mm').subtract(duration).format( "0.mm");
    else
        onDutyHours = moment(realTime, 'HH:mm').subtract(duration).format( "h.mm");
    return parseFloat(onDutyHours).toFixed(2);
}

function subTime(realTime,consumedTime){
    var x = parseFloat(realTime).toFixed(2);
    var y = parseFloat(consumedTime).toFixed(2);
    var z = fromTime(x) - fromTime(y);
    z = toTime(z);
    z = parseFloat(z).toFixed(2);
    return z;
}


function fromTime(time) {
    if(time > 0){
        time = parseFloat(time).toFixed(2);
        var timeArray = time.toString().split('.');
        var hours = parseInt(timeArray[0]);
        var minutes = parseInt(timeArray[1]);
        return (hours * 60) + minutes;    
    }else
    return 0;
    
}

function toTime(number) {
    var hours = Math.floor(number / 60);
    var minutes = number % 60;

    return hours + "." + (minutes <= 9 ? "0" : "") + minutes;
}


/*
function getDrivingHoursOfService(totalHours ){
    var th = 1, tdays = 0, dailyDriving = [], day = 0,hoursLimitInWeek = 70, daysInWeek = 8, dailyDrivingLimit = 11;
    var driving = [];
    for (var i=1; i <= daysInWeek ; i++) { 
        if(totalHours > hoursLimitInWeek ){
            totalHours -= hoursLimitInWeek;       
            th++;
        }
        i = totalHours == hoursLimitInWeek ?  daysInWeek : i;
        var parts = getParts(totalHours, i);
        var drivingHours = getUnequalParts(parts);   
        if(drivingHours.length != 0 && Math.max.apply(Math,drivingHours) <= dailyDrivingLimit){
            tdays += drivingHours.length;
            dailyDriving[day++] = drivingHours;
            driving = drivingHours;
            break;
        }
    }


    totalHours = hoursLimitInWeek;
    for (var ti=1; ti <=th-1 ; ti++) { 
        var parts = getParts(totalHours, daysInWeek);
        var drivingHours = getUnequalParts(parts);   
        if(drivingHours.length != 0 && Math.max.apply(Math,drivingHours) <= dailyDrivingLimit){
            tdays += drivingHours.length;
            dailyDriving[day] = drivingHours;
            driving = $.merge(driving,drivingHours);
            break;
        }        
    }
    return {dailyDriving: driving, tdays:tdays};
}

function getParts(number, parts){
    var base = number / parts;
    var arr = [];
    for(var i = 1; i <= parts; i++) {
        arr[i-1] = Math.round(i * base);
    }
    return arr;
}

function getUnequalParts(arr){
    var temp = [];
    for(var i=arr.length-1;i>=0;i--){
        if(i != 0){
            temp[i] = arr[i]-arr[i-1];
        }else{
            temp[i] = arr[i];
        }
    }
    
    return temp;
}
*/