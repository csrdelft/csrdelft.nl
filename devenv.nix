{ pkgs, config, ... }:

{
  # https://devenv.sh/basics/

  # https://devenv.sh/packages/
  packages = [ pkgs.yarn ];

  # https://devenv.sh/scripts/
  scripts.setup-yarn.exec = ''
        	  set -e
    				trap "exit 0" SIGTERM;
        		yarn install;
        		yarn watch;
        		'';

  # enterShell = ''
  # '';

  # https://devenv.sh/languages/
  languages.php = {
    enable = true;
    version = "7.4";
    fpm.pools.web = {
      settings = {
        "pm" = "dynamic";
        "pm.max_children" = 5;
        "pm.start_servers" = 2;
        "pm.min_spare_servers" = 1;
        "pm.max_spare_servers" = 5;
      };
    };
  };

  languages.javascript.enable = true;

  # https://devenv.sh/pre-commit-hooks/
  # pre-commit.hooks.shellcheck.enable = true;

  # https://devenv.sh/processes/
  # processes.ping.exec = "ping example.com";
  processes = {
    yarn.exec = "setup-yarn";
    # Sleep infinity to prevent shutting down other processes
    composer.exec = "composer install --ignore-platform-reqs && sleep infinity";
  };

  services.mysql = {
    enable = true;
    ensureUsers = [
      {
        name = "csrdelft";
        ensurePermissions = {
          "csrdelft.*" = "ALL PRIVILEGES";
        };
      }
    ];
    initialDatabases = [
      { name = "csrdelft"; }
    ];
  };
  env.DATABASE_URL = "mysql://csrdelft:bl44t@localhost:3306/csrdelft";

  services.memcached.enable = true;
  services.caddy = {
    enable = true;
    config = ''{
			http_port 8000
		}'';
    virtualHosts."localhost:8000" = {
      extraConfig = ''
        root * htdocs
        php_fastcgi unix/${config.languages.php.fpm.pools.web.socket}
        file_server'';
    };
  };
  # See full reference at https://devenv.sh/reference/options/
}
