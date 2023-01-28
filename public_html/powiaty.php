<HTML>
    <HEAD>
        <TITLE> Powiaty </TITLE>
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

            $top = filter_input(INPUT_POST, 'top', FILTER_SANITIZE_STRING);

            if (!$top) {
                $top = 'all';
            }

            $woj_id = $_GET['id'];
            $stmt = oci_parse($conn, "SELECT * FROM powiat WHERE id_wojewodztwa = $woj_id");

            oci_execute($stmt, OCI_NO_AUTO_COMMIT);

            $tmp = oci_parse($conn, "SELECT nazwa FROM wojewodztwo WHERE id = $woj_id");
            oci_execute($tmp, OCI_NO_AUTO_COMMIT);
            $tmp = oci_fetch_array($tmp, OCI_BOTH);
            $tmp = $tmp[0];

            if ($top != 'all') {
                $com_res = oci_parse($conn, 
                                    "SELECT komitet.id, komitet.nazwa, SUM(wyniki.ile) suma 
                                    FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                        JOIN gmina ON lokal.id_gminy = gmina.id
                                        JOIN powiat ON gmina.id_powiatu = powiat.id
                                        JOIN wojewodztwo ON powiat.id_wojewodztwa = wojewodztwo.id
                                        JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                        JOIN komitet ON kandydat.id_komitetu = komitet.id 
                                    WHERE wojewodztwo.id = $woj_id
                                    GROUP BY komitet.id, komitet.nazwa
                                    ORDER BY suma DESC
                                    FETCH FIRST $top ROWS ONLY");
            } else {
                $com_res = oci_parse($conn, 
                                    "SELECT komitet.id, komitet.nazwa, SUM(wyniki.ile) suma 
                                    FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                        JOIN gmina ON lokal.id_gminy = gmina.id
                                        JOIN powiat ON gmina.id_powiatu = powiat.id
                                        JOIN wojewodztwo ON powiat.id_wojewodztwa = wojewodztwo.id
                                        JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                        JOIN komitet ON kandydat.id_komitetu = komitet.id 
                                    WHERE wojewodztwo.id = $woj_id
                                    GROUP BY komitet.id, komitet.nazwa
                                    ORDER BY suma DESC");
            }
            oci_execute($com_res, OCI_NO_AUTO_COMMIT);

            $sum = oci_parse($conn, 
                             "SELECT SUM(wyniki.ile) suma 
                              FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                   JOIN gmina ON lokal.id_gminy = gmina.id
                                   JOIN powiat ON gmina.id_powiatu = powiat.id
                                   JOIN wojewodztwo ON powiat.id_wojewodztwa = wojewodztwo.id
                              WHERE wojewodztwo.id = $woj_id");
            oci_execute($sum, OCI_NO_AUTO_COMMIT);
            $sum = oci_fetch_array($sum, OCI_BOTH);
            $sum = $sum[0];

            if ($top != 'all') {
                $can_res = oci_parse($conn, 
                                     "SELECT kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko, SUM(wyniki.ile) suma 
                                      FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                           JOIN gmina ON lokal.id_gminy = gmina.id
                                           JOIN powiat ON gmina.id_powiatu = powiat.id
                                           JOIN wojewodztwo ON powiat.id_wojewodztwa = wojewodztwo.id
                                           JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                      WHERE wojewodztwo.id = $woj_id
                                      GROUP BY kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko
                                      ORDER BY suma DESC
                                      FETCH FIRST $top ROWS ONLY");
            } else {
                $can_res = oci_parse($conn, 
                                     "SELECT kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko, SUM(wyniki.ile) suma 
                                      FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                           JOIN gmina ON lokal.id_gminy = gmina.id
                                           JOIN powiat ON gmina.id_powiatu = powiat.id
                                           JOIN wojewodztwo ON powiat.id_wojewodztwa = wojewodztwo.id
                                           JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                      WHERE wojewodztwo.id = $woj_id
                                      GROUP BY kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko
                                      ORDER BY suma DESC");
            }
            oci_execute($can_res, OCI_NO_AUTO_COMMIT);
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
        <?PHP echo "<H1> Powiaty - województwo ".$tmp."</H1>"; ?>
        <div class = "row">
            <div class = "column">
                <table>
                    <tr>
                        <th> Numer </th>
                        <th> Nazwa </th>
                    </tr>
                    <?PHP
                        $num = 1;
                        while ($row = oci_fetch_array($stmt, OCI_BOTH)) {
                            echo "<tr>";
                            echo "<th>".$num."</th>";
                            
                            $nazwa = $row['NAZWA'];
                            $low = mb_strtolower($nazwa, "UTF-8");
                            $id = $row['ID'];
                            if ($nazwa != $low && $nazwa != "Warszawa") {
                                $tmp = oci_parse($conn, "SELECT id FROM gmina WHERE id_powiatu = $id");
                                oci_execute($tmp, OCI_NO_AUTO_COMMIT);
                                $tmp = oci_fetch_array($tmp, OCI_BOTH);
                                $tmp = $tmp[0];
                                echo "<th><a href=\"lokale.php?id=".$tmp."\">".$row['NAZWA']."</a></th>";
                            } else {
                                echo "<th><a href=\"gminy.php?id=".$row['ID']."\">".$row['NAZWA']."</a></th>";
                            }
                            echo "</tr>";
                            $num++;
                        }
                    ?>
                </table>
            </div>
            <div class = "column">
                <div class="row-right">
                    <form action="#" method="post">
                        <label for="top">Pokaż:</label>
                        <select name="top" id="top">
                                <option value="0" hidden>--Wybierz--</option>
                                <option value="1">1</option>
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="all">Wszystkie</option>
                        </select>
                        <input type="submit" value="Zmień">
                    </form>
                    <script>
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }
                    </script>
                </div>
                <?PHP
                    if ($top != 'all') {
                        echo "<h2> Top ".$top." komitetów </h2>";
                    } else {
                        echo "<h2> Wyniki wszystkich komitetów </h2>";
                    }
                ?>
                <table>
                    <tr>
                        <th> Numer </th>
                        <th> Nazwa </th>
                        <th> Ilość głosów </th>
                        <th> Procent głosów </th>
                    </tr>
                    <?PHP
                        $num = 1;
                        while ($row = oci_fetch_array($com_res, OCI_BOTH)) {
                            echo "<tr>";
                            echo "<th>".$num."</th>";
                            echo "<th><a href=\"komitety.php?id=".$row['ID']."\">".$row['NAZWA']."</a></th>";
                            echo "<th>".$row['SUMA']."</th>";
                            echo "<th>".round($row['SUMA']/$sum*100, 2)."%</th>";
                            echo "</tr>";
                            $num++;
                        }
                    ?>
                </table>
                <?PHP
                    if ($top != 'all') {
                        echo "<h2> Top ".$top." kandydatów </h2>";
                    } else {
                        echo "<h2> Wyniki wszystkich kandydatów </h2>";
                    }
                ?>
                <table>
                    <tr>
                        <th> Numer </th>
                        <th> Kandydat </th>
                        <th> Ilość głosów </th>
                        <th> Procent głosów </th>
                    </tr>
                    <?PHP
                        $num = 1;
                        while ($row = oci_fetch_array($can_res, OCI_BOTH)) {
                            echo "<tr>";
                            echo "<th>".$num."</th>";
                            if ($row['DRUGIE_IMIE'] == NULL) {
                                echo "<th><a href=\"kandydaci.php?id=".$row['ID']."\">".$row['PIERWSZE_IMIE']." ".$row['NAZWISKO']."</a></th>";
                            } else {
                                echo "<th><a href=\"kandydaci.php?id=".$row['ID']."\">".$row['PIERWSZE_IMIE']." ".$row['DRUGIE_IMIE']." ".$row['NAZWISKO']."</a></th>";
                            }
                            echo "<th>".$row['SUMA']."</th>";
                            echo "<th>".round($row['SUMA']/$sum*100, 2)."%</th>";
                            echo "</tr>";
                            $num++;
                        }
                    ?>
                </table>
            </div>
        </div>
    </BODY>
</HTML>