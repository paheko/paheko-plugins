.PHONY: archives release
all: archives release

archives:
	@mkdir -p archives
	for i in $(cat plugins.list); \
	do \
		PLUGIN=`dirname $$i`; \
		echo $$PLUGIN; \
		php make_plugin.php $$PLUGIN archives/$$PLUGIN.tar.gz; \
	done;

release:
	cd archives && fossil uv rm * && fossil uv sync && fossil uv add * && fossil uv sync