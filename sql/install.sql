-- Asset "Veiculo": campos especificos da viatura.
-- O nome da tabela tem de bater certo com o derivado da classe
-- GlpiPlugin\Vehiclereservation\Vehicle -> glpi_plugin_vehiclereservation_vehicles
CREATE TABLE IF NOT EXISTS `glpi_plugin_vehiclereservation_vehicles` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `entities_id`   INT UNSIGNED NOT NULL DEFAULT 0,
    `is_recursive`  TINYINT(1)   NOT NULL DEFAULT 0,
    `name`          VARCHAR(255) DEFAULT NULL,            -- nome/etiqueta da viatura
    `make`          VARCHAR(255) DEFAULT NULL,            -- marca
    `model`         VARCHAR(255) DEFAULT NULL,            -- modelo
    `year`          SMALLINT     DEFAULT NULL,            -- ano
    `license_plate` VARCHAR(32)  NOT NULL,               -- matricula (obrigatoria)
    `vin`           VARCHAR(64)  DEFAULT NULL,            -- numero de chassis (VIN)
    `locations_id`  INT UNSIGNED NOT NULL DEFAULT 0,      -- localizacao (garagem/parque); exigida pela lista de reservas nativa
    `gps_device_id` VARCHAR(64)  DEFAULT NULL,            -- FUTURO: id do cartao GPS Verizon Connect
    `comment`       TEXT         DEFAULT NULL,
    `is_deleted`    TINYINT(1)   NOT NULL DEFAULT 0,
    `date_creation` TIMESTAMP    NULL DEFAULT NULL,
    `date_mod`      TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `entities_id`   (`entities_id`),
    KEY `locations_id`  (`locations_id`),
    KEY `is_deleted`    (`is_deleted`),
    UNIQUE KEY `license_plate` (`license_plate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------
-- FUTURO (integracao Verizon Connect) — NAO criar agora, apenas planeado.
-- A coluna gps_device_id acima ja mapeia cada viatura ao cartao GPS.
-- Quando a integracao REST existir, criar algo como:
--
-- CREATE TABLE `glpi_plugin_vehiclereservation_trips` (
--     `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
--     `vehicles_id`        INT UNSIGNED NOT NULL,   -- FK -> _vehicles.id
--     `reservations_id`    INT UNSIGNED DEFAULT NULL,-- FK -> glpi_reservations.id (reserva associada)
--     `started_at`         DATETIME     DEFAULT NULL,-- hora de levantamento (arranque)
--     `stopped_at`         DATETIME     DEFAULT NULL,-- hora de paragem (regresso a garagem)
--     `route_geojson`      LONGTEXT     DEFAULT NULL,-- percurso feito
--     `intermediate_stops` LONGTEXT     DEFAULT NULL,-- paragens intermedias (carro parado/desligado)
--     `verizon_event_id`   VARCHAR(128) DEFAULT NULL,-- id do evento na API Verizon
--     `date_creation`      TIMESTAMP    NULL DEFAULT NULL,
--     PRIMARY KEY (`id`),
--     KEY `vehicles_id` (`vehicles_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ------------------------------------------------------------------------
