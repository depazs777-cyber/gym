<?php
// Simplificación: Un modelo que abstraiga terceros (clientes, proveedores, empleados) para la contabilidad
class ThirdPartyModel extends Model {
    protected $table = 'members'; // En este caso usamos members como terceros (clientes) o podríamos tener tabla propia
    // Implementación básica, en un sistema real habría una tabla third_parties separada
}
