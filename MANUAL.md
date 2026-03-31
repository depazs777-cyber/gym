# Manual de Usuario

## 1. Inicio de Sesión
Ingrese su correo y contraseña. Si es un administrador de gimnasio, el sistema cargará automáticamente su entorno.

## 2. Dashboard
El panel principal muestra:
- **Miembros Activos:** Total de clientes con membresía vigente.
- **Ingresos Mes:** Facturación acumulada del mes en curso.
- **Accesos Hoy:** Cantidad de ingresos registrados por molinete/QR.

## 3. Contabilidad
El módulo contable sigue el principio de partida doble.
- **Ver Asientos:** Listado cronológico de movimientos.
- **Nuevo Recibo de Caja:**
  1.  Haga clic en "+ Nuevo Recibo".
  2.  Ingrese el concepto y el valor.
  3.  El sistema genera automáticamente el asiento (Débito a Caja, Crédito a Ingresos).

## 4. Reportes Financieros
Acceda a "Contabilidad" > "Reportes" para ver:
- **Balance General:** Estado de Situación Financiera (Activos, Pasivos, Patrimonio).
- **Estado de Resultados:** Pérdidas y Ganancias del ejercicio.
Ambos reportes se generan en tiempo real basado en los asientos contables confirmados ("posted").

## 5. Control de Acceso
- El sistema genera códigos QR únicos para cada miembro.
- Para validar un ingreso (simulación):
  1.  Ir a `Dashboard`.
  2.  Clic en "Escanear QR" (Simulado vía API).
  3.  El sistema valida: Existencia, Expiración, Estado de Membresía y Anti-passback (bloquea reingreso el mismo día).

## 6. Gestión de Afiliados
(Módulo backend listo en `MembersController`)
- Permite crear, editar y renovar membresías.
