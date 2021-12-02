var base_url = document.mybaseurl,
    favicon,
    cantusers,
    trucks = [], // camiones de la empresa
    traffic = [], // trafico del mp
    drivers = [], // conductores de la empresa
    map,
    viajeComenzado = 0,
    uOptions,
    polyline,
    seguimiento,
    optionspoly = {
            color: '#1BDAE6',
            weight: 4,
            opacity: 0.8,
            smoothFactor: 1
        },
    truckIDSelected,
    predefinedTruck;

// ICONOS PARA MARCADORES DEL MAPA
blueIcon = L.icon({
    iconUrl: base_url + '/img/marker-blue-1x.png',
    shadowUrl: base_url + '/img/marker-shadow.png',
    iconSize: [25, 41], // size of the icon
    shadowSize: [41, 41],
    iconAnchor: [12, 40], // point of the icon which will correspond to marker's location
    shadowAnchor: [25, 41],
    popupAnchor: [0, -40] // point from which the popup should open relative to the iconAnchor 
});
greenIcon = L.icon({
    iconUrl: base_url + '/img/marker-orange-1x.png',
    shadowUrl: base_url + '/img/marker-shadow.png',
    iconSize: [25, 41], // size of the icon
    shadowSize: [41, 41],
    iconAnchor: [12, 40], // point of the icon which will correspond to marker's location
    shadowAnchor: [25, 41],
    popupAnchor: [0, -40] // point from which the popup should open relative to the iconAnchor    
});
traffIconSm = L.icon({
    iconUrl: base_url + '/img/marker-acqua-0x.png',
    iconSize: [13, 21], // size of the icon    
    iconAnchor: [6, 21], // point of the icon which will correspond to marker's location    
    popupAnchor: [0, -20] // point from which the popup should open relative to the iconAnchor
});

function initializeMap() {
    var tiles = L.tileLayer(base_url + '/map' + uOptions['map_color'] + '/{z}/{x}/{y}.png', {
            minZoom: 0,
            maxZoom: 7,
            tms: true,
            //continuousWorld: true
        });
    map = L.map('map', {
            center: [-60, -40],
            zoom: 4,
            minZoom: 3,
            maxZoom: 7,
            zoomControl: null,
            CRS: L.CRS.simple,
            layers: [tiles]
        });
    // Add a new line to the map with no points.
    polyline = L.polyline([],optionspoly).addTo(map);
    var southWest = map.unproject([0, 40000], map.getMaxZoom()),
        northEast = map.unproject([19000, 14500], map.getMaxZoom());
    map.setMaxBounds(new L.LatLngBounds(southWest, northEast));
    L.control.zoom({
            position: 'bottomright'
        }).addTo(map);
    if (predefinedTruck !== 0) 
        truckIDSelected = predefinedTruck;
    else
        truckIDSelected = uOptions.follow_truck;
    seguimiento = (uOptions.tracer_on == 1)?true:false;
}

function getUserOptions() {
    $.getJSON(base_url + "/getinfo/driveroptions", function(d) {
        uOptions = d;
        uOptions.update_on = 1;
        uOptions.lifetime = uOptions.map_updatetime / 1000 * 5;
        uOptions.traffic_lifetime = uOptions.map_updatetime / 1000 * 2;
    });
}

function timeStamp() {
    return Date.now() / 1000 | 0;
}

function getDrivers() {
    $.getJSON(base_url + "/getinfo/listdrivers", function(d) {
        drivers = d;
    });
}

function getTraffic(s, x, y, z) {
    newTraffic = [];
    $.getJSON("http://tracker.ets2map.com/" + s + "/request/" + x + "/" + y + "/" + z, function(gTraffic) {
        $.each(gTraffic.Trucks, function(idx, ttruck) {
            isOurDriver = false;
            $.each(drivers, function(idx, driver) {
                if (parseInt(ttruck.ets2mp_id) === parseInt(driver.mp_id)) {
                    isOurDriver = true;
                    driverId = driver.id;
                }
            });
            if (isOurDriver !== true) {
                isNew = true;
                $.each(traffic, function(index, cT) {
                    if (ttruck.ets2mp_id === cT.ets2mp_id) {
                        isNew = false;
                        traffic[index].x = ttruck.x;
                        traffic[index].y = ttruck.y;
                        traffic[index].updated = timeStamp();
                    }
                });
                if (isNew) {
                    addTraffic = {};
                    addTraffic.x = ttruck.x;
                    addTraffic.y = ttruck.y;
                    addTraffic.ets2mp_id = ttruck.ets2mp_id;
                    addTraffic.id = ttruck.id;
                    addTraffic.name = ttruck.name;
                    addTraffic.updated = timeStamp();
                    drawTraffic(addTraffic);
                }
            }
        });
    });
}

