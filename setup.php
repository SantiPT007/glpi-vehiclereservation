<?php

// unico ficheiro carregado pelo GLPI 11 para descoberta e init do plugin

if (!defined('PLUGIN_VEHICLERESERVATION_VERSION')) {
    define('PLUGIN_VEHICLERESERVATION_VERSION', '1.0.0');
}
if (!defined('PLUGIN_VEHICLERESERVATION_MIN_GLPI')) {
    define('PLUGIN_VEHICLERESERVATION_MIN_GLPI', '11.0.0');
}
if (!defined('PLUGIN_VEHICLERESERVATION_MAX_GLPI')) {
    define('PLUGIN_VEHICLERESERVATION_MAX_GLPI', '11.0.99');
}

function plugin_version_vehiclereservation(): array
{
    return [
        'name'         => 'Gestão e Reserva de Veículos',
        'version'      => PLUGIN_VEHICLERESERVATION_VERSION,
        'author'       => 'Santiago Almendra',
        'license'      => 'GPL v2+',
        'homepage'     => 'https://github.com/SantiPT007/glpi-vehiclereservation',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_VEHICLERESERVATION_MIN_GLPI,
                'max' => PLUGIN_VEHICLERESERVATION_MAX_GLPI,
            ],
        ],
    ];
}

function plugin_vehiclereservation_check_prerequisites(): bool
{
    if (version_compare(GLPI_VERSION, PLUGIN_VEHICLERESERVATION_MIN_GLPI, 'lt')) {
        echo sprintf(__('This plugin requires GLPI >= %s', 'vehiclereservation'), PLUGIN_VEHICLERESERVATION_MIN_GLPI);
        return false;
    }

    // dependencia obrigatoria: as notificacoes/cron das reservas de veiculos sao fornecidas
    // pelo Reservation Alert (as reservas de veiculo sao reservas nativas, ver pelo cron dele).
    if (!(new Plugin())->isActivated('reservationalert')) {
        echo __('Requires the "Reservation Alert" (reservationalert) plugin installed and enabled — it provides the tray notifications and the reservations cron.', 'vehiclereservation');
        return false;
    }

    return true;
}

function plugin_vehiclereservation_check_config(bool $verbose = false): bool
{
    return true;
}

// init, corre em cada pedido autenticado
function plugin_init_vehiclereservation(): void
{
    /** @var array $PLUGIN_HOOKS */
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['vehiclereservation'] = true;

    $plugin = new Plugin();
    if (!$plugin->isActivated('vehiclereservation')) {
        return;
    }

    // torna o veiculo um asset reservavel pelo motor NATIVO de reservas do GLPI:
    // acrescenta o itemtype a $CFG_GLPI['reservation_types'] (ver Plugin::registerClass)
    Plugin::registerClass('GlpiPlugin\\Vehiclereservation\\Vehicle', [
        'reservation_types' => true,
    ]);

    // entrada de menu propria, na seccao Ativos, com icone de veiculo
    $PLUGIN_HOOKS['menu_toadd']['vehiclereservation'] = [
        'assets' => 'GlpiPlugin\\Vehiclereservation\\Vehicle',
    ];

    // sem pagina de configuracao propria: notificacoes/cron vem do Reservation Alert e as
    // reservas usam o motor nativo. Reativar config_page no futuro para a integracao Verizon Connect.
}

function plugin_vehiclereservation_install(): bool
{
    return plugin_vehiclereservation_do_install();
}

function plugin_vehiclereservation_uninstall(): bool
{
    return plugin_vehiclereservation_do_uninstall();
}
