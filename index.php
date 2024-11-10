<?php
session_start();

// Adatbázis kapcsolat
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'volan_app';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kapcsolódási hiba: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaposvári Útvonaltervező</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #001F3F;
            --accent-color: #FFC107;
            --text-light: #FFFFFF;
            --shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .header {
            position: relative;
            background-color: var(--primary-color);
            color: var(--text-light);
            padding: 1rem;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .search-panel {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            
        }

        .input-wrapper {
            flex: 1;
            min-width: 200px;
            padding-right: 30px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus, select:focus {
            border-color: var(--accent-color);
            outline: none;
        }

        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }

        button {
            padding: 12px 24px;
            background-color: var(--accent-color);
            color: var(--primary-color);
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s, background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        button:hover {
            background-color: #FFD700;
            transform: translateY(-2px);
        }

        .map-container {
            height: 500px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        .schedule-container {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .schedule-header {
            background-color: var(--primary-color);
            color: var(--text-light);
            padding: 15px;
        }

        #schedule {
            width: 100%;
            border-collapse: collapse;
        }

        #schedule th, #schedule td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        #schedule th {
            background-color: var(--primary-color);
            color: var(--text-light);
        }

        #schedule tr:hover {
            background-color: #f9f9f9;
        }

        .transport-type {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .transport-icon {
            font-size: 20px;
        }

        i {
            width: 40px;
            height: 25px;
        }

        nav {
            position: relative;
            background-color: var(--primary-color);
            padding: 8px;
            width: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            box-shadow: 0 2px 5px rgba(255, 255, 255, 0.3);
            transition: width 0.6s linear;
            margin-right: 10px;
            margin-top: 30px;
            max-height: 50px;
        }

        nav.active {
            width: 99%;
        }

        nav ul {
            display: flex;
            list-style-type: none;
            padding: 0;
            margin: 0;
            width: 0;
            transition: width 0.6s linear;
        }

        nav.active ul {
            width: 100%;
        }

        nav ul li {
            transform: rotateY(0deg);
            opacity: 0;
            transition: transform 0.6s linear, opacity 0.6s linear;
            padding: 15px;
        }

        nav.active ul li {
            opacity: 1;
            transform: rotateY(360deg);
        }

        nav ul a {
            position: relative;
            color: #000;
            text-decoration: none;
            margin: 0 5px;
        }

        .icon {
            background-color: var(--primary-color);
            border: 0;
            cursor: pointer;
            padding: 0;
            position: relative;
            height: 30px;
            width: 30px;
        }

        .icon:hover{
            background-color: var(--primary-color);
        }

        .icon:focus {
            outline: 0;
        }

        .icon .line {
            background-color: var(--text-light);
            height: 2px;
            width: 20px;
            position: absolute;
            top: 10px;
            left: 5px;
            transition: transform 0.6s linear;
        }

        .icon .line2 {
            top: auto;
            bottom: 10px;
        }

        nav.active .icon .line1 {
            transform: rotate(-765deg) translateY(5.5px);
        }

        nav.active .icon .line2 {
            transform: rotate(765deg) translateY(-5.5px);
        }

        .info-panel {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: var(--shadow);
        }

        @media (max-width: 768px) {
            .input-group {
                flex-direction: column;
            }
            
            .input-wrapper {
                width: 95%;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            button {
                width: 99%;
            }

            nav.active{
                width: 95%;
            }
        }
        .suggestions-list {
    list-style-type: none;
    padding: 0;
    margin: 0;
    background-color: white;
    border: 1px solid #ccc;
    position: absolute;
    width: 20%;
    z-index: 100;
    max-height: 150px;
    overflow-y: auto;
}

.suggestions-list li {
    padding: 8px;
    cursor: pointer;
}

.suggestions-list li:hover {
    background-color: #f0f0f0;
}
    </style>
</head>
<body>
    
    <div class="header">
        <nav class="active" id="nav">
            <ul>
              <li><a href="index.html" style="color: #FFFFFF; font-weight: bold;"><img src="placeholder.png" style="height: 30px; width: 30px;"> Főoldal</a></li>
              <li><a href="buy.html" style="color: #FFFFFF; font-weight: bold;"><img src="tickets.png" style="height: 30px; width: 30px;"> Jegyvásárlás</a></li>
              <li><a href="menetrend.html" style="color: #FFFFFF; font-weight: bold;"><img src="calendar.png" style="height: 30px; width: 30px;"> Menetrend</a></li>
              <li><a href="info.html" style="color: #FFFFFF; font-weight: bold;"><img src="information-button.png" style="height: 30px; width: 30px;"> Információ</a></li>
            </ul>
            <button class="icon" id="toggle">
              <div class="line line1"></div>
              <div class="line line2"></div>
            </button>
          </nav>
        <div class="container">
            <h1><i class="fas fa-map-marked-alt"></i> Kaposvári Útvonaltervező</h1>
        </div>
    </div>
    <div class="container">
        <div class="search-panel">
            <div class="input-group">
                <div class="input-wrapper">
                    <input id="start" type="text" placeholder="Kezdőpont" value="Kaposvár" oninput="filterOptions('start')">
                    <ul id="start-suggestions" class="suggestions-list"></ul>
                </div>
                <div class="input-wrapper">
                    <input id="end" type="text" placeholder="Célpont" value="Kaposvár, Autóbusz állomás" oninput="filterOptions('end')">
                    <ul id="end-suggestions" class="suggestions-list"></ul>
                </div>
                <div>
                    <button id="switchBtn">
                        <img src="switch.png" alt="Switch" style="width: 20px; height: 20px;">
                    </button>
                </div>
            </div>
            <div class="button-group">
                <button id="routeBtn">
                    <i class="fas fa-route"></i>
                    Útvonal keresése
                </button>
            </div>
        </div>
    </div>

        <div class="map-container">
            <div id="map"></div>
        </div>

        <div class="schedule-container">
            <div class="schedule-header">
                <h2><i class="fas fa-clock"></i> Menetrend</h2>
            </div>
            <table id="schedule">
                <thead>
                    <tr>
                        <th>Járat</th>
                        <th>Indulás</th>
                        <th>Érkezés</th>
                        <th>Időtartam</th>
                        <th>Megállók</th>
                    </tr>
                </thead>
                <tbody id="schedule-body"></tbody>
            </table>
        </div>

        <div class="info-panel">
            <h3>Népszerű úticélok Kaposváron</h3>
            <div id="popularDestinations">
                <!-- JavaScript tölti fel -->
            </div>
        </div>
    </div>

    <script>

/*const busStations = [
    "helyi autóbusz állomás", "Berzsenyi u. felűljáró", "Berzsenyi u. 30.", "Ballakúti u.",
    "Lonkahegy forduló", "Nyár", "Berzsenyi u. felűljáró", "Jókai liget", "Szigetvári u. 6.",
    "Szigetvári u. 62.", "Szigetvári u. 139.", "Kaposfüred vá.", "Bersenyi u. 30.", "Füredi u. csp.",
    "Toldi lakónegyed", "Kinizsi ltp.", "Búzavirág u.", "Laktanya", "Volán-telep", "Kaposfüredi u. 12.",
    "Kaposfüredi u. 104.", "Kaposfüred központ", "Állomás u.", "Kaposfüredi u. 244.", "Kaposfüred forduló",
    "Városi könyvtár", "Vasútköz", "Raktár u. forduló", "Mátyás k. u. forduló", "Egyenesi u. forduló",
    "Koppány vezér u. forduló", "Töröcske forduló", "Béla király u. forduló", "Kaposszentjakab forduló",
    "Toponár forduló", "NABI forduló", "Kaposvári Egyetem", "Videoton", "Buzsáki u.", "Aranytér",
    "Sopron u. forduló", "Tóth Árpád u.", "Kométa forduló", "67-es sz. út", "Rózsa u.", "Erdősor u.",
    "Gönczi F. u.", "Városi Fürdő", "Hajnóczy u. csp.", "Jutai u. 24.", "Jutai u. 45.", "Raktár u. 2.",
    "Kecelhegyalja u. 6.", "Kőrösi Cs. S. u. 109.", "Kecelhegyi iskola", "Kőrösi Cs. S. u. 45.",
    "Kenese tér", "Eger u.", "Kapoli A. u.", "Egyenesi u. 42.", "Beszédes J. u.", "Állatkorház", "Kölcsey u.",
    "Tompa M. u.", "Vasútállomás", "Baross G. u.", "Csalogány u.", "Vikár B. u.", "Fő u. 48.", "Fő u. 37-39.",
    "Hársfa u.", "Hősök temploma", "Gyár u.", "Pécsi úti iskola", "Nádasdi u.", "Móricz Zs. u.", "Pécsi u. 227.",
    "Várhegy feljáró", "Nap u", "Hold u.", "Magyar Nobel-díjasok tere", "Bartók B. u.", "Táncsics M. u.",
    "Zichy M. u.", "Aranyeső u.", "Jókai u.", "Szegfű u.", "Gyertyános", "Kertbarát felső", "Kertbarát alsó",
    "Szőlőhegy", "Fenyves u. 37/A", "Fenyves u. 31", "Kórház célgazdaság", "Fenyves u. 63.", "Mező u. csp.",
    "Izzó u.", "Guba S. u. 81.", "Guba S. u. 57.", "Villamossági Gyár", "Toponár posta", "Toponár Orci elágazás",
    "Toponári u. 182.", "Toponári u. 238.", "Erdei F. u.", "Szabó P. u.", "Orci út 14.", "Répáspuszta",
    "Kenyérgyár u. 1.", "Kenyérgyár u. 3.", "Dombóvári u. 4.", "Kaposvári Egyetem forduló", "Virág u.",
    "Pázmány P. u.", "Vöröstelek u.", "Hegyi u.", "Tallián Gy. u. 4.", "Kórház", "Tallián Gy. u. 56.",
    "Tallián Gy. u. 82.", "ÁNTSZ", "Rendőrség", "Szent Imre u. 29.", "Szent Imre u. 13.", "Széchenyi tér",
    "Zárda u.", "Honvéd u.", "Arany J. tér", "Losonc-köz", "Brassó u.", "Nagyszeben u.", "Somssich P. u.",
    "Pázmány P. u.", "Kisgát", "Arany J. u", "Rózsa u.", "Corso"
];

// Function to filter options based on input
function filterOptions(id) {
    const input = document.getElementById(id);
    const suggestionBox = document.getElementById(id + '-suggestions');
    const query = input.value.toLowerCase();
    
    // Clear previous suggestions
    suggestionBox.innerHTML = '';
    
    if (query.length > 0) {
        const filteredStations = busStations.filter(station => station.toLowerCase().includes(query));
        
        filteredStations.forEach(station => {
            const li = document.createElement('li');
            li.textContent = station;
            li.onclick = function() {
                input.value = station;
                suggestionBox.innerHTML = '';
            };
            suggestionBox.appendChild(li);
        });
    }
}

// Optional: Close suggestions list when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.search-panel')) {
        document.querySelectorAll('.suggestions-list').forEach(list => list.innerHTML = '');
    }
});

*/
        // Switch button logic
document.getElementById("switchBtn").onclick = function() {
    const startInput = document.getElementById("start");
    const endInput = document.getElementById("end");

    // Swap values
    const temp = startInput.value;
    startInput.value = endInput.value;
    endInput.value = temp;
};

// Navbar script
const toggle = document.getElementById('toggle');
const nav = document.getElementById('nav');

toggle.addEventListener('click', () => nav.classList.toggle('active'));

let map, directionsService, directionsRenderer;
const kaposvarBusStops = [
{name: "helyi autóbusz állomás", lat: 46.353712944816756 , lng: 17.790623009204865},             
        {name: "Berzsenyi u. felűljáró", lat: 46.356517550424560 , lng: 17.785293459892273},        
        {name: "Berzsenyi u. 30.",  lat: 46.360245694362280 , lng: 17.783764600753784},                                            
        {name: "Ballakúti u.", lat: 46.341454000000000 , lng: 17.800144000000000},                                                                                                                   
        {name: "Lonkahegy forduló", lat: 46.341060000000000 , lng: 17.809980000000000},                                                           
        {name: "Nyár .", lat: 46.340230000000000 , lng:   17.806737000000000     },                               
        {name: "Berzsenyi u. felűljáró   ", lat: 46.355407000000000 , lng: 17.784772000000000},                                                 
        {name: "Jókai liget", lat: 46.351217000000000 , lng:   17.791028000000000},         
        {name: "Szigetvári u. 6. ", lat: 46.349016000000000 , lng:    17.794155000000000},                                                                             
        {name: "Szigetvári u. 62.   ", lat: 46.345504000000000 , lng: 17.796827000000000},                                                                                     
        {name: "Szigetvári u. 139.", lat: 46.339967000000000 , lng:    17.801641000000000    } ,                                                                                          
        {name: "Kaposfüred vá.", lat: 46.414177188967860 , lng:    17.759399414062500    } ,                                                                                      
        {name: "Bersenyi u. 30. ", lat: 46.360245694362280 , lng:    17.783764600753784    } ,                                                                                           
        {name: "Füredi u. csp.", lat: 46.364636210963700 , lng:    17.782359123229980    } ,                                                                                      
        {name: "Toldi lakónegyed", lat: 46.367282937091694 , lng:    17.782611250877380    } ,                                                                                          
        {name: "Kinizsi ltp.", lat: 46.371837596984186 , lng:    17.782562971115112    } ,                                                                                            
        {name: "Búzavirág u.", lat: 46.376005120381800 , lng:    17.781790494918823    } ,                                                                                                
        {name: "Laktanya", lat: 46.378899252341710 , lng:    17.781264781951904    } ,                                                                                           
        {name: "Volán-telep", lat: 46.390476096295025 , lng:    17.779242396354675    } ,                                                                                     
        {name: "Kaposfüredi u. 12.", lat: 46.402831025305830 , lng:    17.779363095760345    } ,                                                                                   
        {name: "Kaposfüredi u. 104.", lat: 46.408939937748370 , lng:    17.779510617256165    } ,                                        
        {name: "Kaposfüred központ", lat: 46.413452288702540 , lng:    17.777397036552430    } ,                                                
        {name: "Állomás u.", lat: 46.413161956046970 , lng:    17.762910425662994    } ,                        
        {name: "Kaposfüredi u. 244.", lat: 46.419606237670900 , lng:    17.776479721069336    } ,                         
        {name: "Kaposfüred forduló", lat: 46.422991722931310 , lng:    17.775487303733826    } ,                            
        {name: "Városi könyvtár", lat: 46.363116599065280 , lng:    17.776970565319060    } ,                                                            
        {name: "Vasútköz", lat: 46.363973583932410 , lng: 17.770326733589172       } ,                     
        {name: "Raktár u. forduló", lat: 46.379363705526960 , lng:    17.769232392311096    } ,                                                
        {name: "Mátyás k. u. forduló", lat: 46.366068790536570 , lng:    17.758959531784058    } ,                       
        {name: "Egyenesi u. forduló", lat: 46.339085936499174 , lng:    17.763682901859283    } ,                    
        {name: "Koppány vezér u. forduló", lat: 46.345209389542640 , lng:    17.771404981613160    } ,                            
        {name: "Töröcske forduló", lat: 46.313599675140970 , lng:    17.779864668846130    } ,                      
        {name: "Béla király u. forduló", lat: 46.348434712544030 , lng:    17.815065979957580    } ,                     
        {name: "Kaposszentjakab forduló", lat: 46.359686674094590 , lng:    17.847394645214080    } ,                             
        {name: "Toponár forduló", lat: 46.407843232130425 , lng:    17.836671173572540    } ,                                
        {name: "NABI forduló", lat: 46.365024899057650 , lng:    17.848915457725525    } ,                           
        {name: "Kaposvári Egyetem", lat: 46.383808193175200 , lng:    17.825261056423187    } ,                                    
        {name: "Videoton", lat: 46.364040217181720 , lng:    17.820736169815063    } ,                                  
        {name: "Buzsáki u.", lat: 46.367928866868034 , lng:    17.792299389839172    } ,                                    
        {name: "Aranytér", lat: 46.367667904754760 , lng:    17.790201902389526    } ,                          
        {name: "Sopron u. forduló ", lat: 46.375490674220465 , lng:    17.785727977752686    } ,                               
        {name: "Tóth Árpád u.", lat: 46.371870908822820 , lng:    17.767518460750580    } ,             
        {name: "Kométa forduló", lat: 46.356947021875070 , lng:    17.821197509765625    } ,                                
        {name: "67-es sz. út", lat: 46.351163683366990 , lng:    17.782756090164185    } ,                                    
        {name: "Rózsa u.", lat: 46.346277729433150 , lng:    17.779365777969360    } ,                                  
        {name: "Erdősor u.", lat: 46.345687090029300 , lng:    17.773953080177307    } ,                                
        {name: "Gönczi F. u.", lat: 46.344759458522690 , lng:    17.774610221385956    } ,                                
        {name: "Városi Fürdő", lat: 46.351209967314425 , lng:    17.799356281757355    } ,                            
        {name: "Hajnóczy u. csp.", lat: 46.366816530326750 , lng:    17.765412926673890    } ,                                
        {name: "Jutai u. 24.", lat: 46.370247858398170 , lng:    17.768795192241670    } ,                                
        {name: "Jutai u. 45.", lat: 46.376943324463234 , lng:    17.763591706752777    } ,                                
        {name: "Raktár u. 2.", lat: 46.378262704506820 , lng:    17.763543426990510    } ,                         
        {name: "Kecelhegyalja u. 6.", lat: 46.363492341385500 , lng:    17.761067748069763    } ,                       
        {name: "Kőrösi Cs. S. u. 109.", lat: 46.358611191864384 , lng:    17.760450839996338    } ,             
        {name: " Kecelhegyi iskola", lat: 46.353500048084380 , lng:    17.765681147575380    } ,                        
        {name: "Kőrösi Cs. S. u. 45.", lat: 46.352144894660360 , lng:    17.762121856212616    } ,                                  
        {name: "Kenese tér", lat: 46.348660589113850 , lng:    17.763318121433258    } ,                                     
        {name: "Eger u.", lat: 46.348264378775690 , lng:    17.768146097660065    } ,                                
        {name: "Kapoli A. u.", lat: 46.347679315267825 , lng:    17.763312757015230    } ,                            
        {name: "Egyenesi u. 42.", lat: 46.345140881766255 , lng:    17.763232290744780    } ,                              
        {name: "Beszédes J. u.", lat: 46.341809819016056 , lng:    17.763240337371826    } ,                                 
        {name: "Állatkorház", lat: 46.352109719465574 , lng:    17.771563231945038    } ,                                  
        {name: "Kölcsey u.", lat: 46.352794706028230 , lng:    17.774274945259094    } ,                                 
        {name: "Tompa M. u.", lat: 46.353674068084040 , lng:    17.778663039207460    } ,                                
        {name: "Vasútállomás", lat: 46.352903932821600 , lng:    17.796105444431305    } ,                                
        {name: "Baross G. u.", lat: 46.352929851011720 , lng:    17.800327241420746    } ,                                
        {name: "Csalogány u.", lat: 46.351143318417705 , lng:    17.808754742145540    } ,                                
        {name: "Vikár B. u.", lat: 46.350356485021180 , lng:    17.812102138996124    } ,                                                                     
        {name: "Fő u. 48.", lat: 46.356810035658340 , lng:    17.798160016536713    } ,                                
        {name: "Fő u. 37-39.", lat: 46.356717477209365 , lng:    17.795362472534180    } ,                                   
        {name: "Hársfa u.", lat: 46.357235802504250 , lng:    17.802070677280426    } ,                              
        {name: "Hősök temploma", lat: 46.357913320297510 , lng:    17.807663083076477    } ,                                     
        {name: "Gyár u.", lat: 46.356928510244230 , lng:    17.814457118511200    } ,                            
        {name: "Pécsi úti iskola", lat: 46.356136206566090 , lng:    17.818520665168762    } ,                                  
        {name: "Nádasdi u.",  lat: 46.355023629907160 , lng:    17.825062572956085    } ,                               
        {name: "Móricz Zs. u.", lat: 46.353829574848916 , lng:    17.835308611392975    } ,                               
        {name: "Pécsi u. 227.", lat: 46.357420917489460 , lng:    17.839656472206116    } ,                             
        {name: "Várhegy feljáró", lat: 46.359329416424664 , lng:    17.843674421310425    } ,                                      
        {name: "Nap", lat: 46.350647151854060 , lng:    17.828941047191620    } ,                                          
        {name: "Hold u.", lat: 46.346305502288780 , lng:    17.834662199020386    } ,                
        {name: "Magyar Nobel-díjasok tere", lat: 46.348853138895365 , lng:    17.763251066207886    } ,                                
        {name: "Bartók B. u.", lat: 46.351437683765035 , lng:    17.790352106094360    } ,                              
        {name: "Táncsics M. u.", lat: 46.345585255004316 , lng:    17.787329256534576    } ,                                 
        {name: "Zichy M. u.", lat: 46.342183856188420 , lng:    17.788951992988586    } ,                                 
        {name: "Aranyeső u.", lat: 46.337669315816115 , lng:    17.790381610393524    } ,                                    
        {name: "Jókai u.", lat: 46.345764854829700 , lng:    17.787168323993683    } ,                                   
        {name: "Szegfű u.",  lat: 46.345566739524855 , lng:    17.783179879188538    } ,                                  
        {name: "Gyertyános", lat: 46.330263403253590 , lng:    17.789416015148163    } ,                             
        {name: "Kertbarát felső", lat: 46.325108859259660 , lng:    17.787289023399353    } ,                              
        {name: "Kertbarát alsó", lat: 46.320153890054550 , lng:    17.784829437732697    } ,                                  
        {name: "Szőlőhegy", lat: 46.312925313409810 , lng:    17.786090075969696    } ,                            
        {name: "Fenyves u. 37/A", lat: 46.307659818792430 , lng:    17.782949209213257    } ,                               
        {name: "Fenyves u. 31", lat: 46.305945922572850 , lng:    17.783346176147460    } ,                          
        {name: "Kórház célgazdaság", lat: 46.313577443568010 , lng:    17.779859304428100    } ,                              
        {name: "Fenyves u. 63.", lat: 46.308354626297070 , lng:    17.782509326934814    } ,                                
        {name: "Mező u. csp.", lat: 46.364166079764190 , lng:    17.813687324523926    } ,                                     
        {name: "Izzó u.", lat: 46.366396390664846 , lng:    17.815342247486115    } ,                              
        {name: "Guba S. u. 81.", lat: 46.373641958986180 , lng:    17.821240425109863    } ,                              
        {name: "Guba S. u. 57.", lat: 46.368658073539770 , lng:    17.817276120185852    } ,                           
        {name: "Villamossági Gyár", lat: 46.377739027323290 , lng:  17.823745608329773      } ,   
        {name: "Toponár  posta", lat: 46.390649998518434 , lng:    17.827809154987335    } ,                                                                                         
        {name: "Toponár  Orci elágazás", lat: 46.3942925677315   , lng:    17.833487391471863    } ,                                         
        {name: "Toponári u. 182.", lat: 46.401804483330140 , lng:    17.834061384201050    } ,                                        
        {name: "Toponári u. 238.", lat: 46.405344584373870 , lng:    17.835268378257750    } ,                                                                                      
        {name: "Erdei F. u.", lat: 46.396229375766644 , lng:    17.845348119735718    } ,                                                                                     
        {name: "Szabó P. u.", lat: 46.392585085872410 , lng:    17.844530045986176    } ,                                                                                  
        {name: "Orci út 14.", lat: 46.395408044363904 , lng:    17.841429412364960    } ,                                                                                  
        {name: "Répáspuszta", lat: 46.429838000000000 , lng:    17.840512000000000    } ,                                                                             
        {name: "Kenyérgyár u. 1.", lat: 46.362879676470875 , lng:    17.816699445247650    } ,                                                                             
        {name: "Kenyérgyár u. 3.", lat: 46.364739861392500 , lng:    17.818161249160767    } ,                                                                               
        {name: "Dombóvári u. 4.", lat: 46.363947670980195 , lng:    17.833637595176697    } ,                                                                    
        {name: "Kaposvári Egyetem forduló", lat: 46.384574192377820 , lng:    17.826073765754700    } ,                                                                                     
        {name: "Virág u.", lat: 46.358487167595270 , lng:    17.803862392902374    } ,                                                                                 
        {name: "Pázmány P. u.", lat: 46.360912068665720 , lng:    17.801375985145570    } ,                                                                               
        {name: "Vöröstelek u.", lat: 46.364267880170260 , lng:    17.799975872039795    } ,                                                                                     
        {name: "Hegyi u.", lat: 46.367684561948180 , lng:    17.797811329364777    } ,                                                                           
        {name: "Tallián Gy. u. 4.", lat: 46.357163607490010 , lng:    17.797277569770813    } ,                                                                                      
        {name: "Kórház", lat: 46.360229034900560 , lng:    17.797264158725740    } ,                                                                          
        {name: "Tallián Gy. u. 56.", lat: 46.362713089656480 , lng:    17.797266840934753    } ,                                                                          
        {name: "Tallián Gy. u. 82.", lat: 46.364639912768110 , lng:   17.797229290008545     } ,                                                                                  
        {name: "ÁNTSZ", lat: 46.365172969985040 , lng:    17.789059281349182    } ,                                                                                  
        {name: "Rendőrség", lat: 46.364528858526550 , lng:    17.793779969215393    } ,                                                                         
        {name: "Szent Imre u. 29.", lat: 46.361678389067265 , lng:    17.793796062469482    } ,                                                                                         
        {name: "Szent Imre u. 13.", lat: 46.360230885952110 , lng:    17.793844342231750    } ,                                                                                              
        {name: "Széchenyi tér", lat: 46.356919254426460 , lng:    17.794136703014374    } ,                                                                                                    
        {name: "Zárda u.",lat: 46.358837026377685 , lng:  17.787715494632720      } ,                                                      
        {name: "Honvéd u.", lat: 46.363140661458800 , lng: 17.787967622280120       },                                              
        {name: "Arany J. tér", lat: 46.366823933639840 , lng:    17.788404822349550},                                                                                  
        {name: "Losonc-köz", lat: 46.370183083435110 , lng:    17.787884473800660    } ,                                                                                     
        {name: "Brassó u.", lat: 46.372092987227674 , lng:    17.787409722805023    } ,                                                                                            
        {name: "Nagyszeben u.", lat: 46.373155249773944 , lng:    17.787109315395355    } ,                                                                            
        {name: "Somssich P. u.", lat: 46.360545563804600 , lng:    17.789102196693420    } ,                                                                                           
        {name: "Pázmány P. u.", lat: 46.365032302613560 , lng: 17.799077332019806       } ,                                                        
        {name: "Kisgát", lat: 46.365048960610670 , lng:    17.808749377727510    } ,                                                                  
        {name: "Arany J. u", lat: 46.366792469552290 , lng:    17.784512937068940    } ,                                                                               
        {name: "Rózsa u.", lat: 46.345850025674650 , lng:    17.778743505477905    } ,                     
        {name: "Corso", lat: 46.355392023023086 , lng: 17.785899639129640    } ,
        ];

const popularDestinations = [
    { name: "Kaposvári Egyetem", lat: 46.3629, lng: 17.8015 },
    { name: "Csiky Gergely Színház", lat: 46.3597, lng: 17.7968 },
    { name: "Rippl-Rónai Múzeum", lat: 46.3593, lng: 17.7941 },
    // More locations...
];

function initMap() {
    const kaposvar = { lat: 46.359997, lng: 17.796976 };
    
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 13,
        center: kaposvar,
        styles: [
            {
                featureType: "transit.station",
                elementType: "labels.icon",
                stylers: [{ visibility: "on" }]
            }
        ]
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: false,
        polylineOptions: {
            strokeColor: "#001F3F",
            strokeWeight: 5
        }
    });

    // Add markers for Kaposvár bus stops
    kaposvarBusStops.forEach(stop => {
        new google.maps.Marker({
            position: { lat: stop.lat, lng: stop.lng },
            map: map,
            title: stop.name
        });
    });

    // Add markers for popular destinations
    popularDestinations.forEach(dest => {
        const button = document.createElement("button");
        button.textContent = dest.name;
        button.onclick = () => {
            document.getElementById("end").value = dest.name;
        };
        destinationsDiv.appendChild(button);

        // Add marker for each destination
        new google.maps.Marker({
            position: { lat: dest.lat, lng: dest.lng },
            map: map,
            title: dest.name
        });
    });
}

document.getElementById("routeBtn").onclick = function() {
    const start = document.getElementById("start").value;
    const end = document.getElementById("end").value;

    const request = {
        origin: start,
        destination: end,
        travelMode: 'TRANSIT',
        transitOptions: {
            modes: ['BUS'],
            routingPreference: 'FEWER_TRANSFERS'
        },
        unitSystem: google.maps.UnitSystem.IMPERIAL
    };

    directionsService.route(request, function(result, status) {
        if (status == "OK") {
            directionsRenderer.setDirections(result);
            updateSchedule(result);
        } else {
            alert("Útvonal nem található: " + status);
        }
    });
};

function updateSchedule(result) {
    const scheduleBody = document.getElementById("schedule-body");
    scheduleBody.innerHTML = ''; // Clear existing schedule

    const legs = result.routes[0].legs;
    legs.forEach(leg => {
        leg.steps.forEach(step => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${step.travel_mode}</td>
                <td>${step.departure_time ? step.departure_time.text : 'N/A'}</td>
                <td>${step.arrival_time ? step.arrival_time.text : 'N/A'}</td>
                <td>${step.duration.text}</td>
                <td>${step.instructions || 'N/A'}</td>
            `;
            scheduleBody.appendChild(row);
        });
    });
}

window.onload = initMap;

    </script>

        <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyArXtWdllsylygVw5t_k-22sXUJn-jMU8k&libraries=places&callback=initMap&loading=async">
    </script>
</body>
</html>
