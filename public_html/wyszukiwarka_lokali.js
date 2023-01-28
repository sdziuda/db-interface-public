const MAX_ROWS = 10;

var form = document.getElementById('wyszukiwarka-lokali');
var service;
var nextAddress = 0;
var delay = 500;
var origin;
var destinations = [];
var id_map = new Map();
var all_results = [];
var table = document.getElementById('results');

function initMap() {
    const map = new google.maps.Map(document.getElementById("map"), {
      center: { lat: 21, lng: 37 },
      zoom: 10,
    });

    var test = fetch('https://students.mimuw.edu.pl/~sd438422/pobierz_lokale.php')
                    .then(response => response.json())
                    .then(data => {
                        return data;
                    });

    test.then(function(data) {
        for (var i = 0; i < data.length; i++) {
            destinations.push(data[i][2]);
            id_map.set(data[i][2], data[i][0]);
        }
    });
}
  
window.initMap = initMap;

form.addEventListener('submit', function(e) {
    e.preventDefault();
    service = new google.maps.DistanceMatrixService();
    var input = form.querySelector('input[type="text"]');
    
    origin = input.value;

    for (var i = table.rows.length - 1; i >= 0; i--) {
        table.deleteRow(i);
    }
    
    console.log(origin);
    theNext();
});

function getData(dests, next) {
    service.getDistanceMatrix({
        origins: [origin],
        destinations: dests,
        travelMode: google.maps.TravelMode.WALKING,
        unitSystem: google.maps.UnitSystem.METRIC,
    }, (response, status) => {
        if (status == "OK") {
            for (var i = 0; i < response.rows[0].elements.length; i++) {
                var element = response.rows[0].elements[i];
                if (element.status == "OK") {
                    var distance = element.distance.value;
                    var address = dests[i];
                    var id = id_map.get(address);
                    all_results.push({id: id, distance: distance, address: address});
                }
            }
        } else {
            if (status == "OVER_QUERY_LIMIT") {
                nextAddress -= 25;
                delay++;
            } else {
                console.log("Error: " + status);
            }
        }
        next();
    });

}

function theNext() {
    if (nextAddress < destinations.length) {
        var currentDests = [];
        for (var i = nextAddress; i < nextAddress + 25 && i < destinations.length; i++) {
            currentDests.push(destinations[i]);
        }
        setTimeout(getData(currentDests, theNext), delay);
        nextAddress += 25;
    } else {
        console.log("Done");
        console.log(all_results);
        all_results.sort(function(a, b) {
            return a.distance - b.distance;
        });
        
        if (all_results.length != 0) {
            var row = table.insertRow(0);
            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);
            cell1.innerHTML = "Numer";
            cell2.innerHTML = "Adres";
            cell3.innerHTML = "Odległość";
        }
        
        for (var i = 0; i < all_results.length && i < MAX_ROWS; i++) {
            var row = table.insertRow(i + 1);
            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);
            cell1.innerHTML = i + 1;
            cell2.innerHTML = "<a href='https://students.mimuw.edu.pl/~sd438422/wyniki.php?id=" + all_results[i].id + "'>" + all_results[i].address + "</a>";
            if (all_results[i].distance < 1000) {
                cell3.innerHTML = all_results[i].distance + " m";
            } else {
                cell3.innerHTML = (all_results[i].distance / 1000).toFixed(2) + " km";
            }
        }
        all_results.length = 0;
        nextAddress = 0;
        delay = 1000;
        return;
    }
}