# Status

Verschillende onderdelen van de stek met hun refactor status

Legenda:

- MVC: Model, view en controller code strikt gescheiden. Controller zo dun mogelijk.
- ACL: Access control list hardcoded of dynamisch.
- DAC: Discretionary access control: toegang op basis van lidmaatschap van een groep.
- Doctrine: Gebruikt doctrine voor database.
- form: Formulieren niet hardcoden met HTML <form> tag maar gebruikmaken van Formulier.class.php.
- .tpl: Smarty templates om de GUI geheel te scheiden van de PHP code. (deprecated)
- .blade: Blade templates om de GUI geheel te scheiden van Model/Controller code.
- API: Application programming interface: HTML, JSON, mixed.

| Module              | MVC | ACL | DAC | Doctrine | form | .blade | API   |
| ------------------- | --- | --- | --- | -------- | ---- | ------ | ----- |
| Agenda              | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | mixed |
| Courant             | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | mixed |
| Forum               | ✔   | ✔   | ✔   | ✔        |      | ✔      | HTML  |
| Groepen             | ✔   | ✔   | ✔   | ✔        | ✔    |        | HTML  |
| Documenten          | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | mixed |
| Eetplan             | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | mixed |
| Login               | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | mixed |
| Profiel             | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | HTML  |
| Ledenlijst          |     |     |     |          |      |        | mixed |
| Bibliotheek         | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | mixed |
| FotoAlbum           | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | mixed |
| CMS                 | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | HTML  |
| Layout              | ✔   | ❌  |     | ✔        | ✔    | ✔      | HTML  |
| SocCie              |     |     |     |          |      |        | mixed |
| MaalCie             | ✔   | ✔   |     | ✔        |      | ✔      | HTML  |
| Menu                | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | mixed |
| InstellingenWebstek | ✔   | ✔   |     | ✔        |      | ✔      | mixed |
| Lidinstellingen     | ✔   | ✔   | ✔   | ✔        | nvt  | ✔      | mixed |
| LidToestemming      | ✔   | ✔   | ✔   | ✔        | nvt  | ✔      | mixed |
| Peilingen           | ✔   | ✔   | ✔   | ✔        |      | ✔      | HTML  |
| Commissievoorkeuren | ✔   | ✔   | ✔   | ✔        | ✔    | ✔      | HTML  |

♻ = Was ooit zo, nu gerefactored
