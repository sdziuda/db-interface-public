<HTML>
    <HEAD>
        <TITLE> Wyszukiwarka </TITLE>
        <META charset="UTF-8">
        <HTML lang="pl">
        <link rel="stylesheet" type="text/css" href="style.css">
        <?PHP
            session_start();

            putenv("NLS_LANG=polish_poland.utf8");
            
            $conn = oci_connect($_SESSION['LOGIN'], $_SESSION['PASS'], "//labora.mimuw.edu.pl/LABS", 'UTF-8');

            if (!$conn) {
                echo "oci_connect failed\n";
                $e = oci_error();
                echo $e['message'];
            }

            $kan_res = '';

            if (isset($_POST['save'])) {
                if (!empty($_POST['search'])) {
                    $search = $_POST['search'];
                    $search = mb_strtoupper($search, 'UTF-8');
                    $kan_res = oci_parse($conn, 
                                         "SELECT id, pierwsze_imie, drugie_imie, nazwisko 
                                          FROM kandydat 
                                          WHERE (drugie_imie IS NULL AND UPPER(CONCAT(CONCAT(pierwsze_imie, ' '), nazwisko)) LIKE '%$search%')
                                                OR (drugie_imie IS NOT NULL AND UPPER(CONCAT(CONCAT(CONCAT(CONCAT(pierwsze_imie, ' '), drugie_imie), ' '), nazwisko)) LIKE '%$search%')
                                          ORDER BY nazwisko, pierwsze_imie, drugie_imie");
                    oci_execute($kan_res, OCI_NO_AUTO_COMMIT);
                }
            } else {
                $kan_res = oci_parse($conn, 
                                     "SELECT id, pierwsze_imie, drugie_imie, nazwisko 
                                      FROM kandydat 
                                      ORDER BY nazwisko, pierwsze_imie, drugie_imie");
                oci_execute($kan_res, OCI_NO_AUTO_COMMIT);
            }
        ?>
    </HEAD>
    <BODY>
        <a href="index.php">
            <button type="button" class="btn-home">
                <img src="./images/home.png">
            </button>
        </a>
        <h1> Wyszukiwarka kandydatów </h1>
        <div class="column-centered">
            <form action="#" method="post">
                <input type="text" name="search" placeholder="Wyszukaj kandydata">
                <input type="submit" name="save" value="Szukaj">
            </form>
            <script>
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            </script>
        </div>
        <?php
            if(!$kan_res) {
                echo "<h3> Nie znaleziono kandydatów </h3>";
            } else {
                $row = oci_fetch_array($kan_res, OCI_BOTH);
                if (!$row) {
                    echo "<h3> Nie znaleziono kandydatów </h3>";
                } else {
                    echo "<table>";
                        echo "<tr>";
                            echo "<th> Numer </th>";
                            echo "<th> Kandydat </th>";
                        echo "</tr>";
                        $num = 1;
                        do {
                            echo "<tr>";
                            echo "<th>".$num."</th>";
                            if ($row['DRUGIE_IMIE'] == NULL) {
                                echo "<th><a href=\"kandydaci.php?id=".$row['ID']."\">".$row['PIERWSZE_IMIE']." ".$row['NAZWISKO']."</a></th>";
                            } else {
                                echo "<th><a href=\"kandydaci.php?id=".$row['ID']."\">".$row['PIERWSZE_IMIE']." ".$row['DRUGIE_IMIE']." ".$row['NAZWISKO']."</a></th>";
                            }
                            echo "</tr>";
                            $num++;
                        } while ($row = oci_fetch_array($kan_res, OCI_BOTH));
                    echo "</table>";
                }
            }
        ?>
    </BODY>
</HTML>