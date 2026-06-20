#!/usr/bin/env bash
# ============================================================================
# ciclo4_reparar_entorno.sh
#
# Repara el entorno para poder correr los tests de Ciclo4PruebasTest
# (Ciclo 4 — CU17 a CU20) del proyecto condominio-SA.
#
# PROBLEMA DETECTADO en la corrida que compartiste:
#   could not find driver (Connection: sqlite, SQL: PRAGMA foreign_keys = ON;)
#
# CAUSA RAÍZ:
#   El test fuerza la conexión a SQLite en memoria (para no tocar tu base
#   real), pero el PHP de este contenedor/Codespace no tiene instalada la
#   extensión pdo_sqlite (ni, en algunos casos, sqlite3). Por eso TODOS los
#   tests fallan en el mismo punto (PRAGMA foreign_keys = ON), apenas Laravel
#   intenta abrir la conexión :memory:.
#
# QUÉ HACE ESTE SCRIPT:
#   1) Detecta el gestor de paquetes del sistema (apt/dnf/yum/apk/brew).
#   2) Instala php-sqlite3 (la extensión pdo_sqlite + sqlite3) sin tocar
#      ninguna otra extensión ni configuración existente.
#   3) Verifica que PHP cargó pdo_sqlite y Pdo_sqlite correctamente.
#   4) Limpia cachés de configuración de Laravel (config/cache), que a veces
#      quedan con datos viejos y esconden el problema real.
#   5) Corre `composer dump-autoload` solo si hace falta (no reinstala nada).
#   6) Ejecuta migrate:fresh --seed en tu base real (PostgreSQL) -- esto NO
#      afecta los tests, que usan SQLite en memoria aparte, pero lo deja
#      hecho porque lo corriste manualmente en tu sesión anterior.
#   7) Corre los tests --filter=Ciclo4PruebasTest y te muestra el resultado.
#
# NO TOCA:
#   - Lógica de negocio (Cuota.php, Residente.php, PagoController,
#     ReservaController, etc.) -- esos cambios ya están bien, según el log
#     del primer script.
#   - phpunit.xml ni .env -- el aislamiento de la base real ya está
#     garantizado por el propio archivo de test (SQLite forzado in-memory
#     dentro del test, no en phpunit.xml).
#
# USO:
#   bash ciclo4_reparar_entorno.sh
#
# Si tu proyecto está en otra ruta, pasala como primer argumento:
#   bash ciclo4_reparar_entorno.sh /workspaces/condominio-SA
# ============================================================================

set -uo pipefail

# ---------------------------------------------------------------------------
# 0) Configuración / utilidades de salida
# ---------------------------------------------------------------------------
PROYECTO_DIR="${1:-$(pwd)}"
SIN_COLOR="\033[0m"
ROJO="\033[31m"
VERDE="\033[32m"
AMARILLO="\033[33m"
AZUL="\033[34m"

paso()   { echo -e "${AZUL}🔧 $1${SIN_COLOR}"; }
ok()     { echo -e "${VERDE}   ✔ $1${SIN_COLOR}"; }
warn()   { echo -e "${AMARILLO}⚠️  $1${SIN_COLOR}"; }
fallo()  { echo -e "${ROJO}❌ $1${SIN_COLOR}"; }

ERRORES=0

echo "🚀 Reparando entorno de tests (pdo_sqlite) en: $PROYECTO_DIR"
echo

# ---------------------------------------------------------------------------
# 1) Verificar que estamos en un proyecto Laravel válido
# ---------------------------------------------------------------------------
if [ ! -f "$PROYECTO_DIR/artisan" ]; then
    fallo "No se encontró '$PROYECTO_DIR/artisan'. ¿La ruta del proyecto es correcta?"
    echo "   Sugerencia: bash $0 /workspaces/condominio-SA"
    exit 1
fi
cd "$PROYECTO_DIR" || exit 1
ok "Proyecto Laravel detectado en $PROYECTO_DIR"

if ! command -v php >/dev/null 2>&1; then
    fallo "No se encontró el comando 'php' en el PATH. Instalá PHP antes de continuar."
    exit 1
fi

PHP_VERSION="$(php -r 'echo PHP_VERSION;' 2>/dev/null)"
ok "PHP detectado: versión $PHP_VERSION"
echo

# ---------------------------------------------------------------------------
# 2) Detectar si pdo_sqlite ya está disponible
# ---------------------------------------------------------------------------
paso "Verificando extensión pdo_sqlite..."
TIENE_PDO_SQLITE=false
if php -m 2>/dev/null | grep -qi '^pdo_sqlite$'; then
    TIENE_PDO_SQLITE=true
    ok "pdo_sqlite ya está cargada en PHP. No hace falta instalar nada."
else
    warn "pdo_sqlite NO está cargada. Se intentará instalar."
fi
echo

