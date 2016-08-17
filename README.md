[![VersionEye](https://img.shields.io/versioneye/d/user/projects/5774370f99ed29003b2812ba.svg?style=flat-square)](https://www.versioneye.com/user/projects/5774370f99ed29003b2812ba)
[![Codacy grade](https://img.shields.io/codacy/grade/70ed86243f82444790e463c24c2a3a0c.svg?style=flat-square)](https://www.codacy.com/app/qurben/csrdelft-nl?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=csrdelft/csrdelft.nl&amp;utm_campaign=Badge_Grade)
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
