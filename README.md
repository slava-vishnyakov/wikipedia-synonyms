```sh
composer install
```

Run (note `head -2000000`! remove it to parse in whole):

```sh
curl https://dumps.wikimedia.org/enwiki/20180401/enwiki-20180401-pages-meta-current.xml.bz2 | \
    bunzip2 | head -2000000 | php get.php | sort > results.txt
```

Get:

```text
...
["air pollutant",{"emissions":1}]
["air pollution",{"black cloud":1}]
["air pressure",{"barometric pressure":2}]
...
```