# ---------------------------------------------------------------------------
# 3) Instalar la extensión si falta, detectando el gestor de paquetes
# ---------------------------------------------------------------------------
if [ "$TIENE_PDO_SQLITE" = false ]; then
    paso "Detectando gestor de paquetes del sistema..."

    SUDO=""
    if [ "$(id -u)" -ne 0 ]; then
        if command -v sudo >/dev/null 2>&1; then
            SUDO="sudo"
        else
            warn "No sos root y no hay 'sudo' disponible. La instalación puede fallar."
        fi
    fi

    INSTALADO=false

    # --- Debian / Ubuntu (Codespaces, devcontainers típicos) ---
    if command -v apt-get >/dev/null 2>&1; then
        paso "Gestor detectado: apt-get (Debian/Ubuntu). Instalando php-sqlite3..."
        $SUDO apt-get update -y >/tmp/ciclo4_apt_update.log 2>&1
        if $SUDO apt-get install -y php-sqlite3 >/tmp/ciclo4_apt_install.log 2>&1; then
            INSTALADO=true
        else
            # Algunos contenedores tienen PHP versionado (php8.2-sqlite3, etc.)
            warn "php-sqlite3 genérico falló. Probando con la versión específica de PHP ($PHP_VERSION)..."
            PHP_MAJOR_MINOR="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
            if $SUDO apt-get install -y "php${PHP_MAJOR_MINOR}-sqlite3" >>/tmp/ciclo4_apt_install.log 2>&1; then
                INSTALADO=true
            fi
        fi

    # --- Fedora / RHEL / CentOS ---
    elif command -v dnf >/dev/null 2>&1; then
        paso "Gestor detectado: dnf (Fedora/RHEL). Instalando php-pdo y php-sqlite3..."
        if $SUDO dnf install -y php-pdo php-sqlite3 >/tmp/ciclo4_dnf_install.log 2>&1; then
            INSTALADO=true
        fi
    elif command -v yum >/dev/null 2>&1; then
        paso "Gestor detectado: yum (CentOS/RHEL). Instalando php-pdo y php-sqlite3..."
        if $SUDO yum install -y php-pdo php-sqlite3 >/tmp/ciclo4_yum_install.log 2>&1; then
            INSTALADO=true
        fi

    # --- Alpine ---
    elif command -v apk >/dev/null 2>&1; then
        paso "Gestor detectado: apk (Alpine). Instalando php-pdo_sqlite..."
        if $SUDO apk add --no-cache php-pdo_sqlite php-sqlite3 >/tmp/ciclo4_apk_install.log 2>&1; then
            INSTALADO=true
        fi

    # --- macOS con Homebrew ---
    elif command -v brew >/dev/null 2>&1; then
        paso "Gestor detectado: Homebrew (macOS)."
        warn "En macOS con Homebrew, pdo_sqlite normalmente viene incluido en el build de PHP."
        warn "Si no está disponible, probá: brew reinstall php"

    else
        fallo "No se detectó un gestor de paquetes soportado (apt/dnf/yum/apk/brew)."
        fallo "Instalá manualmente la extensión PDO SQLite para tu sistema y volvé a correr este script."
        ERRORES=$((ERRORES+1))
    fi

    if [ "$INSTALADO" = true ]; then
        ok "Paquete de SQLite para PHP instalado."
    elif [ "$ERRORES" -eq 0 ]; then
        fallo "No se pudo instalar automáticamente. Revisá los logs en /tmp/ciclo4_*_install.log"
        ERRORES=$((ERRORES+1))
    fi
    echo
fi

# ---------------------------------------------------------------------------
# 4) Re-verificar que la extensión quedó cargada
# ---------------------------------------------------------------------------
paso "Re-verificando extensiones de PHP..."
FALTAN=""
for EXT in pdo_sqlite sqlite3; do
    if php -m 2>/dev/null | grep -qi "^${EXT}$"; then
        ok "Extensión '$EXT' cargada correctamente."
    else
        warn "Extensión '$EXT' todavía no aparece en 'php -m'."
        FALTAN="$FALTAN $EXT"
    fi
done

if [ -n "$FALTAN" ]; then
    fallo "Faltan extensiones:$FALTAN"
    fallo "Si instalaste algo y sigue sin aparecer, puede que necesites reiniciar el"
    fallo "servicio de PHP-FPM, o que el php.ini relevante no sea el que usa la CLI."
    echo "   Tip para diagnosticar: php --ini"
    ERRORES=$((ERRORES+1))
else
    ok "pdo_sqlite y sqlite3 disponibles para PHP CLI."
fi
echo

# Si no se pudo resolver la extensión, no tiene sentido seguir.
if [ "$ERRORES" -gt 0 ]; then
    fallo "No se pudo garantizar pdo_sqlite. Corregí lo anterior y volvé a correr el script."
    exit 1
fi

