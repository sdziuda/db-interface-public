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

            $kom_res = '';

            if (isset($_POST['save'])) {
                if (!empty($_POST['search'])) {
                    $search = $_POST['search'];
                    $search = mb_strtoupper($search, 'UTF-8');
                    $kom_res = oci_parse($conn, "SELECT id, nazwa FROM komitet WHERE nazwa LIKE '%$search%' ORDER BY nazwa");
                    oci_execute($kom_res, OCI_NO_AUTO_COMMIT);
                }
            } else {
                $kom_res = oci_parse($conn, "SELECT id, nazwa FROM komitet ORDER BY nazwa");
                oci_execute($kom_res, OCI_NO_AUTO_COMMIT);
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
        <h1> Wyszukiwarka komitetów </h1>
        <div class="column-centered">
            <form action="#" method="post">
                <input type="text" name="search" placeholder="Wyszukaj komitet">
                <input type="submit" name="save" value="Szukaj">
            </form>
            <script>
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            </script>
        </div>
        <?php
            if(!$kom_res) {
                echo "<h3> Nie znaleziono komitetów </h3>";
            } else {
                $row = oci_fetch_array($kom_res, OCI_BOTH);
                if (!$row) {
                    echo "<h3> Nie znaleziono komitetów </h3>";
                } else {
                    echo "<table>";
                        echo "<tr>";
                            echo "<th> Numer </th>";
                            echo "<th> Nazwa </th>";
                        echo "</tr>";
                        $num = 1;
                        do {
                            echo "<tr>";
                            echo "<th>".$num."</th>";
                            echo "<th><a href=\"komitety.php?id=".$row['ID']."\">".$row['NAZWA']."</a></th>";
                            echo "</tr>";
                            $num++;
                        } while ($row = oci_fetch_array($kom_res, OCI_BOTH));
                    echo "</table>";
                }
            }
        ?>
    </BODY>
</HTML>