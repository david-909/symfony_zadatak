# symfony

Instalirati composer sa linka: https://getcomposer.org/download/

U powershellu:
-Instaliranje scoop-a
> Set-ExecutionPolicy RemoteSigned -Scope CurrentUser # Optional: Needed to run a remote script the first time
> irm get.scoop.sh | iex

-Instaliranje symfony-cli
> scoop install symfony-cli

Update composer paketa:
> composer update

U .env fajlu, podesiti DATABASE_URL sa validnim kredencijalima
> DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7&charset=utf8mb4"

Kreiranje baze:
> symfony console doctrine:database:create

Pokretanje migracija:
> symfony console doctrine:migrations:migrate

Pokretanje servera:
> symfony server:start
