#!/usr/bin/env bash
# ============================================================================
# arreglar_faker_produccion.sh
#
# PROBLEMA REAL (no es el Dockerfile, ni el puerto, ni migrate:fresh):
#
#   El deploy en Render se cae con:
#     "Class Faker\Factory not found"
#
#   Pasa durante el seeding (EmpleadosSeeder y otros 8 seeders más usan
#   ::factory(), que internamente depende de fakerphp/faker). El problema
#   es que fakerphp/faker está declarado en "require-dev" de composer.json,
#   y el Dockerfile corre:
#     composer install --no-dev
#   --no-dev EXCLUYE intencionalmente todo lo de require-dev (es su función:
#   mantener la imagen de producción liviana, sin herramientas de testing).
#   Como tus seeders SÍ necesitan Faker en producción (porque querés que
#   cada deploy pueble la base con datos de prueba vía migrate:fresh --seed),
#   Faker tiene que estar en "require", no en "require-dev".
#
#   Solo editar composer.json a mano NO alcanza: composer.lock queda
#   desincronizado y composer install seguiría sin instalar Faker en
#   producción. Hay que usar el comando real de Composer para que mueva
#   el paquete y regenere el lock de forma consistente.
#
# QUÉ HACE ESTE SCRIPT:
#   1. Quita fakerphp/faker de require-dev (composer remove --dev)
#   2. Lo agrega a require, fijando la misma versión que ya tenías
#      (composer require, sin --dev)
#   3. Verifica que composer.lock quedó consistente
#
# Después de correr este script, hacé commit y push de composer.json
# y composer.lock — ESOS DOS ARCHIVOS son los que tienen que llegar a
# GitHub para que el próximo deploy en Render funcione.
#
# Ejecutar desde la raíz del proyecto (donde está 'artisan' y 'composer.json').
# ============================================================================
set -e

if [ ! -f "composer.json" ]; then
    echo "ERROR: no se encontró 'composer.json' en el directorio actual."
    echo "Ejecuta este script desde la raíz del proyecto Laravel."
    exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
    echo "ERROR: composer no está disponible en el PATH."
    exit 1
fi

echo "== Moviendo fakerphp/faker de require-dev a require =="
echo ""

# Usamos PHP (siempre disponible en un proyecto Laravel) para leer
# composer.json de forma confiable, en vez de parsear el texto a mano.
ALREADY_IN_REQUIRE=$(php -r '
$data = json_decode(file_get_contents("composer.json"), true);
echo isset($data["require"]["fakerphp/faker"]) ? "yes" : "no";
')

if [ "$ALREADY_IN_REQUIRE" = "yes" ]; then
    echo "fakerphp/faker ya está en 'require'. Sin cambios necesarios."
else
    CURRENT_VERSION=$(php -r '
        $data = json_decode(file_get_contents("composer.json"), true);
        echo $data["require-dev"]["fakerphp/faker"] ?? "^1.23";
    ')
    echo "Versión actual detectada: $CURRENT_VERSION"
    echo ""

    echo "[1/2] Quitando fakerphp/faker de require-dev..."
    composer remove --dev fakerphp/faker --no-update

    echo ""
    echo "[2/2] Agregando fakerphp/faker a require (dependencias de producción)..."
    composer require "fakerphp/faker:${CURRENT_VERSION}" --no-update

    echo ""
    echo "Actualizando composer.lock para que quede consistente..."
    composer update fakerphp/faker --with-all-dependencies
fi

echo ""
echo "Verificando que composer install --no-dev instalaría Faker correctamente..."
composer install --no-dev --dry-run 2>&1 | grep -i faker && echo "✔ Faker aparece en la instalación de producción." || echo "(revisa manualmente si no aparece ninguna línea con 'faker' arriba)"

echo ""
echo "== Listo =="
echo ""
echo "IMPORTANTE — esto todavía no llega a Render. Falta:"
echo "  1. git add composer.json composer.lock"
echo "  2. git commit -m 'fix: mover fakerphp/faker a dependencias de producción'"
echo "  3. git push"
echo ""
echo "Recién después de ese push, el próximo deploy en Render va a poder"
echo "ejecutar los seeders sin el error 'Class Faker\\Factory not found'."
