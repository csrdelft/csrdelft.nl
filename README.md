# csrdelft.nl stek

## Plaetjes

In order for your development build to function correctly you'll need a bunch of images

Try to get these:

* /htdocs/plaetjes/famfamfam/
* /htdocs/plaetjes/pasfoto/geen-foto.jpg
* /htdocs/pleatjes/layout/

## Docker

This does **NOT** work yet!

On linux you should be able to run this natively.
On any other platform, resort to boot2docker.

Build the image:

    docker build -t stek .

Run the image:

    docker run -it --rm \
      -p 8080:80 \
      -v `pwd`:/var/www/csrdelft.nl/:ro \
      --name running-stek stek
