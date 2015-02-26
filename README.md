# csrdelft.nl stek

## Docker

Build the image:

    docker build -t stek .

Run the image:

    docker run -it --rm \
      -p 8080:80 \
      -v `pwd`:/var/www/csrdelft.nl/:ro \
      --name running-stek stek
