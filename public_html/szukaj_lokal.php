<HTML>
    <HEAD>
        <TITLE> Wyszukiwarka </TITLE>
        <meta charset="UTF-8">
        <html lang="pl">
        <link rel="stylesheet" type="text/css" href="style.css">
        <script type="module" src="./wyszukiwarka_lokali.js"></script>
        <?PHP
            session_start();
            putenv("NLS_LANG=polish_poland.utf8");
            $conn = oci_connect($_SESSION['LOGIN'], $_SESSION['PASS'], "//labora.mimuw.edu.pl/LABS", 'UTF-8');
            
            if (!$conn) {
                echo "oci_connect failed\n";
                $e = oci_error();
                echo $e['message'];
            }
        ?>
    </HEAD>
    <BODY>
        <div class="row">
            <a href="index.php">
                <button type="button" class="btn-home">
                    <img src="./images/home.png">
                </button>
            </a>
        </div>
        <h1>Wyszukiwarka lokali</h1>
        <div class="column-centered">
            <div id="map"></div>
            <script
                src="https://maps.googleapis.com/maps/api/js?key=NoKeyForNow&callback=initMap"
                defer
            ></script>
            <div class="row">
                <form id="wyszukiwarka-lokali" action="#" method="post">
                    <input type="text" name="search" placeholder="Znajdź najbliższy lokal">
                    <input type="submit" name="save" value="Szukaj">
                </form>
            </div>
        </div>
        <table id="results"></table>
    </BODY>
</HTML>