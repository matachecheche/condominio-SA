#!/usr/bin/env bash
# ============================================================================
# ciclo4_reparar_entorno_v2.sh
#
# v2: ataca específicamente el caso en que 'apt-get install php-sqlite3'
# reporta éxito pero pdo_sqlite/sqlite3 NO terminan cargadas en el PHP CLI.
#
# Lo que viste con tu 'php --ini':
#   Loaded Configuration File: /etc/php/8.2/cli/php.ini
#   Scan for additional .ini files in: /etc/php/8.2/cli/conf.d
#   ... (lista de conf.d) ...
# y en esa lista NO aparece ningún archivo de sqlite (ni 20-sqlite3.ini ni
# 20-pdo_sqlite.ini), mientras que sí aparecen 20-pdo_mysql.ini y
# 20-pdo_pgsql.ini. Esto confirma que el paquete nunca llegó a instalar los
# .ini para PHP 8.2, aunque apt no haya mostrado error.
#
# CAUSA MÁS PROBABLE:
#   El meta-paquete 'php-sqlite3' (sin versión) puede:
#     a) no existir como tal en el repo configurado y quedar "instalado"
#        apuntando a un paquete virtual sin contenido real, o
#     b) instalar los archivos en una ruta de versión de PHP distinta a 8.2
#        (por ejemplo si hay múltiples PHP instalados: 8.1, 8.3, etc.)
#
# ESTE SCRIPT:
#   1) Detecta la versión EXACTA de PHP que usa la CLI (8.2 en tu caso).
#   2) Instala el paquete VERSIONADO explícito: php8.2-sqlite3.
#   3) Si el paquete no existe en los repos disponibles, agrega/actualiza
#      el repositorio de PPA de Ondřej Surý (el repo estándar de PHP en
#      Ubuntu/Codespaces) y reintenta.
#   4) Verifica que los archivos .ini de sqlite EXISTAN físicamente en
#      /etc/php/8.2/cli/conf.d/ (no solo confía en el exit code de apt).
#   5) Si los .ini existen pero PHP no los está usando, los habilita a mano
#      con phpenmod (si existe) o copiándolos al conf.d correcto.
#   6) Verifica de nuevo con 'php -m' y con una conexión PDO real.
#   7) Corre los tests si todo OK.
#
# USO:
#   bash ciclo4_reparar_entorno_v2.sh
#   bash ciclo4_reparar_entorno_v2.sh /workspaces/condominio-SA
# ============================================================================

set -uo pipefail

PROYECTO_DIR="${1:-$(pwd)}"
SIN_COLOR="\033[0m"
ROJO="\033[31m"
VERDE="\033[32m"
AMARILLO="\033[33m"
AZUL="\033[34m"

paso()  { echo -e "${AZUL}🔧 $1${SIN_COLOR}"; }
ok()    { echo -e "${VERDE}   ✔ $1${SIN_COLOR}"; }
warn()  { echo -e "${AMARILLO}⚠️  $1${SIN_COLOR}"; }
fallo() { echo -e "${ROJO}❌ $1${SIN_COLOR}"; }

ERRORES=0

echo "🚀 Reparación v2 de pdo_sqlite — diagnóstico profundo"
echo

# ---------------------------------------------------------------------------
# 1) Validaciones básicas
# ---------------------------------------------------------------------------
if [ ! -f "$PROYECTO_DIR/artisan" ]; then
    fallo "No se encontró '$PROYECTO_DIR/artisan'."
    echo "   Uso: bash $0 /workspaces/condominio-SA"
    exit 1
fi
cd "$PROYECTO_DIR" || exit 1

if ! command -v php >/dev/null 2>&1; then
    fallo "No se encontró 'php' en el PATH."
    exit 1
fi

SUDO=""
if [ "$(id -u)" -ne 0 ] && command -v sudo >/dev/null 2>&1; then
    SUDO="sudo"
fi

