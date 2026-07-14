# glpi-vehiclereservation

🇬🇧 [English version here](README.en.md)

Plugin para GLPI 11 que acrescenta um tipo de ativo novo, **Veículo**, e o torna reservável
pelo motor de reservas nativo do GLPI — igual ao que já acontece com computadores ou
datashows. Fiz isto para gerir a reserva de viaturas de uma frota partilhada.

A ideia é não reinventar nada: o calendário, a disponibilidade, quem reservou, datas e
horas — tudo isso já existe no GLPI. O plugin só cria o asset com os campos de viatura e
regista-o como reservável com
`Plugin::registerClass(..., ['reservation_types' => true])`. O resto é o GLPI a trabalhar.

Tem um irmão para salas de reuniões: [glpi-roomreservation](https://github.com/SantiPT007/glpi-roomreservation)
(mesma arquitetura, campos diferentes).

## Campos

| Campo | Obrigatório | Notas |
|-------|:-----------:|-------|
| Nome | não | etiqueta livre; se vazio mostra-se «Marca Modelo (Matrícula)» |
| Marca | não | ex.: Renault |
| Modelo | não | ex.: Clio |
| Ano | não | 1900–2100 |
| Matrícula | **sim** | única |
| VIN | não | número de chassis |
| Localização | não | garagem/parque; a lista de reservas nativa usa isto |

## Antes de instalar

- GLPI 11.x, PHP 8.1+, MariaDB 10.11 ou equivalente
- o plugin [Reservation Alert](https://github.com/SantiPT007/glpi-reservationalert) tem de
  estar instalado e ativo — as reservas de veículos são reservas nativas
  (`glpi_reservations`) e é ele que trata das notificações e do cron para qualquer reserva,
  por isso não repeti esse código aqui. A instalação bloqueia se ele não estiver ativo.

## Instalar

```bash
cd /var/www/glpi/plugins
git clone https://github.com/SantiPT007/glpi-vehiclereservation vehiclereservation
chown -R www-data:www-data vehiclereservation
```

A pasta tem de se chamar mesmo `vehiclereservation`, o GLPI usa o nome da pasta como
identificador. Depois: Configurar → Plugins → Gestão e Reserva de Veículos → Instalar →
Ativar. Isto cria a tabela `glpi_plugin_vehiclereservation_vehicles` e dá o direito de
gestão de veículos a quem já tem permissão de configuração.

## Usar

1. Ativos → Veículos → Adicionar (a matrícula é obrigatória)
2. abrir a viatura, separador **Reservas**, clicar em «Autorizar as reservas»
3. a partir daí aparece em Ferramentas → Reservas, e o perfil SelfService consegue reservar

Se os utilizadores SelfService não conseguirem reservar, confirmar que o perfil tem o
direito nativo de Reservas (Administração → Perfis → SelfService → Ferramentas → Reservas).

## Desinstalar

Desativar e desinstalar pela interface (remove a tabela, o direito e só os itens
reserváveis do tipo Veículo — não mexe noutras reservas), depois apagar a pasta. Se o
plugin ficar preso na lista, limpar a cache:

```bash
rm -rf /var/www/glpi/files/_cache/*
```

## Idiomas

O código está em inglês (convenção GLPI) e a interface funciona em inglês e português —
a língua vem da sessão do GLPI. As traduções PT-PT estão em `locales/` (domínio
`vehiclereservation`). Depois de editar o `.po`:

```bash
msgfmt locales/pt_PT.po -o locales/pt_PT.mo
```

## Futuro — Verizon Connect

Cada viatura tem um cartão GPS da Verizon Connect. Está planeado (não implementado) ir
buscar à API deles o percurso, hora de levantamento/paragem e paragens intermédias de cada
viagem, associados à reserva. A coluna `gps_device_id` já existe na tabela para mapear
viatura → cartão, e o `sql/install.sql` tem em comentário o desenho de uma futura tabela
de viagens. Quando isto avançar, não deve ser preciso reescrever nada.

## Licença

GPL v2+
