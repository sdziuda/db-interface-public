<HTML>
    <HEAD>
        <TITLE> Strona główna </TITLE>
        <meta charset="UTF-8">
        <html lang="pl">
        <link rel="stylesheet" type="text/css" href="style.css">
        <?PHP
            session_start();
            putenv("NLS_LANG=polish_poland.utf8");
            $_SESSION['LOGIN'] = 'sd438422';
            $_SESSION['PASS'] = '**REMOVED**';
            $conn = oci_connect($_SESSION['LOGIN'], $_SESSION['PASS'], "//labora.mimuw.edu.pl/LABS", 'UTF-8');
            
            if (!$conn) {
                echo "oci_connect failed\n";
                $e = oci_error();
                echo $e['message'];
            }
        ?>
    </HEAD>
    <BODY>
        <div class = "row">
            <div class = "column-centered">
                <a href="wojewodztwa.php">
                    <button type = "button" class="btn-menu">
                        <img src="./images/polska-wojewodztwa-kontury.png">
                    </button>
                </a>
            </div>
            <div class = "column-centered">
                <a href="szukaj_lokal.php">
                    <button type = "button" class="btn-menu">
                        <img src="./images/search-lokal.png">
                    </button>
                </a>
            </div>
        </div>
        <div class = "row-filler"></div>
        <div class = "row">
            <div class = "column-centered">
                <a href="szukaj_komitet.php">
                    <button type = "button" class="btn-menu">
                        <img src="./images/search-komitet.png">
                    </button>
                </a>
            </div>
            <div class = "column-centered">
                <a href="szukaj_kandydat.php">
                    <button type = "button" class="btn-menu">
                        <img src="./images/search-kandydat.png">
                    </button>
                </a>
            </div>
        </div>
    </BODY>
</HTML>
