<?php

namespace GlpiPlugin\Vehiclereservation;

use CommonDBTM;
use Entity;
use Html;
use Location;
use Log;
use Notepad;
use Reservation;
use Session;

// novo tipo de asset "Veiculo", reservavel pelo motor nativo de reservas do GLPI 11.
// os campos quem-reservou/data/hora NAO vivem aqui: vem do sistema nativo de reservas.
class Vehicle extends CommonDBTM
{
    // direito proprio do asset (gestao). a reserva em si usa o direito nativo 'reservation'
    public static $rightname = 'plugin_vehiclereservation_vehicle';

    // ativa o separador de Histórico
    public $dohistory = true;

    public static function getTypeName($nb = 0)
    {
        return _n('Vehicle', 'Vehicles', $nb, 'vehiclereservation');
    }

    public static function getIcon()
    {
        return 'ti ti-car';
    }

    // nome legivel (lista de reservas nativa, notificacoes, etc.):
    // 1) o campo "name" se preenchido; 2) "Marca Modelo (Matrícula)"; 3) matricula; 4) marca/modelo.
    // getFriendlyName() e final no GLPI 11 -> sobrepoe-se computeFriendlyName()
    public function computeFriendlyName()
    {
        $name = trim((string) ($this->fields['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $label = trim(($this->fields['make'] ?? '') . ' ' . ($this->fields['model'] ?? ''));
        $plate = trim((string) ($this->fields['license_plate'] ?? ''));

        if ($label !== '' && $plate !== '') {
            return "{$label} ({$plate})";
        }
        if ($plate !== '') {
            return $plate;
        }
        if ($label !== '') {
            return $label;
        }
        return parent::computeFriendlyName();
    }

    public static function getMenuContent()
    {
        if (!self::canView()) {
            return false;
        }

        $menu = [
            'title' => self::getTypeName(Session::getPluralNumber()),
            'page'  => self::getSearchURL(false),
            'icon'  => self::getIcon(),
            'links' => [
                'search' => self::getSearchURL(false),
            ],
        ];

        if (self::canCreate()) {
            $menu['links']['add'] = self::getFormURL(false);
        }

        return $menu;
    }

    public function defineTabs($options = [])
    {
        $tabs = [];
        $this->addDefaultFormTab($tabs);
        // separador de Reservas nativo: mostra o controlo "permitir reservas" e a lista de reservas
        $this->addStandardTab(Reservation::class, $tabs, $options);
        $this->addStandardTab(Log::class, $tabs, $options);
        $this->addStandardTab(Notepad::class, $tabs, $options);
        return $tabs;
    }

    // campo numerico opcional deixado em branco chega como '' e o MariaDB em
    // modo estrito rejeita '' num SMALLINT — normalizar para NULL
    private function normalizeNumericInput(array $input): array
    {
        if (array_key_exists('year', $input) && trim((string) $input['year']) === '') {
            $input['year'] = null;
        }
        return $input;
    }

    public function prepareInputForAdd($input)
    {
        if (empty(trim((string) ($input['license_plate'] ?? '')))) {
            Session::addMessageAfterRedirect(
                __('License plate is required.', 'vehiclereservation'),
                false,
                ERROR
            );
            return false;
        }
        return $this->normalizeNumericInput($input);
    }

    public function prepareInputForUpdate($input)
    {
        if (array_key_exists('license_plate', $input) && empty(trim((string) $input['license_plate']))) {
            Session::addMessageAfterRedirect(
                __('License plate is required.', 'vehiclereservation'),
                false,
                ERROR
            );
            return false;
        }
        return $this->normalizeNumericInput($input);
    }

    public function showForm($id, array $options = [])
    {
        $this->initForm($id, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Name', 'vehiclereservation') . '</td>';
        echo '<td>' . Html::input('name', ['value' => $this->fields['name'] ?? '']) . '</td>';
        echo '<td>' . __('License plate', 'vehiclereservation') . " <span class='red'>*</span></td>";
        echo '<td>' . Html::input('license_plate', [
            'value'    => $this->fields['license_plate'] ?? '',
            'required' => 'required',
        ]) . '</td>';
        echo '</tr>';

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Make', 'vehiclereservation') . '</td>';
        echo '<td>' . Html::input('make', ['value' => $this->fields['make'] ?? '']) . '</td>';
        echo '<td>' . __('Model', 'vehiclereservation') . '</td>';
        echo '<td>' . Html::input('model', ['value' => $this->fields['model'] ?? '']) . '</td>';
        echo '</tr>';

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Year', 'vehiclereservation') . '</td>';
        echo '<td>' . Html::input('year', [
            'value' => $this->fields['year'] ?? '',
            'type'  => 'number',
            'min'   => '1900',
            'max'   => '2100',
        ]) . '</td>';
        echo '<td>' . __('VIN (chassis number)', 'vehiclereservation') . '</td>';
        echo '<td>' . Html::input('vin', ['value' => $this->fields['vin'] ?? '']) . '</td>';
        echo '</tr>';

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . Entity::getTypeName(1) . '</td>';
        echo '<td>';
        Entity::dropdown(['value' => $this->fields['entities_id'] ?? ($_SESSION['glpiactive_entity'] ?? 0)]);
        echo '</td>';
        echo '<td>' . Location::getTypeName(1) . '</td>';
        echo '<td>';
        Location::dropdown(['value' => $this->fields['locations_id'] ?? 0]);
        echo '</td>';
        echo '</tr>';

        echo "<tr class='tab_bg_1'>";
        echo '<td>' . __('Comment', 'vehiclereservation') . '</td>';
        echo "<td colspan='3'><textarea class='form-control' name='comment' rows='3'>"
            . htmlspecialchars((string) ($this->fields['comment'] ?? ''), ENT_QUOTES)
            . '</textarea></td>';
        echo '</tr>';

        $this->showFormButtons($options);
        return true;
    }

    public function rawSearchOptions()
    {
        $table = self::getTable();

        $opts = [];

        $opts[] = ['id' => 'common', 'name' => self::getTypeName(Session::getPluralNumber())];

        $opts[] = [
            'id'            => '1',
            'table'         => $table,
            'field'         => 'name',
            'name'          => __('Name', 'vehiclereservation'),
            'datatype'      => 'itemlink',
            'massiveaction' => false,
        ];

        $opts[] = [
            'id'            => '2',
            'table'         => $table,
            'field'         => 'id',
            'name'          => __('ID'),
            'datatype'      => 'number',
            'massiveaction' => false,
        ];

        $opts[] = [
            'id'       => '3',
            'table'    => $table,
            'field'    => 'license_plate',
            'name'     => __('License plate', 'vehiclereservation'),
            'datatype' => 'string',
        ];

        $opts[] = [
            'id'       => '4',
            'table'    => $table,
            'field'    => 'make',
            'name'     => __('Make', 'vehiclereservation'),
            'datatype' => 'string',
        ];

        $opts[] = [
            'id'       => '5',
            'table'    => $table,
            'field'    => 'model',
            'name'     => __('Model', 'vehiclereservation'),
            'datatype' => 'string',
        ];

        $opts[] = [
            'id'       => '6',
            'table'    => $table,
            'field'    => 'year',
            'name'     => __('Year', 'vehiclereservation'),
            'datatype' => 'number',
        ];

        $opts[] = [
            'id'       => '7',
            'table'    => $table,
            'field'    => 'vin',
            'name'     => 'VIN',
            'datatype' => 'string',
        ];

        $opts[] = [
            'id'       => '8',
            'table'    => $table,
            'field'    => 'comment',
            'name'     => __('Comment', 'vehiclereservation'),
            'datatype' => 'text',
        ];

        $opts[] = [
            'id'       => '9',
            'table'    => 'glpi_locations',
            'field'    => 'completename',
            'name'     => Location::getTypeName(1),
            'datatype' => 'dropdown',
        ];

        $opts[] = [
            'id'            => '19',
            'table'         => $table,
            'field'         => 'date_mod',
            'name'          => __('Last update'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
        ];

        $opts[] = [
            'id'       => '80',
            'table'    => 'glpi_entities',
            'field'    => 'completename',
            'name'     => Entity::getTypeName(1),
            'datatype' => 'dropdown',
        ];

        $opts[] = [
            'id'            => '121',
            'table'         => $table,
            'field'         => 'date_creation',
            'name'          => __('Creation date'),
            'datatype'      => 'datetime',
            'massiveaction' => false,
        ];

        return $opts;
    }
}
