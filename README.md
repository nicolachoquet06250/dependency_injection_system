# dependency_injection_system
repo : https://github.com/nicolachoquet06250/dependency_injection_system.git
 - Websocket service :
     - Ratchet doc : 
       - https://packagist.org/packages/cboden/ratchet
       - https://github.com/ratchetphp/Ratchet
     - Wrench doc : https://github.com/varspool/Wrench
     
 - OAuth 2 tuto : https://www.sitepoint.com/creating-a-php-oauth-server/
 
# Installation

- ``git clone https://github.com/nicolachoquet06250/dependency_injection_system.git <project-name>``
- ``cd <prject-name>``
- ``php exe.php install:install -p dir=<custom-app-directory-name> repo=<custom-repository-url>``
    - ``custom-app-directory-name`` est un répertoire à la racine du projet dans lequel sera cloné 
    votre repo custom ( il sera créé automatiquement par git ).
    - ``custom-repository-url`` est votre repo custom qu'il faut avoir créé au préalable sur ``github``, ``gitlab`` ou autre.