# ---------------------------------------------------------------------------
# 2) Detectar versión EXACTA de PHP y carpeta conf.d real
# ---------------------------------------------------------------------------
paso "Detectando versión exacta de PHP y su carpeta conf.d..."
PHP_MAJOR_MINOR="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
ok "PHP CLI: versión $PHP_MAJOR_MINOR"

CONFD_DIR="$(php --ini 2>/dev/null | grep -i 'Scan for additional' | sed -E 's/.*in:\s*//')"
if [ -z "$CONFD_DIR" ] || [ ! -d "$CONFD_DIR" ]; then
    warn "No se pudo determinar conf.d desde 'php --ini'; usando ruta estándar."
    CONFD_DIR="/etc/php/${PHP_MAJOR_MINOR}/cli/conf.d"
fi
ok "Carpeta conf.d activa para la CLI: $CONFD_DIR"
echo

# ---------------------------------------------------------------------------
# 3) Función de verificación real (no solo php -m, también busca el .ini)
# ---------------------------------------------------------------------------
verificar_extension_cargada() {
    local ext="$1"
    php -m 2>/dev/null | grep -qi "^${ext}$"
}

mostrar_estado_ini() {
    paso "Archivos .ini relacionados con sqlite en $CONFD_DIR:"
    if ls "$CONFD_DIR" 2>/dev/null | grep -qi sqlite; then
        ls "$CONFD_DIR" | grep -i sqlite | sed 's/^/   - /'
    else
        warn "Ningún archivo .ini de sqlite encontrado en $CONFD_DIR"
    fi
}

mostrar_estado_ini
echo

# ---------------------------------------------------------------------------
# 4) Si ya está todo cargado, saltamos directo a los tests
# ---------------------------------------------------------------------------
if verificar_extension_cargada pdo_sqlite && verificar_extension_cargada sqlite3; then
    ok "pdo_sqlite y sqlite3 YA están cargadas. No hace falta instalar nada."
