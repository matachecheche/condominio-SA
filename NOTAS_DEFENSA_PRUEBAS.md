# Notas: diferencias entre el PDF de defensa y la UI real

Estas dos cosas NO son bugs de código; son diferencias entre lo que describe
"defensa_pruebas_docente.pdf" y el comportamiento real e intencional del
sistema. Tenlas en cuenta al reproducir las pruebas manualmente en el
navegador (Capa 1) para que no parezca que el sistema "no funciona":

## 1. Ventana de horario en Visitas (CU10)

`VisitaController::registrarEntrada()` exige que el clic en "Registrar
Entrada" ocurra dentro de un rango estricto:

    (fecha_inicio - 30 minutos)  <=  ahora  <=  fecha_fin

Si registras la visita con un horario lejano (ej. "mañana 10am-12pm") y
luego intentas dar clic en "Registrar Entrada" de inmediato, el sistema
te rechazará con un mensaje de "Entrada muy temprana". Para probar este
caso manualmente, registra la visita con `fecha_inicio` = ahora mismo (o
unos minutos antes) y `fecha_fin` unas horas después, tal como hace el
test automatizado (`fecha_inicio = now()+1min`, `fecha_fin = now()+3h`).

## 2. Campo "estado" en Mantenimientos (CU9)

El formulario real (`mantenimientos/create.blade.php`) usa un radio
button con valores `1` (Programado) y `0` (Finalizado), no un `<select>`
de texto "Programado"/"Finalizado" como sugiere el PDF. Marca el radio
correspondiente en vez de buscar un dropdown.
