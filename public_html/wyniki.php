<HTML>
    <HEAD>
        <TITLE> Wyniki </TITLE>
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

            $lok_id = $_GET['id'];

            $tmp = oci_parse($conn, "SELECT siedziba FROM lokal WHERE id = $lok_id");
            oci_execute($tmp, OCI_NO_AUTO_COMMIT);
            $tmp = oci_fetch_array($tmp, OCI_BOTH);
            $tmp = $tmp[0];

            $com_res = oci_parse($conn, 
                                 "SELECT komitet.id, komitet.nazwa, SUM(wyniki.ile) suma 
                                  FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                       JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                       JOIN komitet ON kandydat.id_komitetu = komitet.id 
                                  WHERE lokal.id = $lok_id
                                  GROUP BY komitet.id, komitet.nazwa
                                  ORDER BY suma DESC");
            oci_execute($com_res, OCI_NO_AUTO_COMMIT);

            $sum = oci_parse($conn, 
                             "SELECT SUM(wyniki.ile) suma 
                              FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                              WHERE lokal.id = $lok_id");
            oci_execute($sum, OCI_NO_AUTO_COMMIT);
            $sum = oci_fetch_array($sum, OCI_BOTH);
            $sum = $sum[0];

            $can_res = oci_parse($conn, 
                                 "SELECT kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko, SUM(wyniki.ile) suma 
                                  FROM wyniki JOIN lokal ON wyniki.id_lokalu = lokal.id
                                       JOIN kandydat ON wyniki.id_kandydata = kandydat.id
                                  WHERE lokal.id = $lok_id
                                  GROUP BY kandydat.id, kandydat.pierwsze_imie, kandydat.drugie_imie, kandydat.nazwisko
                                  ORDER BY suma DESC, kandydat.nazwisko, kandydat.pierwsze_imie, kandydat.drugie_imie");
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
        <H1> Wyniki </H1>
        <?PHP echo "<H3> Lokal: $tmp </H3>"; ?>
        <div class = "row">
            <div class = "column">
                <H2> Wyniki komitetów </H2>
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
            </div>
            <div class = "column">
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