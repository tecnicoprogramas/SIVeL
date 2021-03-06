# Reglas comunes para crear distribuci�n de fuentes y publicar.  
# Para incluir en Makefile.
# Dominio p�blico. Sin garant�as. structio-info@lists.sourceforge.net

# Variables requeridas:

# PROYECTO nombre corto del proyecto 
# PRY_VERSION version
# ACT_PROC regla para actualizar, las disponibles en este script son act-scp y act-ncftpput
# GENDIST Reglas por iniciar anates de generar distribuci�n (e.g Derechos.txt)
# LIMPIADIST2 Reglas para ejecutar despu�s de haber limpiado directorio (para limpiar otros archivos que no deben incluirse en fuentes distribuidas).
# USERCVS, USERACT, ACTHOST and ACTDIR Usuario, m�quina y directorio en Internet para actualizar
# GENACT Reglas por iniciar antes de actualizar sitio en Internet (e.g index.html)
# FILESACT Archivos por transmitir a ACTHOST:ACTDIR


# Reglas requeridas en el Makefile:
# limpiadist 	Que debe limpiar todo excepto los archivos que deben ir
# 		en una distribuci�n de fuentes.


distcvs:
	cvs -d:pserver:anonymous@$(CVSPROYECTO) login
	cvs -z3 -d:pserver:anonymous@$(CVSPROYECTO) co -r$(RAMA) -P $(PROYECTO)
	mv $(PROYECTO) $(PROYECTO)-$(PRY_VERSION)
	tar cvfz $(PROYECTO)-$(PRY_VERSION).tar.gz $(PROYECTO)-$(PRY_VERSION)
	rm -rf $(PROYECTO)-$(PRY_VERSION)

dist:
	rm -f $(PROYECTO)-$(PRY_VERSION).tar.gz
	rm -rf $(PROYECTO)-$(PRY_VERSION)
	if (test "$(LISTA_DIST)" = "") then { a=`echo *`; } else { a="$(LISTA_DIST)"; } fi; echo "OJO a=$$a"; \
	mkdir -p $(PROYECTO)-$(PRY_VERSION); \
	cp -rf $$a $(PROYECTO)-$(PRY_VERSION)
	find $(PROYECTO)-$(PRY_VERSION) -name "CVS" | xargs rm -rf
	if (test "$(LIMPIADIST2)" != "") then { echo "LIMPIADIST2"; cd $(PROYECTO)-$(PRY_VERSION); make $(LIMPIADIST2);} fi;
	cp Make.inc $(PROYECTO)-$(PRY_VERSION); cd $(PROYECTO)-$(PRY_VERSION); make limpiadist; rm -f Make.inc
	tar cvfz $(PROYECTO)-$(PRY_VERSION).tar.gz $(PROYECTO)-$(PRY_VERSION)
	rm -rf $(PROYECTO)-$(PRY_VERSION)


distregr: $(PROYECTO)-$(PRY_VERSION).tar.gz
	if (test -d $(PROYECTO)-$(PRY_VERSION)) then {\
		echo "No debe exisitr directorio $(PROYECTO)-$(PRY_VERSION)"; \
	} fi;
	tar xvfz $(PROYECTO)-$(PRY_VERSION).tar.gz
	cp confv.sh $(PROYECTO)-$(PRY_VERSION)
	cd $(PROYECTO)-$(PRY_VERSION) && \
	./conf.sh && make && make regr && \
	cd .. && \
	rm -rf $(PROYECTO)-$(PRY_VERSION) && \
	echo "=============================" && \
	echo "Funciona!" && \
	echo "============================="


act: $(GENACT) $(ACT_PROC)
	if (test "$(OTHER_ACT)" != "") then { make $(OTHER_ACT); } fi;

act-scp:
	$(SCP) $(FILESACT) $(USERACT)@$(ACTHOST):$(ACTDIR)

act-ncftpput:
	$(NCFTPPUT) -u $(USERACT) $(ACTHOST) $(ACTDIR) $(FILESACT)