else
    # -----------------------------------------------------------------------
    # 5) Instalar el paquete VERSIONADO explícito (no el genérico)
    # -----------------------------------------------------------------------
    if command -v apt-get >/dev/null 2>&1; then
        PAQUETE_VERSIONADO="php${PHP_MAJOR_MINOR}-sqlite3"
        paso "Actualizando índices de apt..."
        $SUDO apt-get update -y >/tmp/ciclo4_apt_update.log 2>&1
        ok "apt-get update ejecutado (log: /tmp/ciclo4_apt_update.log)"

        paso "Instalando paquete versionado explícito: $PAQUETE_VERSIONADO"
        if $SUDO apt-get install -y --reinstall "$PAQUETE_VERSIONADO" >/tmp/ciclo4_apt_install.log 2>&1; then
            ok "$PAQUETE_VERSIONADO instalado/reinstalado."
        else
            warn "No se pudo instalar $PAQUETE_VERSIONADO directamente."
            warn "Verificando si el paquete existe en los repos disponibles..."

            if apt-cache show "$PAQUETE_VERSIONADO" >/dev/null 2>&1; then
                fallo "El paquete existe en caché pero la instalación falló. Ver /tmp/ciclo4_apt_install.log"
                tail -20 /tmp/ciclo4_apt_install.log
                ERRORES=$((ERRORES+1))
            else
                warn "El paquete $PAQUETE_VERSIONADO no está en los repos actuales."
                warn "Agregando el repositorio oficial de PHP (PPA de Ondřej Surý), que es"
                warn "el que provee Codespaces para paquetes php8.x-* versionados..."

                if ! command -v add-apt-repository >/dev/null 2>&1; then
                    paso "Instalando software-properties-common (necesario para add-apt-repository)..."
                    $SUDO apt-get install -y software-properties-common >>/tmp/ciclo4_apt_install.log 2>&1
                fi

                if command -v add-apt-repository >/dev/null 2>&1; then
                    $SUDO add-apt-repository -y ppa:ondrej/php >>/tmp/ciclo4_apt_install.log 2>&1
                    $SUDO apt-get update -y >>/tmp/ciclo4_apt_update.log 2>&1

                    paso "Reintentando instalación de $PAQUETE_VERSIONADO tras agregar el repo..."
                    if $SUDO apt-get install -y "$PAQUETE_VERSIONADO" >>/tmp/ciclo4_apt_install.log 2>&1; then
                        ok "$PAQUETE_VERSIONADO instalado tras agregar el repositorio."
                    else
                        fallo "Sigue sin poder instalarse. Revisá /tmp/ciclo4_apt_install.log"
                        tail -30 /tmp/ciclo4_apt_install.log
                        ERRORES=$((ERRORES+1))
                    fi
                else
                    fallo "No se pudo instalar add-apt-repository. No se puede agregar el PPA."
                    ERRORES=$((ERRORES+1))
                fi
            fi
        fi

        # También instalamos el genérico libsqlite3 por si falta la lib del sistema
        paso "Asegurando libsqlite3-0 (librería del sistema, no la extensión PHP)..."
        $SUDO apt-get install -y libsqlite3-0 >>/tmp/ciclo4_apt_install.log 2>&1 && ok "libsqlite3-0 presente." || warn "No se pudo confirmar libsqlite3-0 (puede ya estar instalada)."

    else
        fallo "Este script v2 está hecho para apt-get (Debian/Ubuntu/Codespaces)."
        fallo "Tu sistema no tiene apt-get disponible."
        exit 1
    fi
    echo

    # -----------------------------------------------------------------------
    # 6) Verificar que los .ini quedaron físicamente en el conf.d correcto
    # -----------------------------------------------------------------------
    paso "Re-verificando archivos .ini en $CONFD_DIR..."
    mostrar_estado_ini
    echo

    # -----------------------------------------------------------------------
    # 7) Si dpkg dejó los .ini en otra versión de PHP, copiarlos a mano
    # -----------------------------------------------------------------------
    if ! ls "$CONFD_DIR" 2>/dev/null | grep -qi sqlite; then
        warn "Los .ini de sqlite no aparecieron en $CONFD_DIR tras la instalación."
        warn "Buscando en todo el sistema por si quedaron en otra ruta de PHP..."

        ENCONTRADOS=$(find /etc/php -iname "*sqlite*.ini" 2>/dev/null)
        if [ -n "$ENCONTRADOS" ]; then
            echo "$ENCONTRADOS" | sed 's/^/   - encontrado: /'
            paso "Copiando los .ini encontrados a $CONFD_DIR ..."
            for f in $ENCONTRADOS; do
                $SUDO cp -n "$f" "$CONFD_DIR/" 2>/dev/null && ok "Copiado $(basename "$f") a $CONFD_DIR"
            done
        else
            warn "No se encontraron archivos .ini de sqlite en ninguna ruta de /etc/php."
            warn "Esto sugiere que dpkg no llegó a desempaquetar los archivos del módulo."
            warn "Probá ejecutar manualmente: dpkg -L php${PHP_MAJOR_MINOR}-sqlite3"
            warn "para ver qué archivos cree que instaló ese paquete."
        fi
    fi

    # -----------------------------------------------------------------------
    # 8) Habilitar el módulo explícitamente si existe phpenmod
    # -----------------------------------------------------------------------
    if command -v phpenmod >/dev/null 2>&1; then
        paso "Habilitando módulos con phpenmod (pdo_sqlite, sqlite3)..."
        $SUDO phpenmod -v "$PHP_MAJOR_MINOR" pdo_sqlite >/dev/null 2>&1 && ok "phpenmod pdo_sqlite ejecutado." || warn "phpenmod pdo_sqlite no aplicó cambios (puede no ser necesario)."
        $SUDO phpenmod -v "$PHP_MAJOR_MINOR" sqlite3    >/dev/null 2>&1 && ok "phpenmod sqlite3 ejecutado."    || warn "phpenmod sqlite3 no aplicó cambios (puede no ser necesario)."
    fi
    echo