function drawTraffic(t) {
    var marker = L.marker(map.unproject([0, 0], map.getMaxZoom()), {
        icon: traffIconSm
    }).addTo(map);
    popOpts = {
        'closeButton': false
    };
    marker.bindPopup(t.name + " (" + t.id + ")", popOpts);
    marker.on('mouseover', function(e) {
        this.openPopup();
    });
    marker.on('mouseout', function(e) {
        this.closePopup();
    });
    t.mark = marker;
    traffic.push(t);
}

function updateTrafficPos(t) {
    pos = {};
    pos.x = t.x;
    pos.y = t.y;
    pos = cdTranslate(pos);
    t.mark.setLatLng(map.unproject([parseInt(pos.x), parseInt(pos.y)], map.getMaxZoom()));
}

function updateTraffic() {
    $.each(traffic, function(index, t) {
        updateTrafficPos(t);
    });
}

function unloadTraffic() {
    $.each(traffic, function(index, truck) {
        map.removeLayer(truck.mark);
    });
    traffic = [];
}

function deleteIddleTraffic() {
    var deleteTraffic = [];
    $.each(traffic, function(index, truck) {
        if (truck.updated + uOptions.traffic_lifetime < timeStamp()) {
            deleteTraffic.push(truck);
        }
    });
    $.each(deleteTraffic, function(index, truck) {
        removeTraffic(truck);
    });
    deleteTrucks = undefined;
}

function removeTraffic(truck) {
    map.removeLayer(truck.mark);
    $.each(traffic, function(index, t) {
        if (truck.id === t.id) {
            idx = index;
            return false;
        }
    });
    traffic.splice(idx, 1);
}

function reqAliveTrucks() {
    $.getJSON(base_url + "/getinfo/getalive", function(result) {
        cantusers = 0;
        // Recorrer alivetrucks, actualizando o creando markers
        $.each(result, function(sIndex, s) {
            cantusers++;
            inMap = false;
            $.each(trucks, function(index, t) {
                if (s.id === t.id) {
                    t.posx = s.posx;
                    t.posz = s.posz;
                    //t.driverid = s.driverid;          
                    t.telemetry = s.telemetry;
                    t.speed = s.speed;
                    t.fuel_capacity = s.fuel_capacity;
                    t.fuel_load = s.fuel_load;
                    t.brand = s.brand;
                    t.model = s.model;
                    t.trailer_on = s.trailer_on;
                    t.trailer_name = s.trailer_name;
                    t.trailer_mass = s.trailer_mass;
                    t.job_income = s.job_income;
                    t.job_destination = s.job_destination;
                    t.job_source = s.job_source;
                    t.nav_distance = s.nav_distance;
                    t.nav_limit = s.nav_limit;
                    t.drivername = s.drivername;
                    t.fullname = t.fullname;
                    t.updated = timeStamp();
                    t.servername = s.servername;
                    t.servername = s.servername;
                    if (s.game === "ats"){
                        t.game = "American Truck Simulator";
                    }else{
                        t.game = "Euro Truck Simulator 2";
                    }
                    updateTruckPos(t);
                    inMap = true;                    
                }
            });
            if (!inMap) {
                var n = {};
                n.id = s.id;
                n.posx = s.posx;
                n.posz = s.posz;
                n.driverid = s.driverid;
                n.ets2mp_id = s.ets2mp_id;
                n.telemetry = s.telemetry;
                n.speed = s.speed;
                n.fuel_capacity = s.fuel_capacity;
                n.fuel_load = s.fuel_load;
                n.brand = s.brand;
                n.model = s.model;
                n.trailer_on = s.trailer_on;
                n.trailer_name = s.trailer_name;
                n.trailer_mass = s.trailer_mass;
                n.job_income = s.job_income;
                n.job_destination = s.job_destination;
                n.job_source = s.job_source;
                n.nav_distance = s.nav_distance;
                n.nav_limit = s.nav_limit;
                n.drivername = s.drivername;
                n.fullname = s.fullname;
                n.updated = timeStamp();
                n.servername = s.servername;
                if (s.game === "ats"){
                    n.game = "American Truck Simulator";
                }else{
                    n.game = "Euro Truck Simulator 2";
                }
                drawTruck(n);
            }
        });
        favicon.badge(cantusers);
    });
}

