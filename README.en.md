# glpi-vehiclereservation

🇵🇹 [Versão portuguesa aqui](README.md)

GLPI 11 plugin that adds a new asset type, **Vehicle**, and makes it reservable through
GLPI's native reservation engine — the same way computers or projectors already are. I
built this to manage reservations for a shared vehicle fleet.

The whole point is not reinventing anything: the calendar, availability, who reserved,
dates and times — GLPI already has all of that. The plugin just creates the asset with the
vehicle fields and registers it as reservable with
`Plugin::registerClass(..., ['reservation_types' => true])`. The rest is GLPI doing its job.

It has a sibling for meeting rooms: [glpi-roomreservation](https://github.com/SantiPT007/glpi-roomreservation)
(same architecture, different fields).

## Fields

| Field | Required | Notes |
|-------|:--------:|-------|
| Name | no | free label; if empty, "Make Model (Plate)" is shown |
| Make | no | e.g. Renault |
| Model | no | e.g. Clio |
| Year | no | 1900–2100 |
| License plate | **yes** | unique |
| VIN | no | chassis number |
| Location | no | garage/parking; the native reservation list uses this |

## Before installing

- GLPI 11.x, PHP 8.1+, MariaDB 10.11 or equivalent
- the [Reservation Alert](https://github.com/SantiPT007/glpi-reservationalert) plugin must
  be installed and enabled — vehicle reservations are native reservations
  (`glpi_reservations`) and that plugin already handles notifications and the cron for any
  reservation, so I didn't duplicate that code here. Installation is blocked if it's not
  active.

## Installing

```bash
cd /var/www/glpi/plugins
git clone https://github.com/SantiPT007/glpi-vehiclereservation vehiclereservation
chown -R www-data:www-data vehiclereservation
```

The folder really has to be called `vehiclereservation`, GLPI uses the folder name as the
plugin identifier. Then: Setup → Plugins → Gestão e Reserva de Veículos → Install →
Enable. This creates the `glpi_plugin_vehiclereservation_vehicles` table and grants the
vehicle management right to whoever already has config permission.

## Using it

1. Assets → Vehicles → Add (the license plate is required)
2. open the vehicle, **Reservations** tab, click "Authorize reservations"
3. from then on it shows up in Tools → Reservations, and the SelfService profile can
   reserve it

If SelfService users can't reserve, check that the profile has the native Reservations
right (Administration → Profiles → SelfService → Tools → Reservations).

## Uninstalling

Disable and uninstall through the UI (removes the table, the right, and only the
reservable items of the Vehicle type — other reservations are untouched), then delete the
folder. If the plugin gets stuck in the list, clear the cache:

```bash
rm -rf /var/www/glpi/files/_cache/*
```

## Languages

The code is in English (GLPI convention) and the UI works in English and Portuguese — the
language follows the GLPI session. PT-PT translations live in `locales/` (domain
`vehiclereservation`). After editing the `.po`:

```bash
msgfmt locales/pt_PT.po -o locales/pt_PT.mo
```

## Future — Verizon Connect

Each vehicle has a Verizon Connect GPS unit. The plan (not implemented) is to pull each
trip's route, pickup/stop times and intermediate stops from their API, linked to the
reservation. The `gps_device_id` column already exists in the table to map vehicle → GPS
unit, and `sql/install.sql` has the design of a future trips table in a comment. When this
moves forward, nothing should need rewriting.

## License

GPL v2+
