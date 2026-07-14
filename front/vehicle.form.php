<?php

// controlador do formulario do asset Veiculo (add/update/delete/purge), padrao CommonDBTM.
// sem Session::checkCSRF() manual: no GLPI 11 o Symfony consome o token primeiro.

include('../../../inc/includes.php');

$vehicle = new \GlpiPlugin\Vehiclereservation\Vehicle();

if (isset($_POST['add'])) {
    $vehicle->check(-1, CREATE, $_POST);
    $vehicle->add($_POST);
    \Html::back();
} elseif (isset($_POST['update'])) {
    $vehicle->check((int) $_POST['id'], UPDATE, $_POST);
    $vehicle->update($_POST);
    \Html::back();
} elseif (isset($_POST['delete'])) {
    $vehicle->check((int) $_POST['id'], DELETE, $_POST);
    $vehicle->delete($_POST);
    $vehicle->redirectToList();
} elseif (isset($_POST['restore'])) {
    $vehicle->check((int) $_POST['id'], DELETE, $_POST);
    $vehicle->restore($_POST);
    $vehicle->redirectToList();
} elseif (isset($_POST['purge'])) {
    $vehicle->check((int) $_POST['id'], PURGE, $_POST);
    $vehicle->delete($_POST, 1);
    $vehicle->redirectToList();
} else {
    $id = (int) ($_GET['id'] ?? 0);

    \Html::header(
        \GlpiPlugin\Vehiclereservation\Vehicle::getTypeName(\Session::getPluralNumber()),
        $_SERVER['PHP_SELF'],
        'assets',
        \GlpiPlugin\Vehiclereservation\Vehicle::class
    );

    $vehicle->display(['id' => $id]);

    \Html::footer();
}