function deleteIddleTrucks() {
    var deleteTrucks = [];
    $.each(trucks, function(index, truck) {
        if (truck.updated + uOptions.lifetime < timeStamp()) {
            deleteTrucks.push(truck);
            console.log(truck);
            if (truckIDSelected == truck.id){
                map.removeLayer(polyline);
                polyline = L.polyline([],optionspoly);
                map.addLayer(polyline);
                truckIDSelected = 0;
            }
        }
    });
    $.each(deleteTrucks, function(index, truck) {
        removeTruck(truck);
    });
    deleteTrucks = undefined;
}

function drawTruck(truck) {
    var marker = L.marker(map.unproject([0, 0], map.getMaxZoom()), {
        icon: blueIcon
    }).addTo(map);
    popOpts = {
        'closeButton': false
    };
    marker.bindPopup(truck.drivername, popOpts);
    marker.on('mouseover', function(e) {
        this.openPopup();
    });
    marker.on('mouseout', function(e) {
        this.closePopup();
    });
    marker.on('click', function(e) {
        updateFollowTruck(truck.id);
    });
    truck.mark = marker;
    trucks.push(truck);
    updateTruckPos(truck);
}

function cdTranslate(pos) {
    if (pos.x < -31412 && pos.y < -5618) {
        // UK
        ppp = 9.69522;
        x0 = 10225;
        y0 = 23910;
    } else {
        //EUROPA
        ppp = 7.278;
        x0 = 11366;
        y0 = 24046;
    }
    pos.x = pos.x / ppp + x0;
    pos.y = pos.y / ppp + y0;
    return pos;
}

function updateTruckPos(truck) {
    if(truck.game=="Euro Truck Simulator 2"){ // Borrar cuando este el mapa del ATS
        truck.mark.setLatLng(map.unproject([parseInt(truck.posx), parseInt(truck.posz)], map.getMaxZoom()));
    }
}

function removeTruck(truck) {
    map.removeLayer(truck.mark);
    $.each(trucks, function(index, t) {
        if (truck.id === t.id) {
            idx = index;
            return false;
        }
    });
    trucks.splice(idx, 1);
}

