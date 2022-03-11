SHELL:=/bin/bash
.PHONY: archives release
all: archives release

archives:
	@find . -type d -name '.phpintel' -exec rm -rf '{}' \;
	@mkdir -p archives
	@for PLUGIN in $(shell cat plugins.list); \
	do \
		echo $$PLUGIN; \
		rm -f archives/$$PLUGIN.tar.gz; \
		php make_plugin.php $$PLUGIN archives/$$PLUGIN.tar.gz; \
	done;

release:
	cd archives && fossil uv rm * && fossil uv sync && fossil uv add * && fossil uv sync