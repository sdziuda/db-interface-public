<?php
    session_start();
    putenv("NLS_LANG=polish_poland.utf8");
    $conn = oci_connect($_SESSION['LOGIN'], $_SESSION['PASS'], "//labora.mimuw.edu.pl/LABS", 'UTF-8');
    
    if (!$conn) {
        echo "oci_connect failed\n";
        $e = oci_error();
        echo $e['message'];
    }

    $lok = oci_parse($conn, 
                    "SELECT * 
                    FROM lokal JOIN gmina ON lokal.id_gminy = gmina.id
                    WHERE gmina.nazwa = 'Bielany' OR gmina.nazwa = 'Bemowo' OR gmina.nazwa = 'Żoliborz' OR gmina.nazwa = 'Wola' OR gmina.nazwa = 'Śródmieście' OR gmina.nazwa = 'Ochota' OR gmina.nazwa = 'Włochy' OR gmina.nazwa = 'Ursus' OR gmina.nazwa = 'Mokotów' OR gmina.nazwa = 'Ursynów' OR gmina.nazwa = 'Wilanów'");
    oci_execute($lok, OCI_NO_AUTO_COMMIT);
    
    $vec = array();
    while ($row = oci_fetch_array($lok, OCI_BOTH)) {
        $vec[] = $row;
    }

    echo json_encode($vec);
?>