function updateFollowTruck(truckID) {
    var selectedTruck = null;
    uOptions.follow_truck = truckID;
    truckID = String(truckID);
    // Reinicio la capa si selecciono el mapa
    if (truckID === "0"){
        map.removeLayer(polyline);
        polyline = L.polyline([],optionspoly);
        map.addLayer(polyline);
    }
    $.each(trucks, function(index, truck) {
        if (truck.id === truckID) {
            selectedTruck = truck;
            truck.mark.setIcon(greenIcon);
            // Si estoy viendo el mismo camión dibujo una línea, sino, remuevo la capa y empiezo una nueva.
            if(truckIDSelected == selectedTruck.id && seguimiento)
                polyline.addLatLng(L.latLng(map.unproject([parseInt(selectedTruck.posx), parseInt(selectedTruck.posz)], map.getMaxZoom())));
            else{
                map.removeLayer(polyline);
                polyline = L.polyline([],optionspoly);
                map.addLayer(polyline);
                truckIDSelected = selectedTruck.id;
            }
        } else {
            truck.mark.setIcon(blueIcon);
        }
    });
    // INFORMACION SOBRE CAMION SELECCIONADO EN EL SIDEBAR
    truckInfoW = $("#truckinfo-window");
    truckInfoW.empty();
    truckInfoU = $('<ul/>').appendTo(truckInfoW);
    if (selectedTruck) {
        var li = $('<li/>', {
                html: 'Conductor: ' + selectedTruck['drivername'],
                class: 'truck-info'
            }).appendTo(truckInfoU),
            li = $('<li/>', {
                html: 'Simulador: ' + selectedTruck['game'],
                class: 'truck-info'
            }).appendTo(truckInfoU),
            li = $('<li/>', {
                html: 'Servidor: ' + selectedTruck['servername'],
                class: 'truck-info'
            }).appendTo(truckInfoU);
        if (selectedTruck.telemetry == 1) {
            var li = $('<li/>', {
                    html: selectedTruck['brand'] + " " + selectedTruck['model'],
                    class: 'truck-info'
                }).appendTo(truckInfoU);
            // var li=$('<li/>',{ html: 'Velocidad: '+parseInt(selectedTruck['speed'])+ "Km/h", class: 'truck-info'}).appendTo(selectedTruckInfo);
            // var li=$('<li/>',{ text: 'Límite de velocidad: '+selectedTruck['nav_limit']+ "Km/h", class: 'truck-info'}).appendTo(selectedTruckInfo);
            //var li=$('<li/>',{ html: 'Combustible: '+parseInt(selectedTruck['fuel_load'])+"/"+parseInt(selectedTruck['fuel_capacity'])+" Lts", class: 'truck-info'}).appendTo(selectedTruckInfo);
            if (selectedTruck['trailer_on'] === "1") {
                var li = $('<li/>', {
                        html: 'Carga: ' + selectedTruck['trailer_name'],
                        class: 'truck-info'
                    }).appendTo(truckInfoU),
                    li = $('<li/>', {
                        html: 'Peso: ' + parseInt(selectedTruck['trailer_mass']) / 1000 + ' Ton',
                        class: 'truck-info'
                    }).appendTo(truckInfoU),
                    li = $('<li/>', {
                        html: 'Valor: $' + selectedTruck['job_income'],
                        class: 'truck-info'
                    }).appendTo(truckInfoU),
                    li = $('<li/>', {
                        html: 'Desde ' + selectedTruck['job_source'] + ' a ' + selectedTruck['job_destination'],
                        class: 'truck-info'
                    }).appendTo(truckInfoU),
                    li = $('<li/>', {
                        html: 'Distancia restante: ' + parseInt(selectedTruck['nav_distance'] / 1000) + ' Km',
                        class: 'truck-info'
                    }).appendTo(truckInfoU);
            }
            // Combustible
            fuelPercent = parseInt(selectedTruck['fuel_load'] / selectedTruck['fuel_capacity'] * 100);
            var fuelDiv = $('<div/>', {
                    class: 'progress'
                }).appendTo(truckInfoW),
                fuelBar = $('<div/>', {
                    class: 'progress-bar progress-bar-info',
                    role: 'progressbar',
                    style: 'width:' + fuelPercent + '%;',
                    text: parseInt(selectedTruck['fuel_load']) + 'lts.'
                }).appendTo(fuelDiv);
            // Velocidad
            if (selectedTruck['servername'] == "Europe #2" || selectedTruck['servername'] == "South America #1" || selectedTruck['servername'] == "Offline") {
                speedPercent = parseInt(selectedTruck['speed'] / 160 * 100);
            } else {
                speedPercent = parseInt(selectedTruck['speed'] / 110 * 100);
            }
            var speedDiv = $('<div/>', {
                    class: 'progress'
                }).appendTo(truckInfoW),
                speedBar = $('<div/>', {
                    class: 'progress-bar progress-bar-warning',
                    role: 'progressbar',
                    style: 'width:' + speedPercent + '%;',
                    text: parseInt(selectedTruck['speed']) + ' Km/h'
                }).appendTo(speedDiv);
        } else {
            var li = $('<li/>', {
                    html: '<i>Sin telemetry</i>',
                    class: 'truck-info'
                }).appendTo(truckInfoU);
        }
        if(selectedTruck['game']=="Euro Truck Simulator 2"){ // Borrar if cuando este el mapa del ATS
            map.panTo(map.unproject([parseInt(selectedTruck.posx), parseInt(selectedTruck.posz)], map.getMaxZoom()), {
                animate: true
            });
        }
    }
    if ((uOptions.truckinfo_on == 1) && (selectedTruck)) {
        $("#truckinfo-window").show();
    } else {
        $("#truckinfo-window").hide();
    }
}

function updateSidebar() {
    driverOnlineList = $("#drivers-online");
    driverOnlineList.empty();
    $.each(trucks, function(index, truck) {
        var li = $('<li/>').appendTo(driverOnlineList);
        var a = $('<a/>', {
            href: "javascript:updateFollowTruck("+truck.id+")",
            title: truck.fullname,
            text: truck.drivername,
        }).appendTo(li);
    });
    if ((trucks.length > 0) && (uOptions.driverlist_on == 1)) {
        $("#onlinedrivers-window").show();
    } else {
        $("#onlinedrivers-window").hide();
    }
}

function toggleOption(option) {
    uOptions[option] = 1 - uOptions[option];
}

