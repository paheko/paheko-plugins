all: archives release

archives: */garradin_plugin.ini archives/
	@mkdir archives
	for i in */garradin_plugin.ini; \
	do \
		PLUGIN=`dirname $$i`; \
		php make_plugin.php $$PLUGIN archives/$$PLUGIN.tar.gz; \
	done;

release:
	cd archives && fossil uv add *
	fossil uv sync