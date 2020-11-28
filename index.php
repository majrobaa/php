<?php function read_contents($filename)
{
    if (is_file($filename)) {
        $raw_content = file_get_contents($filename);
        return json_decode($raw_content, true);
    } else {
        fopen($filename, 'w');
        return $return = [];
    }
}

function write_contents($filename, $contents)
{
    return file_put_contents($filename, json_encode($contents));
}

function insert($filename, $element)
{
    $contents = read_contents($filename);
    if ($contents !== false) {
        if (!check_email_exist($element['email'], $contents)) {
            $contents[] = $element;
            if (write_contents($filename, $contents) !== false) {
                return true;
            }
        }
    }
    return false;
}

$room_types = $error = [];

$contents = read_contents('inp.json');
$rooms = $contents['szobak'];
$guests = $contents['vendegek'];
foreach ($rooms as $room) {
    if (!in_array($room['tipus'], $room_types)) {
        $room_types[] = $room['tipus'];
    }
}

$room_filter = $balcony_filter = $type_filter = '';
if (!empty($_POST)) {
    if (isset($_POST['kereso'])) {
        $room_filter = $_POST['kereso'];
    }

    if (isset($_POST['erkely'])) {
        $balconies = [
            'Van',
            'Nincs',
            'Mindegy'
        ];
        if (in_array($_POST['erkely'], $balconies)) {
            $balcony_filter = $_POST['erkely'];
        } else {
            $error['balcony'] = 'A megadott adat hibás';
        }
    } else {
        $error['balcony'] = 'Szűréshez válasz az erkély lehetőségek közül';
    }

    if ($_POST['tipus'] != 'összes') {
        $type_filter = $_POST['tipus'];
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP csoport ZH - NEPTUNKÓD</title>
</head>
<style>
    table {
        border-collapse: collapse;
    }

    td, th {
        border: 1px solid black;
        padding: 5px;
        text-align: center;
    }
</style>
<body>
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
    Szobaszám:
    <input name="kereso" value="<?= isset($_POST['kereso']) ? $_POST['kereso'] : '' ?>"><br>
    Erkély:
    <input type="radio" name="erkely"
           value="Van" <?= isset($_POST['erkely']) && $_POST['erkely'] == 'Van' ? 'checked' : '' ?>> Van
    <input type="radio" name="erkely"
           value="Nincs" <?= isset($_POST['erkely']) && $_POST['erkely'] == 'Nincs' ? 'checked' : '' ?>> Nincs
    <input type="radio" name="erkely"
           value="Mindegy" <?= isset($_POST['erkely']) && $_POST['erkely'] == 'Mindegy' ? 'checked' : '' ?>> Mindegy<br>
    Típus:
    <select name="tipus">
        <option value="összes" <?= isset($_POST['tipus']) && $_POST['tipus'] == 'összes' ? 'selected' : '' ?>>összes
        </option>
        <?php
        foreach ($room_types as $type) {
            ?>
            <option value="<?= $type ?>" <?= isset($_POST['tipus']) && $_POST['tipus'] == $type ? 'selected' : '' ?>><?= ucfirst($type) ?></option>
            <?php
        }
        ?>
    </select><br><br>
    <input type="submit" value="Szűrés">
</form>

<br><br>

<div style="<?= count($error) != 0 ? 'color:red;font-size:18px;' : 'display:none' ?>">
    <?php foreach ($error as $msg) {
        echo $msg . '<br>';
    } ?>
</div>

<br><br>

<table>
    <tr>
        <th>Szobaszám</th>
        <th>Típus</th>
        <th>Erkély</th>
        <th>Vendégek</th>
    </tr>
    <?php
    if (count($error) == 0) {
        foreach ($rooms as $room) {
            $show = true;
            if ($room_filter) {
                if ($room_filter != $room['szobaszam']) $show = false;
            }
            if ($type_filter) {
                if ($type_filter != $room['tipus']) $show = false;
            }
            if ($balcony_filter) {
                switch ($balcony_filter) {
                    case'Van':
                        if (!$room['erkely']) $show = false;
                        break;
                    case'Nincs':
                        if ($room['erkely']) $show = false;
                        break;
                }
            }
            if ($show) {
                ?>
                <tr style="background-color:<?= !$room['foglalt'] ? 'green' : 'red' ?>">
                    <td><?= $room['szobaszam'] ?></td>
                    <td><?= $room['tipus'] ?></td>
                    <td><?= $room['erkely'] ? '&#10004;' : '&#10008;' ?></td>
                    <td><?php
                        foreach ($room['vendegek'] as $room_guest) {
                            echo $guests[$room_guest]['nev'] . '<br>';
                        }
                        ?></td>
                </tr>
                <?php
            }
        }
    }
    ?>
</table>
</body>
</html>