# ---------------------------------------------------------------------------
# 5) Limpiar cachés de configuración de Laravel
# ---------------------------------------------------------------------------
paso "Limpiando cachés de Laravel (config, route, view) para evitar falsos positivos..."
php artisan config:clear  >/dev/null 2>&1 && ok "config:clear" || warn "config:clear no se pudo ejecutar (no es crítico)"
php artisan cache:clear   >/dev/null 2>&1 && ok "cache:clear"  || warn "cache:clear no se pudo ejecutar (no es crítico)"
php artisan route:clear   >/dev/null 2>&1 && ok "route:clear"  || warn "route:clear no se pudo ejecutar (no es crítico)"
php artisan view:clear    >/dev/null 2>&1 && ok "view:clear"   || warn "view:clear no se pudo ejecutar (no es crítico)"
echo

# ---------------------------------------------------------------------------
# 6) Verificar autoload de Composer (sin reinstalar dependencias)
# ---------------------------------------------------------------------------
if [ -d vendor ]; then
    if command -v composer >/dev/null 2>&1; then
        paso "Regenerando autoload de Composer (dump-autoload)..."
        composer dump-autoload -q >/dev/null 2>&1 && ok "Autoload regenerado." || warn "No se pudo regenerar autoload (no es crítico)."
    fi
else
    warn "No se encontró carpeta 'vendor/'. Corré 'composer install' antes de los tests."
fi
echo

# ---------------------------------------------------------------------------
# 7) Probar una conexión SQLite en memoria real, igual a la del test
# ---------------------------------------------------------------------------
paso "Probando conexión PDO SQLite en memoria (simulando lo que hace el test)..."
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
    ok "Conexión PDO SQLite en memoria funciona correctamente (PRAGMA foreign_keys = ON)."
else
    fallo "La prueba de conexión PDO SQLite falló: $PRUEBA_PDO"
    fallo "El driver sigue sin estar disponible para PHP CLI. Revisá 'php --ini' y"
    fallo "confirmá que el php.ini cargado por la CLI tenga extension=pdo_sqlite."
    exit 1
fi
echo

# ---------------------------------------------------------------------------
# 8) Migrar y poblar la base real (PostgreSQL) -- igual a tu sesión anterior
# ---------------------------------------------------------------------------
paso "¿Migrar y poblar la base real (PostgreSQL) con migrate:fresh --seed?"
echo "   (Esto NO afecta a los tests, que corren en SQLite en memoria aparte.)"
read -r -p "   Continuar con migrate:fresh --seed sobre tu base real? [s/N]: " RESP
RESP="${RESP:-N}"
if [[ "$RESP" =~ ^[sSyY]$ ]]; then
    paso "Ejecutando migrate:fresh --seed --force..."
    if php artisan migrate:fresh --seed --force; then
        ok "Base real migrada y poblada correctamente."
    else
        fallo "migrate:fresh --seed terminó con errores. Revisá la salida arriba."
        ERRORES=$((ERRORES+1))
    fi
else
    warn "Se omitió migrate:fresh --seed (no se tocó tu base real)."
fi
echo

# ---------------------------------------------------------------------------
# 9) Correr los tests del Ciclo 4
# ---------------------------------------------------------------------------
if [ -f tests/Feature/Ciclo4PruebasTest.php ]; then
    paso "Corriendo tests: php artisan test --filter=Ciclo4PruebasTest"
    echo
    if php artisan test --filter=Ciclo4PruebasTest; then
        echo
        ok "🎉 Todos los tests de Ciclo4PruebasTest pasaron correctamente."
    else
        echo
        fallo "Algunos tests fallaron. Esto ya NO debería ser por 'pdo_sqlite' faltante."
        fallo "Revisá el detalle de cada test arriba: puede ser un dato de seed, un rol/permiso"
        fallo "faltante, o una validación de negocio que el test espera distinto."
        ERRORES=$((ERRORES+1))
    fi
else
    warn "No se encontró tests/Feature/Ciclo4PruebasTest.php en esta carpeta."
    warn "Si el archivo está en otra rama o todavía no se hizo commit/push, copialo"
    warn "a esta ruta y volvé a correr este script, o ejecutá manualmente:"
    echo "     php artisan test --filter=Ciclo4PruebasTest"
fi
echo

# ---------------------------------------------------------------------------
# 10) Resumen final
# ---------------------------------------------------------------------------
echo "────────────────────────────────────────────────────────────────"
if [ "$ERRORES" -eq 0 ]; then
    echo -e "${VERDE}✅ Entorno reparado. pdo_sqlite disponible y verificado.${SIN_COLOR}"
else
    echo -e "${AMARILLO}⚠️  Entorno parcialmente reparado. Revisá los $ERRORES punto(s) marcados arriba.${SIN_COLOR}"
fi
echo "────────────────────────────────────────────────────────────────"
