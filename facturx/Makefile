all:
	composer update
	# Cleanup useless files
	find vendor  -type f -not -name '*.php' -not -name '*.json' -not -name '*.xml' -not -name '*.xsd' -not -name '*.xmp' -not -name '*.icc' -not -name 'LICENSE*' -delete
	rm -rf vendor/setasign/fpdf/{makefont,tutorial,doc}
	rm -rf vendor/smalot/pdfparser/.github
	rm -rf vendor/atgp/factur-x/tests
	# mbstring polyfill not required
	rm -rf vendor/symfony