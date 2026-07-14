<?php

use GlpiPlugin\Vehiclereservation\Vehicle;

// instalacao: cria a tabela do asset e o direito de gestao para os admins
function plugin_vehiclereservation_do_install(): bool
{
    global $DB;

    $schema = Plugin::getPhpDir('vehiclereservation') . '/sql/install.sql';
    if (file_exists($schema)) {
        $DB->runFile($schema);
    }

    $migration = new Migration(PLUGIN_VEHICLERESERVATION_VERSION);

    // upgrade-safe: instalacoes antigas nao tinham locations_id, que a lista de reservas
    // nativa do GLPI exige (LEFT JOIN glpi_locations ON vehicles.locations_id). addField
    // so cria a coluna se faltar, sem perder dados.
    if (!$DB->fieldExists('glpi_plugin_vehiclereservation_vehicles', 'locations_id')) {
        $migration->addField('glpi_plugin_vehiclereservation_vehicles', 'locations_id', 'integer');
        $migration->addKey('glpi_plugin_vehiclereservation_vehicles', 'locations_id');
    }

    // direito proprio do asset, concedido a quem ja tem config UPDATE (super-admin etc.)
    $migration->addRight('plugin_vehiclereservation_vehicle', ALLSTANDARDRIGHT, ['config' => UPDATE]);
    $migration->executeMigration();

    return true;
}

// desinstalacao limpa: remove a tabela, o direito e os itens reservaveis orfaos do nosso tipo
function plugin_vehiclereservation_do_uninstall(): bool
{
    global $DB;

    // limpa as reservas e os itens reservaveis criados para o nosso itemtype (nao mexe no resto do core)
    $itemtype = Vehicle::class;
    $reservationitems_ids = [];
    foreach ($DB->request([
        'SELECT' => 'id',
        'FROM'   => 'glpi_reservationitems',
        'WHERE'  => ['itemtype' => $itemtype],
    ]) as $row) {
        $reservationitems_ids[] = (int) $row['id'];
    }
    if (!empty($reservationitems_ids)) {
        $DB->delete('glpi_reservations', ['reservationitems_id' => $reservationitems_ids]);
        $DB->delete('glpi_reservationitems', ['itemtype' => $itemtype]);
    }

    if ($DB->tableExists('glpi_plugin_vehiclereservation_vehicles')) {
        $DB->dropTable('glpi_plugin_vehiclereservation_vehicles');
    }

    // FUTURO: dropar glpi_plugin_vehiclereservation_trips quando a integracao Verizon existir

    ProfileRight::deleteProfileRights(['plugin_vehiclereservation_vehicle']);

    return true;
}
