<?php
session_start();

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
    <title>Kaposvár Helyi Járatok</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        :root {
            --primary-color: #001F3F;
            --accent-color: #FFC107;
            --text-light: #FFFFFF;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            color: #333;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

/*--------------------------------------------------------------------------------------------------------HEADER--------------------------------------------------------------------------------------------------------*/

        .header {
            position: relative;
            background-color: var(--primary-color);
            color: var(--text-light);
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            text-align: center;
            align-items: left;
            gap: 1rem;
            padding: 16px;
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
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
            padding: 5px;
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

        .time {
            text-align: center;
            font-size: 1.5rem;
            color: var(--text-light);
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            animation: pulse 2s infinite;
        }

        .search-container {
            width: 100%;
            max-width: 700px;
            position: relative;
            align-content: center;
            margin: 1rem 0;
        }

        #searchBox {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--shadow);
            font-size: 1rem;
            transition: var(--transition);
            align-content: center;
        }

        #searchBox:focus {
            outline: none;
            transform: scale(1.02);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .search-icon {
            position: absolute;
            right: 1px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
        }
        
/*--------------------------------------------------------------------------------------------------------HEADER END----------------------------------------------------------------------------------------------------*/

/*--------------------------------------------------------------------------------------------------------CSS - OTHER PARTS----------------------------------------------------------------------------------------------*/
        #filterButtons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            padding: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .filter-button {
            background: white;
            border: none;
            color: #333;
            padding: 0.8rem 1.5rem;
            border-radius: 20px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow);
        }

        .filter-button:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
        }

        .filter-button.active {
            background: var(--accent-color);
            color: #000;
            transform: scale(1.05);
        }

        .route-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        #odaBtn, #visszaBtn{
            width: 100px;
            height: 40px;
            text-align: center;
            margin-top: 2%;
            margin-right: 13%;
        }

        .route-button {
            padding: 12px 28px;
            background-color: var(--accent-color);
            color: var(--primary-color);
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s, background-color 0.2s;
            display: flex;
            float: right;
            margin-right: 10px;
        }

        .route-button:hover {
            background-color: #FFD700;
            transform: translateY(-2px);
        }

        .route-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            transition: var(--transition);
            animation: fadeIn 0.5s ease-out;
        }

        .route-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .route-number {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .bus-icon {
            color: var(--accent-color);
            font-size: 1.5rem;
            animation: bounce 2s infinite;
        }

        .route-details {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .next-departure, .route-frequency {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .show-stops {
            color: var(--accent-color);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 10px;
            transition: var(--transition);
            text-align: center;
            margin-top: 1rem;
        }

        .show-stops:hover {
            background: rgba(0, 31, 63, 0.1);
        }

        .stops-list {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            line-height: 1.6;
            color: #000;
        }

        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 16px;
            background: #4CAF50;
            color: white;
            padding: 0.3rem 0.8rem;
            margin-bottom: 20px;
            border-radius: 15px;
            font-size: 0.8rem;
            animation: pulse 2s infinite;
        }
/*--------------------------------------------------------------------------------------------------------OTHER PARTS END------------------------------------------------------------------------------------------------*/

/*--------------------------------------------------------------------------------------------------------@MEDIA--------------------------------------------------------------------------------------------------------*/

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        @media (max-width: 768px) {
            .header-content {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .filter-button {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .route-container {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.5rem;
            }

            #filterButtons {
                gap: 0.5rem;
            }

            .filter-button {
                padding: 0.5rem 0.8rem;
                font-size: 0.8rem;
            }
        }
/*--------------------------------------------------------------------------------------------------------@MEDIA END----------------------------------------------------------------------------------------------------*/
        
    </style>
</head>
<body>
        <div class="header">
        <nav class="active" id="nav">
            <ul>
            
                <li><a href="index.php" style="color: #FFFFFF; font-weight: bold;"><img src="placeholder.png" style="height: 30px; width: 30px;"> Főoldal</a></li>
              <li><a href="buy.php" style="color: #FFFFFF; font-weight: bold;"><img src="tickets.png" style="height: 30px; width: 30px;"> Jegyvásárlás</a></li>
              <li><a href="menetrend.php" style="color: #FFFFFF; font-weight: bold;"><img src="calendar.png" style="height: 30px; width: 30px;"> Menetrend</a></li>
              <li><a href="info.php" style="color: #FFFFFF; font-weight: bold;"><img src="information-button.png" style="height: 30px; width: 30px;"> Információ</a></li>

            </ul>
            <button class="icon" id="toggle">
              <div class="line line1"></div>
              <div class="line line2"></div>
            </button>
          </nav>
          <div class="navh1">
                <h1><i class="fas fa-bus"></i> Kaposvár Helyi Járatok</h1>
                <div class="live-indicator">
                    <i class="fas fa-circle"></i> Élő követés
                </div>
                <div style="margin-right: 40%;margin-left: 40%" id="time" class="time"></div>
            </div>

            <div style="margin: 0 auto; align-items: center" class="search-container">
                <input type="text" id="searchBox" placeholder="Keress járatszám vagy útvonal alapján..." />
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
    
    <div id="filterButtons">
        <button class="filter-button active" data-filter="all">
            <i class="fas fa-globe"></i> Összes
        </button>
        <button class="filter-button" data-filter="downtown">
            <i class="fas fa-city"></i> Belváros
        </button>
        <button class="filter-button" data-filter="university">
            <i class="fas fa-graduation-cap"></i> Egyetem
        </button>
        <button class="filter-button" data-filter="industrial">
            <i class="fas fa-industry"></i> Ipari
        </button>
        <button class="filter-button" data-filter="suburban">
            <i class="fas fa-home"></i> Külváros
        </button>
        <button class="filter-button" data-filter="hospital">
            <i class="fas fa-hospital"></i> Kórház
        </button>
    </div>

    <div id="routeContainer" class="route-container"></div>

    <script>
        const toggle = document.getElementById('toggle')
        const nav = document.getElementById('nav')

        toggle.addEventListener('click', () => nav.classList.toggle('active'))
        const busRoutes = [
            {
                "number": "12",
                "name": "Helyi autóbusz-állomás - Sopron u. - Laktanya",
                "category": "residential",
                "stops": ["Helyi autóbusz-állomás","Corso","Zárda u.","Honvéd u.","Arany J. tér","Losonc-köz","Brassó u.","Sopron u.","Búzavirág u.","Laktanya"],
                "frequency": "15 percenként",
                "firstBus": "05:00",
                "lastBus": "22:30"
            },
            {
                "number": "13",
                "name": "Helyi autóbusz-állomás - Kecelhegy - Helyi autóbusz-állomás",
                "category": "residential",
                "stops": ["Helyi autóbusz-állomás","Berzsenyi u. felüljáró","Berzsenyi u. 30.","Füredi úti csomópont","Városi könyvtár","Vasútköz","Hajnóczy u. csp.","Mátyás k. u., forduló","Kecelhegyalja u.","Kőrösi Cs. S. u.","Kecelhegyi iskola"
                ,"Bthlen G. u.","Magyar Nobel-díjasok tere","Eger u.","Állatkórház","Kölcsey u.","Tompa M. u.","Berzsenyi u. felüljáró","Helyi autóbusz-állomás"],
                "frequency": "20 percenként",
                "firstBus": "05:30",
                "lastBus": "23:00"
            },
            {
                "number": "20",
                "name": "Raktár u. - Laktanya - Videoton",
                "category": "suburban",
                "stops": ["Raktár u.","Jutai u. 45.","Tóth Á. u.","Jutai u. 24.","Hajnóczy u. csp.","Vasútköz","Városi könyvtár","Füredi úti csomópont","Toldi lakónegyed","Kinizsi ltp.","Búzavirág u."
                ,"Laktanya","Laktanya","Búzavirág u.","Nagyszeben u.","Losonc-köz","Arany J. tér","ÁNTSZ","Pázmány P. u.","Kisgát","Mező u. csp.","Kenyérgyár u. 1.","Videoton"],
                "frequency": "30 percenként",
                "firstBus": "05:15",
                "lastBus": "21:45"
            },
            {
                "number": "21",
                "name": "Raktár u. - Videoton",
                "category": "residential",
                "stops": ["Raktár u.","Raktár u. 2.","Jutai u. 45.","Tóth Á. u.","Jutai u. 24.","Hajnóczy u. csp.","Vasútköz"
                ,"Városi könyvtár","Füredi úti csomópont","ÁNTSZ","Pázmány P. u.","Kisgát","Mező u. csp.","Kenyérgyár u. 1.","Videoton"],
                "frequency": "15 percenként",
                "firstBus": "05:10",
                "lastBus": "22:40"
            },
            {
                "number": "23",
                "name": "Kaposfüred forduló - Füredi csp. - Kaposvári Egyetem",
                "category": "industrial",
                "stops": ["Kaposfüred, forduló","Kaposfüredi u. 244.","Kaposfüred, központ","Állomás u.","Kaposfüred, vá.","Kaposfüredi u. 104.","Kaposfüredi u. 12.","Volán-telep","Laktanya","Búzavirág u.","Kinizsi ltp."
                ,"Toldi lakónegyed","Füredi úti csomópont","ÁNTSZ","Pázmány P. u.","Kisgát","Mező u. csp.","Izzó u.","Guba S. u. 57.","Guba S. u. 81.","Villamossági Gyár","Kaposvári Egyetem"],
                "frequency": "40 percenként",
                "firstBus": "05:30",
                "lastBus": "20:30"
            },
            {
                "number": "26",
                "name": "Kaposfüred forduló - Losonc köz - Videoton - METYX",
                "category": "hospital",
                "stops": ["Kaposfüred, forduló","Kaposfüredi u. 244","Kaposfüred, központ","Állomás u.","Kaposfüred, vá.","Állomás u.","Kaposfüred, központ","Kaposfüredi u. 104.","Kaposfüredi u. 12.","Volán-telep","Laktanya","Búzavirág u."
                ,"Kinizsi ltp.","Losonc-köz","Arany J. tér","ÁNTSZ","Pázmány P. u.","Kisgát","Mező u. csp.","Kenyérgyár u. 1.","Kenyérgyár u. 3","Videoton","Dombovári u. 4.","METYX"],
                "frequency": "30 percenként",
                "firstBus": "05:15",
                "lastBus": "21:45"
            },
            {
                "number": "27",
                "name": "Laktanya - Füredi u. csp. - KOMÉTA",
                "category": "suburban",
                "stops": ["Laktanya","Búzavirág u.","Kinizsi ltp.","Toldi lakónyegyed","Füredi úti csomópont","ÁNTSZ","Pázmány P. u.","Kisgát","Mező u. csp."
                ,"Hősök temploma","Gyár u.","Pécsi úti iskola","Kométa, forduló"],
                "frequency": "60 percenként",
                "firstBus": "06:00",
                "lastBus": "20:00"
            },
            {
                "number": "31",
                "name": "Helyi autóbusz-állomás - Egyenesi u. forduló",
                "category": "suburban",
                "stops": ["Helyi autóbusz-állomás", "Berzsenyi u. felüljáró","Tompa M. u.","Kölcsey u.","Állatkórház","Eger u.","Kapoli A. u.","Egyenesi u. 42.","Beszédes J. u.","Egyenesi u. forduló"],
                "frequency": "40 percenként",
                "firstBus": "05:20",
                "lastBus": "22:00"
            },
            {
                "number": "32",
                "name": "Helyi autóbuszállomás - Kecelhegy - Helyi autóbusz-állomás",
                "category": "hospital",
                "stops": ["Helyi autóbuszállomás","Berzsenyi u. felüljáró","Tompa M. u.","Kölcsey u.","Állatkórház","Eger u.","Magyar Nobel-díjasok tere","Bethlen G. u.","Kecelhegyi iskola","Kőrösi Cs. S. u."
                ,"Kecelheygalja u.","Mátyás k. u., forduló","Hajnóczy u. csp.","Vasútköz","Városi könyvtár","Füredi úti csomópont","Berzsenyi u. 30.","Berzsenyi u. felüljáró","Heyi autóbusz-állomás"],
                "frequency": "25 percenként",
                "firstBus": "05:10",
                "lastBus": "22:00"
            },
            {
                "number": "33",
                "name": "Helyi aut. áll. - Egyenesi u. - Kecelhegy - Helyi aut. áll.",
                "category": "education",
                "stops": ["Helyi autóbuszállomás","Berzsenyi u. felüljáró","Tompa M. u.","Kölcsey u.","Állatkórház","Eger u.","Kapoli A. u.","Egyenesi u. 42.","Beszédes J. u.","Egyenesi u. forduló","Beszédes J. u."
                ,"Egyenesi u. 42.","Kapoli A. u.","Magyar Nobel-díjasok tere","Bethlen G. u.","Kecelhegyi iskola","Kőrösi Cs. S. u.","Kecelhegyalja u.","Mátyás k. u., forduló","Hajnóczy u. csp."
                ,"Vasútköz","Városi könyvtár","Füredi úti csomópont","Berzsenyi u. 30.","Berzsenyi u. felüljáró","Heyi autóbusz-állomás"],
                "frequency": "15 percenként",
                "firstBus": "06:00",
                "lastBus": "22:30"
            },
            {
                "number": "40",
                "name": "Koppány vezér u - 67-es út - Raktár u.",
                "category": "industrial",
                "stops": ["Koppány vezér u.","Erdősor u.","Rózsa u.","67-es sz. út","Berzsenyi u. felüljáró","Helyi autóbusz-állomás","Berzsenyi u. felüljáró","Berzsenyi u. 30.","Füredi úti csomópont","Városi könyvtár"
                ,"Vasútköz","Hajnóczy u. csp.","Jutai u. 24.","Tóth Á. u.","Jutai u. 45.","Raktár u. 2.","Raktár u."],
                "frequency": "45 percenként",
                "firstBus": "05:30",
                "lastBus": "20:30"
            },
            {
                "number": "41",
                "name": "Koppány vezér u - Bartók B. u. - Raktár u.",
                "category": "suburban",
                "stops": ["Koppány vezér u.","Erdősor u.","Rózsa u.","Szegfű u.","Jókai u.","Bartók B. u.","Berzsenyi u. felüljáró","Helyi autóbusz-állomás","Berzsenyi u. felüljáró","Berzsenyi u. 30.","Füredi úti csomópont","Városi könyvtár"
                ,"Vasútköz","Hajnóczy u. csp.","Jutai u. 24.","Tóth Á. u.","Jutai u. 45.","Raktár u. 2.","Raktár u."],
                "frequency": "30 percenként",
                "firstBus": "05:15",
                "lastBus": "21:00"
            },
            {
                "number": "42",
                "name": "Töröcske forduló - Kórház - Laktanya",
                "category": "suburban",
                "stops": ["Töröcske, forduló","Fenyves u. 31.","Fenyves u. 37/A","Szőlőhegy","Kertbarát alsó","Kertbarát felső","Gyertyános","Harangvirág u.","Aranyeső u.","Zichy u.","Táncsics M. u.","Bartók B. u.","Berzsenyi u. felüljáró"
                ,"Helyi autóbusz-állomás","Vasútállomás","Tallián Gy. u. 4.","Kórház","Tallián Gy. u. 56.","Tallián Gy. u. 82.","ÁNTSZ","Füredi úti csomópont","Toldi lakónegyed","Kinizsi ltp.","Búzavirág u.","Laktanya"],
                "frequency": "45 percenként",
                "firstBus": "05:30",
                "lastBus": "22:00"
            },
            {
                "number": "43",
                "name": "Helyi autóbusz-állomás - Kórház- Laktanya - Raktár utca - Helyi autóbusz-állomás",
                "category": "education",
                "stops": ["Helyi autóbusz-állomás", "Vasútállomás","Tallián Gy. u. 4","Kórház","Tallián Gy. u. 56","Tallián Gy. u. 82","Buzsáki u.","Losonc-köz","Brassó u.","Sopron u."
                ,"Búzavirág u.","Laktanya","Raktár u.","Raktár u. 2.","Jutai u. 45.","Tóth Á. u.","Jutai u. 24.","Helyi autóbusz-állomás"],
                "frequency": "20 percenként",
                "firstBus": "06:00",
                "lastBus": "22:30"
            },
            {
                "number": "44",
                "name": "Helyi autóbusz-állomás - Raktár utca - Laktanya -Arany János tér - Helyi autóbusz-állomás",
                "category": "industrial",
                "stops": ["Helyi autóbusz-állomás", "Kapostüskevár","Jutai u. 24.","Tóth Á. u.","Jutai u. 45.","Raktár u. 2.","Raktár u.","Laktanya","Búzavirág u.","Nagyszeben u."
                ,"Losonc-köz","Arany J. tér","Buzsáki u.","Rendőrség","Szent Imre u. 29","Szent Imre u. 13.","Széchényi tér","Rákóczi tér","Helyi autóbusz-állomás"],
                "frequency": "35 percenként",
                "firstBus": "05:20",
                "lastBus": "21:00"
            },
            {
                "number": "45",
                "name": "Helyi autóbusz-állomás - 67-es út - Koppány vezér u.",
                "category": "suburban",
                "stops": ["Helyi autóbusz-állomás","Berzsenyi u. felüljáró","67-es sz. út","Rózsa u.","Gönczi F. u.","Koppány vezér u."],
                "frequency": "30 percenként",
                "firstBus": "05:15",
                "lastBus": "21:45"
            },
            {
                "number": "46",
                "name": "Helyi autóbusz-állomás - Töröcske forduló",
                "category": "hospital",
                "stops": ["Helyi autóbusz-állomás","Berzsenyi u. felüljáró","Bartók B. u.","Táncsics M. u.","Zichy u.","Aranyeső u.","Harangvirág u."
                ,"Gyertányos","Kertbarát felső","Kertbarát alsó","Szőlőhegy","Fenyves u. 37/A","Fenyves u. 31.","Töröcske, forduló"],
                "frequency": "25 percenként",
                "firstBus": "05:30",
                "lastBus": "22:15"
            },
            {
                "number": "47",
                "name": "Koppány vezér u.- Kórház - Kaposfüred forduló",
                "category": "education",
                "stops": ["Koppány vezér u.","Gönczi F. u.","Rózsa u.","67-es sz. út","Berzsenyi u. felüljáró","Helyi autóbusz-állomás","Vasútállomás","Tallián Gy. u. 4.","Kórház","Tallián Gy. u. 56."
                ,"Tallián Gy. u. 82.","ÁNTSZ","Füredi úti csomópont","Toldi lakónegyed","Kinizsi ltp.","Búzavirág u.","Laktanya","Volán-telep","Kaposfüredi u. 12.","Kaposfüredi u. 104.","Kaposfüred, központ","Kaposfüred, forduló"],
                "frequency": "15 percenként",
                "firstBus": "05:50",
                "lastBus": "23:00"
            },
            {
                "number": "61",
                "name": "Helyi- autóbuszállomás - Béla király u.",
                "category": "suburban",
                "stops": ["Helyi autóbusz-állomás","Baross G. u.","Csalogány u.","Vikár B.u.","Béla király u."],
                "frequency": "60 percenként",
                "firstBus": "06:30",
                "lastBus": "19:30"
            },
            {
                "number": "62",
                "name": "Helyi autóbusz-állomás - Városi fürdő - Béla király u.",
                "category": "industrial",
                "stops": ["Helyi aiutóbusz-állomás","Berzsenyi u. felüljáró","Városi fürdő","Csalogány u.","Vikár B. u.","Béla király u."],
                "frequency": "30 percenként",
                "firstBus": "05:15",
                "lastBus": "21:00"
            },
            {
                "number": "70",
                "name": "Helyi autóbusz-állomás - Kaposfüred",
                "category": "residential",
                "stops": ["Helyi autóbusz-állomás","Berzsenyi u. felüljáró","Berzsenyi u. 30.","Füredi úti csomópont","Toldi lakónegyed"
                ,"Kinizsi ltp.","Búzavirág u.","Laktanya","Zöld fűtőmű","Volán-telep","Kaposfüredi u. 12.","Kaposfüredi u. 104.","Kaposfüred, központ","Kaposfüredi u. 244.","Kaposfüred, forduló"],
                "frequency": "15 percenként",
                "firstBus": "05:00",
                "lastBus": "23:00"
            },
            {
                "number": "71",
                "name": "Kaposfüred forduló - Kaposszentjakab forduló",
                "category": "suburban",
                "stops": ["Kaposfüred, forduló","Kaposfüredi u. 244.","Kaposfüred, központ","Kaposfüredi u. 104.","Kaposfüredi u. 12.","Volán-telep","Zöld fűtőmű","Laktanya"
                ,"Búzavirág u.","Kinizsi ltp.","Toldi lakónegyed","Füredi úti csomópont","Berzsenyi u. 30.","Berzsenyi u. felüljáró","Helyi autóbusz-állomás","Rákóczi tér","Fő u. 37-39."
                ,"Hársfa u.","Hősök temploma","Gyár u.","Pécsi úti iskola","Nádasdi u.","Móricz Zs. u.","Pécsi u. 227.","Várhegy feljáró","Kaposszentjakab, forduló"],
                "frequency": "40 percenként",
                "firstBus": "05:10",
                "lastBus": "21:30"
            },
            {
                "number": "72",
                "name": "Kaposfüred forduló - Hold u. - Kaposszentjakab forduló",
                "category": "industrial",
                "stops": ["Kaposfüred, forduló","Kaposfüredi u. 244.","Kaposfüred, központ","Állomás u.","Kaposfüred, vá.","Állomás u.","Kaposfüred, központ","Kaposfüredi u. 104.","Kaposfüredi u. 12.","Volán-telep","Zöld fűtőmű","Laktanya"
                ,"Búzavirág u.","Kinizsi ltp.","Toldi lakónegyed","Füredi úti csomópont","Berzsenyi u. 30.","Berzsenyi u. felüljáró","Helyi autóbusz-állomás","Vasútállomás","Fő u. 48.","Hársfa u.","Hősök temploma","Gyár u.","Pécsi úti iskola"
                ,"Nádasdi u.","Nap u.","Hold u.","Nádasdi u.","Móricz Zs. u.","Pécsi u. 227.","Várhegy feljáró","Kaposzsentjakab, forduló"],
                "frequency": "50 percenként",
                "firstBus": "06:00",
                "lastBus": "20:00"
            },
            {
                "number": "73",
                "name": "Kaposfüred forduló - KOMÉTA - Kaposszentjakab forduló",
                "category": "residential",
                "stops": ["Kaposfüred, forduló","Kaposfüredi u. 244.","Kaposfüred, központ","Kaposfüredi u. 104.","Kaposfüredi u. 12.","Volán-telep","Zöld fűtőmű","Laktanya"
                ,"Búzavirág u.","Kinizsi ltp.","Toldi lakónegyed","Füredi úti csomópont","Berzsenyi u. 30.","Berzsenyi u. felüljáró","Helyi autóbusz-állomás","Rákóczi tér","Fő u. 37-39."
                ,"Hársfa u.","Hősök temploma","Gyár u.","Pécsi úti iskola","Kométa, forduló","Nádasdi u.","Móricz Zs. u.","Pécsi u. 227.","Várhegy feljáró","Kaposszentjakab, forduló"],
                "frequency": "45 percenként",
                "firstBus": "06:15",
                "lastBus": "21:00"
            },
            {
                "number": "74",
                "name": "Hold utca - Helyi autóbusz-állomás",
                "category": "education",
                "stops": ["Hold u.","Nap u.","Nádasdi u.","Pécsi úti iskola","Gyár u.","Hősök temploma","Hásrfa u.","Fő u. 37-39.","Rákóczi tér","Helyi autóbusz-állomás"],
                "frequency": "20 percenként",
                "firstBus": "05:45",
                "lastBus": "22:45"
            },
            {
                "number": "75",
                "name": "Helyi autóbusz-állomás - Kaposszentjakab",
                "category": "suburban",
                "stops": ["Helyi autóbusz-állomás","Rákóczi tér","Fő u. 37-39.","Hársfa u.","Hősök temploma","Gyár u.","Pécsi úti iskola","Nádasdi u.","Móricz Zs. u.","Pécsi u. 227.","Várhegy feljáró","Kaposszentjakab, forduló"],
                "frequency": "60 percenként",
                "firstBus": "06:30",
                "lastBus": "18:30"
            },
            {
                "number": "81",
                "name": "Helyi autóbusz-állomás - Hősök temploma - Toponár forduló",
                "category": "industrial",
                "stops": ["Helyi autóbusz-állomás","Vasútálomás","Hársfa u.","Hősök temploma","Mező u. csp.","Izzó u.","Guba S. u. 57","Guba S. u. 81.","Villamossági Gyár","Kaposvári Egyetem"
                ,"Toponár, posta","Toponár, Orci elágazás","Toponári u. 182.","Toponári u. 238.","Toponár, forduló"],
                "frequency": "35 percenként",
                "firstBus": "05:45",
                "lastBus": "21:15"
            },
            {
                "number": "82",
                "name": "Helyi autóbusz-állomás - Kórház - Toponár Szabó P. u.",
                "category": "education",
                "stops": ["Helyi autóbusz-állomás","Vasútálomás","Tallián Gy. u. 4","Kórház","Tallián Gy. u. 56.","Tallián Gy. u. 82.","Pázmány P. u.","Kisgát","Mező u. csp."
                ,"Izzó u.","Guba S. u. 57","Guba S. u. 81.","Villamossági Gyár","Kaposvári Egyetem","Toponár, posta","Toponár, Orci elágazás","Toponár, Orci út","Toponár, Szabó P. u."],
                "frequency": "25 percenként",
                "firstBus": "06:00",
                "lastBus": "22:00"
            },
            {
                "number": "83",
                "name": "Helyi autóbusz-állomás - Szabó P. u. - Toponár forduló",
                "category": "industrial",
                "stops": ["Helyi autóbusz-állomás","Vasútálomás","Fő u. 48.","Hársfa u.","Hősök temploma","Mező u. csp.","Izzó u.","Guba S. u. 57","Guba S. u. 81.","Villamossági Gyár","Kaposvári Egyetem"
                ,"Toponár, posta","Toponár, Orci elágazás","Toponár, Orci út","Toponár, Szabó P. u.","Toponár, Erdei F. u.","Toponár, Orci út","Toponár, Orci elágazás","Toponári u. 182.","Toponári u. 238.","Toponár, forduló"],
                "frequency": "50 percenként",
                "firstBus": "06:15",
                "lastBus": "19:30"
            },
            {
                "number": "84",
                "name": "Helyi autóbusz-állomás - Toponár, forduló - Répáspuszta",
                "category": "residential",
                "stops": ["Helyi autóbusz-állomás","Vasútálomás","Fő u. 48.","Hársfa u.","Hősök temploma","Mező u. csp.","Izzó u.","Guba S. u. 57","Guba S. u. 81.","Villamossági Gyár","Kaposvári Egyetem"
                ,"Toponár, posta","Toponár, Orci elágazás","Toponári u. 182.","Toponári u. 238.","Toponár, forduló","Répáspuszta","Répáspuszta, forduló"],
                "frequency": "30 percenként",
                "firstBus": "05:30",
                "lastBus": "21:00"
            },
            {
                "number": "85",
                "name": "Helyi autóbusz-állomás - Kisgát- Helyi autóbusz-állomás",
                "category": "suburban",
                "stops": ["Helyi autóbusz-állomás","Vasútálomás","Rákóczi tér","Fő u. 48.","Hársfa u.","Hősök temploma","Mező u. csp.","Kisgát","Pázmány P. u."
                ,"Rendőrség","Szent Imre u. 29","Szent Imre u. 13.","Széchényi tér","Helyi autóbusz-állomás"],
                "frequency": "60 percenként",
                "firstBus": "06:00",
                "lastBus": "19:00"
            },
            {
                "number": "86",
                "name": "Helyi autóbusz-állomás - METYX - Szennyvíztelep",
                "category": "education",
                "stops": ["Helyi autóbusz-állomás","Vasútálomás","Fő u. 48.","Hársfa u.","Hősök temploma","Mező u. csp.","Kenyérgyár u. 1.","Videoton","Dombóvári u. 4.","METYX","Cabero","Sennyvíztelep"],
                "frequency": "30 percenként",
                "firstBus": "05:45",
                "lastBus": "21:45"
            },
            {
                "number": "87",
                "name": "Helyi autóbusz állomás - Videoton - METYX",
                "category": "residential",
                "stops": ["Helyi autóbusz-állomás","Vasútálomás","Fő u. 48.","Hársfa u.","Hősök temploma","Mező u. csp.","Kenyérgyár u. 1.","Videoton","Dombóvári u. 4.","METYX"],
                "frequency": "20 percenként",
                "firstBus": "05:30",
                "lastBus": "22:30"
            },
            {
                "number": "88",
                "name": "Helyi autóbusz-állomás - Videoton",
                "category": "residential",
                "stops": ["Helyi autóbusz-állomás","Vasútálomás","Fő u. 48.","Hársfa u.","Hősök temploma","Mező u. csp.","Kenyérgyár u. 1.","Videoton"],
                "frequency": "15 percenként",
                "firstBus": "05:00",
                "lastBus": "23:30"
            },
            {
                "number": "89",
                "name": "Helyi autóbusz-állomás - Kaposvári Egyetem",
                "category": "hospital",
                "stops": ["Helyi autóbusz-állomás","Vasútálomás","Fő u. 48.","Hársfa u.","Hősök temploma","Mező u. csp.","Izzó u.","Guba S. u. 57","Guba S. u. 81.","Villamossági Gyár","Kaposvári Egyetem"],
                "frequency": "25 percenként",
                "firstBus": "06:00",
                "lastBus": "22:15"
            },
            {
                "number": "90",
                "name": "Helyi autóbusz-állomás - Rómahegy",
                "category": "suburban",
                "stops": ["Helyi autóbusz-állomás","Berzsenyi u. felüljáró","Jókai liget","Szigetvári u. 6.","Szigetvári u. 62.","Ballakúti u.","Szigetvári u. 139.","Nyár u.","Rómahegy"],
                "frequency": "70 percenként",
                "firstBus": "06:30",
                "lastBus": "19:00"
            },
            {
                "number": "91",
                "name": "Rómahegy - Pázmány P u. - Füredi u. csp.",
                "category": "hospital",
                "stops": ["Rómahegy","Nyár u.","Szigetvári u. 139.","Ballakúti u.","Szigetvári u. 62.","Szigetvári u. 6.","Jókai liget","Berzsenyi u. felüljáró","Helyi autóbusz-állomás","Vasútálomás","Fő u. 48.","Hársfa u."
                ,"Virág u.","Pázmány P. u. 1.","Vöröstelek u.","Hegyi u.","Buzsáki u.","Arany J. tér","Füredi úti csomópont"],
                "frequency": "30 percenként",
                "firstBus": "05:10",
                "lastBus": "22:30"
            }

        ];

        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('hu-HU');
            document.getElementById('time').textContent = timeString;
        }

        setInterval(updateTime, 1000);
        updateTime();

        function displayRoutes(filter = "all") {
            const routeContainer = document.getElementById('routeContainer');
            routeContainer.innerHTML = "";
            
            const filteredRoutes = filter === "all" 
                ? busRoutes 
                : busRoutes.filter(route => route.category === filter);

            filteredRoutes.forEach((route, index) => {
                const routeCard = document.createElement('div');
                routeCard.className = 'route-card';
                routeCard.style.animationDelay = `${index * 0.1}s`;
                
                routeCard.innerHTML = `
                    <div class="route-number">
                        <i class="fas fa-bus bus-icon"></i>
                        ${route.number} - ${route.name}
                    </div>
                    
                    <div class="route-details">
                        <div class="next-departure">
                            <i class="far fa-clock"></i>
                            Következő indulás: ${route.firstBus}
                        </div>
                        <div class="route-frequency">
                            <i class="fas fa-sync-alt"></i>
                            Járatsűrűség: ${route.frequency}
                        </div>
                        <div class="button-group">
                        <button class="route-button" id="visszaBtn">
                            Vissza
                        </button>
                        <button class="route-button" id="odaBtn">
                            Oda
                        </button>
                    </div>
                    </div>
                        <div class="show-stops" onclick="toggleStops(this)">
                            <i class="fas fa-map-marker-alt"></i>
                            Megállók megjelenítése
                        </div>
                        <div class="stops-list">
                            ${route.stops.map((stop, i) => 
                                `<div><i class="fas fa-stop"></i> ${stop}</div>`
                            ).join('')}
                        </div>
                `;
                routeContainer.appendChild(routeCard);
            });
        }

        //Irány változtató gomb
        /*document.getElementById("iranyBtn").onclick = function() {
            busRoutes.number.stops.reverse();
        };*/

        function toggleStops(element) {
            const stopsList = element.nextElementSibling;
            const isVisible = stopsList.style.display === "block";
            
            stopsList.style.display = isVisible ? "none" : "block";
            element.innerHTML = isVisible
                ? '<i class="fas fa-map-marker-alt"></i> Megállók megjelenítése'
                : '<i class="fas fa-map-marker-alt"></i> Megállók elrejtése';
            
            if (!isVisible) {
                stopsList.style.opacity = "0";
                stopsList.style.display = "block";
                setTimeout(() => {
                    stopsList.style.opacity = "1";
                }, 10);
            }
        }

        // Keresés funkcionalitás
        const searchBox = document.getElementById('searchBox');
        searchBox.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const routeCards = document.querySelectorAll('.route-card');
            
            routeCards.forEach(card => {
                const routeText = card.textContent.toLowerCase();
                if (routeText.includes(searchTerm)) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.5s ease-out';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Szűrő gombok kezelése
        const filterButtons = document.querySelectorAll('.filter-button');
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                const filter = button.getAttribute('data-filter');
                displayRoutes(filter);
            });
        });

        // Valós idejű adatok szimulálása
        function simulateRealTimeUpdates() {
            const cards = document.querySelectorAll('.route-card');
            cards.forEach(card => {
                const nextDeparture = card.querySelector('.next-departure');
                if (nextDeparture) {
                    const now = new Date();
                    const minutes = now.getMinutes();
                    const nextTime = new Date(now.getTime() + Math.random() * 30 * 60000);
                    const nextTimeString = nextTime.toLocaleTimeString('hu-HU', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    nextDeparture.innerHTML = `
                        <i class="far fa-clock"></i>
                        Következő indulás: ${nextTimeString}
                        <span class="live-indicator" style="font-size: 0.8em; padding: 2px 6px;">
                            <i class="fas fa-circle" style="font-size: 0.6em;"></i> Élő
                        </span>
                    `;
                }
            });
        }

        function setupNotifications() {
            const notificationBtn = document.createElement('button');
            notificationBtn.className = 'filter-button';
            notificationBtn.innerHTML = '<i class="fas fa-bell"></i> Értesítések';
            notificationBtn.style.marginLeft = 'auto';
            
            notificationBtn.addEventListener('click', () => {
                if ('Notification' in window) {
                    Notification.requestPermission().then(permission => {
                        if (permission === 'granted') {
                            new Notification('Kaposvár Helyi Járatok', {
                                body: 'Az értesítések sikeresen bekapcsolva!',
                                icon: '/bus-icon.png'
                            });
                        }
                    });
                }
            });
            
            document.getElementById('filterButtons').appendChild(notificationBtn);
        }

        document.addEventListener('DOMContentLoaded', () => {
            displayRoutes();
            addWeatherWidget();
            setupNotifications();
            setInterval(simulateRealTimeUpdates, 30000);
        });

        // Mobilbarát menü
        function setupMobileMenu() {
            const header = document.querySelector('header');
            const filterButtons = document.getElementById('filterButtons');
            
            let lastScroll = 0;
            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;
                
                if (currentScroll > lastScroll && currentScroll > 100) {
                    header.style.transform = 'translateY(-100%)';
                } else {
                    header.style.transform = 'translateY(0)';
                }
                lastScroll = currentScroll;
            });
        }

        setupMobileMenu();

        
        function addThemeToggle() {
            const themeBtn = document.createElement('button');
            themeBtn.className = 'filter-button';
            themeBtn.innerHTML = '<i class="fas fa-moon"></i>';
            themeBtn.style.position = 'fixed';
            themeBtn.style.bottom = '20px';
            themeBtn.style.right = '20px';
            themeBtn.style.zIndex = '1000';
            
            let isDark = false;
            themeBtn.addEventListener('click', () => {
                isDark = !isDark;
                document.body.style.background = isDark ? '#1a1a1a' : 'linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%)';
                document.body.style.color = isDark ? '#fff' : '#333';
                themeBtn.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
                
                const cards = document.querySelectorAll('.route-card');
                cards.forEach(card => {
                    card.style.background = isDark ? '#2d2d2d' : 'white';
                    card.style.color = isDark ? '#fff' : '#333';
                });
            });
            
            document.body.appendChild(themeBtn);
        }

        addThemeToggle();
    </script>
</body>
</html>