function setToggleButtons() {
    // OPCIONES DE USUARIO  
    $('#truckinfo-window').hide();
    if (uOptions.topbar_on != 1) 
        $('#top-navbar').toggle("fast");
        $("#toggle-topbar").toggleClass("pressed");
    
    if (uOptions.driverlist_on != 1) 
        $("#toggle-online-drivers").toggleClass("pressed");
    
    if (uOptions.truckinfo_on != 1) 
        $("#toggle-truck-info").toggleClass("pressed");
    
  /*  if (uOptions.traffic_on == 1) 
        $("#toggle-traffic").toggleClass("pressed");*/
    
    if (uOptions.chat_on != 1) {
        $("#toggle-chatbox").toggleClass("pressed");
        $("#chatbox-window").toggle("fast");
    }
    if (uOptions.tracer_on != 1) {
        $("#toggle-routetracer").toggleClass("pressed");
    }
    // EVENTOS
    $("#toggle-map-options").on('click', function() {
        $('#map-options-overlay').toggleClass("toggled");
        $("#toggle-map-options").toggleClass("pressed");
    });
    // TOGGLE TOPBAR
    $("#toggle-topbar").on('click', function() {
        $('#top-navbar').toggle("fast");
        $("#toggle-topbar").toggleClass("pressed");
        toggleOption('topbar_on');
    });
    // TOGGLE ONLINE DRIVERS LIST
    $("#toggle-online-drivers").on('click', function() {
        $("#toggle-online-drivers").toggleClass("pressed");
        toggleOption('driverlist_on');
        updateSidebar();
    });
    // TOGGLE TRUCK INFO WINDOW
    $("#toggle-truck-info").on('click', function() {
        $("#toggle-truck-info").toggleClass("pressed");
        toggleOption('truckinfo_on');
        updateFollowTruck(uOptions.follow_truck);
    });
    // TOGGLE TRAFFIC
   /* $("#toggle-traffic").on('click', function() {
        $("#toggle-traffic").toggleClass("pressed");
        toggleOption('trafficOn');
    });*/
    // TOGGLE CHATBOX
    $("#toggle-chatbox").on('click', function() {
        $("#chatbox-window").toggle("fast");
        $("#toggle-chatbox").toggleClass("pressed");
        toggleOption('chatOn');
    });
    // TOGGLE RUTEO
    $("#toggle-routetracer").on('click', function() {
        $("#toggle-routetracer").toggleClass("pressed");
        seguimiento = !seguimiento;
        toggleOption('traceOn');
    });
}

function setMapOptions() {
    map.on('click', function(e) {
        updateFollowTruck(0);
    });
}

function doUpdate() {
    if (uOptions.update_on) {
        reqAliveTrucks();
        deleteIddleTrucks();
        updateSidebar();
        updateFollowTruck(truckIDSelected);
        /*
        if (uOptions.trafficOn == 1 && map.getZoom() > 1){
          mapViewPos = map.project(map.getCenter(),map.getMaxZoom());
          if (mapViewPos.x < 6700 && mapViewPos.y < 23000){     
            mapViewPos.x = parseInt(mapViewPos.x * 9.69) - 100000;
            mapViewPos.y = parseInt(mapViewPos.y * 9.69) - 230000;
          } else {        
            mapViewPos.x = parseInt(mapViewPos.x * 7.27) - 83000;
            mapViewPos.y = parseInt(mapViewPos.y * 7.27) - 175000;          
          }     
          getTraffic(1,mapViewPos.x,mapViewPos.y,2500);
          deleteIddleTraffic();
          updateTraffic();
        } else { 
          unloadTraffic();
        }
        */
        setTimeout(doUpdate, uOptions.map_updatetime);
    }
}

// Realiza la inicializacion cuando se hayan recibido las opciones del usuario.
function waitInitialize() {
    if (typeof uOptions != 'undefined') {
        //console.log(JSON.stringify(uOptions,null,2));
        initializeMap();
        setMapOptions();
        setToggleButtons();
        getDrivers();
        doUpdate();
        map.setZoom(uOptions.zoom_level);
    } else {
        setTimeout(waitInitialize, 100);
    }
}

$(function() {
    var array = window.location.href.split('/');
    if(array.length>4)
        if(array[3]==="follow")
            predefinedTruck = array.pop();
        else
            predefinedTruck = 0;
    getUserOptions();
    favicon = new Favico({
        animation:'none'
    });
    waitInitialize();
});