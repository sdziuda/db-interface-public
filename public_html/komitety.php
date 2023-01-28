<HTML>
    <HEAD>
        <TITLE> Komitety </TITLE>
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
                $top = 10;
            }

            $pow = filter_input(INPUT_POST, 'pow', FILTER_SANITIZE_STRING);
            $gm = filter_input(INPUT_POST, 'gm', FILTER_SANITIZE_STRING);

            $kom_id = $_GET['id'];

            $tmp = oci_parse($conn, "SELECT nazwa FROM komitet WHERE id = $kom_id");
            oci_execute($tmp, OCI_NO_AUTO_COMMIT);
            $tmp = oci_fetch_array($tmp, OCI_BOTH);
            $tmp = $tmp[0];

            $pow_res = oci_parse($conn, 
                                 "SELECT powiat.nazwa, SUM(wyniki.ile) suma 
                                  FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                       JOIN gmina ON lokal.id_gminy = gmina.id
                                       JOIN powiat ON gmina.id_powiatu = powiat.id
                                       JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                       JOIN komitet ON kandydat.id_komitetu = komitet.id 
                                  WHERE komitet.id = $kom_id
                                  GROUP BY powiat.id, powiat.nazwa
                                  ORDER BY suma DESC
                                  FETCH FIRST $top ROWS ONLY");
            oci_execute($pow_res, OCI_NO_AUTO_COMMIT);

            $sum = oci_parse($conn, 
                             "SELECT SUM(wyniki.ile) suma 
                              FROM wyniki JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                   JOIN komitet ON kandydat.id_komitetu = komitet.id
                              WHERE komitet.id = $kom_id");
            oci_execute($sum, OCI_NO_AUTO_COMMIT);
            $sum = oci_fetch_array($sum, OCI_BOTH);
            $sum = $sum[0];

            $can_res = oci_parse($conn, 
                                 "SELECT kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko, SUM(wyniki.ile) suma 
                                  FROM wyniki JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                       JOIN komitet ON kandydat.id_komitetu = komitet.id
                                  WHERE komitet.id = $kom_id
                                  GROUP BY kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko
                                  ORDER BY suma DESC, kandydat.nazwisko, kandydat.pierwsze_imie, kandydat.drugie_imie");
            oci_execute($can_res, OCI_NO_AUTO_COMMIT);

            $pow_res_all = oci_parse($conn, 
                            "SELECT powiat.id, powiat.nazwa, SUM(wyniki.ile) suma 
                            FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                JOIN gmina ON lokal.id_gminy = gmina.id
                                JOIN powiat ON gmina.id_powiatu = powiat.id
                                JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                JOIN komitet ON kandydat.id_komitetu = komitet.id 
                            WHERE komitet.id = $kom_id
                            GROUP BY powiat.id, powiat.nazwa
                            ORDER BY suma DESC");
            oci_execute($pow_res_all, OCI_NO_AUTO_COMMIT);

            if ($pow) {
                $gm_res_all = oci_parse($conn, 
                                        "SELECT gmina.id, gmina.nazwa, SUM(wyniki.ile) suma 
                                        FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                            JOIN gmina ON lokal.id_gminy = gmina.id
                                            JOIN powiat ON gmina.id_powiatu = powiat.id
                                            JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                            JOIN komitet ON kandydat.id_komitetu = komitet.id 
                                        WHERE komitet.id = $kom_id AND powiat.id = $pow
                                        GROUP BY gmina.id, gmina.nazwa
                                        ORDER BY suma DESC");
                oci_execute($gm_res_all, OCI_NO_AUTO_COMMIT);
            } else {
                $gm_res_all = '';
            }

            if ($gm) {
                $pow_this = oci_parse($conn, "SELECT powiat.id FROM gmina JOIN powiat ON gmina.id_powiatu = powiat.id WHERE gmina.id = $gm");
                oci_execute($pow_this, OCI_NO_AUTO_COMMIT);
                $pow_this = oci_fetch_array($pow_this, OCI_BOTH);
                $pow_this = $pow_this[0];

                $gm_res_all = oci_parse($conn, 
                                        "SELECT gmina.id, gmina.nazwa, SUM(wyniki.ile) suma 
                                        FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                            JOIN gmina ON lokal.id_gminy = gmina.id
                                            JOIN powiat ON gmina.id_powiatu = powiat.id
                                            JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                            JOIN komitet ON kandydat.id_komitetu = komitet.id 
                                        WHERE komitet.id = $kom_id AND powiat.id = $pow_this
                                        GROUP BY gmina.id, gmina.nazwa
                                        ORDER BY suma DESC");
                oci_execute($gm_res_all, OCI_NO_AUTO_COMMIT);
                
                $lok_res_all = oci_parse($conn, 
                                        "SELECT lokal.id, lokal.siedziba, SUM(wyniki.ile) suma 
                                        FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                            JOIN gmina ON lokal.id_gminy = gmina.id
                                            JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                            JOIN komitet ON kandydat.id_komitetu = komitet.id 
                                        WHERE komitet.id = $kom_id AND gmina.id = $gm
                                        GROUP BY lokal.id, lokal.siedziba
                                        ORDER BY suma DESC");
                oci_execute($lok_res_all, OCI_NO_AUTO_COMMIT);
            } else {
                $lok_res_all = '';
            }
            
            $num = 0;
            $vec_lok = array();
            while ($row = oci_fetch_array($lok_res_all, OCI_BOTH)) {
                $vec_lok[$num] = $row;
                $num++;
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
        <H1> Komitet </H1>
        <?PHP echo "<H2> $tmp </H2>"; ?>
        <div class = "row">
            <div class = "column">
                <div class="row-left">
                    <form action="#" method="post">
                        <label for="pow">Powiat: </label>
                        <select name="pow" id="pow">
                            <option value="brak" hidden>--Wybierz--</option>
                            <?PHP
                                $vec_pow = array();
                                $num = 0;
                                while ($row = oci_fetch_array($pow_res_all, OCI_BOTH)) {
                                    if ($pow == $row['ID']) {
                                        echo "<option value='".$row['ID']."' selected>".$row['NAZWA']."</option>";
                                    } else {
                                        echo "<option value='".$row['ID']."'>".$row['NAZWA']."</option>";
                                    }
                                    $vec_pow[$num] = $row;
                                    $num++;
                                }
                            ?>
                        </select>
                        <input type="submit" value="Zmień">
                    </form>
                    <script>
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }
                    </script>
                </div>
                <div class="row-left">
                    <form action="#" method="post">
                        <label for="gm">Gmina: </label>
                        <select name="gm" id="gm">
                            <option value="brak" hidden>--Wybierz--</option>
                            <?PHP
                                $vec_gm = array();
                                $num = 0;
                                while ($row = oci_fetch_array($gm_res_all, OCI_BOTH)) {
                                    if ($gm == $row['ID']) {
                                        echo "<option value='".$row['ID']."' selected>".$row['NAZWA']."</option>";
                                    } else {
                                        echo "<option value='".$row['ID']."'>".$row['NAZWA']."</option>";
                                    }
                                    $vec_gm[$num] = $row;
                                    $num++;
                                }
                            ?>
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
                    if ($pow) {
                        $vec = $vec_gm;
                    } elseif ($gm) {
                        $vec = $vec_lok;
                    } else {
                        $vec = $vec_pow;
                    }

                    $row = $vec[0];

                    if ($row) {
                        echo "<table>";
                        
                        echo "<tr>";
                        if ($pow) {
                            echo "<th>Gmina</th>";
                        } elseif ($gm) {
                            echo "<th>Lokal</th>";
                        } else {
                            echo "<th>Powiat</th>";
                        }
                        echo "<th>Suma głosów</th>";
                        echo "</tr>";
                        
                        $num = 0;
                        do {
                            echo "<tr>";
                            if ($gm) {
                                echo "<th>".$row['SIEDZIBA']."</th>";
                            } else {
                                echo "<th>".$row['NAZWA']."</th>";
                            }
                            echo "<th>".$row['SUMA']."</th>";
                            echo "</tr>";
                            $num++;
                        } while ($row = $vec[$num]);
                        
                        echo "</table>";
                    }
                ?>
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
                        </select>
                        <input type="submit" value="Zmień">
                    </form>
                    <script>
                        if (window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.href);
                        }
                    </script>
                </div>
                <?PHP echo "<h2> Top $top powiatów </h2>"; ?>
                <table>
                    <tr>
                        <th> Numer </th>
                        <th> Powiat </th>
                        <th> Ilość głosów </th>
                        <th> Procent głosów </th>
                    </tr>
                    <?PHP
                        $num = 1;
                        while ($row = oci_fetch_array($pow_res, OCI_BOTH)) {
                            echo "<tr>";
                            echo "<th>".$num."</th>";
                            echo "<th>".$row['NAZWA']."</th>";
                            echo "<th>".$row['SUMA']."</th>";
                            echo "<th>".round($row['SUMA']/$sum*100, 2)."%</th>";
                            echo "</tr>";
                            $num++;
                        }
                    ?>
                </table>
                <H2> Wyniki kandydatów </H2>
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