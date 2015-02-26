# csrdelft.nl stek

## Docker

On linux you should be able to run this natively.
On any other platform, resort to boot2docker.

Build the image:

    docker build -t stek .

Run the image:

    docker run -it --rm \
      -p 8080:80 \
      -v `pwd`:/var/www/csrdelft.nl/:ro \
      --name running-stek stek
