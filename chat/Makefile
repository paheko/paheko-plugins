.PHONY := "clean all"

clean:
	rm -f public/emojis.js*

all: public/emojis.js

public/emojis.json:
	php extract_emojis.php > public/emojis.json

public/emojis.js: public/emojis.json
	echo -n 'const emojis = ' > public/emojis.js
	cat public/emojis.json >> public/emojis.js
	echo ';' >> public/emojis.js
