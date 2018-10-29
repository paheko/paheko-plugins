.PHONY: archives release
all: archives release

archives:
	@mkdir -p archives
	for i in */garradin_plugin.ini; \
	do \
		PLUGIN=`dirname $$i`; \
		php make_plugin.php $$PLUGIN archives/$$PLUGIN.tar.gz; \
	done;

release:
	cd archives && fossil uv rm * && fossil uv sync && fossil uv add * && fossil uv sync