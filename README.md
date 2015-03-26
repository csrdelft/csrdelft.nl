# csrdelft.nl stek

## Plaetjes

In order for your development build to function correctly you'll need a bunch of images

Try to get these:

* /htdocs/plaetjes/famfamfam/
* /htdocs/plaetjes/pasfoto/geen-foto.jpg
* /htdocs/pleatjes/layout/
* /htdocs/pleatjes/layout2/

## Docker

On linux you should be able to run this natively.
On any other platform, resort to boot2docker.

    # Run the stek and database
    docker-compose up stek

    # initialize the database (only need to do this once)
    # make sure you have the dump.sql in the root of your repo
    docker run -ti --rm --link <reponame>_stekdb_1:db -v `pwd`:/mnt mariadb bash -c 'exec mysql  -h"$DB_PORT_3306_TCP_ADDR" -u root -p csrdelft < /mnt/dump.sql'

The plaetjes are not in the repo by default, but if you get them into `htdocs/plaetjes`, docker will
use those!
