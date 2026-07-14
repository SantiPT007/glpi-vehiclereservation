<?php

// lista/pesquisa de veiculos (pesquisa nativa do GLPI)

include('../../../inc/includes.php');

\Session::checkRight('plugin_vehiclereservation_vehicle', READ);

\Html::header(
    \GlpiPlugin\Vehiclereservation\Vehicle::getTypeName(\Session::getPluralNumber()),
    $_SERVER['PHP_SELF'],
    'assets',
    \GlpiPlugin\Vehiclereservation\Vehicle::class
);

\Search::show(\GlpiPlugin\Vehiclereservation\Vehicle::class);

\Html::footer();
