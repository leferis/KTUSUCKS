<?php
function AllUsers()
{
    global $database;
    global $session;
    $q = "SELECT * "
        . "FROM " . TBL_SKOLOS . " ORDER BY id";
    $result = $database->query($q);
    /* Error occurred, return given name by default */
    $num_rows = mysqli_num_rows($result);
    if (!$result || ($num_rows < 0)) {
        echo "Error displaying info";
        return;
    }
    if ($num_rows == 0) {
        echo "Lentelė tuščia.";
        return;
    }


    if ($session->isAdmin()) {
        $q = "SELECT * "
            . "FROM " . TBL_SKOLOS . " ORDER BY id";
        $result = $database->query($q);
        /* Display table contents */
        echo "<table align=\"left\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
        echo "<tr><td><b>Studento Vardas</b></td><td><b>Apmoketa</b></td><td><b>Nuo</b></td><td><b>Iki</b></td><td><b>Islaikyta</b></td><td><b>Verte</b></td><td><b>Kiekis</b></td><td><b>Destytojas</b></td><td><b>Pakeisti</b></td><td><b>Laiškas</b></td></tr>\n";
        while ($r = mysqli_fetch_assoc($result)) {
            $id = $r['Id'];
            $vardas = $r['vartotojo_vardas'];
            $Nuos = '';
            $Ikis = '';
            $apmoketas = '';
            $apmoketas = $r['Ampoketa'] == 0 ? 'Ne' : 'Taip';
            $Nuos = $r['Nuo'];
            $Ikis = $r['Iki'];
            $islaikyta = $r['Islaikyta'] == 0 ? 'Ne' : 'Taip';
            $vert = $r['verte'];
            $kiekis = $r['kiekis'];
            $destytojas = $r['Destytojo_id'];
            $ulevelchange = '<form action="process.php" method="POST">
                                <input type="hidden" name="upduser" value="' . $id . '">
                                <input type="hidden" name="subuppakeista" value="1">
                                 <select name="updApmoketqa">
                                    <option value="0" ' . ($apmoketas == 'Ne' ? 'selected' : '') . '>Ne</option>
                                    <option value="1" ' . ($apmoketas == 'Taip' ? 'selected' : '') . '>Taip</option>
                                </select>
                                </td>
                                <td>
                                <input id="Nuoo" type="date" name="Nuoo" value="' . $Nuos . '" >
</td>
<td>
<input id="Ikis" type="date" name="Ikis" value="' . $Ikis . '" >
</td>
<td>
' . $islaikyta . '
</td>
<td>
  <input id="Vertee" type="Number" name="Vertee" value="' . $vert . '" >
</td>
<td>
<input id="Kiekis" type="Number" name="Kiekis" value="' . $kiekis . '" >
</td>
 <td>' . $destytojas . '</td>
<td><input type="submit" value="Keisti"></td>
                    </form>
                  ';
            $but= '<form action="process.php" method="POST">
                                <input type="hidden" name="SendMail" value="' . $id . '"
                                <td><input type="submit" value="Siusti"></td>
                    </form>
                              </tr></tr>   ';
            echo "<tr><td>$vardas</td><td>$ulevelchange</td><td>$but</td>";
        }

        echo "</table><br>\n";
    } else if ($session->isManager()) {
        $q = "SELECT * "
            . "FROM " . TBL_SKOLOS . " WHERE Destytojo_id =" . "'$session->username'" . " ORDER BY id";
        $result = $database->query($q);
        /* Display table contents */
        echo "<table align=\"left\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
        echo "<tr><td><b>Studento Vardas</b></td><td><b>Apmoketa</b></td><td><b>Nuo</b></td><td><b>Iki</b></td><td><b>Islaikyta</b></td><td><b>Verte</b></td><td><b>Kiekis</b></td><td><b>Destytojas</b></td></tr>\n";
        while ($r = mysqli_fetch_assoc($result)) {
            $id = $r['Id'];
            $vardas = $r['vartotojo_vardas'];
            $Nuos = '';
            $Ikis = '';
            $apmoketas = '';
            $apmoketas = $r['Ampoketa'] == 0 ? 'Ne' : 'Taip';
            $Nuos = $r['Nuo'];
            $Ikis = $r['Iki'];

            $islaikyta = $r['Islaikyta'] == 0 ? 'Ne' : 'Taip';
            if ($apmoketas == 'Taip') {
                $ulevelchange = '<form action="process.php" method="POST">
                        
                                <input type="hidden" name="upduser" value="' . $id . '">
                                <input type="hidden" name="subupdislaikyta" value="1">
                                <select name="updlevel" onChange="alert(\' Pakeistas Skolos Islaikymo statusas!\');submit();">
                                    <option value="0" ' . ($islaikyta == 'Ne' ? 'selected' : '') . '>Ne</option>
                                    <option value="1" ' . ($islaikyta == 'Taip' ? 'selected' : '') . '>Taip</option>
                                </select>

                    </form>';
            } else {
                $ulevelchange = $islaikyta;
            }
            $vert = $r['verte'];
            $kiekis = $r['kiekis'];
            $destytojas = $r['Destytojo_id'];
            echo "<tr><td>$vardas</td><td>$apmoketas</td><td>$Nuos</td><td>$Ikis</td><td>$ulevelchange</td><td>$vert</td><td>$kiekis</td><td>$destytojas</td></tr></tr>\n";
        }

        echo "</table><br>\n";
    } else {
        $q = "SELECT * "
            . "FROM " . TBL_SKOLOS . " WHERE vartotojo_vardas =" . "'$session->username'" . " ORDER BY id";
        $result = $database->query($q);
        /* Display table contents */
        echo "<table align=\"left\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\">\n";
        echo "<tr><td><b>Studento Vardas</b></td><td><b>Apmoketa</b></td><td><b>Nuo</b></td><td><b>Iki</b></td><td><b>Islaikyta</b></td><td><b>Verte</b></td><td><b>Kiekis</b></td><td><b>Destytojas</b></td></tr>\n";
        while ($r = mysqli_fetch_assoc($result)) {
            $vardas = $r['vartotojo_vardas'];
            $Nuos = '';
            $Ikis = '';
            $apmoketas = '';
            $apmoketas = $r['Ampoketa'] == 0 ? 'Ne' : 'Taip';
            $Nuos = $r['Nuo'];
            $Ikis = $r['Iki'];
            $islaikyta = $r['Islaikyta'] == 0 ? 'Ne' : 'Taip';
            $vert = $r['verte'];
            $kiekis = $r['kiekis'];
            $destytojas = $r['Destytojo_id'];
            echo "<tr><td>$vardas</td><td>$apmoketas</td><td>$Nuos</td><td>$Ikis</td><td>$islaikyta</td><td>$vert</td><td>$kiekis</td><td>$destytojas</td></tr></tr>\n";
        }

        echo "</table><br>\n";
    }
}

include("include/session.php");
if ($session->logged_in) {
    ?>
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=9; text/html; charset=utf-8"/>
        <title>Operacija2</title>
        <link href="include/styles.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
    <table class="center">
        <tr>
            <td>
                <img src="pictures/top.png"/>
            </td>
        </tr>
        <tr>
            <td>
                <?php
                //Jei vartotojas prisijungęs
                ?>
                <table style="border-width: 2px; border-style: dotted;">
                    <tr>
                        <td>
                            Atgal į [<a href="index.php">Pradžia</a>]
                        </td>
                    </tr>
                </table>
                <br>
                <?php
                Allusers();
                ?>
                <br>
        <tr>
            <td>
                <?php
                include("include/footer.php");
                ?>
            </td>
        </tr>
    </table>
    </body>
    </html>
    <?php
    //Jei vartotojas neprisijungęs, užkraunamas pradinis puslapis  
} else {
    header("Location: index.php");
}
?>