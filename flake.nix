{
	description = "C.S.R. Webstek dev flake";

	inputs = {
		nixpkgs.url = "github:NixOS/nixpkgs/nixos-26.05";
    flake-parts.url = "github:hercules-ci/flake-parts";
	};

	# https://unix.stackexchange.com/questions/464106/killing-background-processes-started-in-nix-shell/489687#489687
	outputs = inputs@ {flake-parts, ... }:
		flake-parts.lib.mkFlake { inherit inputs; } {
			systems = [ "x86_64-linux" ];

		  perSystem = { system, pkgs, ... }:
				let
					php = pkgs.php83.withExtensions (
						{ all, ... }:
						with all;
						[
							curl
							dom
							exif
							gd
							intl
							mysqli
							openssl
							pdo
							pdo_mysql
							simplexml
							zip
							imagick
							opcache
							memcached
							iconv
							filter
							tokenizer
						]
					);
				in
				{
					devShells.default = pkgs.mkShell {
						packages = with pkgs; [
							nodejs
							yarn
							symfony-cli
							php
							php83Packages.composer
							mariadb
							memcached
						];

						shellHook = ''
							set -eu

							DB_DIR="$PWD/.database/mysql"
							DB_SOCKET="$PWD/.database/mysql.sock"
							DB_PID="$PWD/.database/mariadb.pid"
							CACHE_PID="$PWD/.database/memcached.pid"

							mkdir -p .database/locks/

							trap '
								# Check if other instances are still running
								ls -1 .database/locks | grep -qv $$ && { rm .database/locks/$$; exit 0; }

								echo "Last instance exiting, stopping databases..."

								# Stop MariaDB
								if mariadb-admin --socket="$DB_SOCKET" ping >/dev/null 2>&1; then
									mariadb-admin --socket="$DB_SOCKET" shutdown || true
								fi

								# Stop Memcached
								if [ -f "$CACHE_PID" ]; then
									kill "$(cat "$CACHE_PID")" 2>/dev/null || true
									rm -f "$.database/locks/CACHE_PID"
								fi

								rm .database/locks/$$
							' EXIT

							if [[ ! -z "$(ls .database/locks)" ]]; then
								echo "db already running"
							else
								# Initialize MariaDB on first use
								if [ ! -d "$DB_DIR/mysql" ]; then
									mkdir -p "$DB_DIR"
									mariadb-install-db \
										--datadir="$DB_DIR" \
										--auth-root-authentication-method=normal \
										--skip-test-db
								fi

								# Start MariaDB if it is not already running
								if ! mariadb-admin --socket="$DB_SOCKET" ping > /dev/null 2>&1; then
									nohup mariadbd \
										--datadir="$DB_DIR" \
										--socket="$DB_SOCKET" \
										--pid-file="$DB_PID" \
										--bind-address=127.0.0.1 \
										--port=3307 \
										--log-error="$PWD/.database/mariadb.log" \
										> /dev/null 2>&1 &

									echo "Starting MariaDB..."
								fi

								# Start Memcached if it is not already running
								if [ -f "$CACHE_PID" ] && kill -0 "$(cat "$CACHE_PID")" 2>/dev/null; then
									:
								else
									rm -f "$CACHE_PID"

									nohup memcached \
										-l 127.0.0.1 \
										-p 11211 \
										-m 64 \
										-P "$CACHE_PID" \
										> "$PWD/.database/memcached.log" 2>&1 &

									echo "Starting Memcached..."
								fi
							fi

							touch .database/locks/$$

							echo "MariaDB socket: $DB_SOCKET"
							echo "MariaDB port:   3307"
							echo "Memcached port: 11211"

							export DATABASE_URL="mysql://root:@127.0.0.1:3307/csrdelft"
							export MEMCACHED_URL="memcached://127.0.0.1:11211"

							alias db-create='php bin/console doctrine:database:create'
							alias db-fixtures='php bin/console doctrine:fixtures:load'
							alias db-migrate='php bin/console doctrine:migrations:migrate'
							alias db-drop='php bin/console doctrine:database:drop --force'
						 '';
					};
				};
	};
}
