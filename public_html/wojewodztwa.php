<HTML>
    <HEAD>
        <TITLE> Województwa </TITLE>
        <meta charset="UTF-8">
        <html lang="pl">
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
            
            $stmt = oci_parse($conn, "SELECT * FROM wojewodztwo");
            oci_execute($stmt, OCI_NO_AUTO_COMMIT);

            if ($top != 'all') {
                $com_res = oci_parse($conn, 
                                     "SELECT komitet.id, komitet.nazwa, SUM(wyniki.ile) suma 
                                     FROM wyniki JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                          JOIN komitet ON kandydat.id_komitetu = komitet.id
                                     GROUP BY komitet.id, komitet.nazwa
                                     ORDER BY suma DESC
                                     FETCH FIRST $top ROWS ONLY");
            } else {
                $com_res = oci_parse($conn, 
                                     "SELECT komitet.id, komitet.nazwa, SUM(wyniki.ile) suma 
                                     FROM wyniki JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                          JOIN komitet ON kandydat.id_komitetu = komitet.id
                                     GROUP BY komitet.id, komitet.nazwa
                                     ORDER BY suma DESC");
            }
            oci_execute($com_res, OCI_NO_AUTO_COMMIT);

            $com_res_len = oci_parse($conn, "SELECT COUNT(*) FROM komitet");
            oci_execute($com_res_len, OCI_NO_AUTO_COMMIT);
            $com_res_len = oci_fetch_array($com_res_len, OCI_BOTH);
            $com_res_len = $com_res_len[0];

            $sum = oci_parse($conn, "SELECT SUM(ile) FROM wyniki");
            oci_execute($sum, OCI_NO_AUTO_COMMIT);
            $sum = oci_fetch_array($sum, OCI_BOTH);
            $sum = $sum[0];

            if ($top != 'all') {
                $can_res = oci_parse($conn, 
                                     "SELECT kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko, SUM(wyniki.ile) suma 
                                     FROM wyniki JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                     GROUP BY kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko
                                     ORDER BY suma DESC
                                     FETCH FIRST $top ROWS ONLY");
            } else {
                $can_res = oci_parse($conn, 
                                    "SELECT kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko, SUM(wyniki.ile) suma 
                                     FROM wyniki JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                     GROUP BY kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko
                                     ORDER BY suma DESC");
            }
            oci_execute($can_res, OCI_NO_AUTO_COMMIT);

            $can_res_len = oci_parse($conn, "SELECT COUNT(*) FROM kandydat");
            oci_execute($can_res_len, OCI_NO_AUTO_COMMIT);
            $can_res_len = oci_fetch_array($can_res_len, OCI_BOTH);
            $can_res_len = $can_res_len[0];
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
        <H1> Województwa </H1>
        <div class = "row">
            <div class = "column">
                <table>
                    <tr>
                        <th> Numer </th>
                        <th> Nazwa </th>
                    </tr>
                    <?PHP
                        $num= 1;
                        while ($row = oci_fetch_array($stmt, OCI_BOTH)) {
                            echo "<tr>";
                            echo "<th>".$num."</th>";
                            echo "<th><a href=\"powiaty.php?id=".$row['ID']."\">".$row['NAZWA']."</a></th>";
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
                    if ($top != 'all' && $top < $com_res_len) {
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
                    if ($top != 'all' && $top < $can_res_len) {
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
