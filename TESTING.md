# Lista de Verificación QA (Testing)

## Pruebas de Seguridad (P0)
- [ ] Intentar acceder a `http://localhost/app/config/config.php`. **Resultado esperado:** 403 Forbidden o 404 Not Found.
- [ ] Intentar inyectar SQL en el login (`' OR 1=1 --`). **Resultado esperado:** Acceso denegado.
- [ ] Intentar acceder a `/dashboard` sin loguearse. **Resultado esperado:** Redirección a `/login`.

## Flujo Contable
1.  **Crear Recibo:**
    - Ir a Contabilidad > Nuevo Recibo.
    - Ingresar valor: 50000.
    - Guardar.
2.  **Verificar Asiento:**
    - En la lista, debe aparecer el nuevo asiento.
    - Verificar en BD: `total_debit` debe ser 50000, `total_credit` debe ser 50000.

## Flujo de Acceso
1.  **Generar Token:** (Requiere usar script o insertar en BD manualmente para prueba rápida, o usar botón si implementado).
2.  **Validar Token:**
    - Usar herramienta Postman o curl a `/access/scan`.
    - Payload: `token=UUID_DEL_TOKEN`.
    - **Resultado 1:** `allowed: true`.
    - **Resultado 2 (Reintento):** `allowed: false` (Anti-passback/Token usado).