fi

# ---------------------------------------------------------------------------
# 9) Verificación final definitiva
# ---------------------------------------------------------------------------
paso "Verificación final con 'php -m'..."
FALTAN=""
for EXT in pdo_sqlite sqlite3; do
    if verificar_extension_cargada "$EXT"; then
        ok "Extensión '$EXT' cargada."
    else
        warn "Extensión '$EXT' sigue sin aparecer en 'php -m'."
        FALTAN="$FALTAN $EXT"
    fi
done
echo

if [ -n "$FALTAN" ]; then
    fallo "Faltan extensiones:$FALTAN"
    echo
    echo "Diagnóstico manual sugerido (copiá y pegá esto):"
    echo "  dpkg -l | grep -i sqlite"
    echo "  dpkg -L php${PHP_MAJOR_MINOR}-sqlite3 2>&1"
    echo "  find / -iname '*pdo_sqlite*' 2>/dev/null"
    echo "  php --ini"
    echo
    echo "Si el paquete aparece instalado por dpkg pero NO hay .so ni .ini,"
    echo "es muy probable que el Codespace esté usando una imagen de PHP"
    echo "compilada SIN soporte para sqlite (poco común, pero pasa con algunas"
    echo "imágenes 'slim' o devcontainers minimalistas). En ese caso la salida"
    echo "de 'find / -iname pdo_sqlite.so' será vacía, y la solución es"
    echo "reconstruir/cambiar la imagen base del devcontainer, no reinstalar"
    echo "el paquete de nuevo."
    exit 1
fi

# ---------------------------------------------------------------------------
# 10) Prueba real de PDO en memoria (igual a la query que falló)
# ---------------------------------------------------------------------------
paso "Probando PDO sqlite::memory: + PRAGMA foreign_keys = ON (igual al test)..."
PRUEBA_PDO=$(php -r '
try {
    $pdo = new PDO("sqlite::memory:");
    $pdo->exec("PRAGMA foreign_keys = ON;");
    echo "OK";
} catch (Throwable $e) {
    echo "FAIL: " . $e->getMessage();
}
' 2>&1)

if [ "$PRUEBA_PDO" = "OK" ]; then
    ok "Conexión PDO SQLite en memoria funciona correctamente."
else
    fallo "Sigue fallando: $PRUEBA_PDO"
    exit 1
fi
echo

# ---------------------------------------------------------------------------
# 11) Limpiar cachés de Laravel
# ---------------------------------------------------------------------------
paso "Limpiando cachés de Laravel..."
php artisan config:clear >/dev/null 2>&1 && ok "config:clear"
php artisan cache:clear  >/dev/null 2>&1 && ok "cache:clear"
echo

# ---------------------------------------------------------------------------
# 12) Correr los tests del Ciclo 4
# ---------------------------------------------------------------------------
if [ -f tests/Feature/Ciclo4PruebasTest.php ]; then
    paso "Corriendo: php artisan test --filter=Ciclo4PruebasTest"
    echo
    if php artisan test --filter=Ciclo4PruebasTest; then
        echo
        ok "🎉 Todos los tests pasaron."
    else
        echo
        fallo "Algunos tests fallaron, pero ya NO debería ser por el driver sqlite."
        ERRORES=$((ERRORES+1))
    fi
else
    warn "No se encontró tests/Feature/Ciclo4PruebasTest.php"
fi
echo

echo "────────────────────────────────────────────────────────────────"
if [ "$ERRORES" -eq 0 ]; then
    echo -e "${VERDE}✅ pdo_sqlite reparado y verificado funcionalmente.${SIN_COLOR}"
else
    echo -e "${AMARILLO}⚠️  Revisá los $ERRORES punto(s) marcados arriba.${SIN_COLOR}"
fi
echo "────────────────────────────────────────────────────────────────"
